import { Page, expect } from '@playwright/test';
import { GlobalUtils } from '@johnbillion/plugin-infrastructure/acceptance';

// Simple admin utility interface to match what we need
interface Admin {
	visitAdminPage( path?: string, queryString?: string ): Promise<void>;
}

export class UserSwitchingUtils {
	private page: Page;
	private admin: Admin;
	private globalUtils: GlobalUtils;

	constructor( page: Page, admin: Admin, globalUtils: GlobalUtils ) {
		this.page = page;
		this.admin = admin;
		this.globalUtils = globalUtils;
	}

	/**
	 * Put the block editor into a state where the items we need to interact with are
	 * actually usable.
	 */
	async prepareBlockEditor( userId: number = 1 ) {
		// Set user meta for persisted preferences
		const modernPreferences = {
			'core/edit-post': {
				fullscreenMode: false,
				welcomeGuide: false,
			},
		};

		this.globalUtils.runWPCLICommand( `user meta add ${userId} wp_persisted_preferences '${JSON.stringify( modernPreferences )}' --format=json` );

		// Set localStorage too. This is needed because the block editor seems to check
		// localStorage first despite persisted preferences being the source of truth
		await this.page.evaluate( ( userId ) => {
			const legacyPreferences = {
				'core/edit-post': {
					preferences: {
						features: {
							fullscreenMode: false,
							welcomeGuide: false,
						},
					},
				},
			};

			localStorage.setItem(
				`WP_DATA_USER_${userId}`,
				JSON.stringify( legacyPreferences )
			);
		}, userId );
	}

