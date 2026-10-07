<?php

/**
 * @group spam
 *
 * @covers FrmHoneypot
 */
#[\PHPUnit\Framework\Attributes\Group( 'spam' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmHoneypot::class )]
class test_FrmHoneypot extends FrmUnitTest {

	private $form_id;

	private $honeypot;

	public function setUp(): void {
		parent::setUp();
		$this->form_id  = $this->factory->form->create();
		$this->honeypot = new FrmHoneypot( $this->form_id );

		/**
		 * Put the honeypot field ID into the form state, the way rendering a form does.
		 *
		 * FrmHoneypot::get_honeypot_field_id() reads it back out of the state, and with no
		 * value there it returns 0 and the spam check exits early, so validate() reports
		 * every submission as clean. Nothing else in this class renders a form, so the state
		 * has to be set here rather than inherited from whatever ran before.
		 *
		 * @see FrmHoneypot::maybe_render_field()
		 */
		$max_field_id = FrmDb::get_var(
			'frm_fields',
			array(),
			'id',
			array(
				'order_by' => 'id DESC',
			)
		);

		$state_class = class_exists( 'FrmProFormState' ) ? 'FrmProFormState' : 'FrmFormState';
		$state_class::set_initial_value( 'honeypot_field_id', $max_field_id ? $max_field_id + 1 : 1 );
	}

	public function test_validate() {
		$honeypot_field_id = $this->run_private_method( array( $this->honeypot, 'get_honeypot_field_id' ) );

		$_POST['item_meta'][ $honeypot_field_id ] = '';
		$this->assertTrue( $this->honeypot->validate() );

		$_POST['item_meta'][ $honeypot_field_id ] = 'test@email.com';
		$this->assertFalse( $this->honeypot->validate() );
	}

	public function test_is_honeypot_spam() {
		$honeypot_field_id = $this->run_private_method( array( $this->honeypot, 'get_honeypot_field_id' ) );

		$_POST['item_meta'][ $honeypot_field_id ] = '';
		$this->assertFalse( $this->is_honeypot_spam() );

		$_POST['item_meta'][ $honeypot_field_id ] = 'test@email.com';
		$this->assertTrue( $this->is_honeypot_spam() );
	}

	/**
	 * Honeypot spam saved as a spam entry must not store the honeypot value as meta.
	 *
	 * @return void
	 */
	public function test_saved_honeypot_spam_drops_the_honeypot_value() {
		$field_id = $this->factory->field->create(
			array(
				'form_id' => $this->form_id,
				'type'    => 'text',
			)
		);

		// The honeypot ID is set after every field exists, the way rendering a form does.
		$state_class = class_exists( 'FrmProFormState' ) ? 'FrmProFormState' : 'FrmFormState';
		$state_class::set_initial_value( 'honeypot_field_id', $field_id + 1 );
		$honeypot_field_id = $this->run_private_method( array( $this->honeypot, 'get_honeypot_field_id' ) );
		$settings          = FrmAppHelper::get_settings();
		$original          = $settings->spam_handling;
		$original_post     = $_POST;

		$settings->spam_handling             = FrmSpamEntriesHelper::get_default_handling();
		$settings->spam_handling['honeypot'] = FrmSpamEntriesHelper::SAVE;
		$_POST['item_meta']                  = array(
			$field_id          => 'Real value',
			$honeypot_field_id => 'bot@example.com',
		);
		$values                              = array(
			'form_id'   => $this->form_id,
			'item_meta' => $_POST['item_meta'],
		);
		$errors                              = array();

		try {
			FrmEntryValidate::spam_check( false, $values, $errors );
			$this->assertArrayNotHasKey( 'spam', $errors, 'Saved honeypot spam should not show an error.' );
			$this->assertSame( 'honeypot', FrmSpamEntriesHelper::get_flagged_source( $values ) );
			$this->assertArrayNotHasKey( $honeypot_field_id, $_POST['item_meta'], 'The honeypot value should not be saved.' );
			$this->assertSame( 'Real value', $_POST['item_meta'][ $field_id ], 'Real field values should be kept.' );
		} finally {
			FrmSpamEntriesHelper::reset_flags();
			$settings->spam_handling = $original;
			$_POST                   = $original_post;
		}
	}

	private function is_honeypot_spam() {
		return $this->run_private_method( array( $this->honeypot, 'is_honeypot_spam' ) );
	}

	public function test_is_option_on() {
		$this->assertTrue( $this->is_option_on(), 'Honeypot should be on by default' );

		$key      = 'honeypot';
		$value    = '';
		$sanitize = 'sanitize_text_field';

		FrmAppHelper::get_settings()->update_setting( $key, $value, $sanitize );
		$this->honeypot = new FrmHoneypot( $this->form_id );
		$this->assertFalse( $this->is_option_on() );
	}

	private function is_option_on() {
		return $this->run_private_method( array( $this->honeypot, 'is_option_on' ) );
	}
}
