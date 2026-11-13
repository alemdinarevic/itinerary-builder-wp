<?php
/**
 * Loads the WordPress test suite with this plugin active.
 *
 * Run inside wp-env with `yarn test:php`, which provides the WordPress test
 * library at WP_TESTS_DIR. The blocks are registered from build/, so run
 * `yarn build` first.
 *
 * @package TripItinerary
 */

$trip_itinerary_tests_dir = getenv( 'WP_TESTS_DIR' ) ? getenv( 'WP_TESTS_DIR' ) : '/wordpress-phpunit';
$trip_itinerary_root      = dirname( __DIR__, 2 );

if ( ! file_exists( $trip_itinerary_root . '/build/blocks-manifest.php' ) ) {
	echo 'Build the blocks first: yarn build' . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit( 1 );
}

define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $trip_itinerary_root . '/vendor/yoast/phpunit-polyfills' );

require_once $trip_itinerary_tests_dir . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function () use ( $trip_itinerary_root ) {
		require $trip_itinerary_root . '/trip-itinerary.php';
	}
);

require $trip_itinerary_tests_dir . '/includes/bootstrap.php';
