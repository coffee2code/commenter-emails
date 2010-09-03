=== Commenter Emails ===
Contributors: coffee2code
Donate link: http://coffee2code.com/donate
Tags: commenter, commenters, email, address, contact, visitor, comment, coffee2code
Requires at least: 2.6
Tested up to: 3.0.1
Stable tag: 1.2
Version: 1.2

Extract a listing of all commenter emails.


== Description ==

Extract a listing of all commenter emails.

Via the admin page added by the plugin, `Comments -> Commenter Emails`, the admin is presented with the following information:

* A total count of all unique commenters to the blog
* A button to download the entire list of unique commenters' email addresses in CSV (comma-separated values) format
* The entire list of unique commenters' email addresses

The plugin only considers approved comments and does not exclude from its listing any known emails (i.e. admin and post author emails).


== Installation ==

1. Unzip `commenter-emails.zip` inside the `/wp-content/plugins/` directory (or install via the built-in WordPress plugin installer)
1. Activate the plugin through the 'Plugins' admin menu in WordPress
1. View the commenter email information reported in the WordPress admin via `Comments -> Commenter Emails`


== Screenshots ==

1. A screenshot of the admin page created by the plugin to view commenter emails.


== Filters ==

The plugin exposes four filters for hooking.  Typically code making use of these hooks are put into the active theme's functions.php file.

= c2c_commenter_emails_show_csv_button (filter) =

The 'c2c_commenter_emails_show_csv_button' hook allows you to customize whether the button to download a CSV file of the commenter emails list should be present on the plugin's admin settings page.  By default this is true.

Arguments:

* $show_button (bool): Whether the download button is shown; it is 'true' by default.

Example:

`
// Disable the download button
add_filter( 'c2c_commenter_emails_show_csv_button', '__return_false' );
`

= c2c_commenter_emails_show_emails (action) =

The 'c2c_commenter_emails_show_emails' hook allows you to customize whether the listing of emails should appear on the plugin's admin settings page.  By default this is true.

Arguments:

* $show_emails (bool): Whether the listing of emails is shown' it is 'true' by default.

Example:

`
// Disable showing the emails listing
add_filter( 'c2c_commenter_emails_show_emails', '__return_false' );
`

= c2c_commenter_emails_filename (action) =

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

= manage_commenter_emails_options (action) =

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


== Changelog ==

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

= 3.0 =
Minor update. Highlights: added multiple hooks to facilitate customization; added plugin listing link; renamed class; verified WP 3.0 compatibility; misc. non-functionality changes.