<?php
/**
 * Assets
 *
 * @since   2.0.0
 * @package ootb-openstreetmap
 */

namespace OOTB;

class Assets {
	public function __construct() {
		add_action( 'enqueue_block_assets', [ $this, 'frontend' ] );
	}

	public function frontend() {

		if ( Helper::has_block_in_frontend( OOTB_BLOCK_NAME ) ) {
			$options        = get_option( 'ootb_options' );
			$handle_leaflet = 'leaflet';
			$dependencies   = [ $handle_leaflet ];
			wp_enqueue_style(
				$handle_leaflet,
				OOTB_PLUGIN_URL . 'assets/vendor/leaflet/leaflet.css',
				[],
				OOTB_SCRIPT_VERSION[ $handle_leaflet ]
			);
			wp_enqueue_script(
				$handle_leaflet,
				OOTB_PLUGIN_URL . 'assets/vendor/leaflet/leaflet.js',
				[],
				OOTB_SCRIPT_VERSION[ $handle_leaflet ],
				true
			);

			if ( ! empty( $options['prevent_default_gestures'] ) ) {
				$handle_gesture_handling = 'leaflet-gesture-handling';
				$dependencies[]          = $handle_gesture_handling;
				wp_enqueue_style(
					$handle_gesture_handling,
					OOTB_PLUGIN_URL . 'assets/vendor/leaflet-gesture-handling/leaflet-gesture-handling.css',
					[],
					OOTB_SCRIPT_VERSION[ $handle_gesture_handling ]
				);
				wp_enqueue_script(
					$handle_gesture_handling,
					OOTB_PLUGIN_URL . 'assets/vendor/leaflet-gesture-handling/leaflet-gesture-handling.js',
					[ $handle_leaflet ],
					OOTB_SCRIPT_VERSION[ $handle_gesture_handling ],
					true
				);
			}

			wp_enqueue_script(
				'ootb-openstreetmap',
				OOTB_PLUGIN_URL . 'assets/ootb-openstreetmap.js',
				$dependencies,
				OOTB_VERSION,
				true
			);
			wp_add_inline_script( 'ootb-openstreetmap',
				sprintf(
					'const ootb = %s',
					wp_json_encode(
						[
							'providers'              => Helper::providers(),
							'options'                => $options,
							'gestureHandlingOptions' => apply_filters( 'ootb_gesture_handling_options', [] ),
						]
					)
				),
				'before'
			);
		}
	}
}
