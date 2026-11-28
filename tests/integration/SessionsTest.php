<?php declare(strict_types = 1);

namespace UserSwitching\Tests;

use user_switching;
use WP_Session_Tokens;

final class SessionsTest extends Test {
	/**
	 * @covers \switch_to_user
	 */
	public function testExtraSessionsAreNotCreatedForUsersWhenSwitching(): void {
		$admin = self::$testers['admin'];

		// Set up the admin session manager with a session
		$admin_manager = WP_Session_Tokens::get_instance( $admin->ID );
		$admin_token = $admin_manager->create( time() + DAY_IN_SECONDS );
		$admin_before = $admin_manager->get_all();

		// Set up the author session manager, but with no session
		$author_manager = WP_Session_Tokens::get_instance( self::$users['author']->ID );
		$author_before = $author_manager->get_all();

		// Set up the admin user state
		wp_set_current_user( $admin->ID );
		wp_set_auth_cookie( $admin->ID, false, '', $admin_token );

		// Verify the initial state
		self::assertCount( 1, $admin_before );
		self::assertCount( 0, $author_before );

		// Switch user
		$user = switch_to_user( self::$users['author']->ID );

		// Verify no new sessions were created for the old user
		self::assertCount( 1, $admin_manager->get_all() );

		// Verify only one new session was created for the new user
		self::assertCount( 1, $author_manager->get_all() );
	}

	/**
	 * @covers \switch_off_user
	 */
	public function testExtraSessionsAreNotCreatedForUserWhenSwitchingOff(): void {
		$admin = self::$testers['admin'];

		// Set up the admin session manager with a session
		$admin_manager = WP_Session_Tokens::get_instance( $admin->ID );
		$admin_token = $admin_manager->create( time() + DAY_IN_SECONDS );
		$admin_before = $admin_manager->get_all();

		// Set up the admin user state
		wp_set_current_user( $admin->ID );
		wp_set_auth_cookie( $admin->ID, false, '', $admin_token );

		// Verify the initial state
		self::assertCount( 1, $admin_before );

		// Switch off
		$switched = switch_off_user();

		// Verify no new sessions were created for the old user
		self::assertCount( 1, $admin_manager->get_all() );
	}

	/**
	 * @covers \switch_to_user
	 * @covers \switch_off_user
	 */
	public function testPreviousSessionForUserIsReusedWhenSwitchingBack(): void {
		$admin = self::$testers['admin'];

		// Set up the admin session manager with a session
		$admin_manager = WP_Session_Tokens::get_instance( $admin->ID );
		$admin_token = $admin_manager->create( time() + DAY_IN_SECONDS );
		$admin_before = $admin_manager->get_all();

		// Set up the author session manager, but with no session
		$author_manager = WP_Session_Tokens::get_instance( self::$users['author']->ID );

		// Set up the admin user state
		wp_set_current_user( $admin->ID );
		wp_set_auth_cookie( $admin->ID, false, '', $admin_token );

		// Verify the initial state
		self::assertCount( 1, $admin_before );

		// Switch user
		$user = switch_to_user( self::$users['author']->ID );

		// Verify no new sessions were created for the old user
		self::assertCount( 1, $admin_manager->get_all() );

		// Switch back
		$user = switch_to_user( $admin->ID, false, false );

		// Verify no new sessions were created for the original user
		self::assertCount( 1, $admin_manager->get_all() );

		// Verify the session for the switched to user was destroyed
		self::assertCount( 0, $author_manager->get_all() );

		// Switch off
		$off = switch_off_user();

		// Verify no new sessions were created for the old user
		self::assertCount( 1, $admin_manager->get_all() );

		// Switch back on again
		$user = switch_to_user( $admin->ID, false, false );

		// Verify no new sessions were created for the original user
		self::assertCount( 1, $admin_manager->get_all() );
	}

	/**
	 * @covers \switch_to_user
	 */
	public function testExpiredSessionPreventsUserFromSwitchingBack(): void {
		$admin = self::$testers['admin'];

		// Set up the admin session manager with a session
		$admin_manager = WP_Session_Tokens::get_instance( $admin->ID );
		$admin_token = $admin_manager->create( time() + DAY_IN_SECONDS );
		$admin_before = $admin_manager->get_all();

		// Set up the author session manager, but with no session
		$author_manager = WP_Session_Tokens::get_instance( self::$users['author']->ID );
		$author_before = $author_manager->get_all();

		// Set up the admin user state
		wp_set_current_user( $admin->ID );
		wp_set_auth_cookie( $admin->ID, false, '', $admin_token );

		// Verify the initial state
		self::assertCount( 1, $admin_before );
		self::assertCount( 0, $author_before );

		// Switch user
		$user = switch_to_user( self::$users['author']->ID );

		// Verify no new sessions were created for the old user
		self::assertCount( 1, $admin_manager->get_all() );

		// Verify a session was created for the switched to user
		self::assertCount( 1, $author_manager->get_all() );

		// Invalidate the session that the user switched from, to mock its expiry while switched
		$existing = $admin_manager->get( $admin_token );
		$existing['expiration'] = time() - HOUR_IN_SECONDS;
		$admin_manager->update( $admin_token, $existing );

		// Verify the old session has been invalidated
		self::assertNull( $admin_manager->get( $admin_token ) );
		self::assertFalse( user_switching::get_old_user() );

		// Attempt to switch back
		$user = switch_to_user( $admin->ID, false, false );

		// Verify no new session was created for the original user
		self::assertCount( 0, $admin_manager->get_all() );

		// Verify the session for the switched to user was destroyed
		self::assertCount( 0, $author_manager->get_all() );
	}

