import { test } from '@playwright/test';

export class GlobalUtils {

	/**
	 * Run a WP-CLI command
	 */
	static runWPCLICommand( command: string ): string {
		const { execSync } = require( 'child_process' );
		const baseURL = test.info().project.use.baseURL;
		const fullCommand = `docker compose exec --user wp_php wpcli wp --url="${baseURL}" ${command}`;

		try {
			const stdout = execSync( fullCommand, { encoding: 'utf8', cwd: process.cwd() } );
			return stdout.trim();
		} catch ( error: any ) {
			throw new Error( `WP-CLI command failed: ${error.message}` );
		}
	}

	installWordPress() {
		// Install WordPress:
		GlobalUtils.runWPCLICommand( 'db reset --yes' );
		GlobalUtils.runWPCLICommand( 'core install --title="User Switching" --admin_user="admin" --admin_password="password" --admin_email="admin@example.com" --skip-email' );

		// Set a predictable permalink structure:
		GlobalUtils.runWPCLICommand( 'rewrite structure "/%postname%/"' );

		// Install language packs for testing:
		GlobalUtils.runWPCLICommand( 'language core install it_IT' );
		GlobalUtils.runWPCLICommand( 'language plugin install user-switching it_IT' );

		// Activate the plugin under test:
		GlobalUtils.runWPCLICommand( 'plugin activate user-switching' );
	}
}
