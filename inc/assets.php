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
	if ( ( has_block( 'ootb/openstreetmap' ) || has_block_in_reusable( 'ootb/openstreetmap' ) ) && ! is_admin() ) {

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

if ( ! function_exists( 'has_block_in_reusable' ) ) {
	/**
	 * Check if the block exists in a reusable block.
	 *
	 * @param string $block_name The block name.
	 * @param int $post_id The post ID.
	 *
	 * @return bool
	 */
	function has_block_in_reusable( string $block_name = '', int $post_id = 0 ): bool {
		if ( empty( $block_name ) ) {
			return false;
		}

		$post_id = ( 0 !== $post_id ) ? $post_id : get_the_ID();

		if ( empty( $post_id ) || ! has_block( 'block', $post_id ) ) {
			return false;
		}

		$content = get_post_field( 'post_content', $post_id );
		$blocks  = parse_blocks( $content );

		if ( ! is_array( $blocks ) || empty( $blocks ) ) {
			return false;
		}

		foreach ( $blocks as $block ) {
			if ( $block['blockName'] === 'core/block' && ! empty( $block['attrs']['ref'] ) ) {
				if ( has_block( $block_name, $block['attrs']['ref'] ) ) {
					return true;
				}
			}
		}

		return false;
	}
}
