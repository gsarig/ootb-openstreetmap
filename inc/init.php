<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package OOTB
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


function openstreetmap_ootb_block_assets() { // phpcs:ignore
	wp_register_style(
		'openstreetmap-ootb-style-css',
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ),
		is_admin() ? array( 'wp-editor' ) : null,
		filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.style.build.css' )
	);

	wp_register_script(
		'openstreetmap-ootb-block-js',
		plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ),
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
		filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ),
		true
	);

	wp_register_style(
		'openstreetmap-ootb-block-editor-css',
		plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ),
		array( 'wp-edit-blocks' ),
		filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.editor.build.css' )
	);

	wp_localize_script(
		'openstreetmap-ootb-block-js',
		'ootbGlobal',
		[
			'pluginDirPath' => plugin_dir_path( __DIR__ ),
			'pluginDirUrl'  => plugin_dir_url( __DIR__ ),
		]
	);

	register_block_type(
		'ootb/openstreetmap',
		array(
			'style'         => 'openstreetmap-ootb-style-css',
			'editor_script' => 'openstreetmap-ootb-block-js',
			'editor_style'  => 'openstreetmap-ootb-block-editor-css',
		)
	);
}

add_action( 'init', 'openstreetmap_ootb_block_assets' );
