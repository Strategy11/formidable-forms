<?php

/**
 * @since 2.04
 *
 * @group emails
 * @group email-attachment
 */
class test_FrmEmailWithAttachment extends FrmUnitTest {

	/**
	 * @var stdClass
	 */
	private $form = null;

	/**
	 * @var stdClass
	 */
	private $email_action = null;

	/**
	 * @var stdClass
	 */
	private $entry = null;

	public function setUp() {
		parent::setUp();

		$this->form = FrmForm::getOne( 'file-upload' );
		$this->email_action = $this->get_email_action_for_form();
		$this->entry = FrmEntry::getOne( 'many_files_key', true );
	}

	/**
	 * Tests attachments from a single file upload field
	 *
	 * @since 2.04
	 * @covers FrmProNotification::add_attachments
	 */
	public function test_single_file_upload_attachment(){
		FrmNotification::trigger_email( $this->email_action, $this->entry, $this->form );

		$mock_email = end( $GLOBALS['phpmailer']->mock_sent );

		$this->check_to( $mock_email );
		$this->check_for_multi_part_message( $mock_email['body'] );
		$this->check_single_file_upload_attachment( $mock_email['body'] );
	}

	/**
	 * Tests attachments from a multi file upload field
	 *
	 * @since 2.04
	 * @covers FrmProNotification::add_attachments
	 */
	public function test_multi_file_upload_attachment(){
		FrmNotification::trigger_email( $this->email_action, $this->entry, $this->form );

		$mock_email = end( $GLOBALS['phpmailer']->mock_sent );

		$this->check_to( $mock_email );
		$this->check_for_multi_part_message( $mock_email['body'] );
		$this->check_multi_file_upload_attachment( $mock_email['body'] );
	}

	/**
	 * Tests attachments from a repeating multi file upload field
	 *
	 * @since 2.04
	 * @covers FrmProNotification::add_attachments
	 * @group repeating-multi-file-upload-attachment
	 */
	public function test_repeating_multi_file_upload_attachment(){
		FrmNotification::trigger_email( $this->email_action, $this->entry, $this->form );

		$mock_email = end( $GLOBALS['phpmailer']->mock_sent );

		$this->check_to( $mock_email );
		$this->check_for_multi_part_message( $mock_email['body'] );
		$this->check_repeating_multi_file_upload_attachment( $mock_email['body'] );
	}

	private function get_email_action_for_form() {
		$actions = FrmFormAction::get_action_for_form( $this->form->id, 'email' );
		$this->assertNotEmpty( $actions );

		return reset( $actions );
	}

	private function check_to( $mock_email ) {
		$expected_to = array( array( 'admin@example.org', '' ) );
		$this->assertSame( $expected_to, $mock_email['to'] );
	}

	private function check_for_multi_part_message( $body ) {
		$multi_part_message = 'This is a multi-part message in MIME format.';
		$this->assertContains( $multi_part_message, $body );
	}

	private function check_single_file_upload_attachment( $body ) {
		$file_field_id = FrmField::get_id_by_key( 'file_upload_single' );
		$media_id = $this->entry->metas[ $file_field_id ];
		$file_name = FrmProFieldsHelper::get_displayed_file_html( $media_id, 'thumbnail', array( 'show_filename' => true ) );

		$content_type = 'Content-Type: text/xml; name="' . $file_name . '"';
		$this->assertContains( $content_type, $body );

		$content_disposition = 'Content-Disposition: attachment; filename=' . $file_name;
		$this->assertContains( $content_disposition , $body );

	}

	private function check_multi_file_upload_attachment( $body ) {
		$check_files = array(
			'goal-form.png',
			'goal-progress.png',
			'new-graph-types1.png',
		);

		$this->check_for_attachments( $check_files, $body );
	}

	private function check_repeating_multi_file_upload_attachment( $body ) {
		$check_files = array(
			'user-registration-multisite.jpeg',
			'lost-password-form.png',
			'login-form.png',
			'normal-section-job-history-1.png',
			'repeating-section-job-history-1.png'
		);

		$this->check_for_attachments( $check_files, $body );
	}

	private function check_for_attachments( $files, $body ) {
		foreach ( $files as $file_name ) {
			$file_parts = explode( '.', $file_name );

			$content_type = 'Content-Type: image/' . $file_parts[1] . '; name="' . $file_parts[0];
			$this->assertContains( $content_type, $body );

			$content_disposition = 'Content-Disposition: attachment; filename=' . $file_parts[0];
			$this->assertContains( $content_disposition , $body );

		}
	}

}