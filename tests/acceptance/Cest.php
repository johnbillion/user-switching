<?php
/**
 * Base acceptance test class.
 */

class Cest {
	public function _before( AcceptanceTester $I ) {
		$I->cli( 'db reset --yes' );

		# Install WordPress:
		$I->cli( 'core install --title="Example" --admin_user="admin" --admin_password="admin" --admin_email="admin@example.com" --skip-email' );
		$I->cli( 'rewrite structure "%postname%"' );

		# Activate the plugin:
		$I->cli( 'plugin activate user-switching' );
	}
}
