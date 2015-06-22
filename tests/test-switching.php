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
			$admin = $this->testers['super'];
		} else {
			$admin = $this->testers['admin'];
		}

		wp_set_current_user( $admin->ID );


		// Switch user
		$user = $this->switch_to_user( $this->users['author']->ID, true );

		// Check that we've switched
		$this->assertInstanceOf( 'WP_User', $user );
		$this->assertSame( $this->users['author']->ID, $user->ID );
		$this->assertSame( $this->users['author']->ID, get_current_user_id() );

		// Check the `switch_*` actions were fired
		$this->assertSame( 1, did_action( 'switch_to_user' ) );
		$this->assertSame( 0, did_action( 'switch_back_user' ) );
		$this->assertSame( 0, did_action( 'switch_off_user' ) );

		// Check the `switch_*` actions' parameters
		$this->assertSame( $this->users['author']->ID, $this->test_switching_user_id );
		$this->assertSame( $admin->ID,                 $this->test_switching_old_user_id );

		// Check the auth cookie behaviour
		$this->assertSame( $this->users['author']->ID, $this->test_switching_auth_cookie_user_id );
		$this->assertTrue( $this->test_switching_auth_cookie_remember );



		// Switch user again
		$user = $this->switch_to_user( $this->users['editor']->ID, true );

		// Check that we've switched
		$this->assertInstanceOf( 'WP_User', $user );
		$this->assertSame( $this->users['editor']->ID, $user->ID );
		$this->assertSame( $this->users['editor']->ID, get_current_user_id() );

		// Check the `switch_*` actions were fired
		$this->assertSame( 2, did_action( 'switch_to_user' ) );
		$this->assertSame( 0, did_action( 'switch_back_user' ) );
		$this->assertSame( 0, did_action( 'switch_off_user' ) );

		// Check the `switch_*` actions' parameters
		$this->assertSame( $this->users['editor']->ID, $this->test_switching_user_id );
		$this->assertSame( $this->users['author']->ID, $this->test_switching_old_user_id );

		// Check the auth cookie behaviour
		$this->assertSame( $this->users['editor']->ID, $this->test_switching_auth_cookie_user_id );
		$this->assertTrue( $this->test_switching_auth_cookie_remember );



		// Switch back
		$user = $this->switch_to_user( $this->users['author']->ID, false, false );

		// Check that we've switched
		$this->assertInstanceOf( 'WP_User', $user );
		$this->assertSame( $this->users['author']->ID, $user->ID );
		$this->assertSame( $this->users['author']->ID, get_current_user_id() );

		// Check the `switch_*` actions were fired
		$this->assertSame( 2, did_action( 'switch_to_user' ) );
		$this->assertSame( 1, did_action( 'switch_back_user' ) );
		$this->assertSame( 0, did_action( 'switch_off_user' ) );

		// Check the `switch_*` actions' parameters
		$this->assertSame( $this->users['author']->ID, $this->test_switching_user_id );
		$this->assertSame( $this->users['editor']->ID, $this->test_switching_old_user_id );

		// Check the auth cookie behaviour
		$this->assertSame( $this->users['author']->ID, $this->test_switching_auth_cookie_user_id );
		$this->assertFalse( $this->test_switching_auth_cookie_remember );



		// Switch back again
		$user = $this->switch_to_user( $admin->ID, false, false );

		// Check that we've switched
		$this->assertInstanceOf( 'WP_User', $user );
		$this->assertSame( $admin->ID, $user->ID );
		$this->assertSame( $admin->ID, get_current_user_id() );

		// Check the `switch_*` actions were fired
		$this->assertSame( 2, did_action( 'switch_to_user' ) );
		$this->assertSame( 2, did_action( 'switch_back_user' ) );
		$this->assertSame( 0, did_action( 'switch_off_user' ) );

		// Check the `switch_*` actions' parameters
		$this->assertSame( $admin->ID,                 $this->test_switching_user_id );
		$this->assertSame( $this->users['author']->ID, $this->test_switching_old_user_id );

		// Check the auth cookie behaviour
		$this->assertSame( $admin->ID, $this->test_switching_auth_cookie_user_id );
		$this->assertFalse( $this->test_switching_auth_cookie_remember );

	}

	function testSwitchOffAndBack() {

		if ( is_multisite() ) {
			$admin = $this->testers['super'];
		} else {
			$admin = $this->testers['admin'];
		}

		wp_set_current_user( $admin->ID );

		// Switch off
		$user = $this->switch_off_user();

		// Check that we've switched off
		$this->assertTrue( $user );
		$this->assertFalse( is_user_logged_in() );
		$this->assertSame( 0, get_current_user_id() );

		// Check the `switch_*` actions were fired
		$this->assertSame( 0, did_action( 'switch_to_user' ) );
		$this->assertSame( 0, did_action( 'switch_back_user' ) );
		$this->assertSame( 1, did_action( 'switch_off_user' ) );

		// Check the `switch_*` actions' parameters
		$this->assertFalse( $this->test_switching_user_id );
		$this->assertSame( $admin->ID, $this->test_switching_old_user_id );

		// Check the auth cookie behaviour
		$this->assertSame( 1, did_action( 'clear_auth_cookie' ) );

		// Switch back
		$user = $this->switch_to_user( $admin->ID, false, false );

		// Check that we've switched back
		$this->assertInstanceOf( 'WP_User', $user );
		$this->assertTrue( is_user_logged_in() );
		$this->assertSame( $admin->ID, $user->ID );
		$this->assertSame( $admin->ID, get_current_user_id() );

		// Check the `switch_*` actions were fired
		$this->assertSame( 0, did_action( 'switch_to_user' ) );
		$this->assertSame( 1, did_action( 'switch_back_user' ) );
		$this->assertSame( 1, did_action( 'switch_off_user' ) );

		// Check the `switch_*` actions' parameters
		$this->assertSame( $admin->ID, $this->test_switching_user_id );
		$this->assertFalse( $this->test_switching_old_user_id );

		// Check the auth cookie behaviour
		$this->assertSame( $admin->ID, $this->test_switching_auth_cookie_user_id );
		$this->assertFalse( $this->test_switching_auth_cookie_remember );

	}

	function testCurrentUrl() {

		$url = add_query_arg( 'foo', 'bar', home_url( 'baz' ) );
		$this->go_to( $url );
		$this->assertSame( user_switching::current_url(), $url );

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
