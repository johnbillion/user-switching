<?php
/**
 * User Switching plugin for WordPress
 *
 * @package   user-switching
 * @link      https://github.com/johnbillion/user-switching
 * @author    John Blackbourn
 * @copyright 2009-2023 John Blackbourn
 * @license   GPL v2 or later
 *
 * Plugin Name:       User Switching
 * Description:       Instant switching between user accounts in WordPress
 * Version:           1.7.2
 * Plugin URI:        https://wordpress.org/plugins/user-switching/
 * Author:            John Blackbourn & contributors
 * Author URI:        https://github.com/johnbillion/user-switching/graphs/contributors
 * Text Domain:       user-switching
 * Domain Path:       /languages/
 * Network:           true
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main singleton class for the User Switching plugin.
 */
class user_switching {
	/**
	 * The name used to identify the application during a WordPress redirect.
	 *
	 * @var string
	 */
	public static $application = 'WordPress/User Switching';

	const REDIRECT_TYPE_NONE = null;
	const REDIRECT_TYPE_URL = 'url';
	const REDIRECT_TYPE_POST = 'post';
	const REDIRECT_TYPE_TERM = 'term';
	const REDIRECT_TYPE_USER = 'user';
	const REDIRECT_TYPE_COMMENT = 'comment';

	/**
	 * Sets up all the filters and actions.
	 *
	 * @return void
	 */
	public function init_hooks() {
		// Required functionality:
		add_filter( 'user_has_cap', array( $this, 'filter_user_has_cap' ), 10, 4 );
		add_filter( 'map_meta_cap', array( $this, 'filter_map_meta_cap' ), 10, 4 );
		add_filter( 'user_row_actions', array( $this, 'filter_user_row_actions' ), 10, 2 );
		add_action( 'plugins_loaded', array( $this, 'action_plugins_loaded' ), 1 );
		add_action( 'init', array( $this, 'action_init' ) );
		add_action( 'all_admin_notices', array( $this, 'action_admin_notices' ), 1 );
		add_action( 'wp_logout', 'user_switching_clear_olduser_cookie' );
		add_action( 'wp_login', 'user_switching_clear_olduser_cookie' );

		// Nice-to-haves:
		add_filter( 'ms_user_row_actions', array( $this, 'filter_user_row_actions' ), 10, 2 );
		add_filter( 'login_message', array( $this, 'filter_login_message' ), 1 );
		add_filter( 'removable_query_args', array( $this, 'filter_removable_query_args' ) );
		add_action( 'wp_meta', array( $this, 'action_wp_meta' ) );
		add_filter( 'plugin_row_meta', array( $this, 'filter_plugin_row_meta' ), 10, 2 );
		add_action( 'wp_footer', array( $this, 'action_wp_footer' ) );
		add_action( 'personal_options', array( $this, 'action_personal_options' ) );
		add_action( 'admin_bar_menu', array( $this, 'action_admin_bar_menu' ), 11 );
		add_action( 'bp_member_header_actions', array( $this, 'action_bp_button' ), 11 );
		add_action( 'bp_directory_members_actions', array( $this, 'action_bp_button' ), 11 );
		add_action( 'bbp_template_after_user_details_menu_items', array( $this, 'action_bbpress_button' ) );
		add_action( 'woocommerce_login_form_start', array( $this, 'action_woocommerce_login_form_start' ), 10, 0 );
		add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'action_woocommerce_order_details' ), 1 );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'filter_woocommerce_account_menu_items' ), 999 );
		add_filter( 'woocommerce_get_endpoint_url', array( $this, 'filter_woocommerce_get_endpoint_url' ), 10, 2 );
		add_action( 'switch_to_user', array( $this, 'forget_woocommerce_session' ) );
		add_action( 'switch_back_user', array( $this, 'forget_woocommerce_session' ) );
	}

	/**
	 * Defines the names of the cookies used by User Switching.
	 *
	 * @return void
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
	 * Outputs the 'Switch To' link on the user editing screen if the current user has permission to switch to them.
	 *
	 * @param WP_User $user User object for this screen.
	 * @return void
	 */
	public function action_personal_options( WP_User $user ) {
		$link = self::maybe_switch_url( $user );

		if ( ! $link ) {
			return;
		}

		?>
		<tr class="user-switching-wrap">
			<th scope="row">
				<?php echo esc_html_x( 'User Switching', 'User Switching title on user profile screen', 'user-switching' ); ?>
			</th>
			<td>
				<a id="user_switching_switcher" href="<?php echo esc_url( $link ); ?>">
					<?php esc_html_e( 'Switch&nbsp;To', 'user-switching' ); ?>
				</a>
			</td>
		</tr>
		<?php
	}

	/**
	 * Returns whether the current logged in user is being remembered in the form of a persistent browser cookie
	 * (ie. they checked the 'Remember Me' check box when they logged in). This is used to persist the 'remember me'
	 * value when the user switches to another user.
	 *
	 * @return bool Whether the current user is being 'remembered'.
	 */
	public static function remember() {
		/** This filter is documented in wp-includes/pluggable.php */
		$cookie_life = apply_filters( 'auth_cookie_expiration', 172800, get_current_user_id(), false );
		$current = wp_parse_auth_cookie( '', 'logged_in' );

		if ( ! $current ) {
			return false;
		}

		// Here we calculate the expiration length of the current auth cookie and compare it to the default expiration.
		// If it's greater than this, then we know the user checked 'Remember Me' when they logged in.
		return ( intval( $current['expiration'] ) - time() > $cookie_life );
	}

	/**
	 * Loads localisation files and routes actions depending on the 'action' query var.
	 *
	 * @return void
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
				$user_id = absint( $_REQUEST['user_id'] ?? 0 );

				// Check authentication:
				if ( ! current_user_can( 'switch_to_user', $user_id ) ) {
					wp_die( esc_html__( 'Could not switch users.', 'user-switching' ), 403 );
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
						wp_safe_redirect( add_query_arg( $args, $redirect_to ), 302, self::$application );
					} elseif ( ! current_user_can( 'read' ) ) {
						wp_safe_redirect( add_query_arg( $args, home_url() ), 302, self::$application );
					} else {
						wp_safe_redirect( add_query_arg( $args, admin_url() ), 302, self::$application );
					}
					exit;
				} else {
					wp_die( esc_html__( 'Could not switch users.', 'user-switching' ), 404 );
				}
				break;

			// We're attempting to switch back to the originating user:
			case 'switch_to_olduser':
				// Fetch the originating user data:
				$old_user = self::get_old_user();
				if ( ! $old_user ) {
					wp_die( esc_html__( 'Could not switch users.', 'user-switching' ), 400 );
				}

				// Check authentication:
				if ( ! self::authenticate_old_user( $old_user ) ) {
					wp_die( esc_html__( 'Could not switch users.', 'user-switching' ), 403 );
				}

				// Check intent:
				check_admin_referer( "switch_to_olduser_{$old_user->ID}" );

				// Switch user:
				if ( switch_to_user( $old_user->ID, self::remember(), false ) ) {

					if ( ! empty( $_REQUEST['interim-login'] ) && function_exists( 'login_header' ) ) {
						$GLOBALS['interim_login'] = 'success'; // @codingStandardsIgnoreLine
						login_header( '', '' );
						exit;
					}

					$redirect_to = self::get_redirect( $old_user, $current_user );
					$args = array(
						'user_switched' => 'true',
						'switched_back' => 'true',
					);

					if ( $redirect_to ) {
						wp_safe_redirect( add_query_arg( $args, $redirect_to ), 302, self::$application );
					} else {
						wp_safe_redirect( add_query_arg( $args, admin_url( 'users.php' ) ), 302, self::$application );
					}
					exit;
				} else {
					wp_die( esc_html__( 'Could not switch users.', 'user-switching' ), 404 );
				}
				break;

			// We're attempting to switch off the current user:
			case 'switch_off':
				// Check authentication:
				if ( ! $current_user || ! current_user_can( 'switch_off' ) ) {
					/* Translators: "switch off" means to temporarily log out */
					wp_die( esc_html__( 'Could not switch off.', 'user-switching' ), 403 );
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
						wp_safe_redirect( add_query_arg( $args, $redirect_to ), 302, self::$application );
					} else {
						wp_safe_redirect( add_query_arg( $args, home_url() ), 302, self::$application );
					}
					exit;
				} else {
					/* Translators: "switch off" means to temporarily log out */
					wp_die( esc_html__( 'Could not switch off.', 'user-switching' ), 403 );
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
		$redirect_to = '';
		$requested_redirect_to = '';
		$redirect_type = self::REDIRECT_TYPE_NONE;

		if ( ! empty( $_REQUEST['redirect_to'] ) ) {
			// URL
			$redirect_to = self::remove_query_args( wp_unslash( $_REQUEST['redirect_to'] ) );
			$requested_redirect_to = wp_unslash( $_REQUEST['redirect_to'] );
			$redirect_type = self::REDIRECT_TYPE_URL;
		} elseif ( ! empty( $_GET['redirect_to_post'] ) ) {
			// Post
			$post_id = absint( $_GET['redirect_to_post'] );
			$redirect_type = self::REDIRECT_TYPE_POST;

			if ( function_exists( 'is_post_publicly_viewable' ) && is_post_publicly_viewable( $post_id ) ) {
				$link = get_permalink( $post_id );

				if ( is_string( $link ) ) {
					$redirect_to = $link;
					$requested_redirect_to = $link;
				}
			}
		} elseif ( ! empty( $_GET['redirect_to_term'] ) ) {
			// Term
			$term = get_term( absint( $_GET['redirect_to_term'] ) );
			$redirect_type = self::REDIRECT_TYPE_TERM;

			if ( ( $term instanceof WP_Term ) && is_taxonomy_viewable( $term->taxonomy ) ) {
				$link = get_term_link( $term );

				if ( is_string( $link ) ) {
					$redirect_to = $link;
					$requested_redirect_to = $link;
				}
			}
		} elseif ( ! empty( $_GET['redirect_to_user'] ) ) {
			// User
			$user = get_userdata( absint( $_GET['redirect_to_user'] ) );
			$redirect_type = self::REDIRECT_TYPE_USER;

			if ( $user instanceof WP_User ) {
				$link = get_author_posts_url( $user->ID );

				if ( is_string( $link ) ) {
					$redirect_to = $link;
					$requested_redirect_to = $link;
				}
			}
		} elseif ( ! empty( $_GET['redirect_to_comment'] ) ) {
			// Comment
			$comment = get_comment( absint( $_GET['redirect_to_comment'] ) );
			$redirect_type = self::REDIRECT_TYPE_COMMENT;

			if ( $comment instanceof WP_Comment ) {
				if ( 'approved' === wp_get_comment_status( $comment ) ) {
					$link = get_comment_link( $comment );

					if ( is_string( $link ) ) {
						$redirect_to = $link;
						$requested_redirect_to = $link;
					}
				} elseif ( function_exists( 'is_post_publicly_viewable' ) && is_post_publicly_viewable( (int) $comment->comment_post_ID ) ) {
					$link = get_permalink( (int) $comment->comment_post_ID );

					if ( is_string( $link ) ) {
						$redirect_to = $link;
						$requested_redirect_to = $link;
					}
				}
			}
		}

		if ( ! $new_user ) {
			/** This filter is documented in wp-login.php */
			$redirect_to = apply_filters( 'logout_redirect', $redirect_to, $requested_redirect_to, $old_user );
		} else {
			/** This filter is documented in wp-login.php */
			$redirect_to = apply_filters( 'login_redirect', $redirect_to, $requested_redirect_to, $new_user );
		}

		/**
		 * Filters the redirect location after a user switches to another account or switches off.
		 *
		 * @since 1.7.0
		 *
		 * @param string       $redirect_to   The target redirect location, or an empty string if none is specified.
		 * @param string|null  $redirect_type The redirect type, see the `user_switching::REDIRECT_*` constants.
		 * @param WP_User|null $new_user      The user being switched to, or null if there is none.
		 * @param WP_User|null $old_user      The user being switched from, or null if there is none.
		 */
		return apply_filters( 'user_switching_redirect_to', $redirect_to, $redirect_type, $new_user, $old_user );
	}

	/**
	 * Displays the 'Switched to {user}' and 'Switch back to {user}' messages in the admin area.
	 *
	 * @return void
	 */
	public function action_admin_notices() {
		$user = wp_get_current_user();
		$old_user = self::get_old_user();

		if ( $old_user ) {
			$switched_locale = false;
			$lang_attr = '';
			$locale = get_user_locale( $old_user );
			$switched_locale = switch_to_locale( $locale );
			$lang_attr = str_replace( '_', '-', $locale );

			?>
			<div id="user_switching" class="updated notice notice-success is-dismissible">
				<?php
				if ( $lang_attr ) {
					printf(
						'<p lang="%s">',
						esc_attr( $lang_attr )
					);
				} else {
					echo '<p>';
				}
				?>
				<span class="dashicons dashicons-admin-users" style="color:#56c234" aria-hidden="true"></span>
				<?php
				$message = '';
				$just_switched = isset( $_GET['user_switched'] );
				if ( $just_switched ) {
					$message = esc_html( self::switched_to_message( $user ) );
				}
				$switch_back_url = add_query_arg( array(
					'redirect_to' => rawurlencode( self::current_url() ),
				), self::switch_back_url( $old_user ) );

				$message .= sprintf(
					' <a href="%s">%s</a>.',
					esc_url( $switch_back_url ),
					esc_html( self::switch_back_message( $old_user ) )
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

				echo wp_kses( $message, array(
					'a' => array(
						'href' => array(),
					),
				) );
				?>
				</p>
			</div>
			<?php
			if ( $switched_locale ) {
				restore_previous_locale();
			}
		} elseif ( isset( $_GET['user_switched'] ) ) {
			?>
			<div id="user_switching" class="updated notice notice-success is-dismissible">
				<p>
				<?php
				if ( isset( $_GET['switched_back'] ) ) {
					echo esc_html( self::switched_back_message( $user ) );
				} else {
					echo esc_html( self::switched_to_message( $user ) );
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
	 * @return false|WP_User False if there's no old user cookie or it's invalid, WP_User object if it's present and valid.
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
	 * @param WP_User $user A WP_User object (usually from the logged_in cookie).
	 * @return bool Whether verification with the auth cookie passed.
	 */
	public static function authenticate_old_user( WP_User $user ) {
		$cookie = user_switching_get_auth_cookie();
		if ( ! empty( $cookie ) ) {
			if ( self::secure_auth_cookie() ) {
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
	 * Adds a 'Switch back to {user}' link to the account menu, and a `Switch To` link to the user edit menu.
	 *
	 * @param WP_Admin_Bar $wp_admin_bar The admin bar object.
	 * @return void
	 */
	public function action_admin_bar_menu( WP_Admin_Bar $wp_admin_bar ) {
		if ( ! is_admin_bar_showing() ) {
			return;
		}

		if ( $wp_admin_bar->get_node( 'user-actions' ) ) {
			$parent = 'user-actions';
		} else {
			return;
		}

		$old_user = self::get_old_user();

		if ( $old_user ) {
			$wp_admin_bar->add_node( array(
				'parent' => $parent,
				'id' => 'switch-back',
				'title' => esc_html( self::switch_back_message( $old_user ) ),
				'href' => add_query_arg( array(
					'redirect_to' => rawurlencode( self::current_url() ),
				), self::switch_back_url( $old_user ) ),
			) );
		}

		if ( current_user_can( 'switch_off' ) ) {
			$url = self::switch_off_url( wp_get_current_user() );
			$redirect_to = is_admin() ? self::get_admin_redirect_to() : array(
				'redirect_to' => rawurlencode( self::current_url() ),
			);

			if ( is_array( $redirect_to ) ) {
				$url = add_query_arg( $redirect_to, $url );
			}

			$wp_admin_bar->add_node( array(
				'parent' => $parent,
				'id' => 'switch-off',
				/* Translators: "switch off" means to temporarily log out */
				'title' => esc_html__( 'Switch Off', 'user-switching' ),
				'href' => $url,
			) );
		}

		if ( ! is_admin() && is_author() && ( get_queried_object() instanceof WP_User ) ) {
			if ( $old_user ) {
				$wp_admin_bar->add_node( array(
					'parent' => 'edit',
					'id' => 'author-switch-back',
					'title' => esc_html( self::switch_back_message( $old_user ) ),
					'href' => add_query_arg( array(
						'redirect_to' => rawurlencode( self::current_url() ),
					), self::switch_back_url( $old_user ) ),
				) );
			} elseif ( current_user_can( 'switch_to_user', get_queried_object_id() ) ) {
				$wp_admin_bar->add_node( array(
					'parent' => 'edit',
					'id' => 'author-switch-to',
					'title' => esc_html__( 'Switch&nbsp;To', 'user-switching' ),
					'href' => add_query_arg( array(
						'redirect_to' => rawurlencode( self::current_url() ),
					), self::switch_to_url( get_queried_object() ) ),
				) );
			}
		}
	}

	/**
	 * Returns a context-aware redirect parameter for use when switching off in the admin area.
	 *
	 * This is used to redirect the user to the URL of the item they're editing at the time.
	 *
	 * @return ?array<string, int>
	 */
	public static function get_admin_redirect_to() {
		if ( ! empty( $_GET['post'] ) ) {
			// Post
			return array(
				'redirect_to_post' => intval( $_GET['post'] ),
			);
		} elseif ( ! empty( $_GET['tag_ID'] ) ) {
			// Term
			return array(
				'redirect_to_term' => intval( $_GET['tag_ID'] ),
			);
		} elseif ( ! empty( $_GET['user_id'] ) ) {
			// User
			return array(
				'redirect_to_user' => intval( $_GET['user_id'] ),
			);
		} elseif ( ! empty( $_GET['c'] ) ) {
			// Comment
			return array(
				'redirect_to_comment' => intval( $_GET['c'] ),
			);
		}

		return null;
	}

	/**
	 * Adds a 'Switch back to {user}' link to the Meta sidebar widget.
	 *
	 * @return void
	 */
	public function action_wp_meta() {
		$old_user = self::get_old_user();

		if ( $old_user instanceof WP_User ) {
			$url = add_query_arg( array(
				'redirect_to' => rawurlencode( self::current_url() ),
			), self::switch_back_url( $old_user ) );
			printf(
				'<li id="user_switching_switch_on"><a href="%s">%s</a></li>',
				esc_url( $url ),
				esc_html( self::switch_back_message( $old_user ) )
			);
		}
	}

	/**
	 * Adds a 'Switch back to {user}' link to the WordPress footer if the admin toolbar isn't showing.
	 *
	 * @return void
	 */
	public function action_wp_footer() {
		if ( is_admin_bar_showing() || did_action( 'wp_meta' ) ) {
			return;
		}

		/**
		 * Allows the 'Switch back to {user}' link in the WordPress footer to be disabled.
		 *
		 * @since 1.5.5
		 *
		 * @param bool $show_in_footer Whether to show the 'Switch back to {user}' link in footer.
		 */
		if ( ! apply_filters( 'user_switching_in_footer', true ) ) {
			return;
		}

		$old_user = self::get_old_user();

		if ( $old_user instanceof WP_User ) {
			$url = add_query_arg( array(
				'redirect_to' => rawurlencode( self::current_url() ),
			), self::switch_back_url( $old_user ) );
			printf(
				'<p id="user_switching_switch_on" style="position:fixed;bottom:40px;padding:0;margin:0;left:10px;font-size:13px;z-index:99999;"><a href="%s">%s</a></p>',
				esc_url( $url ),
				esc_html( self::switch_back_message( $old_user ) )
			);
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
			$url = self::switch_back_url( $old_user );

			if ( ! empty( $_REQUEST['interim-login'] ) ) {
				$url = add_query_arg( array(
					'interim-login' => '1',
				), $url );
			} elseif ( ! empty( $_REQUEST['redirect_to'] ) ) {
				$url = add_query_arg( array(
					'redirect_to' => rawurlencode( wp_unslash( $_REQUEST['redirect_to'] ) ),
				), $url );
			}

			$message .= '<p class="message" id="user_switching_switch_on">';
			$message .= '<span class="dashicons dashicons-admin-users" style="color:#56c234" aria-hidden="true"></span> ';
			$message .= sprintf(
				'<a href="%1$s" onclick="window.location.href=\'%1$s\';return false;">%2$s</a>',
				esc_url( $url ),
				esc_html( self::switch_back_message( $old_user ) )
			);
			$message .= '</p>';
		}

		return $message;
	}

	/**
	 * Adds a 'Switch To' link to each list of user actions on the Users screen.
	 *
	 * @param array<string,string> $actions Array of actions to display for this user row.
	 * @param WP_User              $user    The user object displayed in this row.
	 * @return array<string,string> Array of actions to display for this user row.
	 */
	public function filter_user_row_actions( array $actions, WP_User $user ) {
		$link = self::maybe_switch_url( $user );

		if ( ! $link ) {
			return $actions;
		}

		$actions['switch_to_user'] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $link ),
			esc_html__( 'Switch&nbsp;To', 'user-switching' )
		);

		return $actions;
	}

	/**
	 * Adds a 'Switch To' link to each member's profile page and profile listings in BuddyPress.
	 *
	 * @return void
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
			'redirect_to' => rawurlencode( bp_core_get_user_domain( $user->ID ) ),
		), $link );

		$components = array_keys( buddypress()->active_components );

		echo bp_get_button( array(
			'id' => 'user_switching',
			'component' => reset( $components ),
			'link_href' => esc_url( $link ),
			'link_text' => esc_html__( 'Switch&nbsp;To', 'user-switching' ),
			'wrapper_id' => 'user_switching_switch_to',
		) );
	}

	/**
	 * Adds a 'Switch To' link to each member's profile page in bbPress.
	 *
	 * @return void
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
			'redirect_to' => rawurlencode( bbp_get_user_profile_url( $user->ID ) ),
		), $link );

		echo '<ul id="user_switching_switch_to">';
		printf(
			'<li><a href="%s">%s</a></li>',
			esc_url( $link ),
			esc_html__( 'Switch&nbsp;To', 'user-switching' )
		);
		echo '</ul>';
	}

	/**
	 * Filters the array of row meta for each plugin in the Plugins list table.
	 *
	 * @param array<int,string> $plugin_meta An array of the plugin row's meta data.
	 * @param string            $plugin_file Path to the plugin file relative to the plugins directory.
	 * @return array<int,string> An array of the plugin row's meta data.
	 */
	public function filter_plugin_row_meta( array $plugin_meta, $plugin_file ) {
		if ( 'user-switching/user-switching.php' !== $plugin_file ) {
			return $plugin_meta;
		}

		$plugin_meta[] = sprintf(
			'<a href="%1$s"><span class="dashicons dashicons-star-filled" aria-hidden="true" style="font-size:14px;line-height:1.3"></span>%2$s</a>',
			'https://github.com/sponsors/johnbillion',
			esc_html_x( 'Sponsor', 'verb', 'user-switching' )
		);

		return $plugin_meta;
	}

	/**
	 * Filters the list of query arguments which get removed from admin area URLs in WordPress.
	 *
	 * @link https://core.trac.wordpress.org/ticket/23367
	 *
	 * @param array<int,string> $args Array of removable query arguments.
	 * @return array<int,string> Updated array of removable query arguments.
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
	 * @return string|false The required URL, or false if there's no old user or the user doesn't have the required capability.
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
			'action' => 'switch_to_user',
			'user_id' => $user->ID,
			'nr' => 1,
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
			'nr' => 1,
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
			'nr' => 1,
		), wp_login_url() ), "switch_off_{$user->ID}" );
	}

	/**
	 * Returns the message shown to the user when they've switched to a user.
	 *
	 * @param WP_User $user The concerned user.
	 * @return string The message.
	 */
	public static function switched_to_message( WP_User $user ) {
		$message = sprintf(
			/* Translators: 1: user display name; 2: username; */
			__( 'Switched to %1$s (%2$s).', 'user-switching' ),
			$user->display_name,
			$user->user_login
		);

		// Removes the user login from this message without invalidating existing translations
		return str_replace( sprintf(
			' (%s)',
			$user->user_login
		), '', $message );
	}

	/**
	 * Returns the message shown to the user for the link to switch back to their original user.
	 *
	 * @param WP_User $user The concerned user.
	 * @return string The message.
	 */
	public static function switch_back_message( WP_User $user ) {
		$message = sprintf(
			/* Translators: 1: user display name; 2: username; */
			__( 'Switch back to %1$s (%2$s)', 'user-switching' ),
			$user->display_name,
			$user->user_login
		);

		// Removes the user login from this message without invalidating existing translations
		return str_replace( sprintf(
			' (%s)',
			$user->user_login
		), '', $message );
	}

	/**
	 * Returns the message shown to the user when they've switched back to their original user.
	 *
	 * @param WP_User $user The concerned user.
	 * @return string The message.
	 */
	public static function switched_back_message( WP_User $user ) {
		$message = sprintf(
			/* Translators: 1: user display name; 2: username; */
			__( 'Switched back to %1$s (%2$s).', 'user-switching' ),
			$user->display_name,
			$user->user_login
		);

		// Removes the user login from this message without invalidating existing translations
		return str_replace( sprintf(
			' (%s)',
			$user->user_login
		), '', $message );
	}

	/**
	 * Returns the current URL.
	 *
	 * @return string The current URL.
	 */
	public static function current_url() {
		return ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	/**
	 * Removes a list of common confirmation-style query args from a URL.
	 *
	 * @param string $url A URL.
	 * @return string The URL with query args removed.
	 */
	public static function remove_query_args( $url ) {
		return remove_query_arg( wp_removable_query_args(), $url );
	}

	/**
	 * Returns whether User Switching's equivalent of the 'logged_in' cookie should be secure.
	 *
	 * This is used to set the 'secure' flag on the old user cookie, for enhanced security.
	 *
	 * @link https://core.trac.wordpress.org/ticket/15330
	 *
	 * @return bool Should the old user cookie be secure?
	 */
	public static function secure_olduser_cookie() {
		return ( is_ssl() && ( 'https' === wp_parse_url( home_url(), PHP_URL_SCHEME ) ) );
	}

	/**
	 * Returns whether User Switching's equivalent of the 'auth' cookie should be secure.
	 *
	 * This is used to determine whether to set a secure auth cookie.
	 *
	 * @return bool Whether the auth cookie should be secure.
	 */
	public static function secure_auth_cookie() {
		return ( is_ssl() && ( 'https' === wp_parse_url( wp_login_url(), PHP_URL_SCHEME ) ) );
	}

	/**
	 * Adds a 'Switch back to {user}' link to the WooCommerce login screen.
	 *
	 * @return void
	 */
	public function action_woocommerce_login_form_start() {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $this->filter_login_message( '' );
	}

	/**
	 * Adds a 'Switch To' link to the WooCommerce order screen.
	 *
	 * @param WC_Order $order The WooCommerce order object.
	 * @return void
	 */
	public function action_woocommerce_order_details( WC_Order $order ) {
		$user = $order->get_user();

		if ( ! $user || ! current_user_can( 'switch_to_user', $user->ID ) ) {
			return;
		}

		$url = add_query_arg( array(
			'redirect_to' => rawurlencode( $order->get_view_order_url() ),
		), self::switch_to_url( $user ) );

		printf(
			'<p class="form-field form-field-wide"><a href="%1$s">%2$s</a></p>',
			esc_url( $url ),
			esc_html__( 'Switch&nbsp;To', 'user-switching' )
		);
	}

	/**
	 * Adds a 'Switch back to {user}' link to the My Account screen in WooCommerce.
	 *
	 * @param array<string, string> $items Menu items.
	 * @return array<string, string> Menu items.
	 */
	public function filter_woocommerce_account_menu_items( array $items ) {
		$old_user = self::get_old_user();

		if ( ! $old_user ) {
			return $items;
		}

		$items['user-switching-switch-back'] = self::switch_back_message( $old_user );

		return $items;
	}

	/**
	 * Sets the URL of the 'Switch back to {user}' link in the My Account screen in WooCommerce.
	 *
	 * @param string $url      The URL for the menu item.
	 * @param string $endpoint The endpoint slug for the menu item.
	 * @return string  The URL for the menu item.
	 */
	public function filter_woocommerce_get_endpoint_url( $url, $endpoint ) {
		if ( 'user-switching-switch-back' !== $endpoint ) {
			return $url;
		}

		$old_user = self::get_old_user();

		if ( ! $old_user ) {
			return $url;
		}

		return self::switch_back_url( $old_user );
	}

	/**
	 * Instructs WooCommerce to forget the session for the current user, without deleting it.
	 *
	 * @return void
	 */
	public function forget_woocommerce_session() {
		if ( ! function_exists( 'WC' ) ) {
			return;
		}

		$wc = WC();

		if ( ! property_exists( $wc, 'session' ) ) {
			return;
		}

		if ( ! method_exists( $wc->session, 'forget_session' ) ) {
			return;
		}

		$wc->session->forget_session();
	}

	/**
	 * Filters a user's capabilities so they can be altered at runtime.
	 *
	 * This is used to:
	 *
	 *  - Grant the 'switch_to_user' capability to the user if they have the ability to edit the user they're trying to
	 *    switch to (and that user is not themselves).
	 *  - Grant the 'switch_off' capability to the user if they can edit other users.
	 *
	 * Important: This does not get called for Super Admins. See filter_map_meta_cap() below.
	 *
	 * @param array<string,bool> $user_caps     Array of key/value pairs where keys represent a capability name and boolean values
	 *                                          represent whether the user has that capability.
	 * @param array<int,string>  $required_caps Array of required primitive capabilities for the requested capability.
	 * @param array<int,mixed>   $args {
	 *     Arguments that accompany the requested capability check.
	 *
	 *     @type string    $0 Requested capability.
	 *     @type int       $1 Concerned user ID.
	 *     @type mixed  ...$2 Optional second and further parameters.
	 * }
	 * @param WP_User            $user          Concerned user object.
	 * @return array<string,bool> Array of concerned user's capabilities.
	 */
	public function filter_user_has_cap( array $user_caps, array $required_caps, array $args, WP_User $user ) {
		if ( 'switch_to_user' === $args[0] ) {
			if ( empty( $args[2] ) ) {
				$user_caps['switch_to_user'] = false;
				return $user_caps;
			}
			if ( array_key_exists( 'switch_users', $user_caps ) ) {
				$user_caps['switch_to_user'] = $user_caps['switch_users'];
				return $user_caps;
			}

			$user_caps['switch_to_user'] = ( user_can( $user->ID, 'edit_user', $args[2] ) && ( $args[2] !== $user->ID ) );
		} elseif ( 'switch_off' === $args[0] ) {
			if ( array_key_exists( 'switch_users', $user_caps ) ) {
				$user_caps['switch_off'] = $user_caps['switch_users'];
				return $user_caps;
			}

			$user_caps['switch_off'] = user_can( $user->ID, 'edit_users' );
		}

		return $user_caps;
	}

	/**
	 * Filters the required primitive capabilities for the given primitive or meta capability.
	 *
	 * This is used to:
	 *
	 *  - Add the 'do_not_allow' capability to the list of required capabilities when a Super Admin is trying to switch
	 *    to themselves.
	 *
	 * It affects nothing else as Super Admins can do everything by default.
	 *
	 * @param array<int,string> $required_caps Array of required primitive capabilities for the requested capability.
	 * @param string            $cap           Capability or meta capability being checked.
	 * @param int               $user_id       Concerned user ID.
	 * @param array<int,mixed>  $args {
	 *     Arguments that accompany the requested capability check.
	 *
	 *     @type mixed ...$0 Optional second and further parameters.
	 * }
	 * @return array<int,string> Array of required capabilities for the requested action.
	 */
	public function filter_map_meta_cap( array $required_caps, $cap, $user_id, array $args ) {
		if ( 'switch_to_user' === $cap ) {
			if ( empty( $args[0] ) || $args[0] === $user_id ) {
				$required_caps[] = 'do_not_allow';
			}
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
			$instance = new user_switching();
		}

		return $instance;
	}

	/**
	 * Private class constructor. Use `get_instance()` to get the instance.
	 */
	private function __construct() {}
}

if ( ! function_exists( 'user_switching_set_olduser_cookie' ) ) {
	/**
	 * Sets authorisation cookies containing the originating user information.
	 *
	 * @since 1.4.0 The `$token` parameter was added.
	 *
	 * @param int    $old_user_id The ID of the originating user, usually the current logged in user.
	 * @param bool   $pop         Optional. Pop the latest user off the auth cookie, instead of appending the new one. Default false.
	 * @param string $token       Optional. The old user's session token to store for later reuse. Default empty string.
	 * @return void
	 */
	function user_switching_set_olduser_cookie( $old_user_id, $pop = false, $token = '' ) {
		$secure_auth_cookie = user_switching::secure_auth_cookie();
		$secure_olduser_cookie = user_switching::secure_olduser_cookie();
		$expiration = time() + 172800; // 48 hours
		$auth_cookie = user_switching_get_auth_cookie();
		$olduser_cookie = wp_generate_auth_cookie( $old_user_id, $expiration, 'logged_in', $token );

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
			array_push( $auth_cookie, wp_generate_auth_cookie( $old_user_id, $expiration, $scheme, $token ) );
		}

		$auth_cookie = wp_json_encode( $auth_cookie );

		if ( false === $auth_cookie ) {
			return;
		}

		/**
		 * Fires immediately before the User Switching authentication cookie is set.
		 *
		 * @since 1.4.0
		 *
		 * @param string $auth_cookie JSON-encoded array of authentication cookie values.
		 * @param int    $expiration  The time when the authentication cookie expires as a UNIX timestamp.
		 * @param int    $old_user_id User ID.
		 * @param string $scheme      Authentication scheme. Values include 'auth' or 'secure_auth'.
		 * @param string $token       User's session token to use for the latest cookie.
		 */
		do_action( 'set_user_switching_cookie', $auth_cookie, $expiration, $old_user_id, $scheme, $token );

		$scheme = 'logged_in';

		/**
		 * Fires immediately before the User Switching old user cookie is set.
		 *
		 * @since 1.4.0
		 *
		 * @param string $olduser_cookie The old user cookie value.
		 * @param int    $expiration     The time when the logged-in authentication cookie expires as a UNIX timestamp.
		 * @param int    $old_user_id    User ID.
		 * @param string $scheme         Authentication scheme. Values include 'auth' or 'secure_auth'.
		 * @param string $token          User's session token to use for this cookie.
		 */
		do_action( 'set_olduser_cookie', $olduser_cookie, $expiration, $old_user_id, $scheme, $token );

		/**
		 * Allows preventing auth cookies from actually being sent to the client.
		 *
		 * @since 1.5.4
		 *
		 * @param bool $send Whether to send auth cookies to the client.
		 */
		if ( ! apply_filters( 'user_switching_send_auth_cookies', true ) ) {
			return;
		}

		setcookie( $auth_cookie_name, $auth_cookie, $expiration, SITECOOKIEPATH, COOKIE_DOMAIN, $secure_auth_cookie, true );
		setcookie( USER_SWITCHING_OLDUSER_COOKIE, $olduser_cookie, $expiration, COOKIEPATH, COOKIE_DOMAIN, $secure_olduser_cookie, true );
	}
}

if ( ! function_exists( 'user_switching_clear_olduser_cookie' ) ) {
	/**
	 * Clears the cookies containing the originating user, or pops the latest item off the end if there's more than one.
	 *
	 * @param bool $clear_all Optional. Whether to clear the cookies (as opposed to just popping the last user off the end). Default true.
	 * @return void
	 */
	function user_switching_clear_olduser_cookie( $clear_all = true ) {
		$auth_cookie = user_switching_get_auth_cookie();
		if ( ! empty( $auth_cookie ) ) {
			array_pop( $auth_cookie );
		}
		if ( $clear_all || empty( $auth_cookie ) ) {
			/**
			 * Fires just before the user switching cookies are cleared.
			 *
			 * @since 1.4.0
			 */
			do_action( 'clear_olduser_cookie' );

			/** This filter is documented in user-switching.php */
			if ( ! apply_filters( 'user_switching_send_auth_cookies', true ) ) {
				return;
			}

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

			$old_cookie = end( $auth_cookie );

			$old_user_id = wp_validate_auth_cookie( $old_cookie, $scheme );
			if ( $old_user_id ) {
				$parts = wp_parse_auth_cookie( $old_cookie, $scheme );

				if ( false !== $parts ) {
					user_switching_set_olduser_cookie( $old_user_id, true, $parts['token'] );
				}
			}
		}
	}
}

if ( ! function_exists( 'user_switching_get_olduser_cookie' ) ) {
	/**
	 * Gets the value of the cookie containing the originating user.
	 *
	 * @return string|false The old user cookie, or boolean false if there isn't one.
	 */
	function user_switching_get_olduser_cookie() {
		if ( isset( $_COOKIE[ USER_SWITCHING_OLDUSER_COOKIE ] ) ) {
			return wp_unslash( $_COOKIE[ USER_SWITCHING_OLDUSER_COOKIE ] );
		} else {
			return false;
		}
	}
}

if ( ! function_exists( 'user_switching_get_auth_cookie' ) ) {
	/**
	 * Gets the value of the auth cookie containing the list of originating users.
	 *
	 * @return array<int,string> Array of originating user authentication cookie values. Empty array if there are none.
	 */
	function user_switching_get_auth_cookie() {
		if ( user_switching::secure_auth_cookie() ) {
			$auth_cookie_name = USER_SWITCHING_SECURE_COOKIE;
		} else {
			$auth_cookie_name = USER_SWITCHING_COOKIE;
		}

		if ( isset( $_COOKIE[ $auth_cookie_name ] ) && is_string( $_COOKIE[ $auth_cookie_name ] ) ) {
			$cookie = json_decode( wp_unslash( $_COOKIE[ $auth_cookie_name ] ) );
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
	 * @return false|WP_User WP_User object on success, false on failure.
	 */
	function switch_to_user( $user_id, $remember = false, $set_old_user = true ) {
		$user = get_userdata( $user_id );

		if ( ! $user ) {
			return false;
		}

		$old_user_id = ( is_user_logged_in() ) ? get_current_user_id() : false;
		$old_token = wp_get_session_token();
		$auth_cookies = user_switching_get_auth_cookie();
		$auth_cookie = end( $auth_cookies );
		$cookie_parts = $auth_cookie ? wp_parse_auth_cookie( $auth_cookie ) : false;

		if ( $set_old_user && $old_user_id ) {
			// Switching to another user
			$new_token = '';
			user_switching_set_olduser_cookie( $old_user_id, false, $old_token );
		} else {
			// Switching back, either after being switched off or after being switched to another user
			$new_token = $cookie_parts['token'] ?? '';
			user_switching_clear_olduser_cookie( false );
		}

		/**
		 * Attaches the original user ID and session token to the new session when a user switches to another user.
		 *
		 * @param array<string, mixed> $session Array of extra data.
		 * @return array<string, mixed> Array of extra data.
		 */
		$session_filter = function ( array $session ) use ( $old_user_id, $old_token ) {
			$session['switched_from_id'] = $old_user_id;
			$session['switched_from_session'] = $old_token;
			return $session;
		};

		add_filter( 'attach_session_information', $session_filter, 99 );

		wp_clear_auth_cookie();
		wp_set_auth_cookie( $user_id, $remember, '', $new_token );
		wp_set_current_user( $user_id );

		remove_filter( 'attach_session_information', $session_filter, 99 );

		if ( $set_old_user && $old_user_id ) {
			/**
			 * Fires when a user switches to another user account.
			 *
			 * @since 0.6.0
			 * @since 1.4.0 The `$new_token` and `$old_token` parameters were added.
			 *
			 * @param int    $user_id     The ID of the user being switched to.
			 * @param int    $old_user_id The ID of the user being switched from.
			 * @param string $new_token   The token of the session of the user being switched to. Can be an empty string
			 *                            or a token for a session that may or may not still be valid.
			 * @param string $old_token   The token of the session of the user being switched from.
			 */
			do_action( 'switch_to_user', $user_id, $old_user_id, $new_token, $old_token );
		} else {
			/**
			 * Fires when a user switches back to their originating account.
			 *
			 * @since 0.6.0
			 * @since 1.4.0 The `$new_token` and `$old_token` parameters were added.
			 *
			 * @param int       $user_id     The ID of the user being switched back to.
			 * @param int|false $old_user_id The ID of the user being switched from, or false if the user is switching back
			 *                               after having been switched off.
			 * @param string    $new_token   The token of the session of the user being switched to. Can be an empty string
			 *                               or a token for a session that may or may not still be valid.
			 * @param string    $old_token   The token of the session of the user being switched from.
			 */
			do_action( 'switch_back_user', $user_id, $old_user_id, $new_token, $old_token );
		}

		if ( $old_token && $old_user_id && ! $set_old_user ) {
			// When switching back, destroy the session for the old user
			$manager = WP_Session_Tokens::get_instance( $old_user_id );
			$manager->destroy( $old_token );
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

		$old_token = wp_get_session_token();

		user_switching_set_olduser_cookie( $old_user_id, false, $old_token );
		wp_clear_auth_cookie();
		wp_set_current_user( 0 );

		/**
		 * Fires when a user switches off.
		 *
		 * @since 0.6.0
		 * @since 1.4.0 The `$old_token` parameter was added.
		 *
		 * @param int    $old_user_id The ID of the user switching off.
		 * @param string $old_token   The token of the session of the user switching off.
		 */
		do_action( 'switch_off_user', $old_user_id, $old_token );

		return true;
	}
}

if ( ! function_exists( 'current_user_switched' ) ) {
	/**
	 * Returns whether the current user switched into their account.
	 *
	 * @return false|WP_User False if the user isn't logged in or they didn't switch in; old user object (which evaluates to
	 *                       true) if the user switched into the current user account.
	 */
	function current_user_switched() {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		return user_switching::get_old_user();
	}
}

$GLOBALS['user_switching'] = user_switching::get_instance();
$GLOBALS['user_switching']->init_hooks();
