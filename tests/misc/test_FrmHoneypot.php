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

	/**
	 * @covers FrmHoneypot::validate
	 */
	public function test_validate() {
		$_POST['frm_verify'] = '';
		$this->assertTrue( $this->honeypot->validate() );

		$_POST['frm_verify'] = 'test@email.com';
		$this->assertFalse( $this->honeypot->validate() );
	}

	/**
	 * @covers FrmHoneypot::is_honeypot_spam
	 */
	public function test_is_honeypot_spam() {
		$_POST['frm_verify'] = '';
		$this->assertFalse( $this->is_honeypot_spam() );

		$_POST['frm_verify'] = 'test@email.com';
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

		$this->form_id  = $this->factory->form->create(
			array(
				'options' => array(
					'honeypot' => 'off',
				),
			)
		);
		$this->honeypot = new FrmHoneypot( $this->form_id );
		$this->assertFalse( $this->is_option_on() );
	}

	private function is_option_on() {
		return $this->run_private_method( array( $this->honeypot, 'is_option_on' ) );
	}
}
