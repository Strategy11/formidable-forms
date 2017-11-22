<?php
/**
 * @group emails
 * @group pro
 */
class WP_Test_FrmProEmail extends WP_Test_FrmEmail {

	protected $email_form_key = 'contact-with-email';
	protected $name_field_key = 'contact-name';
	protected $email_field_key = 'contact-email';

	public function setUp() {
		$user_factory = new WP_UnitTest_Factory_For_User();
		$args = array(
			'user_login' => 'email_user',
			'user_pass' => 'email_pass',
			'user_email' => 'emailtest@mail.com'
		);
		$user_factory->create_object( $args );
		$this->set_current_user_to_username( 'email_user' );

		parent::setUp();
	}

	/**
	 * Tests userID field ID and key, multiple to addresses, double quotes, user-defined subject
	 *
	 * To: Name [x], Name2<jamie@test.com>, [admin_email] (x is the ID of a userID field)
	 * CC: "Jamie Wahlin" test@mail.com
	 * BCC: [if x equals="Jamie Wahlin"]jw@test.com[/if x], where x is the key of a text field
	 * Reply_to: Reply Name
	 * From:
	 * Subject: Submission from [x]
	 * Message: [default-message]
	 * Inc_user_info: false
	 * Plain_text: true
	 *
	 * @covers FrmNotification::trigger_email
	 */
	public function test_trigger_email_six() {
		$entry_clone = clone $this->entry;
		$expected = array();
		$admin_email = get_option('admin_email');

		// To
		$user_field_id = FrmField::get_id_by_key( 'contact-user-id' );
		$this->assertNotFalse( $user_field_id, 'The UserID field is not retrieved with key contact-user-id.' );
		$this->assertArrayHasKey( $user_field_id, $entry_clone->metas, 'The UserID value is empty. A user may not have been set when the entry was created.' );
		$this->email_action->post_content['email_to'] = 'Name [' . $user_field_id . '], Name2<jamie@test.com>, [admin_email]';
		$user = wp_get_current_user();
		$expected['to'] = array( array( $user->user_email, 'Name' ), array( 'jamie@test.com', 'Name2' ), array( $admin_email, '' ) );

		// CC
		$this->email_action->post_content['cc'] = '"Jamie Wahlin" test@mail.com';
		$expected['cc'] = array( array( 'test@mail.com', 'Jamie Wahlin' ) );

		// BCC
		$name_id = FrmField::get_id_by_key( 'contact-name' );
		$entry_clone->metas[ $name_id ] = 'Jamie Wahlin';
		$this->email_action->post_content['bcc'] = '[if ' . $name_id . ' equals="Jamie Wahlin"]jw@test.com[/if ' . $name_id . ']';
		$expected['bcc'] = array( array( 'jw@test.com', '' ) );

		// Subject
		$this->email_action->post_content['email_subject'] = 'Submission from [' . $name_id . ']';
		$expected['subject'] = self::prepare_subject( 'Submission from Jamie Wahlin' );

		// From
		$this->email_action->post_content['from'] = '';
		$expected['from'] = FrmAppHelper::site_name() . ' <' . $admin_email . '>';

		// Reply to
		$this->email_action->post_content['reply_to'] = 'Reply Name';
		$expected['reply_to'] = 'Reply Name <' . get_option('admin_email') . '>';

		// Body - set plain text to true
		$this->email_action->post_content['plain_text'] = true;
		$expected['body'] = FrmEntriesController::show_entry_shortcode( array( 'id' => $entry_clone->id, 'entry' => $entry_clone, 'plain_text' => true ) );

		// Content type
		$expected['content_type'] = 'Content-Type: text/plain; charset=UTF-8';

		FrmNotification::trigger_email( $this->email_action, $entry_clone, $this->contact_form );

		$mock_email = end( $GLOBALS['phpmailer']->mock_sent );

		$this->check_recipients( $expected, $mock_email );
		$this->check_senders( $expected, $mock_email );
		$this->check_subject( $expected, $mock_email );
		$this->check_message_body( $expected, $mock_email );
		$this->check_content_type( $expected, $mock_email );
	}

