<?php
/**
 * Uninstall Out of the Block: OpenStreetMap
 *
 * @package OOTB
 * @since 1.2
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

$option_name = 'ootb_options';

delete_option( $option_name );
