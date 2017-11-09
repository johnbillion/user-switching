<?php
/**
 * User Switching plugin for WordPress
 *
 * @package   user-switching
 * @link      https://github.com/johnbillion/user-switching
 * @author    John Blackbourn <john@johnblackbourn.com>
 * @copyright 2009-2017 John Blackbourn
 * @license   GPL v2 or later
 *
 * Plugin Name: User Switching
 * Description: Instant switching between user accounts in WordPress
 * Version:     1.3.0
 * Plugin URI:  https://johnblackbourn.com/wordpress-plugin-user-switching/
 * Author:      John Blackbourn
 * Author URI:  https://johnblackbourn.com/
 * Text Domain: user-switching
 * Domain Path: /languages/
 * Network:     true
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

/**
 * Main container class for the User Switching plugin.
 */
class user_switching {

	/**
	 * Class constructor. Sets up some filters and actions.
	 */
	private function __construct() {

		// Required functionality:
		add_filter( 'user_has_cap',                    array( $this, 'filter_user_has_cap' ), 10, 4 );
		add_filter( 'map_meta_cap',                    array( $this, 'filter_map_meta_cap' ), 10, 4 );
		add_filter( 'user_row_actions',                array( $this, 'filter_user_row_actions' ), 10, 2 );
		add_action( 'plugins_loaded',                  array( $this, 'action_plugins_loaded' ) );
		add_action( 'init',                            array( $this, 'action_init' ) );
		add_action( 'all_admin_notices',               array( $this, 'action_admin_notices' ), 1 );
		add_action( 'wp_logout',                       'user_switching_clear_olduser_cookie' );
		add_action( 'wp_login',                        'user_switching_clear_olduser_cookie' );

		// Nice-to-haves:
		add_filter( 'ms_user_row_actions',             array( $this, 'filter_user_row_actions' ), 10, 2 );
		add_filter( 'login_message',                   array( $this, 'filter_login_message' ), 1 );
		add_filter( 'removable_query_args',            array( $this, 'filter_removable_query_args' ) );
		add_action( 'wp_meta',                         array( $this, 'action_wp_meta' ) );
		add_action( 'wp_footer',                       array( $this, 'action_wp_footer' ) );
		add_action( 'personal_options',                array( $this, 'action_personal_options' ) );
		add_action( 'admin_bar_menu',                  array( $this, 'action_admin_bar_menu' ), 11 );
		add_action( 'bp_member_header_actions',        array( $this, 'action_bp_button' ), 11 );
		add_action( 'bp_directory_members_actions',    array( $this, 'action_bp_button' ), 11 );
		add_action( 'bbp_template_after_user_details', array( $this, 'action_bbpress_button' ) );

	}

	/**
	 * Defines the names of our cookies.
	 */
	public function action_plugins_loaded() {

		// User Switching's auth_cookie
		if ( ! defined( 'USER_SWITCHING_COOKIE' ) ) {
			define( 'USER_SWITCHING_COOKIE', 'wordpress_user_sw_' . COOKIEHASH );
		}

		// User Switching's secure_auth_cookie
		if ( ! defined( 'USER_SWITCHING_SECURE_COOKIE' ) ) {
			define( 'USER_SWITCHING_SECURE_COOKIE', 'wordpress_user_sw_secure_' . COOKIEHASH );
		}

		// User Switching's logged_in_cookie
		if ( ! defined( 'USER_SWITCHING_OLDUSER_COOKIE' ) ) {
			define( 'USER_SWITCHING_OLDUSER_COOKIE', 'wordpress_user_sw_olduser_' . COOKIEHASH );
		}

	}

	/**
	 * Outputs the 'Switch To' link on the user editing screen if we have permission to switch to this user.
	 *
	 * @param WP_User $user User object for this screen.
	 */
	public function action_personal_options( WP_User $user ) {
		$link = self::maybe_switch_url( $user );

		if ( ! $link ) {
			return;
		}

		?>
		<tr>
			<th scope="row"><?php echo esc_html_x( 'User Switching', 'User Switching title on user profile screen', 'user-switching' ); ?></th>
			<td><a href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Switch&nbsp;To', 'user-switching' ); ?></a></td>
		</tr>
		<?php
	}

