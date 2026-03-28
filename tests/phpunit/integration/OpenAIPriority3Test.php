<?php
/**
 * Integration test for the OpenAI callback Priority 3 path.
 *
 * This file deliberately does NOT define a wp_ai_client_prompt() stub.
 * The test runs in a separate process so the stub from OpenAICallbackTest.php
 * is absent, making the priority-3 branch (no key, no WP AI Client) reachable.
 *
 * @package ootb-openstreetmap
 */

namespace OOTB\Tests\Integration;

use WP_UnitTestCase;

class OpenAIPriority3Test extends WP_UnitTestCase {

	/**
	 * Priority 3: no plugin API key and wp_ai_client_prompt not available.
	 * The callback must return a 400 WP_Error with code missing_api_key.
	 *
	 * Runs in a separate process so that the wp_ai_client_prompt stub defined
	 * in OpenAICallbackTest.php is not loaded into this process.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_returns_400_when_no_key_and_no_wp_ai_client(): void {
		if ( function_exists( 'wp_ai_client_prompt' ) ) {
			$this->markTestSkipped( 'wp_ai_client_prompt is available; priority-3 path is unreachable in this environment.' );
		}

		delete_option( 'ootb_options' );

		$openai  = new \OOTB\OpenAI();
		$request = new \WP_REST_Request( 'POST', '/ootb-openstreetmap/v1/openai/' );
		$request->set_param( 'prompt', 'Capital of France' );

		$response = $openai->openai_callback( $request );

		$this->assertWPError( $response );
		$this->assertSame( 'missing_api_key', $response->get_error_code() );
		$error_data = $response->get_error_data( 'missing_api_key' );
		$this->assertSame( 400, $error_data['status'] );
	}
}
