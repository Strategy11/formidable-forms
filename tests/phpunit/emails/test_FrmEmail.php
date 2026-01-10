<?php

/**
 * @group emails
 * @group free
 */
class test_FrmEmail extends FrmUnitTest {

	/**
	 * @var string
	 */
	protected $email_form_key = 'free_field_types';

	/**
	 * @var string
	 */
	protected $name_field_key = 'free-text-field';

	/**
	 * @var string
	 */
	protected $email_field_key = 'free-email-field';

	/**
	 * @var stdClass
	 */
	protected $contact_form;

	/**
	 * @var stdClass
	 */
	protected $email_action;

	/**
	 * @var stdClass
	 */
	protected $entry;

	public static function wpSetUpBeforeClass() {
		$_POST = array();
		self::empty_tables();
		self::frm_install();
	}

	public function setUp(): void {
		parent::setUp();

		$this->contact_form = $this->factory->form->get_object_by_id( $this->email_form_key );
		$this->email_action = $this->get_email_action_for_form( $this->contact_form->id );
		$this->entry        = $this->create_entry( $this->contact_form );
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
	 * @covers FrmNotification::trigger_email
	 */
	public function test_trigger_email_one() {
		$pass_entry = clone $this->entry;

		$expected = array(
			'to'           => array( array( $this->email_action->post_content['email_to'], '' ) ),
			'cc'           => array(),
			'bcc'          => array(),
			'from'         => FrmAppHelper::site_name() . ' <' . get_option( 'admin_email' ) . '>',
			'reply_to'     => get_option( 'admin_email' ),
			'subject'      => self::prepare_subject( $this->contact_form->name . ' Form submitted on ' . FrmAppHelper::site_name() ),
			'body'         => FrmEntriesController::show_entry_shortcode(
				array(
					'id'    => $this->entry->id,
					'entry' => $pass_entry,
				)
			),
			'content_type' => 'Content-Type: text/html; charset=UTF-8',
		);

		FrmNotification::trigger_email( $this->email_action, $this->entry, $this->contact_form );

		$mock_email = end( $GLOBALS['phpmailer']->mock_sent );

		$this->check_recipients( $expected, $mock_email, 'no_cc', 'no_bcc' );
		$this->check_senders( $expected, $mock_email );
		$this->check_subject( $expected, $mock_email );
		$this->check_message_body( $expected, $mock_email );
		$this->check_content_type( $expected, $mock_email );
	}

	/**
	 * Tests multiple to addresses, double quotes, user-defined subject
	 *
	 * To: Name2<jamie@test.com>, [admin_email]
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
	public function test_trigger_email_two() {
		$entry_clone = clone $this->entry;
		$expected    = array();
		$admin_email = get_option( 'admin_email' );

		// Adjust entry values
		$name_id                        = FrmField::get_id_by_key( $this->name_field_key );
		$entry_clone->metas[ $name_id ] = 'Test Testerson';

		// To
		$this->email_action->post_content['email_to'] = 'Name2<jamie@test.com>, [admin_email]';
		$expected['to']                               = array( array( 'jamie@test.com', 'Name2' ), array( $admin_email, '' ) );

		// CC
		$this->email_action->post_content['cc'] = '"Jamie Wahlin" test@mail.com';
		$expected['cc']                         = array( array( 'test@mail.com', 'Jamie Wahlin' ) );

		// BCC
		$this->email_action->post_content['bcc'] = 'jw@test.com';
		$expected['bcc']                         = array( array( 'jw@test.com', '' ) );

		// Subject
		$this->email_action->post_content['email_subject'] = 'Submission from [' . $name_id . ']';
		$expected['subject']                               = self::prepare_subject( 'Submission from Test Testerson' );

		// From
		$this->email_action->post_content['from'] = '';
		$expected['from']                         = FrmAppHelper::site_name() . ' <' . $admin_email . '>';

		// Reply to
		$this->email_action->post_content['reply_to'] = 'Reply Name';
		$expected['reply_to']                         = 'Reply Name <' . get_option( 'admin_email' ) . '>';

		// Body - set plain text to true
		$this->email_action->post_content['plain_text'] = true;
		$expected['body']                               = FrmEntriesController::show_entry_shortcode(
			array(
				'id'         => $entry_clone->id,
				'entry'      => $entry_clone,
				'plain_text' => true,
			)
		);

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
	 * Tests multiple cc and bcc addresses
	 *
	 * To: Name test1@mail.com, Name2<test2@mail.com>, add test3@mail.com and 1231231234 with hook
	 * CC: "Jamie Wahlin" <testcc1@mail.com>,[x] <[y]>
	 * BCC: testbcc1@mail.com, "Tester test" testbcc2@mail.com
	 * Reply_to: "Reply To" <[emailFieldKey]>
	 * From: "Name"
	 * Subject: Set to "New Subject" with filter
	 * Message: [default-message]
	 * Inc_user_info: true
	 * Plain_text: false
	 *
	 * @covers FrmNotification::trigger_email
	 */
	public function test_trigger_email_three() {
		$entry_clone = clone $this->entry;
		$expected    = array();
		$name_id     = FrmField::get_id_by_key( $this->name_field_key );
		$email_id    = FrmField::get_id_by_key( $this->email_field_key );

		// Adjust entry values
		$entry_clone->metas[ $name_id ]  = 'Test Testerson';
		$entry_clone->metas[ $email_id ] = 'tester@mail.com';

		// To
		$this->email_action->post_content['email_to'] = 'Name test1@mail.com, Name2<test2@mail.com>';
		$expected['to']                               = array( array( 'test1@mail.com', 'Name' ), array( 'test2@mail.com', 'Name2' ), array( 'test3@mail.com', '' ) );
		add_filter( 'frm_to_email', array( $this, 'add_to_emails' ), 10, 4 );

		// CC
		$this->email_action->post_content['cc']  = '"Jamie Wahlin" <testcc1@mail.com>,';
		$this->email_action->post_content['cc'] .= '[' . $name_id . '] <[' . $email_id . ']>';
		$expected['cc']                          = array( array( 'testcc1@mail.com', 'Jamie Wahlin' ), array( 'tester@mail.com', 'Test Testerson' ) );

		// BCC
		$this->email_action->post_content['bcc'] = 'testbcc1@mail.com, "Tester test" testbcc2@mail.com';
		$expected['bcc']                         = array( array( 'testbcc1@mail.com', '' ), array( 'testbcc2@mail.com', 'Tester test' ) );

		// Subject
		$this->email_action->post_content['email_subject'] = 'Original subject';
		$expected['subject']                               = self::prepare_subject( 'New subject' );
		add_filter( 'frm_email_subject', array( $this, 'change_email_subject' ), 10, 2 );

		// From
		$this->email_action->post_content['from'] = 'Name';
		$expected['from']                         = 'Name <' . get_option( 'admin_email' ) . '>';

		// Reply to
		$this->email_action->post_content['reply_to'] = '"Reply To" <[' . $this->email_field_key . ']>';
		$expected['reply_to']                         = 'Reply To <tester@mail.com>';

		// Body - set inc_user_info to true
		$this->email_action->post_content['inc_user_info'] = true;
		$expected['body']                                  = FrmEntriesController::show_entry_shortcode(
			array(
				'id'        => $entry_clone->id,
				'entry'     => $entry_clone,
				'user_info' => true,
			)
		);

		// Content type
		$expected['content_type'] = 'Content-Type: text/html; charset=UTF-8';

		FrmNotification::trigger_email( $this->email_action, $entry_clone, $this->contact_form );

		// Remove filters so they don't interfere with subsequent tests
		remove_filter( 'frm_to_email', array( $this, 'add_to_emails' ), 10 );
		remove_filter( 'frm_email_subject', array( $this, 'change_email_subject' ), 10 );

		$mock_email = end( $GLOBALS['phpmailer']->mock_sent );

		$this->check_recipients( $expected, $mock_email );
		$this->check_senders( $expected, $mock_email );
		$this->check_subject( $expected, $mock_email );
		$this->check_message_body( $expected, $mock_email );
		$this->check_content_type( $expected, $mock_email );
	}

	/**
	 * Tests multiple to addresses, double quotes, custom subject, no reply-to
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
	 * @covers FrmNotification::trigger_email
	 */
	public function test_trigger_email_four() {
		$entry_clone = clone $this->entry;
		$expected    = array();

		// To
		$this->email_action->post_content['email_to'] = 'Name test1@mail.com, "Name Two"<test2@mail.com>';
		$expected['to']['first']                      = array( array( 'test1@mail.com', 'Name' ) );
		$expected['to']['second']                     = array( array( 'test2@mail.com', 'Name Two' ) );
		add_filter( 'frm_send_separate_emails', array( $this, 'send_separate_emails' ), 10, 2 );

		// CC
		$this->email_action->post_content['cc'] = '"Jamie Wahlin" test@mail.com';
		$expected['cc']                         = array( array( 'test@mail.com', 'Jamie Wahlin' ) );

		// BCC
		$this->email_action->post_content['bcc'] = '';
		$expected['bcc']                         = array();

		// Subject
		$this->email_action->post_content['email_subject'] = 'Submission from test';
		$expected['subject']                               = self::prepare_subject( 'Submission from test' );

		// From
		$this->email_action->post_content['from'] = 'testfrom@mail.com';
		$expected['from']                         = FrmAppHelper::site_name() . ' <testfrom@mail.com>';

		// Reply to
		$this->email_action->post_content['reply_to'] = '';
		$expected['reply_to']                         = 'testfrom@mail.com';

		// Body - set plain text to true
		$this->email_action->post_content['plain_text'] = true;
		$expected['body']                               = FrmEntriesController::show_entry_shortcode(
			array(
				'id'         => $entry_clone->id,
				'entry'      => $entry_clone,
				'plain_text' => true,
			)
		);

		// Content type
		$expected['content_type'] = 'Content-Type: text/plain; charset=UTF-8';

		FrmNotification::trigger_email( $this->email_action, $entry_clone, $this->contact_form );

		remove_filter( 'frm_send_separate_emails', array( $this, 'send_separate_emails' ), 10 );

		$email_count         = count( $GLOBALS['phpmailer']->mock_sent );
		$previous_mock_email = $GLOBALS['phpmailer']->mock_sent[ $email_count - 2 ];
		$mock_email          = end( $GLOBALS['phpmailer']->mock_sent );

		$this->assertSame( $expected['to']['first'], $previous_mock_email['to'], 'To address is not set correctly when using User ID field.' );
		$this->assertSame( $expected['to']['second'], $mock_email['to'], 'To address is not set correctly when using User ID field.' );
		$this->assertSame( $expected['cc'], $mock_email['cc'], 'CC not set correctly when using User ID field' );
		$this->assertEquals( $expected['bcc'], $mock_email['bcc'], 'BCC not set correctly when conditional statement with quotes' );

		$this->check_senders( $expected, $mock_email );
		$this->check_subject( $expected, $mock_email );
		$this->check_message_body( $expected, $mock_email );
		$this->check_content_type( $expected, $mock_email );
	}

	/**
	 * Tests userID field ID and key, multiple to addresses, double quotes, user-defined subject
	 *
	 * To: [admin_email]
	 * CC: "Jamie Wahlin" test@mail.com
	 * BCC:
	 * Reply_to: [admin_email]
	 * From: "Yahoo" test@yahoo.com
	 * Subject: Submission from [x]
	 * Message: [default-message]
	 * Inc_user_info: false
	 * Plain_text: true
	 *
	 * @covers FrmNotification::trigger_email
	 */
	public function test_trigger_email_five() {
		$entry_clone = clone $this->entry;
		$expected    = array();
		$admin_email = get_option( 'admin_email' );

		// Update entry values
		$name_id                        = FrmField::get_id_by_key( $this->name_field_key );
		$entry_clone->metas[ $name_id ] = 'Test Testerson';

		// To
		$this->email_action->post_content['email_to'] = '[admin_email]';
		$expected['to']                               = array( array( $admin_email, '' ) );

		// CC
		$this->email_action->post_content['cc'] = '"Jamie Wahlin" test@mail.com';
		$expected['cc']                         = array( array( 'test@mail.com', 'Jamie Wahlin' ) );

		// BCC
		$this->email_action->post_content['bcc'] = '';
		$expected['bcc']                         = array();

		// Subject
		$this->email_action->post_content['email_subject'] = 'Submission from [' . $name_id . ']';
		$expected['subject']                               = self::prepare_subject( 'Submission from Test Testerson' );

		// From
		$this->email_action->post_content['from'] = '"Yahoo" test@yahoo.com';
		$sitename                                 = strtolower( FrmAppHelper::get_server_value( 'SERVER_NAME' ) );

		if ( str_starts_with( $sitename, 'www.' ) ) {
			$sitename = substr( $sitename, 4 );
		}

		$expected['from'] = 'Yahoo <wordpress@' . $sitename . '>';

		// Reply to
		$expected['reply_to'] = $admin_email;

		// Body - set plain text to true
		$this->email_action->post_content['plain_text'] = true;
		$expected['body']                               = FrmEntriesController::show_entry_shortcode(
			array(
				'id'         => $entry_clone->id,
				'entry'      => $entry_clone,
				'plain_text' => true,
			)
		);

		// Content type
		$expected['content_type'] = 'Content-Type: text/plain; charset=UTF-8';

		FrmNotification::trigger_email( $this->email_action, $entry_clone, $this->contact_form );

		$mock_email = end( $GLOBALS['phpmailer']->mock_sent );

		$this->check_recipients( $expected, $mock_email, 'yes_cc', 'no_bcc' );
		$this->check_senders( $expected, $mock_email );
		$this->check_subject( $expected, $mock_email );
		$this->check_message_body( $expected, $mock_email );
		$this->check_content_type( $expected, $mock_email );
	}

	/**
	 * Reply_to:
	 * From: [x] [y]
	 *
	 * @covers FrmNotification::trigger_email
	 */
	public function test_trigger_email_six() {
		$name_id                         = FrmField::get_id_by_key( $this->name_field_key );
		$email_id                        = FrmField::get_id_by_key( $this->email_field_key );
		$entry_clone                     = clone $this->entry;
		$entry_clone->metas[ $name_id ]  = 'Test Testerson';
		$entry_clone->metas[ $email_id ] = 'tester@mail.com';

		$this->email_action->post_content['from']     = '[' . $name_id . '] [' . $email_id . ']';
		$this->email_action->post_content['reply_to'] = '';

		$expected = array(
			'from'     => 'Test Testerson <tester@mail.com>',
			'reply_to' => 'tester@mail.com',
		);

		FrmNotification::trigger_email( $this->email_action, $entry_clone, $this->contact_form );

		$mock_email = end( $GLOBALS['phpmailer']->mock_sent );

		$this->check_senders( $expected, $mock_email );
	}

	protected function prepare_subject( $subject ) {
		return wp_specialchars_decode( strip_tags( stripslashes( $subject ) ), ENT_QUOTES );
	}

	protected function get_email_action_for_form( $form_id ) {
		$actions = FrmFormAction::get_action_for_form( $form_id, 'email' );
		$this->assertNotEmpty( $actions );

		return reset( $actions );
	}

	protected function create_entry( $form ) {
		$entry_data = $this->factory->field->generate_entry_array( $form );
		$entry_id   = $this->factory->entry->create( $entry_data );
		$entry      = FrmEntry::getOne( $entry_id, true );
		$this->assertNotEmpty( $entry );

		return $entry;
	}

	protected function check_recipients( $expected, $mock_email, $cc_status = 'yes_cc', $bcc_status = 'yes_bcc' ) {
		$this->assertSame( $expected['to'], $mock_email['to'], 'To does not match expected.' );
		$this->assertSame( $expected['cc'], $mock_email['cc'], 'CC does not match expected.' );
		$this->assertSame( $expected['bcc'], $mock_email['bcc'], 'BCC does not match expected.' );

		if ( $cc_status === 'no_cc' ) {
			$this->check_no_cc_included( $mock_email );
		}

		if ( $bcc_status === 'no_bcc' ) {
			$this->check_no_bcc_included( $mock_email );
		}
	}

	protected function check_senders( $expected, $mock_email ) {
		$this->assertNotFalse( strpos( $mock_email['header'], 'From: ' . $expected['from'] ), 'From does not match expected.' );
		$this->assertNotFalse( strpos( $mock_email['header'], 'Reply-To: ' . $expected['reply_to'] ), 'Reply-to does not match expected.' );
	}

	protected function check_subject( $expected, $mock_email ) {
		if ( isset( $mock_email['subject'] ) ) {
			$this->assertSame( $expected['subject'], $mock_email['subject'], 'Subject does not match expected.' );
		}
	}

	protected function check_message_body( $expected, $mock_email ) {
		// Remove line breaks from body for comparison
		$expected['body']   = preg_replace( "/\r|\n/", '', $expected['body'] );
		$mock_email['body'] = preg_replace( "/\r|\n/", '', $mock_email['body'] );

		$this->assertSame( $expected['body'], $mock_email['body'], 'Message body does not match expected.' );
	}

	protected function check_content_type( $expected, $mock_email ) {
		$this->assertNotFalse( strpos( $mock_email['header'], $expected['content_type'] ), 'Content type does not match expected.' );
	}

	protected function check_no_cc_included( $mock_email ) {
		$this->assertFalse( strpos( $mock_email['header'], 'Cc:' ), 'CC is included when it should not be.' );
	}

	protected function check_no_bcc_included( $mock_email ) {
		$this->assertFalse( strpos( $mock_email['header'], 'Bcc:' ), 'BCC is included when it should not be.' );
	}

	public function add_to_emails( $to_emails, $values, $form_id, $args ) {
		$to_emails[] = 'test3@mail.com';
		$to_emails[] = '1231231234';
		return $to_emails;
	}

	public function change_email_subject( $subject, $args ) {
		return 'New subject';
	}

	public function send_separate_emails( $is_single, $args ) {
		return true;
	}

	/**
	 * @covers FrmEmail::set_from
	 */
	public function test_set_from() {
		$default_email = get_option( 'admin_email' );
		$default_name  = FrmAppHelper::site_name();

		$from = array(
			'From name <[admin_email]>' => 'From name <' . $default_email . '>',
			''                          => $default_name . ' <' . $default_email . '>',
			'Name'                      => 'Name <' . $default_email . '>',
			'testfrom@example.com'      => $default_name . ' <testfrom@example.com>',
		);

		$this->check_private_properties( $from, 'from' );
	}

	/**
	 * @covers FrmEmail::set_reply_to
	 */
	public function test_set_reply_to() {
		$default_email = get_option( 'admin_email' );
		$reply_to      = array(
			'admin2.[admin_email]'          => 'admin2.' . $default_email,
			''                              => $default_email,
			'Reply Name'                    => 'Reply Name <' . $default_email . '>',
			'Reply To <tester@example.com>' => 'Reply To <tester@example.com>',
		);
		$this->check_private_properties( $reply_to, 'reply_to' );

		// create an entry with no email and then try to use its shortcode to get a reply_to value.
		// the default should use the from email, not the admin "default email".
		$email_field_key                             = 'free_field_types' === $this->contact_form->form_key ? 'free-email-field' : 'contact-email';
		$entry_data                                  = $this->factory->field->generate_entry_array( $this->contact_form );
		$email_field                                 = FrmField::getOne( $email_field_key );
		$entry_data['item_meta'][ $email_field->id ] = '';
		$entry_id                                    = $this->factory->entry->create( $entry_data );
		$entry                                       = FrmEntry::getOne( $entry_id, true );
		$action                                      = $this->email_action;
		$action->post_content['from']                = 'fromemail@example.com';
		$action->post_content['reply_to']            = '[' . $email_field_key . ']';
		$email                                       = new FrmEmail( $action, $entry, $this->contact_form );
		$actual                                      = $this->get_private_property( $email, 'reply_to' );
		$this->assertEquals( 'fromemail@example.com', $actual );
	}

	/**
	 * @covers FrmEmail::set_is_plain_text
	 */
	public function test_set_is_plain_text() {
		$settings = array(
			'0' => false,
			'1' => true,
		);
		$this->check_private_properties( $settings, 'plain_text', 'is_plain_text' );
	}

	/**
	 * @covers FrmEmail::set_include_user_info
	 */
	public function test_set_include_user_info() {
		$settings = array(
			'0' => false,
			'1' => true,
		);
		$this->check_private_properties( $settings, 'inc_user_info', 'include_user_info' );
	}

	/**
	 * @covers FrmEmail::set_content_type
	 */
	public function test_set_content_type() {
		$settings = array(
			'0' => 'text/html',
			'1' => 'text/plain',
		);
		$this->check_private_properties( $settings, 'plain_text', 'content_type' );
	}

	/**
	 * @covers FrmEmail::set_subject
	 */
	public function test_set_subject() {
		$name_id  = FrmField::get_id_by_key( $this->name_field_key );
		$default  = $this->contact_form->name . ' Form submitted on ' . FrmAppHelper::site_name();
		$settings = array(
			''                   => $default,
			'Original subject'   => 'Original subject',
			'[' . $name_id . ']' => $this->entry->metas[ $name_id ],
		);
		$this->check_private_properties( $settings, 'email_subject', 'subject' );
	}

	/**
	 * @covers FrmEmail::set_message
	 */
	public function test_set_message() {
		$name_id = FrmField::get_id_by_key( $this->name_field_key );
		$default = FrmEntriesHelper::replace_default_message(
			'[default-message]',
			array(
				'id'         => $this->entry->id,
				'entry'      => $this->entry,
				'plain_text' => $this->email_action->post_content['plain_text'],
				'user_info'  => $this->email_action->post_content['inc_user_info'],
			)
		);

		$settings = array(
			''                   => $default,
			'[default-message]'  => $default,
			'Original message'   => 'Original message',
			'[' . $name_id . ']' => $this->entry->metas[ $name_id ],
		);

		foreach ( $settings as $key => $expected ) {
			$settings[ $key ] = trim( wpautop( $expected, false ) );
		}

		$this->check_private_properties( $settings, 'email_message', 'message' );
	}

	/**
	 * @covers FrmEmail::add_autop
	 */
	public function test_add_autop() {
		$action                             = $this->email_action;
		$action->post_content['plain_text'] = '0';
		$messages                           = array(
			'<html><head><style>label{font-size:14px;font-weight:bold;padding-bottom:5px;}</style></head><body>
LINE 1<br>LINE 2<br></body></html>'
			=>
			'<html><head><style>label{font-size:14px;font-weight:bold;padding-bottom:5px;}</style></head><body><p>LINE 1<br />LINE 2</p></body></html>',
		);

		foreach ( $messages as $message => $expected ) {
			$action->post_content['email_message'] = $message;
			$email                                 = new FrmEmail( $action, $this->entry, $this->contact_form );
			$actual                                = $this->get_private_property( $email, 'message' );
			$this->assertEquals( $expected, $actual );
		}
	}

	/**
	 * @covers FrmEmail::set_message
	 */
	public function test_message_user_info() {
		$settings = array(
			array(
				'email_message' => 'Original',
				'inc_user_info' => 0,
				'compare'       => 'NotContains',
			),
			array(
				'email_message' => 'Original',
				'inc_user_info' => 1,
				'compare'       => 'Contains',
			),
		);

		$action = $this->email_action;

		foreach ( $settings as $setting ) {
			foreach ( $setting as $name => $value ) {
				$action->post_content[ $name ] = $value;
			}

			$email  = new FrmEmail( $action, $this->entry, $this->contact_form );
			$actual = $this->get_private_property( $email, 'message' );

			if ( $setting['compare'] === 'Contains' ) {
				$this->assertNotFalse( strpos( $actual, 'Referrer:' ) );
			} else {
				$this->assertFalse( strpos( $actual, 'Referrer:' ) );
			}
		}
	}

	/**
	 * @covers FrmEmail::set_message
	 */
	public function test_plain_text_message() {
		$action                                = $this->email_action;
		$action->post_content['email_message'] = 'Value <br/>with HTML';

		$settings = array(
			0 => '<p>Value <br />with HTML</p>', // This is testing HTML, not plain text because it's indexed by 0 which is used for the plain_text setting.
			1 => 'Value with HTML',
		);

		foreach ( $settings as $setting => $expected ) {
			$action->post_content['plain_text'] = $setting;
			$email                              = new FrmEmail( $action, $this->entry, $this->contact_form );
			$actual                             = $this->get_private_property( $email, 'message' );
			$this->assertEquals( $actual, $expected );
		}
	}

	private function check_private_properties( $settings, $setting_name, $property = '' ) {
		if ( ! $property ) {
			$property = $setting_name;
		}

		$action = $this->email_action;

		foreach ( $settings as $setting => $expected ) {
			$action->post_content[ $setting_name ] = $setting;
			$email                                 = new FrmEmail( $action, $this->entry, $this->contact_form );
			$actual                                = $this->get_private_property( $email, $property );
			$this->assertEquals( $expected, $actual );
		}
	}
}