	/**
	 * Returns whether or not the current logged in user is being remembered in the form of a persistent browser cookie
	 * (ie. they checked the 'Remember Me' check box when they logged in). This is used to persist the 'remember me'
	 * value when the user switches to another user.
	 *
	 * @return bool Whether the current user is being 'remembered' or not.
	 */
	public static function remember() {

		/**
		 * Filter the duration of the authentication cookie expiration period.
		 *
		 * This matches the WordPress core filter in `wp_set_auth_cookie()`.
		 *
		 * @since 0.2.2
		 *
		 * @param int  $length   Duration of the expiration period in seconds.
		 * @param int  $user_id  User ID.
		 * @param bool $remember Whether to remember the user login. Default false.
		 */
		$cookie_life = apply_filters( 'auth_cookie_expiration', 172800, get_current_user_id(), false );
		$current     = wp_parse_auth_cookie( '', 'logged_in' );

		// Here we calculate the expiration length of the current auth cookie and compare it to the default expiration.
		// If it's greater than this, then we know the user checked 'Remember Me' when they logged in.
		return ( ( $current['expiration'] - time() ) > $cookie_life );

	}

	/**
	 * Loads localisation files and routes actions depending on the 'action' query var.
	 */
	public function action_init() {

		load_plugin_textdomain( 'user-switching', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		if ( ! isset( $_REQUEST['action'] ) ) {
			return;
		}

		$current_user = ( is_user_logged_in() ) ? wp_get_current_user() : null;

		switch ( $_REQUEST['action'] ) {

			// We're attempting to switch to another user:
			case 'switch_to_user':
				if ( isset( $_REQUEST['user_id'] ) ) {
					$user_id = absint( $_REQUEST['user_id'] );
				} else {
					$user_id = 0;
				}

				// Check authentication:
				if ( ! current_user_can( 'switch_to_user', $user_id ) ) {
					wp_die( esc_html__( 'Could not switch users.', 'user-switching' ) );
				}

				// Check intent:
				check_admin_referer( "switch_to_user_{$user_id}" );

				// Switch user:
				$user = switch_to_user( $user_id, self::remember() );
				if ( $user ) {

					$redirect_to = self::get_redirect( $user, $current_user );

					// Redirect to the dashboard or the home URL depending on capabilities:
					$args = array(
						'user_switched' => 'true',
					);
					if ( $redirect_to ) {
						wp_safe_redirect( add_query_arg( $args, $redirect_to ) );
					} elseif ( ! current_user_can( 'read' ) ) {
						wp_safe_redirect( add_query_arg( $args, home_url() ) );
					} else {
						wp_safe_redirect( add_query_arg( $args, admin_url() ) );
					}
					exit;

				} else {
					wp_die( esc_html__( 'Could not switch users.', 'user-switching' ) );
				}
				break;

			// We're attempting to switch back to the originating user:
			case 'switch_to_olduser':
				// Fetch the originating user data:
				$old_user = self::get_old_user();
				if ( ! $old_user ) {
					wp_die( esc_html__( 'Could not switch users.', 'user-switching' ) );
				}

				// Check authentication:
				if ( ! self::authenticate_old_user( $old_user ) ) {
					wp_die( esc_html__( 'Could not switch users.', 'user-switching' ) );
				}

				// Check intent:
				check_admin_referer( "switch_to_olduser_{$old_user->ID}" );

				// Switch user:
				if ( switch_to_user( $old_user->ID, self::remember(), false ) ) {

					if ( ! empty( $_REQUEST['interim-login'] ) ) {
						$GLOBALS['interim_login'] = 'success';
						login_header( '', '' );
						exit;
					}

					$redirect_to = self::get_redirect( $old_user, $current_user );
					$args = array(
						'user_switched' => 'true',
						'switched_back' => 'true',
					);
					if ( $redirect_to ) {
						wp_safe_redirect( add_query_arg( $args, $redirect_to ) );
					} else {
						wp_safe_redirect( add_query_arg( $args, admin_url( 'users.php' ) ) );
					}
					exit;
				} else {
					wp_die( esc_html__( 'Could not switch users.', 'user-switching' ) );
				}
				break;

			// We're attempting to switch off the current user:
			case 'switch_off':
				// Check authentication:
				if ( ! current_user_can( 'switch_off' ) ) {
					/* Translators: "switch off" means to temporarily log out */
					wp_die( esc_html__( 'Could not switch off.', 'user-switching' ) );
				}

				// Check intent:
				check_admin_referer( "switch_off_{$current_user->ID}" );

				// Switch off:
				if ( switch_off_user() ) {
					$redirect_to = self::get_redirect( null, $current_user );
					$args = array(
						'switched_off' => 'true',
					);
					if ( $redirect_to ) {
						wp_safe_redirect( add_query_arg( $args, $redirect_to ) );
					} else {
						wp_safe_redirect( add_query_arg( $args, home_url() ) );
					}
					exit;
				} else {
					/* Translators: "switch off" means to temporarily log out */
					wp_die( esc_html__( 'Could not switch off.', 'user-switching' ) );
				}
				break;

		}

	}

	/**
	 * Fetches the URL to redirect to for a given user (used after switching).
	 *
	 * @param  WP_User $new_user Optional. The new user's WP_User object.
	 * @param  WP_User $old_user Optional. The old user's WP_User object.
	 * @return string The URL to redirect to.
	 */
	protected static function get_redirect( WP_User $new_user = null, WP_User $old_user = null ) {

		if ( ! empty( $_REQUEST['redirect_to'] ) ) {
			$redirect_to = self::remove_query_args( wp_unslash( $_REQUEST['redirect_to'] ) ); // WPCS: sanitization ok
			$requested_redirect_to = wp_unslash( $_REQUEST['redirect_to'] ); // WPCS: sanitization ok
		} else {
			$redirect_to = '';
			$requested_redirect_to = '';
		}

		if ( ! $new_user ) {
			/**
			 * Filter the redirect URL when a user switches off.
			 *
			 * This matches the WordPress core filter in wp-login.php.
			 *
			 * @since 1.0.4
			 *
			 * @param string  $redirect_to           The redirect destination URL.
			 * @param string  $requested_redirect_to The requested redirect destination URL passed as a parameter.
			 * @param WP_User $old_user              The WP_User object for the user that's switching off.
			 */
			$redirect_to = apply_filters( 'logout_redirect', $redirect_to, $requested_redirect_to, $old_user );
		} else {
			/**
			 * Filter the redirect URL when a user switches to another user or switches back.
			 *
			 * This matches the WordPress core filter in wp-login.php.
			 *
			 * @since 0.8.7
			 *
			 * @param string  $redirect_to           The redirect destination URL.
			 * @param string  $requested_redirect_to The requested redirect destination URL passed as a parameter.
			 * @param WP_User $new_user              The WP_User object for the user that's being switched to.
			 */
			$redirect_to = apply_filters( 'login_redirect', $redirect_to, $requested_redirect_to, $new_user );
		}

		return $redirect_to;

	}

	/**
	 * Displays the 'Switched to {user}' and 'Switch back to {user}' messages in the admin area.
	 */
	public function action_admin_notices() {
		$user     = wp_get_current_user();
		$old_user = self::get_old_user();

		if ( $old_user ) {

			?>
			<div id="user_switching" class="updated">
				<p><span class="dashicons dashicons-admin-users" style="color:#56c234"></span>
				<?php
					$message = '';
					$just_switched = isset( $_GET['user_switched'] );
					if ( $just_switched ) {
						$message = esc_html( sprintf(
							/* Translators: 1: user display name; 2: username; */
							__( 'Switched to %1$s (%2$s).', 'user-switching' ),
							$user->display_name,
							$user->user_login
						) );
					}
					$switch_back_url = add_query_arg( array(
						'redirect_to' => urlencode( self::current_url() ),
					), self::switch_back_url( $old_user ) );
					$message .= sprintf(
						' <a href="%s">%s</a>.',
						esc_url( $switch_back_url ),
						esc_html( sprintf(
							/* Translators: 1: user display name; 2: username; */
							__( 'Switch back to %1$s (%2$s)', 'user-switching' ),
							$old_user->display_name,
							$old_user->user_login
						) )
					);

					/**
					 * Filters the contents of the message that's displayed to switched users in the admin area.
					 *
					 * @since 1.1.0
					 *
					 * @param string  $message         The message displayed to the switched user.
					 * @param WP_User $user            The current user object.
					 * @param WP_User $old_user        The old user object.
					 * @param string  $switch_back_url The switch back URL.
					 * @param bool    $just_switched   Whether the user made the switch on this page request.
					 */
					$message = apply_filters( 'user_switching_switched_message', $message, $user, $old_user, $switch_back_url, $just_switched );
					echo $message; // WPCS: XSS ok.
				?>
				</p>
			</div>
			<?php

		} elseif ( isset( $_GET['user_switched'] ) ) {

			?>
			<div id="user_switching" class="updated">
				<p>
				<?php
					if ( isset( $_GET['switched_back'] ) ) {
						echo esc_html( sprintf(
							/* Translators: 1: user display name; 2: username; */
							__( 'Switched back to %1$s (%2$s).', 'user-switching' ),
							$user->display_name,
							$user->user_login
						) );
					} else {
						echo esc_html( sprintf(
							/* Translators: 1: user display name; 2: username; */
							__( 'Switched to %1$s (%2$s).', 'user-switching' ),
							$user->display_name,
							$user->user_login
						) );
					}
				?>
				</p>
			</div>
			<?php

		}
	}

	/**
	 * Validates the old user cookie and returns its user data.
	 *
	 * @return bool|WP_User False if there's no old user cookie or it's invalid, WP_User object if it's present and valid.
	 */
	public static function get_old_user() {
		$cookie = user_switching_get_olduser_cookie();
		if ( ! empty( $cookie ) ) {
			$old_user_id = wp_validate_auth_cookie( $cookie, 'logged_in' );

			if ( $old_user_id ) {
				return get_userdata( $old_user_id );
			}
		}
		return false;
	}

	/**
	 * Authenticates an old user by verifying the latest entry in the auth cookie.
	 *
	 * @param  WP_User $user A WP_User object (usually from the logged_in cookie).
	 * @return bool Whether verification with the auth cookie passed.
	 */
	public static function authenticate_old_user( WP_User $user ) {
		$cookie = user_switching_get_auth_cookie();
		if ( ! empty( $cookie ) ) {

			if ( user_switching::secure_auth_cookie() ) {
				$scheme = 'secure_auth';
			} else {
				$scheme = 'auth';
			}

			$old_user_id = wp_validate_auth_cookie( end( $cookie ), $scheme );

			if ( $old_user_id ) {
				return ( $user->ID === $old_user_id );
			}
		}
		return false;
	}

	/**
	 * Adds a 'Switch back to {user}' link to the account menu in WordPress' admin bar.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The admin bar object.
	 */
	public function action_admin_bar_menu( WP_Admin_Bar $wp_admin_bar ) {

		if ( ! function_exists( 'is_admin_bar_showing' ) ) {
			return;
		}
		if ( ! is_admin_bar_showing() ) {
			return;
		}

		if ( method_exists( $wp_admin_bar, 'get_node' ) ) {
			if ( $wp_admin_bar->get_node( 'user-actions' ) ) {
				$parent = 'user-actions';
			} else {
				return;
			}
		} elseif ( get_option( 'show_avatars' ) ) {
			$parent = 'my-account-with-avatar';
		} else {
			$parent = 'my-account';
		}

		$old_user = self::get_old_user();

		if ( $old_user ) {

			$wp_admin_bar->add_menu( array(
				'parent' => $parent,
				'id'     => 'switch-back',
				'title'  => esc_html( sprintf(
					/* Translators: 1: user display name; 2: username; */
					__( 'Switch back to %1$s (%2$s)', 'user-switching' ),
					$old_user->display_name,
					$old_user->user_login
				) ),
				'href'   => add_query_arg( array(
					'redirect_to' => urlencode( self::current_url() ),
				), self::switch_back_url( $old_user ) ),
			) );

		}

		if ( current_user_can( 'switch_off' ) ) {

			$url = self::switch_off_url( wp_get_current_user() );
			if ( ! is_admin() ) {
				$url = add_query_arg( array(
					'redirect_to' => urlencode( self::current_url() ),
				), $url );
			}

			$wp_admin_bar->add_menu( array(
				'parent' => $parent,
				'id'     => 'switch-off',
				/* Translators: "switch off" means to temporarily log out */
				'title'  => esc_html__( 'Switch Off', 'user-switching' ),
				'href'   => $url,
			) );

		}

	}

	/**
	 * Adds a 'Switch back to {user}' link to the Meta sidebar widget.
	 */
	public function action_wp_meta() {
		$old_user = self::get_old_user();

		if ( $old_user instanceof WP_User ) {
			$link = sprintf(
				/* Translators: 1: user display name; 2: username; */
				__( 'Switch back to %1$s (%2$s)', 'user-switching' ),
				$old_user->display_name,
				$old_user->user_login
			);
			$url = add_query_arg( array(
				'redirect_to' => urlencode( self::current_url() ),
			), self::switch_back_url( $old_user ) );
			echo '<li id="user_switching_switch_on"><a href="' . esc_url( $url ) . '">' . esc_html( $link ) . '</a></li>';
		}

	}

	/**
	 * Adds a 'Switch back to {user}' link to the WordPress footer if the admin toolbar isn't showing.
	 */
	public function action_wp_footer() {
		if ( is_admin_bar_showing() || did_action( 'wp_meta' ) ) {
			return;
		}

		$old_user = self::get_old_user();

		if ( $old_user instanceof WP_User ) {
			$link = sprintf(
				/* Translators: 1: user display name; 2: username; */
				__( 'Switch back to %1$s (%2$s)', 'user-switching' ),
				$old_user->display_name,
				$old_user->user_login
			);
			$url = add_query_arg( array(
				'redirect_to' => urlencode( self::current_url() ),
			), self::switch_back_url( $old_user ) );
			echo '<p id="user_switching_switch_on"><a href="' . esc_url( $url ) . '">' . esc_html( $link ) . '</a></p>';
		}

	}

	/**
	 * Adds a 'Switch back to {user}' link to the WordPress login screen.
	 *
	 * @param  string $message The login screen message.
	 * @return string The login screen message.
	 */
	public function filter_login_message( $message ) {
		$old_user = self::get_old_user();

		if ( $old_user instanceof WP_User ) {
			$link = sprintf(
				/* Translators: 1: user display name; 2: username; */
				__( 'Switch back to %1$s (%2$s)', 'user-switching' ),
				$old_user->display_name,
				$old_user->user_login
			);
			$url = self::switch_back_url( $old_user );

			if ( ! empty( $_REQUEST['interim-login'] ) ) {
				$url = add_query_arg( array(
					'interim-login' => '1',
				), $url );
			} elseif ( ! empty( $_REQUEST['redirect_to'] ) ) {
				$url = add_query_arg( array(
					'redirect_to' => urlencode( wp_unslash( $_REQUEST['redirect_to'] ) ), // WPCS: sanitization ok
				), $url );
			}

			$message .= '<p class="message" id="user_switching_switch_on">';
			$message .= '<span class="dashicons dashicons-admin-users" style="color:#56c234"></span> ';
			$message .= '<a href="' . esc_url( $url ) . '" onclick="window.location.href=\'' . esc_url( $url ) . '\';return false;">' . esc_html( $link ) . '</a>';
			$message .= '</p>';
		}

		return $message;

	}

	/**
	 * Adds a 'Switch To' link to each list of user actions on the Users screen.
	 *
	 * @param string[] $actions The actions to display for this user row.
	 * @param WP_User  $user    The user object displayed in this row.
	 * @return string[] The actions to display for this user row.
	 */
	public function filter_user_row_actions( array $actions, WP_User $user ) {
		$link = self::maybe_switch_url( $user );

		if ( ! $link ) {
			return $actions;
		}

		$actions['switch_to_user'] = '<a href="' . esc_url( $link ) . '">' . esc_html__( 'Switch&nbsp;To', 'user-switching' ) . '</a>';

		return $actions;
	}

	/**
	 * Adds a 'Switch To' link to each member's profile page and profile listings in BuddyPress.
	 */
	public function action_bp_button() {
		$user = null;

		if ( bp_is_user() ) {
			$user = get_userdata( bp_displayed_user_id() );
		} elseif ( bp_is_members_directory() ) {
			$user = get_userdata( bp_get_member_user_id() );
		}

		if ( ! $user ) {
			return;
		}

		$link = self::maybe_switch_url( $user );

		if ( ! $link ) {
			return;
		}

		$link = add_query_arg( array(
			'redirect_to' => urlencode( bp_core_get_user_domain( $user->ID ) ),
		), $link );

		$components = array_keys( buddypress()->active_components );

		echo bp_get_button( array(
			'id'         => 'user_switching',
			'component'  => reset( $components ),
			'link_href'  => esc_url( $link ),
			'link_text'  => esc_html__( 'Switch&nbsp;To', 'user-switching' ),
			'wrapper_id' => 'user_switching_switch_to',
		) );

	}

	/**
	 * Adds a 'Switch To' link to each member's profile page in bbPress.
	 */
	public function action_bbpress_button() {
		$user = get_userdata( bbp_get_user_id() );

		if ( ! $user ) {
			return;
		}

		$link = self::maybe_switch_url( $user );

		if ( ! $link ) {
			return;
		}

		$link = add_query_arg( array(
			'redirect_to' => urlencode( bbp_get_user_profile_url( $user->ID ) ),
		), $link );

		?>
		<ul id="user_switching_switch_to">
			<li><a href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Switch&nbsp;To', 'user-switching' ); ?></a></li>
		</ul>
		<?php

	}

	/**
	 * Filters the list of query arguments which get removed from admin area URLs in WordPress.
	 *
	 * @link https://core.trac.wordpress.org/ticket/23367
	 *
	 * @param string[] $args List of removable query arguments.
	 * @return string[] Updated list of removable query arguments.
	 */
	public function filter_removable_query_args( array $args ) {
		return array_merge( $args, array(
			'user_switched',
			'switched_off',
			'switched_back',
		) );
	}

	/**
	 * Returns the switch to or switch back URL for a given user.
	 *
	 * @param  WP_User $user The user to be switched to.
	 * @return string|bool The required URL, or false if there's no old user or the user doesn't have the required capability.
	 */
	public static function maybe_switch_url( WP_User $user ) {

		$old_user = self::get_old_user();

		if ( $old_user && ( $old_user->ID === $user->ID ) ) {
			return self::switch_back_url( $old_user );
		} elseif ( current_user_can( 'switch_to_user', $user->ID ) ) {
			return self::switch_to_url( $user );
		} else {
			return false;
		}

	}

	/**
	 * Returns the nonce-secured URL needed to switch to a given user ID.
	 *
	 * @param  WP_User $user The user to be switched to.
	 * @return string The required URL.
	 */
	public static function switch_to_url( WP_User $user ) {
		return wp_nonce_url( add_query_arg( array(
			'action'  => 'switch_to_user',
			'user_id' => $user->ID,
			'nr'      => 1,
		), wp_login_url() ), "switch_to_user_{$user->ID}" );
	}

	/**
	 * Returns the nonce-secured URL needed to switch back to the originating user.
	 *
	 * @param  WP_User $user The old user.
	 * @return string        The required URL.
	 */
	public static function switch_back_url( WP_User $user ) {
		return wp_nonce_url( add_query_arg( array(
			'action' => 'switch_to_olduser',
			'nr'     => 1,
		), wp_login_url() ), "switch_to_olduser_{$user->ID}" );
	}

	/**
	 * Returns the nonce-secured URL needed to switch off the current user.
	 *
	 * @param  WP_User $user The user to be switched off.
	 * @return string        The required URL.
	 */
	public static function switch_off_url( WP_User $user ) {
		return wp_nonce_url( add_query_arg( array(
			'action' => 'switch_off',
			'nr'     => 1,
		), wp_login_url() ), "switch_off_{$user->ID}" );
	}

	/**
	 * Returns the current URL.
	 *
	 * @return string The current URL.
	 */
	public static function current_url() {
		return ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; // @codingStandardsIgnoreLine
	}

	/**
	 * Removes a list of common confirmation-style query args from a URL.
	 *
	 * @param  string $url A URL.
	 * @return string The URL with query args removed.
	 */
	public static function remove_query_args( $url ) {
		if ( function_exists( 'wp_removable_query_args' ) ) {
			$url = remove_query_arg( wp_removable_query_args(), $url );
		}

		return $url;
	}

	/**
	 * Returns whether or not User Switching's equivalent of the 'logged_in' cookie should be secure.
	 *
	 * This is used to set the 'secure' flag on the old user cookie, for enhanced security.
	 *
	 * @link https://core.trac.wordpress.org/ticket/15330
	 *
	 * @return bool Should the old user cookie be secure?
	 */
	public static function secure_olduser_cookie() {
		return ( is_ssl() && ( 'https' === parse_url( home_url(), PHP_URL_SCHEME ) ) );
	}

	/**
	 * Returns whether or not User Switching's equivalent of the 'auth' cookie should be secure.
	 *
	 * This is used to determine whether to set a secure auth cookie or not.
	 *
	 * @return bool Should the auth cookie be secure?
	 */
	public static function secure_auth_cookie() {
		return ( is_ssl() && ( 'https' === parse_url( wp_login_url(), PHP_URL_SCHEME ) ) );
	}

	/**
	 * Filters a user's capabilities so they can be altered at runtime.
	 *
	 * This is used to:
	 *  - Grant the 'switch_to_user' capability to the user if they have the ability to edit the user they're trying to
	 *    switch to (and that user is not themselves).
	 *  - Grant the 'switch_off' capability to the user if they can edit other users.
	 *
	 * Important: This does not get called for Super Admins. See filter_map_meta_cap() below.
	 *
	 * @param bool[]   $user_caps     Concerned user's capabilities.
	 * @param string[] $required_caps Required primitive capabilities for the requested capability.
	 * @param array    $args {
	 *     Arguments that accompany the requested capability check.
	 *
	 *     @type string    $0 Requested capability.
	 *     @type int       $1 Concerned user ID.
	 *     @type mixed  ...$2 Optional second and further parameters.
	 * }
	 * @param WP_User  $user          Concerned user object.
	 * @return bool[] Concerned user's capabilities.
	 */
	public function filter_user_has_cap( array $user_caps, array $required_caps, array $args, WP_User $user ) {
		if ( 'switch_to_user' === $args[0] ) {
			$user_caps['switch_to_user'] = ( user_can( $user->ID, 'edit_user', $args[2] ) && ( $args[2] !== $user->ID ) );
		} elseif ( 'switch_off' === $args[0] ) {
			$user_caps['switch_off'] = user_can( $user->ID, 'edit_users' );
		}
		return $user_caps;
	}

	/**
	 * Filters the required primitive capabilities for the given primitive or meta capability.
	 *
	 * This is used to:
	 *  - Add the 'do_not_allow' capability to the list of required capabilities when a Super Admin is trying to switch
	 *    to themselves.
	 *
	 * It affects nothing else as Super Admins can do everything by default.
	 *
	 * @param string[] $required_caps Required primitive capabilities for the requested capability.
	 * @param string   $cap           Capability or meta capability being checked.
	 * @param int      $user_id       Concerned user ID.
	 * @param array    $args {
	 *     Arguments that accompany the requested capability check.
	 *
	 *     @type mixed ...$0 Optional second and further parameters.
	 * }
	 * @return string[] Required capabilities for the requested action.
	 */
	public function filter_map_meta_cap( array $required_caps, $cap, $user_id, array $args ) {
		if ( ( 'switch_to_user' === $cap ) && ( $args[0] === $user_id ) ) {
			$required_caps[] = 'do_not_allow';
		}
		return $required_caps;
	}

	/**
	 * Singleton instantiator.
	 *
	 * @return user_switching User Switching instance.
	 */
	public static function get_instance() {
		static $instance;

		if ( ! isset( $instance ) ) {
			$instance = new user_switching;
		}

		return $instance;
	}

}

if ( ! function_exists( 'user_switching_set_olduser_cookie' ) ) {
/**
 * Sets authorisation cookies containing the originating user information.
 *
 * @param int  $old_user_id The ID of the originating user, usually the current logged in user.
 * @param bool $pop         Optional. Pop the latest user off the auth cookie, instead of appending the new one. Default false.
 */
function user_switching_set_olduser_cookie( $old_user_id, $pop = false ) {
	$secure_auth_cookie    = user_switching::secure_auth_cookie();
	$secure_olduser_cookie = user_switching::secure_olduser_cookie();
	$expiration            = time() + 172800; // 48 hours
	$auth_cookie           = user_switching_get_auth_cookie();
	$olduser_cookie        = wp_generate_auth_cookie( $old_user_id, $expiration, 'logged_in' );

	if ( $secure_auth_cookie ) {
		$auth_cookie_name = USER_SWITCHING_SECURE_COOKIE;
		$scheme = 'secure_auth';
	} else {
		$auth_cookie_name = USER_SWITCHING_COOKIE;
		$scheme = 'auth';
	}

	if ( $pop ) {
		array_pop( $auth_cookie );
	} else {
		array_push( $auth_cookie, wp_generate_auth_cookie( $old_user_id, $expiration, $scheme ) );
	}

	setcookie( $auth_cookie_name, json_encode( $auth_cookie ), $expiration, SITECOOKIEPATH, COOKIE_DOMAIN, $secure_auth_cookie, true );
	setcookie( USER_SWITCHING_OLDUSER_COOKIE, $olduser_cookie, $expiration, COOKIEPATH, COOKIE_DOMAIN, $secure_olduser_cookie, true );
}
}

if ( ! function_exists( 'user_switching_clear_olduser_cookie' ) ) {
/**
 * Clears the cookies containing the originating user, or pops the latest item off the end if there's more than one.
 *
 * @param bool $clear_all Optional. Whether to clear the cookies (as opposed to just popping the last user off the end). Default true.
 */
function user_switching_clear_olduser_cookie( $clear_all = true ) {
	$auth_cookie = user_switching_get_auth_cookie();
	if ( ! empty( $auth_cookie ) ) {
		array_pop( $auth_cookie );
	}
	if ( $clear_all || empty( $auth_cookie ) ) {
		$expire = time() - 31536000;
		setcookie( USER_SWITCHING_COOKIE,         ' ', $expire, SITECOOKIEPATH, COOKIE_DOMAIN );
		setcookie( USER_SWITCHING_SECURE_COOKIE,  ' ', $expire, SITECOOKIEPATH, COOKIE_DOMAIN );
		setcookie( USER_SWITCHING_OLDUSER_COOKIE, ' ', $expire, COOKIEPATH, COOKIE_DOMAIN );
	} else {

		if ( user_switching::secure_auth_cookie() ) {
			$scheme = 'secure_auth';
		} else {
			$scheme = 'auth';
		}

		$old_user_id = wp_validate_auth_cookie( end( $auth_cookie ), $scheme );
		if ( $old_user_id ) {
			user_switching_set_olduser_cookie( $old_user_id, true );
		}
	}
}
}

if ( ! function_exists( 'user_switching_get_olduser_cookie' ) ) {
/**
 * Gets the value of the cookie containing the originating user.
 *
 * @return string|bool The old user cookie, or boolean false if there isn't one.
 */
function user_switching_get_olduser_cookie() {
	if ( isset( $_COOKIE[ USER_SWITCHING_OLDUSER_COOKIE ] ) ) {
		return wp_unslash( $_COOKIE[ USER_SWITCHING_OLDUSER_COOKIE ] ); // WPCS: sanitization ok
	} else {
		return false;
	}
}
}

if ( ! function_exists( 'user_switching_get_auth_cookie' ) ) {
/**
 * Gets the value of the auth cookie containing the list of originating users.
 *
 * @return string[] Array of originating user authentication cookie values. Empty array if there are none.
 */
function user_switching_get_auth_cookie() {
	if ( user_switching::secure_auth_cookie() ) {
		$auth_cookie_name = USER_SWITCHING_SECURE_COOKIE;
	} else {
		$auth_cookie_name = USER_SWITCHING_COOKIE;
	}

	if ( isset( $_COOKIE[ $auth_cookie_name ] ) ) {
		$cookie = json_decode( wp_unslash( $_COOKIE[ $auth_cookie_name ] ) ); // WPCS: sanitization ok
	}
	if ( ! isset( $cookie ) || ! is_array( $cookie ) ) {
		$cookie = array();
	}
	return $cookie;
}
}

if ( ! function_exists( 'switch_to_user' ) ) {
/**
 * Switches the current logged in user to the specified user.
 *
 * @param  int  $user_id      The ID of the user to switch to.
 * @param  bool $remember     Optional. Whether to 'remember' the user in the form of a persistent browser cookie. Default false.
 * @param  bool $set_old_user Optional. Whether to set the old user cookie. Default true.
 * @return bool|WP_User WP_User object on success, false on failure.
 */
function switch_to_user( $user_id, $remember = false, $set_old_user = true ) {
	$user = get_userdata( $user_id );

	if ( ! $user ) {
		return false;
	}

	$old_user_id = ( is_user_logged_in() ) ? get_current_user_id() : false;

	if ( $set_old_user && $old_user_id ) {
		user_switching_set_olduser_cookie( $old_user_id );
	} else {
		user_switching_clear_olduser_cookie( false );
	}

	wp_clear_auth_cookie();
	wp_set_auth_cookie( $user_id, $remember );
	wp_set_current_user( $user_id );

	if ( $set_old_user ) {
		/**
		 * Fires when a user switches to another user account.
		 *
		 * @since 0.6.0
		 *
		 * @param int $user_id     The ID of the user being switched to.
		 * @param int $old_user_id The ID of the user being switched from.
		 */
		do_action( 'switch_to_user', $user_id, $old_user_id );
	} else {
		/**
		 * Fires when a user switches back to their originating account.
		 *
		 * @since 0.6.0
		 *
		 * @param int       $user_id     The ID of the user being switched back to.
		 * @param int|false $old_user_id The ID of the user being switched from, or false if the user is switching back
		 *                               after having been switched off.
		 */
		do_action( 'switch_back_user', $user_id, $old_user_id );
	}

	return $user;
}
}

if ( ! function_exists( 'switch_off_user' ) ) {
/**
 * Switches off the current logged in user. This logs the current user out while retaining a cookie allowing them to log
 * straight back in using the 'Switch back to {user}' system.
 *
 * @return bool True on success, false on failure.
 */
function switch_off_user() {
	$old_user_id = get_current_user_id();

	if ( ! $old_user_id ) {
		return false;
	}

	user_switching_set_olduser_cookie( $old_user_id );
	wp_clear_auth_cookie();
	wp_set_current_user( 0 );

	/**
	 * Fires when a user switches off.
	 *
	 * @since 0.6.0
	 *
	 * @param int $old_user_id The ID of the user switching off.
	 */
	do_action( 'switch_off_user', $old_user_id );

	return true;
}
}

if ( ! function_exists( 'current_user_switched' ) ) {
/**
 * Returns whether or not the current user switched into their account.
 *
 * @return bool|WP_User False if the user isn't logged in or they didn't switch in; old user object (which evaluates to
 *                      true) if the user switched into the current user account.
 */
function current_user_switched() {
	if ( ! is_user_logged_in() ) {
		return false;
	}

	return user_switching::get_old_user();
}
}

$GLOBALS['user_switching'] = user_switching::get_instance();
