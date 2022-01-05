<?php
/**
 * Acceptance tests for switching from a user who uses English to a user who doesn't
 */

/**
 * Test class.
 */
class SwitchFromEnglishCest {
	public function _before( AcceptanceTester $I ) {
		$I->comment( 'As an administrator of a site which uses more than one language' );
		$I->comment( 'I need to be able to switch to user accounts that use a different language' );
		$I->comment( 'And see the output of User Switching in my original language' );

		$I->haveUserInDatabase( 'autore', 'author', [
			'display_name' => 'Autore',
			'meta' => [
				'locale' => 'it_IT',
			],
		] );
	}

	public function SwitchFromEnglishAdminToItalianAuthorAndBack( AcceptanceTester $I ) {
		// Given I am logged in as admin
		$I->loginAsAdmin();
		// When I switch to user "autore"
		$I->switchToUser( 'autore' );
		// Then the page language should be "it-IT"
		$I->thePageLanguageShouldBe( 'it-IT' );
		// But I should see a status message that says "Switched to Autore"
		$I->seeAdminSuccessNotice( 'Switched to Autore' );
		// And the "#user_switching p" element language should be "en-US"
		$I->theElementLanguageShouldBe( '#user_switching p', 'en-US' );

		// When I go to the dashboard
		$I->amOnAdminPage( '/' );
		// And I switch back to "admin"
		$I->switchBack( 'admin' );
		// Then the page language should be "en-US"
		$I->thePageLanguageShouldBe( 'en-US' );
		// And I should see a status message that says "Switched back to admin"
		$I->seeAdminSuccessNotice( 'Switched back to admin' );
	}
}
