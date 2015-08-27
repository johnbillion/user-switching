<?php

abstract class User_Switching_Test extends WP_UnitTestCase {

	protected $users   = array();
	protected $testers = array();

	function setUp() {

		parent::setUp();

		// Hide deprecated warnings on PHP 7 so the use of deprecated constructors in WordPress
		// don't cause our tests to fail
		if ( version_compare( PHP_VERSION, 7, '>=' ) ) {
			error_reporting( E_ALL & ~E_DEPRECATED );
		}

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

		// Prevent undefined index notices when using `wp_validate_auth_cookie()`.
		// See https://core.trac.wordpress.org/ticket/32636
		if ( ! isset( $_SERVER['REQUEST_METHOD'] ) ) {
			$_SERVER['REQUEST_METHOD'] = 'GET';
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
		if ( defined( 'HHVM_VERSION' ) ) {
			return;
		}
		$this->silence_warning = PHPUnit_Framework_Error_Warning::$enabled;
		PHPUnit_Framework_Error_Warning::$enabled = false;
		$this->silence_display = ini_get( 'display_errors' );
		$this->silence_log = ini_get( 'error_log' );
		ini_set( 'display_errors', 0 );
		ini_set( 'error_log', '/dev/null' );
	}

	private function go_forth() {
		if ( defined( 'HHVM_VERSION' ) ) {
			return;
		}
		PHPUnit_Framework_Error_Warning::$enabled = $this->silence_warning;
		ini_set( 'display_errors', $this->silence_display );
		ini_set( 'error_log', $this->silence_log );
	}

}
