<?php

class User_Switching_Test_Caps extends User_Switching_Test {

	function testSuperAdminCaps() {

		if ( is_multisite() ) {

			# Super Admins can switch to all users:
			$this->assertTrue( user_can( $this->super->ID, 'switch_to_user', $this->admin->ID ) );
			$this->assertTrue( user_can( $this->super->ID, 'switch_to_user', $this->editor->ID ) );
			$this->assertTrue( user_can( $this->super->ID, 'switch_to_user', $this->author->ID ) );
			$this->assertTrue( user_can( $this->super->ID, 'switch_to_user', $this->contributor->ID ) );
			$this->assertTrue( user_can( $this->super->ID, 'switch_to_user', $this->subscriber->ID ) );
			$this->assertTrue( user_can( $this->super->ID, 'switch_to_user', $this->no_role->ID ) );

			# Super Admins cannot switch to themselves:
			$this->assertFalse( user_can( $this->super->ID, 'switch_to_user', $this->super->ID ) );

			# Super Admins can switch off:
			$this->assertTrue( user_can( $this->super->ID, 'switch_off' ) );

		}

	}

	function testAdminCaps() {

		if ( is_multisite() ) {

			# Admins cannot switch to other users:
			$this->assertFalse( user_can( $this->admin->ID, 'switch_to_user', $this->super->ID ) );
			$this->assertFalse( user_can( $this->admin->ID, 'switch_to_user', $this->editor->ID ) );
			$this->assertFalse( user_can( $this->admin->ID, 'switch_to_user', $this->author->ID ) );
			$this->assertFalse( user_can( $this->admin->ID, 'switch_to_user', $this->contributor->ID ) );
			$this->assertFalse( user_can( $this->admin->ID, 'switch_to_user', $this->subscriber->ID ) );
			$this->assertFalse( user_can( $this->admin->ID, 'switch_to_user', $this->no_role->ID ) );

			# Admins cannot switch to themselves:
			$this->assertFalse( user_can( $this->admin->ID, 'switch_to_user', $this->admin->ID ) );

			# Admins cannot switch off:
			$this->assertFalse( user_can( $this->admin->ID, 'switch_off' ) );

		} else {

			# Admins can switch to all users:
			$this->assertTrue( user_can( $this->admin->ID, 'switch_to_user', $this->editor->ID ) );
			$this->assertTrue( user_can( $this->admin->ID, 'switch_to_user', $this->author->ID ) );
			$this->assertTrue( user_can( $this->admin->ID, 'switch_to_user', $this->contributor->ID ) );
			$this->assertTrue( user_can( $this->admin->ID, 'switch_to_user', $this->subscriber->ID ) );
			$this->assertTrue( user_can( $this->admin->ID, 'switch_to_user', $this->no_role->ID ) );

			# Admins cannot switch to themselves:
			$this->assertFalse( user_can( $this->admin->ID, 'switch_to_user', $this->admin->ID ) );

			# Admins can switch off:
			$this->assertTrue( user_can( $this->admin->ID, 'switch_off' ) );

		}

	}

	function testEditorCaps() {

		# Editors cannot switch to other users:
		$this->assertFalse( user_can( $this->editor->ID, 'switch_to_user', $this->admin->ID ) );
		$this->assertFalse( user_can( $this->editor->ID, 'switch_to_user', $this->author->ID ) );
		$this->assertFalse( user_can( $this->editor->ID, 'switch_to_user', $this->contributor->ID ) );
		$this->assertFalse( user_can( $this->editor->ID, 'switch_to_user', $this->subscriber->ID ) );
		$this->assertFalse( user_can( $this->editor->ID, 'switch_to_user', $this->no_role->ID ) );

		if ( is_multisite() ) {
			$this->assertFalse( user_can( $this->editor->ID, 'switch_to_user', $this->super->ID ) );
		}

		# Editors cannot switch to themselves:
		$this->assertFalse( user_can( $this->editor->ID, 'switch_to_user', $this->editor->ID ) );

		# Editors cannot switch off:
		$this->assertFalse( user_can( $this->editor->ID, 'switch_off' ) );

	}

