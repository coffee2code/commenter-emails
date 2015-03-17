<?php

class Commenter_Emails_Test extends WP_UnitTestCase {

	function setUp() {
		parent::setUp();
	}

	function tearDown() {
		parent::tearDown();
	}


	/*
	 *
	 * DATA PROVIDERS
	 *
	 */


	/*
	 *
	 * HELPER FUNCTIONS
	 *
	 */


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


	/*
	 *
	 * TESTS
	 *
	 */


	function test_class_exists() {
		$this->assertTrue( class_exists( 'c2c_CommenterEmails' ) );
	}

	function test_version() {
		$this->assertEquals( '2.3', c2c_CommenterEmails::version() );
	}

	/*
	 * get_emails()
	 */

	function test_get_emails_default( $comment_ids = array() ) {
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

	function test_get_emails_with_empty_fields() {
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

	function test_get_emails_with_object_return_type() {
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

	function test_get_emails_with_specific_fields() {
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

	function test_get_emails_with_invalid_field_ignores_that_field() {
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

	function test_get_emails_omitting_comment_author_email_still_includes_it() {
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

	function test_get_emails_repeat_commenter_only_listed_once() {
		$email = 'commenter@example.com';
		$comment_ids = $this->create_comments( 3, 3, array(), array( 'comment_author_email' => $email ) );

		$this->assertEquals(
			array( array( $email ) ),
			c2c_CommenterEmails::get_emails( array( 'comment_author_email' ) )
		);
	}

	function test_get_emails_ignores_unapproved_comments() {
		$comment_ids = $this->create_comments();

		wp_delete_comment( $comment_ids[0] );
		wp_trash_comment(  $comment_ids[1] );
		wp_spam_comment(   $comment_ids[2] );

		$this->test_get_emails_default( array_slice( $comment_ids, 3 ) );
	}

	/*
	 * Admin area tests. All tests beyond this point assume the admin area.
	 */

	function test_admin_stuff() {
		define( 'WP_ADMIN', true );
		c2c_CommenterEmails::init();

		$this->assertTrue( is_admin() );
	}


	/*
	 * Filters
	 */

	function test_hooks_action_admin_menu_for_admin_menu() {
		$this->test_admin_stuff();

		$this->assertEquals( 10, has_action( 'admin_menu', array( 'c2c_CommenterEmails', 'admin_menu' ) ) );
	}

	function test_hooks_action_admin_menu_for_do_init() {
		$this->test_admin_stuff();

		$this->assertEquals( 11, has_action( 'admin_menu', array( 'c2c_CommenterEmails', 'do_init' ) ) );
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
