<?php
/**
 * Revocation of sessions created by User Switching.
 *
 * @package user-switching
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Extends the core session manager so that destroying a session also destroys any sessions that were
 * created by switching to another user from that session.
 */
final class User_Switching_Session_Tokens extends WP_User_Meta_Session_Tokens {
	/**
	 * Updates or destroys a session.
	 *
	 * @param string                    $verifier Verifier for the session to update.
	 * @param array<string, mixed>|null $session  Optional. Session. Omitting this argument destroys the session.
	 * @return void
	 */
	protected function update_session( $verifier, $session = null ) {
		parent::update_session( $verifier, $session );

		if ( null === $session ) {
			$this->destroy_switched_sessions( $verifier );
		}
	}

	/**
	 * Destroys all sessions for this user except the one with the given verifier.
	 *
	 * @param string $verifier Verifier of the session to keep.
	 * @return void
	 */
	protected function destroy_other_sessions( $verifier ) {
		parent::destroy_other_sessions( $verifier );
		$this->destroy_switched_sessions( $verifier, true );
	}

	/**
	 * Destroys all sessions for this user.
	 *
	 * @return void
	 */
	protected function destroy_all_sessions() {
		parent::destroy_all_sessions();
		$this->destroy_switched_sessions();
	}

	/**
	 * Destroys sessions of other users that were switched into from a session of this user.
	 *
	 * @param string|null $verifier Optional. Verifier of the originating session, or null for all sessions.
	 * @param bool        $keep     Optional. Whether to destroy all switched sessions except those that originated
	 *                              from `$verifier`. Default false.
	 */
	private function destroy_switched_sessions( ?string $verifier = null, bool $keep = false ): void {
		$user_id = (int) $this->user_id;

		// The originating session is recorded on the target user's session, so find users that have one.
		$user_ids = get_users( [
			'blog_id' => 0,
			'fields' => 'ID',
			'meta_key' => 'session_tokens',
			'meta_value' => '"switched_from_id";i:' . $user_id . ';',
			'meta_compare' => 'LIKE',
		] );

		foreach ( $user_ids as $target_id ) {
			$target = new self( (int) $target_id );

			foreach ( $target->get_sessions() as $target_verifier => $session ) {
				if ( ( $session['switched_from_id'] ?? null ) !== $user_id ) {
					continue;
				}

				if ( empty( $session['switched_from_session'] ) || ! is_string( $session['switched_from_session'] ) ) {
					continue;
				}

				$origin = hash( 'sha256', $session['switched_from_session'] );

				if ( null === $verifier || ( $origin === $verifier ) !== $keep ) {
					// Only the verifier is known here, not the token that destroy() needs.
					$target->update_session( $target_verifier );
				}
			}
		}
	}
}
