<?php declare(strict_types = 1);

namespace UserSwitching\Tests;

/**
 * @covers \user_switching::filter_wp_headers
 */
final class CachingTest extends Test {
	public function testPageCachingIsPreventedWhenSwitchedOff(): void {
		wp_set_current_user( self::$testers['admin']->ID );
		switch_off_user();

		$headers = $this->get_headers();

		self::assertArrayHasKey( 'Cache-Control', $headers );
		self::assertIsString( $headers['Cache-Control'] );
		self::assertStringContainsString( 'no-cache', $headers['Cache-Control'] );
		self::assertStringContainsString( 'max-age=0', $headers['Cache-Control'] );
		self::assertTrue( defined( 'DONOTCACHEPAGE' ) );
	}

	public function testHeadersAreNotChangedWhenSwitchedToAnotherUser(): void {
		wp_set_current_user( self::$testers['admin']->ID );
		switch_to_user( self::$users['editor']->ID );

		self::assertSame( [], $this->get_headers() );
	}

	public function testHeadersAreNotChangedWhenLoggedOut(): void {
		self::assertSame( [], $this->get_headers() );
	}

	public function testHeadersAreNotChangedWithAnInvalidOldUserCookie(): void {
		$_COOKIE[ USER_SWITCHING_OLDUSER_COOKIE ] = 'invalid';

		self::assertSame( [], $this->get_headers() );
	}

	/**
	 * @return array<string, string|false>
	 */
	private function get_headers(): array {
		/** @var array<string, string|false> */
		return apply_filters( 'wp_headers', [], $GLOBALS['wp'] );
	}
}