	function testAuthorCaps() {

		# Authors cannot switch to other users:
		$this->assertFalse( user_can( $this->author->ID, 'switch_to_user', $this->admin->ID ) );
		$this->assertFalse( user_can( $this->author->ID, 'switch_to_user', $this->editor->ID ) );
		$this->assertFalse( user_can( $this->author->ID, 'switch_to_user', $this->contributor->ID ) );
		$this->assertFalse( user_can( $this->author->ID, 'switch_to_user', $this->subscriber->ID ) );
		$this->assertFalse( user_can( $this->author->ID, 'switch_to_user', $this->no_role->ID ) );

		if ( is_multisite() ) {
			$this->assertFalse( user_can( $this->author->ID, 'switch_to_user', $this->super->ID ) );
		}

		# Authors cannot switch to themselves:
		$this->assertFalse( user_can( $this->author->ID, 'switch_to_user', $this->author->ID ) );

		# Authors cannot switch off:
		$this->assertFalse( user_can( $this->author->ID, 'switch_off' ) );

	}

	function testContributorCaps() {

		# Contributors cannot switch to other users:
		$this->assertFalse( user_can( $this->contributor->ID, 'switch_to_user', $this->admin->ID ) );
		$this->assertFalse( user_can( $this->contributor->ID, 'switch_to_user', $this->editor->ID ) );
		$this->assertFalse( user_can( $this->contributor->ID, 'switch_to_user', $this->author->ID ) );
		$this->assertFalse( user_can( $this->contributor->ID, 'switch_to_user', $this->subscriber->ID ) );
		$this->assertFalse( user_can( $this->contributor->ID, 'switch_to_user', $this->no_role->ID ) );

		if ( is_multisite() ) {
			$this->assertFalse( user_can( $this->contributor->ID, 'switch_to_user', $this->super->ID ) );
		}

		# Contributors cannot switch to themselves:
		$this->assertFalse( user_can( $this->contributor->ID, 'switch_to_user', $this->contributor->ID ) );

		# Contributors cannot switch off:
		$this->assertFalse( user_can( $this->contributor->ID, 'switch_off' ) );

	}

	function testSubscriberCaps() {

		# Subscribers cannot switch to other users:
		$this->assertFalse( user_can( $this->subscriber->ID, 'switch_to_user', $this->admin->ID ) );
		$this->assertFalse( user_can( $this->subscriber->ID, 'switch_to_user', $this->editor->ID ) );
		$this->assertFalse( user_can( $this->subscriber->ID, 'switch_to_user', $this->author->ID ) );
		$this->assertFalse( user_can( $this->subscriber->ID, 'switch_to_user', $this->contributor->ID ) );
		$this->assertFalse( user_can( $this->subscriber->ID, 'switch_to_user', $this->no_role->ID ) );

		if ( is_multisite() ) {
			$this->assertFalse( user_can( $this->subscriber->ID, 'switch_to_user', $this->super->ID ) );
		}

		# Subscribers cannot switch to themselves:
		$this->assertFalse( user_can( $this->subscriber->ID, 'switch_to_user', $this->subscriber->ID ) );

		# Subscribers cannot switch off:
		$this->assertFalse( user_can( $this->subscriber->ID, 'switch_off' ) );

	}

	function testNoRoleCaps() {

		# Users with no role cannot switch to other users:
		$this->assertFalse( user_can( $this->no_role->ID, 'switch_to_user', $this->admin->ID ) );
		$this->assertFalse( user_can( $this->no_role->ID, 'switch_to_user', $this->editor->ID ) );
		$this->assertFalse( user_can( $this->no_role->ID, 'switch_to_user', $this->author->ID ) );
		$this->assertFalse( user_can( $this->no_role->ID, 'switch_to_user', $this->contributor->ID ) );
		$this->assertFalse( user_can( $this->no_role->ID, 'switch_to_user', $this->subscriber->ID ) );

		if ( is_multisite() ) {
			$this->assertFalse( user_can( $this->no_role->ID, 'switch_to_user', $this->super->ID ) );
		}

		# Users with no role cannot switch to themselves:
		$this->assertFalse( user_can( $this->no_role->ID, 'switch_to_user', $this->no_role->ID ) );

		# Users with no role cannot switch off:
		$this->assertFalse( user_can( $this->no_role->ID, 'switch_off' ) );

	}

}
