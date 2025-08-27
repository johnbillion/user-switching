import { request } from '@playwright/test';
import { RequestUtils } from '@wordpress/e2e-test-utils-playwright';
import * as path from 'path';

async function globalSetup() {
	const baseURL = process.env.WP_BASE_URL;
	if ( ! baseURL ) {
		throw new Error( 'WP_BASE_URL environment variable is required' );
	}

	const storageStatePath = path.join( process.cwd(), 'tests/acceptance/storage/admin-storage-state.json' );

	// Create request context
	const requestContext = await request.newContext( { baseURL } );

	// Setup RequestUtils with storage state
	const requestUtils = new RequestUtils( requestContext, {
		storageStatePath,
		baseURL,
		user: {
			username: 'admin',
			password: 'admin',
		},
	} );

	// Authenticate and save the storageState to disk
	await requestUtils.setupRest();

	await requestContext.dispose();
}

export default globalSetup;
