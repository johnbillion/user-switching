<?php

class User_Switching_Test extends WP_UnitTestCase {

	function setUp() {

		parent::setUp();

		$this->admin = $this->factory->user->create_and_get( array(
			'role' => 'administrator'
		) );
		$this->editor = $this->factory->user->create_and_get( array(
			'role' => 'editor'
		) );
		$this->author = $this->factory->user->create_and_get( array(
			'role' => 'author'
		) );
		$this->contributor = $this->factory->user->create_and_get( array(
			'role' => 'contributor'
		) );
		$this->subscriber = $this->factory->user->create_and_get( array(
			'role' => 'subscriber'
		) );

		if ( is_multisite() ) {
			$this->super = $this->factory->user->create_and_get( array(
				'role' => 'administrator'
			) );
			grant_super_admin( $this->super->ID );
		}

	}

	function testAdminCaps() {

		if ( is_multisite() ) {

			# Super Admins can switch to all users:
			$this->assertTrue( user_can( $this->super->ID, 'switch_to_user', $this->admin->ID ) );
			$this->assertTrue( user_can( $this->super->ID, 'switch_to_user', $this->editor->ID ) );
			$this->assertTrue( user_can( $this->super->ID, 'switch_to_user', $this->author->ID ) );
			$this->assertTrue( user_can( $this->super->ID, 'switch_to_user', $this->contributor->ID ) );
			$this->assertTrue( user_can( $this->super->ID, 'switch_to_user', $this->subscriber->ID ) );

			# Super Admins cannot switch to themselves:
			$this->assertFalse( user_can( $this->super->ID, 'switch_to_user', $this->super->ID ) );

			# Admins cannot switch to other users:
			$this->assertFalse( user_can( $this->admin->ID, 'switch_to_user', $this->super->ID ) );
			$this->assertFalse( user_can( $this->admin->ID, 'switch_to_user', $this->editor->ID ) );
			$this->assertFalse( user_can( $this->admin->ID, 'switch_to_user', $this->author->ID ) );
			$this->assertFalse( user_can( $this->admin->ID, 'switch_to_user', $this->contributor->ID ) );
			$this->assertFalse( user_can( $this->admin->ID, 'switch_to_user', $this->subscriber->ID ) );

			# Admins cannot switch to themselves:
			$this->assertFalse( user_can( $this->admin->ID, 'switch_to_user', $this->admin->ID ) );

		} else {

			# Admins can switch to all users:
			$this->assertTrue( user_can( $this->admin->ID, 'switch_to_user', $this->editor->ID ) );
			$this->assertTrue( user_can( $this->admin->ID, 'switch_to_user', $this->author->ID ) );
			$this->assertTrue( user_can( $this->admin->ID, 'switch_to_user', $this->contributor->ID ) );
			$this->assertTrue( user_can( $this->admin->ID, 'switch_to_user', $this->subscriber->ID ) );

			# Admins cannot switch to themselves:
			$this->assertFalse( user_can( $this->admin->ID, 'switch_to_user', $this->admin->ID ) );

		}

	}

}
