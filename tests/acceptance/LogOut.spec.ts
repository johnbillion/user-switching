import { test, expect } from './utils/test-setup.js';

test.describe( 'Log Out', () => {
	test.beforeAll( async ( { globalUtils } ) => {
		globalUtils.installWordPress();
		globalUtils.runWPCLICommand( 'user create editor editor@example.com --role=editor --display_name="editor" --user_pass=password' );
	} );

	test( 'Logging out after switching ends the session of the user who switched', {
		annotation: {
			type: 'user-story',
			description: 'As an administrator, I need logging out to end my session even after I switch to another user, in order to keep my account secure'
		}
	}, async ( {
		browser,
		page,
		userSwitching,
	}, testInfo ) => {
		const baseURL = testInfo.project.use.baseURL!;

		// Login as admin and switch to editor
		await userSwitching.loginViaPage( 'admin', 'password' );
		await userSwitching.switchToUser( 'editor' );
		await userSwitching.verifyLoggedInAs( 'editor' );

		// The old user cookie contains a logged-in cookie value for the admin
		const prefix = 'wordpress_user_sw_olduser_';
		const oldUserCookie = ( await page.context().cookies() ).find( ( cookie ) => cookie.name.startsWith( prefix ) );
		expect( oldUserCookie ).toBeDefined();

		// Use that value as the logged-in cookie in another browser
		const otherContext = await browser.newContext( { baseURL } );
		await otherContext.addCookies( [ {
			name: `wordpress_logged_in_${oldUserCookie!.name.slice( prefix.length )}`,
			value: oldUserCookie!.value,
			url: baseURL,
		} ] );
		const otherPage = await otherContext.newPage();

		await otherPage.goto( '/' );
		await expect( otherPage.locator( '#wpadminbar .display-name' ).first() ).toContainText( 'admin' );

		// Log out
		const logOutUrl = await page.locator( '#wp-admin-bar-logout a' ).getAttribute( 'href' );
		await page.goto( logOutUrl! );
		expect( page.url() ).toContain( 'loggedout=true' );

		// The admin session should no longer be valid
		await otherPage.goto( '/' );
		await expect( otherPage.locator( '#wpadminbar .display-name' ).first() ).not.toBeVisible();

		await otherContext.close();
	} );
} );
