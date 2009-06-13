<?php
/*
Plugin Name: Commenter Emails
Version: 1.1
Plugin URI: http://coffee2code.com/wp-plugins/commenter-emails
Author: Scott Reilly
Author URI: http://coffee2code.com
Description: Extract a listing of all commenter emails.

Via the admin page added by the plugin, Comments -> Commenter Emails, the admin is presented with the following information:
* A total count of all unique commenters to the blog
* A button to download the entire list of unique commenters' email addresses in CSV (comma-separated values) format
* The entire list of unique commenters' email addresses

The plugin only considers approved comments and does not exclude from its listing any known emails (i.e. admin and post author emails).

Compatible with WordPress 2.2+, 2.3+, 2.5+, 2.6+, 2.7+, 2.8+.

=>> Read the accompanying readme.txt file for more information.  Also, visit the plugin's homepage
=>> for more information and the latest updates

Installation:

1. Download the file http://coffee2code.com/wp-plugins/commenter-emails.zip and unzip it into your 
/wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' admin menu in WordPress
3. View the commenter email information reported in the WordPress admin via Comments -> Commenter Emails

*/

/*
Copyright (c) 2007-2009 by Scott Reilly (aka coffee2code)

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

if ( !class_exists('CommenterEmails') ) :

class CommenterEmails {
	var $show_csv_button = true;	// Setting to determine if the plugin's admin page should show the CSV button
	var $show_emails = true;		// Setting to determine if the plugin's admin page should show the list of emails
	var $csv_filename = 'commenter-emails.csv';

	function CommenterEmails() {
		if ( is_admin() ) {
			add_action('admin_menu', array(&$this, 'admin_menu'));
			$this->handle_csv_download();
		}
	}

	function get_emails() {
		global $wpdb;
		$sql = "SELECT DISTINCT comment_author_email 
				FROM {$wpdb->comments} 
				WHERE
					comment_approved = '1' AND
					comment_author_email != '' AND
					(comment_type = '' OR comment_type = 'comment') 
				ORDER BY comment_author_email ASC";
		$emails = $wpdb->get_col($sql);
		return $emails;
	}

	function handle_csv_download() {
		global $pagenow;
		if ( ('edit-comments.php' == $pagenow) && 
			 $_GET['page'] && ($_GET['page'] == basename(__FILE__)) && 
			($_GET['download_csv'] == '1')
		   ) {
			header('Content-type: text/csv');
			header('Content-Disposition: attachment; filename="'.$this->csv_filename.'"');
			echo implode(',', $this->get_emails());
			exit();
		}
	}

	function admin_menu() {
		// Add menu under Comments:
		add_submenu_page('edit-comments.php', 'Commenter Emails', 'Commenter Emails', 10, basename(__FILE__), array(&$this, 'admin_page'));
	}

	function admin_page() {
		$emails = $this->get_emails();
		$emails_count = count($emails);
		$emails = implode('<br />', $emails);
		$logo = plugins_url() . '/' . basename($_GET['page'], '.php') . '/c2c_minilogo.png';

		echo <<<HTML
		<div class='wrap'>
			<div class='icon32' style='width:44px;'><img src='{$logo}' alt='A plugin by coffee2code' /><br /></div>
			<h2>Commenter Emails</h2>
			<p>There are $emails_count unique commenter email addresses for this blog.</p>
		</div>
HTML;

		if ( $this->show_csv_button ) {
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

		if ( $this->show_emails ) {
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
} // end CommenterEmails

endif; // end if !class_exists()

if ( class_exists('CommenterEmails') )
	new CommenterEmails();

?>