<?php

namespace OOTB;

class OpenAI {
	public string $url = 'https://api.openai.com/v1/chat/completions';
	public string $context = "You are an application that helps users to find locations on a map. The user enters a question in the search box and you need to find the answer to it. You only reply with the location's name, the city and the country that it belongs to. If the answer is a city, then you only reply with the city and the country. If the answer is a country, then you only reply with the country's name. If the answer has multiple locations, then you should include all locations to your answer. Your replies should be in an array. For example, if the question is `Which are the two biggest cities of Greece?`, the answer should be [\"Athens, Greece\", \"Thessaloniki, Greece\"]. If the question is `What is the capital of Greece?`, the answer should be [\"Athens, Greece\"]. If the question is `Eiffel Tower', the answer should be '[Eiffel Tower, Paris, France]'. If the question cannot be answered by a list of locations, then you should reply with a message `invalid_question`.";

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'openai_rest_endpoint' ] );
	}

	public function openai_rest_endpoint() {
		register_rest_route( 'ootb-openstreetmap/v1', '/openai/', [
			'methods'             => 'POST',
			'callback'            => [ $this, 'openai_callback' ],
			'permission_callback' => function () {
				return current_user_can( 'edit_posts' );
			}
		] );
	}

	public function openai_callback( \WP_REST_Request $request ) {
		$parameters = $request->get_json_params();
		$api_key    = Helper::get_option( 'api_openai' );

		$headers  = [
			'Authorization' => 'Bearer ' . $api_key,
			'Content-Type'  => 'application/json'
		];
		$body     = [
			'model'    => 'gpt-3.5-turbo',
			'messages' => [
				[
					'role'    => 'system',
					'content' => $this->context
				],
				[ 'role' => 'user', 'content' => $parameters[ 'prompt' ] ]
			]
		];
		$response = wp_safe_remote_post( $this->url, [
			'method'  => 'POST',
			'headers' => $headers,
			'body'    => json_encode( $body ),
			'timeout' => 15  // In seconds
		] );

		if ( is_wp_error( $response ) ) {
			return new \WP_Error( 'openai_error', __( 'An error occurred while making the OpenAI API request.', 'ootb-openstreetmap' ) );
		} else {
			return rest_ensure_response( json_decode( wp_remote_retrieve_body( $response ), true ) );
		}
	}
}
