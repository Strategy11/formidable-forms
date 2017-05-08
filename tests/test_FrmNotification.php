<?php

/**
 * @group notification
 * TODO: test attachments
 */
class WP_Test_FrmNotification extends FrmUnitTest {

	public function test_class_is_tested() {
		$this->assertTrue( true );
	}

	/**
	 * @covers FrmNotification::trigger_email
	 */
	public function test_trigger_email(){
		// get the imported form with the email action
		$form = $this->factory->form->get_object_by_id( $this->contact_form_key );

		// get the email settings
		$actions = FrmFormAction::get_action_for_form( $form->id, 'email' );
		$this->assertNotEmpty( $actions );

		$user_factory = new WP_UnitTest_Factory_For_User();
		$args = array(
			'user_login' => 'email_user',
			'user_pass' => 'email_pass',
			'user_email' => 'emailtest@mail.com'
		);
		$user_factory->create_object( $args );
		$this->set_current_user_to_username( 'email_user' );

		$entry_data = $this->factory->field->generate_entry_array( $form );
		$entry_id = $this->factory->entry->create( $entry_data );

		$entry = FrmEntry::getOne( $entry_id, true );
		$this->assertNotEmpty( $entry );

		foreach ( $actions as $action ) {
			$this->check_email_one( $action, $entry, $form );
			$this->_check_email_two( $action, $entry, $form );
			$this->check_email_three( $action, $entry, $form );
			$this->check_email_four( $action, $entry, $form );
			$this->check_email_five( $action, $entry, $form );
		}
	}

