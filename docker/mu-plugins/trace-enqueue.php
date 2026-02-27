<?php
/**
 * Trace why the editor script isn't being enqueued
 */

// Log when scripts are enqueued
add_action('enqueue_block_editor_assets', function() {
    error_log('=== enqueue_block_editor_assets fired ===');

    global $wp_scripts;

    $handle = 'ootb-openstreetmap-editor-script';

    if (isset($wp_scripts->registered[$handle])) {
        error_log('Script IS registered: ' . $handle);
        error_log('Script src: ' . $wp_scripts->registered[$handle]->src);
        error_log('Script deps: ' . print_r($wp_scripts->registered[$handle]->deps, true));

        if (in_array($handle, $wp_scripts->queue)) {
            error_log('Script IS in queue!');
        } else {
            error_log('Script NOT in queue - manually enqueueing...');
            wp_enqueue_script($handle);
        }
    } else {
        error_log('Script NOT registered: ' . $handle);
    }
}, 999);

// Check after everything
add_action('admin_footer', function() {
    global $wp_scripts;

    $handle = 'ootb-openstreetmap-editor-script';

    error_log('=== admin_footer check ===');
    if (in_array($handle, $wp_scripts->queue)) {
        error_log('Script IS in final queue');
    } else {
        error_log('Script NOT in final queue');
    }
}, 999);
