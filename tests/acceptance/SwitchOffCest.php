<?php declare(strict_types = 1);

namespace UserSwitching\Tests;

/**
 * Acceptance tests for switching off.
 */
final class SwitchOffCest {
	public function _before( \AcceptanceTester $I ): void {
		$I->comment( 'As an administrator' );
		$I->comment( 'I need to be able to switch off' );
		$I->comment( 'In order to view the site without logging out completely' );
	}

	public function SwitchOffFromDashboardAndBackFromFrontEnd( \AcceptanceTester $I ): void {
		$I->loginAsAdmin();
		$I->amOnAdminPage( '/' );
		$I->switchOff();
		$I->seeCurrentUrlEquals( '/?switched_off=true' );
		$I->amLoggedOut();

		$I->switchBackTo( 'admin' );
		$I->seeCurrentUrlEquals( '/?user_switched=true&switched_back=true' );
		$I->amLoggedInAs( 'admin' );
	}

	public function SwitchOffFromDashboardAndBackFromLoginScreen( \AcceptanceTester $I ): void {
		$I->loginAsAdmin();
		$I->amOnAdminPage( '/' );
		$I->switchOff();
		$I->seeCurrentUrlEquals( '/?switched_off=true' );
		$I->amLoggedOut();

		$I->amOnPage( 'wp-login.php' );
		$I->switchBackTo( 'admin' );
		$I->seeCurrentUrlEquals( '/wp-admin/users.php' );
		$I->seeAdminSuccessNotice( 'Switched back to admin.' );
		$I->amLoggedInAs( 'admin' );
	}

	public function SwitchOffFromPublishedPostEditingScreen( \AcceptanceTester $I ): void {
		$I->loginAsAdmin();
		$id = $I->havePostInDatabase( [
			'post_status' => 'publish',
			'post_name' => 'hello-world',
		] );
		$I->amNotUsingTheEditorForTheFirstTime();
		$I->amEditingPostWithId( $id );
		$I->switchOff();
		$I->seeCurrentUrlEquals( '/hello-world/?switched_off=true' );
		$I->amLoggedOut();
	}

	public function SwitchOffFromDraftPostEditingScreen( \AcceptanceTester $I ): void {
		$I->loginAsAdmin();
		$id = $I->havePostInDatabase( [
			'post_status' => 'draft',
			'post_name' => 'hello-world',
		] );
		$I->amNotUsingTheEditorForTheFirstTime();
		$I->amEditingPostWithId( $id );
		$I->switchOff();
		$I->seeCurrentUrlEquals( '/?switched_off=true' );
		$I->amLoggedOut();
	}

	public function SwitchOffFromTermEditingScreen( \AcceptanceTester $I ): void {
		$I->loginAsAdmin();
		$term = $I->haveTermInDatabase( 'hello', 'category' );
		$I->amOnAdminPage( '/term.php?taxonomy=category&tag_ID=' . $term[0] );
		$I->switchOff();
		$I->seeCurrentUrlEquals( '/category/hello/?switched_off=true' );
		$I->amLoggedOut();
	}

	public function SwitchOffFromUserEditingScreen( \AcceptanceTester $I ): void {
		$I->loginAsAdmin();
		$id = $I->haveUserInDatabase( 'example', 'editor' );
		$I->amEditingUserWithId( $id );
		$I->switchOff();
		$I->seeCurrentUrlEquals( '/author/example/?switched_off=true' );
		$I->amLoggedOut();
	}

	public function SwitchOffFromApprovedCommentEditingScreen( \AcceptanceTester $I ): void {
		$I->loginAsAdmin();
		$postId = $I->havePostInDatabase( [
			'post_status' => 'publish',
			'post_name' => 'leave-a-comment',
		] );
		$commentId = $I->haveCommentInDatabase( $postId, [
			'comment_approved' => '1',
		] );
		$I->amOnAdminPage( '/comment.php?action=editcomment&c=' . $commentId );
		$I->switchOff();
		$I->seeCurrentUrlEquals( '/leave-a-comment/?switched_off=true#comment-' . $commentId );
		$I->amLoggedOut();
	}

	public function SwitchOffFromUnapprovedCommentEditingScreen( \AcceptanceTester $I ): void {
		$I->loginAsAdmin();
		$postId = $I->havePostInDatabase( [
			'post_status' => 'publish',
			'post_name' => 'leave-a-comment',
		] );
		$commentId = $I->haveCommentInDatabase( $postId, [
			'comment_approved' => '0',
		] );
		$I->amOnAdminPage( '/comment.php?action=editcomment&c=' . $commentId );
		$I->switchOff();
		$I->seeCurrentUrlEquals( '/leave-a-comment/?switched_off=true' );
		$I->amLoggedOut();
	}
}
