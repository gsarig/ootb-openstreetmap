<?php
/**
 * WordPress-integrated diagnostic script
 * Access via http://localhost:8080/?ootb_debug=1
 */

add_action('init', function() {
    if (!isset($_GET['ootb_debug'])) {
        return;
    }

    // Load plugin.php for get_plugins()
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    // Prevent output buffering
    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: text/html; charset=utf-8');

    echo "<h2>OOTB OpenStreetMap WordPress Diagnostics</h2>";

    echo "<h3>Plugin Status:</h3>";
    echo "<ul>";

    $plugin_file = 'ootb-openstreetmap/ootb-openstreetmap.php';
    $all_plugins = get_plugins();

    if (isset($all_plugins[$plugin_file])) {
        echo "<li>✓ Plugin found in plugin list</li>";
        echo "<li>Plugin Name: " . esc_html($all_plugins[$plugin_file]['Name']) . "</li>";
        echo "<li>Plugin Version: " . esc_html($all_plugins[$plugin_file]['Version']) . "</li>";
    } else {
        echo "<li>✗ Plugin NOT in plugin list</li>";
        echo "<li>Available plugins: <pre>" . esc_html(print_r(array_keys($all_plugins), true)) . "</pre></li>";
    }

    if (is_plugin_active($plugin_file)) {
        echo "<li>✓ Plugin is ACTIVE</li>";
    } else {
        echo "<li>✗ Plugin is NOT ACTIVE</li>";
    }

    echo "</ul>";

    echo "<h3>Block Registration:</h3>";
    echo "<ul>";

    $block_registry = WP_Block_Type_Registry::get_instance();
    $ootb_block = $block_registry->get_registered('ootb/openstreetmap');

    if ($ootb_block) {
        echo "<li>✓ Block 'ootb/openstreetmap' is REGISTERED</li>";
    } else {
        echo "<li>✗ Block 'ootb/openstreetmap' is NOT registered</li>";
        $all_blocks = array_keys($block_registry->get_all_registered());
        echo "<li>Registered blocks count: " . count($all_blocks) . "</li>";
        echo "<li>Sample blocks: " . esc_html(implode(', ', array_slice($all_blocks, 0, 10))) . "...</li>";
    }

    echo "</ul>";

    echo "<h3>Constants and Paths:</h3>";
    echo "<ul>";
    echo "<li>OOTB_PLUGIN_PATH: " . (defined('OOTB_PLUGIN_PATH') ? OOTB_PLUGIN_PATH : '✗ NOT DEFINED') . "</li>";
    echo "<li>OOTB_PLUGIN_URL: " . (defined('OOTB_PLUGIN_URL') ? OOTB_PLUGIN_URL : '✗ NOT DEFINED') . "</li>";
    if (defined('OOTB_PLUGIN_PATH')) {
        echo "<li>vendor/autoload.php exists: " . (file_exists(OOTB_PLUGIN_PATH . 'vendor/autoload.php') ? '✓ YES' : '✗ NO') . "</li>";
        echo "<li>build/block exists: " . (is_dir(OOTB_PLUGIN_PATH . 'build/block') ? '✓ YES' : '✗ NO') . "</li>";
    }
    echo "</ul>";

    die();
}, 999); // Run late to ensure blocks are registered
