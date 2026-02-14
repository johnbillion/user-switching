<?php declare(strict_types = 1);

namespace UserSwitching\Tests;

use user_switching;

final class ShutdownHandlerTest extends Test {
	/**
	 * Test that action_shutdown_for_wp_die() doesn't emit warnings during early boot.
	 *
	 * This is a regression test for an edge case where wp_die() occurs during early boot
	 * before the cookie constants are defined (i.e., before plugins_loaded fires).
	 * The guard condition checking did_action('plugins_loaded') ensures the method returns
	 * early in this scenario without accessing undefined constants.
	 *
	 * @covers user_switching::action_shutdown_for_wp_die
	 */
	public function testShutdownHandlerReturnsEarlyBeforePluginsLoaded(): void {
		global $wp_actions;

		// Get the plugin instance
		$plugin = user_switching();

		// Simulate a wp_die scenario
		do_action( 'admin_page_access_denied' );

		// Temporarily modify the action count to simulate plugins_loaded not having fired
		$original_count = $wp_actions['plugins_loaded'] ?? 0;
		$wp_actions['plugins_loaded'] = 0;

		try {
			// Set up error handler to catch any warnings about undefined constants
			$warning_caught = false;
			$warning_message = '';
			set_error_handler(
				function( $errno, $errstr ) use ( &$warning_caught, &$warning_message ) {
					// Check specifically for undefined constant warnings
					if ( ( $errno === E_WARNING || $errno === E_NOTICE ) &&
					     strpos( $errstr, 'USER_SWITCHING' ) !== false ) {
						$warning_caught = true;
						$warning_message = $errstr;
					}
					return true; // Suppress the warning from being displayed
				},
				E_ALL
			);

			// Call the shutdown handler - should return early without accessing constants
			ob_start();
			$plugin->action_shutdown_for_wp_die();
			$output = ob_get_clean();

			// Restore error handler
			restore_error_handler();

			// Assert no warnings about USER_SWITCHING constants were emitted
			self::assertFalse(
				$warning_caught,
				'No warnings about USER_SWITCHING constants should be emitted when plugins_loaded has not fired. Got: ' . $warning_message
			);

			// Assert no output was produced (method returned early)
			self::assertEmpty(
				$output,
				'No output should be produced when plugins_loaded has not fired'
			);
		} finally {
			// Ensure action count is restored even if test fails
			$wp_actions['plugins_loaded'] = $original_count;
		}
	}

	/**
	 * Test that action_shutdown_for_wp_die() handles the case where no user is switched.
	 *
	 * @covers user_switching::action_shutdown_for_wp_die
	 */
	public function testShutdownHandlerReturnsEarlyWithoutSwitchedUser(): void {
		// Get the plugin instance
		$plugin = user_switching();

		// Simulate a wp_die scenario
		do_action( 'admin_page_access_denied' );

		// Ensure no user is switched (no old user cookie)
		unset( $_COOKIE[ USER_SWITCHING_OLDUSER_COOKIE ] );

		// Call the shutdown handler - should return early without output
		ob_start();
		$plugin->action_shutdown_for_wp_die();
		$output = ob_get_clean();

		// Should have no output since there's no old user to switch back to
		self::assertEmpty( $output, 'No output expected when no old user exists' );
	}

	/**
	 * Test that action_shutdown_for_wp_die() outputs the switch back link when appropriate.
	 *
	 * @covers user_switching::action_shutdown_for_wp_die
	 */
	public function testShutdownHandlerOutputsSwitchBackLink(): void {
		$admin = self::$testers['admin'];
		$author = self::$users['author'];

		// Set up a switched user scenario
		wp_set_current_user( $admin->ID );
		switch_to_user( $author->ID );

		// Simulate a wp_die scenario
		do_action( 'admin_page_access_denied' );

		// Get the plugin instance
		$plugin = user_switching();

		// Call the shutdown handler
		ob_start();
		$plugin->action_shutdown_for_wp_die();
		$output = ob_get_clean();

		// Should contain the switch back link
		self::assertStringContainsString( 'user_switching_wp_die', $output, 'Should output the switch back element' );
		self::assertStringContainsString( 'Switch back', $output, 'Should contain switch back text' );
		self::assertStringContainsString( $admin->display_name, $output, 'Should contain the old user display name' );
	}
}
