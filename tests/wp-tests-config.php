<?php
/**
 * WordPress Test Configuration
 *
 * @package UserSwitching
 */

// Test database settings.
define( 'DB_NAME', getenv( 'WORDPRESS_DB_NAME' ) );
define( 'DB_USER', getenv( 'WORDPRESS_DB_USER' ) );
define( 'DB_PASSWORD', getenv( 'WORDPRESS_DB_PASSWORD' ) );
define( 'DB_HOST', getenv( 'WORDPRESS_DB_HOST' ) );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

// Test site settings.
define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );
define( 'WP_PHP_BINARY', 'php' );

// Authentication unique keys and salts.
define( 'AUTH_KEY', 'put your unique phrase here' );
define( 'SECURE_AUTH_KEY', 'put your unique phrase here' );
define( 'LOGGED_IN_KEY', 'put your unique phrase here' );
define( 'NONCE_KEY', 'put your unique phrase here' );
define( 'AUTH_SALT', 'put your unique phrase here' );
define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
define( 'LOGGED_IN_SALT', 'put your unique phrase here' );
define( 'NONCE_SALT', 'put your unique phrase here' );

// Table prefix.
$table_prefix = 'wp_integration_'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Required for test configuration.

// WordPress debug settings.
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', true );
}
if ( ! defined( 'WP_DEBUG_DISPLAY' ) ) {
	define( 'WP_DEBUG_DISPLAY', false );
}
if ( ! defined( 'WP_DEBUG_LOG' ) ) {
	define( 'WP_DEBUG_LOG', false );
}

// Multisite configuration (when testing multisite).
// Note: WP_TESTS_MULTISITE is defined in bootstrap.php when needed
if ( defined( 'WP_TESTS_MULTISITE' ) && WP_TESTS_MULTISITE ) {
	define( 'MULTISITE', true );
	define( 'SUBDOMAIN_INSTALL', false );
	define( 'DOMAIN_CURRENT_SITE', WP_TESTS_DOMAIN );
	define( 'PATH_CURRENT_SITE', '/' );
	define( 'SITE_ID_CURRENT_SITE', 1 );
	define( 'BLOG_ID_CURRENT_SITE', 1 );
}

// WordPress absolute path.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/vendor/wordpress/wordpress/' );
}

/* That's all, stop editing! Happy testing. */
