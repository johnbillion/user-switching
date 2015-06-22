<?php

class User_Switching_Test_Auth extends User_Switching_Test {

	function testOldUserCookieAuthentication() {

		$admin  = $this->testers['admin'];
		$editor = $this->testers['editor'];
		$expiry = time() + 172800;

		// A valid authentication cookie should pass authentication:
		$auth_cookie = wp_generate_auth_cookie( $editor->ID, $expiry, 'auth' );
		$_COOKIE[ USER_SWITCHING_COOKIE ] = json_encode( array( $auth_cookie ) );
		$this->assertTrue( user_switching::authenticate_old_user( $editor ) );
		$this->assertFalse( user_switching::authenticate_old_user( $admin ) );

		// An expired but otherwise valid authentication cookie should not pass authentication:
		$auth_cookie = wp_generate_auth_cookie( $editor->ID, time() - 1000, 'auth' );
		$_COOKIE[ USER_SWITCHING_COOKIE ] = json_encode( array( $auth_cookie ) );
		$this->assertFalse( user_switching::authenticate_old_user( $editor ) );
		$this->assertFalse( user_switching::authenticate_old_user( $admin ) );

		// A valid authentication cookie with the incorrect scheme should not pass authentication:
		$logged_in_cookie = wp_generate_auth_cookie( $editor->ID, $expiry, 'logged_in' );
		$_COOKIE[ USER_SWITCHING_COOKIE ] = json_encode( array( $logged_in_cookie ) );
		$this->assertFalse( user_switching::authenticate_old_user( $editor ) );
		$this->assertFalse( user_switching::authenticate_old_user( $admin ) );
		$logged_in_cookie = wp_generate_auth_cookie( $editor->ID, $expiry, 'secure_auth' );
		$_COOKIE[ USER_SWITCHING_COOKIE ] = json_encode( array( $logged_in_cookie ) );
		$this->assertFalse( user_switching::authenticate_old_user( $editor ) );
		$this->assertFalse( user_switching::authenticate_old_user( $admin ) );

		// A malformed cookie should not pass authentication and not trigger any PHP errors:
		$_COOKIE[ USER_SWITCHING_COOKIE ] = 'hello';
		$this->assertFalse( user_switching::authenticate_old_user( $editor ) );
		$this->assertFalse( user_switching::authenticate_old_user( $admin ) );

		// A non-JSON-encoded cookie should not pass authentication and not trigger any PHP errors:
		$auth_cookie = wp_generate_auth_cookie( $editor->ID, $expiry, 'auth' );
		$_COOKIE[ USER_SWITCHING_COOKIE ] = $auth_cookie;
		$this->assertFalse( user_switching::authenticate_old_user( $editor ) );
		$this->assertFalse( user_switching::authenticate_old_user( $admin ) );

		// No cookie should not pass authentication and not trigger any PHP errors:
		unset( $_COOKIE[ USER_SWITCHING_COOKIE ] );
		$this->assertFalse( user_switching::authenticate_old_user( $editor ) );
		$this->assertFalse( user_switching::authenticate_old_user( $admin ) );

	}

}
