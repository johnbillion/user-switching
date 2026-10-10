import { test, expect } from './utils/test-setup';

test.describe( 'Post Lock', () => {
	let sharedPostId: string;
	let editorId: string;

	test.beforeAll( async ( { globalUtils } ) => {
		await globalUtils.installWordPress();
		sharedPostId = globalUtils.runWPCLICommand( 'post create --post_title="Test Post" --post_status=publish --porcelain' );
		editorId = globalUtils.runWPCLICommand( 'user create editor editor@example.com --role=editor --user_pass=password --porcelain' );
	} );

	test( 'Switch back from a post editing screen releases the post lock', {
		annotation: {
			type: 'user-story',
			description: 'As a user who has switched accounts to edit a post, I want to switch back without seeing the post locked modal, in order to continue editing'
		}
	}, async ( {
		page,
		admin,
		userSwitching,
		globalUtils,
	} ) => {
		await userSwitching.loginViaPage( 'admin', 'password' );

		// Simulate a fresh lock left behind by the editor user.
		const setStaleLock = () => {
			const now = Math.floor( Date.now() / 1000 );
			globalUtils.runWPCLICommand( `post meta update ${sharedPostId} _edit_lock "${now}:${editorId}"` );
		};

		// Without the after-switch-back query args, the lock modal should be visible.
		setStaleLock();
		await admin.visitAdminPage( 'post.php', `post=${sharedPostId}&action=edit` );
		await expect( page.getByText( 'This post is already being edited' ) ).toBeVisible();

		// With the after-switch-back query args, the lock modal should not be visible.
		setStaleLock();
		await admin.visitAdminPage( 'post.php', `post=${sharedPostId}&action=edit&user_switched=true&switched_back=true` );
		await expect( page.getByText( 'This post is already being edited' ) ).not.toBeVisible();
	} );
} );
