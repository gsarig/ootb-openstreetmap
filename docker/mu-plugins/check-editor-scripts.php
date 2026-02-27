<?php
/**
 * Check editor scripts in admin
 * Access by editing the test-map page and adding ?check_editor=1 to URL
 */

add_action('admin_footer', function() {
    if (!isset($_GET['check_editor'])) {
        return;
    }

    global $wp_scripts;

    ?>
    <script>
    console.log('=== OOTB Debug ===');
    console.log('wp.blocks.getBlockTypes():', wp.blocks.getBlockTypes());
    console.log('OOTB block:', wp.blocks.getBlockType('ootb/openstreetmap'));
    console.log('ootbGlobal:', typeof ootbGlobal !== 'undefined' ? ootbGlobal : 'NOT DEFINED');
    </script>
    <div style="position: fixed; bottom: 0; left: 0; right: 0; background: yellow; padding: 10px; z-index: 999999;">
        <strong>Editor Scripts Debug:</strong>
        <ul>
            <?php
            foreach ($wp_scripts->queue as $handle) {
                if (strpos($handle, 'ootb') !== false || strpos($handle, 'block-editor') !== false) {
                    $script = $wp_scripts->registered[$handle] ?? null;
                    if ($script) {
                        echo '<li>' . esc_html($handle) . ': ' . esc_html($script->src) . '</li>';
                    }
                }
            }
            ?>
        </ul>
        Check browser console for block registration info.
    </div>
    <?php
}, 999);
