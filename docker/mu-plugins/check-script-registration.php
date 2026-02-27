<?php
/**
 * Check if editor script is properly registered
 * Access by editing the test-map page and adding ?check_reg=1 to URL
 */

add_action('admin_footer', function() {
    if (!isset($_GET['check_reg'])) {
        return;
    }

    global $wp_scripts;

    ?>
    <div style="position: fixed; bottom: 0; left: 0; right: 0; background: #ffeb3b; padding: 10px; z-index: 999999; max-height: 400px; overflow: auto; font-size: 12px;">
        <strong>Script Registration Debug:</strong>
        <ul style="margin: 0; padding-left: 20px;">
            <?php
            // Check if script is registered
            $handle = 'ootb-openstreetmap-editor-script';
            if (isset($wp_scripts->registered[$handle])) {
                $script = $wp_scripts->registered[$handle];
                echo '<li><strong style="color:green;">' . esc_html($handle) . ' IS REGISTERED</strong><br>';
                echo 'src: ' . esc_html($script->src) . '<br>';
                echo 'deps: ' . esc_html(implode(', ', $script->deps)) . '<br>';
                echo 'ver: ' . esc_html($script->ver) . '<br>';
                if (!empty($script->extra)) {
                    echo 'Has inline scripts/data: <pre style="background: white; padding: 5px;">' . esc_html(print_r($script->extra, true)) . '</pre>';
                } else {
                    echo '<strong style="color: red;">NO INLINE SCRIPTS ATTACHED!</strong>';
                }
                echo '</li>';
            } else {
                echo '<li><strong style="color:red;">❌ ' . esc_html($handle) . ' NOT REGISTERED!</strong></li>';

                echo '<li>All registered scripts with "ootb" in name:<ul>';
                foreach ($wp_scripts->registered as $h => $s) {
                    if (strpos($h, 'ootb') !== false) {
                        echo '<li>' . esc_html($h) . ' => ' . esc_html($s->src) . '</li>';
                    }
                }
                echo '</ul></li>';
            }
            ?>
        </ul>
    </div>
    <?php
}, 999);
