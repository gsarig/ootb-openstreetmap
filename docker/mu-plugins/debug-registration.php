<?php
/**
 * Debug block registration.
 * Only runs when ?ootb_debug=1 or OOTB_DEBUG=1 env var is set.
 */

$ootb_debug_enabled = ( isset( $_GET['ootb_debug'] ) && '1' === $_GET['ootb_debug'] )
	|| ( '1' === getenv( 'OOTB_DEBUG' ) );

if ( $ootb_debug_enabled ) {
	add_action( 'init', function () {
		error_log( '=== Checking block registration at init ===' );

		$block_path = OOTB_PLUGIN_PATH . '/build/block';
		error_log( 'Block path: ' . $block_path );
		error_log( 'Block path exists: ' . ( file_exists( $block_path ) ? 'YES' : 'NO' ) );
		error_log( 'block.json exists: ' . ( file_exists( $block_path . '/block.json' ) ? 'YES' : 'NO' ) );

		$block_json = file_get_contents( $block_path . '/block.json' );
		$block_data = json_decode( $block_json, true );
		error_log( 'Block name from JSON: ' . ( $block_data['name'] ?? 'NOT FOUND' ) );
		error_log( 'Editor script from JSON: ' . ( $block_data['editorScript'] ?? 'NOT FOUND' ) );

		// Check if block is registered.
		$registry = WP_Block_Type_Registry::get_instance();
		if ( $registry->is_registered( 'ootb/openstreetmap' ) ) {
			error_log( 'Block IS registered in WordPress' );
			$block_type = $registry->get_registered( 'ootb/openstreetmap' );
			error_log( 'Block editor_script handle: ' . ( $block_type->editor_script ?? 'NOT SET' ) );
		} else {
			error_log( 'Block NOT registered in WordPress' );
		}
	}, 999 );
}
