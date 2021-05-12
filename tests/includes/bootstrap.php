<?php

$_root_dir = getcwd();

$_tests_dir = getenv( 'WP_PHPUNIT__DIR' );

require_once $_tests_dir . '/includes/functions.php';

tests_add_filter( 'muplugins_loaded', function() use ( $_root_dir ) : void {
	require_once $_root_dir . '/user-switching.php';
} );

require $_tests_dir . '/includes/bootstrap.php';

require dirname( dirname( __FILE__ ) ) . '/user-switching-test.php';
