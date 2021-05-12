<?php

declare(strict_types = 1);

/**
 * @covers \user_switching::authenticate_old_user
 */
class TestAuthentication extends User_Switching_Test {

	public function testValidCookiePassesAuthentication() : void {
		$expiry = time() + 172800;

		$auth_cookie = wp_generate_auth_cookie( self::$testers['editor']->ID, $expiry, 'auth' );
		$_COOKIE[ USER_SWITCHING_COOKIE ] = json_encode( array( $auth_cookie ) );
		self::assertTrue( user_switching::authenticate_old_user( self::$testers['editor'] ) );
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['admin'] ) );
	}

	public function testExpiredCookieDoesNotPassAuthentication() : void {
		$auth_cookie = wp_generate_auth_cookie( self::$testers['editor']->ID, time() - 1000, 'auth' );
		$_COOKIE[ USER_SWITCHING_COOKIE ] = json_encode( array( $auth_cookie ) );
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['editor'] ) );
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['admin'] ) );
	}

	public function testValidCookieWithIncorrectSchemeDoesNotPassAuthentication() : void {
		$expiry = time() + 172800;

		$logged_in_cookie = wp_generate_auth_cookie( self::$testers['editor']->ID, $expiry, 'logged_in' );
		$_COOKIE[ USER_SWITCHING_COOKIE ] = json_encode( array( $logged_in_cookie ) );
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['editor'] ) );
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['admin'] ) );
		$logged_in_cookie = wp_generate_auth_cookie( self::$testers['editor']->ID, $expiry, 'secure_auth' );
		$_COOKIE[ USER_SWITCHING_COOKIE ] = json_encode( array( $logged_in_cookie ) );
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['editor'] ) );
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['admin'] ) );
	}

	public function testMalformedCookieDoesNotPassAuthentication() : void {
		$_COOKIE[ USER_SWITCHING_COOKIE ] = 'hello';
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['editor'] ) );
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['admin'] ) );
	}

	/**
	 * @testdox A non-JSON encoded cookie does not pass authentication
	 */
	public function testANonJsonEncodedCookieDoesNotPassAuthentication() : void {
		$expiry = time() + 172800;

		$auth_cookie = wp_generate_auth_cookie( self::$testers['editor']->ID, $expiry, 'auth' );
		$_COOKIE[ USER_SWITCHING_COOKIE ] = $auth_cookie;
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['editor'] ) );
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['admin'] ) );
	}

	public function testNoCookieDoesNotPassAuthentication() : void {
		unset( $_COOKIE[ USER_SWITCHING_COOKIE ] );
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['editor'] ) );
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['admin'] ) );
	}

}
