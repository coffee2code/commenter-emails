<?php

defined( 'ABSPATH' ) or die();

class Commenter_Emails_Test extends WP_UnitTestCase {

	protected $captured_c2c_commenter_emails_show_csv_button;
	protected $captured_c2c_commenter_emails_show_emails;
	protected $captured_c2c_commenter_emails_filename = '';

	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();

		$captured_c2c_commenter_emails_show_emails = null;
		$captured_c2c_commenter_emails_filename = '';

		remove_filter( 'c2c_commenter_emails_show_csv_button', array( $this, 'c2c_commenter_emails_show_csv_button' ) );
		remove_filter( 'c2c_commenter_emails_show_emails', array( $this, 'c2c_commenter_emails_show_emails' ) );
		remove_filter( 'c2c_commenter_emails_filename',    array( $this, 'c2c_commenter_emails_filename' ) );
	}


	//
	//
	// DATA PROVIDERS
	//
	//


	//
	//
	// HELPER FUNCTIONS
	//
	//


	private function create_users( $number = 5 ) {
		return $this->factory->user->create_many( $number );
	}

	private function create_comments( $num_comments_per_post = 5, $num_posts = 3, $post_args = array(), $comment_args = array() ) {
		$comment_ids = array();

		for ( $i = 0; $i < $num_posts; $i++ ) {
			$post_id = $this->factory->post->create( $post_args );
			for ( $j = 0; $j < $num_comments_per_post; $j++ ) {
				$k = $i . '-' . $j;
				$c_args = array(
					'comment_post_ID'      => $post_id,
					'comment_author'       => "Commenter {$k}",
					'comment_author_email' => "commenter{$k}@example.com",
					'comment_author_url'   => "http://example.com/user{$k}",
					'comment_author_IP'    => "127.0.0.{$j}",
					'comment_content'      => "The comment content by {$k}.",
				);
				$comment_ids[] = $this->factory->comment->create( wp_parse_args( $comment_args, $c_args ) );
			}
		}

		return $comment_ids;
	}

	public function c2c_commenter_emails_show_csv_button( $value ) {
		return $this->captured_c2c_commenter_emails_show_csv_button = $value;
	}

	public function c2c_commenter_emails_show_emails( $value ) {
		return $this->captured_c2c_commenter_emails_show_emails = $value;
	}

	public function c2c_commenter_emails_filename( $filename ) {
		return $this->captured_c2c_commenter_emails_filename = $filename;
	}

	public function change_c2c_commenter_emails_filename( $filename ) {
		return sprintf( 'custom-file-output.%s.csv', mysql2date( 'Y.m.d', current_time( 'mysql' ) ) );
	}

	//
	//
	// TESTS
	//
	//

	public function test_class_exists() {
		$this->assertTrue( class_exists( 'c2c_CommenterEmails' ) );
	}

	public function test_version() {
		$this->assertEquals( '2.5.1', c2c_CommenterEmails::version() );
	}

	public function test_hooks_plugins_loaded() {
		$this->assertEquals( 10, has_action( 'plugins_loaded', array( 'c2c_CommenterEmails', 'init' ) ) );
	}

	public function test_hooks_action_admin_menu_for_admin_menu() {
		$this->assertEquals( 11, has_action( 'admin_menu', array( 'c2c_CommenterEmails', 'admin_menu' ) ) );
	}

	//
	// get_emails()
	//

	public function test_get_emails_default( $comment_ids = array() ) {
		// Allow other tests to reuse this test without this recreating comments.
		if ( empty( $comment_ids ) ) {
			$comment_ids = $this->create_comments();
		}

		$emails = array();
		foreach ( $comment_ids as $comment_id ) {
			$comment = get_comment( $comment_id );
			$emails[] = array( $comment->comment_author_email, $comment->comment_author, $comment->comment_author_url );
		}

		$this->assertEquals(
			$emails,
			c2c_CommenterEmails::get_emails()
		);
	}

	public function test_get_emails_with_empty_fields() {
		$comment_ids = $this->create_comments();

		$emails = array();
		foreach ( $comment_ids as $comment_id ) {
			$comment = get_comment( $comment_id );
			$emails[] = array( $comment->comment_author_email, $comment->comment_author, $comment->comment_author_url );
		}

		$this->assertEquals(
			$emails,
			c2c_CommenterEmails::get_emails( '' )
		);
	}

	public function test_get_emails_for_specified_posts() {
		$comment_ids = $this->create_comments();

		$main_comment = get_comment( $comment_ids[1] );

		$emails = array();
		foreach ( $comment_ids as $comment_id ) {
			$comment = get_comment( $comment_id );
			if ( $comment->comment_post_ID !== $main_comment->comment_post_ID ) {
				continue;
			}
			$emails[] = array( $comment->comment_author_email, $comment->comment_author, $comment->comment_author_url );
		}

		$this->assertEquals(
			$emails,
			c2c_CommenterEmails::get_emails( array(), array( $main_comment->comment_post_ID ) )
		);
	}

	public function test_get_emails_with_object_return_type() {
		$comment_ids = $this->create_comments();

		$emails = array();
		foreach ( $comment_ids as $comment_id ) {
			$comment = get_comment( $comment_id );
			$emails[] = (object) array(
				'comment_author_email' => $comment->comment_author_email,
				'comment_author'       => $comment->comment_author,
				'comment_author_url'   => $comment->comment_author_url
			);
		}

		$this->assertEquals(
			$emails,
			c2c_CommenterEmails::get_emails( array(), array(), OBJECT )
		);
	}

	public function test_get_emails_with_object_return_type_via_depracated_arg() {
		$comment_ids = $this->create_comments();

		$emails = array();
		foreach ( $comment_ids as $comment_id ) {
			$comment = get_comment( $comment_id );
			$emails[] = (object) array(
				'comment_author_email' => $comment->comment_author_email,
				'comment_author'       => $comment->comment_author,
				'comment_author_url'   => $comment->comment_author_url
			);
		}

		$this->assertEquals(
			$emails,
			c2c_CommenterEmails::get_emails( array(), OBJECT )
		);
	}

	public function test_get_emails_with_specific_fields() {
		$comment_ids = $this->create_comments();
		$emails = array();
		foreach ( $comment_ids as $comment_id ) {
			$comment = get_comment( $comment_id );
			$emails[] = array( $comment->comment_author_email, $comment->comment_author_IP );
		}

		$this->assertEquals(
			$emails,
			c2c_CommenterEmails::get_emails( array( 'comment_author_email', 'comment_author_IP' ) )
		);
	}

	public function test_get_emails_with_invalid_field_ignores_that_field() {
		$comment_ids = $this->create_comments();
		$emails = array();
		foreach ( $comment_ids as $comment_id ) {
			$comment = get_comment( $comment_id );
			$emails[] = array( $comment->comment_author_email, $comment->comment_author_IP );
		}

		$this->assertEquals(
			$emails,
			c2c_CommenterEmails::get_emails( array( 'comment_author_email', 'comment_author_IP', 'bogus_field' ) )
		);
	}

	public function test_get_emails_omitting_comment_author_email_still_includes_it() {
		$comment_ids = $this->create_comments();
		$emails = array();
		foreach ( $comment_ids as $comment_id ) {
			$comment = get_comment( $comment_id );
			$emails[] = array( $comment->comment_author_email, $comment->comment_author );
		}

		$this->assertEquals(
			$emails,
			c2c_CommenterEmails::get_emails( array( 'comment_author' ) )
		);
	}

	public function test_get_emails_with_all_the_args() {
		$comment_ids = $this->create_comments();

		$main_comment = get_comment( $comment_ids[1] );

		$emails = array();
		foreach ( $comment_ids as $comment_id ) {
			$comment = get_comment( $comment_id );
			if ( $comment->comment_post_ID !== $main_comment->comment_post_ID ) {
				continue;
			}
			$emails[] = (object) array(
				'comment_author_email' => $comment->comment_author_email,
				'comment_author_url'   => $comment->comment_author_url
			);
		}

		$this->assertEquals(
			$emails,
			c2c_CommenterEmails::get_emails(
				array( 'comment_author_email', 'comment_author_url' ),
				array( $main_comment->comment_post_ID ),
				OBJECT
			)
		);
	}

	public function test_get_emails_repeat_commenter_only_listed_once() {
		$email = 'commenter@example.com';
		$comment_ids = $this->create_comments( 3, 3, array(), array( 'comment_author_email' => $email ) );

		$this->assertEquals(
			array( array( $email ) ),
			c2c_CommenterEmails::get_emails( array( 'comment_author_email' ) )
		);
	}

	public function test_get_emails_ignores_unapproved_comments() {
		$comment_ids = $this->create_comments();

		wp_delete_comment( $comment_ids[0] );
		wp_trash_comment(  $comment_ids[1] );
		wp_spam_comment(   $comment_ids[2] );

		$this->test_get_emails_default( array_slice( $comment_ids, 3 ) );
	}

	/*
	 * get_plugin_basename()
	 */

	public function test_get_plugin_basename() {
		$this->assertEquals(
			plugin_basename( dirname( dirname( __FILE__ ) ) . '/commenter-emails.php' ),
			c2c_CommenterEmails::get_plugin_basename()
		);
	}

	/*
	 * should_show_csv_button()
	 */

	public function test_default_for_should_show_csv_button() {
		$this->assertTrue( c2c_CommenterEmails::should_show_csv_button() );
	}

	/*
	 * filter: c2c_commenter_emails_show_csv_button
	 */

	public function test_default_for_filter_c2c_commenter_emails_show_csv_button() {
		add_filter( 'c2c_commenter_emails_show_csv_button', array( $this, 'c2c_commenter_emails_show_csv_button' ) );

		$this->assertTrue( c2c_CommenterEmails::should_show_csv_button() );
		$this->assertTrue( $this->captured_c2c_commenter_emails_show_csv_button );
	}

	public function test_c2c_commenter_emails_show_csv_button() {
		add_filter( 'c2c_commenter_emails_show_csv_button', '__return_false' );
		// Capture filtered value.
		add_filter( 'c2c_commenter_emails_show_csv_button', array( $this, 'c2c_commenter_emails_show_csv_button' ) );

		$this->assertFalse( c2c_CommenterEmails::should_show_csv_button() );
		$this->assertFalse( $this->captured_c2c_commenter_emails_show_csv_button );

		// Cleanup
		remove_filter( 'c2c_commenter_emails_show_csv_button', '__return_false' );
	}

	/*
	 * should_show_email_addresses()
	 */

	public function test_default_for_should_show_email_addresses() {
		$this->assertTrue( c2c_CommenterEmails::should_show_email_addresses() );
	}

	/*
	 * filter: c2c_commenter_emails_show_emails
	 */

	public function test_default_for_filter_c2c_commenter_emails_show_emails() {
		add_filter( 'c2c_commenter_emails_show_emails', array( $this, 'c2c_commenter_emails_show_emails' ) );

		$this->assertTrue( c2c_CommenterEmails::should_show_email_addresses() );
		$this->assertTrue( $this->captured_c2c_commenter_emails_show_emails );
	}

	public function test_filter_c2c_commenter_emails_show_emails() {
		add_filter( 'c2c_commenter_emails_show_emails', '__return_false' );
		// Capture filtered value.
		add_filter( 'c2c_commenter_emails_show_emails', array( $this, 'c2c_commenter_emails_show_emails' ) );

		$this->assertFalse( c2c_CommenterEmails::should_show_email_addresses() );
		$this->assertFalse( $this->captured_c2c_commenter_emails_show_emails );

		// Cleanup
		remove_filter( 'c2c_commenter_emails_show_emails', '__return_false' );
	}

	/*
	 * get_filename()
	 */

	public function test_default_for_get_filename() {
		$date_str = mysql2date( 'Y-m-d', current_time( 'mysql' ) );

		// Note: This assertion could fail if run microseconds before midnight.
		$this->assertRegExp( "/^commenter-emails-{$date_str}-[0-9]{4}.csv$/", c2c_CommenterEmails::get_filename() );
	}

	/*
	 * filter: c2c_commenter_emails_filename
	 */

	public function test_default_for_filter_c2c_commenter_emails_filename() {
		add_filter( 'c2c_commenter_emails_filename', array( $this, 'c2c_commenter_emails_filename' ) );

		$date_str = mysql2date( 'Y-m-d', current_time( 'mysql' ) );
		$regex    = "/^commenter-emails-{$date_str}-[0-9]{4}.csv$/";

		// Note: These assertions could fail if run microseconds before midnight.
		$this->assertRegExp( $regex, c2c_CommenterEmails::get_filename() );
		$this->assertRegExp( $regex, $this->captured_c2c_commenter_emails_filename );
	}

	public function test_filter_c2c_commenter_emails_filename() {
		add_filter( 'c2c_commenter_emails_filename', array( $this, 'change_c2c_commenter_emails_filename' ) );
		// Capture filtered value.
		add_filter( 'c2c_commenter_emails_filename', array( $this, 'c2c_commenter_emails_filename' ) );

		$expected = sprintf( 'custom-file-output.%s.csv', mysql2date( 'Y.m.d', current_time( 'mysql' ) ) );

		// Note: These assertions could fail if run microseconds before midnight.
		$this->assertEquals( $expected, c2c_CommenterEmails::get_filename() );
		$this->assertEquals( $expected, $this->captured_c2c_commenter_emails_filename );

		// Cleanup
		remove_filter( 'c2c_commenter_emails_filename', array( $this, 'change_c2c_commenter_emails_filename' ) );
	}

	/*
	 * plugin_action_links()
	 */

	public function test_plugin_action_links() {
		$expected_file = urlencode( c2c_CommenterEmails::get_plugin_basename() );

		$this->assertEquals(
			sprintf( '<a href="edit-comments.php?page=%s">Listing</a>', $expected_file ),
			c2c_CommenterEmails::plugin_action_links( array() )[0]
		);
	}

	// TEST: default csv filename is used

	// TEST: custom csv filename is used

	// TEST: default separator used in csv

	// TEST: comment_author_url included in csv via setting

	// TEST: custom fields output to csv via filter c2c_commenter_emails_fields

	// TEST: custom separator use in csv via filter c2c_commenter_emails_field_separator

	// TEST: plugin action links added

	// TEST: plugin menu option added under Comments

	// TEST: CSS is enqueued on appropriate admin page

	// TEST: CSS not enqueued on inappropriate admin page

	// TEST: CSS not enqueued on frontend
}
