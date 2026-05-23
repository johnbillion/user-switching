import { test as base, expect, Page } from '@playwright/test';
import { captureHtmlOnFailure } from '@johnbillion/plugin-infrastructure/acceptance';
import { UserSwitchingUtils } from './user-switching.js';
import { UserSwitchingGlobalUtils } from './user-switching-global-utils.js';

class Admin {
	private page: Page;

	constructor( page: Page ) {
		this.page = page;
	}

	async visitAdminPage( path: string = '', queryString: string = '' ) {
		const url = `/wp-admin/${path}${queryString ? '?' + queryString : ''}`;
		await this.page.goto( url );
	}
}

type UserSwitchingFixtures = {
	admin: Admin;
	userSwitching: UserSwitchingUtils;
	globalUtils: UserSwitchingGlobalUtils;
};

export const test = base.extend<UserSwitchingFixtures>( {
	admin: async ( { page }, use ) => {
		const admin = new Admin( page );
		await use( admin );
	},
	userSwitching: async ( { page, admin, globalUtils }, use ) => {
		const userSwitching = new UserSwitchingUtils( page, admin, globalUtils );
		await use( userSwitching );
	},
	globalUtils: async ( {}, use, testInfo ) => {
		const baseURL = testInfo.project.use.baseURL!;
		const globalUtils = new UserSwitchingGlobalUtils( { baseURL, pluginSlug: 'user-switching' } );
		await use( globalUtils );
	},
} );

captureHtmlOnFailure( test );

export { expect };
