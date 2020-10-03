<?php
/**
 * Plugin Name: Out of the Block: OpenStreetMap
 * Plugin URI: https://wordpress.org/plugins/ootb-openstreetmap/
 * Description: A map block for the Gutenberg Editor using OpenStreetMaps and Leaflet that needs no API keys and works out of the box.
 * Version: 1.3
 * Author: gsarig
 * Author URI: https://www.gsarigiannidis.gr
 * Text Domain: ootb-openstreetmap
 * Domain Path: /languages
 *
 */

use OOTB\Options;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Localize the plugin.
add_action( 'init', 'ootb_load_textdomain' );
function ootb_load_textdomain() {
	load_plugin_textdomain( 'ootb-openstreetmap', false, basename( dirname( __FILE__ ) ) . '/languages' );
}

// Add settings link on plugin page.
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ootb_settings_links' );
function ootb_settings_links( $links ) {
	array_unshift( $links,
		'<a href="' . admin_url( 'options-general.php?page=ootb-openstreetmap' ) . '">' . __( 'Settings',
			'ootb-openstreetmap' ) . '</a>' );

	return $links;
}

function ootb_blocks_plugin_dirpath( $file = '' ) {
	return plugin_dir_path( __FILE__ ) . $file;
}

require_once plugin_dir_path( __FILE__ ) . 'inc/Helper.class.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/init.php';
require_once plugin_dir_path( __FILE__ ) . 'inc/assets.php';
require_once plugin_dir_path( __FILE__ ) . '/admin/Options.class.php';

new Options();
