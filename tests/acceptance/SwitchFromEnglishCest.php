<?php
/**
 * Acceptance tests for switching from a user who uses English to a user who doesn't
 */

class SwitchFromEnglishCest extends Cest {
	public function _before( AcceptanceTester $I ) {
		parent::_before( $I );

		$I->comment( 'As an administrator of a site which uses more than one language' );
		$I->comment( 'I need to be able to switch to user accounts that use a different language' );
		$I->comment( 'And see the output of User Switching in my original language' );

		# Install language files:
		$I->cli( 'language core install it_IT' );
		$I->cli( 'language plugin install user-switching it_IT' );

		$I->haveUserInDatabase( 'autore', 'author', [
			'display_name' => 'Autore',
			'meta' => [
				'locale' => 'it_IT',
			],
		] );
	}

	public function SwitchFromEnglishAdminToItalianAuthorAndBack( AcceptanceTester $I ) {
		$I->loginAsAdmin();
		$I->switchToUser( 'autore' );
		$I->canSeeThePageInLanguage( 'it-IT' );
		$I->seeAdminSuccessNotice( 'Switched to Autore' );
		$I->canSeeTheElementInLanguage( '#user_switching p', 'en-US' );

		$I->amOnAdminPage( '/' );
		$I->switchBackTo( 'admin' );
		$I->canSeeThePageInLanguage( 'en-US' );
		$I->seeAdminSuccessNotice( 'Switched back to admin' );
	}
}
