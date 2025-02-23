<?php /** @noinspection PhpComposerExtensionStubsInspection */

/**
 * OpenAI integration
 *
 * @since   2.5.0
 * @package ootb-openstreetmap
 */

namespace OOTB;

class OpenAI {
	public string $context = "You are an application that helps users to find locations on a map. The user enters a question in the search box and you need to find the answer to it. You only reply with the location's name, the city and the country that it belongs to. If the answer is a city, then you only reply with the city and the country. If the answer is a country, then you only reply with the country's name. If the answer has multiple locations, then you should include all locations to your answer. Your replies should be in an array. For example, if the question is `Which are the two biggest cities of Greece?`, the answer should be [\"Athens, Greece\", \"Thessaloniki, Greece\"]. If the question is `What is the capital of Greece?`, the answer should be [\"Athens, Greece\"]. If the question is `Eiffel Tower', the answer should be '[\"Eiffel Tower, Paris, France\"]'. If the question cannot be answered by a list of locations, then you should reply with a message `invalid_question`.";

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'openai_rest_endpoint' ] );
	}


	/**
	 * Provides default values for AI API configuration fields.
	 *
	 * @param string $field Optional. Specifies a particular configuration field to retrieve. Defaults to an empty string.
	 *
	 * @return array|string Returns an array of default configuration values if no specific field is requested.
	 *                      Returns a string containing the value of the requested field if it exists.
	 */
	public static function ai_api_defaults( string $field = '' ): array|string {
		$defaults = [
			'url'   => 'https://api.openai.com/v1/chat/completions',
			'model' => 'gpt-3.5-turbo',
		];
		if ( empty( $field ) || ! isset ( $defaults[ $field ] ) ) {
			return $defaults;
		}

		return $defaults[ $field ];
	}

	/**
	 * Registers a REST API endpoint for the OpenAI integration.
	 *
	 * @return void
	 */
	public function openai_rest_endpoint(): void {
		register_rest_route( 'ootb-openstreetmap/v1', '/openai/', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'openai_callback' ],
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			}
		] );
	}

	/**
	 * Sends a request to the OpenAI API and returns the API response or an error.
	 *
	 * @param \WP_REST_Request $request The REST request object containing parameters from the client.
	 *
	 * @return \WP_Error|\WP_REST_Response Returns a WP_Error if the API request fails or a WP_REST_Response object if the request is successful.
	 */
	public function openai_callback( \WP_REST_Request $request ): \WP_Error|\WP_REST_Response {
		$parameters = $request->get_json_params();
		$api_key    = Helper::get_option( 'api_openai' );
		$api_url    = ! empty( Helper::get_option( 'api_ai_provider' ) ) ? Helper::get_option( 'api_ai_provider' ) : self::ai_api_defaults( 'url' );
		$api_model  = ! empty( Helper::get_option( 'api_ai_model' ) ) ? Helper::get_option( 'api_ai_model' ) : self::ai_api_defaults( 'model' );
		$headers    = [
			'Authorization' => 'Bearer ' . $api_key,
			'Content-Type'  => 'application/json'
		];
		$body       = [
			'model'    => $api_model,
			'messages' => [
				[
					'role'    => 'system',
					'content' => $this->context
				],
				[ 'role' => 'user', 'content' => $parameters[ 'prompt' ] ]
			]
		];
		$response   = wp_safe_remote_post( $api_url, [
			'method'  => 'POST',
			'headers' => $headers,
			'body'    => wp_json_encode( $body ),
			'timeout' => 15  // In seconds
		] );

		if ( is_wp_error( $response ) ) {
			return new \WP_Error(
				'openai_error',
				sprintf(
					/* translators: %1$s is the error message */
					__( 'An error occurred while making the OpenAI API request: %1$s', 'ootb-openstreetmap' ),
					$response->get_error_message()
				),
			);
		} else {
			return rest_ensure_response( json_decode( wp_remote_retrieve_body( $response ), true ) );
		}
	}
}
