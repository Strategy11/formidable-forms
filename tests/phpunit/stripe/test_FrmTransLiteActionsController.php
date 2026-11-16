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
	 * @param string $amount
	 * @param array  $currency
	 *
	 * @return string
	 */
	private function maybe_use_decimal( &$amount, $currency ) {
		return $this->run_private_method( array( 'FrmTransLiteActionsController', 'maybe_use_decimal' ), array( &$amount, $currency ) );
	}
}
