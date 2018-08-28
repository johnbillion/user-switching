<?php

class User_Switching_Test_Caps extends User_Switching_Test {

	/**
	 * @group multisite
	 * @group ms-required
	 */
	function testSuperAdminCaps() {

		# Super Admins can switch to all users:
		$this->assertTrue( user_can( $this->testers['super']->ID, 'switch_to_user', $this->users['super']->ID ) );
		$this->assertTrue( user_can( $this->testers['super']->ID, 'switch_to_user', $this->users['admin']->ID ) );
		$this->assertTrue( user_can( $this->testers['super']->ID, 'switch_to_user', $this->users['editor']->ID ) );
		$this->assertTrue( user_can( $this->testers['super']->ID, 'switch_to_user', $this->users['author']->ID ) );
		$this->assertTrue( user_can( $this->testers['super']->ID, 'switch_to_user', $this->users['contributor']->ID ) );
		$this->assertTrue( user_can( $this->testers['super']->ID, 'switch_to_user', $this->users['subscriber']->ID ) );
		$this->assertTrue( user_can( $this->testers['super']->ID, 'switch_to_user', $this->users['no_role']->ID ) );

		# Super Admins cannot switch to themselves:
		$this->assertFalse( user_can( $this->testers['super']->ID, 'switch_to_user', $this->testers['super']->ID ) );

		# Super Admins can switch off:
		$this->assertTrue( user_can( $this->testers['super']->ID, 'switch_off' ) );

	}

