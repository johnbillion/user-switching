<?php
/**
 * Plugin Name: User Switching Playground Helper
 * Description: A helper plugin for running User Switching in the WordPress playground
 */

add_filter(
	'user_row_actions',
	function ( array $actions ) {
		unset( $actions['resetpassword'] );
		return $actions;
	}
);
