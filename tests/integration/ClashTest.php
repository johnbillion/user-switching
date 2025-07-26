<?php declare(strict_types = 1);

namespace UserSwitching\Tests;

use user_switching;
use WP_Session_Tokens;

final class ClashTest extends Test {
	/**
	 * @covers \user_switching::get_duplicated_switch
	 */
	public function testSessionClashIsDetected(): void {
		$admin = self::$testers['admin'];
		$another = self::$testers['contributor'];

		// Set up the admin session manager with a session
		$admin_manager = WP_Session_Tokens::get_instance( $admin->ID );
		$admin_token = $admin_manager->create( time() + DAY_IN_SECONDS );

		// Set up the admin user state
		wp_set_current_user( $admin->ID );
		wp_set_auth_cookie( $admin->ID, false, '', $admin_token );

		// Verify that there is initially no session clash
		self::assertNull( user_switching::get_duplicated_switch( self::$users['author'], $admin ) );

		// Switch the admin to author
		switch_to_user( self::$users['author']->ID );

		// Verify that a session clash is detected if another user were to attempt to switch to the author
		self::assertIsArray( user_switching::get_duplicated_switch( self::$users['author'], $another ) );

		// Verify that no session clash is detected for the user who just made the switch (this allows a user to switch into the same user multiple times)
		self::assertNull( user_switching::get_duplicated_switch( self::$users['author'], $admin ) );

		// Verify that no session clash is detected for a target user who nobody has switched into
		self::assertNull( user_switching::get_duplicated_switch( self::$users['editor'], $another ) );
		self::assertNull( user_switching::get_duplicated_switch( self::$users['editor'], $admin ) );

		// Switch the admin back to their admin account
		switch_to_user( $admin->ID, false, false );

		// Verify that there are now no session clashes
		self::assertNull( user_switching::get_duplicated_switch( self::$users['author'], $another ) );
		self::assertNull( user_switching::get_duplicated_switch( self::$users['author'], $admin ) );
	}

	/**
	 * @covers \user_switching::get_duplicated_switch
	 */
	public function testSessionClashByCurrentUserIsIgnored(): void {
		$admin = self::$testers['admin'];

		// Set up the admin session manager with two sessions, mimicking two different browsers or devices
		$admin_manager_1 = WP_Session_Tokens::get_instance( $admin->ID );
		$admin_token_1 = $admin_manager_1->create( time() + DAY_IN_SECONDS );
		$admin_manager_2 = WP_Session_Tokens::get_instance( $admin->ID );
		$admin_token_2 = $admin_manager_2->create( time() + DAY_IN_SECONDS );

		// Set up the admin user state for their first session
		wp_set_current_user( $admin->ID );
		wp_set_auth_cookie( $admin->ID, false, '', $admin_token_1 );

		// Switch the admin to author
		switch_to_user( self::$users['author']->ID );

		// Set up the admin user state for their second session
		wp_set_current_user( $admin->ID );
		wp_set_auth_cookie( $admin->ID, false, '', $admin_token_2 );

		// Verify that there is no session clash because the clash is from the same user
		self::assertNull( user_switching::get_duplicated_switch( self::$users['author'], $admin ) );
	}
}
