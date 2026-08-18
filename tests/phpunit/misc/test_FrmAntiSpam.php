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

	public function tearDown(): void {
		$this->set_private_property( 'FrmAntiSpam', 'filters_added', false );
		parent::tearDown();
	}

	/**
	 * @covers FrmAntiSpam::init
	 * @covers FrmAntiSpam::add_token_to_form
	 */
	public function test_init_only_adds_token_filters_once() {
		$this->set_private_property( 'FrmAntiSpam', 'filters_added', false );
		remove_filter( 'frm_run_antispam', '__return_false' );

		$first_form_id  = $this->factory->form->create( array( 'options' => array( 'antispam' => 1 ) ) );
		$second_form_id = $this->factory->form->create( array( 'options' => array( 'antispam' => 1 ) ) );

		FrmAntiSpam::maybe_init( $first_form_id );
		FrmAntiSpam::maybe_init( $second_form_id );

		$second_form = FrmForm::getOne( $second_form_id );
		$this->assertSame( 1, substr_count( apply_filters( 'frm_form_attributes', '', $second_form ), 'data-token=' ) );
		$this->assertSame( 1, substr_count( apply_filters( 'frm_form_div_attributes', '', $second_form ), 'data-token=' ) );
	}

	/**
	 * @covers FrmAntiSpam::add_token_to_form
	 */
	public function test_token_is_not_added_to_forms_without_antispam() {
		$this->set_private_property( 'FrmAntiSpam', 'filters_added', false );
		remove_filter( 'frm_run_antispam', '__return_false' );

		$antispam_form_id = $this->factory->form->create( array( 'options' => array( 'antispam' => 1 ) ) );
		FrmAntiSpam::maybe_init( $antispam_form_id );

		$plain_form = FrmForm::getOne( $this->factory->form->create() );
		$this->assertSame( 0, substr_count( apply_filters( 'frm_form_attributes', '', $plain_form ), 'data-token=' ) );
		$this->assertSame( 0, substr_count( apply_filters( 'frm_form_div_attributes', '', $plain_form ), 'data-token=' ) );

		$antispam_form = FrmForm::getOne( $antispam_form_id );
		$this->assertSame( 1, substr_count( apply_filters( 'frm_form_attributes', '', $antispam_form ), 'data-token=' ) );
	}

	/**
	 * @covers FrmAntiSpam::get
	 */
	public function test_get() {
		$token_string = $this->run_private_method( array( $this->antispam, 'get' ) );
		$this->assertIsString( $token_string );
		$this->assertGreaterThanOrEqual( 32, strlen( $token_string ) );
	}

	/**
	 * @covers FrmAntiSpam::get_antispam_secret_key
	 */
	public function test_get_antispam_secret_key() {
		$secret_key = $this->run_private_method( array( $this->antispam, 'get_antispam_secret_key' ) );
		$this->assertIsString( $secret_key );
		$this->assertGreaterThanOrEqual( 32, strlen( $secret_key ) );
	}

	/**
	 * @covers FrmAntiSpam::get_valid_tokens
	 */
	public function test_get_valid_tokens() {
		$valid_tokens = $this->run_private_method( array( $this->antispam, 'get_valid_tokens' ) );
		$this->assertIsArray( $valid_tokens );
		$this->assertGreaterThanOrEqual( 1, count( $valid_tokens ) );
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