	/**
	 * Tests multiple cc and bcc addresses, user ID key,
	 *
	 * To: Name test1@mail.com, Name2<test2@mail.com>, add test3@mail.com and 1231231234 with hook
	 * CC: "Jamie Wahlin" <testcc1@mail.com>,[x show="display_name"] <[x show="user_email"]>
	 * BCC: [if x equals="Jamie Wahlin"]testbcc1@mail.com[/if x], "Tester test" testbcc2@mail.com where x is the key of a text field
	 * Reply_to: "Reply To" <[userIDkey]>
	 * From: "Name"
	 * Subject: Set to "New Subject" with filter
	 * Message: [default-message]
	 * Inc_user_info: true
	 * Plain_text: false
	 * Attachments:
	 *
	 * @covers FrmNotification::trigger_email
	 */
	public function test_trigger_email_seven() {
		$entry_clone = clone $this->entry;
		$expected = array();

		// To
		$user_field_id = FrmField::get_id_by_key( 'contact-user-id' );
		$this->assertNotFalse( $user_field_id, 'The UserID field is not retrieved with key contact-user-id.' );
		$this->assertArrayHasKey( $user_field_id, $entry_clone->metas, 'The UserID value is empty. A user may not have been set when the entry was created.' );
		$this->email_action->post_content['email_to'] = 'Name test1@mail.com, Name2<test2@mail.com>';
		$user = wp_get_current_user();
		$expected['to'] = array( array( 'test1@mail.com', 'Name' ), array( 'test2@mail.com', 'Name2' ), array( 'test3@mail.com', '' ) );
		add_filter('frm_to_email', array( $this, 'add_to_emails' ), 10, 4 );

		// CC
		$this->email_action->post_content['cc'] = '"Jamie Wahlin" <testcc1@mail.com>,';
		$this->email_action->post_content['cc'] .= '[' . $user_field_id . ' show="display_name"] <[' . $user_field_id . ' show="user_email"]>';
		$expected['cc'] = array( array( 'testcc1@mail.com', 'Jamie Wahlin' ), array( $user->user_email, $user->display_name ) );

		// BCC
		$name_id = FrmField::get_id_by_key( 'contact-name' );
		$entry_clone->metas[ $name_id ] = 'Jamie Wahlin';
		$this->email_action->post_content['bcc'] = '[if ' . $name_id . ' equals="Jamie Wahlin"]testbcc1@mail.com[/if ' . $name_id . '], "Tester test" testbcc2@mail.com';
		$expected['bcc'] = array( array( 'testbcc1@mail.com', '' ), array( 'testbcc2@mail.com', 'Tester test' ) );

		// Subject
		$this->email_action->post_content['email_subject'] = 'Original subject';
		$expected['subject'] = self::prepare_subject( 'New subject' );
		add_filter( 'frm_email_subject', array( $this, 'change_email_subject' ), 10, 2 );

		// From
		$this->email_action->post_content['from'] = 'Name';
		$expected['from'] = 'Name <' . get_option('admin_email') . '>';

		// Reply to
		$this->email_action->post_content['reply_to'] = '"Reply To" <[contact-user-id]>';
		$expected['reply_to'] = 'Reply To <' . $user->user_email . '>';

		// Body - set inc_user_info to true
		$this->email_action->post_content['inc_user_info'] = true;
		$expected['body'] = FrmEntriesController::show_entry_shortcode( array( 'id' => $entry_clone->id, 'entry' => $entry_clone, 'user_info' => true ) );

		// Content type
		$expected['content_type'] = 'Content-Type: text/html; charset=UTF-8';

		FrmNotification::trigger_email( $this->email_action, $entry_clone, $this->contact_form );

		// Remove filters so they don't interfere with subsequent tests
		remove_filter('frm_to_email', array( $this, 'add_to_emails' ), 10 );
		remove_filter( 'frm_email_subject', array( $this, 'change_email_subject' ), 10 );

		$mock_email = end( $GLOBALS['phpmailer']->mock_sent );

		$this->check_recipients( $expected, $mock_email );
		$this->check_senders( $expected, $mock_email );
		$this->check_subject( $expected, $mock_email );
		$this->check_message_body( $expected, $mock_email );
		$this->check_content_type( $expected, $mock_email );
	}



}
