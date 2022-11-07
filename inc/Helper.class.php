<?php
/**
 * Helper functions
 *
 * @since   1.2
 * @package ootb-openstreetmap
 */

namespace OOTB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helper {
	/**
	 * Asset Providers.
	 *
	 * @return mixed
	 */
	public static function providers() {
		$json_file = trailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) . 'assets/providers.json';
		$request   = file_get_contents( $json_file );

		return json_decode( $request );
	}

	/**
	 * Checks if the block exists in the frontend.
	 *
	 * @param string $block_name The block name.
	 *
	 * @return bool
	 */
	public static function has_block_in_frontend( string $block_name = '' ): bool {
		if ( empty( $block_name ) ) {
			return false;
		}

		return (
			       has_block( $block_name ) ||
			       self::has_block_in_reusable( $block_name ) ||
			       self::has_block_in_widget( $block_name )
		       )
		       && ! is_admin();
	}

	/**
	 * Check if the block exists in a reusable block.
	 *
	 * @param string $block_name The block name.
	 * @param int $post_id The post ID.
	 *
	 * @return bool
	 */
	public static function has_block_in_reusable( string $block_name = '', int $post_id = 0 ): bool {
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
			if ( 'core/block' === $block['blockName'] && ! empty( $block['attrs']['ref'] ) ) {
				if ( has_block( $block_name, $block['attrs']['ref'] ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if the block exists in a widget.
	 *
	 * @param string $block_name The block name.
	 *
	 * @return bool
	 */
	public static function has_block_in_widget( string $block_name = '' ): bool {
		if ( empty( $block_name ) ) {
			return false;
		}

		$blocks = get_option( 'widget_block' );

		if ( ! is_array( $blocks ) || empty( $blocks ) ) {
			return false;
		}

		foreach ( $blocks as $block ) {
			if ( is_array( $block ) && isset( $block['content'] ) && has_block( $block_name, $block['content'] ) ) {
				return true;
			}
		}

		return false;
	}
}
