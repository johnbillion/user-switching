<?php
use Behat\Behat\Context\Context,
	Behat\Behat\Context\SnippetAcceptingContext,
	Behat\Gherkin\Node\TableNode;

use Johnbillion\WordPressExtension\Context\WordPressContext;

/**
 * Defines application features from the specific context.
 */
class UserSwitchingContext extends WordPressContext implements Context, SnippetAcceptingContext {

	/**
	 * Switch to the specified user
	 *
	 * @param string $user_id
	 *
	 * @When /^(?:|I )switch to user "(?P<user_id>[^"]+)"$/
	 */
	public function switch_to_user( $user_id ) {

		if ( is_numeric( $user_id ) ) {
			$user = get_userdata( $user_id );
		} else {
			$user = get_user_by( 'login', $user_id );
		}

		assertTrue( $user->exists() );

		$this->visitPath( sprintf( 'wp-admin/user-edit.php?user_id=%d', $user->ID ) );
		$this->clickLink( "Switch\xc2\xa0To" );

	}

	/**
	 * Switch to the specified user
	 *
	 * @param string $user_id
	 *
	 * @Then /^(?:|I )should be logged in as [user ]?"(?P<user_id>[^"]+)"$/
	 */
	public function logged_in_as( $user_id ) {

		if ( is_numeric( $user_id ) ) {
			$user = get_userdata( $user_id );
		} else {
			$user = get_user_by( 'login', $user_id );
		}

		assertTrue( $user->exists() );

		$this->visitPath( '/' );

		assertTrue( $this->getSession()->getPage()->hasContent( sprintf( 'Howdy, %s', $user->display_name ) ) );
	}

}
