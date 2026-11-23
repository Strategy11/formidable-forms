<?php

/**
 * @group stripe
 *
 * @covers FrmTransLiteActionsController
 */
#[\PHPUnit\Framework\Attributes\Group( 'stripe' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmTransLiteActionsController::class )]
class test_FrmTransLiteActionsController extends FrmUnitTest {

	public function test_get_fields_for_price() {
		$form_id  = $this->factory->form->create();
		$field_id = $this->factory->field->create(
			array(
				'form_id' => $form_id,
				'type'    => 'number',
			)
		);

		$this->factory->post->create(
			array(
				'post_content' => json_encode(
					array(
						'amount' => '[' . $field_id . ']',
					)
				),
				'menu_order'   => $form_id,
				'post_type'    => 'frm_form_actions',
				'post_status'  => 'publish',
				'post_excerpt' => 'payment',
			)
		);
		$actions = FrmTransLiteActionsController::get_actions_for_form( $form_id );
		$action  = reset( $actions );
		$fields  = $this->get_fields_for_price( $action );

		$this->assertIsArray( $fields );
		$this->assertNotEmpty( $fields );

		$field = reset( $fields );
		$this->assertEquals( $field_id, $field );
	}

	private function get_fields_for_price( $action ) {
		return $this->run_private_method( array( 'FrmTransLiteActionsController', 'get_fields_for_price' ), array( $action ) );
	}

	public function test_maybe_use_decimal() {
		// We need a currency with a . thousands separator.
		$currency = array(
			'thousand_separator' => '.',
			'decimal_separator'  => ',',
		);

		// Test with two decimal places.
		$amount = '111.50';
		$this->maybe_use_decimal( $amount, $currency );
		$this->assertSame( '111,50', $amount );

		// Test with a single decimal place.
		$amount = '111.5';
		$this->maybe_use_decimal( $amount, $currency );
		$this->assertSame( '111,5', $amount );

		// Test to make sure that three decimal places does not convert.
		// It should be interpreted as thousands.
		$amount = '111.500';
		$this->maybe_use_decimal( $amount, $currency );
		$this->assertSame( '111.500', $amount );
	}

	/**
	 * @covers FrmTransLiteActionsController::trigger_payment_status_change
	 */
	public function test_trigger_payment_status_change_keeps_passed_payment() {
		$entry    = $this->create_entry_with_payments( array( 'complete', 'pending' ) );
		$older_id = $entry['payment_ids'][0];
		$older    = ( new FrmTransLitePayment() )->get_one( $older_id );

		$atts = $this->trigger_payment_status_change(
			array(
				'status'  => 'refunded',
				'payment' => $older,
			)
		);

		$this->assertNotNull( $atts );
		$this->assertEquals( $older_id, $atts['payment']->id );
		$this->assertEquals( $entry['id'], $atts['entry_id'] );
	}

	/**
	 * Stripe loads a payment, saves the new status, then passes the copy it loaded first.
	 * The payment actions must see the saved status, or a pending copy stops them from running.
	 *
	 * @covers FrmTransLiteActionsController::trigger_payment_status_change
	 */
	public function test_trigger_payment_status_change_uses_the_saved_status() {
		$entry       = $this->create_entry_with_payments( array( 'pending' ) );
		$payment_id  = $entry['payment_ids'][0];
		$frm_payment = new FrmTransLitePayment();
		$stale       = $frm_payment->get_one( $payment_id );

		$frm_payment->update( $payment_id, array( 'status' => 'complete' ) );

		$received = null;
		$callback = function ( $hook_atts ) use ( &$received ) {
			$received = $hook_atts;
		};

		add_action( 'frm_payment_status_complete', $callback );
		FrmTransLiteActionsController::trigger_payment_status_change(
			array(
				'status'  => 'complete',
				'payment' => $stale,
			)
		);
		remove_action( 'frm_payment_status_complete', $callback );

		$this->assertSame( 'pending', $stale->status, 'The test should pass a copy loaded before the update.' );
		$this->assertNotNull( $received );
		$this->assertEquals( $payment_id, $received['payment']->id, 'The passed payment should still be used.' );
		$this->assertSame( 'complete', $received['payment']->status, 'The payment should have the saved status, so payment actions run.' );
	}

	/**
	 * @covers FrmTransLiteActionsController::trigger_payment_status_change
	 */
	public function test_trigger_payment_status_change_loads_payment_for_entry() {
		$entry = $this->create_entry_with_payments( array( 'complete' ) );

		$atts = $this->trigger_payment_status_change(
			array(
				'status' => 'refunded',
				'entry'  => (object) array( 'id' => $entry['id'] ),
			)
		);

		$this->assertNotNull( $atts );
		$this->assertEquals( $entry['payment_ids'][0], $atts['payment']->id );
	}

	/**
	 * @param array $atts
	 *
	 * @return array|null The atts passed to the frm_payment_status_refunded hook.
	 */
	private function trigger_payment_status_change( $atts ) {
		$received = null;
		$callback = function ( $hook_atts ) use ( &$received ) {
			$received = $hook_atts;
		};

		add_action( 'frm_payment_status_refunded', $callback );
		FrmTransLiteActionsController::trigger_payment_status_change( $atts );
		remove_action( 'frm_payment_status_refunded', $callback );

		return $received;
	}

