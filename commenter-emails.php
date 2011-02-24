<?php
/**
 * @package Commenter_Emails
 * @author Scott Reilly
 * @version 1.3
 */
/*
Plugin Name: Commenter Emails
Version: 1.3
Plugin URI: http://coffee2code.com/wp-plugins/commenter-emails/
Author: Scott Reilly
Author URI: http://coffee2code.com
Description: Extract a listing of all commenter emails.

Compatible with WordPress 2.6+, 2.7+, 2.8+, 2.9+, 3.0+, 3.1+.

=>> Read the accompanying readme.txt file for instructions and documentation.
=>> Also, visit the plugin's homepage for additional information and updates.
=>> Or visit: http://wordpress.org/extend/plugins/commenter-emails/

*/

/*
Copyright (c) 2007-2011 by Scott Reilly (aka coffee2code)

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

if ( is_admin() && !class_exists( 'c2c_CommenterEmails' ) ) :

class c2c_CommenterEmails {
	private static $show_csv_button = '';	// Setting to determine if the plugin's admin page should show the CSV button
	private static $show_emails = '';		// Setting to determine if the plugin's admin page should show the list of emails
	private static $csv_filename = '';
	private static $plugin_basename = '';

	/**
	 * Constructor
	 */
	public static function init() {
		self::$plugin_basename = plugin_basename( __FILE__ );
		add_action( 'init', array( __CLASS__, 'do_init' ) );
	}

	/**
	 * Initialize hooks and data
	 */
	public static function do_init() {
		self::handle_csv_download();
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );

		self::$show_csv_button = apply_filters( 'c2c_commenter_emails_show_csv_button', true );
		self::$show_emails     = apply_filters( 'c2c_commenter_emails_show_emails', true );
		self::$csv_filename    = apply_filters( 'c2c_commenter_emails_filename', 'commenter-emails.csv' );
	}

	/**
	 * Query database to obtain the list of commenter email addresses.
	 * Only checks comments that are approved, have a author email, and are of the comment_type 'comment' (or '').
	 *
	 * @return array List of email addresses
	 */
	public static function get_emails() {
		global $wpdb;
		$sql = "SELECT DISTINCT comment_author_email
				FROM {$wpdb->comments}
				WHERE
					comment_approved = '1' AND
					comment_author_email != '' AND
					(comment_type = '' OR comment_type = 'comment')
				ORDER BY comment_author_email ASC";
		$emails = $wpdb->get_col( $sql );
		return $emails;
	}

	/**
	 * Handler to download commenter emails directly as CSV file.
	 *
	 * @return void (Text is streamed to file to user)
	 */
	public static function handle_csv_download() {
		global $pagenow;
		if ( ( 'edit-comments.php' == $pagenow ) &&
			isset( $_GET['page'] ) && ( $_GET['page'] == basename( __FILE__ ) ) &&
			isset( $_GET['download_csv'] ) && ( $_GET['download_csv'] == '1' )
		   ) {
			header( 'Content-type: text/csv' );
			header( 'Content-Disposition: attachment; filename="' . self::$csv_filename . '"' );
			echo implode( ',', self::get_emails() );
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
		add_comments_page( 'Commenter Emails', 'Commenter Emails',
			apply_filters( 'manage_commenter_emails_options', 'manage_options' ), self::$plugin_basename, array( __CLASS__, 'admin_page' ) );
	}

	/**
	 * Adds a 'Settings' link to the plugin action links.
	 *
	 * @param array $action_links The current action links
	 * @return array The action links
	 */
	public static function plugin_action_links( $action_links ) {
		$settings_link = '<a href="edit-comments.php?page=' . self::$plugin_basename.'">' . __( 'Listing' ) . '</a>';
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
		$emails = implode( '<br />', $emails );
		$logo = plugins_url( 'c2c_minilogo.png', __FILE__ );

		echo <<<HTML
		<div class='wrap'>
			<div class='icon32' style='width:44px;'><img src='{$logo}' alt='A plugin by coffee2code' /><br /></div>
			<h2>Commenter Emails</h2>
			<p>There are $emails_count unique commenter email addresses for this blog.</p>
		</div>

HTML;

		if ( self::$show_csv_button ) {
		echo <<<HTML
		<div class='wrap'>
			<h2>Download</h2>
			<p><form action="" method="get">
				<label for="submit">Download the email addresses as a CSV file :
					<input type="submit" name="submit" value="Download" />
				</label>
				<input type="hidden" name="page" value="{$_GET['page']}">
				<input type="hidden" name="download_csv" value="1" />
			</form></p>
		</div>

HTML;
		}

		if ( self::$show_emails ) {
		echo <<<HTML
		<div class='wrap'>
			<h2>All Commenter Emails</h2>
			<p>
				$emails
			</p>
			<p>$emails_count commenter emails listed.</p>
		</div>

HTML;
		}
		echo <<<END
		<style type="text/css">
			#c2c {
				text-align:center;
				color:#888;
				background-color:#ffffef;
				padding:5px 0 0;
				margin-top:12px;
				border-style:solid;
				border-color:#dadada;
				border-width:1px 0;
			}
			#c2c div {
				margin:0 auto;
				padding:5px 40px 0 0;
				width:45%;
				min-height:40px;
				background:url('$logo') no-repeat top right;
			}
			#c2c span {
				display:block;
				font-size:x-small;
			}
		</style>
		<div id='c2c' class='wrap'>
			<div>
			This plugin brought to you by <a href="http://coffee2code.com" title="coffee2code.com">Scott Reilly, aka coffee2code</a>.
			<span><a href="http://coffee2code.com/donate" title="Please consider a donation">Did you find this plugin useful?</a></span>
			</div>
		</div>

END;
	}
} // end c2c_CommenterEmails

c2c_CommenterEmails::init();

endif; // end if !class_exists()

?>