import { test as base, expect, Page } from '@playwright/test';
import { UserSwitchingUtils } from './user-switching';
import { GlobalUtils } from './global-utils';

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
	globalUtils: GlobalUtils;
};

export const test = base.extend<UserSwitchingFixtures>( {
	admin: async ( { page }, use ) => {
		const admin = new Admin( page );
		await use( admin );
	},
	userSwitching: async ( { page, admin }, use ) => {
		const userSwitching = new UserSwitchingUtils( page, admin );
		await use( userSwitching );
	},
	globalUtils: async ( {}, use ) => {
		const globalUtils = new GlobalUtils();
		await use( globalUtils );
	},
} );

export { expect };
