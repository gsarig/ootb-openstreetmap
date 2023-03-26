<?php
/**
 * Plugin Name:       Out of the Block: OpenStreetMap
 * Plugin URI:        https://wordpress.org/plugins/ootb-openstreetmap/
 * Description:       A map block for the Gutenberg Editor using OpenStreetMaps and Leaflet that needs no API keys and works out of the box.
 * Requires at least: 5.8.6
 * Requires PHP:      7.4
 * Version:           2.4.0
 * Author:            Giorgos Sarigiannidis
 * Author URI:        https://www.gsarigiannidis.gr
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ootb-openstreetmap
 * Domain Path:       /languages
 *
 * @package           ootb-openstreetmap
 */

define( 'OOTB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'OOTB_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'OOTB_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

const OOTB_BLOCK_NAME     = 'ootb/openstreetmap';
const OOTB_VERSION        = '2.4.0';
const OOTB_SCRIPT_VERSION = [
	'leaflet'                  => '1.9.3',
	'leaflet-gesture-handling' => '1.4.4',
];
const OOTB_PLUGIN_INC     = OOTB_PLUGIN_PATH . 'includes/';

// Require Composer autoloader if it exists.
if ( file_exists( OOTB_PLUGIN_PATH . 'vendor/autoload.php' ) ) {
	require_once OOTB_PLUGIN_PATH . 'vendor/autoload.php';
}

require_once OOTB_PLUGIN_INC . '/core.php';
\OOTB\Core\setup();
