# Changelog

## _(in-progress)_
* New: Add README.md file
* New: Add CHANGELOG.md file and move all but most recent changelog entries into it
* New: Add GitHub link to readme.txt
* Change: Initialize plugin on 'plugins_loaded' action instead of on load
* Change: Merge `do_init()` into `init()`
* Change: Prevent object instantiation
    * Add private `__construct()`
    * Add private `__wakeup()`
* Change: Unit tests: Minor whitespace tweaks to bootstrap
* Change: Note compatibility through WP 5.1+
* Change: Rename readme.txt section from 'Filters' to 'Hooks'
* Change: Modify formatting of hook name in readme to prevent being uppercased when shown in the Plugin Directory
* Change: Update copyright date (2019)
* Change: Update License URI to be HTTPS

## 2.4.1 _(2017-02-28)_
* Change: Update unit test bootstrap
    * Default `WP_TESTS_DIR` to `/tmp/wordpress-tests-lib` rather than erroring out if not defined via environment variable
    * Enable more error output for unit tests
* Change: Note compatibility through WP 4.7+
* Change: Change description
* Change: Minor readme.txt content and formatting tweaks
* Change: Update copyright date (2017)
* New: Add LICENSE file

## 2.4 _(2016-01-29)_

### Highlights:

This release introduces the ability to limit listing commenter information for specific posts, as well as many minor behind-the-scenes changes.

## Details:

* Feature: Add ability to list commenter emails for selected posts.
    * Add 'Help' panel to setting page with input field for comma-separated post IDs.
    * Add 'post_ids' arg to `get_emails()`.
    * Handle addition of 'post_ids' arg before existing 'output'.
* Change: Add support for language packs:
    * Change textdomain from 1c2c_ce' to 'commenter-emails'.
    * Don't load plugin translations from file.
    * Remove .pot file and /lang subdirectory.
    * Remove 'Domain Path' from plugin header.
* Change: Move download section below listing and make it a bit more inline.
* Change: Move initialization from `init()` into new `do_init()`, with `init()` hooking `plugins_loaded` to invoke.
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

## 2.3 _(2015-03-17)_
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

## 2.2.1 _(2013-12-30)_
* Note compatibility through WP 3.8+
* Update copyright date (2014)
* Change donate link
* Minor readme.txt tweaks (mostly spacing)
* Add banner
* Update screenshot

## 2.2
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

## 2.1
* Add support for localization
* Add .pot
* Move CSS into commenter-emails.css and enqueue
* Add enqueue_admin_css()
* Store plugin settings page id in private static 'plugin_page'
* Hook `download_csv()` to plugin-specific load action
* Add `version()` to return plugin version
* Minor code reformatting (spacing)
* Note compatibility through WP 3.3+
* Drop support for versions of WP older than 3.1
* Update screenshot (now based on WP 3.3)
* Add 'Domain Path' directive to top of main plugin file
* Add link to plugin directory page to readme.txt
* Update copyright date (2012)

## 2.0
* Fix bug preventing download of .csv file
* Fix bug where filters were applied too late (after download handling)
* Add optional arguments `$fields` and `$output` to `get_emails()`; method now returns array of user objects
* Add filter `c2c_commenter_emails_fields` to allow overriding default fields included in CSV file
* Add filter `c2c_commenter_emails_field_separator` to allow overriding field separator
* Improve csv generation by using `fputcsv()`
* Now also list commenter names in addition to emails on the plugin's settings page
* Change format of download filename, e.g. comment-emails-2011-06-26-1123.csv (where numbers represent date and time of download)
* Note compatibility through WP 3.2+
* Minor code formatting change (spacing)
* Fix plugin homepage and author links in description in readme.txt

## 1.3
* Switch from object instantiation to direct class invocation
* Explicitly declare all functions public static and class variables private static
* Note compatibility through WP 3.1+
* Update copyright date (2011)

## 1.2
* Allow filtering of default filename for saved csv file, via `c2c_commenter_emails_filename` filter
* Allow filtering of whether settings page should actually list the email addresses, via `c2c_commenter_emails_show_csv_button` filter
* Allow filtering of whether settings page should actually show the download csv button, via `c2c_commenter_emails_show_emails` filter
* Move everything in constructor into new `init()` which gets called on admin_init
* Add 'Listing' link to plugin's entry in plugins list that links to plugin's admin page
* Check for `is_admin()` before defining class rather than during constructor
* Assign object instance to global variable, `$c2c_commenter_emails`, to allow for external manipulation
* Rename class from `CommenterEmails` to `c2c_CommenterEmails`
* Remove docs from top of plugin file (all that and more are in readme.txt)
* Note support for WP 2.9+, 3.0+
* Minor code reformatting (spacing)
* Add PHPDoc documentation
* Add package info to top of file
* Add Filters and Upgrade Notice sections to readme.txt
* Update copyright date
* Remove trailing whitespace in header docs

## 1.1.1
* Require `manage_options` permission to access commenter email listing
* Noted dropped support for versions of WP older than 2.6

## 1.1
* Fixed unnecessary second call to `get_emails()`
* Used `plugins_url()` instead of hardcoded path
* Used $pagenow instead of manual means for checking current page
* Check it's in the admin before doing anything
* CSV filename is now a class variable
* Added c2c logo to top of admin page
* Added extended description
* Removed pre-WP2.6 support
* Noted compatibility with WP 2.6+, 2.7+, and 2.8+

## 1.0
* Initial release
