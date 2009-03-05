=== Plugin Info ===
Contributors: johnbillion
Tags: user, users, switching
Requires at least: 2.7
Tested up to: 2.7.1
Stable tag: trunk

Instant user switching for WordPress

= Description =

This plugin allows you to quickly swap between users in WordPress at the click of a button. You'll be instantly logged out and logged in as your desired user. This is handy for test environments where you regularly log out and in between different accounts, or for adminstrators of blogs with multiple accounts who need to switch between them.

= Features =

 * Instant switching and redirection to the WordPress Dashboard.
 * It's completely secure (see the "Security" section below).
 * WordPress and WordPress MU compatible (watch out for a bbPress version soon).

= Security =

 * Only administrators can switch to another user. Lower level users cannot switch between accounts.
 * User switching is protected with the WordPress nonce security system, meaning only those who are allowed to switch users can switch.
 * Full support for administration over SSL (if applicable).
 * Passwords are not (and cannot be) revealed.

== Installation ==

This plugin only works with WordPress 2.7 or later.

1. Unzip the ZIP file and drop the folder straight into your 'wp-content/plugins' directory.
2. Activate the plugin through the 'Plugins' menu.
3. Visit the 'Users' menu and click 'edit' next to a user. From there you'll see a link to 'Switch To' that user.

As an added bonus, anyone using a development version of WordPress (version 2.8-bleeding-edge r10629 or higher) will see a 'Switch To' link right from the 'Users' menu. This feature will appear in the 2.8 release.

== FAQ ==

= Does this plugin work in WordPress MU? =

Yes.

= Does this plugin work as a mu-plugin in WordPress MU? =

Yes, except you'll need to install the 'user-switching.php' file into the root of your mu-plugins directory, not in the 'user-switching' subdirectory. This is a current limitation of WordPress MU, not this plugin.

== Screenshots ==

1. The 'Switch To' link
2. The 'Switch To' link in WordPress 2.8
