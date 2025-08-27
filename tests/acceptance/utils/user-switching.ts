import { Page, expect } from '@playwright/test';
import { Admin, RequestUtils } from '@wordpress/e2e-test-utils-playwright';
import type { WP_REST_API_User } from 'wp-types';

export class UserSwitchingUtils {
	private page: Page;
	private admin: Admin;
	private requestUtils: RequestUtils;

	constructor( page: Page, admin: Admin, requestUtils: RequestUtils ) {
		this.page = page;
		this.admin = admin;
		this.requestUtils = requestUtils;
	}

	/**
	 * Switch to the specified user
	 */
	async switchToUser( username: string ) {
		const userId = await this.getUserIdByUsername( username );
		await this.admin.visitAdminPage( 'user-edit.php', `user_id=${userId}` );
		await this.page.click( '#user_switching_switcher' );
		await this.page.waitForURL( '**/wp-admin/**' );
	}

	/**
	 * Switch off
	 */
	async switchOff() {
		await this.page.hover( '#wp-admin-bar-my-account' );
		await this.page.click( 'text=Switch Off' );
	}

	/**
	 * Switch back to the original user
	 */
	async switchBackTo( displayName: string, lang: string = 'en-US' ) {
		try {
			await this.page.hover( '#wp-admin-bar-my-account' );
		} catch ( error ) {
			// Element might not be visible, continue
		}

		let text: string;
		switch ( lang ) {
			case 'it-IT':
				text = `Torna a ${displayName}`;
				break;
			case 'en-US':
			default:
				text = `Switch back to ${displayName}`;
				break;
		}

		// Click on the switch back link (exact text match)
		await this.page.click( `text=${text}` );
	}

	/**
	 * Verify that the user is logged in as the specified user
	 */
	async verifyLoggedInAs( username: string ) {
		const displayName = await this.getUserDisplayName( username );
		await expect( this.page.locator( '#wpadminbar .display-name' ) ).toContainText( displayName );
	}

	/**
	 * Verify that the user is logged out
	 */
	async verifyLoggedOut() {
		await expect( this.page.locator( '#wpadminbar .display-name' ) ).not.toBeVisible();
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
	 * Verify the page language
	 */
	async canSeePageInLanguage( lang: string ) {
		await expect( this.page.locator( 'html' ) ).toHaveAttribute( 'lang', lang );
	}

	/**
	 * Create a user with the specified username, role, and optional custom data
	 * If user already exists, return the existing user
	 */
	async createUser( username: string, role: string, customData: Partial<WP_REST_API_User> = {} ): Promise<WP_REST_API_User> {
		// First check if user already exists
		try {
			const existingUserId = await this.getUserIdByUsername( username );
			return await this.requestUtils.rest<WP_REST_API_User>( {
				path: `/wp/v2/users/${existingUserId}`,
			} );
		} catch ( error ) {
			// User doesn't exist, continue to create
		}

		const defaultData = {
			username,
			email: `${username}@example.com`,
			first_name: username,
			last_name: 'User',
			roles: [ role ],
			password: username,
		};

		const userData = { ...defaultData, ...customData };

		return await this.requestUtils.rest<WP_REST_API_User>( {
			path: '/wp/v2/users',
			method: 'POST',
			data: userData,
		} );
	}

	/**
	 * Put the block editor into a state where items are usable
	 */
	async prepareBlockEditor() {
		const userId = 1;

		// Save user meta to database via REST API (WordPress 6.2+)
		const preferences = {
			'core/edit-post': {
				fullscreenMode: false,
				welcomeGuide: false,
			},
		};

		// Update user meta via REST API
		await this.requestUtils.rest( {
			path: `/wp/v2/users/${userId}`,
			method: 'POST',
			data: {
				meta: {
					wp_acceptance_persisted_preferences: preferences,
				},
			},
		} );

		// Also set localStorage for pre-6.1 compatibility
		await this.page.evaluate( () => {
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
		} );
	}

	/**
	 * Get user ID by username using WordPress REST API
	 */
	private async getUserIdByUsername( username: string ): Promise<number> {
		// Get all users and find the one with matching username
		const users = await this.requestUtils.rest<WP_REST_API_User[]>( {
			path: '/wp/v2/users',
			params: { search: username, per_page: 100 },
		} );

		const user = users.find( ( u ) => u.username === username );
		if ( user ) {
			return user.id;
		}

		throw new Error( `User with username "${username}" not found` );
	}

	/**
	 * Get user display name by username using WordPress REST API
	 */
	private async getUserDisplayName( username: string ): Promise<string> {
		// Get all users and find the one with matching username
		const users = await this.requestUtils.rest<WP_REST_API_User[]>( {
			path: '/wp/v2/users',
			params: { search: username, per_page: 100 },
		} );

		const user = users.find( ( u ) => u.username === username );
		if ( user ) {
			return user.name;
		}

		throw new Error( `User with username "${username}" not found` );
	}
}
