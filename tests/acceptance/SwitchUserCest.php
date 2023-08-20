<?php
/**
 * Acceptance tests for switching users.
 */

class SwitchUserCest {
	public function _before( AcceptanceTester $I ) {
		$I->comment( 'As an administrator' );
		$I->comment( 'I need to be able to switch between users' );
		$I->comment( 'In order to access different user accounts' );
	}

	public function SwitchToEditorThenBackFromFrontEnd( AcceptanceTester $I ) {
		$I->loginAsAdmin();
		$I->haveUserInDatabase( 'editor', 'editor' );

		$I->switchToUser( 'editor' );
		$I->seeCurrentUrlEquals( '/wp-admin/' );
		$I->seeAdminSuccessNotice( 'Switched to editor.' );
		$I->amLoggedInAs( 'editor' );

		$I->amOnPage( '/' );
		$I->switchBackTo( 'admin' );
		$I->seeCurrentUrlEquals( '/?user_switched=true&switched_back=true' );
		$I->amLoggedInAs( 'admin' );
	}

	public function SwitchToEditorThenBackFromAdminArea( AcceptanceTester $I ) {
		$I->loginAsAdmin();
		$I->haveUserInDatabase( 'editor', 'editor' );

		$I->switchToUser( 'editor' );
		$I->seeCurrentUrlEquals( '/wp-admin/' );
		$I->seeAdminSuccessNotice( 'Switched to editor.' );
		$I->amLoggedInAs( 'editor' );

		$I->amOnAdminPage( 'tools.php' );
		$I->switchBackTo( 'admin' );
		$I->seeCurrentUrlEquals( '/wp-admin/tools.php' );
		$I->seeAdminSuccessNotice( 'Switched back to admin.' );
		$I->amLoggedInAs( 'admin' );
	}
}
