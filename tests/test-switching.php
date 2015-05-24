<?php

class User_Switching_Test_Switching extends User_Switching_Test {

	function setUp() {

		add_action( 'switch_to_user',         array( $this, '_action_switch_user' ), 10, 2 );
		add_action( 'switch_back_user',       array( $this, '_action_switch_user' ), 10, 2 );
		add_action( 'switch_off_user',        array( $this, '_action_switch_off' ), 10 );
		add_filter( 'auth_cookie_expiration', array( $this, '_filter_auth_cookie_expiration' ), 10, 3 );

		parent::setUp();

	}

	function testSwitchUserAndBack() {

		if ( is_multisite() ) {
			$admin = $this->super;
		} else {
			$admin = $this->admin;
		}

		wp_set_current_user( $admin->ID );

		/*
		 * `switch_to_user()` and the functions it subsequently calls will trigger "headers already sent" PHP errors, so
		 * we need to mute them in order to avoid phpunit throwing an exception.
		 */
		$this->silence();
		$user = switch_to_user( $this->editor->ID, true );
		$this->go_forth();

		// Check that we've switched
		$this->assertInstanceOf( 'WP_User', $user );
		$this->assertEquals( $this->editor->ID, $user->ID );
		$this->assertEquals( $this->editor->ID, get_current_user_id() );

		// Check the `switch_*` actions and their parameters
		$this->assertEquals( 1,                 did_action( 'switch_to_user' ) );
		$this->assertEquals( 0,                 did_action( 'switch_back_user' ) );
		$this->assertEquals( 0,                 did_action( 'switch_off_user' ) );
		$this->assertEquals( $this->editor->ID, $this->test_switching_user_id );
		$this->assertEquals( $admin->ID,        $this->test_switching_old_user_id );

		// Check the auth cookie behaviour
		$this->assertEquals( $this->editor->ID, $this->test_switching_auth_cookie_user_id );
		$this->assertTrue( $this->test_switching_auth_cookie_remember );

		// Switch back
		$this->silence();
		$user = switch_to_user( $admin->ID, false, false );
		$this->go_forth();

		// Check that we've switched
		$this->assertInstanceOf( 'WP_User', $user );
		$this->assertEquals( $admin->ID, $user->ID );
		$this->assertEquals( $admin->ID, get_current_user_id() );

		// Check the `switch_*` actions and their parameters
		$this->assertEquals( 1,                 did_action( 'switch_to_user' ) );
		$this->assertEquals( 1,                 did_action( 'switch_back_user' ) );
		$this->assertEquals( 0,                 did_action( 'switch_off_user' ) );
		$this->assertEquals( $admin->ID,        $this->test_switching_user_id );
		$this->assertEquals( $this->editor->ID, $this->test_switching_old_user_id );

		// Check the auth cookie behaviour
		$this->assertEquals( $admin->ID, $this->test_switching_auth_cookie_user_id );
		$this->assertFalse( $this->test_switching_auth_cookie_remember );

	}

	function testSwitchOffAndBack() {

		if ( is_multisite() ) {
			$admin = $this->super;
		} else {
			$admin = $this->admin;
		}

		wp_set_current_user( $admin->ID );

		// Switch off
		$this->silence();
		$user = switch_off_user();
		$this->go_forth();

		// Check that we've switched off
		$this->assertTrue( $user );
		$this->assertEquals( 0, get_current_user_id() );

		// Check the `switch_*` actions and their parameters
		$this->assertEquals( 0,          did_action( 'switch_to_user' ) );
		$this->assertEquals( 0,          did_action( 'switch_back_user' ) );
		$this->assertEquals( 1,          did_action( 'switch_off_user' ) );
		$this->assertFalse( $this->test_switching_user_id );
		$this->assertEquals( $admin->ID, $this->test_switching_old_user_id );

		// Check the auth cookie behaviour
		$this->assertEquals( 1, did_action( 'clear_auth_cookie' ) );

		// Switch back
		$this->silence();
		$user = switch_to_user( $admin->ID, false, false );
		$this->go_forth();

		// Check that we've switched back
		$this->assertInstanceOf( 'WP_User', $user );
		$this->assertEquals( $admin->ID, $user->ID );
		$this->assertEquals( $admin->ID, get_current_user_id() );

		// Check the `switch_*` actions and their parameters
		$this->assertEquals( 0,          did_action( 'switch_to_user' ) );
		$this->assertEquals( 1,          did_action( 'switch_back_user' ) );
		$this->assertEquals( 1,          did_action( 'switch_off_user' ) );
		$this->assertEquals( $admin->ID, $this->test_switching_user_id );
		$this->assertEquals( 0,          $this->test_switching_old_user_id );

		// Check the auth cookie behaviour
		$this->assertEquals( $admin->ID, $this->test_switching_auth_cookie_user_id );
		$this->assertFalse( $this->test_switching_auth_cookie_remember );

	}

	function _action_switch_user( $user_id, $old_user_id ) {
		$this->test_switching_user_id     = $user_id;
		$this->test_switching_old_user_id = $old_user_id;
	}

	function _action_switch_off( $old_user_id ) {
		$this->test_switching_user_id     = false;
		$this->test_switching_old_user_id = $old_user_id;
	}

	function _filter_auth_cookie_expiration( $length, $user_id, $remember ) {
		$this->test_switching_auth_cookie_user_id  = $user_id;
		$this->test_switching_auth_cookie_remember = $remember;
		return $length;
	}

}
