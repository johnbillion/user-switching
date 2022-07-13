<?php
/**
 * Acceptance tests for switching off.
 */

class SwitchOffCest extends Cest {
	public function _before( AcceptanceTester $I ) {
		parent::_before( $I );

		$I->comment( 'As an administrator' );
		$I->comment( 'I need to be able to switch off' );
		$I->comment( 'In order to view the site without logging out completely' );

		$I->haveUserInDatabase( 'editor', 'editor' );
	}

	public function SwitchOffFromDashboardAndBackFromFrontEnd( AcceptanceTester $I ) {
		$I->loginAsAdmin();
		$I->amOnAdminPage( '/' );
		$I->switchOff();
		$I->amLoggedOut();

		$I->switchBack( 'admin' );
		$I->amLoggedInAs( 'admin' );
		$I->dontSeeInCurrentUrl( '/wp-admin' );
	}

	public function SwitchOffFromDashboardAndBackFromLoginScreen( AcceptanceTester $I ) {
		$I->loginAsAdmin();
		$I->amOnAdminPage( '/' );
		$I->switchOff();
		$I->amLoggedOut();

		$I->amOnPage( 'wp-login.php' ); // $I->amOnPage($I->getLoginUrl());
		$I->switchBack( 'admin' );
		$I->seeAdminSuccessNotice( 'Switched back to admin (admin)' );
		$I->amLoggedInAs( 'admin' );
	}
}
