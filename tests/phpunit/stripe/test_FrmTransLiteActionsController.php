<?php

/**
 * @group stripe
 */
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

	/**
	 * @covers FrmTransLiteActionsController::maybe_use_decimal
	 */
	public function test_maybe_use_decimal() {
		// We need a currency with a . thousands separator.
		$currency = array(
			'thousand_separator' => '.',
			'decimal_separator'  => ',',
		);

		// Test with two decimal places.
		$amount = '111.50';
		$this->maybe_use_decimal( $amount, $currency );
		$this->assertEquals( '111,50', $amount );

		// Test with a single decimal place.
		$amount = '111.5';
		$this->maybe_use_decimal( $amount, $currency );
		$this->assertEquals( '111,5', $amount );

		// Test to make sure that three decimal places does not convert.
		// It should be interpreted as thousands.
		$amount = '111.500';
		$this->maybe_use_decimal( $amount, $currency );
		$this->assertEquals( '111.500', $amount );
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
