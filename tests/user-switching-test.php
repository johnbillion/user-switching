<?php

abstract class User_Switching_Test extends WP_UnitTestCase {

	protected static $users   = array();
	protected static $testers = array();

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {

		$roles = array(
			'admin'       => 'administrator',
			'editor'      => 'editor',
			'author'      => 'author',
			'contributor' => 'contributor',
			'subscriber'  => 'subscriber',
			'no_role'     => '',
		);

		foreach ( $roles as $name => $role ) {
			self::$users[ $name ] = $factory->user->create_and_get( array(
				'role' => $role,
			) );
			self::$testers[ $name ] = $factory->user->create_and_get( array(
				'role' => $role,
			) );
		}

		if ( is_multisite() ) {
			self::$users['super'] = $factory->user->create_and_get( array(
				'role' => 'administrator'
			) );
			self::$testers['super'] = $factory->user->create_and_get( array(
				'role' => 'administrator'
			) );
			grant_super_admin( self::$users['super']->ID );
			grant_super_admin( self::$testers['super']->ID );
		}

		add_filter( 'send_auth_cookies', '__return_false' );
	}

	protected function switch_to_user( $user_id, $remember = false, $set_old_user = true ) {
		return switch_to_user( $user_id, $remember, $set_old_user );
	}

	protected function switch_off_user() {
		return switch_off_user();
	}

}
