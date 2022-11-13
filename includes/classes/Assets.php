<?php
/**
 * Assets
 *
 * @since   2.0
 * @package ootb-openstreetmap
 */

namespace OOTB;

class Assets {
	public function __construct() {
		add_action( 'enqueue_block_assets', [ $this, 'frontend' ] );
	}

	public function frontend() {

		if ( Helper::has_block_in_frontend( OOTB_BLOCK_NAME ) ) {
			wp_enqueue_style(
				'leaflet',
				OOTB_PLUGIN_URL . 'assets/vendor/leaflet/leaflet.css',
				[],
				OOTB_VERSION
			);
			wp_enqueue_script(
				'leaflet',
				OOTB_PLUGIN_URL . 'assets/vendor/leaflet/leaflet.js',
				[],
				OOTB_VERSION,
				true
			);
			wp_enqueue_script(
				'ootb-openstreetmap',
				OOTB_PLUGIN_URL . 'assets/ootb-openstreetmap.js',
				[ 'leaflet' ],
				OOTB_VERSION,
				true
			);
			wp_add_inline_script( 'ootb-openstreetmap',
				sprintf(
					'const ootb = %s',
					wp_json_encode(
						[
							'providers' => Helper::providers(),
							'options'   => get_option( 'ootb_options' ),
						]
					)
				),
				'before'
			);
		}
	}
}
