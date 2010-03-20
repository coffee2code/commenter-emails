=== Commenter Emails ===
Contributors: coffee2code
Donate link: http://coffee2code.com/donate
Tags: commenter, email, visitor, comment, coffee2code
Requires at least: 2.6
Tested up to: 2.8.4
Stable tag: 1.1.1
Version: 1.1.1

Extract a listing of all commenter emails.

== Description ==

Extract a listing of all commenter emails.

Via the admin page added by the plugin, `Comments -> Commenter Emails`, the admin is presented with the following information:

* A total count of all unique commenters to the blog
* A button to download the entire list of unique commenters' email addresses in CSV (comma-separated values) format
* The entire list of unique commenters' email addresses

The plugin only considers approved comments and does not exclude from its listing any known emails (i.e. admin and post author emails).

== Installation ==

1. Unzip `commenter-emails.zip` inside the `/wp-content/plugins/` directory, or upload `commenter-emails.php` into `/wp-content/plugins/`
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. View the commenter email information reported in the WordPress admin via `Comments -> Commenter Emails`

== Screenshots ==

1. A screenshot of the admin page created by the plugin to view commenter emails.

== Changelog ==

= 1.1.1 =
* Require 'manage_options' permission to access commenter email listing
* Noted dropped support for versions of WP older than 2.6

= 1.1 =
* Fixed unnecessary second call to get_emails()
* Used plugins_url() instead of hardcoded path
* Used $pagenow instead of manual means for checking current page
* Check it's in the admin before doing anything
* CSV filename is now a class variable
* Added c2c logo to top of admin page
* Added extended description
* Removed pre-WP2.6 support
* Noted compatibility with WP 2.6+, 2.7+, and 2.8+

= 1.0 =
* Initial release