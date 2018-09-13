<?php

class User_Switching_Test_Sessions extends User_Switching_Test {

	public function testExtraSessionsAreNotCreatedForUsersWhenSwitching() {
		if ( is_multisite() ) {
			$admin = self::$testers['super'];
		} else {
			$admin = self::$testers['admin'];
		}

		// Set up the admin session manager with a session
		$admin_manager = WP_Session_Tokens::get_instance( $admin->ID );
		$admin_token   = $admin_manager->create( time() + DAY_IN_SECONDS );
		$admin_before  = $admin_manager->get_all();

		// Set up the author session manager, but with no session
		$author_manager = WP_Session_Tokens::get_instance( self::$users['author']->ID );
		$author_before  = $author_manager->get_all();

		// Set up the admin user state
		wp_set_current_user( $admin->ID );
		wp_set_auth_cookie( $admin->ID, false, '', $admin_token );

		// Sanity checks
		$this->assertNotEmpty( $admin_token );
		$this->assertNotEmpty( wp_get_session_token() );

		// Verify the initial state
		$this->assertCount( 1, $admin_before );
		$this->assertCount( 0, $author_before );

		// Switch user
		$user = $this->switch_to_user( self::$users['author']->ID );

		// Verify no new sessions were created for the old user
		$this->assertCount( 1, $admin_manager->get_all() );

		// Verify only one new session was created for the new user
		$this->assertCount( 1, $author_manager->get_all() );

		$cookie = user_switching_get_auth_cookie();
		$parts  = wp_parse_auth_cookie( end( $cookie ) );

		// Verify the stored session token matches
		$this->assertSame( $admin_token, $parts['token'] );
	}

	public function testExtraSessionsAreNotCreatedForUserWhenSwitchingOff() {
		if ( is_multisite() ) {
			$admin = self::$testers['super'];
		} else {
			$admin = self::$testers['admin'];
		}

		// Set up the admin session manager with a session
		$admin_manager = WP_Session_Tokens::get_instance( $admin->ID );
		$admin_token   = $admin_manager->create( time() + DAY_IN_SECONDS );
		$admin_before  = $admin_manager->get_all();

		// Set up the admin user state
		wp_set_current_user( $admin->ID );
		wp_set_auth_cookie( $admin->ID, false, '', $admin_token );

		// Sanity checks
		$this->assertNotEmpty( $admin_token );
		$this->assertNotEmpty( wp_get_session_token() );

		// Verify the initial state
		$this->assertCount( 1, $admin_before );

		// Switch off
		$switched = $this->switch_off_user();

		// Verify no new sessions were created for the old user
		$this->assertCount( 1, $admin_manager->get_all() );

		$cookie = user_switching_get_auth_cookie();
		$parts  = wp_parse_auth_cookie( end( $cookie ) );

		// Verify the stored session token matches
		$this->assertSame( $admin_token, $parts['token'] );
	}

	public function testPreviousSessionForUserIsReusedWhenSwitchingBack() {
		if ( is_multisite() ) {
			$admin = self::$testers['super'];
		} else {
			$admin = self::$testers['admin'];
		}

		// Set up the admin session manager with a session
		$admin_manager = WP_Session_Tokens::get_instance( $admin->ID );
		$admin_token   = $admin_manager->create( time() + DAY_IN_SECONDS );
		$admin_before  = $admin_manager->get_all();

		// Set up the admin user state
		wp_set_current_user( $admin->ID );
		wp_set_auth_cookie( $admin->ID, false, '', $admin_token );

		// Verify the initial state
		$this->assertCount( 1, $admin_before );

		// Switch user
		$user = $this->switch_to_user( self::$users['author']->ID );

		// Verify no new sessions were created for the old user
		$this->assertCount( 1, $admin_manager->get_all() );

		// Switch back
		$user = $this->switch_to_user( $admin->ID, false, false );

		// Verify no new sessions were created for the original user
		$this->assertCount( 1, $admin_manager->get_all() );
	}

}
