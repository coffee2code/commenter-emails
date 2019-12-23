=== Commenter Emails ===
Contributors: coffee2code
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6ARCFJ9TX3522
Tags: commenter, commenters, email, address, contact, visitor, comment, coffee2code
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 4.1
Tested up to: 5.3
Stable tag: 2.5.1

Extract a listing of data for all commenters (email addresses, names, URLs), and an option to export that data as a CSV file.


== Description ==

Via the admin page added by the plugin, `Comments -> Commenter Emails`, admin users are presented with the following information:

* A total count of all unique commenters to the site
* The entire list of each unique commenters' email addresses, names, and provided website URLs
* A button to download the entire list of unique commenters' email addresses (and, optionally, their website URL) in CSV (comma-separated values) format

The plugin only considers approved comments and does not exclude from its listing any known email addresses (i.e. admin and post author email addresses).

Links: [Plugin Homepage](http://coffee2code.com/wp-plugins/commenter-emails/) | [Plugin Directory Page](https://wordpress.org/plugins/commenter-emails/) | [GitHub](https://github.com/coffee2code/commenter-emails/) | [Author Homepage](http://coffee2code.com)


== Installation ==

1. Install via the built-in WordPress plugin installer. Or download and unzip `commenter-emails.zip` inside the plugins directory for your site (typically `wp-content/plugins/`)
2. Activate the plugin through the 'Plugins' admin menu in WordPress
3. View the commenter email information reported in the WordPress admin via `Comments -> Commenter Emails`


== Screenshots ==

1. A screenshot of the admin page created by the plugin to view commenter emails.
2. A screenshot of the plugin's settings page with the "Help" tab expanded to reveal input for limited the listing of commenters to those who commented on specified posts.


== Hooks ==

The plugin exposes six filters for hooking. Code using these filters should ideally be put into a mu-plugin or site-specific plugin (which is beyond the scope of this readme to explain). Less ideally, you could put them in your active theme's functions.php file.

**c2c_commenter_emails_show_csv_button (filter)**

The 'c2c_commenter_emails_show_csv_button' hook allows you to customize whether the button to download a CSV file of the commenter emails list should be present on the plugin's admin settings page. By default this is true.

Arguments:

* $show_button (bool): Whether the download button is shown; it is 'true' by default.

Example:

`
// Disable the download button
add_filter( 'c2c_commenter_emails_show_csv_button', '__return_false' );
`

**c2c_commenter_emails_show_emails (filter)**

The 'c2c_commenter_emails_show_emails' hook allows you to customize whether the listing of emails should appear on the plugin's admin settings page. By default this is true.

Arguments:

* $show_emails (bool): Whether the listing of emails is shown' it is 'true' by default.

Example:

`
// Disable showing the emails listing
add_filter( 'c2c_commenter_emails_show_emails', '__return_false' );
`

**c2c_commenter_emails_filename (filter)**

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

**manage_commenter_emails_options (filter)**

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

**c2c_commenter_emails_fields (filter)**

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

**c2c_commenter_emails_field_separator (filter)**

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

= 2.5.1 (2019-06-25) =
* Change: Update unit test install script and bootstrap to use latest WP unit test repo
* Change: Note compatibility through WP 5.2+

= 2.5 (2019-04-17) =
* New: Add README.md file
* New: Add CHANGELOG.md file and move all but most recent changelog entries into it
* New: Add inline documentation for hooks
* New: Add GitHub link to readme.txt
* Change: Initialize plugin on 'plugins_loaded' action instead of on load
* Change: Merge `do_init()` into `init()`
* Change: Prevent object instantiation
    * Add private `__construct()`
    * Add private `__wakeup()`
* Change: Cast return values for a number of hooks to boolean or array
* Change: (Hardening) Encode plugin basename before use as part of a URL
* Change: Add missing translation textdomain
* Change: Unit tests: Minor whitespace tweaks to bootstrap
* Change: Note compatibility through WP 5.1+
* Change: Rename readme.txt section from 'Filters' to 'Hooks'
* Change: Modify formatting of hook name in readme to prevent being uppercased when shown in the Plugin Directory
* Change: Update copyright date (2019)
* Change: Update License URI to be HTTPS

= 2.4.1 (2017-02-28) =
* Change: Update unit test bootstrap
    * Default `WP_TESTS_DIR` to `/tmp/wordpress-tests-lib` rather than erroring out if not defined via environment variable
    * Enable more error output for unit tests
* Change: Note compatibility through WP 4.7+
* Change: Change description
* Change: Minor readme.txt content and formatting tweaks
* Change: Update copyright date (2017)
* New: Add LICENSE file

_Full changelog is available in [CHANGELOG.md](https://github.com/coffee2code/commenter-emails/blob/master/CHANGELOG.md)._


== Upgrade Notice ==

= 2.5.1 =
Trivial update: modernized unit tests and noted compatibility through WP 5.2+

= 2.5 =
Minor update: tweaked plugin initialization, noted compatibility through WP 5.1+, created CHANGELOG.md to store historical changelog outside of readme.txt, and updated copyright date (2019)

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
