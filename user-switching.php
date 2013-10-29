<?php
/*
Plugin Name:  User Switching
Description:  Instant switching between user accounts in WordPress
Version:      0.8.4
Plugin URI:   https://lud.icro.us/wordpress-plugin-user-switching/
Author:       John Blackbourn
Author URI:   https://johnblackbourn.com/
Text Domain:  user-switching
Domain Path:  /languages/
License:      GPL v2 or later

Copyright © 2013 John Blackbourn

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

*/

class user_switching {

	/**
	 * Class constructor. Set up some filters and actions.
	 */
	public function __construct() {

		# Required functionality:
		add_filter( 'user_has_cap',                 array( $this, 'filter_user_has_cap' ), 10, 3 );
		add_filter( 'map_meta_cap',                 array( $this, 'filter_map_meta_cap' ), 10, 4 );
		add_filter( 'user_row_actions',             array( $this, 'filter_user_row_actions' ), 10, 2 );
		add_action( 'plugins_loaded',               array( $this, 'action_plugins_loaded' ) );
		add_action( 'init',                         array( $this, 'action_init' ) );
		add_action( 'all_admin_notices',            array( $this, 'action_admin_notices' ), 1 );
		add_action( 'wp_logout',                    'wp_clear_olduser_cookie' );
		add_action( 'wp_login',                     'wp_clear_olduser_cookie' );
    
		# Nice-to-haves:
		add_filter( 'ms_user_row_actions',          array( $this, 'filter_user_row_actions' ), 10, 2 );
		add_action( 'wp_footer',                    array( $this, 'action_wp_footer' ) );
		add_action( 'personal_options',             array( $this, 'action_personal_options' ) );
		add_action( 'admin_bar_menu',               array( $this, 'action_admin_bar_menu' ), 11 );
		add_action( 'bp_adminbar_menus',            array( $this, 'action_bp_menus' ), 9 );
		add_action( 'bp_member_header_actions',     array( $this, 'action_bp_button' ), 11 );
		add_filter( 'login_message',                array( $this, 'filter_login_message' ), 1 );
		add_action( 'bp_directory_members_actions', array( $this, 'action_bp_button' ), 11 );

	}

	/**
	 * Define the name of the old user cookie. Uses WordPress' cookie hash for increased security.
	 *
	 * @return null
	 */
	public function action_plugins_loaded() {
		if ( !defined( 'OLDUSER_COOKIE' ) )
			define( 'OLDUSER_COOKIE', 'wordpress_olduser_' . COOKIEHASH );
	}

	/**
	 * Output the 'Switch To' link on the user editing screen if we have permission to switch to this user.
	 *
	 * @param WP_User $user User object for this screen
	 * @return null
	 */
	public function action_personal_options( WP_User $user ) {

		if ( ! $link = self::maybe_switch_url( $user->ID ) )
			return;

		?>
		<tr>
			<th scope="row"><?php _ex( 'User Switching', 'User Switching title on user profile screen', 'user-switching' ); ?></th>
			<td><a href="<?php echo $link; ?>"><?php _e( 'Switch&nbsp;To', 'user-switching' ); ?></a></td>
		</tr>
		<?php
	}

	/**
	 * Return whether or not the current logged in user is being remembered in the form of a persistent browser
	 * cookie (ie. they checked the 'Remember Me' check box when they logged in). This is used to persist the
	 * 'remember me' value when the user switches to another user.
	 *
	 * @return bool Whether the current user is being 'remembered' or not.
	 */
	public static function remember() {

		$current     = wp_parse_auth_cookie( '', 'logged_in' );
		$cookie_life = apply_filters( 'auth_cookie_expiration', 172800, get_current_user_id(), false );

		# Here we calculate the expiration length of the current auth cookie and compare it to the default expiration.
		# If it's greater than this, then we know the user checked 'Remember Me' when they logged in.
		return ( ( $current['expiration'] - time() ) > $cookie_life );

	}