	function testAdminCaps() {

		if ( is_multisite() ) {

			# Admins cannot switch to other users:
			$this->assertFalse( user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['super']->ID ) );
			$this->assertFalse( user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['admin']->ID ) );
			$this->assertFalse( user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['editor']->ID ) );
			$this->assertFalse( user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['author']->ID ) );
			$this->assertFalse( user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['contributor']->ID ) );
			$this->assertFalse( user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['subscriber']->ID ) );
			$this->assertFalse( user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['no_role']->ID ) );

			# Admins cannot switch to themselves:
			$this->assertFalse( user_can( $this->testers['admin']->ID, 'switch_to_user', $this->testers['admin']->ID ) );

			# Admins cannot switch off:
			$this->assertFalse( user_can( $this->testers['admin']->ID, 'switch_off' ) );

		} else {

			# Admins can switch to all users:
			$this->assertTrue( user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['admin']->ID ) );
			$this->assertTrue( user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['editor']->ID ) );
			$this->assertTrue( user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['author']->ID ) );
			$this->assertTrue( user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['contributor']->ID ) );
			$this->assertTrue( user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['subscriber']->ID ) );
			$this->assertTrue( user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['no_role']->ID ) );

			# Admins cannot switch to themselves:
			$this->assertFalse( user_can( $this->testers['admin']->ID, 'switch_to_user', $this->testers['admin']->ID ) );

			# Admins can switch off:
			$this->assertTrue( user_can( $this->testers['admin']->ID, 'switch_off' ) );

		}

	}

	function testEditorCaps() {

		# Editors cannot switch to other users:
		$this->assertFalse( user_can( $this->testers['editor']->ID, 'switch_to_user', $this->users['admin']->ID ) );
		$this->assertFalse( user_can( $this->testers['editor']->ID, 'switch_to_user', $this->users['editor']->ID ) );
		$this->assertFalse( user_can( $this->testers['editor']->ID, 'switch_to_user', $this->users['author']->ID ) );
		$this->assertFalse( user_can( $this->testers['editor']->ID, 'switch_to_user', $this->users['contributor']->ID ) );
		$this->assertFalse( user_can( $this->testers['editor']->ID, 'switch_to_user', $this->users['subscriber']->ID ) );
		$this->assertFalse( user_can( $this->testers['editor']->ID, 'switch_to_user', $this->users['no_role']->ID ) );

		if ( is_multisite() ) {
			$this->assertFalse( user_can( $this->testers['editor']->ID, 'switch_to_user', $this->users['super']->ID ) );
		}

		# Editors cannot switch to themselves:
		$this->assertFalse( user_can( $this->testers['editor']->ID, 'switch_to_user', $this->testers['editor']->ID ) );

		# Editors cannot switch off:
		$this->assertFalse( user_can( $this->testers['editor']->ID, 'switch_off' ) );

	}

	function testAuthorCaps() {

		# Authors cannot switch to other users:
		$this->assertFalse( user_can( $this->testers['author']->ID, 'switch_to_user', $this->users['admin']->ID ) );
		$this->assertFalse( user_can( $this->testers['author']->ID, 'switch_to_user', $this->users['editor']->ID ) );
		$this->assertFalse( user_can( $this->testers['author']->ID, 'switch_to_user', $this->users['author']->ID ) );
		$this->assertFalse( user_can( $this->testers['author']->ID, 'switch_to_user', $this->users['contributor']->ID ) );
		$this->assertFalse( user_can( $this->testers['author']->ID, 'switch_to_user', $this->users['subscriber']->ID ) );
		$this->assertFalse( user_can( $this->testers['author']->ID, 'switch_to_user', $this->users['no_role']->ID ) );

		if ( is_multisite() ) {
			$this->assertFalse( user_can( $this->testers['author']->ID, 'switch_to_user', $this->users['super']->ID ) );
		}

		# Authors cannot switch to themselves:
		$this->assertFalse( user_can( $this->testers['author']->ID, 'switch_to_user', $this->testers['author']->ID ) );

		# Authors cannot switch off:
		$this->assertFalse( user_can( $this->testers['author']->ID, 'switch_off' ) );

	}

	function testContributorCaps() {

		# Contributors cannot switch to other users:
		$this->assertFalse( user_can( $this->testers['contributor']->ID, 'switch_to_user', $this->users['admin']->ID ) );
		$this->assertFalse( user_can( $this->testers['contributor']->ID, 'switch_to_user', $this->users['editor']->ID ) );
		$this->assertFalse( user_can( $this->testers['contributor']->ID, 'switch_to_user', $this->users['author']->ID ) );
		$this->assertFalse( user_can( $this->testers['contributor']->ID, 'switch_to_user', $this->users['contributor']->ID ) );
		$this->assertFalse( user_can( $this->testers['contributor']->ID, 'switch_to_user', $this->users['subscriber']->ID ) );
		$this->assertFalse( user_can( $this->testers['contributor']->ID, 'switch_to_user', $this->users['no_role']->ID ) );

		if ( is_multisite() ) {
			$this->assertFalse( user_can( $this->testers['contributor']->ID, 'switch_to_user', $this->users['super']->ID ) );
		}

		# Contributors cannot switch to themselves:
		$this->assertFalse( user_can( $this->testers['contributor']->ID, 'switch_to_user', $this->testers['contributor']->ID ) );

		# Contributors cannot switch off:
		$this->assertFalse( user_can( $this->testers['contributor']->ID, 'switch_off' ) );

	}

	function testSubscriberCaps() {

		# Subscribers cannot switch to other users:
		$this->assertFalse( user_can( $this->testers['subscriber']->ID, 'switch_to_user', $this->users['admin']->ID ) );
		$this->assertFalse( user_can( $this->testers['subscriber']->ID, 'switch_to_user', $this->users['editor']->ID ) );
		$this->assertFalse( user_can( $this->testers['subscriber']->ID, 'switch_to_user', $this->users['author']->ID ) );
		$this->assertFalse( user_can( $this->testers['subscriber']->ID, 'switch_to_user', $this->users['contributor']->ID ) );
		$this->assertFalse( user_can( $this->testers['subscriber']->ID, 'switch_to_user', $this->users['subscriber']->ID ) );
		$this->assertFalse( user_can( $this->testers['subscriber']->ID, 'switch_to_user', $this->users['no_role']->ID ) );

		if ( is_multisite() ) {
			$this->assertFalse( user_can( $this->testers['subscriber']->ID, 'switch_to_user', $this->users['super']->ID ) );
		}

		# Subscribers cannot switch to themselves:
		$this->assertFalse( user_can( $this->testers['subscriber']->ID, 'switch_to_user', $this->testers['subscriber']->ID ) );

		# Subscribers cannot switch off:
		$this->assertFalse( user_can( $this->testers['subscriber']->ID, 'switch_off' ) );

	}

	function testNoRoleCaps() {

		# Users with no role cannot switch to other users:
		$this->assertFalse( user_can( $this->testers['no_role']->ID, 'switch_to_user', $this->users['admin']->ID ) );
		$this->assertFalse( user_can( $this->testers['no_role']->ID, 'switch_to_user', $this->users['editor']->ID ) );
		$this->assertFalse( user_can( $this->testers['no_role']->ID, 'switch_to_user', $this->users['author']->ID ) );
		$this->assertFalse( user_can( $this->testers['no_role']->ID, 'switch_to_user', $this->users['contributor']->ID ) );
		$this->assertFalse( user_can( $this->testers['no_role']->ID, 'switch_to_user', $this->users['subscriber']->ID ) );
		$this->assertFalse( user_can( $this->testers['no_role']->ID, 'switch_to_user', $this->users['no_role']->ID ) );

		if ( is_multisite() ) {
			$this->assertFalse( user_can( $this->testers['no_role']->ID, 'switch_to_user', $this->users['super']->ID ) );
		}

		# Users with no role cannot switch to themselves:
		$this->assertFalse( user_can( $this->testers['no_role']->ID, 'switch_to_user', $this->testers['no_role']->ID ) );

		# Users with no role cannot switch off:
		$this->assertFalse( user_can( $this->testers['no_role']->ID, 'switch_off' ) );

	}

	public function testAbilityToSwitchUsersCanBeGrantedToUser() {
		# Editors cannot switch to other users:
		$can_already_switch = user_can( $this->testers['editor']->ID, 'switch_to_user', $this->users['admin']->ID );

		# Grant the ability for this user to switch users:
		$this->testers['editor']->add_cap( 'switch_users' );

		# Ensure the user can switch:
		$can_switch_user = user_can( $this->testers['editor']->ID, 'switch_to_user', $this->users['admin']->ID );
		$can_switch_off  = user_can( $this->testers['editor']->ID, 'switch_off' );

		# Revert the cap:
		$this->testers['editor']->remove_cap( 'switch_users' );

		# Assert:
		$this->assertFalse( $can_already_switch );
		$this->assertTrue( $can_switch_user );
		$this->assertTrue( $can_switch_off );
	}

	public function testAbilityToSwitchUsersCanBeGrantedToRole() {
		# Editors cannot switch to other users:
		$can_already_switch = user_can( $this->testers['editor']->ID, 'switch_to_user', $this->users['admin']->ID );

		# Grant the ability for this role to switch users:
		get_role( 'editor' )->add_cap( 'switch_users' );

		# Ensure the user can switch:
		$can_switch_user = user_can( $this->testers['editor']->ID, 'switch_to_user', $this->users['admin']->ID );
		$can_switch_off  = user_can( $this->testers['editor']->ID, 'switch_off' );

		# Revert the cap:
		get_role( 'editor' )->remove_cap( 'switch_users' );

		# Assert:
		$this->assertFalse( $can_already_switch );
		$this->assertTrue( $can_switch_user );
		$this->assertTrue( $can_switch_off );
	}

	/**
	 * @group ms-excluded
	 */
	public function testAbiliityToSwitchUsersCanBeDeniedFromUser() {
		# Admins can switch to other users:
		$can_already_switch = user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['author']->ID );

		# Revoke the ability for this role to switch users:
		$this->testers['admin']->add_cap( 'switch_users', false );

		# Ensure the user can no longer switch:
		$can_switch_user = user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['author']->ID );
		$can_switch_off  = user_can( $this->testers['admin']->ID, 'switch_off' );

		# Revert the cap:
		$this->testers['admin']->remove_cap( 'switch_users' );

		# Assert:
		$this->assertTrue( $can_already_switch );
		$this->assertFalse( $can_switch_user );
		$this->assertFalse( $can_switch_off );
	}

	/**
	 * @group ms-excluded
	 */
	public function testAbiliityToSwitchUsersCanBeDeniedFromRole() {
		# Admins can switch to other users:
		$can_already_switch = user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['author']->ID );

		# Revoke the ability for this role to switch users:
		get_role( 'administrator' )->add_cap( 'switch_users', false );

		# Ensure the user can no longer switch:
		$can_switch_user = user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['author']->ID );
		$can_switch_off  = user_can( $this->testers['admin']->ID, 'switch_off' );

		# Revert the cap:
		get_role( 'administrator' )->remove_cap( 'switch_users' );

		# Assert:
		$this->assertTrue( $can_already_switch );
		$this->assertFalse( $can_switch_user );
		$this->assertFalse( $can_switch_off );
	}

	/**
	 * @group multisite
	 * @group ms-required
	 */
	public function testAbiliityToSwitchUsersCanBeGrantedToAdministratorRoleOnMultisite() {
		# Admins on Multisite cannot switch to other users:
		$can_already_switch = user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['author']->ID );

		# Grant the ability for this role to switch users:
		get_role( 'administrator' )->add_cap( 'switch_users' );

		# Ensure the user can switch:
		$can_switch_user = user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['author']->ID );
		$can_switch_off  = user_can( $this->testers['admin']->ID, 'switch_off' );

		# Revert the cap:
		get_role( 'administrator' )->remove_cap( 'switch_users' );

		# Assert:
		$this->assertFalse( $can_already_switch );
		$this->assertTrue( $can_switch_user );
		$this->assertTrue( $can_switch_off );
	}

	/**
	 * @group multisite
	 * @group ms-required
	 */
	public function testAbiliityToSwitchUsersCanBeGrantedToAdministratorUserOnMultisite() {
		# Admins on Multisite cannot switch to other users:
		$can_already_switch = user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['author']->ID );

		# Grant the ability for this role to switch users:
		$this->testers['admin']->add_cap( 'switch_users' );

		# Ensure the user can switch:
		$can_switch_user = user_can( $this->testers['admin']->ID, 'switch_to_user', $this->users['author']->ID );
		$can_switch_off  = user_can( $this->testers['admin']->ID, 'switch_off' );

		# Revert the cap:
		$this->testers['admin']->remove_cap( 'switch_users' );

		# Assert:
		$this->assertFalse( $can_already_switch );
		$this->assertTrue( $can_switch_user );
		$this->assertTrue( $can_switch_off );
	}

}
