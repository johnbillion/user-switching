<?php

add_filter(
	'user_row_actions',
	function ( array $actions ) {
		unset( $actions['resetpassword'] );
		return $actions;
	}
);
