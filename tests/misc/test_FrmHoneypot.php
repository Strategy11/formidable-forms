<?php

/**
 * @group mike
 */
class test_FrmHoneypot extends FrmUnitTest {

	private $form_id;

	private $honeypot;

	public function setUp() {
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
	 * @covers FrmHoneypot::honeypot_option_is_on
	 */
	public function test_honeypot_option_is_on() {
		$this->assertTrue( $this->honeypot_option_is_on(), 'Honeypot should be on by default' );

		$this->form_id  = $this->factory->form->create(
			array(
				'options' => array(
					'honeypot' => 0,
				),
			)
		);
		$this->honeypot = new FrmHoneypot( $this->form_id );
		$this->assertFalse( $this->honeypot_option_is_on() );
	}

	private function honeypot_option_is_on() {
		return $this->run_private_method( array( $this->honeypot, 'honeypot_option_is_on' ) );
	}
}
