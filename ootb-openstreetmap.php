<?php
/**
 * Plugin Name: Out of the Block: OpenStreetMap
 * Plugin URI: https://wordpress.org/plugins/ootb-openstreetmap/
 * Description: A map block for the Gutenberg Editor using OpenStreetMaps and Leaflet that needs no API keys and works out of the box.
 * Version: 1.0.1
 * Author: gsarig
 * Author URI: https://www.gsarigiannidis.gr
 * Text Domain: ootb-openstreetmap
 * Domain Path: /languages
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ootb_blocks_plugin_dirpath( $file = '' ) {
	return plugin_dir_path( __FILE__ ) . $file;
}

require_once plugin_dir_path( __FILE__ ) . 'inc/init.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/assets.php';