	/**
	 * @param string[] $statuses One payment is created per status, oldest first.
	 *
	 * @return array{id: int, payment_ids: int[]}
	 */
	private function create_entry_with_payments( $statuses ) {
		( new FrmTransLiteDb() )->upgrade();

		$form        = $this->factory->form->create_and_get();
		$entry       = $this->factory->entry->create_and_get( $this->factory->field->generate_entry_array( $form ) );
		$frm_payment = new FrmTransLitePayment();
		$payment_ids = array();

		foreach ( $statuses as $index => $status ) {
			$payment_ids[] = $frm_payment->create(
				array(
					'item_id'    => $entry->id,
					'action_id'  => 1,
					'amount'     => '10.00',
					'paysys'     => 'stripe',
					'receipt_id' => 'pi_test_' . $index,
					'status'     => $status,
					'created_at' => gmdate( 'Y-m-d H:i:s', strtotime( '+' . $index . ' day' ) ),
				)
			);
		}

		return array(
			'id'          => $entry->id,
			'payment_ids' => $payment_ids,
		);
	}

	/**
	 * With the Stripe add-on active, a form without a Gateway field would be saved without a payment.
	 * Lite processes it with the gateway set in the action.
	 *
	 * @covers FrmTransLiteActionsController::trigger_action_without_gateway_field
	 */
	public function test_trigger_action_without_gateway_field_uses_the_action_gateway() {
		$form_id  = $this->factory->form->create();
		$response = $this->trigger_test_gateway( $form_id );

		$this->assertSame( 1, FrmLiteTestGatewayActionsController::$calls, 'The gateway set in the action should process the payment.' );
		$this->assertSame( 'complete', $response, 'A charge that asks for triggers should run the completed payment triggers.' );
	}

	/**
	 * A form with a Gateway field is left to the add-on, so the payment is never processed twice.
	 *
	 * @covers FrmTransLiteActionsController::trigger_action_without_gateway_field
	 */
	public function test_trigger_action_without_gateway_field_skips_a_form_with_a_gateway_field() {
		$form_id = $this->factory->form->create();
		$this->factory->field->create(
			array(
				'form_id' => $form_id,
				'type'    => 'gateway',
			)
		);

		$this->trigger_test_gateway( $form_id );

		$this->assertSame( 0, FrmLiteTestGatewayActionsController::$calls, 'The add-on handles forms with a Gateway field.' );
	}

	/**
	 * A posted gateway field ID means the add-on found the gateway.
	 *
	 * @covers FrmTransLiteActionsController::trigger_action_without_gateway_field
	 */
	public function test_trigger_action_without_gateway_field_skips_a_posted_gateway() {
		$form_id              = $this->factory->form->create();
		$_POST['frm_gateway'] = '123';

		try {
			$this->trigger_test_gateway( $form_id );
		} finally {
			unset( $_POST['frm_gateway'] );
		}

		$this->assertSame( 0, FrmLiteTestGatewayActionsController::$calls, 'The add-on handles a submission with a posted gateway.' );
	}

	/**
	 * Run the fallback with a stand-in gateway and report the payment status hook that fired.
	 *
	 * @param int $form_id The form the payment action belongs to.
	 *
	 * @return string The status passed to a frm_payment_status_* hook, or an empty string.
	 */
	private function trigger_test_gateway( $form_id ) {
		FrmLiteTestGatewayActionsController::$calls = 0;

		$status   = '';
		$gateways = function ( $gateways ) {
			$gateways['lite_test'] = array(
				'label'      => 'Test',
				'user_label' => 'Test',
				'class'      => 'LiteTestGateway',
				'recurring'  => false,
			);
			return $gateways;
		};
		$complete = function () use ( &$status ) {
			$status = 'complete';
		};
		$entry    = $this->factory->entry->create_and_get( array( 'form_id' => $form_id ) );
		$action   = (object) array(
			'menu_order'   => $form_id,
			'post_content' => array(
				'description' => '',
				'gateway'     => array( 'lite_test' ),
			),
		);

		add_filter( 'frm_payment_gateways', $gateways );
		add_action( 'frm_payment_status_complete', $complete );

		try {
			FrmTransLiteActionsController::trigger_action_without_gateway_field( $action, $entry, (object) array( 'id' => $form_id ) );
		} finally {
			remove_filter( 'frm_payment_gateways', $gateways );
			remove_action( 'frm_payment_status_complete', $complete );
		}

		return $status;
	}

	/**
	 * @param string $amount
	 * @param array  $currency
	 *
	 * @return string
	 */
	private function maybe_use_decimal( &$amount, $currency ) {
		return $this->run_private_method( array( 'FrmTransLiteActionsController', 'maybe_use_decimal' ), array( &$amount, $currency ) );
	}
}

/**
 * Stands in for a gateway's ActionsController, to count how often it is asked to charge.
 */
class FrmLiteTestGatewayActionsController {

	/**
	 * @var int
	 */
	public static $calls = 0;

	/**
	 * @param object $action The payment action.
	 * @param object $entry  The entry being submitted.
	 * @param object $form   The form being submitted.
	 *
	 * @return array
	 */
	public static function trigger_gateway( $action, $entry, $form ) {
		++self::$calls;

		return array(
			'success'      => true,
			'run_triggers' => true,
			'show_errors'  => false,
		);
	}
}
