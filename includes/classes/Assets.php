<?php /** @noinspection PhpComposerExtensionStubsInspection */

/**
 * Assets
 *
 * @since   2.0.0
 * @package ootb-openstreetmap
 */

namespace OOTB;

class Assets {
	public string $handle_ootb_script = 'ootb-openstreetmap-view-script';
	public string $handle_leaflet = 'leaflet';

	public function __construct() {
		global $ootb_inline_scripts_tracking;
		$ootb_inline_scripts_tracking = [];
		add_action( 'enqueue_block_assets', [ $this, 'frontend' ] );
	}

	public function frontend() {
		$this->frontend_assets();
		$this->script_variables();
	}

	public function shortcode_assets() {
		wp_enqueue_style( 'ootb-openstreetmap-style', '', $this->handle_leaflet, OOTB_SCRIPT_VERSION['leaflet'] );
		wp_enqueue_script( $this->handle_leaflet );
		wp_enqueue_script( $this->handle_ootb_script );
	}

	public function frontend_assets() {
		wp_register_script(
			$this->handle_leaflet,
			OOTB_PLUGIN_URL . 'assets/vendor/leaflet/leaflet.js',
			[],
			OOTB_SCRIPT_VERSION[ $this->handle_leaflet ],
			true
		);

		if ( ! empty( Helper::get_option( 'prevent_default_gestures' ) ) ) {
			$handle_gesture_handling = 'leaflet-gesture-handling';
			wp_register_script(
				$handle_gesture_handling,
				OOTB_PLUGIN_URL . 'assets/vendor/leaflet-gesture-handling/leaflet-gesture-handling.js',
				[ $this->handle_leaflet ],
				OOTB_SCRIPT_VERSION[ $handle_gesture_handling ],
				true
			);
		}
	}

	public function script_variables() {
		global $ootb_inline_scripts_tracking;
		// Do not proceed if the script is already present.
		if ( in_array( $this->handle_ootb_script, $ootb_inline_scripts_tracking, true ) ) {
			return;
		}
		$options = Helper::get_option();
		$params  = [
			'providers' => Helper::providers(),
			'options'   => $options,
		];

		if ( ! empty( $options[ 'prevent_default_gestures' ] ) ) {
			$params[ 'gestureHandlingOptions' ] = apply_filters(
				'ootb_gesture_handling_options',
				[
					'locale' => Helper::get_gesture_handling_locale(),
				]
			);
		}

		$ootb_inline_scripts_tracking[] = $this->handle_ootb_script;
		wp_add_inline_script( $this->handle_ootb_script,
			sprintf(
				'const ootb = %s',
				wp_json_encode( $params )
			),
			'before'
		);
	}
}
