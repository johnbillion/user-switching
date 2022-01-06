<?php

declare(strict_types = 1);

namespace UserSwitching\Tests;

use user_switching;

/**
 * @covers \user_switching::authenticate_old_user
 */
class Authentication extends Test {

	public function testValidCookiePassesAuthentication() {
		$expiry = time() + 172800;

		$auth_cookie = wp_generate_auth_cookie( self::$testers['editor']->ID, $expiry, 'auth' );
		$_COOKIE[ USER_SWITCHING_COOKIE ] = json_encode( array( $auth_cookie ) );
		self::assertTrue( user_switching::authenticate_old_user( self::$testers['editor'] ) );
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['admin'] ) );
	}

	public function testExpiredCookieDoesNotPassAuthentication() {
		$auth_cookie = wp_generate_auth_cookie( self::$testers['editor']->ID, time() - 1000, 'auth' );
		$_COOKIE[ USER_SWITCHING_COOKIE ] = json_encode( array( $auth_cookie ) );
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['editor'] ) );
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['admin'] ) );
	}

	public function testValidCookieWithIncorrectSchemeDoesNotPassAuthentication() {
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

	public function testMalformedCookieDoesNotPassAuthentication() {
		$_COOKIE[ USER_SWITCHING_COOKIE ] = 'hello';
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['editor'] ) );
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['admin'] ) );
	}

	/**
	 * @testdox A non-JSON encoded cookie does not pass authentication
	 */
	public function testANonJsonEncodedCookieDoesNotPassAuthentication() {
		$expiry = time() + 172800;

		$auth_cookie = wp_generate_auth_cookie( self::$testers['editor']->ID, $expiry, 'auth' );
		$_COOKIE[ USER_SWITCHING_COOKIE ] = $auth_cookie;
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['editor'] ) );
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['admin'] ) );
	}

	public function testNoCookieDoesNotPassAuthentication() {
		unset( $_COOKIE[ USER_SWITCHING_COOKIE ] );
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['editor'] ) );
		self::assertFalse( user_switching::authenticate_old_user( self::$testers['admin'] ) );
	}

}
