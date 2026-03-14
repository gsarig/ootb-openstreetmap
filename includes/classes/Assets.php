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
	private const HANDLE_MARKERCLUSTER_SCRIPT        = 'leaflet-markercluster';
	private const HANDLE_MARKERCLUSTER_STYLE         = 'leaflet-markercluster-style';
	private const HANDLE_MARKERCLUSTER_STYLE_DEFAULT = 'leaflet-markercluster-default-style';

	public string $handle_ootb_script                 = 'ootb-openstreetmap-view-script';
	public string $handle_leaflet                     = 'leaflet';
	public string $handle_fullscreen_script           = 'leaflet-fullscreen-script';
	public string $handle_fullscreen_style            = 'leaflet-fullscreen-style';
	public string $handle_markercluster_script        = self::HANDLE_MARKERCLUSTER_SCRIPT;
	public string $handle_markercluster_style         = self::HANDLE_MARKERCLUSTER_STYLE;
	public string $handle_markercluster_style_default = self::HANDLE_MARKERCLUSTER_STYLE_DEFAULT;

	public function __construct() {
		global $ootb_inline_scripts_tracking;
		$ootb_inline_scripts_tracking = [];
		add_action( 'enqueue_block_assets', [ $this, 'frontend' ] );
		add_action( 'enqueue_block_assets', [ $this, 'maybe_enqueue_clustering' ] );
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
		wp_enqueue_style( self::HANDLE_MARKERCLUSTER_STYLE );
		wp_enqueue_style( self::HANDLE_MARKERCLUSTER_STYLE_DEFAULT );
		wp_enqueue_script( self::HANDLE_MARKERCLUSTER_SCRIPT );

		// Guarantee the cluster script loads before the view script by injecting
		// it as a dependency. Without this, WordPress has no ordering constraint
		// between the two scripts and may print them in the wrong order, causing
		// typeof L.markerClusterGroup to be undefined when view.js runs.
		$scripts     = wp_scripts();
		$view_handle = 'ootb-openstreetmap-view-script';
		if ( isset( $scripts->registered[ $view_handle ] ) &&
			! in_array( self::HANDLE_MARKERCLUSTER_SCRIPT, $scripts->registered[ $view_handle ]->deps, true ) ) {
			$scripts->registered[ $view_handle ]->deps[] = self::HANDLE_MARKERCLUSTER_SCRIPT;
		}
	}

	public function maybe_enqueue_clustering(): void {
		foreach ( $this->get_post_contents_for_clustering_scan() as $content ) {
			if ( ( has_block( 'ootb/openstreetmap', $content ) &&
					$this->has_clustering_block( parse_blocks( $content ) ) ) ||
				( has_shortcode( $content, 'ootb_query' ) &&
					preg_match( '/\[ootb_query[^\]]*\benableclustering\s*=\s*["\']?true\b["\']?/i', $content ) ) ) {
				self::enqueue_clustering();
				return;
			}
		}
	}

	/**
	 * Returns post_content strings to scan for clustering.
	 * On singular pages this is just the current post; on archives/search/home
	 * it is all posts in the main query, so clustering CSS is enqueued before
	 * wp_head even on non-singular page types.
	 *
	 * @return string[]
	 */
	private function get_post_contents_for_clustering_scan(): array {
		$post = get_post();
		if ( $post instanceof \WP_Post ) {
			return [ $post->post_content ];
		}

		global $wp_query;
		if ( empty( $wp_query->posts ) || ! is_array( $wp_query->posts ) ) {
			return [];
		}

		$contents = [];
		foreach ( $wp_query->posts as $queried_post ) {
			if ( $queried_post instanceof \WP_Post ) {
				$contents[] = $queried_post->post_content;
			}
		}
		return $contents;
	}

	/**
	 * Recursively checks whether any block in the tree has enableClustering set.
	 *
	 * @param array<mixed> $blocks
	 */
	private function has_clustering_block( array $blocks ): bool {
		foreach ( $blocks as $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}
			if ( 'ootb/openstreetmap' === ( $block['blockName'] ?? '' ) &&
				! empty( $block['attrs']['enableClustering'] ) ) {
				return true;
			}
			$inner = $block['innerBlocks'] ?? [];
			if ( is_array( $inner ) && ! empty( $inner ) && $this->has_clustering_block( $inner ) ) {
				return true;
			}
		}
		return false;
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
		 * Note: iconCreateFunction, spiderfyShapePositions, and chunkProgress
		 * expect JavaScript functions and cannot be set through this filter —
		 * these keys are stripped automatically. Override them in JavaScript
		 * instead. Non-array return values are ignored.
		 *
		 * @since 2.11.0
		 */
		$cluster_options = apply_filters( 'ootb_marker_cluster_options', [] );
		if ( ! empty( $cluster_options ) && is_array( $cluster_options ) ) {
			// Strip keys that require JavaScript functions — passing them as PHP
			// values (strings, arrays) would cause markercluster to crash when
			// it tries to invoke them.
			unset( $cluster_options['iconCreateFunction'], $cluster_options['spiderfyShapePositions'], $cluster_options['chunkProgress'] );
			if ( ! empty( $cluster_options ) ) {
				$params['clusterOptions'] = $cluster_options;
			}
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
