<?php
/**
 * Plugin Name:       Out of the Block: OpenStreetMap
 * Plugin URI:        https://wordpress.org/plugins/ootb-openstreetmap/
 * Description:       A map block for the Gutenberg Editor using OpenStreetMaps and Leaflet that needs no API keys and works out of the box.
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           2.0
 * Author:            gsarig
 * Author URI:        https://www.gsarigiannidis.gr
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ootb-openstreetmap
 * Domain Path:       /languages
 *
 * @package           ootb-openstreetmap
 */

use OOTB\Options;

define( 'OOTB_BLOCK_NAME', 'ootb/openstreetmap' );

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
require_once plugin_dir_path( __FILE__ ) . 'inc/assets.php';
require_once plugin_dir_path( __FILE__ ) . '/admin/Options.class.php';

new Options();

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_ootb_openstreetmap_block_init() {
	register_block_type( __DIR__ . '/build' );
	wp_add_inline_script(
		'ootb-openstreetmap-editor-script',
		'const ootbGlobal = ' . json_encode(
			[
				'pluginDirPath' => plugin_dir_path( __FILE__ ),
				'pluginDirUrl'  => plugin_dir_url( __FILE__ ),
				'options'       => get_option( 'ootb_options' ),
				'adminUrl'      => admin_url( 'options-general.php?page=ootb-openstreetmap' ),
				'providers'     => \OOTB\Helper::providers(),
			]
		),
		'before'
	);
}

add_action( 'init', 'create_block_ootb_openstreetmap_block_init' );



