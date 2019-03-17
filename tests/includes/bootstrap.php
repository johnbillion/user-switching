<?php

$_root_dir = getcwd();

require_once $_root_dir . '/vendor/autoload.php';

$_env_dir = dirname( dirname( __DIR__ ) );

if ( is_readable( $_env_dir . '/.env' ) ) {
	$dotenv = Dotenv\Dotenv::create( $_env_dir );
	$dotenv->load();
}

$_tests_dir = getenv( 'WP_PHPUNIT__DIR' );

require_once $_tests_dir . '/includes/functions.php';

tests_add_filter( 'muplugins_loaded', function() use ( $_root_dir ) {
	require_once $_root_dir . '/user-switching.php';
} );

require $_tests_dir . '/includes/bootstrap.php';

require dirname( dirname( __FILE__ ) ) . '/user-switching-test.php';
