import { test, expect } from './utils/test-setup.js';

test.describe( 'Switch To English', () => {
	test.beforeAll( async ( { globalUtils } ) => {
		// Install WordPress fresh for this test suite
		globalUtils.installWordPress();
	} );

	test( 'Switch from Italian admin to English author and back', {
		annotation: {
			type: 'user-story',
			description: 'As an administrator of a site which uses more than one language, I need to be able to switch between users, and see the output of User Switching in my original language'
		}
	}, async ( {
		page,
		admin,
		userSwitching,
		globalUtils,
	}, testInfo ) => {
		// Check WordPress version - this test requires 6.2+
		testInfo.skip( ! globalUtils.isWordPressVersionAtLeast( 6.2 ), 'This test requires WordPress 6.2 or later' );

		// Create Italian admin user
		await userSwitching.createUser( 'admin_it', 'administrator', {
			name: 'Admin IT',
			locale: 'it_IT',
		} );

		// Create English author user
		await userSwitching.createUser( 'author_en', 'author', {
			name: 'Author EN',
		} );

		// Login as Italian admin
		await userSwitching.loginViaPage( 'admin_it', 'password' );

		// Switch to English author
		await userSwitching.switchToUser( 'author_en' );
		await userSwitching.canSeeThePageInLanguage( 'en-US' );
		await userSwitching.seeAdminSuccessNotice( 'Cambiato a Author EN.' );

		// The user switching element should be in Italian
		const switchingElement = page.locator( '#user_switching p' );
		await expect( switchingElement ).toHaveAttribute( 'lang', 'it-IT' );

		// Go to dashboard
		await admin.visitAdminPage( '/' );

		// Switch back to Italian admin
		await userSwitching.switchBackTo( 'admin_it', 'it-IT' );
		await userSwitching.canSeeThePageInLanguage( 'it-IT' );
		await userSwitching.seeAdminSuccessNotice( 'Tornato a Admin IT.' );
	} );
} );
