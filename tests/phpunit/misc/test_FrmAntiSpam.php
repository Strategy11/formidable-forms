<?php

/**
 * @group spam
 */
class test_FrmAntiSpam extends FrmUnitTest {

	private $factory;
	private $antispam;

	public function setUp(): void {
		parent::setUp();
		$form_id        = $this->factory->form->create();
		$this->antispam = new FrmAntiSpam( $form_id );
	}

	/**
	 * @covers FrmAntiSpam::get
	 */
	public function test_get() {
		$token_string = $this->run_private_method( array( $this->antispam, 'get' ) );
		$this->assertTrue( is_string( $token_string ) );
		$this->assertTrue( strlen( $token_string ) >= 32 );
	}

	/**
	 * @covers FrmAntiSpam::get_antispam_secret_key
	 */
	public function test_get_antispam_secret_key() {
		$secret_key = $this->run_private_method( array( $this->antispam, 'get_antispam_secret_key' ) );
		$this->assertTrue( is_string( $secret_key ) );
		$this->assertTrue( strlen( $secret_key ) >= 32 );
	}

	/**
	 * @covers FrmAntiSpam::get_valid_tokens
	 */
	public function test_get_valid_tokens() {
		$valid_tokens = $this->run_private_method( array( $this->antispam, 'get_valid_tokens' ) );
		$this->assertTrue( is_array( $valid_tokens ) );
		$this->assertTrue( count( $valid_tokens ) >= 1 );
	}

	/**
	 * @covers FrmAntiSpam::verify
	 */
	public function test_verify() {
		$valid_tokens = $this->run_private_method( array( $this->antispam, 'get_valid_tokens' ) );
		$valid_token  = reset( $valid_tokens );
		$this->assertTrue( $this->run_private_method( array( $this->antispam, 'verify' ), array( $valid_token ) ) );
	}
}
