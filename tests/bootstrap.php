<?php declare(strict_types = 1);

// Load the Composer autoloader first
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Use WP_TESTS_DIR environment variable if set, otherwise use wp-phpunit
if ( getenv( 'WP_TESTS_DIR' ) ) {
	$_tests_dir = rtrim( getenv( 'WP_TESTS_DIR' ), '/' ) . '/';
} else {
	// Default path using wp-phpunit package
	$_tests_dir = dirname( __DIR__ ) . '/vendor/wp-phpunit/wp-phpunit/';
}

// Get access to tests_add_filter() function
require_once $_tests_dir . 'includes/functions.php';

// Manually load the plugin
tests_add_filter( 'muplugins_loaded', fn() => require_once dirname( __DIR__ ) . '/user-switching.php' );

// Start up the WP testing environment
require_once $_tests_dir . 'includes/bootstrap.php';
