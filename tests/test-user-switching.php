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

	}

	function testCaps() {

		# Can admins switch to editors?
		$this->assertTrue( user_can( $this->admin->ID, 'switch_to_user', $this->editor->ID ) );

		# Can editors switch to admins?
		$this->assertFalse( user_can( $this->editor->ID, 'switch_to_user', $this->admin->ID ) );

		# Can a user switch to themselves?
		$this->assertFalse( user_can( $this->admin->ID, 'switch_to_user', $this->admin->ID ) );

	}

}
