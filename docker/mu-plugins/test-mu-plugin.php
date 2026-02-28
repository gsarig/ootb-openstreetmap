<?php
/**
 * Test if mu-plugins are loading.
 * Only runs when ?mu_plugin_test=1 or MU_PLUGIN_TEST=1 env var is set.
 */

$mu_plugin_test_enabled = ( isset( $_GET['mu_plugin_test'] ) && '1' === $_GET['mu_plugin_test'] )
	|| ( '1' === getenv( 'MU_PLUGIN_TEST' ) );

if ( $mu_plugin_test_enabled ) {
	error_log( '=== MU-PLUGIN TEST: This file is loading! ===' );
	add_action( 'admin_notices', function () {
		echo '<div class="notice notice-warning"><p><strong>MU-PLUGIN TEST:</strong> MU-plugins are working!</p></div>';
	} );
	add_action( 'wp_footer', function () {
		echo '<!-- MU-PLUGIN TEST: This is visible in frontend source -->';
	} );
}
