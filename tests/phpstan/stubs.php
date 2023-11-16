<?php

// User Switching constants:
define( 'USER_SWITCHING_COOKIE', '' );
define( 'USER_SWITCHING_OLDUSER_COOKIE', '' );
define( 'USER_SWITCHING_SECURE_COOKIE', '' );

/**
 * @return bool
 */
function bp_is_user() {}

/**
 * @return int
 */
function bp_displayed_user_id() {}

/**
 * @return bool
 */
function bp_is_members_directory() {}

/**
 * @return int
 */
function bp_get_member_user_id() {}

/**
 * @return string
 */
function bp_core_get_user_domain( int $user_id ) {}

/**
 * @return string
 */
function bp_get_button( array $args ) {}

/**
 * @return \BuddyPress
 */
function buddypress() {}

class BuddyPress {
	/**
	 * @var array<string, string>
	 */
	public $active_components;
}

/**
 * @return int
 */
function bbp_get_user_id() {}

/**
 * @return string
 */
function bbp_get_user_profile_url( int $user_id ) {}

class WooCommerce {
	/**
	 * @var \WC_Session
	 */
	public $session;
}

class WC_Session {
	/**
	 * @return void
	 */
	public function forget_session() {}
}

class WC_Order {
	/**
	 * @return \WP_User|false
	 */
	public function get_user() {}

	/**
	 * @return string
	 */
	public function get_view_order_url() {}
}

/**
 * @return \WooCommerce
 */
function WC() {}
