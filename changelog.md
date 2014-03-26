
## Upgrade Notice ##

### 0.8.8 ###
* Spanish Translation by Marcelo Pedra.
* User Switching is now a network-only plugin.

## Changelog ##

### 0.8.8 ###
* Spanish Translation by Marcelo Pedra.
* User Switching is now a network-only plugin.

### 0.8.7 ###
* Respect the `secure_logged_in_cookie` and `login_redirect` filters.

### 0.8.6 ###
* Correctly encode the `redirect_to` parameter in the login screen message.

### 0.8.5 ###
* Add a 'Switch To' link to bbPress profile screens.

### 0.8.4 ###
* Revert a change in 0.8.3 which switched to using the `login_init` hook. This hook is fired too late.

### 0.8.3 ###
* Switch to storing data in cookies as JSON instead of PHP-serialized.
* Show admin notices on all possible admin screens.
* Tweak the redirect location for BuddyPress user profiles.
* Change the textdomain in the plugin to match the plugin slug (required for language packs in WordPress core).

### 0.8.2 ###
* Russian translation by R J

### 0.8.1 ###
* Japanese translation by Yusuke Hayasaki

### 0.8 ###
* Nested switching and switching back is now supported (capability permitting). Switch, switch again, switch back, switch back!
* Fix for BuddyPress 1.7 member profile pages. Props nat0n.
* Updated Arabic translation by Hassan Hisham.
* A little code refactoring and improving, completed inline docs.

### 0.7.1 ###
* Arabic translation by Hassan Hisham.
* Minor code tweaks.

### 0.7 ###
* Always show a 'Switch back' link in the footer when the admin toolbar isn't showing.
* More intuitive redirecting after switching.

### 0.6.3 ###
* Lithuanian translation by Tommixoft.

### 0.6.2 ###
* Polish translation by Bartosz Arendt.

### 0.6.1 ###
* Slovak translation by Max Samael.

### 0.6 ###
* More intuitive redirecting after switching.
* Avoid a BuddyPress bug preventing Switch To buttons from appearing.
* Added a template function: `current_user_switched()` which lets you know if the current user switched into their account.
* Added some hooks: `switch_to_user`, `switch_back_user` and `switch_off_user`, fired when appropriate.

### 0.5.2 ###
* Farsi (Persian) translation by Amin Ab.
* Display switch back links in Network Admin and login screen.
* Avoid a BuddyPress bug preventing Switch To buttons from appearing.

### 0.5.1.2 ###
* German translation by Ralph Stenzel.

### 0.5.1.1 ###
* Chinese Simplified translation by Sparanoid.

### 0.5.1 ###
* Toolbar tweaks for WordPress 3.3.

### 0.5 ###
* New "Switch off" function: Log out and log instantly back in again when needed (see the FAQ).

### 0.4.1 ###
* Support for upcoming changes to the admin bar in WordPress 3.3.

### 0.4 ###
* Add some extended support for BuddyPress.
* Add some extended support for Multisite.
* Fix a permissions problem for users with no privileges.
* Fix a PHP warning when used as a mu-plugin (thanks Scribu).

### 0.3.2 ###
* Fix the 'Switch back to' menu item in the WordPress admin bar (WordPress 3.1+).
* Fix a formatting issue on the user profile page.

### 0.3.1 ###
* Prevent admins switching to multisite super admin accounts.

### 0.3 ###
* Adds an admin bar menu item (WordPress 3.1+) for switching back to the user you switched from.

### 0.2.2 ###
* Respect the current 'Remember me' setting when switching users.
* Redirect to home page instead of admin screen if the user you're switching to has no privileges.

### 0.2.1 ###
* Edge case bugfix to prevent 'Switch back to...' message appearing when it shouldn't.

### 0.2 ###
* Functionality for switching back to user you switched from.

### 0.1 ###
* Initial release.
