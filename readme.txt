=== Commenter Emails ===
Contributors: coffee2code
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6ARCFJ9TX3522
Tags: commenter, commenters, email, address, contact, visitor, comment, coffee2code
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 4.1
Tested up to: 4.7
Stable tag: 2.4.1

Extract a listing of data for all commenters (email addresses, names, URLs), and an option to export that data as a CSV file.


== Description ==

Via the admin page added by the plugin, `Comments -> Commenter Emails`, admin users are presented with the following information:

* A total count of all unique commenters to the site
* The entire list of each unique commenters' email addresses, names, and provided website URLs
* A button to download the entire list of unique commenters' email addresses (and, optionally, their website URL) in CSV (comma-separated values) format

The plugin only considers approved comments and does not exclude from its listing any known email addresses (i.e. admin and post author email addresses).

Links: [Plugin Homepage](http://coffee2code.com/wp-plugins/commenter-emails/) | [Plugin Directory Page](https://wordpress.org/plugins/commenter-emails/) | [Author Homepage](http://coffee2code.com)


== Installation ==

1. Install via the built-in WordPress plugin installer. Or download and unzip `commenter-emails.zip` inside the plugins directory for your site (typically `wp-content/plugins/`)
2. Activate the plugin through the 'Plugins' admin menu in WordPress
3. View the commenter email information reported in the WordPress admin via `Comments -> Commenter Emails`


== Screenshots ==

1. A screenshot of the admin page created by the plugin to view commenter emails.
2. A screenshot of the plugin's settings page with the "Help" tab expanded to reveal input for limited the listing of commenters to those who commented on specified posts.


== Filters ==

The plugin exposes six filters for hooking. Code using these filters should ideally be put into a mu-plugin or site-specific plugin (which is beyond the scope of this readme to explain). Less ideally, you could put them in your active theme's functions.php file.

= c2c_commenter_emails_show_csv_button (filter) =

The 'c2c_commenter_emails_show_csv_button' hook allows you to customize whether the button to download a CSV file of the commenter emails list should be present on the plugin's admin settings page. By default this is true.

Arguments:

* $show_button (bool): Whether the download button is shown; it is 'true' by default.

Example:

`
// Disable the download button
add_filter( 'c2c_commenter_emails_show_csv_button', '__return_false' );
`

= c2c_commenter_emails_show_emails (filter) =

The 'c2c_commenter_emails_show_emails' hook allows you to customize whether the listing of emails should appear on the plugin's admin settings page. By default this is true.

Arguments:

* $show_emails (bool): Whether the listing of emails is shown' it is 'true' by default.

Example:

`
// Disable showing the emails listing
add_filter( 'c2c_commenter_emails_show_emails', '__return_false' );
`

= c2c_commenter_emails_filename (filter) =

The 'c2c_commenter_emails_filename' hook allows you to customize the name used for the .csv file when being downloaded. By default this is 'commenter-emails.csv'.

Arguments:

* $filename (string): The filename. By default this is 'commenter-emails.csv'.

Example:

`
/**
 * Change the default filename to embed today's date for the Commenter Emails plugin.
 *
 * @param string $filename The filename for the CSV file.
 * @return string.
 */
function change_ce_filename( $filename ) {
	$date = date('m-d-Y', strtotime('today')); // Get today's date in m-d-Y format (i.e. 02-25-2010)
	return "emails-$date.csv";
}
add_filter( 'c2c_commenter_emails_filename', 'change_ce_filename' );
`

= manage_commenter_emails_options (filter) =

The 'manage_commenter_emails_options' hook allows you to customize the capability required to access the commenter emails admin page. You should be certain that you've created the capability and assigned that capability to the desired user(s). By default this is the 'manage_options' capability.

Arguments:

* $options (string): Capability name. By default this is the 'manage_options' capability.

Example:

`
/**
 * Change the capability needed to see the Commenter Emails admin page for the Commenter Emails plugin.
 *
 * @param string $capability The necessary capability.
 * @return string
 */
function change_ce_cap( $capability ) {
	return 'manage_commenter_emails';
}
add_filter( 'manage_commenter_emails_options', 'change_ce_cap' );
`

= c2c_commenter_emails_fields (filter) =

The 'c2c_commenter_emails_fields' hook allows you to customize the user fields included in the download CSV file. By default the CSV file includes comment_author and comment_author_email.

Arguments:

* $fields (array): Array of field names. Items must correspond to columns in the comments table. By default this is `array( 'comment_author', 'comment_author_email' )`. Whether explicitly included or not, 'comment_author_email' will always be output in the CSV.

Example:

`
/**
 * Include the commenter's IP address in the download CSV for the Commenter Emails plugin.
 *
 * @param array $fields The comment email fields to include in the CSV output.
 * @return array
 */
function change_ce_fields( $fields ) {
	$fields[] = 'comment_author_IP';
	return $fields;
}
add_filter( 'c2c_commenter_emails_fields', 'change_ce_fields' );
`

= c2c_commenter_emails_field_separator (filter) =

The 'c2c_commenter_emails_field_separator' hook allows you to customize the separator used in the CSV file.

Arguments:

* $separator (string): String to be used as the data separator in the CSV file. Default is ','.

Example:

`
/**
 * Change the data fields separator to '|' for Commenter Emails plugin.
 *
 * @param string $separator The defautl separator.
 * @return string
 */
function change_ce_field_separator( $separator ) {
	return '|';
}
add_filter( 'c2c_commenter_emails_field_separator', 'change_ce_field_separator' );
`


== Changelog ==

= 2.4.1 (2017-02-28) =
* Change: Update unit test bootstrap
    * Default `WP_TESTS_DIR` to `/tmp/wordpress-tests-lib` rather than erroring out if not defined via environment variable
    * Enable more error output for unit tests
* Change: Note compatibility through WP 4.7+
* Change: Change description
* Change: Minor readme.txt content and formatting tweaks
* Change: Update copyright date (2017)
* New: Add LICENSE file

= 2.4 (2016-01-29) =
Highlights:
* This release introduces the ability to limit listing commenter information for specific posts, as well as many minor behind-the-scenes changes.

Details:
* Feature: Add ability to list commenter emails for selected posts.
    * Add 'Help' panel to setting page with input field for comma-separated post IDs.
    * Add 'post_ids' arg to `get_emails()`.
    * Handle addition of 'post_ids' arg before existing 'output'.
* Change: Add support for language packs:
    * Change textdomain from 'c2c_ce' to 'commenter-emails'.
    * Don't load plugin translations from file.
    * Remove .pot file and /lang subdirectory.
    * Remove 'Domain Path' from plugin header.
* Change: Move download section below listing and make it a bit more inline.
* Change: Move initialization from `init()` into new `do_init()`, with `init()` hooking 'plugins_loaded' to invoke.
* Change: Rename existing `do_init()` to `admin_menu()`.
* Change: Rename existing `admin_menu()` to `register_admin_menu()`, which is now called from `admin_menu()` instead of a hook callback.
* New: Introduce `get_post_ids()`, `help_tabs()`.
* Change: Add padding to table cells.
* Change: Make default value for 'fields' arg of `get_emails()` an empty array since function already enforces default value.
* Change: Change admin page header from 'h2' to 'h1' tag.
* Change: Minor improvements to inline docs and test docs.
* New: Create empty index.php to prevent files from being listed if web server has enabled directory listings.
* Change: Update screenshots.
* Change: Note compatibility through WP 4.4+.
* Change: Remove support for WordPress older than 4.1.
* Change: Update copyright date (2016).

= 2.3 (2015-03-17) =
* Ensure only valid comment fields can be specified as first argument to `get_emails()`
* Use the default argument value of 'fields' argument if it is explicitly set as empty
* Remove `is_admin()` check preventing class from being defined (to facilitate unit testing and general use by other code)
* Add unit tests
* Minor code reformatting (spacing, bracing)
* Note compatibility through WP 4.1+
* Change documentation links to w.org to be https
* Update copyright date (2015)
* Add plugin icon
* Regenerate .pot

= 2.2.1 (2013-12-30) =
* Note compatibility through WP 3.8+
* Update copyright date (2014)
* Change donate link
* Minor readme.txt tweaks (mostly spacing)
* Add banner
* Update screenshot

= 2.2 =
* Show comment author URLs in listing
* Add checkbox to allow inclusion of comment author URLs in CSV
* Add check to prevent execution of code if file is directly accessed
* Re-license as GPLv2 or later (from X11)
* Add 'License' and 'License URI' header tags to readme.txt and plugin file
* Remove ending PHP close tag
* Minor documentation tweaks
* Note compatibility through WP 3.5+
* Update copyright date (2013)
* Move screenshot into repo's assets directory

= 2.1 =
* Add support for localization
* Add .pot
* Move CSS into commenter-emails.css and enqueue
* Add enqueue_admin_css()
* Store plugin settings page id in private static 'plugin_page'
* Hook download_csv() to plugin-specific load action
* Add version() to return plugin version
* Minor code reformatting (spacing)
* Note compatibility through WP 3.3+
* Drop support for versions of WP older than 3.1
* Update screenshot (now based on WP 3.3)
* Add 'Domain Path' directive to top of main plugin file
* Add link to plugin directory page to readme.txt
* Update copyright date (2012)

= 2.0 =
* Fix bug preventing download of .csv file
* Fix bug where filters were applied too late (after download handling)
* Add optional arguments $fields and $output to get_emails(); method now returns array of user objects
* Add filter 'c2c_commenter_emails_fields' to allow overriding default fields included in CSV file
* Add filter 'c2c_commenter_emails_field_separator' to allow overriding field separator
* Improve csv generation by using fputcsv()
* Now also list commenter names in addition to emails on the plugin's settings page
* Change format of download filename, e.g. comment-emails-2011-06-26-1123.csv (where numbers represent date and time of download)
* Note compatibility through WP 3.2+
* Minor code formatting change (spacing)
* Fix plugin homepage and author links in description in readme.txt

= 1.3 =
* Switch from object instantiation to direct class invocation
* Explicitly declare all functions public static and class variables private static
* Note compatibility through WP 3.1+
* Update copyright date (2011)

= 1.2 =
* Allow filtering of default filename for saved csv file, via 'c2c_commenter_emails_filename' filter
* Allow filtering of whether settings page should actually list the email addresses, via 'c2c_commenter_emails_show_csv_button' filter
* Allow filtering of whether settings page should actually show the download csv button, via 'c2c_commenter_emails_show_emails' filter
* Move everything in constructor into new init() which gets called on admin_init
* Add 'Listing' link to plugin's entry in plugins list that links to plugin's admin page
* Check for is_admin() before defining class rather than during constructor
* Assign object instance to global variable, $c2c_commenter_emails, to allow for external manipulation
* Rename class from 'CommenterEmails' to 'c2c_CommenterEmails'
* Remove docs from top of plugin file (all that and more are in readme.txt)
* Note support for WP 2.9+, 3.0+
* Minor code reformatting (spacing)
* Add PHPDoc documentation
* Add package info to top of file
* Add Filters and Upgrade Notice sections to readme.txt
* Update copyright date
* Remove trailing whitespace in header docs

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


== Upgrade Notice ==

= 2.4.1 =
Trivial update: noted compatibility through WP 4.7+, updated copyright date (2017), minor unit test adjustments

= 2.4 =
Feature update: added ability to list commenter info for only select posts; improved support for localization; verified compatibility through WP 4.4; removed compatibility with WP earlier than 4.1; updated copyright date (2016)

= 2.3 =
Recommended update: code hardening; added unit tests; noted compatibility with WP 4.1+; added plugin icon

= 2.2.1 =
Trivial update: noted compatibility with WP 3.8+

= 2.2 =
Minor feature update: added commenter URLs to listing and optionally to downloaded CSV; noted compatibility through WP 3.5+; explicitly stated license

= 2.1 =
Recommended update: added support for localization; enqueue CSS; updated screenshot; compatibility is now WP 3.1-3.3+.

= 2.0 =
Recommended update: fixed critical functional bugs; list commenter names alongside email; changed download filename format; added filters; noted compatibility through WP 3.2+; and more.

= 1.3 =
Minor update: slight implementation modification; updated copyright date; other minor code changes.

= 1.2 =
Minor update. Highlights: added multiple hooks to facilitate customization; added plugin listing link; renamed class; verified WP 3.0 compatibility; misc. non-functionality changes.
