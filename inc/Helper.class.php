<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS on the block.
 *
 * @since   1.2
 * @package OOTB
 */

namespace OOTB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helper {
	public static function providers() {
		$json_file = trailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) . 'assets/providers.json';
		$request   = file_get_contents( $json_file );

		return json_decode( $request );
	}
}
