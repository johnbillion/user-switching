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

		if ( is_multisite() ) {
			$this->super = $this->factory->user->create_and_get( array(
				'role' => 'administrator'
			) );
			grant_super_admin( $this->super->ID );
		}

	}

	function testCaps() {

		if ( is_multisite() ) {

			# Can super admins switch to admins?
			$this->assertTrue( user_can( $this->super->ID, 'switch_to_user', $this->admin->ID ) );

			# Can admins switch to editors?
			$this->assertFalse( user_can( $this->admin->ID, 'switch_to_user', $this->editor->ID ) );

			# Can editors switch to admins?
			$this->assertFalse( user_can( $this->editor->ID, 'switch_to_user', $this->admin->ID ) );

			# Can admins switch to super admins?
			$this->assertFalse( user_can( $this->admin->ID, 'switch_to_user', $this->super->ID ) );

			# Can a super admin switch to themselves?
			$this->assertFalse( user_can( $this->super->ID, 'switch_to_user', $this->super->ID ) );

		} else {

			# Can admins switch to editors?
			$this->assertTrue( user_can( $this->admin->ID, 'switch_to_user', $this->editor->ID ) );

			# Can editors switch to admins?
			$this->assertFalse( user_can( $this->editor->ID, 'switch_to_user', $this->admin->ID ) );

			# Can an admin switch to themselves?
			$this->assertFalse( user_can( $this->admin->ID, 'switch_to_user', $this->admin->ID ) );

		}

	}

}
