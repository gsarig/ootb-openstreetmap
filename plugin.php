<?php
/**
 * Plugin Name: Out of the Block: OpenStreetMap
 * Plugin URI: https://www.gsarigiannidis.gr
 * Description: An OpenStreet Maps block that works out of the box. Or should we say, ...Out of the Block?
 * Author: gsarig
 * Author URI: https://www.gsarigiannidis.gr
 * Version: 1.0.0
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 *
 * @package OOTB
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ootb_blocks_plugin_dirpath( $file = '' ) {
	$path = plugin_dir_path( __FILE__ ) . $file;

	return $path;
}

/**
 * Block Initializer.
 */
require_once plugin_dir_path( __FILE__ ) . 'inc/init.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/assets.php';
