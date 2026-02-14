import { test, expect } from './utils/test-setup';

test.describe( 'Expired Nonce', () => {
	test.beforeAll( async ( { globalUtils } ) => {
		globalUtils.installWordPress();
		globalUtils.runWPCLICommand( 'user create admin2 admin2@example.com --role=administrator --display_name="admin2" --user_pass=password' );
		globalUtils.runWPCLICommand( 'user create subscriber subscriber@example.com --role=subscriber --display_name="subscriber" --user_pass=password' );
	} );

	test( 'Switch back link works after another user logs out everywhere else', {
		annotation: {
			type: 'issue',
			description: 'https://github.com/johnbillion/user-switching/issues/144'
		}
	}, async ( {
		page,
		browser,
		admin,
		userSwitching,
	} ) => {
		// User A: Login as admin and switch to subscriber
		await userSwitching.loginViaPage( 'admin', 'password' );
		await userSwitching.switchToUser( 'subscriber' );
		await userSwitching.verifyLoggedInAs( 'subscriber' );

		// User B: Create a separate browser context with independent cookies
		const contextB = await browser.newContext();
		const pageB = await contextB.newPage();

		// User B: Login as admin2
		await pageB.goto( '/wp-login.php' );
		await pageB.fill( 'input[name="log"]', 'admin2' );
		await pageB.fill( 'input[name="pwd"]', 'password' );
		await pageB.locator( '#wp-submit' ).click();
		await expect( pageB.locator( '#wpadminbar .display-name' ).first() ).toContainText( 'admin2' );

		// User B: Switch to subscriber (with force, as admin is already switched)
		const subscriberId = await pageB.evaluate( async () => {
			const response = await fetch( '/wp-json/wp/v2/users?search=subscriber', {
				headers: { 'X-WP-Nonce': ( window as any ).wpApiSettings?.nonce || '' },
			} );
			const users = await response.json();
			return users[0]?.id;
		} );

		// Navigate to user edit page and click Switch To
		await pageB.goto( `/wp-admin/user-edit.php?user_id=${subscriberId}` );
		// Handle the duplicate switch confirmation if it appears
		const [response] = await Promise.all( [
			pageB.waitForNavigation(),
			pageB.locator( '#user_switching_switcher' ).click(),
		] );

		// If we got a duplicate switch warning (409), click "Yes, switch"
		if ( pageB.url().includes( 'action=switch_to_user' ) ) {
			const yesButton = pageB.locator( 'a.button', { hasText: /Yes, switch/ } );
			if ( await yesButton.isVisible( { timeout: 2000 } ).catch( () => false ) ) {
				await yesButton.click();
			}
		}

		// Verify User B is now subscriber
		await pageB.goto( '/wp-admin/' );
		await expect( pageB.locator( '#wpadminbar .display-name' ).first() ).toContainText( 'subscriber' );

		// User A: Click "Log Out Everywhere Else" on subscriber's profile
		await admin.visitAdminPage( 'profile.php' );
		await page.locator( 'button:has-text("Log Out Everywhere Else")' ).click();
		await expect( page.locator( '[role="alert"]' ) ).toContainText( 'You are now logged out everywhere else.' );

		// User B: Their session is now destroyed. Try to access wp-admin.
		await pageB.goto( '/wp-admin/' );

		// User B should be redirected to login page
		expect( pageB.url() ).toContain( 'wp-login.php' );

		// User B should see a "Switch back to admin2" link on the login page
		const switchBackLink = pageB.locator( '#user_switching_switch_on a' );
		await expect( switchBackLink ).toContainText( 'Switch back to admin2' );

		// User B: Click the switch back link - this is the critical action
		// Before the fix, this would show "The link you followed has expired"
		await switchBackLink.click();

		// Verify User B successfully switched back to admin2
		expect( pageB.url() ).toContain( 'user_switched=true' );
		expect( pageB.url() ).toContain( 'switched_back=true' );
		await expect( pageB.locator( '#wpadminbar .display-name' ).first() ).toContainText( 'admin2' );

		// Clean up
		await contextB.close();
	} );
} );
