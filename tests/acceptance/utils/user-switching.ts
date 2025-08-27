import { Page, expect } from '@playwright/test';
import { Admin } from '@wordpress/e2e-test-utils-playwright';
import { GlobalUtils } from './global-utils';

export class UserSwitchingUtils {
	private page: Page;
	private admin: Admin;

	constructor( page: Page, admin: Admin ) {
		this.page = page;
		this.admin = admin;
	}

	/**
	 * Put the block editor into a state where the items we need to interact with are
	 * actually usable.
	 */
	async prepareBlockEditor() {
		const userId = 1;

		// Set user meta for WordPress 6.1+ persisted preferences
		const modernPreferences = {
			'core/edit-post': {
				fullscreenMode: false,
				welcomeGuide: false,
			},
		};

		GlobalUtils.runWPCLICommand( `user meta add ${userId} wp_persisted_preferences '${JSON.stringify( modernPreferences )}' --format=json` );

		// Set localStorage for pre-6.1 compatibility
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

		// @todo verify we're logged in (can't use HTTP status code as WP returns 200 even on failed login)
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
		await this.page.getByText( expectedText ).click();
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
	createUser( username: string, role: string, customData: { email?: string; first_name?: string; last_name?: string; name?: string } = {} ) {
		const email = customData.email || `${username}@example.com`;
		let displayName: string;
		
		if ( customData.name ) {
			displayName = customData.name;
		} else if ( customData.first_name || customData.last_name ) {
			displayName = `${customData.first_name || ''} ${customData.last_name || ''}`.trim();
		} else {
			displayName = username;
		}

		GlobalUtils.runWPCLICommand( `user create ${username} ${email} --role=${role} --display_name="${displayName}" --user_pass=password` );
	}

	/**
	 * Get user ID by username
	 */
	private getUserIdByUsername( username: string ): number {
		return parseInt( GlobalUtils.runWPCLICommand( `user get ${username} --field=ID` ), 10 );
	}

	/**
	 * Get user display name by username
	 */
	private getUserDisplayName( username: string ): string {
		return GlobalUtils.runWPCLICommand( `user get ${username} --field=display_name` );
	}

}
