<?php declare(strict_types = 1);

namespace UserSwitching\Tests;

use user_switching;

final class SwitchingTest extends Test {
	/**
	 * @var int|false
	 */
	private $test_switching_user_id;

	/**
	 * @var int|false
	 */
	private $test_switching_old_user_id;

	private int $test_switching_auth_cookie_user_id;

	private bool $test_switching_auth_cookie_remember;

	public function _before(): void {
		parent::_before();

		add_action( 'switch_to_user',         array( $this, '_action_switch_user' ), 10, 2 );
		add_action( 'switch_back_user',       array( $this, '_action_switch_user' ), 10, 2 );
		add_action( 'switch_off_user',        array( $this, '_action_switch_off' ), 10 );
		add_filter( 'auth_cookie_expiration', array( $this, '_filter_auth_cookie_expiration' ), 10, 3 );
	}

	/**
	 * @covers \switch_to_user
	 */
	public function testSwitchUserAndBack(): void {
		$admin = self::$testers['admin'];

		wp_set_current_user( $admin->ID );


		// Switch user
		$user = switch_to_user( self::$users['author']->ID, true );

		// Check that we've switched
		self::assertInstanceOf( 'WP_User', $user );
		self::assertSame( self::$users['author']->ID, $user->ID );
		self::assertSame( self::$users['author']->ID, get_current_user_id() );

		// Check the `switch_*` actions were fired
		self::assertSame( 1, did_action( 'switch_to_user' ) );
		self::assertSame( 0, did_action( 'switch_back_user' ) );
		self::assertSame( 0, did_action( 'switch_off_user' ) );

		// Check the `switch_*` actions' parameters
		self::assertSame( self::$users['author']->ID, $this->test_switching_user_id );
		self::assertSame( $admin->ID,                 $this->test_switching_old_user_id );

		// Check the auth cookie behaviour
		self::assertSame( self::$users['author']->ID, $this->test_switching_auth_cookie_user_id );
		self::assertTrue( $this->test_switching_auth_cookie_remember );



		// Switch user again
		$user = switch_to_user( self::$users['editor']->ID, true );

		// Check that we've switched
		self::assertInstanceOf( 'WP_User', $user );
		self::assertSame( self::$users['editor']->ID, $user->ID );
		self::assertSame( self::$users['editor']->ID, get_current_user_id() );

		// Check the `switch_*` actions were fired
		self::assertSame( 2, did_action( 'switch_to_user' ) );
		self::assertSame( 0, did_action( 'switch_back_user' ) );
		self::assertSame( 0, did_action( 'switch_off_user' ) );

		// Check the `switch_*` actions' parameters
		self::assertSame( self::$users['editor']->ID, $this->test_switching_user_id );
		self::assertSame( self::$users['author']->ID, $this->test_switching_old_user_id );

		// Check the auth cookie behaviour
		self::assertSame( self::$users['editor']->ID, $this->test_switching_auth_cookie_user_id );
		self::assertTrue( $this->test_switching_auth_cookie_remember );



		// Switch back
		$user = switch_to_user( self::$users['author']->ID, false, false );

		// Check that we've switched
		self::assertInstanceOf( 'WP_User', $user );
		self::assertSame( self::$users['author']->ID, $user->ID );
		self::assertSame( self::$users['author']->ID, get_current_user_id() );

		// Check the `switch_*` actions were fired
		self::assertSame( 2, did_action( 'switch_to_user' ) );
		self::assertSame( 1, did_action( 'switch_back_user' ) );
		self::assertSame( 0, did_action( 'switch_off_user' ) );

		// Check the `switch_*` actions' parameters
		self::assertSame( self::$users['author']->ID, $this->test_switching_user_id );
		self::assertSame( self::$users['editor']->ID, $this->test_switching_old_user_id );

		// Check the auth cookie behaviour
		self::assertSame( self::$users['author']->ID, $this->test_switching_auth_cookie_user_id );
		self::assertFalse( $this->test_switching_auth_cookie_remember );



		// Switch back again
		$user = switch_to_user( $admin->ID, false, false );

		// Check that we've switched
		self::assertInstanceOf( 'WP_User', $user );
		self::assertSame( $admin->ID, $user->ID );
		self::assertSame( $admin->ID, get_current_user_id() );

		// Check the `switch_*` actions were fired
		self::assertSame( 2, did_action( 'switch_to_user' ) );
		self::assertSame( 2, did_action( 'switch_back_user' ) );
		self::assertSame( 0, did_action( 'switch_off_user' ) );

		// Check the `switch_*` actions' parameters
		self::assertSame( $admin->ID,                 $this->test_switching_user_id );
		self::assertSame( self::$users['author']->ID, $this->test_switching_old_user_id );

		// Check the auth cookie behaviour
		self::assertSame( $admin->ID, $this->test_switching_auth_cookie_user_id );
		self::assertFalse( $this->test_switching_auth_cookie_remember );
	}

