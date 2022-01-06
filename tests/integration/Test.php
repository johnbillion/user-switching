<?php

declare(strict_types = 1);

namespace UserSwitching\Tests;

abstract class Test extends \Codeception\TestCase\WPTestCase {

	/**
	 * @var \WP_User[]
	 */
	protected static $users = array();

	/**
	 * @var \WP_User[]
	 */
	protected static $testers = array();

	/**
	 * @var array<int, string>
	 */
	protected $sessions = array();

	/**
	 * @return void
	 */
	public static function wpSetUpBeforeClass( \WP_UnitTest_Factory $factory ) {
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

		add_filter( 'user_switching_send_auth_cookies', '__return_false' );
	}

	/**
	 * @return void
	 */
	public function _before() {
		add_action( 'set_auth_cookie',           array( $this, 'action_set_auth_cookie' ), 10, 6 );
		add_action( 'set_logged_in_cookie',      array( $this, 'action_set_logged_in_cookie' ), 10, 6 );
		add_action( 'clear_auth_cookie',         array( $this, 'action_clear_auth_cookie' ) );

		add_action( 'set_user_switching_cookie', array( $this, 'action_set_user_switching_cookie' ), 10, 5 );
		add_action( 'set_olduser_cookie',        array( $this, 'action_set_olduser_cookie' ), 10, 5 );
		add_action( 'clear_olduser_cookie',      array( $this, 'action_clear_olduser_cookie' ) );
	}

	public function action_set_auth_cookie( $cookie, $expire, $expiration, $user_id, $scheme, $token ) {
		$_COOKIE[ SECURE_AUTH_COOKIE ] = $cookie;
		$_COOKIE[ AUTH_COOKIE ]        = $cookie;
		$this->sessions[ $user_id ]    = $token;
	}

	public function action_set_logged_in_cookie( $cookie, $expire, $expiration, $user_id, $scheme, $token ) {
		$_COOKIE[ LOGGED_IN_COOKIE ] = $cookie;
	}

	public function action_clear_auth_cookie() {
		unset( $_COOKIE[ LOGGED_IN_COOKIE ] );
		unset( $_COOKIE[ SECURE_AUTH_COOKIE ] );
		unset( $_COOKIE[ AUTH_COOKIE ] );
	}

	public function action_set_user_switching_cookie( $cookie, $expiration, $user_id, $scheme, $token ) {
		$_COOKIE[ USER_SWITCHING_COOKIE ]        = $cookie;
		$_COOKIE[ USER_SWITCHING_SECURE_COOKIE ] = $cookie;
	}

	public function action_set_olduser_cookie( $cookie, $expiration, $user_id, $scheme, $token ) {
		$_COOKIE[ USER_SWITCHING_OLDUSER_COOKIE ] = $cookie;
	}

	public function action_clear_olduser_cookie() {
		unset( $_COOKIE[ USER_SWITCHING_COOKIE ] );
		unset( $_COOKIE[ USER_SWITCHING_SECURE_COOKIE ] );
		unset( $_COOKIE[ USER_SWITCHING_OLDUSER_COOKIE ] );
	}

}
