<?php

abstract class User_Switching_Test extends WP_UnitTestCase {

	protected $users   = array();
	protected $testers = array();

	function setUp() {

		parent::setUp();

		$roles = array(
			'admin'       => 'administrator',
			'editor'      => 'editor',
			'author'      => 'author',
			'contributor' => 'contributor',
			'subscriber'  => 'subscriber',
			'no_role'     => '',
		);

		foreach ( $roles as $name => $role ) {
			$this->users[ $name ] = $this->factory->user->create_and_get( array(
				'role' => $role,
			) );
			$this->testers[ $name ] = $this->factory->user->create_and_get( array(
				'role' => $role,
			) );
		}

		if ( is_multisite() ) {
			$this->users['super'] = $this->factory->user->create_and_get( array(
				'role' => 'administrator'
			) );
			$this->testers['super'] = $this->factory->user->create_and_get( array(
				'role' => 'administrator'
			) );
			grant_super_admin( $this->users['super']->ID );
			grant_super_admin( $this->testers['super']->ID );
		}

	}

	protected function switch_to_user( $user_id, $remember = false, $set_old_user = true ) {

		// Verify the integrity of our wrapper methods
		$target  = new ReflectionFunction( 'switch_to_user' );
		$wrapper = new ReflectionMethod( __METHOD__ );
		$this->assertSame( $wrapper->getNumberOfParameters(), $target->getNumberOfParameters() );

		/*
		 * `switch_to_user()` and the functions it subsequently calls will trigger "headers already sent" PHP errors, so
		 * we need to mute them in order to avoid phpunit throwing an exception.
		 */
		$this->silence();
		$user = switch_to_user( $user_id, $remember, $set_old_user );
		$this->go_forth();

		return $user;

	}

	protected function switch_off_user() {

		// Verify the integrity of our wrapper methods
		$target  = new ReflectionFunction( 'switch_off_user' );
		$wrapper = new ReflectionMethod( __METHOD__ );
		$this->assertSame( $wrapper->getNumberOfParameters(), $target->getNumberOfParameters() );

		/*
		 * `switch_off_user()` and the functions it subsequently calls will trigger "headers already sent" PHP errors, so
		 * we need to mute them in order to avoid phpunit throwing an exception.
		 */
		$this->silence();
		$user = switch_off_user();
		$this->go_forth();

		return $user;

	}

	private function silence() {
		$this->silence_warning = PHPUnit_Framework_Error_Warning::$enabled;
		PHPUnit_Framework_Error_Warning::$enabled = false;
		$this->silence_display = ini_get( 'display_errors' );
		ini_set( 'display_errors', 0 );
	}

	private function go_forth() {
		PHPUnit_Framework_Error_Warning::$enabled = $this->silence_warning;
		ini_set( 'display_errors', $this->silence_display );
	}

}
