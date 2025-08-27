import { test, expect } from './utils/test-setup';

test.describe( 'Switch To English', () => {
	test.describe( 'WordPress 6.2+', () => {
		test.beforeAll( async () => {
			// Check WordPress version - these tests require 6.2+
			// This would need to be implemented based on actual WP version check
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
		} ) => {
			// Create Italian admin user
			await userSwitching.createUser( 'admin_it', 'administrator', {
				first_name: 'Admin',
				last_name: 'IT',
			} );

			// Create English author user
			await userSwitching.createUser( 'author_en', 'author', {
				first_name: 'Author',
				last_name: 'EN',
			} );

			// Login as Italian admin
			await page.goto( '/wp-login.php' );
			await page.fill( '#user_login', 'admin_it' );
			await page.fill( '#user_pass', 'admin_it' );
			await page.locator( '#wp-submit' ).click();

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
} );
