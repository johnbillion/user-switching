<?php
/*
Plugin Name:  User Switching
Description:  Instant switching between user accounts in WordPress
Version:      0.4.1
Plugin URI:   http://lud.icro.us/wordpress-plugin-user-switching/
Author:       John Blackbourn
Author URI:   http://johnblackbourn.com/

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

	function __construct() {
		add_filter( 'user_has_cap',                 array( $this, 'user_cap_filter' ), 10, 3 );
		add_filter( 'map_meta_cap',                 array( $this, 'map_meta_cap' ), 10, 4 );
		add_action( 'plugins_loaded',               array( $this, 'set_old_cookie' ) );
		add_action( 'init',                         array( $this, 'init' ) );
		add_action( 'admin_notices',                array( $this, 'admin_notice' ) );
		add_action( 'user_row_actions',             array( $this, 'user_row' ), 10, 2 );
		add_action( 'ms_user_row_actions',          array( $this, 'user_row' ), 10, 2 );
		add_action( 'personal_options',             array( $this, 'personal_options' ) );
		add_action( 'admin_bar_menu',               array( $this, 'admin_bar_menu' ), 11 );
		add_action( 'bp_adminbar_menus',            array( $this, 'bp_menu' ), 9 );
		add_action( 'bp_member_header_actions',     array( $this, 'bp_button' ), 11 );
		add_action( 'bp_directory_members_actions', array( $this, 'bp_button' ), 11 );
		add_action( 'wp_logout',                    'wp_clear_olduser_cookie' );
		add_action( 'wp_login',                     'wp_clear_olduser_cookie' );
	}

	function set_old_cookie() {
		if ( !defined( 'OLDUSER_COOKIE' ) )
			define( 'OLDUSER_COOKIE', 'wordpress_olduser_' . COOKIEHASH );
	}

	function personal_options( $user ) {
		if ( !current_user_can( 'switch_to_user', $user->ID ) )
			return;
		?>
		<tr>
			<th scope="row"><?php _e( 'User Switching', 'user_switching' ); ?></th>
			<td><a href="<?php echo $this->switch_to_url( $user->ID ); ?>"><?php _e( 'Switch&nbsp;To', 'user_switching' ); ?></a></td>
		</tr>
		<?php
	}

	function remember() {

		$current_user = wp_get_current_user();
		$current      = wp_parse_auth_cookie( '', 'logged_in' );
		$cookie_life  = apply_filters( 'auth_cookie_expiration', 172800, $current_user->ID, false );

		return ( ( $current['expiration'] - time() ) > $cookie_life );

	}

	function init() {
		if ( !isset( $_REQUEST['action'] ) )
			return;

		switch ( $_REQUEST['action'] ) {
			case 'switch_to_user':
				$user_id = intval( $_REQUEST['user_id'] );
				check_admin_referer( "switch_to_user_{$user_id}" );

				if ( switch_to_user( $user_id, $this->remember() ) ) {

					if ( !current_user_can( 'read' ) )
						wp_redirect( add_query_arg( array( 'user_switched' => 'true' ), home_url() ) );
					else
						wp_redirect( add_query_arg( array( 'user_switched' => 'true' ), admin_url() ) );
					die();

				} else {
					wp_die( __( 'Could not switch users.', 'user_switching' ) );
				}
				break;
			case 'switch_to_olduser':
				check_admin_referer( 'switch_to_olduser' );

				if ( !$old_user = $this->get_old_user() )
					wp_die( __( 'Could not switch users.', 'user_switching' ) );

				if ( switch_to_user( $old_user->ID, $this->remember(), false ) ) {
					wp_redirect( add_query_arg( array( 'user_switched' => 'true', 'back' => 'true' ), admin_url('users.php') ) );
					die();
				} else {
					wp_die( __( 'Could not switch users.', 'user_switching' ) );
				}
				break;
		}
	}

	function admin_notice() {
		global $user_identity, $user_login;

		if ( $old_user = $this->get_old_user() ) {

			?>
			<div id="user_switching" class="updated">
				<p><?php
					if ( isset( $_GET['user_switched'] ) )
						printf( __( 'Switched to %1$s (%2$s).', 'user_switching' ), $user_identity, $user_login );
					printf( __( ' <a href="%1$s">Switch back to %2$s (%3$s)</a>.', 'user_switching' ), $this->switch_back_url(), $old_user->display_name, $old_user->user_login );
				?></p>
			</div>
			<?php

		} else if ( isset( $_GET['user_switched'] ) ) {

			?>
			<div id="user_switching" class="updated">
				<p><?php
					if ( isset( $_GET['back'] ) )
						printf( __( 'Switched back to %1$s (%2$s).', 'user_switching' ), $user_identity, $user_login );
					else
						printf( __( 'Switched to %1$s (%2$s).', 'user_switching' ), $user_identity, $user_login );
				?></p>
			</div>
			<?php

		}
	}

	function get_old_user() {
		if ( isset( $_COOKIE[OLDUSER_COOKIE] ) ) {
			if ( $old_user_id = wp_validate_auth_cookie( $_COOKIE[OLDUSER_COOKIE], 'old_user' ) )
				return get_userdata( $old_user_id );
		}
		return false;
	}

	function admin_bar_menu() {
		global $wp_admin_bar;

		if ( !function_exists( 'is_admin_bar_showing' ) )
			return;
		if ( !is_admin_bar_showing() )
			return;

		if ( $old_user = $this->get_old_user() ) {

			foreach ( array( 'my-account-with-avatar', 'my-account' ) as $parent ) {
				$wp_admin_bar->add_menu( array(
					'parent' => $parent,
					'title'  => sprintf( __( 'Switch back to %1$s (%2$s)', 'user_switching' ), $old_user->display_name, $old_user->user_login ),
					'href'   => $this->switch_back_url()
				) );
			}

		}

	}

	function user_row( $actions, $user ) {
		if ( current_user_can( 'switch_to_user', $user->ID ) )
			$actions[] = '<a href="' . $this->switch_to_url( $user->ID ) . '">' . __( 'Switch&nbsp;To', 'user_switching' ) . '</a>';
		return $actions;
	}

	function bp_menu() {

		if ( !is_admin() and $old_user = $this->get_old_user() ) {

			echo '<li id="bp-adminbar-userswitching-menu" style="background-image:none"><a href="' . $this->switch_back_url() . '">';
			printf( __( 'Switch back to %1$s (%2$s)', 'user_switching' ), $old_user->display_name, $old_user->user_login );
			echo '</a></li>';

		}

	}

	function bp_button() {

		global $bp, $members_template;

		if ( !empty( $members_template ) )
			$id = intval( $members_template->member->id );
		else
			$id = intval( $bp->displayed_user->id );

		if ( current_user_can( 'switch_to_user', $id ) ) {
			echo bp_get_button( array(
				'id'         => 'user_switching',
				'link_href'  => $this->switch_to_url( $id ),
				'link_text'  => __( 'Switch&nbsp;To', 'user_switching' )
			) );
		}

	}

	function switch_to_url( $user_id ) {
		return wp_nonce_url( add_query_arg( array(
			'action'  => 'switch_to_user',
			'user_id' => $user_id
		), site_url( 'wp-login.php', 'login' ) ), "switch_to_user_{$user_id}" );
	}

	function switch_back_url() {
		return wp_nonce_url( add_query_arg( array(
			'action' => 'switch_to_olduser'
		), site_url( 'wp-login.php', 'login' ) ), 'switch_to_olduser' );
	}

	function user_cap_filter( $user_caps, $required_caps, $args ) {
		if ( 'switch_to_user' == $args[0] )
			$user_caps['switch_to_user'] = ( current_user_can( 'edit_user', $args[2] ) and ( $args[2] != $args[1] ) );
		return $user_caps;
	}

	function map_meta_cap( $caps, $cap, $user_id, $args ) {
		if ( ( 'switch_to_user' == $cap ) and ( $args[0] == $user_id ) )
			$caps[] = 'do_not_allow';
		return $caps;
	}

}

if ( !function_exists( 'wp_set_olduser_cookie' ) ) {
function wp_set_olduser_cookie( $old_user_id = 0 ) {
	$expiration = time() + 172800; # 48 hours
	$cookie = wp_generate_auth_cookie( $old_user_id, $expiration, 'old_user' );
	setcookie( OLDUSER_COOKIE, $cookie, $expiration, COOKIEPATH, COOKIE_DOMAIN, false );
}
}

if ( !function_exists( 'wp_clear_olduser_cookie' ) ) {
function wp_clear_olduser_cookie() {
	setcookie( OLDUSER_COOKIE, ' ', time() - 31536000, COOKIEPATH, COOKIE_DOMAIN );
}
}

if ( !function_exists( 'switch_to_user' ) ) {
function switch_to_user( $user_id = 0, $remember = false, $old_user_id = 0 ) {
	if ( !function_exists( 'wp_set_auth_cookie' ) )
		return false;
	if ( !$user_id )
		return false;
	if ( !$user = get_userdata( $user_id ) )
		return false;

	if ( 0 === $old_user_id ) {
		if ( $current_user = wp_get_current_user() )
			$old_user_id = $current_user->ID;
	}

	if ( $old_user_id )
		wp_set_olduser_cookie( $old_user_id );
	else
		wp_clear_olduser_cookie();

	wp_clear_auth_cookie();
	wp_set_auth_cookie( $user_id, $remember );
	wp_set_current_user( $user_id );

	return true;
}
}

load_plugin_textdomain( 'user_switching', false, dirname( plugin_basename( __FILE__ ) ) );

$user_switching = new user_switching;

?>