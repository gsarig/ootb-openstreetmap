<?php
add_action( 'enqueue_block_assets', 'openstreetmap_non_react_block_assets' );
function openstreetmap_non_react_block_assets() {
	if ( has_block( 'ootb/openstreetmap' ) && ! is_admin() ) {

		$leaflet_css = 'assets/leaflet/leaflet.css';
		wp_enqueue_style(
			'leaflet',
			plugins_url( $leaflet_css, dirname( __FILE__ ) ),
			[],
		);
		$leaflet_js = 'assets/leaflet/leaflet.js';
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

	}
}
