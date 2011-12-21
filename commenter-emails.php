<?php
/**
 * @package Commenter_Emails
 * @author Scott Reilly
 * @version 2.1
 */
/*
Plugin Name: Commenter Emails
Version: 2.1
Plugin URI: http://coffee2code.com/wp-plugins/commenter-emails/
Author: Scott Reilly
Author URI: http://coffee2code.com
Text Domain: commenter-emails
Domain Path: /lang/
Description: Extract a listing of all commenter emails.

Compatible with WordPress 3.1+, 3.2+, 3.3+.

=>> Read the accompanying readme.txt file for instructions and documentation.
=>> Also, visit the plugin's homepage for additional information and updates.
=>> Or visit: http://wordpress.org/extend/plugins/commenter-emails/

TODO:
	* Handle large number of commenters (page listing?)
*/

/*
Copyright (c) 2007-2012 by Scott Reilly (aka coffee2code)

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy,
modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR
IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

if ( is_admin() && ! class_exists( 'c2c_CommenterEmails' ) ) :

class c2c_CommenterEmails {
	private static $show_csv_button = ''; // Setting to determine if the plugin's admin page should show the CSV button
	private static $show_emails     = ''; // Setting to determine if the plugin's admin page should show the list of emails
	private static $csv_filename    = '';
	private static $plugin_basename = '';
	private static $plugin_page     = '';

	/**
	 * Returns version of the plugin.
	 *
	 * @since 2.1
	 */
	public static function version() {
		return '2.1';
	}

	/**
	 * Constructor
	 */
	public static function init() {
		self::$plugin_basename = plugin_basename( __FILE__ );

		load_plugin_textdomain( 'c2c_ce', false, basename( dirname( __FILE__ ) ) . DIRECTORY_SEPARATOR . 'lang' );

		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_menu', array( __CLASS__, 'do_init' ), 11 );
	}

	/**
	 * Initialize hooks and data
	 */
	public static function do_init() {
		self::$show_csv_button = apply_filters( 'c2c_commenter_emails_show_csv_button', true );
		self::$show_emails     = apply_filters( 'c2c_commenter_emails_show_emails',     true );
		self::$csv_filename    = apply_filters( 'c2c_commenter_emails_filename',        'commenter-emails-' .
			mysql2date( 'Y-m-d-Hi', current_time( 'mysql' ) ) . '.csv' );

		if ( ! empty( self::$plugin_page ) ) {
			// Handles CSV download
			add_action( 'load-' . self::$plugin_page, array( __CLASS__, 'handle_csv_download' ) );
			// Register and enqueue styles for admin page
			add_action( 'load-' . self::$plugin_page, array( __CLASS__, 'enqueue_admin_css' ) );
		}
	}

	/**
	 * Query database to obtain the list of commenter email addresses.
	 * Only checks comments that are approved, have a author email, and are
	 * of the comment_type 'comment' (or '').
	 *
	 * Only one entry is returned per email address.  If a given email address
	 * has multiple instances in the database, each with different names, then
	 * the most recent comment will be used to obtain any additional field data
	 * such as comment_author, etc.
	 *
	 * @param array $fields
	 * @param string $output (optional) Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants. See WP docs for wpdb::get_results() for more info
	 * @return mixed List of email addresses
	 */
	public static function get_emails( $fields = array( 'comment_author_email', 'comment_author' ), $output = ARRAY_N ) {
		global $wpdb;

		// comment_author_email must be one of the fields
		if ( ! in_array( 'comment_author_email', $fields ) )
			array_unshift( $fields,  'comment_author_email' );

		$fields = implode( ', ', $fields );
		$sql = "SELECT $fields
				FROM {$wpdb->comments} t1
				INNER JOIN ( SELECT MAX(comment_ID) AS id FROM {$wpdb->comments} GROUP BY comment_author_email ) t2 ON t1.comment_ID = t2.id
				WHERE
					comment_approved = '1' AND
					comment_author_email != '' AND
					(comment_type = '' OR comment_type = 'comment')
				GROUP BY comment_author_email
				ORDER BY comment_author_email ASC";
		$emails = $wpdb->get_results( $sql, $output );
		return $emails;
	}

	/**
	 * Handler to download commenter emails directly as CSV file.
	 *
	 * @return void (Text is streamed to file to user)
	 */
	public static function handle_csv_download() {
		if ( isset( $_GET['download_csv'] ) && $_GET['download_csv'] == '1' ) {
			header( 'Content-type: text/csv' );
			header( 'Cache-Control: no-store, no-cache' );
			header( 'Content-Disposition: attachment; filename="' . self::$csv_filename . '"' );

			$outstream = fopen( "php://output", 'w' );

			$fields    = apply_filters( 'c2c_commenter_emails_fields', array( 'comment_author', 'comment_author_email' ) );
			$field_sep = apply_filters( 'c2c_commenter_emails_field_separator', ',' );

			foreach ( (array) self::get_emails( $fields ) as $item )
				fputcsv( $outstream, $item, $field_sep, '"' );

			fclose( $outstream );

			exit();
		}
	}

	/**
	 * Creates the admin menu.
	 *
	 * @return void
	 */
	public static function admin_menu() {
		add_filter( 'plugin_action_links_' . self::$plugin_basename, array( __CLASS__, 'plugin_action_links' ) );
		// Add menu under Comments
		self::$plugin_page = add_comments_page( __( 'Commenter Emails', 'c2c_ce' ), __( 'Commenter Emails', 'c2c_ce' ),
			apply_filters( 'manage_commenter_emails_options', 'manage_options' ), self::$plugin_basename, array( __CLASS__, 'admin_page' ) );
	}

	/**
	 * Enqueues stylesheets.
	 *
	 * @since 2.1
	 */
	public static function enqueue_admin_css() {
		wp_register_style( __CLASS__, plugins_url( 'commenter-emails.css', __FILE__ ) );
		wp_enqueue_style( __CLASS__ );
	}

	/**
	 * Adds a 'Settings' link to the plugin action links.
	 *
	 * @param array $action_links The current action links
	 * @return array The action links
	 */
	public static function plugin_action_links( $action_links ) {
		$settings_link = '<a href="edit-comments.php?page=' . self::$plugin_basename.'" title="">' . __( 'Listing', 'c2c_ce' ) . '</a>';
		array_unshift( $action_links, $settings_link );
		return $action_links;
	}

	/**
	 * Outputs the contents of the plugin's admin page.
	 *
	 * @return void
	 */
	public static function admin_page() {
		$emails = self::get_emails();
		$emails_count = count( $emails );
		$logo = plugins_url( 'c2c_minilogo.png', __FILE__ );

		echo '<div class="wrap">';
		echo '<div class="icon32" style="width:44px;"><img src="' . $logo . '" alt="' . __( 'A plugin by coffee2code', 'c2c_ce' ) . '"/><br /></div>';
		echo '<h2>' . __( 'Commenter Emails', 'c2c_ce' ) . '</h2>';
		echo '<p>' . sprintf( __( 'There are %s unique and approved commenter email addresses for this site.', 'c2c_ce' ), $emails_count ) . '</p>';
		echo '</div>';

		if ( self::$show_csv_button ) {
			echo '<div class="wrap">';
			echo '<h2>' . __( 'Download', 'c2c_ce' ) . '</h2>';
			echo '<p><form action="" method="get">';
			echo '<label for="submit">';
			_e( 'Download the email addresses as a CSV file :', 'c2c_ce' );
			echo '<input type="submit" name="submit" value="' . esc_attr( __( 'Download', 'c2c_ce' ) ) . '" />';
			echo '</label>';
			echo '<input type="hidden" name="page" value="' . esc_attr( $_GET['page'] ) . '" />';
			echo '<input type="hidden" name="download_csv" value="1" />';
			echo '</form></p></div>';
		}

		if ( self::$show_emails ) {
			echo '<div class="wrap">';
			echo '<h2>' . __( 'All Commenter Emails', 'c2c_ce' ) . '</h2>';
			echo '<table>';
			echo '<tr><th>' . __( 'Email', 'c2c_ce' ) . '</th><th>' . __( 'Name', 'c2c_ce' ) . '</th></tr>';

			foreach ( $emails as $item )
				echo '<tr><td>' . esc_html( $item[0] ) . '</td><td>' . esc_html( $item[1] ) . '</td></tr>';

			echo '</table>';
			echo '<p>' . sprintf( __( '%s commenter emails listed.', 'c2c_ce' ), $emails_count ) . '</p>';
			echo '</div>';
		}

		echo '<div id="c2c-ce" class="wrap"><div>';
		_e( 'This plugin brought to you by <a href="http://coffee2code.com" title="coffee2code.com">Scott Reilly, aka coffee2code</a>.', 'c2c_ce' );
		echo '<span><a href="http://coffee2code.com/donate" title="' . esc_attr( __( 'Please consider a donation', 'c2c_ce' ) ) . '">';
		_e( 'Did you find this plugin useful?', 'c2c_ce' );
		echo '</a></span></div></div>';
	}
} // end c2c_CommenterEmails

c2c_CommenterEmails::init();

endif; // end if ! class_exists()

?>