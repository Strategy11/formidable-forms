<?php

/**
 * @group spam
 */
class test_FrmAntiSpam extends FrmUnitTest {

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
		$this->assertIsString( $token_string );
		$this->assertGreaterThanOrEqual(32, strlen( $token_string ));
	}

	/**
	 * @covers FrmAntiSpam::get_antispam_secret_key
	 */
	public function test_get_antispam_secret_key() {
		$secret_key = $this->run_private_method( array( $this->antispam, 'get_antispam_secret_key' ) );
		$this->assertIsString( $secret_key );
		$this->assertGreaterThanOrEqual(32, strlen( $secret_key ));
	}

	/**
	 * @covers FrmAntiSpam::get_valid_tokens
	 */
	public function test_get_valid_tokens() {
		$valid_tokens = $this->run_private_method( array( $this->antispam, 'get_valid_tokens' ) );
		$this->assertIsArray( $valid_tokens );
		$this->assertGreaterThanOrEqual(1, count( $valid_tokens ));
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
