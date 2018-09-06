<?php

class User_Switching_Test_Sessions extends User_Switching_Test {

	public function testExtraSessionsAreNotCreatedForOldUserWhenSwitching() {
		if ( is_multisite() ) {
			$admin = self::$testers['super'];
		} else {
			$admin = self::$testers['admin'];
		}

		// Set up the session
		$manager = WP_Session_Tokens::get_instance( $admin->ID );
		$token   = $manager->create( time() + DAY_IN_SECONDS );
		$before  = $manager->get_all();

		// Set up the user state
		wp_set_current_user( $admin->ID );
		wp_set_auth_cookie( $admin->ID, false, '', $token );

		// Sanity checks
		$this->assertNotEmpty( $token );
		$this->assertNotEmpty( wp_get_session_token() );

		// Start with a base session
		$this->assertCount( 1, $before );

		// Switch user
		$user = $this->switch_to_user( self::$users['author']->ID );

		// Verify no new sessions were created for the old user
		$this->assertCount( 1, $manager->get_all() );
	}

}