	/**
	 * @covers \user_switching::remember
	 * @covers \switch_to_user
	 */
	public function testSwitchedSessionRemainsValidWhenOriginatingSessionExpires(): void {
		$operator = self::$testers[ is_multisite() ? 'super' : 'admin' ];
		$author = self::$users['author'];
		$operator_sessions = WP_Session_Tokens::get_instance( $operator->ID );
		$author_sessions = WP_Session_Tokens::get_instance( $author->ID );
		$browser_expiries = [];
		add_action( 'set_logged_in_cookie', static function ( string $cookie, int $expire ) use ( &$browser_expiries ): void {
			$browser_expiries[] = $expire;
		}, 10, 2 );

		// Issue a remembered login with only a minute left on its cookie and server session.
		wp_set_current_user( $operator->ID );
		$one_minute = static fn (): int => MINUTE_IN_SECONDS;
		add_filter( 'auth_cookie_expiration', $one_minute );
		wp_set_auth_cookie( $operator->ID, true );
		remove_filter( 'auth_cookie_expiration', $one_minute );
		$operator_token = wp_get_session_token();
		self::assertGreaterThan( time(), $browser_expiries[0] );

		$switched_at = time();
		switch_to_user( $author->ID, user_switching::remember() );
		$switched_token = wp_get_session_token();
		self::assertSame( 0, $browser_expiries[1], 'A nearly expired remembered login should switch to a browser-session cookie.' );

		$operator_session = $operator_sessions->get( $operator_token );
		$switched_session = $author_sessions->get( $switched_token );
		self::assertIsArray( $operator_session );
		self::assertIsArray( $switched_session );
		self::assertGreaterThanOrEqual( $switched_at + 2 * DAY_IN_SECONDS, $switched_session['expiration'] );
		self::assertTrue( user_switching::authenticate_old_user( $operator ) );

		// Backdate the original session so WordPress expires it on its next read.
		$operator_session['expiration'] = time() - 1;
		$operator_sessions->update( $operator_token, $operator_session );

		self::assertFalse( $operator_sessions->verify( $operator_token ) );
		self::assertFalse( user_switching::authenticate_old_user( $operator ) );
		self::assertTrue( $author_sessions->verify( $switched_token ), 'The switched session should remain valid after its originating session expires.' );
		self::assertSame( $author->ID, wp_validate_auth_cookie( '', 'logged_in' ) );
	}

	/**
	 * @covers \switch_to_user
	 * @covers \switch_off_user
	 */
	public function testSessionTokensAreCorrectlyReusedWhenSwitching(): void {
		$admin = self::$testers['admin'];

		// Set up the admin session manager with a session
		$admin_manager = WP_Session_Tokens::get_instance( $admin->ID );
		$admin_token = $admin_manager->create( time() + DAY_IN_SECONDS );

		// Set up the author session manager, but with no session
		$author_manager = WP_Session_Tokens::get_instance( self::$users['author']->ID );

		// Set up the admin user state
		wp_set_current_user( $admin->ID );
		wp_set_auth_cookie( $admin->ID, false, '', $admin_token );

		// Switch user
		$user = switch_to_user( self::$users['author']->ID );
		$author_token = wp_get_session_token();
		$cookies = user_switching_get_auth_cookie();
		$cookie = end( $cookies );

		self::assertIsString( $cookie );

		$parts = wp_parse_auth_cookie( $cookie );

		self::assertIsArray( $parts );

		// Verify the original user session information is stored in the switch stack and against the new user session
		$author_session = $author_manager->get( $author_token );

		self::assertIsArray( $author_session );

		self::assertArrayHasKey( 'switched_from_id', $author_session );
		self::assertArrayHasKey( 'switched_from_session', $author_session );
		self::assertSame( $admin->ID, $author_session['switched_from_id'] );
		self::assertSame( $admin_token, $author_session['switched_from_session'] );
		self::assertSame( $admin_token, $parts['token'] );

		// Switch back
		$user = switch_to_user( $admin->ID, false, false );

		// Verify the original session token was reused
		self::assertCount( 1, $admin_manager->get_all() );
		self::assertNotNull( $admin_manager->get( $admin_token ) );

		// Verify the session for the switched to user was destroyed
		self::assertCount( 0, $author_manager->get_all() );
		self::assertNull( $author_manager->get( $author_token ) );

		// Switch off
		$off = switch_off_user();
		$cookies = user_switching_get_auth_cookie();
		$cookie = end( $cookies );

		self::assertIsString( $cookie );

		$parts = wp_parse_auth_cookie( $cookie );

		self::assertIsArray( $parts );

		// Verify the original user session information is stored in the switch stack
		self::assertSame( $admin_token, $parts['token'] );

		// Switch back on again
		$user = switch_to_user( $admin->ID, false, false );

		// Verify the original session token was reused
		self::assertCount( 1, $admin_manager->get_all() );
		self::assertNotNull( $admin_manager->get( $admin_token ) );
	}

