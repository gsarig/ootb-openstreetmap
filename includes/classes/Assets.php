<?php
/**
 * Assets class for managing plugin assets.
 *
 * @noinspection PhpComposerExtensionStubsInspection
 *
 * @since   2.0.0
 * @package ootb-openstreetmap
 */

namespace OOTB;

class Assets {
	public string $handle_ootb_script               = 'ootb-openstreetmap-view-script';
	public string $handle_leaflet                   = 'leaflet';
	public string $handle_fullscreen_script         = 'leaflet-fullscreen-script';
	public string $handle_fullscreen_style          = 'leaflet-fullscreen-style';
	public string $handle_markercluster_script      = 'leaflet-markercluster';
	public string $handle_markercluster_style       = 'leaflet-markercluster-style';
	public string $handle_markercluster_style_default = 'leaflet-markercluster-default-style';

	public function __construct() {
		global $ootb_inline_scripts_tracking;
		$ootb_inline_scripts_tracking = [];
		add_action( 'enqueue_block_assets', [ $this, 'frontend' ] );
	}

	public function frontend(): void {
		$this->frontend_assets();
		$this->script_variables();
	}

	public function shortcode_assets(): void {
		wp_enqueue_style( 'ootb-openstreetmap-style', '', [ $this->handle_leaflet ], OOTB_SCRIPT_VERSION['leaflet'] );
		wp_enqueue_style( $this->handle_fullscreen_style );
		wp_enqueue_script( $this->handle_leaflet );
		wp_enqueue_script( $this->handle_fullscreen_script );
		wp_enqueue_script( $this->handle_ootb_script );
	}

	public function frontend_assets(): void {
		wp_register_script(
			$this->handle_leaflet,
			OOTB_PLUGIN_URL . 'assets/vendor/leaflet/leaflet.js',
			[],
			OOTB_SCRIPT_VERSION[ $this->handle_leaflet ],
			true
		);

		wp_register_style(
			$this->handle_fullscreen_style,
			OOTB_PLUGIN_URL . 'assets/vendor/leaflet-fullscreen/leaflet.fullscreen.css',
			[],
			OOTB_VERSION
		);

		wp_register_script(
			$this->handle_fullscreen_script,
			OOTB_PLUGIN_URL . 'assets/vendor/leaflet-fullscreen/Leaflet.fullscreen.js',
			[ $this->handle_leaflet ],
			OOTB_VERSION,
			true
		);

		wp_register_style(
			$this->handle_markercluster_style,
			OOTB_PLUGIN_URL . 'assets/vendor/leaflet-markercluster/MarkerCluster.css',
			[],
			OOTB_VERSION
		);

		wp_register_style(
			$this->handle_markercluster_style_default,
			OOTB_PLUGIN_URL . 'assets/vendor/leaflet-markercluster/MarkerCluster.Default.css',
			[],
			OOTB_VERSION
		);

		wp_register_script(
			$this->handle_markercluster_script,
			OOTB_PLUGIN_URL . 'assets/vendor/leaflet-markercluster/leaflet.markercluster.js',
			[ $this->handle_leaflet ],
			OOTB_VERSION,
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

	public static function enqueue_clustering(): void {
		wp_enqueue_style( 'leaflet-markercluster-style' );
		wp_enqueue_style( 'leaflet-markercluster-default-style' );
		wp_enqueue_script( 'leaflet-markercluster' );

		// Guarantee the cluster script loads before the view script by injecting
		// it as a dependency. Without this, WordPress has no ordering constraint
		// between the two scripts and may print them in the wrong order, causing
		// typeof L.markerClusterGroup to be undefined when view.js runs.
		global $wp_scripts;
		$view_handle = 'ootb-openstreetmap-view-script';
		if ( isset( $wp_scripts->registered[ $view_handle ] ) &&
			! in_array( 'leaflet-markercluster', $wp_scripts->registered[ $view_handle ]->deps, true ) ) {
			$wp_scripts->registered[ $view_handle ]->deps[] = 'leaflet-markercluster';
		}
	}

	public function script_variables(): void {
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

		if ( ! empty( $options['prevent_default_gestures'] ) ) {
			$params['gestureHandlingOptions'] = apply_filters(
				'ootb_gesture_handling_options',
				[
					'locale' => Helper::get_gesture_handling_locale(),
				]
			);
		}

		/**
		 * Filters the options passed to L.markerClusterGroup() on the frontend.
		 *
		 * All scalar/array options supported by Leaflet.markercluster can be set
		 * here (e.g. maxClusterRadius, disableClusteringAtZoom, showCoverageOnHover).
		 * Note: iconCreateFunction expects a JavaScript function and cannot be set
		 * through this filter — override it in JavaScript instead.
		 * Non-array return values are ignored.
		 *
		 * @since 2.11.0
		 */
		$cluster_options = apply_filters( 'ootb_marker_cluster_options', [] );
		if ( ! empty( $cluster_options ) && is_array( $cluster_options ) ) {
			$params['clusterOptions'] = $cluster_options;
		}

		$ootb_inline_scripts_tracking[] = $this->handle_ootb_script;
		wp_add_inline_script(
			$this->handle_ootb_script,
			sprintf(
				'const ootb = %s',
				wp_json_encode( $params )
			),
			'before'
		);
	}
}
