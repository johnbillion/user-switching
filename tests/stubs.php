<?php

// WP core constants:
define( 'COOKIE_DOMAIN', '' );
define( 'COOKIEHASH', '' );
define( 'COOKIEPATH', '' );
define( 'SITECOOKIEPATH', '' );

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
	public $active_components = [];
}

/**
 * @return int
 */
function bbp_get_user_id() {}

/**
 * @return string
 */
function bbp_get_user_profile_url( int $user_id ) {}
