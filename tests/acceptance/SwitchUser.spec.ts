import { test, expect } from './utils/test-setup.js';

test.describe( 'User Switching', () => {
	test.beforeAll( async ( { globalUtils } ) => {
		// Install WordPress fresh for this test suite
		globalUtils.installWordPress();
		// Create an editor user once for all tests
		globalUtils.runWPCLICommand( 'user create editor editor@example.com --role=editor --display_name="editor" --user_pass=password' );
	} );

	test( 'Switch to editor then back from front end', {
		annotation: {
			type: 'user-story',
			description: 'As an administrator, I need to be able to switch between users, in order to access different user accounts'
		}
	}, async ( {
		page,
		admin,
		userSwitching,
	} ) => {
		// Login as admin
		await userSwitching.loginViaPage( 'admin', 'password' );
		await admin.visitAdminPage( '/' );

		// Switch to editor
		await userSwitching.switchToUser( 'editor' );
		expect( page.url() ).toContain( '/wp-admin/' );
		await userSwitching.seeAdminSuccessNotice( 'Switched to editor.' );
		await userSwitching.verifyLoggedInAs( 'editor' );

		// Go to front end
		await page.goto( '/' );

		// Switch back to admin
		await userSwitching.switchBackTo( 'admin' );
		expect( page.url() ).toContain( '/?user_switched=true&switched_back=true' );
		await userSwitching.verifyLoggedInAs( 'admin' );
	} );

	test( 'Switch to editor then back from admin area', {
		annotation: {
			type: 'user-story',
			description: 'As an administrator, I need to be able to switch between users, in order to access different user accounts'
		}
	}, async ( {
		page,
		admin,
		userSwitching,
	} ) => {
		// Login as admin
		await userSwitching.loginViaPage( 'admin', 'password' );
		await admin.visitAdminPage( '/' );

		// Switch to editor
		await userSwitching.switchToUser( 'editor' );
		expect( page.url() ).toContain( '/wp-admin/' );
		await userSwitching.seeAdminSuccessNotice( 'Switched to editor.' );
		await userSwitching.verifyLoggedInAs( 'editor' );

		// Navigate to tools page
		await admin.visitAdminPage( 'tools.php' );

		// Switch back to admin
		await userSwitching.switchBackTo( 'admin' );
		expect( page.url() ).toContain( '/wp-admin/tools.php' );
		await userSwitching.seeAdminSuccessNotice( 'Switched back to admin.' );
		await userSwitching.verifyLoggedInAs( 'admin' );
	} );
} );
