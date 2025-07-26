# User Switching

[![](https://img.shields.io/badge/ethical-open%20source-4baaaa.svg?style=flat-square)](#ethical-open-source)
[![](https://img.shields.io/wordpress/plugin/installs/user-switching?style=flat-square)](https://wordpress.org/plugins/user-switching/)
[![](https://img.shields.io/github/actions/workflow/status/johnbillion/user-switching/integration-tests.yml?branch=develop&style=flat-square)](https://github.com/johnbillion/user-switching/actions)

This plugin allows you to quickly swap between user accounts in WordPress at the click of a button. You'll be instantly logged out and logged in as your desired user. This is handy for helping customers on WooCommerce sites, membership sites, testing environments, or for any site where administrators need to switch between multiple accounts.

## Features

 * Switch user: Instantly switch to any user account from the *Users* screen.
 * Switch back: Instantly switch back to your originating account.
 * Switch off: Log out of your account but retain the ability to instantly switch back in again.
 * Compatible with Multisite, WooCommerce, BuddyPress, and bbPress.
 * Compatible with most membership and user management plugins.
 * Compatible with most two-factor authentication solutions (see the [FAQ](https://wordpress.org/plugins/user-switching/faq/) for more info).
 * Approved for use on enterprise-grade WordPress platforms such as [Altis](https://www.altis-dxp.com/) and [WordPress VIP](https://wpvip.com/).

Note: User Switching supports versions of WordPress up to three years old, and PHP version 7.4 or higher.

## Security

 * Only users with the ability to edit other users can switch user accounts. By default this is only Administrators on single site installations, and Super Admins on Multisite installations.
 * Passwords are not (and cannot be) revealed.
 * Uses the cookie authentication system in WordPress when remembering the account(s) you've switched from and when switching back.
 * Implements the nonce security system in WordPress, meaning only those who intend to switch users can switch.
 * Full support for user session validation where appropriate.
 * Full support for HTTPS.
 * Backed by [the Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/user-switching)

## Usage

 1. Visit the *Users* menu in WordPress and you'll see a *Switch To* link in the list of action links for each user.
 2. Click this and you will immediately switch into that user account.
 3. You can switch back to your originating account via the *Switch back* link on each dashboard screen or in your profile menu in the WordPress toolbar.

## Frequently Asked Questions

[See the FAQ on the WordPress.org plugin page for User Switching](https://wordpress.org/plugins/user-switching/#faq).

## Other Plugins

I maintain several other plugins for developers. Check them out:

* [Query Monitor](https://wordpress.org/plugins/query-monitor/) is the developer tools panel for WordPress
* [WP Crontrol](https://wordpress.org/plugins/wp-crontrol/) lets you view and control what's happening in the WP-Cron system

## Privacy Statement

User Switching does not send data to any third party, nor does it include any third party resources, nor will it ever do so.

User Switching makes use of browser cookies in order to allow users to switch to another account. Its cookies operate using the same mechanism as the authentication cookies in WordPress core, which means their values contain the user's `user_login` field in plain text which should be treated as potentially personally identifiable information (PII) for privacy and regulatory reasons (GDPR, CCPA, etc). The names of the cookies are:

* `wordpress_user_sw_{COOKIEHASH}`
* `wordpress_user_sw_secure_{COOKIEHASH}`
* `wordpress_user_sw_olduser_{COOKIEHASH}`

See also the FAQ for some questions relating to privacy and safety when switching between users.

## Accessibility Statement

User Switching aims to be fully accessible to all of its users. It implements best practices for web accessibility, outputs semantic and structured markup, adheres to the default styles and accessibility guidelines of WordPress, uses the accessibility APIs provided by WordPress and web browsers where appropriate, and is fully accessible via keyboard.

User Switching should adhere to Web Content Accessibility Guidelines (WCAG) 2.0 at level AA when used with a recent version of WordPress where its admin area itself adheres to these guidelines. If you've experienced or identified an accessibility issue in User Switching, please open a thread in [the User Switching plugin support forum](https://wordpress.org/support/plugin/user-switching/) and I'll address it swiftly.

## Sponsors

<p align="center">The time that I spend maintaining this plugin and others is in part sponsored by:</p>

<p align="center"><a href="https://automattic.com"><img src="https://cdn.jsdelivr.net/gh/johnbillion/johnbillion@latest/assets/sponsors/automattic.svg" alt="Automattic" width="50%"></a></p>

<p align="center"><a href="https://servmask.com"><img src="https://cdn.jsdelivr.net/gh/johnbillion/johnbillion@latest/assets/sponsors/servmask.svg" alt="ServMask" width="25%"></a></p>

<p align="center">Plus all my kind sponsors on GitHub:</p>

<p align="center"><a href="https://github.com/sponsors/johnbillion"><img src="https://cdn.jsdelivr.net/gh/johnbillion/johnbillion@latest/sponsors.svg" alt="Sponsors"></p>

<p align="center"><a href="https://github.com/sponsors/johnbillion">Click here to find out about supporting my open source tools and plugins</a>.</p>

## Screenshots

1. The *Switch To* link on the Users screen  
   ![The Switch To link on the Users screen](.wordpress-org/screenshot-1.png)
2. The *Switch To* link on a user's profile  
   ![The Switch To link on a user's profile](.wordpress-org/screenshot-2.png)
