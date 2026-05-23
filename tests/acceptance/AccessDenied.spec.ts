import { test, expect } from './utils/test-setup.js';

test.describe( 'Access Denied', () => {
	test.beforeEach( async ( { globalUtils } ) => {
		globalUtils.installWordPress();
	} );

	test( 'Switch back from admin screen access denied', {
		annotation: {
			type: 'user-story',
			description: 'As a user who has switched accounts, I want to see a Switch Back link when I am denied access to an admin screen, in order to quickly switch back to my original account'
		}
	}, async ( {
		page,
		admin,
		userSwitching,
		globalUtils,
	} ) => {
		// Create editor user for testing
		globalUtils.runWPCLICommand( 'user create editor editor@example.com --role=editor --user_pass=password' );

		// Login through the page's browser context
		await userSwitching.loginViaPage( 'admin', 'password' );

		// Switch to editor
		await userSwitching.switchToUser( 'editor' );

		// Try to access an admin screen which the editor can't access
		await admin.visitAdminPage( 'tools.php', 'page=foo' );

		// Should see error in title
		await expect( page ).toHaveTitle( /Error/ );

		// Switch back to admin
		await userSwitching.switchBackTo( 'admin' );

		// Should be on the same admin screen but with switched parameters
		const expectedPath = '/wp-admin/tools.php?page=foo&user_switched=true&switched_back=true';
		expect( page.url() ).toContain( expectedPath );
	} );

	test( 'Switch back from item access denied', {
		annotation: {
			type: 'user-story',
			description: 'As a user who has switched accounts, I want to see a Switch Back link when I am denied access to an item, in order to quickly switch back to my original account'
		}
	}, async ( {
		page,
		admin,
		userSwitching,
		globalUtils,
	}, testInfo ) => {
		// Create editor user for testing
		globalUtils.runWPCLICommand( 'user create editor editor@example.com --role=editor --user_pass=password' );

		// Login through the page's browser context
		await userSwitching.loginViaPage( 'admin', 'password' );

		// Switch to editor
		await userSwitching.switchToUser( 'editor' );

		// Try to edit an item which the editor can't access
		await admin.visitAdminPage( 'post.php', 'post=12345&action=edit' );

		// Should see error in title
		await expect( page ).toHaveTitle( /Error/ );

		// Switch back to admin
		await userSwitching.switchBackTo( 'admin' );

		// Should be on the same page but with switched parameters
		const expectedPath = '/wp-admin/post.php?post=12345&action=edit&user_switched=true&switched_back=true';
		expect( page.url() ).toContain( expectedPath );
	} );} );
