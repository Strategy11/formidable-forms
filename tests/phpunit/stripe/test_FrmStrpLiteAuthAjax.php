<?php

/**
 * @group ajax
 * @group stripe
 */
#[\PHPUnit\Framework\Attributes\Group( 'ajax' )]
#[\PHPUnit\Framework\Attributes\Group( 'stripe' )]
class test_FrmStrpLiteAuthAjax extends FrmAjaxUnitTest {

	/**
	 * @var array The Connect requests made by the test.
	 */
	private $requests = array();

	public function setUp(): void {
		parent::setUp();
		$db = new FrmTransLiteDb();
		$db->upgrade();
		FrmStrpLiteAppHelper::get_settings()->settings->test_mode = 1;

		foreach ( array( 'account_id', 'client_password', 'server_password', 'details_submitted' ) as $key ) {
			update_option( 'frm_strp_connect_' . $key . '_test', 'test_fixture' );
		}

		$load_ajax_hooks = new ReflectionMethod( 'FrmStrpLiteHooksController', 'load_ajax_hooks' );
		$load_ajax_hooks->setAccessible( true );
		$load_ajax_hooks->invoke( null );
		add_filter( 'pre_http_request', array( $this, 'record_connect_request' ), 10, 2 );

		// The AJAX handler fires admin_init, so keep core update checks from making their own requests.
		remove_action( 'admin_init', '_maybe_update_core' );
		remove_action( 'admin_init', '_maybe_update_plugins' );
		remove_action( 'admin_init', '_maybe_update_themes' );
	}

	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'record_connect_request' ), 10 );
		parent::tearDown();
	}

	/**
	 * @param mixed $response The response supplied by another filter.
	 * @param array $args     The request options.
	 *
	 * @return array
	 */
	public function record_connect_request( $response, $args ) {
		if ( ! is_array( $args['body'] ) || ! isset( $args['body']['frm_strp_connect_action'] ) ) {
			return $response;
		}

		$this->requests[] = $args['body'];

		return array(
			'headers'  => array(),
			'body'     => wp_json_encode( array( 'success' => false ) ),
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'  => array(),
		);
	}

	/**
	 * @param array $form The posted form inputs.
	 *
	 * @return void
	 */
	private function post_failed_payment( $form ) {
		$_POST = array(
			'action' => 'frm_failed_payment',
			'nonce'  => wp_create_nonce( 'frm_strp_ajax' ),
			'form'   => wp_slash( wp_json_encode( $form ) ),
		);
	}

	public function test_invalid_nonce_stops_before_any_lookup() {
		$this->post_failed_payment(
			array(
				array(
					'name'  => 'form_id',
					'value' => '1',
				),
				array(
					'name'  => 'frmintent1[]',
					'value' => 'pi_fixture_secret_fixture',
				),
			)
		);
		$_POST['nonce'] = 'invalid';

		try {
			$this->_handleAjax( 'frm_failed_payment' );
			$this->fail( 'An invalid nonce must stop the request.' );
		} catch ( WPAjaxDieStopException $e ) {
			$this->assertSame( '-1', $e->getMessage() );
		}

		$this->assertSame( array(), $this->requests );
	}

	public function test_request_without_intents_ends_without_a_response() {
		$this->post_failed_payment(
			array(
				array(
					'name'  => 'form_id',
					'value' => '1',
				),
			)
		);

		try {
			$this->_handleAjax( 'frm_failed_payment' );
			$this->fail( 'A request without intents must end the request.' );
		} catch ( WPAjaxDieStopException $e ) {
			$this->assertSame( '', $e->getMessage() );
		}

		$this->assertSame( '', $this->_last_response );
		$this->assertSame( array(), $this->requests );
	}

	public function test_posted_intents_are_echoed_back_after_cleanup() {
		$secret = 'pi_fixture_secret_' . wp_generate_password( 24, false );
		$this->post_failed_payment(
			array(
				array(
					'name'  => 'form_id',
					'value' => '1',
				),
				array(
					'name'  => 'frmintent1[]',
					'value' => $secret,
				),
			)
		);

		try {
			$this->_handleAjax( 'frm_failed_payment' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$response = json_decode( $this->_last_response, true );
		$this->assertTrue( $response['success'] );
		$this->assertSame( array( $secret ), $response['data']['intents'] );
		// No local payment row uses this intent, so Stripe is never asked.
		$this->assertSame( array(), $this->requests );
	}
}