	/**
	 * Load localisation files and route actions depending on the 'action' query var.
	 * 
	 * @return null
	 */
	public function action_init() {

		load_plugin_textdomain( 'user-switching', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		if ( !isset( $_REQUEST['action'] ) )
			return;

		if ( isset( $_REQUEST['redirect_to'] ) and !empty( $_REQUEST['redirect_to'] ) )
			$redirect_to = remove_query_arg( self::remove_query_args(), $_REQUEST['redirect_to'] );
		else
			$redirect_to = false;

		switch ( $_REQUEST['action'] ) {

			# We're attempting to switch to another user:
			case 'switch_to_user':
				$user_id = absint( $_REQUEST['user_id'] );

				check_admin_referer( "switch_to_user_{$user_id}" );

				# Switch user:
				if ( switch_to_user( $user_id, self::remember() ) ) {

					# Redirect to the dashboard or the home URL depending on capabilities:
					if ( $redirect_to )
						wp_safe_redirect( add_query_arg( array( 'user_switched' => 'true' ), $redirect_to ) );
					else if ( !current_user_can( 'read' ) )
						wp_redirect( add_query_arg( array( 'user_switched' => 'true' ), home_url() ) );
					else
						wp_redirect( add_query_arg( array( 'user_switched' => 'true' ), admin_url() ) );
					die();

				} else {
					wp_die( __( 'Could not switch users.', 'user-switching' ) );
				}
				break;

			# We're attempting to switch back to the originating user:
			case 'switch_to_olduser':

				check_admin_referer( 'switch_to_olduser' );

				# Fetch the originating user data:
				if ( !$old_user = self::get_old_user() )
					wp_die( __( 'Could not switch users.', 'user-switching' ) );

				# Switch user:
				if ( switch_to_user( $old_user->ID, self::remember(), false ) ) {
					if ( $redirect_to )
						wp_safe_redirect( add_query_arg( array( 'user_switched' => 'true', 'switched_back' => 'true' ), $redirect_to ) );
					else
						wp_redirect( add_query_arg( array( 'user_switched' => 'true', 'switched_back' => 'true' ), admin_url( 'users.php' ) ) );
					die();
				} else {
					wp_die( __( 'Could not switch users.', 'user-switching' ) );
				}
				break;

			# We're attempting to switch off the current user:
			case 'switch_off':

				check_admin_referer( 'switch_off' );

				# Switch off:
				if ( switch_off_user() ) {
					if ( $redirect_to )
						wp_safe_redirect( add_query_arg( array( 'switched_off' => 'true' ), $redirect_to ) );
					else
						wp_redirect( add_query_arg( array( 'switched_off' => 'true' ), home_url() ) );
					die();
				} else {
					wp_die( __( 'Could not switch off.', 'user-switching' ) );
				}
				break;

		}

	}

	/**
	 * Display the 'Switched to {user}' and 'Switch back to {user}' messages in the admin area.
	 *
	 * @return null
	 */
	public function action_admin_notices() {
		$user = wp_get_current_user();

		if ( $old_user = self::get_old_user() ) {

			?>
			<div id="user_switching" class="updated">
				<p><?php
					if ( isset( $_GET['user_switched'] ) )
						printf( __( 'Switched to %1$s (%2$s).', 'user-switching' ), $user->display_name, $user->user_login );
					$url = add_query_arg( array(
						'redirect_to' => urlencode( self::current_url() )
					), self::switch_back_url() );
					printf( ' <a href="%s">%s</a>.', $url, sprintf( __( 'Switch back to %1$s (%2$s)', 'user-switching' ), $old_user->display_name, $old_user->user_login ) );
				?></p>
			</div>
			<?php

		} else if ( isset( $_GET['user_switched'] ) ) {

			?>
			<div id="user_switching" class="updated">
				<p><?php
					if ( isset( $_GET['switched_back'] ) )
						printf( __( 'Switched back to %1$s (%2$s).', 'user-switching' ), $user->display_name, $user->user_login );
					else
						printf( __( 'Switched to %1$s (%2$s).', 'user-switching' ), $user->display_name, $user->user_login );
				?></p>
			</div>
			<?php

		}
	}

	/**
	 * Validate the latest item in the old_user cookie and return its user data.
	 *
	 * @return bool|WP_User False if there's no old user cookie or it's invalid, WP_User object if it's present and valid.
	 */
	public static function get_old_user() {
		$cookie = wp_get_olduser_cookie();
		if ( !empty( $cookie ) ) {
			if ( $old_user_id = wp_validate_auth_cookie( end( $cookie ), 'old_user' ) )
				return get_userdata( $old_user_id );
		}
		return false;
	}

	/**
	 * Adds a 'Switch back to {user}' link to the account menu in WordPress' admin bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The admin bar object
	 * @return null
	 */
	public function action_admin_bar_menu( WP_Admin_Bar $wp_admin_bar ) {

		if ( !function_exists( 'is_admin_bar_showing' ) )
			return;
		if ( !is_admin_bar_showing() )
			return;

		if ( method_exists( $wp_admin_bar, 'get_node' ) and $wp_admin_bar->get_node( 'user-actions' ) )
			$parent = 'user-actions';
		else if ( get_option( 'show_avatars' ) )
			$parent = 'my-account-with-avatar';
		else
			$parent = 'my-account';

		if ( $old_user = self::get_old_user() ) {

			$wp_admin_bar->add_menu( array(
				'parent' => $parent,
				'id'     => 'switch-back',
				'title'  => sprintf( __( 'Switch back to %1$s (%2$s)', 'user-switching' ), $old_user->display_name, $old_user->user_login ),
				'href'   => add_query_arg( array(
					'redirect_to' => urlencode( self::current_url() )
				), self::switch_back_url() )
			) );

		}

		if ( current_user_can( 'switch_off' ) ) {

			$url = self::switch_off_url();
			if ( !is_admin() ) {
				$url = add_query_arg( array(
					'redirect_to' => urlencode( self::current_url() )
				), $url );
			}

			$wp_admin_bar->add_menu( array(
				'parent' => $parent,
				'id'     => 'switch-off',
				'title'  => __( 'Switch Off', 'user-switching' ),
				'href'   => $url
			) );

		}

	}

	/**
	 * Adds a 'Switch back to {user}' link to the WordPress footer if the admin toolbar isn't showing.
	 *
	 * @return null
	 */
	public function action_wp_footer() {

		if ( !is_admin_bar_showing() and $old_user = self::get_old_user() ) {
			$link = sprintf( __( 'Switch back to %1$s (%2$s)', 'user-switching' ), $old_user->display_name, $old_user->user_login );
			$url = add_query_arg( array(
				'redirect_to' => urlencode( self::current_url() )
			), self::switch_back_url() );
			echo '<p id="user_switching_switch_on"><a href="' . $url . '">' . $link . '</a></p>';
		}

	}

	/**
	 * Adds a 'Switch back to {user}' link to the WordPress login screen.
	 *
	 * @param string $message The login screen message
	 * @return string The login screen message
	 */
	public function filter_login_message( $message ) {

		if ( $old_user = self::get_old_user() ) {
			$link = sprintf( __( 'Switch back to %1$s (%2$s)', 'user-switching' ), $old_user->display_name, $old_user->user_login );
			$url = self::switch_back_url();
			if ( isset( $_REQUEST['redirect_to'] ) and !empty( $_REQUEST['redirect_to'] ) ) {
				$url = add_query_arg( array(
					'redirect_to' => $_REQUEST['redirect_to']
				), $url );
			}
			$message .= '<p class="message"><a href="' . $url . '">' . $link . '</a></p>';
		}

		return $message;

	}

	/**
	 * Adds a 'Switch To' link to each list of user actions on the Users screen.
	 *
	 * @param array   $actions The actions to display for this user row
	 * @param WP_User $user    The user object displayed in this row
	 * @return array The actions to display for this user row
	 */
	public function filter_user_row_actions( array $actions, WP_User $user ) {

		if ( ! $link = self::maybe_switch_url( $user->ID ) )
			return $actions;

		$actions['switch_to_user'] = '<a href="' . $link . '">' . __( 'Switch&nbsp;To', 'user-switching' ) . '</a>';

		return $actions;
	}

	/**
	 * Adds a 'Switch back to {user}' link to the BuddyPress admin bar.
	 *
	 * @return null
	 */
	public function action_bp_menus() {

		if ( !is_admin() and $old_user = self::get_old_user() ) {

			echo '<li id="bp-adminbar-userswitching-menu" style="background-image:none"><a href="' . self::switch_back_url() . '">';
			printf( __( 'Switch back to %1$s (%2$s)', 'user-switching' ), $old_user->display_name, $old_user->user_login );
			echo '</a></li>';

		}

	}

	/**
	 * Adds a 'Switch To' link to each member's profile page and profile listings in BuddyPress.
	 *
	 * @return null
	 */
	public function action_bp_button() {

		global $bp, $members_template;

		if ( !empty( $members_template ) and empty( $bp->displayed_user->id ) )
			$id = absint( $members_template->member->id );
		else
			$id = absint( $bp->displayed_user->id );

		if ( ! $link = self::maybe_switch_url( $id ) )
			return;

		$link = add_query_arg( array(
			'redirect_to' => urlencode( bp_core_get_user_domain( $id ) )
		), $link );

		# Workaround for https://buddypress.trac.wordpress.org/ticket/4212
		$components = array_keys( $bp->active_components );
		if ( !empty( $components ) )
			$component = reset( $components );
		else
			$component = 'core';

		echo bp_get_button( array(
			'id'         => 'user_switching',
			'component'  => $component, 
			'link_href'  => $link,
			'link_text'  => __( 'Switch&nbsp;To', 'user-switching' )
		) );

	}

	/**
	 * Helper function. Returns the switch to or switch back URL for a given user ID.
	 *
	 * @param int $user_id The user ID to be switched to.
	 * @return string|bool The required URL, or false if there's no old user or the user doesn't have the required capability.
	 */
	public static function maybe_switch_url( $user_id ) {

		$old_user = self::get_old_user();

		if ( $old_user and ( $old_user->ID == $user_id ) )
			return self::switch_back_url();
		else if ( current_user_can( 'switch_to_user', $user_id ) )
			return self::switch_to_url( $user_id );
		else
			return false;

	}

	/**
	 * Helper function. Returns the nonce-secured URL needed to switch to a given user ID.
	 *
	 * @param int $user_id The user ID to be switched to.
	 * @return string The required URL
	 */
	public static function switch_to_url( $user_id ) {
		return wp_nonce_url( add_query_arg( array(
			'action'  => 'switch_to_user',
			'user_id' => $user_id
		), wp_login_url() ), "switch_to_user_{$user_id}" );
	}

	/**
	 * Helper function. Returns the nonce-secured URL needed to switch back to the originating user.
	 *
	 * @return string The required URL
	 */
	public static function switch_back_url() {
		return wp_nonce_url( add_query_arg( array(
			'action' => 'switch_to_olduser'
		), wp_login_url() ), 'switch_to_olduser' );
	}

	/**
	 * Helper function. Returns the nonce-secured URL needed to switch off the current user.
	 *
	 * @return string The required URL
	 */
	public static function switch_off_url() {
		return wp_nonce_url( add_query_arg( array(
			'action' => 'switch_off'
		), wp_login_url() ), 'switch_off' );
	}

	/**
	 * Helper function. Returns the current URL.
	 *
	 * @return string The current URL
	 */
	public static function current_url() {
		return ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	/**
	 * Helper function. Returns an array of query args to remove from the query string when doing a redirect.
	 * 
	 * @return array Query args to remove from query string.
	 */
	public static function remove_query_args() {
		return array(
			'user_switched', 'switched_off', 'switched_back',
			'message', 'updated', 'settings-updated', 'saved',
			'activated', 'activate', 'deactivate',
			'locked', 'skipped', 'deleted', 'trashed', 'untrashed'
		);
	}

	/**
	 * Filter the user's capabilities so they can be added/removed on the fly.
	 *
	 * This is used to grant the 'switch_to_user' capability to a user if they have the ability to edit the user
	 * they're trying to switch to (and that user is not themselves), and to grant the 'switch_off' capability to
	 * a user if they can edit users.
	 *
	 * Important: This does not get called for Super Admins. See filter_map_meta_cap() below.
	 *
	 * @param array $user_caps     User's capabilities
	 * @param array $required_caps Actual required capabilities for the requested capability
	 * @param array $args          Arguments that accompany the requested capability check:
	 *                             [0] => Requested capability from current_user_can()
	 *                             [1] => Current user ID
	 *                             [2] => Optional second parameter from current_user_can()
	 * @return array User's capabilities
	 */
	public function filter_user_has_cap( array $user_caps, array $required_caps, array $args ) {
		if ( 'switch_to_user' == $args[0] )
			$user_caps['switch_to_user'] = ( user_can( $args[1], 'edit_user', $args[2] ) and ( $args[2] != $args[1] ) );
		else if ( 'switch_off' == $args[0] )
			$user_caps['switch_off'] = user_can( $args[1], 'edit_users' );
		return $user_caps;
	}

	/**
	 * Filters the actual required capabilities for a given capability or meta capability.
	 *
	 * This is used to add the 'do_not_allow' capability to the list of required capabilities when a super admin
	 * is trying to switch to themselves. It affects nothing else as super admins can do everything by default.
	 *
	 * @param array  $required_caps Actual required capabilities for the requested action
	 * @param string $cap           Capability or meta capability being checked
	 * @param string $user_id       Current user ID
	 * @param array  $args          Arguments that accompany this capability check
	 * @return array Required capabilities for the requested action
	 */
	public function filter_map_meta_cap( array $required_caps, $cap, $user_id, array $args ) {
		if ( ( 'switch_to_user' == $cap ) and ( $args[0] == $user_id ) )
			$required_caps[] = 'do_not_allow';
		return $required_caps;
	}

}

/**
 * Sets an authorisation cookie containing the originating user, or appends it if there's more than one.
 *
 * @param int $old_user_id The ID of the originating user, usually the current logged in user.
 * @return null
 */
if ( !function_exists( 'wp_set_olduser_cookie' ) ) {
function wp_set_olduser_cookie( $old_user_id ) {
	$expiration = time() + 172800; # 48 hours
	$cookie = wp_get_olduser_cookie();
	$cookie[] = wp_generate_auth_cookie( $old_user_id, $expiration, 'old_user' );
	setcookie( OLDUSER_COOKIE, json_encode( $cookie ), $expiration, COOKIEPATH, COOKIE_DOMAIN, false );
}
}

/**
 * Clears the cookie containing the originating user, or pops the latest item off the end if there's more than one.
 *
 * @param bool $clear_all Whether to clear the cookie or just pop the last user information off the end.
 * @return null
 */
if ( !function_exists( 'wp_clear_olduser_cookie' ) ) {
function wp_clear_olduser_cookie( $clear_all = true ) {
	$cookie = wp_get_olduser_cookie();
	if ( $clear_all or empty( $cookie ) ) {
		setcookie( OLDUSER_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN );
	} else {
		array_pop( $cookie );
		$expiration = time() + 172800; # 48 hours
		setcookie( OLDUSER_COOKIE, json_encode( $cookie ), $expiration, COOKIEPATH, COOKIE_DOMAIN, false );
	}
}
}

/**
 * Gets the value of the cookie containing the list of originating users.
 *
 * @return array Array of originating user authentication cookies. @see wp_generate_auth_cookie()
 */
if ( !function_exists( 'wp_get_olduser_cookie' ) ) {
function wp_get_olduser_cookie() {
	if ( isset( $_COOKIE[OLDUSER_COOKIE] ) )
		$cookie = json_decode( stripslashes( $_COOKIE[OLDUSER_COOKIE] ) );
	if ( !isset( $cookie ) or !is_array( $cookie ) )
		$cookie = array();
	return $cookie;
}
}

/**
 * Switches the current logged in user to the specified user.
 *
 * @param int  $user_id      The ID of the user to switch to.
 * @param bool $remember     Whether to 'remember' the user in the form of a persistent browser cookie. Optional.
 * @param bool $set_old_user Whether to set the old user cookie. Optional.
 * @return bool True on success, false on failure.
 */
if ( !function_exists( 'switch_to_user' ) ) {
function switch_to_user( $user_id, $remember = false, $set_old_user = true ) {
	if ( !$user = get_userdata( $user_id ) )
		return false;

	if ( $set_old_user and is_user_logged_in() ) {
		$old_user_id = get_current_user_id();
		wp_set_olduser_cookie( $old_user_id );
	} else {
		$old_user_id = false;
		wp_clear_olduser_cookie( false );
	}

	wp_clear_auth_cookie();
	wp_set_auth_cookie( $user_id, $remember );
	wp_set_current_user( $user_id );

	if ( $set_old_user )
		do_action( 'switch_to_user', $user_id, $old_user_id );
	else
		do_action( 'switch_back_user', $user_id, $old_user_id );

	return true;
}
}

/**
 * Switches off the current logged in user. This logs the current user out while retaining a cookie allowing them to log straight
 * back in using the 'Switch back to {user}' system.
 *
 * @return bool True on success, false on failure.
 */
if ( !function_exists( 'switch_off_user' ) ) {
function switch_off_user() {
	if ( !$old_user_id = get_current_user_id() )
		return false;

	wp_set_olduser_cookie( $old_user_id );
	wp_clear_auth_cookie();

	do_action( 'switch_off_user', $old_user_id );

	return true;
}
}

/**
 * Helper function. Did the current user switch into their account?
 *
 * @return bool|object False if the user isn't logged in or they didn't switch in; old user object (which evalutes to true) if the user switched into the current user account.
 */
if ( !function_exists( 'current_user_switched' ) ) {
function current_user_switched() {
	if ( !is_user_logged_in() )
		return false;

	return user_switching::get_old_user();
}
}

global $user_switching;

$user_switching = new user_switching;
