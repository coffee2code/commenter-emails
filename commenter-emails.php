<?php
/**
 * Plugin Name: Commenter Emails
 * Version:     2.4.1
 * Plugin URI:  http://coffee2code.com/wp-plugins/commenter-emails/
 * Author:      Scott Reilly
 * Author URI:  http://coffee2code.com/
 * Text Domain: commenter-emails
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Description: Extract a listing of data for all commenters (email addresses, names, URLs), and an option to export that data as a CSV file.
 *
 * Compatible with WordPress 4.1+ through 4.7+.
 *
 * =>> Read the accompanying readme.txt file for instructions and documentation.
 * =>> Also, visit the plugin's homepage for additional information and updates.
 * =>> Or visit: http://wordpress.org/plugins/commenter-emails/
 *
 * @package Commenter_Emails
 * @author  Scott Reilly
 * @version 2.4.1
 */

/*
 * TODO:
 * - Handle large number of commenters (page listing?)
 * - Move settings page export setting from Help panel to Screen Options panel
 */

/*
	Copyright (c) 2007-2017 by Scott Reilly (aka coffee2code)

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

defined( 'ABSPATH' ) or die();

if ( ! class_exists( 'c2c_CommenterEmails' ) ) :

class c2c_CommenterEmails {
	/**
	 * Setting to determine if the plugin's admin page should show the CSV button.
	 *
	 * @var string
	 * @access private
	 */
	private static $show_csv_button = '';

	/**
	 * Setting to determine if the plugin's admin page should show the list of emails.
	 *
	 * @var string
	 * @access private
	 */
	private static $show_emails     = '';

	/**
	 * CSV filename.
	 *
	 * @var string
	 * @access private
	 */
	private static $csv_filename    = '';

	/**
	 * Stored value of plugin basename.
	 *
	 * @var string
	 * @access private
	 */
	private static $plugin_basename = '';

	/**
	 * Stored value of plugin page.
	 *
	 * @var string
	 * @access private
	 */
	private static $plugin_page     = '';

	/**
	 * Returns version of the plugin.
	 *
	 * @since 2.1
	 */
	public static function version() {
		return '2.4.1';
	}

	/**
	 * Constructor.
	 */
	public static function init() {
		add_action( 'plugins_loaded', array( __CLASS__, 'do_init' ) );
	}

	/**
	 * Initializes plugin.
	 *
	 * @since 2.4
	 */
	public static function do_init() {
		self::$plugin_basename = plugin_basename( __FILE__ );

		// Load textdomain.
		load_plugin_textdomain( 'commenter-emails' );

		// Register hooks.
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ), 11 );
	}

	/**
	 * Initialize hooks and data.
	 */
	public static function admin_menu() {
		self::register_admin_menu();

		self::$show_csv_button = apply_filters( 'c2c_commenter_emails_show_csv_button', true );
		self::$show_emails     = apply_filters( 'c2c_commenter_emails_show_emails',     true );
		self::$csv_filename    = apply_filters( 'c2c_commenter_emails_filename',        'commenter-emails-' .
								 mysql2date( 'Y-m-d-Hi', current_time( 'mysql' ) ) . '.csv' );

		if ( self::$plugin_page ) {
			// Handles CSV download.
			add_action( 'load-' . self::$plugin_page, array( __CLASS__, 'handle_csv_download' ) );
			// Register and enqueue styles for admin page.
			add_action( 'load-' . self::$plugin_page, array( __CLASS__, 'enqueue_admin_css' ) );
			// Register help tabs.
			add_action( 'load-' . self::$plugin_page, array( __CLASS__, 'help_tabs' ) );
		}
	}

	/**
	 * Query database to obtain the list of commenter email addresses.
	 *
	 * Only checks comments that are approved, have a author email, and are
	 * of the comment_type 'comment' (or '').
	 *
	 * Only one entry is returned per email address.  If a given email address
	 * has multiple instances in the database, each with different names, then
	 * the most recent comment will be used to obtain any additional field data
	 * such as comment_author, etc.
	 *
	 * @param array  $fields   Optional. The fields to obtain from each comment.
	 * @param array  $post_ids Optional. IDs of posts whose comments should be considered.
	 * @param string $output   Optional. Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K
	 *                         constants. See WP docs for wpdb::get_results() for
	 *                         more info.
	 * @return mixed List of email addresses.
	 */
	public static function get_emails( $fields = array(), $post_ids = array(), $output = ARRAY_N ) {
		global $wpdb;

		// If $field is explicitly empty, use the default.
		if ( ! $fields ) {
			$fields = array( 'comment_author_email', 'comment_author', 'comment_author_url' );
		}

		// Ensure only valid comment fields are specified.
		$fields = array_intersect(
			(array) $fields,
			array( 'comment_ID', 'comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_author_IP',
					'comment_date', 'comment_date_gmt', 'comment_content', 'comment_karma', 'comment_approved', 'comment_agent',
					'comment_type', 'comment_parent', 'user_id' )
		);

		// In v2.4, the $post_ids arg was introduced and put before the existing
		// $output arg. This block of code handles backwards compatibility for whoever
		// may be using that arg.
		if ( ! is_array( $post_ids ) && in_array( $post_ids, array( ARRAY_A, ARRAY_N, OBJECT, OBJECT_K ) ) ) {
			$output = $post_ids;
			$post_ids = array();
		}

		// Ensure comment_author_email is always included.
		if ( ! in_array( 'comment_author_email', $fields ) ) {
			array_unshift( $fields,  'comment_author_email' );
		}

		$fields = implode( ', ', $fields );

		if ( $post_ids ) {
			$post_ids = implode( ', ', array_map( 'intval', (array) $post_ids ) );
			$posts = " AND comment_post_ID IN ( $post_ids ) ";
		} else {
			$posts = '';
		}

		$sql = "SELECT $fields
				FROM {$wpdb->comments} t1
				INNER JOIN ( SELECT MAX(comment_ID) AS id FROM {$wpdb->comments} GROUP BY comment_author_email ) t2 ON t1.comment_ID = t2.id
				WHERE
					comment_approved = '1' AND
					comment_author_email != '' AND
					(comment_type = '' OR comment_type = 'comment')
					{$posts}
				GROUP BY comment_author_email
				ORDER BY comment_author_email ASC";
		$emails = $wpdb->get_results( $sql, $output );

		return $emails;
	}

	/**
	 * Handles download of commenter emails directly as CSV file.
	 */
	public static function handle_csv_download() {
		if ( isset( $_GET['download_csv'] ) && '1' == $_GET['download_csv'] ) {
			header( 'Content-type: text/csv' );
			header( 'Cache-Control: no-store, no-cache' );
			header( 'Content-Disposition: attachment; filename="' . self::$csv_filename . '"' );

			$outstream = fopen( "php://output", 'w' );

			$default_fields = array( 'comment_author', 'comment_author_email' );
			if ( isset( $_GET['include_url'] ) && '1' == $_GET['include_url'] ) {
				$default_fields[] = 'comment_author_url';
			}

			$fields    = apply_filters( 'c2c_commenter_emails_fields', $default_fields );
			$field_sep = apply_filters( 'c2c_commenter_emails_field_separator', ',' );

			foreach ( (array) self::get_emails( $fields, self::get_post_ids() ) as $item ) {
				fputcsv( $outstream, $item, $field_sep, '"' );
			}

			fclose( $outstream );

			exit();
		}
	}

	/**
	 * Creates the admin menu.
	 */
	public static function register_admin_menu() {
		// Add plugin action links.
		add_filter( 'plugin_action_links_' . self::$plugin_basename, array( __CLASS__, 'plugin_action_links' ) );

		// Add menu item under Comments.
		self::$plugin_page = add_comments_page( __( 'Commenter Emails', 'commenter-emails' ), __( 'Commenter Emails', 'commenter-emails' ),
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
	 * @param array  $action_links The current action links.
	 * @return array The action links.
	 */
	public static function plugin_action_links( $action_links ) {
		$settings_link = '<a href="edit-comments.php?page=' . self::$plugin_basename.'">' . __( 'Listing', 'commenter-emails' ) . '</a>';
		array_unshift( $action_links, $settings_link );
		return $action_links;
	}

	/**
	 * Gets the post IDs specified by the user.
	 *
	 * @since 2.4
	 *
	 * @return array
	 */
	 protected static function get_post_ids() {
		$key = 'c2c-commenter-emails-post-ids';
		if ( ! isset( $_GET[ $key ] ) || ! $_GET[ $key ] ) {
			return array();
		}

		return array_unique( array_map( 'intval', array_map( 'trim', explode( ',', $_GET[ $key ] ) ) ) );
	}

	/**
	 * Outputs the contents of the plugin's admin page.
	 */
	public static function admin_page() {
		$post_ids = self::get_post_ids();
		$emails = self::get_emails( array(), $post_ids );
		$emails_count = count( $emails );
		$logo = plugins_url( 'c2c_minilogo.png', __FILE__ );

		echo '<div class="wrap">';
		echo '<h1>' . __( 'Commenter Emails', 'commenter-emails' ) . '</h1>';
		echo '<p>';
		if ( $post_ids ) {
			printf(
				/* translators: %s: count of number of commenter email addresses */
				_n(
					'There is %s unique and approved commenter email address for the specified posts.',
					'There are %s unique and approved commenter email addresses for the specified posts.',
					$emails_count,
					'commenter-emails'
				),
				$emails_count
			);
		} else {
			printf(
				/* translators: %s: count of number of commenter email addresses */
				_n(
					'There is %s unique and approved commenter email address for this site.',
					'There are %s unique and approved commenter email addresses for this site.',
					$emails_count,
					'commenter-emails'
				),
				$emails_count
			);
		}
		echo '</p>';
		echo '</div>';

		if ( self::$show_emails ) {
			echo '<div class="wrap">';
			echo '<h2>' . __( 'All Commenter Emails', 'commenter-emails' ) . '</h2>';

			if ( $emails ) {
				echo '<table class="commenter-emails-table">';
				echo '<tr><th>' . __( 'Email', 'commenter-emails' ) . '</th><th>' . __( 'Name', 'commenter-emails' ) . '</th>';
				echo '<th>' . __( 'URL', 'commenter-emails' ) . '</th></tr>';

				foreach ( $emails as $item ) {
					echo '<tr><td>' . esc_html( $item[0] ) . '</td><td>' . esc_html( $item[1] ) . '</td>';
					echo '<td>' . make_clickable( esc_html( $item[2] ) ) . '</td></tr>';
				}

				echo '</table>';
			}

			echo '<p>';
			if ( $post_ids ) {
				if ( count( $post_ids ) > 1 ) {
					if ( $emails_count ) {
						printf( _n( '%s commenter email listed for the specified posts.', '%s commenter emails listed for the specified posts.', $emails_count, 'commenter-emails' ), $emails_count );
					} else {
						_e( 'There were no commenters found to list for the specified posts.', 'commenter-emails' );
					}
				} else {
					if ( $emails_count ) {
						printf( _n( '%s commenter email listed for the specified post.', '%s commenter emails listed for the specified post.', $emails_count, 'commenter-emails' ), $emails_count );
					} else {
						_e( 'There were no commenters found to list for the specified post.', 'commenter-emails' );
					}
				}
			} elseif ( ! $emails_count ) {
				printf( __( 'There were no commenters found to list.' ) );
			} else {
				printf( __( '%s commenter emails listed.', 'commenter-emails' ), $emails_count );
			}
			echo '</p>';
			echo '</div>';
		}

		if ( self::$show_csv_button ) {
			echo '<div class="wrap">';
			echo '<p><form action="" method="get">';
			echo '<label for="submit">';
			_e( 'Download this list of email addresses as a CSV file :', 'commenter-emails' );
			echo ' <input type="submit" name="submit" value="' . esc_attr__( 'Download', 'commenter-emails' ) . '" />';
			echo '</label>';
			echo '<label for="include_url">(';
			echo '<input type="checkbox" name="include_url" value="1" /> ';
			_e( 'Include commenter website?', 'commenter-emails' );
			echo ')<input type="hidden" name="page" value="' . esc_attr( $_GET['page'] ) . '" />';
			echo '<input type="hidden" name="download_csv" value="1" />';
			echo '</form></p></div>';
		}

		echo '<div class="wrap">';
		echo '<p class="description">NOTE: If you would like to list only commenters for selected posts, use the "Help" tab above to specify those posts.</p>';
		echo '</div>';

		echo '<div id="c2c-ce" class="wrap"><div>';
		printf( __( 'This plugin brought to you by <a href="%s">Scott Reilly, aka coffee2code</a>.', 'commenter-emails' ), 'http://coffee2code.com' );
		echo '<span><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=6ARCFJ9TX3522" title="' . esc_attr__( 'Please consider a donation', 'commenter-emails' ) . '">';
		_e( 'Did you find this plugin useful?', 'commenter-emails' );
		echo '</a></span></div></div>';
	}

	/**
	 * Registers help tabs.
	 *
	 * @since 2.4
	 */
	public static function help_tabs() {
		if ( ! class_exists( 'WP_Screen' ) ) {
			return;
		}

		$screen = get_current_screen();

		if ( $screen->id != self::$plugin_page ) {
			return;
		}

		$content = '<form action="" method="get">';
		$content .= '<p><label for="c2c-commenter-emails-post-ids">';
		$content .=  __( 'Filter the listing to only include commenters who commented on specified posts.', 'commenter-emails' );
		$content .= '</label></p>';
		$content .= '<input type="text" size="50" name="c2c-commenter-emails-post-ids" value="' . implode( ', ', self::get_post_ids() ) . '"/>' . "\n";
		$content .= '<p class="description">';
		$content .= __( 'Comma-separated list of post IDs to limit commenter emails to those posts.', 'commenter-emails' );
		$content .= "</p>\n";
		$content .= '<input type="hidden" name="page" value="' . esc_attr( $_GET['page'] ) . '"/>';
		$content .= '<input type="submit" name="c2c-commenter-emails-apply" class="button" value="' . esc_attr__( 'Apply', 'commenter-emails' ) . '" />';
		$content .= "</form>\n";

		$screen->add_help_tab( array(
			'id'      => 'c2c-commenter-emails',
			'title'   => __( 'Limit to posts', 'commenter-emails' ),
			'content' => $content,
		) );
	}
} // end c2c_CommenterEmails

c2c_CommenterEmails::init();

endif; // end if ! class_exists()
