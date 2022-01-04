<?php

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 */
class FunctionalTester extends \Codeception\Actor {
	use _generated\FunctionalTesterActions;

	/**
	 * Switch to the specified user
	 *
	 * @param string $user_id
	 */
	public function switchToUser( $user_id ) {
		throw new \Exception( 'Not implemented' );
	}

	/**
	 * Switch off
	 */
	public function switchOff() {
		throw new \Exception( 'Not implemented' );
	}

	/**
	 * Switch back to the original user
	 *
	 * @param string $user_id
	 */
	public function switchBack( $user_id ) {
		throw new \Exception( 'Not implemented' );
	}

	/**
	 * Verify that the user is logged in as the specified user
	 *
	 * @param string $user_id
	 */
	public function loggedInAs( $user_id ) {
		throw new \Exception( 'Not implemented' );
	}

	/**
	 * Verify that the user is logged out
	 */
	public function loggedOut() {
		throw new \Exception( 'Not implemented' );
	}

	/**
	 * Verify the page language
	 *
	 * @param string $lang
	 */
	public function thePageLanguageShouldBe( $lang ) {
		throw new \Exception( 'Not implemented' );
	}

	/**
	 * Verify the language of an element
	 *
	 * @param string $selector
	 * @param string $lang
	 */
	public function theElementLanguageShouldBe( $selector, $lang ) {
		throw new \Exception( 'Not implemented' );
	}
}
