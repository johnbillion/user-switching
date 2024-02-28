<?php declare(strict_types = 1);

namespace UserSwitching\Tests;

abstract class Test extends \Codeception\TestCase\WPTestCase {
	/**
	 * @var array<string, \WP_User>
	 */
	protected static $users = [];

	/**
	 * @var array<string, \WP_User>
	 */
	protected static $testers = [];

	/**
	 * @var array<int, string>
	 */
	protected $sessions = [];

	/**
	 * @return void
	 */
	public static function wpSetUpBeforeClass( \WP_UnitTest_Factory $factory ) {
		$roles = array(
			'admin' => 'administrator',
			'editor' => 'editor',
			'author' => 'author',
			'contributor' => 'contributor',
			'subscriber' => 'subscriber',
			'no_role' => '',
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

	public function _before(): void {
		add_action( 'set_auth_cookie',           array( $this, 'action_set_auth_cookie' ), 10, 6 );
		add_action( 'set_logged_in_cookie',      array( $this, 'action_set_logged_in_cookie' ), 10 );
		add_action( 'clear_auth_cookie',         array( $this, 'action_clear_auth_cookie' ) );

		add_action( 'set_user_switching_cookie', array( $this, 'action_set_user_switching_cookie' ), 10 );
		add_action( 'set_olduser_cookie',        array( $this, 'action_set_olduser_cookie' ), 10 );
		add_action( 'clear_olduser_cookie',      array( $this, 'action_clear_olduser_cookie' ) );
	}

	final public function action_set_auth_cookie(
		string $cookie,
		int $expire,
		int $expiration,
		int $user_id,
		string $scheme,
		string $token
	): void {
		$_COOKIE[ SECURE_AUTH_COOKIE ] = $cookie;
		$_COOKIE[ AUTH_COOKIE ] = $cookie;
		$this->sessions[ $user_id ] = $token;
	}

	final public function action_set_logged_in_cookie( string $cookie ): void {
		$_COOKIE[ LOGGED_IN_COOKIE ] = $cookie;
	}

	final public function action_clear_auth_cookie(): void {
		unset( $_COOKIE[ LOGGED_IN_COOKIE ] );
		unset( $_COOKIE[ SECURE_AUTH_COOKIE ] );
		unset( $_COOKIE[ AUTH_COOKIE ] );
	}

	final public function action_set_user_switching_cookie( string $cookie ): void {
		$_COOKIE[ USER_SWITCHING_COOKIE ] = $cookie;
		$_COOKIE[ USER_SWITCHING_SECURE_COOKIE ] = $cookie;
	}

	final public function action_set_olduser_cookie( string $cookie ): void {
		$_COOKIE[ USER_SWITCHING_OLDUSER_COOKIE ] = $cookie;
	}

	final public function action_clear_olduser_cookie(): void {
		unset( $_COOKIE[ USER_SWITCHING_COOKIE ] );
		unset( $_COOKIE[ USER_SWITCHING_SECURE_COOKIE ] );
		unset( $_COOKIE[ USER_SWITCHING_OLDUSER_COOKIE ] );
	}
}
