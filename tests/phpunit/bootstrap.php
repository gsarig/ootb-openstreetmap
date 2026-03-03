<?php
$_tests_dir = getenv( 'WP_TESTS_DIR' ) ?: '/tmp/wordpress-tests-lib';

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	echo "Could not find {$_tests_dir}/includes/functions.php." . PHP_EOL;
	echo "Run: bash bin/install-wp-tests.sh wordpress_test wordpress wordpress db latest" . PHP_EOL;
	exit( 1 );
}

require_once dirname( __DIR__, 2 ) . '/vendor/autoload.php';

// Load functions.php first — this is what defines tests_add_filter()
require_once $_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function () {
		require_once dirname( __DIR__, 2 ) . '/ootb-openstreetmap.php';
	}
);

// Now load the full WP test bootstrap (which calls the filter above)
require $_tests_dir . '/includes/bootstrap.php';
