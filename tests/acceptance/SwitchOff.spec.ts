import { test, expect } from './utils/test-setup';
import { GlobalUtils } from './utils/global-utils';

test.describe( 'Switch Off', () => {
	test.beforeAll( async ( { globalUtils } ) => {
		await globalUtils.installWordPress();
	} );
	test( 'Switch off from dashboard and back from front end', {
		annotation: {
			type: 'user-story',
			description: 'As an administrator, I need to be able to switch off, in order to view the site without logging out completely'
		}
	}, async ( {
		page,
		admin,
		userSwitching,
	} ) => {
		// Login as admin
		await userSwitching.loginViaPage( 'admin', 'password' );
		await admin.visitAdminPage( '/' );

		// Switch off
		await userSwitching.switchOff();
		expect( page.url() ).toContain( '/?switched_off=true' );
		await userSwitching.verifyLoggedOut();

		// Switch back to admin
		await userSwitching.switchBackTo( 'admin' );
		expect( page.url() ).toContain( '/?user_switched=true&switched_back=true' );
		await userSwitching.verifyLoggedInAs( 'admin' );
	} );

	test( 'Switch off from dashboard and back from login screen', {
		annotation: {
			type: 'user-story',
			description: 'As an administrator, I need to be able to switch off, in order to view the site without logging out completely'
		}
	}, async ( {
		page,
		admin,
		userSwitching,
	} ) => {
		// Login as admin
		await userSwitching.loginViaPage( 'admin', 'password' );
		await admin.visitAdminPage( '/' );

		// Switch off
		await userSwitching.switchOff();
		expect( page.url() ).toContain( '/?switched_off=true' );
		await userSwitching.verifyLoggedOut();

		// Go to login page
		await page.goto( '/wp-login.php' );

		// Switch back to admin
		await userSwitching.switchBackTo( 'admin' );
		expect( page.url() ).toContain( '/wp-admin/users.php' );
		await userSwitching.seeAdminSuccessNotice( 'Switched back to admin.' );
		await userSwitching.verifyLoggedInAs( 'admin' );
	} );

	test( 'Switch off from published post editing screen', {
		annotation: {
			type: 'user-story',
			description: 'As an administrator, I need to be able to switch off, in order to view the site without logging out completely'
		}
	}, async ( {
		page,
		admin,
		editor,
		userSwitching,
	} ) => {
		// Login as admin
		await userSwitching.loginViaPage( 'admin', 'password' );
		await admin.visitAdminPage( '/' );

		// Create a published post
		const postId = GlobalUtils.runWPCLICommand( 'post create --post_title="Hello World" --post_status=publish --porcelain' );

		// Prepare block editor
		await userSwitching.prepareBlockEditor();

		// Edit the post
		await admin.visitAdminPage( 'post.php', `post=${postId}&action=edit` );

		// Switch off
		await userSwitching.switchOff();
		expect( page.url() ).toContain( '/hello-world/?switched_off=true' );
		await userSwitching.verifyLoggedOut();
	} );

	test( 'Switch off from draft post editing screen', {
		annotation: {
			type: 'user-story',
			description: 'As an administrator, I need to be able to switch off, in order to view the site without logging out completely'
		}
	}, async ( {
		page,
		admin,
		editor,
		userSwitching,
	} ) => {
		// Login as admin
		await userSwitching.loginViaPage( 'admin', 'password' );
		await admin.visitAdminPage( '/' );

		// Create a draft post
		const postId = GlobalUtils.runWPCLICommand( 'post create --post_title="Draft Post" --post_status=draft --porcelain' );

		// Prepare block editor
		await userSwitching.prepareBlockEditor();

		// Edit the post
		await admin.visitAdminPage( 'post.php', `post=${postId}&action=edit` );

		// Switch off
		await userSwitching.switchOff();
		expect( page.url() ).toContain( '/?switched_off=true' );
		await userSwitching.verifyLoggedOut();
	} );

	test( 'Switch off from term editing screen', {
		annotation: {
			type: 'user-story',
			description: 'As an administrator, I need to be able to switch off, in order to view the site without logging out completely'
		}
	}, async ( {
		page,
		admin,
		userSwitching,
	} ) => {
		// Login as admin
		await userSwitching.loginViaPage( 'admin', 'password' );
		await admin.visitAdminPage( '/' );

		// Create a category
		const termId = GlobalUtils.runWPCLICommand( 'term create category "Hello Category" --slug=hello --porcelain' );

		// Edit the term
		await admin.visitAdminPage( 'term.php', `taxonomy=category&tag_ID=${termId}` );

		// Switch off
		await userSwitching.switchOff();
		expect( page.url() ).toContain( '/category/hello/?switched_off=true' );
		await userSwitching.verifyLoggedOut();
	} );

	test( 'Switch off from user editing screen', {
		annotation: {
			type: 'user-story',
			description: 'As an administrator, I need to be able to switch off, in order to view the site without logging out completely'
		}
	}, async ( {
		page,
		admin,
		userSwitching,
	} ) => {
		// Login as admin
		await userSwitching.loginViaPage( 'admin', 'password' );
		await admin.visitAdminPage( '/' );

		// Create a user
		userSwitching.createUser( 'example', 'editor', {
			first_name: 'Example',
			last_name: 'User',
		} );
		const userId = GlobalUtils.runWPCLICommand( 'user get example --field=ID' );

		// Edit the user
		await admin.visitAdminPage( 'user-edit.php', `user_id=${userId}` );

		// Switch off
		await userSwitching.switchOff();
		expect( page.url() ).toContain( '/author/example/?switched_off=true' );
		await userSwitching.verifyLoggedOut();
	} );

	test( 'Switch off from approved comment editing screen', {
		annotation: {
			type: 'user-story',
			description: 'As an administrator, I need to be able to switch off, in order to view the site without logging out completely'
		}
	}, async ( {
		page,
		admin,
		userSwitching,
	} ) => {
		// Login as admin
		await userSwitching.loginViaPage( 'admin', 'password' );
		await admin.visitAdminPage( '/' );

		// Create a post
		const postId = GlobalUtils.runWPCLICommand( 'post create --post_title="Leave a Comment" --post_status=publish --porcelain' );

		// Create an approved comment
		const commentId = GlobalUtils.runWPCLICommand( `comment create --comment_post_ID=${postId} --comment_content="Great post!" --comment_approved=1 --porcelain` );

		// Edit the comment
		await admin.visitAdminPage( 'comment.php', `action=editcomment&c=${commentId}` );

		// Switch off
		await userSwitching.switchOff();
		expect( page.url() ).toContain( `/leave-a-comment/?switched_off=true#comment-${commentId}` );
		await userSwitching.verifyLoggedOut();
	} );

	test( 'Switch off from unapproved comment editing screen', {
		annotation: {
			type: 'user-story',
			description: 'As an administrator, I need to be able to switch off, in order to view the site without logging out completely'
		}
	}, async ( {
		page,
		admin,
		userSwitching,
	} ) => {
		// Login as admin
		await userSwitching.loginViaPage( 'admin', 'password' );
		await admin.visitAdminPage( '/' );

		// Create a post
		const postId = GlobalUtils.runWPCLICommand( 'post create --post_title="Leave a Comment" --post_status=publish --porcelain' );

		// Create an unapproved comment
		const commentId = GlobalUtils.runWPCLICommand( `comment create --comment_post_ID=${postId} --comment_content="Pending comment" --comment_approved=0 --porcelain` );

		// Edit the comment
		await admin.visitAdminPage( 'comment.php', `action=editcomment&c=${commentId}` );

		// Switch off
		await userSwitching.switchOff();
		expect( page.url() ).toContain( '/leave-a-comment/?switched_off=true' );
		await userSwitching.verifyLoggedOut();
	} );
} );