	/**
	 * @covers \user_switching::action_wp_logout
	 */
	public function testOriginatingSessionIsDestroyedWhenSwitchedUserLogsOut(): void {
		$admin = self::$testers['admin'];
		$admin_manager = WP_Session_Tokens::get_instance( $admin->ID );
		$admin_token = $admin_manager->create( time() + DAY_IN_SECONDS );
		$admin_other_token = $admin_manager->create( time() + DAY_IN_SECONDS );
		$author_manager = WP_Session_Tokens::get_instance( self::$users['author']->ID );

		wp_set_current_user( $admin->ID );
		wp_set_auth_cookie( $admin->ID, false, '', $admin_token );

		switch_to_user( self::$users['author']->ID );
		$author_token = wp_get_session_token();

		self::assertNotNull( $admin_manager->get( $admin_token ) );

		wp_logout();

		self::assertNull( $author_manager->get( $author_token ) );
		self::assertNull( $admin_manager->get( $admin_token ), 'The session that the user switched from should be destroyed' );
		self::assertNotNull( $admin_manager->get( $admin_other_token ), 'Other sessions for the user should not be affected' );
	}

	/**
	 * @covers \user_switching::action_wp_logout
	 */
	public function testEverySessionInTheSwitchStackIsDestroyedWhenSwitchedUserLogsOut(): void {
		$admin = self::$testers['admin'];
		$admin_manager = WP_Session_Tokens::get_instance( $admin->ID );
		$admin_token = $admin_manager->create( time() + DAY_IN_SECONDS );
		$author_manager = WP_Session_Tokens::get_instance( self::$users['author']->ID );
		$editor_manager = WP_Session_Tokens::get_instance( self::$users['editor']->ID );

		wp_set_current_user( $admin->ID );
		wp_set_auth_cookie( $admin->ID, false, '', $admin_token );

		switch_to_user( self::$users['author']->ID );
		$author_token = wp_get_session_token();

		switch_to_user( self::$users['editor']->ID );
		$editor_token = wp_get_session_token();

		self::assertNotNull( $admin_manager->get( $admin_token ) );
		self::assertNotNull( $author_manager->get( $author_token ) );

		wp_logout();

		self::assertNull( $editor_manager->get( $editor_token ) );
		self::assertNull( $author_manager->get( $author_token ) );
		self::assertNull( $admin_manager->get( $admin_token ) );
	}

	/**
	 * @covers \user_switching::action_wp_logout
	 */
	public function testSessionsForOldUserCookiesWithoutASessionTokenAreDestroyedWhenSwitchedUserLogsOut(): void {
		$admin = self::$testers['admin'];
		$admin_manager = WP_Session_Tokens::get_instance( $admin->ID );

		// With no current session, each old user cookie gets a new session
		wp_set_current_user( $admin->ID );
		switch_to_user( self::$users['author']->ID );

		self::assertNotEmpty( $admin_manager->get_all() );

		wp_logout();

		self::assertSame( [], $admin_manager->get_all() );
	}

	/**
	 * @covers \user_switching::action_wp_logout
	 */
	public function testSessionIsNotDestroyedByAnInvalidOldUserCookieWhenLoggingOut(): void {
		$editor = self::$users['editor'];
		$editor_manager = WP_Session_Tokens::get_instance( $editor->ID );
		$editor_token = $editor_manager->create( time() + DAY_IN_SECONDS );
		$forged_cookie = implode( '|', [ $editor->user_login, time() + DAY_IN_SECONDS, $editor_token, 'invalid' ] );

		$_COOKIE[ USER_SWITCHING_COOKIE ] = json_encode( [ $forged_cookie ] );
		$_COOKIE[ USER_SWITCHING_OLDUSER_COOKIE ] = $forged_cookie;

		wp_logout();

		self::assertNotNull( $editor_manager->get( $editor_token ) );
	}
}
