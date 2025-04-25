<?php

/**
 * @group spam
 */
class test_FrmHoneypot extends FrmUnitTest {

	private $form_id;

	private $honeypot;

	public function setUp(): void {
		parent::setUp();
		$this->form_id  = $this->factory->form->create();
		$this->honeypot = new FrmHoneypot( $this->form_id );
	}

	public function test_get_honeypot_field_id() {
		$fields       = $this->get_private_property( $this->honeypot, 'fields' );
		$max_field_id = 0;
		foreach ( $fields as $field ) {
			$max_field_id = max( $max_field_id, $field->id );
		}

		$this->assertEquals( $max_field_id + 1, $this->run_private_method( array( $this->honeypot, 'get_honeypot_field_id' ) ) );
	}

	/**
	 * @covers FrmHoneypot::validate
	 */
	public function test_validate() {
		$honeypot_field_id = $this->run_private_method( array( $this->honeypot, 'get_honeypot_field_id' ) );

		$_POST['item_meta'][ $honeypot_field_id ] = '';
		$this->assertTrue( $this->honeypot->validate() );

		$_POST['item_meta'][ $honeypot_field_id ] = 'test@email.com';
		$this->assertFalse( $this->honeypot->validate() );
	}

	/**
	 * @covers FrmHoneypot::is_honeypot_spam
	 */
	public function test_is_honeypot_spam() {
		$honeypot_field_id = $this->run_private_method( array( $this->honeypot, 'get_honeypot_field_id' ) );

		$_POST['item_meta'][ $honeypot_field_id ] = '';
		$this->assertFalse( $this->is_honeypot_spam() );

		$_POST['item_meta'][ $honeypot_field_id ] = 'test@email.com';
		$this->assertTrue( $this->is_honeypot_spam() );
	}

	private function is_honeypot_spam() {
		return $this->run_private_method( array( $this->honeypot, 'is_honeypot_spam' ) );
	}

	/**
	 * @covers FrmHoneypot::is_option_on
	 */
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
