<?php
/**
 * Show plugin options status
 * Visit http://localhost:8080/?show_options=1
 */

add_action('init', function() {
    if (!isset($_GET['show_options'])) {
        return;
    }

    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: text/html; charset=utf-8');

    echo "<h2>OOTB Plugin Options Status</h2>";

    $options = get_option('ootb_options');

    echo "<h3>Options value:</h3>";
    echo "<pre>";
    var_dump($options);
    echo "</pre>";

    echo "<h3>Helper::get_option() returns:</h3>";
    echo "<pre>";
    if (class_exists('\OOTB\Helper')) {
        var_dump(\OOTB\Helper::get_option());
    } else {
        echo "Helper class not available";
    }
    echo "</pre>";

    die();
}, 1);
