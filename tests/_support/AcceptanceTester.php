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
class AcceptanceTester extends \Codeception\Actor {
	use _generated\AcceptanceTesterActions;

	/**
	 * Put the block editor into a state where the items we need to interact with are
	 * actually usable.
	 *
	 * @return void
	 */
	public function amNotUsingTheEditorForTheFirstTime() {
		$user_id = 1;
		$key = sprintf(
			'%spersisted_preferences',
			$this->grabTablePrefix()
		);
		$value = [
			'core/edit-post' => [
				'fullscreenMode' => false,
				'welcomeGuide' => false,
			],
		];

		$this->haveUserMetaInDatabase(
			$user_id,
			$key,
			[
				$value,
			]
		);

		// Pre-6.1 support for when the editor welcome screen state was only stored in local storage.
		$value = [
			'core/edit-post' => [
				'preferences' => [
					'features' => [
						'fullscreenMode' => false,
						'welcomeGuide' => false,
					],
				],
			],
		];

		$this->executeJS(
			sprintf(
				"localStorage.setItem( 'WP_DATA_USER_%d', '%s' );",
				$user_id,
				json_encode( $value )
			)
		);
	}

	/**
	 * Switch to the specified user
	 *
	 * @param string $user_login
	 */
	public function switchToUser( $user_login ) {
		$user_id = $this->grabUserIdFromDatabase( $user_login );

		$this->amOnAdminPage( sprintf( 'user-edit.php?user_id=%d', $user_id ) );
		$this->click( '#user_switching_switcher' );
	}

	/**
	 * Switch off
	 */
	public function switchOff() {
		$this->moveMouseOver( '#wp-admin-bar-my-account' );
		$this->click( 'Switch Off' );
	}

	/**
	 * Switch back to the original user
	 *
	 * @param string $user_login
	 */
	public function switchBackTo( $user_login ) {
		$display_name = $this->grabFromDatabase(
			$this->grabUsersTableName(),
			'display_name',
			[
				'user_login' => $user_login,
			]
		);

		try {
			$this->moveMouseOver( '#wp-admin-bar-my-account' );
		} catch ( \Codeception\Exception\ElementNotFound $e ) {
			// Nothing.
		}

		$this->click( sprintf(
			'Switch back to %s',
			$display_name
		) );
	}

	/**
	 * Verify that the user is logged in as the specified user
	 *
	 * @param string $user_login
	 */
	public function amLoggedInAs( $user_login ) {
		$display_name = $this->grabFromDatabase(
			$this->grabUsersTableName(),
			'display_name',
			[
				'user_login' => $user_login,
			]
		);

		$this->see(
			$display_name,
			'#wpadminbar .display-name'
		);
	}

	/**
	 * Verify that the user is logged out
	 */
	public function amLoggedOut() {
		$this->cantSeeElement( '#wpadminbar .display-name' );
	}

	/**
	 * Verify the page language
	 *
	 * @param string $lang
	 */
	public function canSeeThePageInLanguage( $lang ) {
		$this->canSeeTheElementInLanguage( 'html', $lang );
	}

	/**
	 * Verify the language of an element
	 *
	 * @param string $selector
	 * @param string $lang
	 */
	public function canSeeTheElementInLanguage( $selector, $lang ) {
		$this->seeElement( $selector, [
			'lang' => $lang,
		] );
	}

	/**
	 * Checks that the current page contains an admin success notice.
	 *
	 * @param string $text The message text to search for.
	 */
	public function seeAdminSuccessNotice( $text ) {
		return $this->see( $text, '.notice-success' );
	}

	/**
	 * Checks that the current page contains an admin success notice.
	 *
	 * @param string $text The message text to search for.
	 */
	public function seeAdminWarningNotice( $text ) {
		return $this->see( $text, '.notice-warning' );
	}

	/**
	 * Checks that the current page contains an admin success notice.
	 *
	 * @param string $text The message text to search for.
	 */
	public function seeAdminErrorNotice( $text ) {
		return $this->see( $text, '.notice-error' );
	}

	/**
	 * Checks that the current page contains an admin success notice.
	 *
	 * @param string $text The message text to search for.
	 */
	public function seeAdminInfoNotice( $text ) {
		return $this->see( $text, '.notice-info' );
	}
}
