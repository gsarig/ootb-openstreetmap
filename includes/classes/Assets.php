<?php /** @noinspection PhpComposerExtensionStubsInspection */

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
		$this->frontend_assets();
	}

	public function frontend_assets() {
		$options        = Helper::get_option();
		$handle_leaflet = 'leaflet';
		$params         = [
			'providers' => Helper::providers(),
			'options'   => $options,
		];
		wp_register_script(
			$handle_leaflet,
			OOTB_PLUGIN_URL . 'assets/vendor/leaflet/leaflet.js',
			[],
			OOTB_SCRIPT_VERSION[ $handle_leaflet ],
			true
		);

		if ( ! empty( $options[ 'prevent_default_gestures' ] ) ) {
			$handle_gesture_handling            = 'leaflet-gesture-handling';
			$params[ 'gestureHandlingOptions' ] = apply_filters(
				'ootb_gesture_handling_options',
				[
					'locale' => Helper::get_gesture_handling_locale(),
				]
			);
			wp_register_script(
				$handle_gesture_handling,
				OOTB_PLUGIN_URL . 'assets/vendor/leaflet-gesture-handling/leaflet-gesture-handling.js',
				[ $handle_leaflet ],
				OOTB_SCRIPT_VERSION[ $handle_gesture_handling ],
				true
			);
		}

		wp_add_inline_script( 'ootb-openstreetmap-view-script',
			sprintf(
				'const ootb = %s',
				wp_json_encode( $params )
			),
			'before'
		);
	}
}
