import { test, expect } from './utils/test-setup.js';
import { UserSwitchingUtils } from './utils/user-switching.js';

interface Admin {
	visitAdminPage( path?: string, queryString?: string ): Promise<void>;
}

/**
 * The command palette was introduced in the block editor in WordPress 6.3 and
 * made available admin-wide (via the admin bar) in WordPress 6.9. Each screen
 * runs the same set of command palette tests, guarded by its minimum version.
 */
const screens = [
	{
		name: 'in the block editor',
		minVersion: 6.3,
		async gotoAndOpen( userSwitching: UserSwitchingUtils, admin: Admin, currentUser: string ) {
			await userSwitching.prepareBlockEditor( userSwitching.getUserIdByUsername( currentUser ) );
			await admin.visitAdminPage( 'post-new.php' );
			await userSwitching.openCommandPaletteFromEditor();
		},
	},
	{
		name: 'from the admin bar',
		minVersion: 6.9,
		async gotoAndOpen( userSwitching: UserSwitchingUtils, admin: Admin ) {
			await admin.visitAdminPage( '/' );
			await userSwitching.openCommandPaletteFromAdminBar();
		},
	},
];

test.describe( 'Command Palette', () => {
	test.beforeAll( async ( { globalUtils } ) => {
		// Install WordPress fresh for this test suite
		globalUtils.installWordPress();
		// Create an editor user once for all tests
		globalUtils.runWPCLICommand( 'user create editor editor@example.com --role=editor --display_name="editor" --user_pass=password' );
	} );

	for ( const screen of screens ) {
		test.describe( screen.name, () => {
			test( 'Switch to a user', {
				annotation: {
					type: 'user-story',
					description: 'As an administrator, I need to be able to search for and switch to a user from the command palette, in order to quickly access different user accounts'
				}
			}, async ( {
				admin,
				userSwitching,
				globalUtils,
			}, testInfo ) => {
				testInfo.skip( ! globalUtils.isWordPressVersionAtLeast( screen.minVersion ), `This test requires WordPress ${screen.minVersion} or later` );

				// Login as admin
				await userSwitching.loginViaPage( 'admin', 'password' );

				// Open the command palette and search for the editor user. The
				// search hits the REST API so the matching command appears
				// asynchronously.
				await screen.gotoAndOpen( userSwitching, admin, 'admin' );
				await userSwitching.searchCommandPalette( 'editor' );
				await userSwitching.runCommand( 'Switch to editor' );

				// Should be switched to the editor
				await userSwitching.seeAdminSuccessNotice( 'Switched to editor.' );
				await userSwitching.verifyLoggedInAs( 'editor' );
			} );

			test( 'Switch off', {
				annotation: {
					type: 'user-story',
					description: 'As an administrator, I need to be able to switch off from the command palette, in order to view the site without logging out completely'
				}
			}, async ( {
				page,
				admin,
				userSwitching,
				globalUtils,
			}, testInfo ) => {
				testInfo.skip( ! globalUtils.isWordPressVersionAtLeast( screen.minVersion ), `This test requires WordPress ${screen.minVersion} or later` );

				// Login as admin
				await userSwitching.loginViaPage( 'admin', 'password' );

				// Open the command palette and run the Switch Off command
				await screen.gotoAndOpen( userSwitching, admin, 'admin' );
				await userSwitching.searchCommandPalette( 'Switch Off' );
				await userSwitching.runCommand( 'Switch Off' );

				// Should be switched off and logged out
				expect( page.url() ).toContain( 'switched_off=true' );
				await userSwitching.verifyLoggedOut();
			} );

			test( 'Switch back', {
				annotation: {
					type: 'user-story',
					description: 'As a user who has switched accounts, I need to be able to switch back from the command palette, in order to quickly return to my original account'
				}
			}, async ( {
				page,
				admin,
				userSwitching,
				globalUtils,
			}, testInfo ) => {
				testInfo.skip( ! globalUtils.isWordPressVersionAtLeast( screen.minVersion ), `This test requires WordPress ${screen.minVersion} or later` );

				// Login as admin
				await userSwitching.loginViaPage( 'admin', 'password' );
				await admin.visitAdminPage( '/' );

				// Switch to the editor
				await userSwitching.switchToUser( 'editor' );
				await userSwitching.verifyLoggedInAs( 'editor' );

				// Open the command palette as the editor and run the Switch Back command
				await screen.gotoAndOpen( userSwitching, admin, 'editor' );
				await userSwitching.searchCommandPalette( 'Switch back' );
				await userSwitching.runCommand( 'Switch back to admin' );

				// Should be switched back to admin
				expect( page.url() ).toContain( 'user_switched=true' );
				await userSwitching.verifyLoggedInAs( 'admin' );
			} );
		} );
	}
} );
