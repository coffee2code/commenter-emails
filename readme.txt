=== Commenter Emails ===
Contributors: coffee2code
Donate link: http://coffee2code.com/donate
Tags: commenter, commenters, email, address, contact, visitor, comment, coffee2code
Requires at least: 3.1
Tested up to: 3.3
Stable tag: 2.1
Version: 2.1

Extract a listing of all commenter emails.


== Description ==

Extract a listing of all commenter emails.

Via the admin page added by the plugin, `Comments -> Commenter Emails`, the admin is presented with the following information:

* A total count of all unique commenters to the blog
* A button to download the entire list of unique commenters' email addresses in CSV (comma-separated values) format
* The entire list of unique commenters' email addresses and names

The plugin only considers approved comments and does not exclude from its listing any known emails (i.e. admin and post author emails).

Links: [Plugin Homepage](http://coffee2code.com/wp-plugins/commenter-emails/) | [Plugin Directory Page](http://wordpress.org/extend/plugins/commenter-emails/) | [Author Homepage](http://coffee2code.com)


== Installation ==

1. Unzip `commenter-emails.zip` inside the `/wp-content/plugins/` directory (or install via the built-in WordPress plugin installer)
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. View the commenter email information reported in the WordPress admin via `Comments -> Commenter Emails`


== Screenshots ==

1. A screenshot of the admin page created by the plugin to view commenter emails.


== Filters ==

The plugin exposes six filters for hooking.  Typically, customizations utilizing these hooks would be put into your active theme's functions.php file, or used by another plugin.

= c2c_commenter_emails_show_csv_button (filter) =

The 'c2c_commenter_emails_show_csv_button' hook allows you to customize whether the button to download a CSV file of the commenter emails list should be present on the plugin's admin settings page.  By default this is true.

Arguments:

* $show_button (bool): Whether the download button is shown; it is 'true' by default.

Example:

`
// Disable the download button
add_filter( 'c2c_commenter_emails_show_csv_button', '__return_false' );
`

= c2c_commenter_emails_show_emails (filter) =

The 'c2c_commenter_emails_show_emails' hook allows you to customize whether the listing of emails should appear on the plugin's admin settings page.  By default this is true.

Arguments:

* $show_emails (bool): Whether the listing of emails is shown' it is 'true' by default.

Example:

`
// Disable showing the emails listing
add_filter( 'c2c_commenter_emails_show_emails', '__return_false' );
`

= c2c_commenter_emails_filename (filter) =

The 'c2c_commenter_emails_filename' hook allows you to customize the name used for the .csv file when being downloaded.  By default this is 'commenter-emails.csv'.

Arguments:

* $filename (string): The filename.  By default this is 'commenter-emails.csv'.

Example:

`
// Change the default filename to embed today's date.
add_filter( 'c2c_commenter_emails_filename', 'change_ce_filename' );
function change_ce_filename( $filename ) {
	$date = date('m-d-Y', strtotime('today')); // Get today's date in m-d-Y format (i.e. 02-25-2010)
	return "emails-$date.csv";
}
`

= manage_commenter_emails_options (filter) =

The 'manage_commenter_emails_options' hook allows you to customize the capability required to access the commenter emails admin page.  You should be certain that you've created the capability and assigned that capability to the desired user(s).  By default this is the 'manage_options' capability.

Arguments:

* $options (string): Capability name.  By default this is the 'manage_options' capability.

Example:

`
// Change the capability needed to see the Commenter Emails admin page
add_filter( 'manage_commenter_emails_options', 'change_ce_cap' );
function change_ce_cap( $capability ) {
	return 'manage_commenter_emails';
}
`

= c2c_commenter_emails_fields (filter) =

The 'c2c_commenter_emails_fields' hook allows you to customize the user fields included in the download CSV file.  By default the CSV file includes comment_author and comment_author_email.

Arguments:

* $fields (array): Array of field names. Items must correspond to columns in the comments table. By default this is `array( 'comment_author', 'comment_author_email' )`. Whether explicitly included or not, 'comment_author_email' will always be output in the CSV.

Example:

`
// Include the commenter's URL address in the download CSV
add_filter( 'c2c_commenter_emails_fields', 'change_ce_fields' );
function change_ce_fields( $fields ) {
	$fields[] = 'comment_author_url';
	return $fields;
}
`

= c2c_commenter_emails_field_separator (filter) =

The 'c2c_commenter_emails_field_separator' hook allows you to customize the separator used in the CSV file.

Arguments:

* $separator (string): String to be used as the data separator in the CSV file. Default is ','.

Example:

`
// Change the data fields separator to '|'
add_filter( 'c2c_commenter_emails_field_separator', 'change_ce_field_separator' );
function change_ce_field_separator( $separator ) {
	return '|';
}
`


== Changelog ==

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

= 2.1 =
Recommended update: added support for localization; enqueue CSS; updated screenshot; compatibility is now WP 3.1-3.3+.

= 2.0 =
Recommended update: fixed critical functional bugs; list commenter names alongside email; changed download filename format; added filters; noted compatibility through WP 3.2+; and more.

= 1.3 =
Minor update: slight implementation modification; updated copyright date; other minor code changes.

= 1.2 =
Minor update. Highlights: added multiple hooks to facilitate customization; added plugin listing link; renamed class; verified WP 3.0 compatibility; misc. non-functionality changes.