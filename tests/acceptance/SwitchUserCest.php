<?php
/**
 * Acceptance tests for switching users.
 */

class SwitchUserCest extends Cest {
	public function _before( AcceptanceTester $I ) {
		parent::_before( $I );

		$I->comment( 'As an administrator' );
		$I->comment( 'I need to be able to switch between users' );
		$I->comment( 'In order to access different user accounts' );

		$I->haveUserInDatabase( 'editor', 'editor' );
	}

	public function SwitchToEditorAndBack( AcceptanceTester $I ) {
		$I->loginAsAdmin();
		$I->switchToUser( 'editor' );
		$I->seeAdminSuccessNotice( 'Switched to editor' );
		$I->amLoggedInAs( 'editor' );

		$I->amOnAdminPage( '/' );
		$I->switchBack( 'admin' );
		$I->seeAdminSuccessNotice( 'Switched back to admin' );
		$I->amLoggedInAs( 'admin' );
	}

	public function SwitchOffAndBack( AcceptanceTester $I ) {
		$I->loginAsAdmin();
		$I->switchOff();
		$I->amLoggedOut();

		$I->switchBack( 'admin' );
		$I->amLoggedInAs( 'admin' );
	}
}
