import { GlobalUtils } from './utils/global-utils';
import { test, expect } from './utils/test-setup';

test.describe( 'Access Denied', () => {
	test.beforeAll( async ( { globalUtils } ) => {
		await globalUtils.installWordPress();
	} );

	test( 'Switch back from page access denied', {
		annotation: {
			type: 'user-story',
			description: 'As a user who has switched accounts, I want to see a Switch Back link on "access denied" screens, in order to quickly switch back to my original account'
		}
	}, async ( {
		page,
		admin,
		userSwitching,
	} ) => {
		// Create editor user for testing
		GlobalUtils.runWPCLICommand( 'user create editor editor@example.com --role=editor --user_pass=password' );

		// Login through the page's browser context
		await userSwitching.loginViaPage( 'admin', 'password' );

		// Switch to editor
		await userSwitching.switchToUser( 'editor' );

		// Try to access a non-existent admin page (which editor can't access)
		await admin.visitAdminPage( 'tools.php', 'page=foo' );

		// Should see error in title
		await expect( page ).toHaveTitle( /Error/ );

		// Switch back to admin
		await userSwitching.switchBackTo( 'admin' );

		// Should be on the same page but with switched parameters
		const expectedPath = '/wp-admin/tools.php?page=foo&user_switched=true&switched_back=true';
		expect( page.url() ).toContain( expectedPath );
	} );
} );