	/**
	 * @covers \switch_to_user
	 * @covers \switch_off_user
	 */
	public function testSwitchOffAndBack(): void {
		$admin = self::$testers['admin'];

		wp_set_current_user( $admin->ID );

		// Switch off
		$user = switch_off_user();

		// Check that we've switched off
		self::assertTrue( $user );
		self::assertFalse( is_user_logged_in() );
		self::assertSame( 0, get_current_user_id() );

		// Check the `switch_*` actions were fired
		self::assertSame( 0, did_action( 'switch_to_user' ) );
		self::assertSame( 0, did_action( 'switch_back_user' ) );
		self::assertSame( 1, did_action( 'switch_off_user' ) );

		// Check the `switch_*` actions' parameters
		self::assertFalse( $this->test_switching_user_id );
		self::assertSame( $admin->ID, $this->test_switching_old_user_id );

		// Check the auth cookie behaviour
		self::assertSame( 1, did_action( 'clear_auth_cookie' ) );

		// Switch back
		$user = switch_to_user( $admin->ID, false, false );

		// Check that we've switched back
		self::assertInstanceOf( 'WP_User', $user );
		self::assertTrue( is_user_logged_in() );
		self::assertSame( $admin->ID, $user->ID );
		self::assertSame( $admin->ID, get_current_user_id() );

		// Check the `switch_*` actions were fired
		self::assertSame( 0, did_action( 'switch_to_user' ) );
		self::assertSame( 1, did_action( 'switch_back_user' ) );
		self::assertSame( 1, did_action( 'switch_off_user' ) );

		// Check the `switch_*` actions' parameters
		self::assertSame( $admin->ID, $this->test_switching_user_id );
		self::assertFalse( $this->test_switching_old_user_id );

		// Check the auth cookie behaviour
		self::assertSame( $admin->ID, $this->test_switching_auth_cookie_user_id );
		self::assertFalse( $this->test_switching_auth_cookie_remember );
	}

	/**
	 * @covers \switch_to_user
	 */
	public function testSwitchToNonExistentUserFails(): void {
		// Switch user
		$user = switch_to_user( 0 );

		self::assertFalse( $user );
	}

	/**
	 * @covers \user_switching_clear_olduser_cookie
	 */
	public function testOldUserIsClearedWhenLoggingOut(): void {
		$admin = self::$testers['admin'];

		wp_set_current_user( $admin->ID );

		// Switch user
		switch_to_user( self::$users['author']->ID, true );

		// Check that we are considered switched
		$switched = current_user_switched();
		self::assertInstanceOf( 'WP_User', $switched );
		self::assertSame( $admin->ID, $switched->ID );

		// Log out
		wp_logout();

		// Check that we are no longer considered switched
		$switched = current_user_switched();
		self::assertFalse( $switched );
	}

	/**
	 * @covers \user_switching_clear_olduser_cookie
	 */
	public function testOldUserIsClearedWhenLoggingIn(): void {
		$admin = self::$testers['admin'];

		wp_set_current_user( $admin->ID );

		// Switch user
		switch_to_user( self::$users['author']->ID, true );

		// Check that we are considered switched
		$switched = current_user_switched();
		self::assertInstanceOf( 'WP_User', $switched );
		self::assertSame( $admin->ID, $switched->ID );

		// Clear the current user
		wp_set_current_user( 0 );

		// Log in
		$signed_on = wp_signon( array(
			'user_login' => self::$users['author']->user_login,
			'user_password' => 'password',
		) );
		self::assertNotWPError( $signed_on );

		// Check that we are no longer considered switched
		$switched = current_user_switched();
		self::assertFalse( $switched );
	}

	/**
	 * @covers \user_switching_clear_olduser_cookie
	 * @see https://github.com/johnbillion/user-switching/issues/142
	 */
	public function testBrokenWPLoginActionValueIsIgnored(): void {
		$admin = self::$testers['admin'];

		wp_set_current_user( $admin->ID );

		// Switch user
		switch_to_user( self::$users['author']->ID, true );

		// Check that we are considered switched
		$switched = current_user_switched();
		self::assertInstanceOf( 'WP_User', $switched );
		self::assertSame( $admin->ID, $switched->ID );

		// ...
		do_action( 'wp_login', null );

		// Check that we are no longer considered switched
		$switched = current_user_switched();
		self::assertFalse( $switched );
	}

	/**
	 * @covers \user_switching::current_url
	 */
	public function testCurrentUrl(): void {
		$url = add_query_arg( 'foo', 'bar', home_url( 'baz' ) );
		$this->go_to( $url );
		self::assertSame( user_switching::current_url(), $url );
	}

	/**
	 * @param int|false $old_user_id
	 */
	public function _action_switch_user( int $user_id, $old_user_id ): void {
		$this->test_switching_user_id = $user_id;
		$this->test_switching_old_user_id = $old_user_id;
	}

	public function _action_switch_off( int $old_user_id ): void {
		$this->test_switching_user_id = false;
		$this->test_switching_old_user_id = $old_user_id;
	}

	public function _filter_auth_cookie_expiration( int $length, int $user_id, bool $remember ): int {
		$this->test_switching_auth_cookie_user_id = $user_id;
		$this->test_switching_auth_cookie_remember = $remember;

		return $length;
	}
}