	/**
	 * Login via the wp-login.php page
	 */
	async loginViaPage( username: string, password: string ) {
		await this.page.goto( '/wp-login.php' );
		await this.page.fill( 'input[name="log"]', username );
		await this.page.fill( 'input[name="pwd"]', password );
		await this.page.locator( '#wp-submit' ).click();

		// Wait for the redirect to complete before returning, otherwise a subsequent
		// navigation can race the login request and land without the auth cookie.
		await this.page.waitForURL( /\/wp-admin\// );

		// WP returns a 200 for a failed login, so assert on the logged-in state instead.
		await expect( this.page.locator( '#wpadminbar' ) ).toBeVisible();
	}

	/**
	 * Switch to the specified user
	 */
	async switchToUser( username: string ) {
		const userId = this.getUserIdByUsername( username );
		await this.admin.visitAdminPage( 'user-edit.php', `user_id=${userId}` );
		await this.page.locator( '#user_switching_switcher' ).click();
	}

	/**
	 * Open the command palette from the block editor (WP 6.3+).
	 */
	async openCommandPaletteFromEditor() {
		// The document bar in the editor header was introduced in WordPress 6.4. Before
		// that the palette can only be opened with the keyboard shortcut.
		if ( ! this.globalUtils.isWordPressVersionAtLeast( 6.4 ) ) {
			await this.openCommandPaletteWithKeyboard();
			return;
		}

		await this.page.locator( '.editor-document-bar__command' ).click();
		await expect( this.page.locator( '[cmdk-input]' ) ).toBeVisible();
	}

	/**
	 * Open the command palette via its keyboard shortcut.
	 */
	async openCommandPaletteWithKeyboard() {
		// The shortcut is registered by a script which loads asynchronously, so pressing
		// it too early does nothing. Keep pressing until the palette appears.
		await expect( async () => {
			await this.page.keyboard.press( 'ControlOrMeta+k' );
			await expect( this.page.locator( '[cmdk-input]' ) ).toBeVisible( { timeout: 2000 } );
		} ).toPass();
	}

	/**
	 * Type a query into the open command palette.
	 */
	async searchCommandPalette( query: string ) {
		await this.page.locator( '[cmdk-input]' ).fill( query );
	}

	/**
	 * Wait for a command with the given label to appear in the palette, then click it.
	 */
	async runCommand( label: string ) {
		const command = this.page.locator( '[cmdk-item]', { hasText: label } );
		await expect( command.first() ).toBeVisible();
		await command.first().click();
	}

	/**
	 * Switch off
	 */
	async switchOff() {
		await this.page.hover( '#wp-admin-bar-my-account' );
		await this.page.locator( 'text=Switch Off' ).click();
	}

	/**
	 * Switch back to the original user
	 */
	async switchBackTo( userLogin: string, lang: string = 'en-US' ) {
		const displayName = this.getUserDisplayName( userLogin );

		// Try to hover over the admin bar account area to open the dropdown if it exists
		// This is optional as the admin bar may not be present on all pages
		try {
			await this.page.hover( '#wp-admin-bar-my-account', { timeout: 2000 } );
		} catch ( error ) {
			// Admin bar not found or not needed, continue without hover
		}

		// Get the expected text format - just "Switch back to DisplayName"
		let expectedText: string;
		switch ( lang ) {
			case 'it-IT':
				expectedText = `Torna a ${displayName}`;
				break;
			case 'en-US':
			default:
				expectedText = `Switch back to ${displayName}`;
				break;
		}

		// Use Playwright's getByText which is more reliable than text= selector
		// Get the first visible link with the expected text
		await this.page.getByText( expectedText ).first().click();
	}

	/**
	 * Verify that the user is logged in as the specified user
	 */
	async verifyLoggedInAs( username: string ) {
		const displayName = this.getUserDisplayName( username );
		await expect( this.page.locator( '#wpadminbar .display-name' ).first() ).toContainText( displayName );
	}

	/**
	 * Verify that the user is logged out
	 */
	async verifyLoggedOut() {
		await expect( this.page.locator( '#wpadminbar .display-name' ) ).not.toBeVisible();
	}

	/**
	 * Verify the page language
	 */
	async canSeeThePageInLanguage( lang: string ) {
		await this.canSeeTheElementInLanguage( 'html', lang );
	}

	/**
	 * Verify the language of an element
	 */
	async canSeeTheElementInLanguage( selector: string, lang: string ) {
		await expect( this.page.locator( selector ) ).toHaveAttribute( 'lang', lang );
	}

	/**
	 * Check for admin success notice
	 */
	async seeAdminSuccessNotice( text: string ) {
		await expect( this.page.locator( '.notice-success' ) ).toContainText( text );
	}

	/**
	 * Check for admin warning notice
	 */
	async seeAdminWarningNotice( text: string ) {
		await expect( this.page.locator( '.notice-warning' ) ).toContainText( text );
	}

	/**
	 * Check for admin error notice
	 */
	async seeAdminErrorNotice( text: string ) {
		await expect( this.page.locator( '.notice-error' ) ).toContainText( text );
	}

	/**
	 * Check for admin info notice
	 */
	async seeAdminInfoNotice( text: string ) {
		await expect( this.page.locator( '.notice-info' ) ).toContainText( text );
	}

	/**
	 * Create a user with the specified username, role, and optional custom data
	 */
	createUser( username: string, role: string, customData: { email?: string; name?: string; locale?: string } = {} ) {
		const email = customData.email || `${username}@example.com`;
		const displayName = customData.name || username;

		this.globalUtils.runWPCLICommand( `user create ${username} ${email} --role=${role} --display_name="${displayName}" --user_pass=password` );

		// Set user locale if provided
		if ( customData.locale ) {
			this.globalUtils.runWPCLICommand( `user meta update ${username} locale ${customData.locale}` );
		}
	}

	/**
	 * Get user ID by username
	 */
	getUserIdByUsername( username: string ): number {
		return parseInt( this.globalUtils.runWPCLICommand( `user get ${username} --field=ID` ), 10 );
	}

	/**
	 * Get user display name by username
	 */
	private getUserDisplayName( username: string ): string {
		return this.globalUtils.runWPCLICommand( `user get ${username} --field=display_name` );
	}

}