	/**
	 * Tests the following:
	 * To: admin@example.org
	 * CC:
	 * BCC:
	 * Reply_to: [x] where x is the ID of an email field
	 * From: [sitename] <[admin_email]>
	 * Subject:
	 * Message: [default-message]
	 * Inc_user_info: false
	 * Plaint_text: false
	 *
	 * @param $action
	 * @param $entry
	 * @param $form
	 */
	private function check_email_one( $action, $entry, $form ) {
		$pass_entry = clone $entry;

		$expected = array(
			'to' => array( $action->post_content['email_to'], '' ),
			'cc' => array(),
			'bcc' => array(),
			'from' => FrmAppHelper::site_name() . ' <' . get_option('admin_email') . '>',
			'reply_to' => get_option('admin_email'),
			'subject' => self::prepare_subject( $form->name . ' Form submitted on ' . FrmAppHelper::site_name() ),
			'body' =>  FrmEntryFormat::show_entry( array( 'id' => $entry->id, 'entry' => $pass_entry ) ),
			'content_type' => 'Content-Type: text/html; charset=UTF-8',
		);

		self::prepare_body( $expected['body'] );

		FrmNotification::trigger_email( $action, $entry, $form );

		$mock_email = end( $GLOBALS['phpmailer']->mock_sent );

		// Remove line breaks for comparison
		$expected['body'] = preg_replace( '/\n/', '', $expected['body'] );
		$mock_email['body'] = preg_replace( '/\n/', '', $mock_email['body'] );

		$this->assertSame( $expected['to'], $mock_email['to'][0] );
		$this->assertSame( $expected['cc'], $mock_email['cc'] );
		$this->assertNotContains( 'Cc:', $mock_email['header'] );
		$this->assertSame( $expected['bcc'], $mock_email['bcc'] );
		$this->assertNotContains( 'Bcc:', $mock_email['header'] );
		$this->assertContains( 'From: ' . $expected['from'], $mock_email['header'] );
		$this->assertContains( 'Reply-To: ' . $expected['reply_to'], $mock_email['header'] );
		if ( isset( $mock_email['subject'] ) ) {
			$this->assertSame( $expected[ 'subject' ], $mock_email[ 'subject' ] );
		}
		$this->assertSame( $expected['body'], $mock_email['body'] );
		$this->assertContains( $expected['content_type'], $mock_email['header'] );
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
	 * @param $action
	 * @param $entry
	 * @param $form
	 */
	private function _check_email_two( $action, $entry, $form ) {
		$entry_clone = clone $entry;
		$action_clone = clone $action;
		$expected = array();
		$admin_email = get_option('admin_email');

		// To
		$user_field_id = FrmField::get_id_by_key( 'user14' );
		$this->assertNotFalse( $user_field_id, 'The UserID field is not retrieved with key user14.' );
		$this->assertArrayHasKey( $user_field_id, $entry_clone->metas, 'The UserID value is empty. A user may not have been set when the entry was created.' );
		$action_clone->post_content['email_to'] = 'Name [' . $user_field_id . '], Name2<jamie@test.com>, [admin_email]';
		$user = wp_get_current_user();
		$expected['to'] = array( array( $user->user_email, 'Name' ), array( 'jamie@test.com', 'Name2' ), array( $admin_email, '' ) );

		// CC
		$action_clone->post_content['cc'] = '"Jamie Wahlin" test@mail.com';
		$expected['cc'] = array( array( 'test@mail.com', 'Jamie Wahlin' ) );

		// BCC
		$name_id = FrmField::get_id_by_key( 'qh4icy3' );
		$entry_clone->metas[ $name_id ] = 'Jamie Wahlin';
		$action_clone->post_content['bcc'] = '[if ' . $name_id . ' equals="Jamie Wahlin"]jw@test.com[/if ' . $name_id . ']';
		$expected['bcc'] = array( array( 'jw@test.com', '' ) );

		// Subject
		$action_clone->post_content['email_subject'] = 'Submission from [' . $name_id . ']';
		$expected['subject'] = self::prepare_subject( 'Submission from Jamie Wahlin' );

		// From
		$action_clone->post_content['from'] = '';
		$expected['from'] = FrmAppHelper::site_name() . ' <' . $admin_email . '>';

		// Reply to
		$action_clone->post_content['reply_to'] = 'Reply Name';
		$expected['reply_to'] = 'Reply Name <' . get_option('admin_email') . '>';

		// Body - set plain text to true
		$action_clone->post_content['plain_text'] = true;
		$expected['body'] = FrmEntryFormat::show_entry( array( 'id' => $entry_clone->id, 'entry' => $entry_clone, 'plain_text' => true ) );
		self::prepare_body( $expected['body'] );

		// Content type
		$expected['content_type'] = 'Content-Type: text/plain; charset=UTF-8';

		FrmNotification::trigger_email( $action_clone, $entry_clone, $form );

		$mock_email = end( $GLOBALS['phpmailer']->mock_sent );


		$this->assertSame( $expected['to'], $mock_email['to'], 'To address is not set correctly when using User ID field.' );
		$this->assertSame( $expected['cc'], $mock_email['cc'], 'CC not set correctly when using User ID field' );
		$this->assertEquals( $expected['bcc'], $mock_email['bcc'], 'BCC not set correctly when conditional statement with quotes' );
		$this->assertContains( 'From: ' . $expected['from'], $mock_email['header'] );
		$this->assertContains( 'Reply-To: ' . $expected['reply_to'], $mock_email['header'] );
		if ( isset( $mock_email['subject'] ) ) {
			$this->assertSame( $expected[ 'subject' ], $mock_email[ 'subject' ] );
		}
		$this->assertSame( $expected['body'], $mock_email['body'], 'A plain text email body does not have the expected content.' );
		$this->assertContains( $expected['content_type'], $mock_email['header'] );
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
	 * Message: [default-message]// TODO: change from here down
	 * Inc_user_info: true
	 * Plain_text: false
	 * Attachments: // TODO
	 *
	 * @param $action
	 * @param $entry
	 * @param $form
	 */
	private function check_email_three( $action, $entry, $form ) {
		$entry_clone = clone $entry;
		$action_clone = clone $action;
		$expected = array();

		// To
		$user_field_id = FrmField::get_id_by_key( 'user14' );
		$this->assertNotFalse( $user_field_id, 'The UserID field is not retrieved with key user14.' );
		$this->assertArrayHasKey( $user_field_id, $entry_clone->metas, 'The UserID value is empty. A user may not have been set when the entry was created.' );
		$action_clone->post_content['email_to'] = 'Name test1@mail.com, Name2<test2@mail.com>';
		$user = wp_get_current_user();
		$expected['to'] = array( array( 'test1@mail.com', 'Name' ), array( 'test2@mail.com', 'Name2' ), array( 'test3@mail.com', '' ) );
		add_filter('frm_to_email', 'WP_Test_FrmNotification::add_to_emails', 10, 4 );

		// CC
		$action_clone->post_content['cc'] = '"Jamie Wahlin" <testcc1@mail.com>,';
		$action_clone->post_content['cc'] .= '[' . $user_field_id . ' show="display_name"] <[' . $user_field_id . ' show="user_email"]>';
		$expected['cc'] = array( array( 'testcc1@mail.com', 'Jamie Wahlin' ), array( $user->user_email, $user->display_name ) );

		// BCC
		$name_id = FrmField::get_id_by_key( 'qh4icy3' );
		$entry_clone->metas[ $name_id ] = 'Jamie Wahlin';
		$action_clone->post_content['bcc'] = '[if ' . $name_id . ' equals="Jamie Wahlin"]testbcc1@mail.com[/if ' . $name_id . '], "Tester test" testbcc2@mail.com';
		$expected['bcc'] = array( array( 'testbcc1@mail.com', '' ), array( 'testbcc2@mail.com', 'Tester test' ) );

		// Subject
		$action_clone->post_content['email_subject'] = 'Original subject';
		$expected['subject'] = self::prepare_subject( 'New subject' );
		add_filter( 'frm_email_subject', 'WP_Test_FrmNotification::change_email_subject', 10, 2 );

		// From
		$action_clone->post_content['from'] = 'Name';
		$expected['from'] = 'Name <' . get_option('admin_email') . '>';

		// Reply to
		$action_clone->post_content['reply_to'] = '"Reply To" <[user14]>';
		$expected['reply_to'] = 'Reply To <' . $user->user_email . '>';

		// Body - set inc_user_info to true
		$action_clone->post_content['inc_user_info'] = true;
		$expected['body'] = FrmEntryFormat::show_entry( array( 'id' => $entry_clone->id, 'entry' => $entry_clone, 'user_info' => true ) );
		self::prepare_body( $expected['body'] );

		// Content type
		$expected['content_type'] = 'Content-Type: text/html; charset=UTF-8';

		FrmNotification::trigger_email( $action_clone, $entry_clone, $form );

		// Remove filters so they don't interfere with subsequent tests
		remove_filter('frm_to_email', 'WP_Test_FrmNotification::add_to_emails', 10 );
		remove_filter( 'frm_email_subject', 'WP_Test_FrmNotification::change_email_subject', 10 );

		$mock_email = end( $GLOBALS['phpmailer']->mock_sent );

		// Remove line breaks from body for comparison
		$expected['body'] = preg_replace( '/\n/', '', $expected['body'] );
		$mock_email['body'] = preg_replace( '/\n/', '', $mock_email['body'] );

		$this->assertSame( $expected['to'], $mock_email['to'], 'To address is not set correctly.' );
		$this->assertSame( $expected['cc'], $mock_email['cc'], 'CC not set correctly' );
		$this->assertEquals( $expected['bcc'], $mock_email['bcc'], 'BCC not set correctly' );
		$this->assertContains( 'From: ' . $expected['from'], $mock_email['header'] );
		$this->assertContains( 'Reply-To: ' . $expected['reply_to'], $mock_email['header'] );
		if ( isset( $mock_email['subject'] ) ) {
			$this->assertSame( $expected[ 'subject' ], $mock_email[ 'subject' ] );
		}
		$this->assertSame( $expected['body'], $mock_email['body'], 'Mail body does not have the expected content.' );
		$this->assertContains( $expected['content_type'], $mock_email['header'] );
	}

	/**
	 * Tests userID field ID and key, multiple to addresses, double quotes, user-defined subject
	 *
	 * To: Name test1@mail.com, "Name Two"<test2@mail.com>, [admin_email] (frm_send_separate_emails hook used)
	 * CC: "Jamie Wahlin" test@mail.com
	 * BCC:
	 * Reply_to:
	 * From: testfrom@mail.com
	 * Subject: Submission from test
	 * Message: [default-message]
	 * Inc_user_info: false
	 * Plain_text: true
	 *
	 * @param $action
	 * @param $entry
	 * @param $form
	 */
	private function check_email_four( $action, $entry, $form ) {
		$entry_clone = clone $entry;
		$action_clone = clone $action;
		$expected = array();

		// To
		$action_clone->post_content['email_to'] = 'Name test1@mail.com, "Name Two"<test2@mail.com>';
		$expected['to']['first'] = array( array( 'test1@mail.com', 'Name' ) );
		$expected['to']['second'] = array( array( 'test2@mail.com', 'Name Two' ) );
		add_filter( 'frm_send_separate_emails', 'WP_Test_FrmNotification::send_separate_emails', 10, 2 );

		// CC
		$action_clone->post_content['cc'] = '"Jamie Wahlin" test@mail.com';
		$expected['cc'] = array( array( 'test@mail.com', 'Jamie Wahlin' ) );

		// BCC
		$action_clone->post_content['bcc'] = '';
		$expected['bcc'] = array();

		// Subject
		$action_clone->post_content['email_subject'] = 'Submission from test';
		$expected['subject'] = self::prepare_subject( 'Submission from test' );

		// From
		$action_clone->post_content['from'] = 'testfrom@mail.com';
		$expected['from'] = FrmAppHelper::site_name() . ' <testfrom@mail.com>';

		// Reply to
		$action_clone->post_content['reply_to'] = '';
		$expected['reply_to'] = FrmAppHelper::site_name() . ' <testfrom@mail.com>';

		// Body - set plain text to true
		$action_clone->post_content['plain_text'] = true;
		$expected['body'] = FrmEntryFormat::show_entry( array( 'id' => $entry_clone->id, 'entry' => $entry_clone, 'plain_text' => true ) );
		self::prepare_body( $expected['body'] );

		// Content type
		$expected['content_type'] = 'Content-Type: text/plain; charset=UTF-8';

		FrmNotification::trigger_email( $action_clone, $entry_clone, $form );

		remove_filter( 'frm_send_separate_emails', 'WP_Test_FrmNotification::send_separate_emails', 10 );

		$email_count = count( $GLOBALS['phpmailer']->mock_sent );
		$previous_mock_email = $GLOBALS['phpmailer']->mock_sent[ $email_count - 2 ];
		$mock_email = end( $GLOBALS['phpmailer']->mock_sent );

		$this->assertSame( $expected['to']['first'], $previous_mock_email['to'], 'To address is not set correctly when using User ID field.' );
		$this->assertSame( $expected['to']['second'], $mock_email['to'], 'To address is not set correctly when using User ID field.' );
		$this->assertSame( $expected['cc'], $mock_email['cc'], 'CC not set correctly when using User ID field' );
		$this->assertEquals( $expected['bcc'], $mock_email['bcc'], 'BCC not set correctly when conditional statement with quotes' );
		$this->assertContains( 'From: ' . $expected['from'], $mock_email['header'] );
		$this->assertContains( 'Reply-To: ' . $expected['reply_to'], $mock_email['header'] );
		if ( isset( $mock_email['subject'] ) ) {
			$this->assertSame( $expected[ 'subject' ], $mock_email[ 'subject' ] );
		}
		$this->assertSame( $expected['body'], $mock_email['body'], 'A plain text email body does not have the expected content.' );
		$this->assertContains( $expected['content_type'], $mock_email['header'] );
	}

	/**
	 * Tests userID field ID and key, multiple to addresses, double quotes, user-defined subject
	 *
	 * To: [admin_email]
	 * CC: "Jamie Wahlin" test@mail.com
	 * BCC:
	 * Reply_to: [x] where x is the ID of an email field
	 * From: test@yahoo.com
	 * Subject: Submission from [x]
	 * Message: [default-message]
	 * Inc_user_info: false
	 * Plain_text: true
	 *
	 * @param $action
	 * @param $entry
	 * @param $form
	 */
	private function check_email_five( $action, $entry, $form ) {
		$entry_clone = clone $entry;
		$action_clone = clone $action;
		$expected = array();
		$admin_email = get_option('admin_email');

		// To
		$action_clone->post_content['email_to'] = '[admin_email]';
		$expected['to'] = array( array( $admin_email, '' ) );

		// CC
		$action_clone->post_content['cc'] = '"Jamie Wahlin" test@mail.com';
		$expected['cc'] = array( array( 'test@mail.com', 'Jamie Wahlin' ) );

		// BCC
		$action_clone->post_content['bcc'] = '';
		$expected['bcc'] = array();

		// Subject
		$name_id = FrmField::get_id_by_key( 'qh4icy3' );
		$entry_clone->metas[ $name_id ] = 'Jamie Wahlin';
		$action_clone->post_content['email_subject'] = 'Submission from [' . $name_id . ']';
		$expected['subject'] = self::prepare_subject( 'Submission from Jamie Wahlin' );

		// From
		$action_clone->post_content['from'] = '"Yahoo" test@yahoo.com';
		$sitename = strtolower( FrmAppHelper::get_server_value( 'SERVER_NAME' ) );
		if ( substr( $sitename, 0, 4 ) == 'www.' ) {
			$sitename = substr( $sitename, 4 );
		}
		$expected['from'] = 'Yahoo <wordpress@' . $sitename. '>';

		// Reply to
		$expected['reply_to'] = $admin_email;

		// Body - set plain text to true
		$action_clone->post_content['plain_text'] = true;
		$expected['body'] = FrmEntryFormat::show_entry( array( 'id' => $entry_clone->id, 'entry' => $entry_clone, 'plain_text' => true ) );
		self::prepare_body( $expected['body'] );

		// Content type
		$expected['content_type'] = 'Content-Type: text/plain; charset=UTF-8';

		FrmNotification::trigger_email( $action_clone, $entry_clone, $form );

		$mock_email = end( $GLOBALS['phpmailer']->mock_sent );


		$this->assertSame( $expected['to'], $mock_email['to'], 'To address is not set correctly when using User ID field.' );
		$this->assertSame( $expected['cc'], $mock_email['cc'], 'CC not set correctly when using User ID field' );
		$this->assertEquals( $expected['bcc'], $mock_email['bcc'], 'BCC not set correctly when conditional statement with quotes' );
		$this->assertContains( 'From: ' . $expected['from'], $mock_email['header'] );
		$this->assertContains( 'Reply-To: ' . $expected['reply_to'], $mock_email['header'] );
		if ( isset( $mock_email['subject'] ) ) {
			$this->assertSame( $expected[ 'subject' ], $mock_email[ 'subject' ] );
		}
		$this->assertSame( $expected['body'], $mock_email['body'], 'A plain text email body does not have the expected content.' );
		$this->assertContains( $expected['content_type'], $mock_email['header'] );
	}

	private function prepare_subject( $subject ) {
		$subject = wp_specialchars_decode( strip_tags( stripslashes( $subject ) ), ENT_QUOTES );
		$charset = get_option('blog_charset');
		return '=?' . $charset . '?B?' . base64_encode( $subject ) . '?=';
	}

	private function prepare_body( &$body ) {
		$body = preg_replace( '/\r/', '', $body );
	}

	public static function add_to_emails(  $to_emails, $values, $form_id, $args ) {
		$to_emails[] = 'test3@mail.com';
		$to_emails[] = '1231231234';
		return $to_emails;
	}

	public static function change_email_subject(  $subject, $args ) {
		$subject = 'New subject';
		return $subject;
	}

	public static function send_separate_emails( $is_single, $args ) {
		return true;
	}
}