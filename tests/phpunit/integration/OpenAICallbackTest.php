<?php
/**
 * Integration tests for the OpenAI REST callback routing.
 *
 * Covers:
 * - Priority 1: plugin API key set → direct HTTP call (pre_http_request mock)
 * - Priority 2: no plugin key, wp_ai_client_prompt available → normalised response
 * - Priority 2 error: wp_ai_client_prompt returns WP_Error → 502
 * - Options notice: field_api_key_openai outputs the WP AI Client notice when available
 *
 * @package ootb-openstreetmap
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
// Stub for the WordPress 7.0+ WP AI Client API.
// Defined conditionally so it does not conflict in a real WP 7.0 environment.
namespace {
	if ( ! class_exists( 'MockWPAIClientPrompt' ) ) {
		class MockWPAIClientPrompt {
			/** @var string|\WP_Error */
			public static $next_result = '';

			public function using_system_instruction( string $instruction ): static {
				return $this;
			}

			public function generate_text( string $prompt ): string|\WP_Error {
				return static::$next_result;
			}
		}
	}

	if ( ! function_exists( 'wp_ai_client_prompt' ) ) {
		// Track that the stub is in control so tests can skip safely on WP 7.0+.
		define( 'OOTB_TEST_WP_AI_CLIENT_STUBBED', true );
		function wp_ai_client_prompt(): MockWPAIClientPrompt {
			return new MockWPAIClientPrompt();
		}
	} else {
		// Real wp_ai_client_prompt() is present (WP 7.0+). Tests that depend on the
		// controllable stub must be skipped to avoid hitting the real AI backend.
		define( 'OOTB_TEST_WP_AI_CLIENT_STUBBED', false );
	}
}
// phpcs:enable

namespace OOTB\Tests\Integration {

	use WP_UnitTestCase;

	class OpenAICallbackTest extends WP_UnitTestCase {

		private \OOTB\OpenAI $openai;

		public function setUp(): void {
			parent::setUp();
			$this->openai = new \OOTB\OpenAI();
			delete_option( 'ootb_options' );
			\MockWPAIClientPrompt::$next_result = '';
		}

		public function tearDown(): void {
			delete_option( 'ootb_options' );
			\MockWPAIClientPrompt::$next_result = '';
			parent::tearDown();
		}

		private function make_request( string $prompt = 'Capital of France' ): \WP_REST_Request {
			$request = new \WP_REST_Request( 'POST', '/ootb-openstreetmap/v1/openai/' );
			$request->set_param( 'prompt', $prompt );
			return $request;
		}

		/**
		 * Priority 1: plugin API key is set.
		 * The callback must use wp_safe_remote_post and pass the decoded response body through.
		 */
		public function test_plugin_key_path_passes_decoded_response_through(): void {
			update_option( 'ootb_options', [ 'api_openai' => 'sk-test-key' ] );

			$expected      = [ 'choices' => [ [ 'message' => [ 'content' => '["Paris, France"]' ] ] ] ];
			$fake_response = [
				'response' => [ 'code' => 200, 'message' => 'OK' ],
				'body'     => wp_json_encode( $expected ),
				'headers'  => [],
				'cookies'  => [],
			];

			$filter = static fn() => $fake_response;
			add_filter( 'pre_http_request', $filter );

			$response = $this->openai->openai_callback( $this->make_request() );

			remove_filter( 'pre_http_request', $filter );

			$this->assertInstanceOf( \WP_REST_Response::class, $response );
			$data = $response->get_data();
			$this->assertSame( '["Paris, France"]', $data['choices'][0]['message']['content'] );
		}

		/**
		 * Priority 2 success: no plugin key, wp_ai_client_prompt available and returns a string.
		 * The callback must normalise the result to the choices[0].message.content shape.
		 */
		public function test_wp_ai_client_returns_normalised_choices_shape(): void {
			if ( ! OOTB_TEST_WP_AI_CLIENT_STUBBED ) {
				$this->markTestSkipped( 'Requires the test stub; skipping on WP 7.0+ where wp_ai_client_prompt() is provided by core.' );
			}

			\MockWPAIClientPrompt::$next_result = '["Paris, France"]';

			$response = $this->openai->openai_callback( $this->make_request() );

			$this->assertInstanceOf( \WP_REST_Response::class, $response );
			$data = $response->get_data();
			$this->assertArrayHasKey( 'choices', $data );
			$this->assertSame( '["Paris, France"]', $data['choices'][0]['message']['content'] );
		}

		/**
		 * Priority 2 error: no plugin key, wp_ai_client_prompt returns a WP_Error.
		 * The callback must return a WP_Error with code wp_ai_client_error and HTTP status 502.
		 */
		public function test_wp_ai_client_error_returns_502(): void {
			if ( ! OOTB_TEST_WP_AI_CLIENT_STUBBED ) {
				$this->markTestSkipped( 'Requires the test stub; skipping on WP 7.0+ where wp_ai_client_prompt() is provided by core.' );
			}

			\MockWPAIClientPrompt::$next_result = new \WP_Error( 'connector_down', 'Upstream failure' );

			$response = $this->openai->openai_callback( $this->make_request() );

			$this->assertWPError( $response );
			$this->assertSame( 'wp_ai_client_error', $response->get_error_code() );
			$error_data = $response->get_error_data( 'wp_ai_client_error' );
			$this->assertSame( 502, $error_data['status'] );
		}

		/**
		 * Options: when wp_ai_client_prompt is available and no plugin key is set,
		 * the notice must tell the user the connector will be used automatically.
		 */
		public function test_options_notice_shown_when_wp_ai_client_available_and_no_key(): void {
			if ( ! OOTB_TEST_WP_AI_CLIENT_STUBBED ) {
				$this->markTestSkipped( 'Requires the test stub; skipping on WP 7.0+ where wp_ai_client_prompt() is provided by core.' );
			}

			$options = new \OOTB\Options();

			ob_start();
			$options->field_api_key_openai(
				[
					'label_for'   => 'api_openai',
					'description' => 'Your API key.',
				]
			);
			$output = ob_get_clean();

			$this->assertStringContainsString( 'notice-info', $output );
			$this->assertStringContainsString( 'use it automatically', $output );
			$this->assertStringNotContainsString( 'takes precedence', $output );
		}

		/**
		 * Options: when wp_ai_client_prompt is available and a plugin key is also set,
		 * the notice must clarify that the plugin key takes precedence.
		 */
		public function test_options_notice_shows_precedence_when_both_key_and_connector_set(): void {
			if ( ! OOTB_TEST_WP_AI_CLIENT_STUBBED ) {
				$this->markTestSkipped( 'Requires the test stub; skipping on WP 7.0+ where wp_ai_client_prompt() is provided by core.' );
			}

			update_option( 'ootb_options', [ 'api_openai' => 'sk-test-key' ] );

			$options = new \OOTB\Options();

			ob_start();
			$options->field_api_key_openai(
				[
					'label_for'   => 'api_openai',
					'description' => 'Your API key.',
				]
			);
			$output = ob_get_clean();

			$this->assertStringContainsString( 'notice-info', $output );
			$this->assertStringContainsString( 'takes precedence', $output );
			$this->assertStringNotContainsString( 'use it automatically', $output );
		}
	}
}
