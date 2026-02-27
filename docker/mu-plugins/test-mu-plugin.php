<?php
/**
 * Test if mu-plugins are loading
 * Visit any page and check logs
 */

error_log('=== MU-PLUGIN TEST: This file is loading! ===');

add_action('admin_notices', function() {
    echo '<div class="notice notice-warning"><p><strong>MU-PLUGIN TEST:</strong> MU-plugins are working!</p></div>';
});

add_action('wp_footer', function() {
    echo '<!-- MU-PLUGIN TEST: This is visible in frontend source -->';
});
