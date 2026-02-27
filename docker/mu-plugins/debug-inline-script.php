<?php
/**
 * Debug what's happening with the inline script
 */

add_action('init', function() {
    if (!is_admin()) {
        return;
    }

    error_log('=== OOTB Debug ===');
    error_log('OOTB_PLUGIN_PATH: ' . (defined('OOTB_PLUGIN_PATH') ? OOTB_PLUGIN_PATH : 'NOT DEFINED'));

    if (class_exists('\OOTB\Helper')) {
        try {
            $options = \OOTB\Helper::get_option();
            error_log('Helper::get_option(): ' . print_r($options, true));
        } catch (Exception $e) {
            error_log('Helper::get_option() ERROR: ' . $e->getMessage());
        }

        try {
            $providers = \OOTB\Helper::providers();
            error_log('Helper::providers(): ' . print_r($providers, true));
        } catch (Exception $e) {
            error_log('Helper::providers() ERROR: ' . $e->getMessage());
        }

        try {
            $location = \OOTB\Helper::default_location();
            error_log('Helper::default_location(): ' . print_r($location, true));
        } catch (Exception $e) {
            error_log('Helper::default_location() ERROR: ' . $e->getMessage());
        }

        try {
            $post_types = \OOTB\Helper::get_post_types();
            error_log('Helper::get_post_types(): ' . print_r($post_types, true));
        } catch (Exception $e) {
            error_log('Helper::get_post_types() ERROR: ' . $e->getMessage());
        }
    } else {
        error_log('OOTB\Helper class NOT FOUND');
    }
}, 999);
