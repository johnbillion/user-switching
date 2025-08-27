import { test as base, expect } from '@playwright/test';
import { Admin, Editor, PageUtils } from '@wordpress/e2e-test-utils-playwright';
import { UserSwitchingUtils } from './user-switching';
import { GlobalUtils } from './global-utils';

type UserSwitchingFixtures = {
	admin: Admin;
	editor: Editor;
	pageUtils: PageUtils;
	userSwitching: UserSwitchingUtils;
	globalUtils: GlobalUtils;
};

export const test = base.extend<UserSwitchingFixtures>( {
	pageUtils: async ( { page, browserName }, use ) => {
		const pageUtils = new PageUtils( { page, browserName } );
		await use( pageUtils );
	},
	editor: async ( { page }, use ) => {
		const editor = new Editor( { page } );
		await use( editor );
	},
	admin: async ( { page, pageUtils, editor }, use ) => {
		const admin = new Admin( { page, pageUtils, editor } );
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
