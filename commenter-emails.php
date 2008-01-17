<?php
/*
Plugin Name: Commenter Emails
Version: 0.9
Author: Scott Reilly
Author URI: http://www.coffee2code.com
Description: Extract a listing of all commenter emails 

Copyright (c) 2007 by Scott Reilly (aka coffee2code)

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

class CommenterEmails {
	
	function CommenterEmails() {
		add_action('admin_menu', array(&$this, 'admin_menu'));
		$this->handle_csv_download();
	}

	function get_emails() {
		global $wpdb;
		$emails = $wpdb->get_col("SELECT DISTINCT comment_author_email FROM {$wpdb->comments} ORDER BY comment_author_email ASC");
		return $emails;
	}

	function handle_csv_download() {
		if ( (basename($_SERVER['PHP_SELF']) == 'edit-comments.php') && 
			 $_GET['page'] && ($_GET['page'] == basename(__FILE__)) && 
			($_GET['download_csv'] == '1')
		   ) {
			header('Content-type: text/csv');
			header('Content-Disposition: attachment; filename="commenter-emails.csv"');
			echo implode(',', $this->get_emails());
			exit();
		}
	}

	function admin_menu() {
		// Add menu under Comments:
	    add_submenu_page('edit-comments.php', 'Commenter Emails', 'Commenter Emails', 10, basename(__FILE__), array(&$this, 'admin_page'));
	}

	function admin_page() {
		$show_csv_button = true;
		$show_emails = true;

		$emails = $this->get_emails();
		$emails_count = count($emails);
		$emails = implode(',<br />', $this->get_emails());

		echo <<<HTML
		<div class='wrap'>
			<h2>Commenter Emails</h2>
			<p>There are $emails_count unique commenter email addresses for this blog.</p>
		</div>
HTML;

		if ($show_csv_button) {
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

		if ($show_emails) {
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
	}
}
// Get the ball rolling
function init_CommenterEmails() {
	global $commenter_emails;
	$commenter_emails = new CommenterEmails();
}
add_action('init', 'init_CommenterEmails');
?>