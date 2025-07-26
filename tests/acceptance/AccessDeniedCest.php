<?php declare(strict_types = 1);

namespace UserSwitching\Tests;

/**
 * Acceptance tests for "access denied" scenarios.
 */
final class AccessDeniedCest {
	public function _before( \AcceptanceTester $I ): void {
		$I->comment( 'As a user who has switched accounts' );
		$I->comment( 'I want to see a Switch Back link on "access denied" screens' );
		$I->comment( 'In order to quickly switch back to my original account' );
	}

	public function SwitchBackFromPageAccessDenied( \AcceptanceTester $I ): void {
		$I->loginAsAdmin();
		$I->haveUserInDatabase( 'editor', 'editor' );
		$I->switchToUser( 'editor' );
		$I->amOnAdminPage( '/tools.php?page=foo' );
		$I->seeInTitle( 'Error' );
		$I->switchBackTo( 'admin' );
		$I->seeCurrentUrlEquals( '/wp-admin/tools.php?page=foo&user_switched=true&switched_back=true' );
	}
}
