<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS on the block.
 *
 * @since   1.0.0
 * @package OOTB
 */

use OOTB\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


function openstreetmap_ootb_block_assets() { // phpcs:ignore
	wp_register_style(
		'ootb-openstreetmap-style-css',
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ),
		is_admin() ? array( 'wp-editor' ) : null,
		filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' )
	);

	wp_register_script(
		'ootb-openstreetmap-block-js',
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ),
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
		filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ),
		true
	);

	wp_register_style(
		'ootb-openstreetmap-block-editor-css',
		plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ),
		array( 'wp-edit-blocks' ),
		filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' )
	);

	wp_localize_script(
		'ootb-openstreetmap-block-js',
		'ootbGlobal',
		[
			'pluginDirPath' => plugin_dir_path( __DIR__ ),
			'pluginDirUrl'  => plugin_dir_url( __DIR__ ),
			'options'       => get_option( 'ootb_options' ),
			'adminUrl'      => admin_url( 'options-general.php?page=ootb-openstreetmap' ),
			'providers'     => Helper::providers(),
		]
	);

	register_block_type(
		'ootb/openstreetmap',
		array(
			'style'         => 'ootb-openstreetmap-style-css',
			'editor_script' => 'ootb-openstreetmap-block-js',
			'editor_style'  => 'ootb-openstreetmap-block-editor-css',
		)
	);
}

add_action( 'init', 'openstreetmap_ootb_block_assets' );
