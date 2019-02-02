<?php

class User_Switching_Test_Caps extends User_Switching_Test {

	public function data_roles() {
		$roles = [
			[
				'admin',
				! is_multisite(),
			],
			[
				'editor',
				false,
			],
			[
				'author',
				false,
			],
			[
				'contributor',
				false,
			],
			[
				'subscriber',
				false,
			],
			[
				'no_role',
				false,
			],
		];

		if ( is_multisite() ) {
			$roles[] = [
				'super',
				true,
			];
		}

		return $roles;
	}

	public function testAllRolesAreTested() {
		$tested_roles = array_column( $this->data_roles(), 0 );

		$this->assertSame( array_keys( self::$testers ), $tested_roles );
		$this->assertSame( array_keys( self::$users ), $tested_roles );
	}

	/**
	 * @dataProvider data_roles
	 */
	public function testUserCanOrCannotSwitchAccordingToRole( string $role, bool $can_switch ) {
		foreach ( self::$users as $user_role => $user ) {
			if ( self::$testers[ $role ]->ID === $user->ID ) {
				# No user can switch to themselves:
				$this->assertFalse( user_can( self::$testers[ $role ]->ID, 'switch_to_user', $user->ID ), 'User should not be able to switch to themselves' );
			} else {
				# Can the user switch?
				$this->assertSame( $can_switch, user_can( self::$testers[ $role ]->ID, 'switch_to_user', $user->ID ), sprintf(
					'Broken user switching capability. Destination role: %s',
					$user_role
				) );
			}
		}

		# Can the user switch off?
		$this->assertSame( $can_switch, user_can( self::$testers[ $role ]->ID, 'switch_off' ) );
	}

	public function testAbilityToSwitchUsersCanBeGrantedToUser() {
		# Editors cannot switch to other users:
		$can_already_switch = user_can( self::$testers['editor']->ID, 'switch_to_user', self::$users['admin']->ID );

		# Grant the ability for this user to switch users:
		self::$testers['editor']->add_cap( 'switch_users' );

		# Ensure the user can switch:
		$can_switch_user = user_can( self::$testers['editor']->ID, 'switch_to_user', self::$users['admin']->ID );
		$can_switch_off  = user_can( self::$testers['editor']->ID, 'switch_off' );

		# Revert the cap:
		self::$testers['editor']->remove_cap( 'switch_users' );

		# Assert:
		$this->assertFalse( $can_already_switch );
		$this->assertTrue( $can_switch_user );
		$this->assertTrue( $can_switch_off );
	}

	public function testAbilityToSwitchUsersCanBeGrantedToRole() {
		# Editors cannot switch to other users:
		$can_already_switch = user_can( self::$testers['editor']->ID, 'switch_to_user', self::$users['admin']->ID );

		# Grant the ability for this role to switch users:
		get_role( 'editor' )->add_cap( 'switch_users' );

		# Ensure the user can switch:
		$can_switch_user = user_can( self::$testers['editor']->ID, 'switch_to_user', self::$users['admin']->ID );
		$can_switch_off  = user_can( self::$testers['editor']->ID, 'switch_off' );

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
		$can_already_switch = user_can( self::$testers['admin']->ID, 'switch_to_user', self::$users['author']->ID );

		# Revoke the ability for this role to switch users:
		self::$testers['admin']->add_cap( 'switch_users', false );

		# Ensure the user can no longer switch:
		$can_switch_user = user_can( self::$testers['admin']->ID, 'switch_to_user', self::$users['author']->ID );
		$can_switch_off  = user_can( self::$testers['admin']->ID, 'switch_off' );

		# Revert the cap:
		self::$testers['admin']->remove_cap( 'switch_users' );

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
		$can_already_switch = user_can( self::$testers['admin']->ID, 'switch_to_user', self::$users['author']->ID );

		# Revoke the ability for this role to switch users:
		get_role( 'administrator' )->add_cap( 'switch_users', false );

		# Ensure the user can no longer switch:
		$can_switch_user = user_can( self::$testers['admin']->ID, 'switch_to_user', self::$users['author']->ID );
		$can_switch_off  = user_can( self::$testers['admin']->ID, 'switch_off' );

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
		$can_already_switch = user_can( self::$testers['admin']->ID, 'switch_to_user', self::$users['author']->ID );

		# Grant the ability for this role to switch users:
		get_role( 'administrator' )->add_cap( 'switch_users' );

		# Ensure the user can switch:
		$can_switch_user = user_can( self::$testers['admin']->ID, 'switch_to_user', self::$users['author']->ID );
		$can_switch_off  = user_can( self::$testers['admin']->ID, 'switch_off' );

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
		$can_already_switch = user_can( self::$testers['admin']->ID, 'switch_to_user', self::$users['author']->ID );

		# Grant the ability for this role to switch users:
		self::$testers['admin']->add_cap( 'switch_users' );

		# Ensure the user can switch:
		$can_switch_user = user_can( self::$testers['admin']->ID, 'switch_to_user', self::$users['author']->ID );
		$can_switch_off  = user_can( self::$testers['admin']->ID, 'switch_off' );

		# Revert the cap:
		self::$testers['admin']->remove_cap( 'switch_users' );

		# Assert:
		$this->assertFalse( $can_already_switch );
		$this->assertTrue( $can_switch_user );
		$this->assertTrue( $can_switch_off );
	}

}
