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
	}

	public function SwitchOffFromDashboardAndBackFromFrontEnd( AcceptanceTester $I ) {
		$I->loginAsAdmin();
		$I->amOnAdminPage( '/' );
		$I->switchOff();
		$I->amLoggedOut();
		$I->seeCurrentUrlEquals( '?switched_off=true' );

		$I->switchBack( 'admin' );
		$I->seeCurrentUrlEquals( '/?user_switched=true&switched_back=true' );
		$I->amLoggedInAs( 'admin' );
	}

	public function SwitchOffFromDashboardAndBackFromLoginScreen( AcceptanceTester $I ) {
		$I->loginAsAdmin();
		$I->amOnAdminPage( '/' );
		$I->switchOff();
		$I->amLoggedOut();
		$I->seeCurrentUrlEquals( '?switched_off=true' );

		$I->amOnPage( 'wp-login.php' );
		$I->switchBack( 'admin' );
		$I->seeCurrentUrlEquals( '/wp-admin/users.php?user_switched=true&switched_back=true' );
		$I->seeAdminSuccessNotice( 'Switched back to admin (admin)' );
		$I->amLoggedInAs( 'admin' );
	}

	public function SwitchOffFromPublishedPostEditingScreen( AcceptanceTester $I ) {
		$I->loginAsAdmin();
		$id = $I->havePostInDatabase( [
			'post_type' => 'post',
			'post_status' => 'publish',
			'post_name' => 'hello-world',
		] );
		$I->amEditingPostWithId( $id );
		$I->switchOff();
		$I->amLoggedOut();
		$I->seeCurrentUrlEquals( '/hello-world?switched_off=true' );
	}
}
