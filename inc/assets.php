<?php
/**
 * Enqueue frontend assets
 *
 * @since   1.0.0
 * @package OOTB
 */

use OOTB\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
add_action( 'enqueue_block_assets', 'openstreetmap_non_react_block_assets' );
function openstreetmap_non_react_block_assets() {
	if ( has_block( 'ootb/openstreetmap' ) && ! is_admin() ) {

		$leaflet_css = 'assets/vendor/leaflet/leaflet.css';
		wp_enqueue_style(
			'leaflet',
			plugins_url( $leaflet_css, dirname( __FILE__ ) ),
			[]
		);
		$leaflet_js = 'assets/vendor/leaflet/leaflet.js';
		wp_enqueue_script(
			'leaflet',
			plugins_url( $leaflet_js, dirname( __FILE__ ) ),
			[],
			filemtime( ootb_blocks_plugin_dirpath( $leaflet_js ) ),
			true
		);

		$openstreetmap = 'assets/ootb-openstreetmap.js';
		wp_enqueue_script(
			'ootb-openstreetmap',
			plugins_url( $openstreetmap, dirname( __FILE__ ) ),
			[ 'leaflet' ],
			filemtime( ootb_blocks_plugin_dirpath( $openstreetmap ) ),
			true
		);
		wp_localize_script( 'ootb-openstreetmap',
			'ootb',
			[
				'providers' => Helper::providers(),
				'options'   => get_option( 'ootb_options' ),
			] );
	}
}
