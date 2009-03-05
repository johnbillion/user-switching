<?php
/*
Plugin Name:  User Switching
Description:  Instant user switching for WordPress
Version:      0.1
Plugin URI:   http://lud.icro.us/wordpress-plugin-user-switching/
Author:       John Blackbourn
Author URI:   http://johnblackbourn.com/
Requires:     2.7
Tested up to: 2.7.1

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

Changelog:

0.1 - 2009/03/03
	Initial release.

*/

class user_switching {

	function user_switching() {
		add_action( 'admin_init',          array( &$this, 'admin_init' ) );
		add_action( 'admin_notices',       array( &$this, 'admin_notice' ) );
		add_action( 'user_row_actions',    array( &$this, 'user_row' ), 10, 2 ); # 2.8-bleeding-edge > r10629 only
		add_action( 'personal_options',    array( &$this, 'personal_options' ) );
	}

	function personal_options( $user ) {
		$current_user = wp_get_current_user();
		if ( !current_user_can( 'edit_user', $user->ID ) or ( $user->ID == $current_user->ID ) )
			return;
		?>
		<th scope="row"><?php _e( 'User Switching', 'user_switching' ); ?></th>
		<td style="padding-top:10px"><a href="<?php echo wp_nonce_url("users.php?action=switch_to_user&amp;user_id={$user->ID}", "switch_to_user_{$user->ID}"); ?>"><?php _e( 'Switch To', 'user_switching' ); ?></a></td>
		<?php
	}

	function admin_init() {
		if ( isset( $_REQUEST['action'] ) and ( 'switch_to_user' == $_REQUEST['action'] ) ) {
			$user_id = (int) $_REQUEST['user_id'];
			check_admin_referer( "switch_to_user_$user_id" );

			if ( switch_to_user( $user_id ) ) {
				wp_redirect( add_query_arg( array( 'user_switched' => 'true' ), admin_url() ) );
				die();
			}
		}
	}

	function admin_notice() {
		if ( isset( $_GET['user_switched'] ) ) {
			?><div id="user_switched" class="updated fade"><p><?php printf( __( 'Switched to %s.', 'user_switching' ), $GLOBALS['user_identity'] ); ?></p></div><?php
		}
	}

	function user_row( $actions, $user ) {
		$current_user = wp_get_current_user();

		if ( $current_user->ID != $user->ID )
			$actions[] = '<a href="' . wp_nonce_url("users.php?action=switch_to_user&amp;user_id={$user->ID}", "switch_to_user_{$user->ID}") . '">' . __( 'Switch To', 'user_switching' ) . '</a>';

		return $actions;
	}

}

function switch_to_user( $user_id = 0, $remember = false, $secure = '' ) {
	if ( !function_exists( 'wp_set_auth_cookie' ) )
		return false;
	if ( !$user_id )
		return false;
	if ( !$user = get_userdata( $user_id ) )
		return false;

	wp_clear_auth_cookie();
	wp_set_auth_cookie( $user_id, $remember, $secure );

	return true;
}

load_plugin_textdomain( 'user_switching', PLUGINDIR . '/' . dirname( plugin_basename( __FILE__ ) ), dirname( plugin_basename( __FILE__ ) ) ); # eugh

$user_switching = new user_switching();

?>