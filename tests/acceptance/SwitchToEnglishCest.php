<?php declare(strict_types = 1);

namespace UserSwitching\Tests;

use Codeception\Scenario;

/**
 * Acceptance tests for switching from a user who doesn't use English to a user who does
 */
final class SwitchToEnglishCest {
	public function _before( \AcceptanceTester $I ): void {
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

	public function SwitchFromItalianAdminToEnglishAuthorAndBack( \AcceptanceTester $I, Scenario $scenario ): void {
		$version = (string) $I->cli( ['core', 'version'] );

		if ( version_compare( $version, '6.2', '<' ) ) {
			$scenario->skip( 'This test requires WordPress 6.2 or later.' );
		}

		$I->loginAs( 'admin_it', 'admin_it' );
		$I->switchToUser( 'author_en' );
		$I->canSeeThePageInLanguage( 'en-US' );
		$I->seeAdminSuccessNotice( 'Cambiato a Author EN.' );
		$I->canSeeTheElementInLanguage( '#user_switching p', 'it-IT' );

		$I->amOnAdminPage( '/' );
		$I->switchBackTo( 'admin_it', 'it-IT' );
		$I->canSeeThePageInLanguage( 'it-IT' );
		$I->seeAdminSuccessNotice( 'Tornato a Admin IT.' );
	}
}
