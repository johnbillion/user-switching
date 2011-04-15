=== User Switching ===
Contributors: johnbillion
Donate link: http://lud.icro.us/donations/
Tags: user, users, profiles, switching, wpmu, multisite
Requires at least: 2.7
Tested up to: 3.1.1
Stable tag: trunk

Instant switching between user accounts in WordPress, WordPress Multisite, and WordPress MU.

== Description ==

This plugin allows you to quickly swap between user accounts in WordPress at the click of a button. You'll be instantly logged out and logged in as your desired user. This is handy for test environments where you regularly log out and in between different accounts, or for adminstrators of blogs with multiple accounts who need to switch between them.

= Features =

 * Instant switching and redirection to the WordPress Dashboard.
 * Instant switching back to the originating account.
 * It's completely secure (see the "Security" section below).
 * Compatible with WordPress, WordPress Multisite, and WordPress MU.

= Security =

 * Only administrators can switch to another user. Lower level users cannot switch between accounts.
 * User switching is protected with the WordPress nonce security system, meaning only those who are allowed to switch users can switch.
 * Full support for administration over SSL (if applicable).
 * Passwords are not (and cannot be) revealed.

== Installation ==

If you're installing this in WordPress MU as a mu-plugin then see the FAQ for slightly different instructions.

1. Unzip the ZIP file and drop the folder straight into your 'wp-content/plugins' directory.
2. Activate the plugin through the 'Plugins' menu.
3. Visit the 'Users' menu and you'll see a 'Switch To' link right next to each user.

== Frequently Asked Questions ==

= Does this plugin work with WordPress Multisite? =

Yes.

= Does this plugin work in WordPress MU? =

Yes.

= Does this plugin work as a mu-plugin? =

Yes, except you'll need to install the 'user-switching.php' file into the root of your mu-plugins directory, not in the 'user-switching' subdirectory. This is a restriction of WordPress, not this plugin.

== Screenshots ==

1. The 'Switch To' link on a user's profile
2. The 'Switch To' link on the Users screen

== Changelog ==

= 0.3.2 =
* Fix the 'Switch back to' menu item in the WordPress admin bar (WordPress 3.1+).
* Fix a formatting issue on the user profile page.

= 0.3.1 =
* Prevent admins switching to multisite super admin accounts.

= 0.3 =
* Adds an admin bar menu item (WordPress 3.1+) for switching back to the user you switched from.

= 0.2.2 =
* Respect the current 'Remember me' setting when switching users.
* Redirect to home page instead of admin screen if the user you're switching to has no privileges.

= 0.2.1 =
* Edge case bugfix to prevent 'Switch back to...' message appearing when it shouldn't.

= 0.2 =
* Functionality for switching back to user you switched from.

= 0.1 =
* Initial release.
