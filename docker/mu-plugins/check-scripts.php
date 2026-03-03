<?php
/**
 * Check enqueued scripts
 * Access via http://localhost:8080/test-map/?check_scripts=1
 */

add_action('wp_footer', function() {
    if (!isset($_GET['check_scripts'])) {
        return;
    }

    global $wp_scripts, $wp_styles;

    echo "<div style='position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; z-index: 99999; overflow: auto; padding: 20px;'>";
    echo "<h2>Enqueued Scripts & Styles Debug</h2>";

    echo "<h3>Scripts:</h3><ul>";
    if (!empty($wp_scripts->queue)) {
        foreach ($wp_scripts->queue as $handle) {
            $script = $wp_scripts->registered[$handle] ?? null;
            if ($script) {
                echo "<li><strong>" . esc_html($handle) . "</strong>: " . esc_html($script->src) . "</li>";
            }
        }
    } else {
        echo "<li>No scripts in queue</li>";
    }
    echo "</ul>";

    echo "<h3>Styles:</h3><ul>";
    if (!empty($wp_styles->queue)) {
        foreach ($wp_styles->queue as $handle) {
            $style = $wp_styles->registered[$handle] ?? null;
            if ($style) {
                echo "<li><strong>" . esc_html($handle) . "</strong>: " . esc_html($style->src) . "</li>";
            }
        }
    } else {
        echo "<li>No styles in queue</li>";
    }
    echo "</ul>";

    echo "<h3>OOTB-related assets:</h3><ul>";

    // Check registered scripts
    foreach ($wp_scripts->registered as $handle => $script) {
        if (strpos($handle, 'ootb') !== false || strpos($handle, 'leaflet') !== false) {
            $queued = in_array($handle, $wp_scripts->queue) ? '✓ QUEUED' : '✗ not queued';
            echo "<li><strong>" . esc_html($handle) . "</strong> ($queued): " . esc_html($script->src) . "</li>";
        }
    }

    echo "</ul>";
    echo "</div>";
}, 999);
