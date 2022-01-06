<?php

declare(strict_types = 1);

namespace UserSwitching\Tests;

/**
 * @covers \user_switching::filter_user_has_cap
 * @covers \user_switching::filter_map_meta_cap
 */
class Capabilities extends Test {

	/**
	 * @return array<string, array<int, string|bool>>
	 */
	public function data_roles() {
		$roles = [
			'admin' => [
				'admin',
				! is_multisite(),
			],
			'editor' => [
				'editor',
				false,
			],
			'author' => [
				'author',
				false,
			],
			'contributor' => [
				'contributor',
				false,
			],
			'subscriber' => [
				'subscriber',
				false,
			],
			'none' => [
				'no_role',
				false,
			],
		];

		if ( is_multisite() ) {
			$roles['super admin'] = [
				'super',
				true,
			];
		}

		return $roles;
	}

	public function testAllRolesAreTested() {
		$tested_roles = array_column( $this->data_roles(), 0 );

		self::assertSame( array_keys( self::$testers ), $tested_roles );
		self::assertSame( array_keys( self::$users ), $tested_roles );
	}

	/**
	 * @dataProvider data_roles
	 * @testdox User with role of $role can or cannot switch according to role
	 */
	public function testUserCanOrCannotSwitchAccordingToRole( $role, $can_switch ) {
		foreach ( self::$users as $user_role => $user ) {
			if ( self::$testers[ $role ]->ID === $user->ID ) {
				# No user can switch to themselves:
				self::assertFalse( user_can( self::$testers[ $role ]->ID, 'switch_to_user', $user->ID ), 'User should not be able to switch to themselves' );
			} else {
				# Can the user switch?
				self::assertSame( $can_switch, user_can( self::$testers[ $role ]->ID, 'switch_to_user', $user->ID ), sprintf(
					'Broken user switching capability. Destination role: %s',
					$user_role
				) );
			}
		}

		# Can the user switch off?
		self::assertSame( $can_switch, user_can( self::$testers[ $role ]->ID, 'switch_off' ) );
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
		self::assertFalse( $can_already_switch );
		self::assertTrue( $can_switch_user );
		self::assertTrue( $can_switch_off );
	}

	public function testAbilityToSwitchUsersCanBeGrantedToRole() {
		# Editors cannot switch to other users:
		$can_already_switch = user_can( self::$testers['editor']->ID, 'switch_to_user', self::$users['admin']->ID );

		/** @var \WP_Role */
		$role = get_role( 'editor' );

		# Grant the ability for this role to switch users:
		$role->add_cap( 'switch_users' );

		# Ensure the user can switch:
		$can_switch_user = user_can( self::$testers['editor']->ID, 'switch_to_user', self::$users['admin']->ID );
		$can_switch_off  = user_can( self::$testers['editor']->ID, 'switch_off' );

		# Revert the cap:
		$role->remove_cap( 'switch_users' );

		# Assert:
		self::assertFalse( $can_already_switch );
		self::assertTrue( $can_switch_user );
		self::assertTrue( $can_switch_off );
	}

	/**
	 * @group ms-excluded
	 */
	public function testAbilityToSwitchUsersCanBeDeniedFromUser() {
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
		self::assertTrue( $can_already_switch );
		self::assertFalse( $can_switch_user );
		self::assertFalse( $can_switch_off );
	}

	/**
	 * @group ms-excluded
	 */
	public function testAbilityToSwitchUsersCanBeDeniedFromRole() {
		# Admins can switch to other users:
		$can_already_switch = user_can( self::$testers['admin']->ID, 'switch_to_user', self::$users['author']->ID );

		/** @var \WP_Role */
		$role = get_role( 'administrator' );

		# Revoke the ability for this role to switch users:
		$role->add_cap( 'switch_users', false );

		# Ensure the user can no longer switch:
		$can_switch_user = user_can( self::$testers['admin']->ID, 'switch_to_user', self::$users['author']->ID );
		$can_switch_off  = user_can( self::$testers['admin']->ID, 'switch_off' );

		# Revert the cap:
		$role->remove_cap( 'switch_users' );

		# Assert:
		self::assertTrue( $can_already_switch );
		self::assertFalse( $can_switch_user );
		self::assertFalse( $can_switch_off );
	}

	/**
	 * @group multisite
	 * @group ms-required
	 */
	public function testAbilityToSwitchUsersCanBeGrantedToAdministratorRoleOnMultisite() {
		# Admins on Multisite cannot switch to other users:
		$can_already_switch = user_can( self::$testers['admin']->ID, 'switch_to_user', self::$users['author']->ID );

		/** @var \WP_Role */
		$role = get_role( 'administrator' );

		# Grant the ability for this role to switch users:
		$role->add_cap( 'switch_users' );

		# Ensure the user can switch:
		$can_switch_user = user_can( self::$testers['admin']->ID, 'switch_to_user', self::$users['author']->ID );
		$can_switch_off  = user_can( self::$testers['admin']->ID, 'switch_off' );

		# Revert the cap:
		$role->remove_cap( 'switch_users' );

		# Assert:
		self::assertFalse( $can_already_switch );
		self::assertTrue( $can_switch_user );
		self::assertTrue( $can_switch_off );
	}

	/**
	 * @group multisite
	 * @group ms-required
	 */
	public function testAbilityToSwitchUsersCanBeGrantedToAdministratorUserOnMultisite() {
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
		self::assertFalse( $can_already_switch );
		self::assertTrue( $can_switch_user );
		self::assertTrue( $can_switch_off );
	}

	/**
	 * @dataProvider data_roles
	 * @testdox User with role of $role cannot switch to no user
	 */
	public function testSwitchingToNoUserIsNotAllowed( $role ) {
		self::assertFalse( user_can( self::$testers[ $role ]->ID, 'switch_to_user', 0 ) );
	}

}
