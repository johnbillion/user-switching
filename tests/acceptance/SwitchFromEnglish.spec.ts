import { test, expect } from './utils/test-setup.js';

test.describe( 'Switch From English', () => {
	test.beforeAll( async ( { globalUtils } ) => {
		// Install WordPress fresh for this test suite
		globalUtils.installWordPress();
	} );

	test( 'Switch from English admin to Italian author and back', {
		annotation: {
			type: 'user-story',
			description: 'As an administrator of a site which uses more than one language, I need to be able to switch to user accounts that use a different language, and see the output of User Switching in my original language'
		}
	}, async ( {
		page,
		admin,
		userSwitching,
		globalUtils,
	}, testInfo ) => {
		// Check WordPress version - this test requires 6.2+
		testInfo.skip( ! globalUtils.isWordPressVersionAtLeast( 6.2 ), 'This test requires WordPress 6.2 or later' );

		// Create Italian author user
		userSwitching.createUser( 'autore', 'author', {
			name: 'Autore',
			locale: 'it_IT',
		} );

		// Login as admin (English)
		await userSwitching.loginViaPage( 'admin', 'password' );
		await admin.visitAdminPage( '/' );

		// Switch to Italian author
		await userSwitching.switchToUser( 'autore' );
		await userSwitching.canSeeThePageInLanguage( 'it-IT' );
		await userSwitching.seeAdminSuccessNotice( 'Switched to Autore.' );

		// The user switching element should be in English
		const switchingElement = page.locator( '#user_switching p' );
		await expect( switchingElement ).toHaveAttribute( 'lang', 'en-US' );

		// Go to dashboard
		await admin.visitAdminPage( '/' );

		// Switch back to English admin
		await userSwitching.switchBackTo( 'admin' );
		await userSwitching.canSeeThePageInLanguage( 'en-US' );
		await userSwitching.seeAdminSuccessNotice( 'Switched back to admin.' );
	} );
} );
