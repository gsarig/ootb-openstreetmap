<?php
/**
 * Core plugin functionality.
 *
 * @since   2.0.0
 * @package ootb-openstreetmap
 */

namespace OOTB\Core;

use OOTB\Assets;
use OOTB\Helper;
use OOTB\OpenAI;
use OOTB\Options;
use OOTB\Query;

function setup() {
	$n = function ( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_action( 'init', $n( 'i18n' ) );
	add_action( 'init', $n( 'openstreetmap_block_init' ) );
}

new Options();
new Assets();
new OpenAI();
new Query();

/**
 * Registers the default textdomain.
 *
 * @return void
 */
function i18n() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'ootb-openstreetmap' );
	load_textdomain( 'ootb-openstreetmap', WP_LANG_DIR . '/ootb-openstreetmap/ootb-openstreetmap-' . $locale . '.mo' );
	load_plugin_textdomain( 'ootb-openstreetmap', false, plugin_basename( OOTB_PLUGIN_PATH ) . '/languages/' );
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets, so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 * @noinspection PhpUnused
 */
function openstreetmap_block_init() {
	register_block_type( OOTB_PLUGIN_PATH . '/build',
		[
			'render_callback' => '\OOTB\Query::render_callback'
		]
	);
	if ( is_admin() ) {
		wp_add_inline_script(
			'ootb-openstreetmap-editor-script',
			'const ootbGlobal = ' . wp_json_encode(
				[
					'pluginDirPath'   => OOTB_PLUGIN_PATH,
					'pluginDirUrl'    => OOTB_PLUGIN_URL,
					'options'         => Helper::get_option(),
					'adminUrl'        => admin_url( 'options-general.php?page=ootb-openstreetmap' ),
					'providers'       => Helper::providers(),
					'defaultLocation' => [ Helper::default_location() ],
					'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
					'postTypes'       => Helper::get_post_types(),
				]
			),
			'before'
		);
	}
}
