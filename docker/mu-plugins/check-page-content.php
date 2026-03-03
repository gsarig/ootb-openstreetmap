<?php
/**
 * Check test page content
 * Access via http://localhost:8080/?check_page=1
 */

add_action('init', function() {
    if (!isset($_GET['check_page'])) {
        return;
    }

    // Prevent output buffering
    while (ob_get_level()) {
        ob_end_clean();
    }

    header('Content-Type: text/html; charset=utf-8');

    echo "<h2>Test Page Content Check</h2>";

    // Get the test-map page
    $page = get_page_by_path('test-map', OBJECT, 'page');

    if (!$page) {
        echo "<p>✗ Page 'test-map' not found</p>";

        // List all pages
        $all_pages = get_pages();
        echo "<h3>All pages:</h3><ul>";
        foreach ($all_pages as $p) {
            echo "<li>" . esc_html($p->post_title) . " (slug: " . esc_html($p->post_name) . ")</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>✓ Page found: " . esc_html($page->post_title) . "</p>";
        echo "<p>Status: " . esc_html($page->post_status) . "</p>";
        echo "<p>URL: <a href='" . get_permalink($page->ID) . "'>" . get_permalink($page->ID) . "</a></p>";

        echo "<h3>Page Content (raw):</h3>";
        echo "<pre>" . esc_html($page->post_content) . "</pre>";

        echo "<h3>Has OOTB block:</h3>";
        if (strpos($page->post_content, 'wp:ootb/openstreetmap') !== false) {
            echo "<p>✓ YES - Contains OOTB block comment</p>";
        } else {
            echo "<p>✗ NO - Does not contain OOTB block</p>";
        }

        echo "<h3>Rendered Content:</h3>";
        echo "<div style='border: 2px solid blue; padding: 10px;'>";
        echo apply_filters('the_content', $page->post_content);
        echo "</div>";
    }

    die();
}, 999);
