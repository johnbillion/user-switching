<?php

use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ElementTextException;
use Behat\Mink\Exception\ExpectationException;
use PaulGibbs\WordpressBehatExtension\Context\RawWordpressContext as WordPressContext;
use PaulGibbs\WordpressBehatExtension\Context\Traits\UserAwareContextTrait as UserContext;
use PHPUnit\Framework\Assert;

/**
 * Defines application features from the specific context.
 */
class UserSwitchingContext extends WordPressContext {
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

		Assert::assertNotEmpty( $user_id );

		$this->visitPath( sprintf( 'wp-admin/user-edit.php?user_id=%d', $user_id ) );
		$this->getSession()->getPage()->clickLink( "Switch\xc2\xa0To" );
	}

	/**
	 * Switch off
	 *
	 * @When /^(?:|I )switch off$/
	 */
	public function switch_off() {
		$this->getSession()->getPage()->clickLink( "Switch Off" );
	}

	/**
	 * Switch back to the original user
	 *
	 * @param string $user_id
	 *
	 * @When /^(?:|I )switch back to "(?P<user_id>[^"]+)"$/
	 */
	public function switch_back( $user_id ) {
		$display_name = $this->getUserDataFromUsername( 'display_name', $user_id );

		Assert::assertNotEmpty( $user_id );
		Assert::assertNotEmpty( $display_name );

		$this->getSession()->getPage()->clickLink( sprintf(
			'Switch back to %1$s (%2$s)',
			$display_name,
			$user_id
		) );
	}

	/**
	 * Verify that the user is logged in as the specified user
	 *
	 * @param string $user_id
	 *
	 * @Then /^(?:|I )should be logged in as [user ]?"(?P<user_id>[^"]+)"$/
	 *
	 * @throws ElementNotFoundException If the display name could not be found.
	 * @throws ElementTextException     If the display name is incorrect.
	 */
	public function logged_in_as( $user_id ) {
		$display_name = $this->getUserDataFromUsername( 'display_name', $user_id );

		Assert::assertNotEmpty( $display_name );

		$this->visitPath( '/' );

		$browser  = $this->getSession();
		$selector = '#wpadminbar .display-name';
		$element  = $browser->getPage()->find( 'css', $selector );

		if ( ! $element ) {
			throw new ElementNotFoundException(
				$browser->getDriver(),
				'element',
				'css',
				$selector
			);
		}

		if ( $display_name !== $element->getText() ) {
			throw new ElementTextException(
				sprintf(
					'The user is logged in as "%s"',
					$element->getText()
				),
				$browser->getDriver(),
				$element
			);
		}
	}

	/**
	 * Verify that the user is logged out
	 *
	 * @Then /^(?:|I )should be logged out$/
	 *
	 * @throws ExpectationException If the user is not logged out.
	 */
	public function logged_out() {
		$this->visitPath( '/' );

		$browser  = $this->getSession();
		$selector = '#wpadminbar .display-name';
		$element  = $browser->getPage()->find( 'css', $selector );

		if ( $element ) {
			throw new ExpectationException(
				'The user is not logged out',
				$browser->getDriver()
			);
		}
	}

}
