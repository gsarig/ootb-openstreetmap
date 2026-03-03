<?php
/**
 * Trace why the editor script isn't being enqueued.
 * Only runs when ?trace_enqueue=1 or TRACE_ENQUEUE_DEBUG env var is set.
 */

$trace_enqueue_enabled = ( isset( $_GET['trace_enqueue'] ) && '1' === $_GET['trace_enqueue'] )
	|| getenv( 'TRACE_ENQUEUE_DEBUG' );

if ( $trace_enqueue_enabled ) {
	add_action( 'enqueue_block_editor_assets', function () {
		error_log( '=== enqueue_block_editor_assets fired ===' );
		global $wp_scripts;
		$handle = 'ootb-openstreetmap-editor-script';
		if ( isset( $wp_scripts->registered[ $handle ] ) ) {
			error_log( 'Script IS registered: ' . $handle );
			error_log( 'Script src: ' . $wp_scripts->registered[ $handle ]->src );
			error_log( 'Script deps: ' . print_r( $wp_scripts->registered[ $handle ]->deps, true ) );
			if ( in_array( $handle, $wp_scripts->queue, true ) ) {
				error_log( 'Script IS in queue!' );
			} else {
				error_log( 'Script NOT in queue' );
			}
		} else {
			error_log( 'Script NOT registered: ' . $handle );
		}
	}, 999 );

	add_action( 'admin_footer', function () {
		global $wp_scripts;
		$handle = 'ootb-openstreetmap-editor-script';
		error_log( '=== admin_footer check ===' );
		if ( in_array( $handle, $wp_scripts->queue, true ) ) {
			error_log( 'Script IS in final queue' );
		} else {
			error_log( 'Script NOT in final queue' );
		}
	}, 999 );
}
