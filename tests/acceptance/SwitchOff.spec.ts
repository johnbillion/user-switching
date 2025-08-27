import { test, expect } from './utils/test-setup';
import type { WP_REST_API_Post, WP_REST_API_Comment, WP_REST_API_Category } from 'wp-types';

test.describe( 'Switch Off', () => {
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
		await admin.visitAdminPage( '/' );

		// Switch off
		await userSwitching.switchOff();
		expect( page.url() ).toContain( '/?switched_off=true' );
		await userSwitching.verifyLoggedOut();

		// Switch back to admin
		await userSwitching.switchBackTo( 'admin User' );
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
		await admin.visitAdminPage( '/' );

		// Switch off
		await userSwitching.switchOff();
		expect( page.url() ).toContain( '/?switched_off=true' );
		await userSwitching.verifyLoggedOut();

		// Go to login page
		await page.goto( '/wp-login.php' );

		// Switch back to admin
		await userSwitching.switchBackTo( 'admin User' );
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
		requestUtils,
		userSwitching,
	} ) => {
		// Login as admin
		await admin.visitAdminPage( '/' );

		// Create a published post
		const post = await requestUtils.rest<WP_REST_API_Post>( {
			path: '/wp/v2/posts',
			method: 'POST',
			data: {
				title: 'Test Post',
				content: 'Test content',
				status: 'publish',
				slug: 'hello-world',
			},
		} );

		// Prepare block editor
		await userSwitching.prepareBlockEditor();

		// Edit the post
		await admin.visitAdminPage( 'post.php', `post=${post.id}&action=edit` );

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
		requestUtils,
		userSwitching,
	} ) => {
		// Login as admin
		await admin.visitAdminPage( '/' );

		// Create a draft post
		const post = await requestUtils.rest<WP_REST_API_Post>( {
			path: '/wp/v2/posts',
			method: 'POST',
			data: {
				title: 'Draft Post',
				content: 'Draft content',
				status: 'draft',
				slug: 'hello-world',
			},
		} );

		// Prepare block editor
		await userSwitching.prepareBlockEditor();

		// Edit the post
		await admin.visitAdminPage( 'post.php', `post=${post.id}&action=edit` );

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
		requestUtils,
		userSwitching,
	} ) => {
		// Login as admin
		await admin.visitAdminPage( '/' );

		// Create a category using REST API
		const term = await requestUtils.rest<WP_REST_API_Category>( {
			path: '/wp/v2/categories',
			method: 'POST',
			data: {
				name: 'Test Category',
				slug: 'hello',
			},
		} );

		// Edit the term
		await admin.visitAdminPage( 'term.php', `taxonomy=category&tag_ID=${term.id}` );

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
		requestUtils,
		userSwitching,
	} ) => {
		// Login as admin
		await admin.visitAdminPage( '/' );

		// Create a user
		const user = await userSwitching.createUser( 'example', 'editor', {
			first_name: 'Example',
			last_name: 'User',
		} );

		// Edit the user
		await admin.visitAdminPage( 'user-edit.php', `user_id=${user.id}` );

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
		requestUtils,
		userSwitching,
	} ) => {
		// Login as admin
		await admin.visitAdminPage( '/' );

		// Create a post
		const post = await requestUtils.rest<WP_REST_API_Post>( {
			path: '/wp/v2/posts',
			method: 'POST',
			data: {
				title: 'Comment Test',
				content: 'Leave a comment',
				status: 'publish',
				slug: 'leave-a-comment',
			},
		} );

		// Create an approved comment
		const comment = await requestUtils.rest<WP_REST_API_Comment>( {
			path: '/wp/v2/comments',
			method: 'POST',
			data: {
				post: post.id,
				content: 'Test comment',
				status: 'approved',
			},
		} );

		// Edit the comment
		await admin.visitAdminPage( 'comment.php', `action=editcomment&c=${comment.id}` );

		// Switch off
		await userSwitching.switchOff();
		expect( page.url() ).toContain( `/leave-a-comment/?switched_off=true#comment-${comment.id}` );
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
		requestUtils,
		userSwitching,
	} ) => {
		// Login as admin
		await admin.visitAdminPage( '/' );

		// Create a post
		const post = await requestUtils.rest<WP_REST_API_Post>( {
			path: '/wp/v2/posts',
			method: 'POST',
			data: {
				title: 'Comment Test',
				content: 'Leave a comment',
				status: 'publish',
				slug: 'leave-a-comment',
			},
		} );

		// Create an unapproved comment
		const comment = await requestUtils.rest<WP_REST_API_Comment>( {
			path: '/wp/v2/comments',
			method: 'POST',
			data: {
				post: post.id,
				content: 'Test comment',
				status: 'hold',
			},
		} );

		// Edit the comment
		await admin.visitAdminPage( 'comment.php', `action=editcomment&c=${comment.id}` );

		// Switch off
		await userSwitching.switchOff();
		expect( page.url() ).toContain( '/leave-a-comment/?switched_off=true' );
		await userSwitching.verifyLoggedOut();
	} );
} );
