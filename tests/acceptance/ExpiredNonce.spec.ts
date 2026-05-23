import { test, expect } from './utils/test-setup.js';

test.describe( 'Expired Nonce', () => {
	test.beforeAll( async ( { globalUtils } ) => {
		globalUtils.installWordPress();
		globalUtils.runWPCLICommand( 'user create subscriber subscriber@example.com --role=subscriber --display_name="subscriber" --user_pass=password' );
	} );

	test( 'Switch back link works after session is destroyed', {
		annotation: {
			type: 'issue',
			description: 'https://github.com/johnbillion/user-switching/issues/144'
		}
	}, async ( {
		page,
		globalUtils,
		userSwitching,
	} ) => {
		// Login as admin and switch to subscriber
		await userSwitching.loginViaPage( 'admin', 'password' );
		await userSwitching.switchToUser( 'subscriber' );
		await userSwitching.verifyLoggedInAs( 'subscriber' );

		// Destroy all subscriber sessions via WP-CLI
		globalUtils.runWPCLICommand( 'user session destroy subscriber --all' );

		// Try to access wp-admin, which redirects to login with stale cookie
		await page.goto( '/wp-admin/' );
		expect( page.url() ).toContain( 'wp-login.php' );

		// The switch back link should be present on the login page
		const switchBackLink = page.locator( '#user_switching_switch_on a' );
		await expect( switchBackLink ).toContainText( 'Switch back to admin' );

		// Click the switch back link - before the fix this showed "The link you followed has expired"
		await switchBackLink.click();

		// Verify successfully switched back to admin
		expect( page.url() ).toContain( 'user_switched=true' );
		expect( page.url() ).toContain( 'switched_back=true' );
		await expect( page.locator( '#wpadminbar .display-name' ).first() ).toContainText( 'admin' );
	} );
} );
