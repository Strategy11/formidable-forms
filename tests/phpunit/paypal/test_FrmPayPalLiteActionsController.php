<?php

/**
 * @group paypal
 *
 * @covers FrmPayPalLiteActionsController
 */
#[\PHPUnit\Framework\Attributes\Group( 'paypal' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmPayPalLiteActionsController::class )]
class test_FrmPayPalLiteActionsController extends FrmUnitTest {

	/**
	 * @var string|null The capture status returned by Connect.
	 */
	private $capture_status = 'COMPLETED';

	/**
	 * @var bool Whether the response includes a capture.
	 */
	private $has_capture = true;

	/**
	 * @var array The original posted values.
	 */
	private $original_post;

	/**
	 * @var mixed The original PayPal test mode setting.
	 */
	private $original_test_mode;

	public function setUp(): void {
		parent::setUp();
		( new FrmTransLiteDb() )->upgrade();
		$this->original_post                                        = $_POST;
		$this->original_test_mode                                   = FrmPayPalLiteAppHelper::get_settings()->settings->test_mode;
		FrmPayPalLiteAppHelper::get_settings()->settings->test_mode = 1;
		update_option( 'frm_paypal_connect_merchant_id_test', 'merchant_fixture' );
		update_option( 'frm_paypal_connect_server_password_test', 'password_fixture' );
		$_POST['paypal_order_id'] = 'order_fixture';
		$this->reset_payment_state();
		add_filter( 'pre_http_request', array( $this, 'mock_connect' ), 10, 2 );
	}

	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'mock_connect' ), 10 );
		$this->reset_payment_state();
		$_POST = $this->original_post;
		FrmPayPalLiteAppHelper::get_settings()->settings->test_mode = $this->original_test_mode;
		parent::tearDown();
	}

	/**
	 * @return void
	 */
	private function reset_payment_state() {
		foreach ( array( 'active_order_id', 'active_payment_source', 'active_subscription' ) as $property ) {
			$this->set_private_property( 'FrmPayPalLiteActionsController', $property, null );
		}
		$this->set_private_property( 'FrmPayPalLiteActionsController', 'pending_capture', false );
	}

	/**
	 * @param mixed $response The response supplied by another filter.
	 * @param array $args The HTTP request options.
	 *
	 * @return mixed
	 */
	public function mock_connect( $response, $args ) {
		$action = $args['body']['frm_paypal_api_action'] ?? '';

		if ( ! in_array( $action, array( 'get_order', 'capture_order' ), true ) ) {
			return $response;
		}

		$order = (object) array(
			'id'             => 'order_fixture',
			'status'         => 'get_order' === $action ? 'APPROVED' : 'COMPLETED',
			'purchase_units' => array(
				(object) array( 'amount' => (object) array( 'value' => '10.00' ) ),
			),
		);

		if ( 'capture_order' === $action && $this->has_capture ) {
			$capture = (object) array( 'id' => 'capture_fixture' );

			if ( null !== $this->capture_status ) {
				$capture->status = $this->capture_status;
			}
			$order->purchase_units[0]->payments = (object) array( 'captures' => array( $capture ) );
		}

		$http_response       = new \WpOrg\Requests\Response();
		$http_response->body = wp_json_encode(
			array(
				'success' => true,
				'data'    => $order,
			)
		);
		return array(
			'http_response' => new WP_HTTP_Requests_Response( $http_response, '' ),
			'headers'       => array(),
			'body'          => $http_response->body,
			'response'      => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'       => array(),
		);
	}

	/**
	 * @return array The gateway result and the saved payment.
	 */
	private function submit_payment() {
		$form   = $this->factory->form->create_and_get();
		$entry  = $this->factory->entry->create_and_get( $this->factory->field->generate_entry_array( $form ) );
		$action = $this->factory->post->create_and_get(
			array(
				'post_type'    => FrmFormActionsController::$action_post_type,
				'post_excerpt' => 'payment',
				'post_status'  => 'publish',
				'menu_order'   => $form->id,
				'post_content' => wp_json_encode(
					array(
						'gateway'  => array( 'paypal' ),
						'amount'   => '10',
						'currency' => 'USD',
						'type'     => 'single',
					)
				),
			)
		);
		FrmFormAction::clear_cache();
		$action = FrmFormAction::get_single_action_type( $action->ID, 'payment' );
		$result = FrmPayPalLiteActionsController::trigger_gateway( $action, $entry, $form );
		return array( $result, ( new FrmTransLitePayment() )->get_one_by( $entry->id, 'item_id' ) );
	}

	public function test_declined_capture_does_not_create_a_payment_or_run_success_actions() {
		$this->capture_status     = 'DECLINED';
		$before                   = did_action( 'frm_payment_status_complete' );
		list( $result, $payment ) = $this->submit_payment();
		$this->assertFalse( $result['success'] );
		$this->assertStringContainsString( 'declined', $result['error'] );
		$this->assertFalse( (bool) $payment );
		$this->assertSame( $before, did_action( 'frm_payment_status_complete' ) );
	}

	public function test_pending_capture_waits_for_the_completion_webhook() {
		$this->capture_status     = 'PENDING';
		$before                   = did_action( 'frm_payment_status_complete' );
		list( $result, $payment ) = $this->submit_payment();
		$this->assertTrue( $result['success'] );
		$this->assertSame( 'pending', $payment->status );
		$this->assertSame( 'capture_fixture', $payment->receipt_id );
		$this->assertTrue( $this->get_private_property( 'FrmPayPalLiteActionsController', 'pending_capture' ) );
		$this->assertSame( $before, did_action( 'frm_payment_status_complete' ) );

		$this->handle_capture_event( 'PAYMENT.CAPTURE.COMPLETED' );
		$this->assertSame( 'complete', ( new FrmTransLitePayment() )->get_one( $payment->id )->status );
		$this->assertSame( $before + 1, did_action( 'frm_payment_status_complete' ) );
		$this->handle_capture_event( 'PAYMENT.CAPTURE.COMPLETED' );
		$this->assertSame( $before + 1, did_action( 'frm_payment_status_complete' ) );
	}

	public function test_pending_capture_can_be_denied_without_success_actions() {
		$this->capture_status     = 'PENDING';
		$before                   = did_action( 'frm_payment_status_complete' );
		list( $result, $payment ) = $this->submit_payment();
		$this->assertTrue( $result['success'] );
		$this->handle_capture_event( 'PAYMENT.CAPTURE.DENIED' );
		$this->assertSame( 'failed', ( new FrmTransLitePayment() )->get_one( $payment->id )->status );
		$this->assertSame( $before, did_action( 'frm_payment_status_complete' ) );
	}

	public function test_completed_capture_runs_success_actions() {
		$before                   = did_action( 'frm_payment_status_complete' );
		list( $result, $payment ) = $this->submit_payment();
		$this->assertTrue( $result['success'] );
		$this->assertSame( 'complete', $payment->status );
		$this->assertSame( 'capture_fixture', $payment->receipt_id );
		$this->assertSame( $before + 1, did_action( 'frm_payment_status_complete' ) );
	}

	public function test_missing_capture_status_preserves_legacy_behavior() {
		$this->capture_status     = null;
		list( $result, $payment ) = $this->submit_payment();
		$this->assertTrue( $result['success'] );
		$this->assertSame( 'complete', $payment->status );
	}

	public function test_missing_capture_preserves_the_order_id_fallback() {
		$this->has_capture        = false;
		list( $result, $payment ) = $this->submit_payment();
		$this->assertTrue( $result['success'] );
		$this->assertSame( 'complete', $payment->status );
		$this->assertSame( 'order_fixture', $payment->receipt_id );
	}

	/**
	 * @param string $event_type The PayPal capture event type.
	 *
	 * @return void
	 */
	private function handle_capture_event( $event_type ) {
		$controller = new FrmPayPalLiteEventsController();
		$this->set_private_property(
			$controller,
			'event',
			(object) array(
				'event_type' => $event_type,
				'resource'   => (object) array( 'id' => 'capture_fixture' ),
			)
		);
		$this->run_private_method( array( $controller, 'handle_event' ) );
	}
}
