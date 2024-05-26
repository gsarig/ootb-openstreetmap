<?php /** @noinspection PhpComposerExtensionStubsInspection */

/**
 * Custom fields
 *
 * @since   2.8.0
 * @package ootb-openstreetmap
 */

namespace OOTB;

class CustomFields {

	public function __construct() {
		add_action( 'init', [ $this, 'register_meta' ] );
	}

	/**
	 * Register meta field.
	 */
	public function register_meta() {
		if ( ! Helper::get_option( 'geodata' ) ) {
			return;
		}
		register_meta(
			'post',
			'geo_latitude',
			[
				'type'              => 'number',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => 'sanitize_float',
			]
		);
		register_meta(
			'post',
			'geo_longitude',
			[
				'type'              => 'number',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => 'sanitize_float',
			]
		);
		register_meta(
			'post',
			'geo_address',
			[
				'type'              => 'string',
				'show_in_rest'      => true,
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
			]
		);
	}
}
