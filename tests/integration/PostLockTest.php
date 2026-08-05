<?php declare(strict_types = 1);

namespace UserSwitching\Tests;

/**
 * @covers \user_switching::filter_post_lock_window_after_switch_back
 */
final class PostLockTest extends Test {
	public function testPostLockWindowIsZeroedOnPostEditScreenAfterSwitchBack(): void {
		$_GET = [
			'switched_back' => 'true',
			'action' => 'edit',
		];

		self::assertSame( 0, apply_filters( 'wp_check_post_lock_window', 150 ) );
	}

	public function testPostLockWindowIsUnchangedWithoutSwitchedBack(): void {
		$_GET = [
			'action' => 'edit',
		];

		self::assertSame( 150, apply_filters( 'wp_check_post_lock_window', 150 ) );
	}

	public function testPostLockWindowIsUnchangedWhenActionIsNotEdit(): void {
		$_GET = [
			'switched_back' => 'true',
		];

		self::assertSame( 150, apply_filters( 'wp_check_post_lock_window', 150 ) );
	}

	public function testStalePostLockIsTreatedAsExpiredAfterSwitchBack(): void {
		$current = self::$testers['admin'];
		$previous = self::$users['editor'];

		$post = self::factory()->post->create_and_get();
		self::assertInstanceOf( 'WP_Post', $post );

		wp_set_current_user( $current->ID );

		// Simulate a fresh lock left behind by the user being switched away from.
		update_post_meta( $post->ID, '_edit_lock', time() . ':' . $previous->ID );

		// Without the after-switch-back query args, the lock is considered active.
		$_GET = [];
		self::assertSame( $previous->ID, wp_check_post_lock( $post->ID ) );

		// With the after-switch-back query args, the lock is treated as expired.
		$_GET = [
			'switched_back' => 'true',
			'action' => 'edit',
		];
		self::assertFalse( wp_check_post_lock( $post->ID ) );
	}
}
