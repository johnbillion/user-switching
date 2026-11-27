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
	 * @covers \switch_to_user
	 */
	public function testRevokingOriginatingSessionInvalidatesSwitchedSession(): void {
		$operator = self::$testers[ is_multisite() ? 'super' : 'admin' ];
		$author = self::$users['author'];
		$operator_sessions = WP_Session_Tokens::get_instance( $operator->ID );
		$author_sessions = WP_Session_Tokens::get_instance( $author->ID );
		$author_login_token = $author_sessions->create( time() + DAY_IN_SECONDS );

		wp_set_current_user( $operator->ID );
		wp_set_auth_cookie( $operator->ID );
		$operator_token = wp_get_session_token();

		switch_to_user( $author->ID );
		$switched_token = wp_get_session_token();
		self::assertTrue( $operator_sessions->verify( $operator_token ) );
		self::assertTrue( $author_sessions->verify( $switched_token ) );

		$operator_sessions->destroy( $operator_token );

		self::assertFalse( $operator_sessions->verify( $operator_token ) );
		self::assertTrue( $author_sessions->verify( $author_login_token ), "The author's own session should remain valid." );
		self::assertFalse( $author_sessions->verify( $switched_token ), 'The switched session should be invalid after its originating session is revoked.' );
	}

	/**
	 * @covers \switch_to_user
	 */
	public function testDestroyingAllOriginatingSessionsInvalidatesSwitchedSession(): void {
		$operator = self::$testers[ is_multisite() ? 'super' : 'admin' ];
		$author = self::$users['author'];
		$operator_sessions = WP_Session_Tokens::get_instance( $operator->ID );
		$author_sessions = WP_Session_Tokens::get_instance( $author->ID );
		$author_login_token = $author_sessions->create( time() + DAY_IN_SECONDS );

		wp_set_current_user( $operator->ID );
		wp_set_auth_cookie( $operator->ID );

		switch_to_user( $author->ID );
		$switched_token = wp_get_session_token();
		self::assertTrue( $author_sessions->verify( $switched_token ) );

		$operator_sessions->destroy_all();

		self::assertTrue( $author_sessions->verify( $author_login_token ), "The author's own session should remain valid." );
		self::assertFalse( $author_sessions->verify( $switched_token ), 'The switched session should be invalid after all originating sessions are revoked.' );
	}

	/**
	 * @covers \switch_to_user
	 */
	public function testDestroyingOtherOriginatingSessionsOnlyInvalidatesTheirSwitchedSessions(): void {
		$operator = self::$testers[ is_multisite() ? 'super' : 'admin' ];
		$author = self::$users['author'];
		$operator_sessions = WP_Session_Tokens::get_instance( $operator->ID );
		$author_sessions = WP_Session_Tokens::get_instance( $author->ID );

		wp_set_current_user( $operator->ID );
		wp_set_auth_cookie( $operator->ID );
		$kept_token = wp_get_session_token();
		switch_to_user( $author->ID );
		$kept_switched_token = wp_get_session_token();

		wp_set_current_user( $operator->ID );
		wp_set_auth_cookie( $operator->ID );
		switch_to_user( $author->ID );
		$other_switched_token = wp_get_session_token();

		$operator_sessions->destroy_others( $kept_token );

		self::assertTrue( $operator_sessions->verify( $kept_token ) );
		self::assertTrue( $author_sessions->verify( $kept_switched_token ), 'The switched session from the kept session should remain valid.' );
		self::assertFalse( $author_sessions->verify( $other_switched_token ), 'The switched session from a revoked session should be invalid.' );
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
}
