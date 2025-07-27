<?php declare(strict_types = 1);

namespace UserSwitching\Tests;

use Codeception\Scenario;

/**
 * Acceptance tests for switching from a user who uses English to a user who doesn't
 */
final class SwitchFromEnglishCest {
	public function _before( \AcceptanceTester $I ): void {
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

	public function SwitchFromEnglishAdminToItalianAuthorAndBack( \AcceptanceTester $I, Scenario $scenario ): void {
		require dirname( __DIR__, 2 ) . '/vendor/wordpress/wordpress/wp-includes/version.php';

		/** @var string $wp_version */

		$I->comment( sprintf( 'Running test on WordPress version %s', $wp_version ) );

		if ( version_compare( $wp_version, '6.2', '<' ) ) {
			$scenario->skip( 'This test requires WordPress 6.2 or later.' );
		}

		$I->loginAsAdmin();
		$I->switchToUser( 'autore' );
		$I->canSeeThePageInLanguage( 'it-IT' );
		$I->seeAdminSuccessNotice( 'Switched to Autore.' );
		$I->canSeeTheElementInLanguage( '#user_switching p', 'en-US' );

		$I->amOnAdminPage( '/' );
		$I->switchBackTo( 'admin' );
		$I->canSeeThePageInLanguage( 'en-US' );
		$I->seeAdminSuccessNotice( 'Switched back to admin.' );
	}
}
