<?php
/**
 * Plugin Name: Out of the Block: OpenStreetMap
 * Plugin URI: https://wordpress.org/plugins/ootb-openstreetmap/
 * Description: A map block for the Gutenberg Editor using OpenStreetMaps and Leaflet that needs no API keys and works out of the box.
 * Version: 1.1
 * Author: gsarig
 * Author URI: https://www.gsarigiannidis.gr
 * Text Domain: ootb-openstreetmap
 * Domain Path: /languages
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Localize the plugin.
add_action( 'init', 'ootb_load_textdomain' );
function ootb_load_textdomain() {
	load_plugin_textdomain( 'ootb-openstreetmap', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

function ootb_blocks_plugin_dirpath( $file = '' ) {
	return plugin_dir_path( __FILE__ ) . $file;
}

require_once plugin_dir_path( __FILE__ ) . 'inc/init.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/assets.php';
