<?php
/**
 * Acceptance tests for switching from a user who doesn't use English to a user who does
 */

/**
 * Test class.
 */
class SwitchToEnglishCest {
	public function _before( AcceptanceTester $I ) {
		$I->comment( 'As an administrator of a site which uses more than one language' );
		$I->comment( 'I need to be able to switch between users' );
		$I->comment( 'And see the output of User Switching in my original language' );

		$I->haveUserInDatabase( 'admin_it', 'administrator', [
			'display_name' => 'Admin IT',
			'meta' => [
				'locale' => 'it_IT',
			],
		] );
		$I->haveUserInDatabase( 'author_en', 'author', [
			'display_name' => 'Author EN',
		] );
	}

	public function SwitchFromItalianAdminToEnglishAuthorAndBack( AcceptanceTester $I ) {
		// Given I am logged in as admin_it
		$I->loginAs( 'admin_it', 'admin_it' );
		// When I switch to user "author_en"
		$I->switchToUser( 'author_en' );
		// Then the page language should be "en-US"
		$I->thePageLanguageShouldBe( 'en-US' );
		// But I should see a status message that says "Cambiato a Author EN"
		$I->seeAdminSuccessNotice( 'Cambiato a Author EN' );
		// And the "#user_switching p" element language should be "it-IT"
		$I->theElementLanguageShouldBe( '#user_switching p', 'it-IT' );

		// When I go to the dashboard
		$I->amOnAdminPage( '/' );
		// And I switch back to "admin_it"
		$I->switchBack( 'admin_it' );
		// Then the page language should be "it-IT"
		$I->thePageLanguageShouldBe( 'it-IT' );
		// And I should see a status message that says "Tornato a Admin IT"
		$I->seeAdminSuccessNotice( 'Tornato a Admin IT' );
	}
}
