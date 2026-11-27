<?php declare(strict_types = 1);

namespace UserSwitching\Tests;

/**
 * These tests run in separate processes because `DONOTCACHEPAGE` is a constant.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 * @covers \user_switching::action_send_headers
 */
final class CachingTest extends Test {
	public function testPageCachingIsPreventedWhenSwitchedOff(): void {
		wp_set_current_user( self::$testers['admin']->ID );
		switch_off_user();

		do_action( 'send_headers', $GLOBALS['wp'] );

		self::assertTrue( defined( 'DONOTCACHEPAGE' ) );
	}

	public function testPageCachingIsNotPreventedWhenSwitchedToAnotherUser(): void {
		wp_set_current_user( self::$testers['admin']->ID );
		switch_to_user( self::$users['editor']->ID );

		do_action( 'send_headers', $GLOBALS['wp'] );

		self::assertFalse( defined( 'DONOTCACHEPAGE' ) );
	}

	public function testPageCachingIsNotPreventedWhenLoggedOut(): void {
		do_action( 'send_headers', $GLOBALS['wp'] );

		self::assertFalse( defined( 'DONOTCACHEPAGE' ) );
	}

	public function testPageCachingIsNotPreventedWithAnInvalidOldUserCookie(): void {
		$_COOKIE[ USER_SWITCHING_OLDUSER_COOKIE ] = 'invalid';

		do_action( 'send_headers', $GLOBALS['wp'] );

		self::assertFalse( defined( 'DONOTCACHEPAGE' ) );
	}
}
