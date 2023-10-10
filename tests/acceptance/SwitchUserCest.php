<?php declare(strict_types = 1);

namespace UserSwitching\Tests;

/**
 * Acceptance tests for switching users.
 */
final class SwitchUserCest {
	public function _before( \AcceptanceTester $I ): void {
		$I->comment( 'As an administrator' );
		$I->comment( 'I need to be able to switch between users' );
		$I->comment( 'In order to access different user accounts' );
	}

	public function SwitchToEditorThenBackFromFrontEnd( \AcceptanceTester $I ): void {
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

	public function SwitchToEditorThenBackFromAdminArea( \AcceptanceTester $I ): void {
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
