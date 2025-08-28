<?php declare(strict_types = 1);

namespace UserSwitching\Tests;

abstract class Test extends \WP_UnitTestCase {
	/**
	 * @var array<string, \WP_User>
	 */
	protected static array $users = [];

	/**
	 * @var array<string, \WP_User>
	 */
	protected static array $testers = [];

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
			$user = $factory->user->create_and_get( array(
				'role' => $role,
			) );
			if ( is_wp_error( $user ) ) {
				throw new \RuntimeException( 'Failed to create user: ' . $user->get_error_message() );
			}
			self::$users[ $name ] = $user;

			$tester = $factory->user->create_and_get( array(
				'role' => $role,
			) );
			if ( is_wp_error( $tester ) ) {
				throw new \RuntimeException( 'Failed to create tester: ' . $tester->get_error_message() );
			}
			self::$testers[ $name ] = $tester;
		}

		if ( is_multisite() ) {
			$super_user = $factory->user->create_and_get( array(
				'role' => 'administrator',
			) );
			if ( is_wp_error( $super_user ) ) {
				throw new \RuntimeException( 'Failed to create super user: ' . $super_user->get_error_message() );
			}
			self::$users['super'] = $super_user;

			$super_tester = $factory->user->create_and_get( array(
				'role' => 'administrator',
			) );
			if ( is_wp_error( $super_tester ) ) {
				throw new \RuntimeException( 'Failed to create super tester: ' . $super_tester->get_error_message() );
			}
			self::$testers['super'] = $super_tester;

			grant_super_admin( self::$users['super']->ID );
			grant_super_admin( self::$testers['super']->ID );
		}

		add_filter( 'user_switching_send_auth_cookies', '__return_false' );
	}

	public function set_up(): void {
		parent::set_up();

		add_action( 'set_auth_cookie',           array( $this, 'action_set_auth_cookie' ), 10, 6 );
		add_action( 'set_logged_in_cookie',      array( $this, 'action_set_logged_in_cookie' ), 10 );
		add_action( 'clear_auth_cookie',         array( $this, 'action_clear_auth_cookie' ) );

		add_action( 'set_user_switching_cookie', array( $this, 'action_set_user_switching_cookie' ), 10 );
		add_action( 'set_olduser_cookie',        array( $this, 'action_set_olduser_cookie' ), 10 );
		add_action( 'clear_olduser_cookie',      array( $this, 'action_clear_olduser_cookie' ) );

		$_COOKIE = [];
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
