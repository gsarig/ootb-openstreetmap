<?php
/**
 * Helper functions
 *
 * @since   1.2
 * @package ootb-openstreetmap
 */

namespace OOTB;

class Helper {
	/**
	 * Asset Providers.
	 *
	 * @return mixed
	 */
	public static function providers() {
		$json_file = OOTB_PLUGIN_PATH . 'assets/providers.json';

		return wp_json_file_decode( $json_file );
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

	/**
	 * The fallback coordinates.
	 *
	 * @return string[]
	 */
	public static function fallback_location(): array {
		return [
			"37.97155174977503",
			"23.72656345367432",
		];
	}

	/**
	 * Get the default location and return its coordinates.
	 *
	 * @return array|string[]
	 */
	public static function default_location(): array {
		$options = get_option( 'ootb_options' );
		if ( ! empty( $options['default_lat'] ) && ! empty( $options['default_lng'] ) ) {
			return [
				$options['default_lat'],
				$options['default_lng'],
			];
		}
		$timezone = wp_timezone_string();
		// Return empty if a manual timezone is set.
		if ( ! str_contains( $timezone, '/' ) ) {
			return self::fallback_location();
		}
		$defaults = wp_json_file_decode( OOTB_PLUGIN_PATH . '/assets/defaults.json', true );
		$column = array_column( $defaults, 'timezone' );
		$entry  = array_search( $timezone, $column );
		if ( empty( $defaults[ $entry ] ) || empty( $defaults[ $entry ]->lat ) || empty( $defaults[ $entry ]->lng ) ) {
			return self::fallback_location();
		}

		return [
			strval( $defaults[ $entry ]->lat ),
			strval( $defaults[ $entry ]->lng ),
		];
	}
}
