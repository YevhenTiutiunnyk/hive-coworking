<?php
/**
 * Bootstrap for integration tests. Runs inside the wp-env tests container.
 *
 * @package Hive\Core
 */

$hive_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( false === $hive_tests_dir ) {
	$hive_tests_dir = '/wordpress-phpunit';
}

// The constant name is defined by the WordPress test suite.
define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', dirname( __DIR__, 4 ) . '/vendor/yoast/phpunit-polyfills' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedConstantFound

require_once $hive_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function (): void {
		require dirname( __DIR__, 2 ) . '/hive-core.php';
	}
);

require $hive_tests_dir . '/includes/bootstrap.php';
require __DIR__ . '/TestCase.php';

// The test database is installed fresh, so run activation once for the whole suite.
\Hive\Core\Activation::activate();
