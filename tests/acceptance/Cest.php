<?php
/**
 * Base acceptance test class.
 */

class Cest {
	public function _before( AcceptanceTester $I ) {
		$I->cli( 'db reset --yes' );

		# Install WordPress:
		$I->cli( 'core install --title="Example" --admin_user="admin" --admin_password="admin" --admin_email="admin@example.com" --skip-email' );

		# Activate the plugin:
		$I->cli( 'plugin activate user-switching' );

		# Install language files:
		$I->cli( 'language core install it_IT' );
		$I->cli( 'language plugin install user-switching it_IT' );
	}
}
