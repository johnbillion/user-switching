<?php
use Behat\Behat\Context\Context,
	Behat\Behat\Context\SnippetAcceptingContext,
	Behat\Gherkin\Node\TableNode;

use PaulGibbs\WordpressBehatExtension\Context\RawWordpressContext as WordPressContext;
use PaulGibbs\WordpressBehatExtension\Context\Traits\UserAwareContextTrait as UserContext;

/**
 * Defines application features from the specific context.
 */
class UserSwitchingContext extends WordPressContext implements Context, SnippetAcceptingContext {
    use UserContext;

	/**
	 * Switch to the specified user
	 *
	 * @param string $user_id
	 *
	 * @When /^(?:|I )switch to user "(?P<user_id>[^"]+)"$/
	 */
	public function switch_to_user( $user_id ) {
		$user_id = $this->getUserIdFromLogin( $user_id );

		PHPUnit_Framework_Assert::assertNotEmpty( $user_id );

		$this->visitPath( sprintf( 'wp-admin/user-edit.php?user_id=%d', $user_id ) );
		$this->getSession()->getPage()->clickLink( "Switch\xc2\xa0To" );
	}

	/**
	 * Verify that the user is logged in as the specified user
	 *
	 * @param string $user_id
	 *
	 * @Then /^(?:|I )should be logged in as [user ]?"(?P<user_id>[^"]+)"$/
	 */
	public function logged_in_as( $user_id ) {
		$display_name = $this->getUserDataFromUsername( 'display_name', $user_id );

		PHPUnit_Framework_Assert::assertNotEmpty( $display_name );

		$this->visitPath( '/' );

		PHPUnit_Framework_Assert::assertTrue( $this->getSession()->getPage()->hasContent( sprintf( 'Howdy, %s', $display_name ) ) );
	}

}
