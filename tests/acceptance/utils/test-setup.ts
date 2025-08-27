import { test as base, expect } from '@playwright/test';
import { Admin, Editor, RequestUtils, PageUtils } from '@wordpress/e2e-test-utils-playwright';
import { UserSwitchingUtils } from './user-switching';
import * as path from 'path';

type UserSwitchingFixtures = {
	admin: Admin;
	editor: Editor;
	pageUtils: PageUtils;
	requestUtils: RequestUtils;
	userSwitching: UserSwitchingUtils;
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
	requestUtils: async ( { baseURL }, use ) => {
		const storageStatePath = path.join( process.cwd(), 'tests/acceptance/admin-storage-state.json' );
		const requestUtils = await RequestUtils.setup( {
			baseURL,
			storageStatePath,
		} );
		await use( requestUtils );
	},
	admin: async ( { page, pageUtils, editor, requestUtils }, use ) => {
		const admin = new Admin( { page, pageUtils, editor, requestUtils } );
		await use( admin );
	},
	userSwitching: async ( { page, admin, requestUtils }, use ) => {
		const userSwitching = new UserSwitchingUtils( page, admin, requestUtils );
		await use( userSwitching );
	},
} );

export { expect };
