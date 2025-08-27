import { test, expect } from './utils/test-setup';
import type { WP_REST_API_User } from 'wp-types';

test.describe( 'Switch From English', () => {
	test.describe( 'WordPress 6.2+', () => {
		test.beforeAll( async () => {
			// Check WordPress version - these tests require 6.2+
			// This would need to be implemented based on actual WP version check
		} );

		test( 'Switch from English admin to Italian author and back', {
			annotation: {
				type: 'user-story',
				description: 'As an administrator of a site which uses more than one language, I need to be able to switch to user accounts that use a different language, and see the output of User Switching in my original language'
			}
		}, async ( {
			page,
			admin,
			requestUtils,
			userSwitching,
		} ) => {
			// Create Italian author user
			await userSwitching.createUser( 'autore', 'author', {
				first_name: 'Autore',
				last_name: 'Test',
			} );

			// Login as admin (English)
			await admin.visitAdminPage( '/' );

			// Switch to Italian author
			await userSwitching.switchToUser( 'autore' );
			await userSwitching.canSeePageInLanguage( 'it-IT' );
			await userSwitching.seeAdminSuccessNotice( 'Switched to Autore.' );
			
			// The user switching element should be in English
			const switchingElement = page.locator( '#user_switching p' );
			await expect( switchingElement ).toHaveAttribute( 'lang', 'en-US' );

			// Go to dashboard
			await admin.visitAdminPage( '/' );
			
			// Switch back to English admin
			await userSwitching.switchBackTo( 'admin User' );
			await userSwitching.canSeePageInLanguage( 'en-US' );
			await userSwitching.seeAdminSuccessNotice( 'Switched back to admin.' );
		} );
	} );
} );
