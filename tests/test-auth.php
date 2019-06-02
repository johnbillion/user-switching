<?php

class TestAuthentication extends User_Switching_Test {

	function testOldUserCookieAuthentication() {
		$expiry = time() + 172800;

		// A valid authentication cookie should pass authentication:
		$auth_cookie = wp_generate_auth_cookie( self::$testers['editor']->ID, $expiry, 'auth' );
		$_COOKIE[ USER_SWITCHING_COOKIE ] = json_encode( array( $auth_cookie ) );
		$this->assertTrue( user_switching::authenticate_old_user( self::$testers['editor'] ) );
		$this->assertFalse( user_switching::authenticate_old_user( self::$testers['admin'] ) );

		// An expired but otherwise valid authentication cookie should not pass authentication:
		$auth_cookie = wp_generate_auth_cookie( self::$testers['editor']->ID, time() - 1000, 'auth' );
		$_COOKIE[ USER_SWITCHING_COOKIE ] = json_encode( array( $auth_cookie ) );
		$this->assertFalse( user_switching::authenticate_old_user( self::$testers['editor'] ) );
		$this->assertFalse( user_switching::authenticate_old_user( self::$testers['admin'] ) );

		// A valid authentication cookie with the incorrect scheme should not pass authentication:
		$logged_in_cookie = wp_generate_auth_cookie( self::$testers['editor']->ID, $expiry, 'logged_in' );
		$_COOKIE[ USER_SWITCHING_COOKIE ] = json_encode( array( $logged_in_cookie ) );
		$this->assertFalse( user_switching::authenticate_old_user( self::$testers['editor'] ) );
		$this->assertFalse( user_switching::authenticate_old_user( self::$testers['admin'] ) );
		$logged_in_cookie = wp_generate_auth_cookie( self::$testers['editor']->ID, $expiry, 'secure_auth' );
		$_COOKIE[ USER_SWITCHING_COOKIE ] = json_encode( array( $logged_in_cookie ) );
		$this->assertFalse( user_switching::authenticate_old_user( self::$testers['editor'] ) );
		$this->assertFalse( user_switching::authenticate_old_user( self::$testers['admin'] ) );

		// A malformed cookie should not pass authentication and not trigger any PHP errors:
		$_COOKIE[ USER_SWITCHING_COOKIE ] = 'hello';
		$this->assertFalse( user_switching::authenticate_old_user( self::$testers['editor'] ) );
		$this->assertFalse( user_switching::authenticate_old_user( self::$testers['admin'] ) );

		// A non-JSON-encoded cookie should not pass authentication and not trigger any PHP errors:
		$auth_cookie = wp_generate_auth_cookie( self::$testers['editor']->ID, $expiry, 'auth' );
		$_COOKIE[ USER_SWITCHING_COOKIE ] = $auth_cookie;
		$this->assertFalse( user_switching::authenticate_old_user( self::$testers['editor'] ) );
		$this->assertFalse( user_switching::authenticate_old_user( self::$testers['admin'] ) );

		// No cookie should not pass authentication and not trigger any PHP errors:
		unset( $_COOKIE[ USER_SWITCHING_COOKIE ] );
		$this->assertFalse( user_switching::authenticate_old_user( self::$testers['editor'] ) );
		$this->assertFalse( user_switching::authenticate_old_user( self::$testers['admin'] ) );

	}

}
