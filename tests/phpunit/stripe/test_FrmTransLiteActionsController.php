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
	 * @covers FrmTransLiteActionsController::find_decimal_position
	 */
	public function test_find_decimal_position() {
		// A dot-thousands currency (e.g. EUR).
		$currency = array(
			'thousand_separator' => '.',
			'decimal_separator'  => ',',
		);

		// A single dot with a 1-2 digit tail reads as a decimal point even though this
		// currency configures '.' as its thousand separator.
		$this->assertSame( 3, $this->find_decimal_position( '111.50', $currency ) );
		$this->assertSame( 3, $this->find_decimal_position( '111.5', $currency ) );

		// Three digits after a single dot reads as thousands grouping instead.
		$this->assertFalse( $this->find_decimal_position( '111.500', $currency ) );

		// Repeated dots and no comma at all is unambiguous thousands grouping.
		$this->assertFalse( $this->find_decimal_position( '1.234.567', $currency ) );

		// Both separators present is a different locale's format (e.g. a US-style
		// "1,111.50" typed into a form with this dot-thousands currency) -- whichever
		// separator appears last is the real decimal point, regardless of currency config.
		$this->assertSame( 5, $this->find_decimal_position( '1,111.50', $currency ) );
		$this->assertSame( 5, $this->find_decimal_position( '1.111,50', $currency ) );

		// A repeated occurrence of the character that turns out to be the decimal separator
		// doesn't move the split point off the rightmost occurrence (formidable-forms#3379:
		// blindly replacing every occurrence of it is what caused the original truncation).
		$this->assertSame( 9, $this->find_decimal_position( '1,234.567,89', $currency ) );

		// A comma-thousands currency (e.g. GBP): a lone comma is always thousands grouping,
		// never reinterpreted as a decimal point, regardless of its tail length.
		$currency = array(
			'thousand_separator' => ',',
			'decimal_separator'  => '.',
		);
		$this->assertFalse( $this->find_decimal_position( '1,23', $currency ) );
	}

	/**
	 * @param string $amount
	 * @param array  $currency
	 *
	 * @return int|false
	 */
	private function find_decimal_position( $amount, $currency ) {
		return $this->run_private_method( array( 'FrmTransLiteActionsController', 'find_decimal_position' ), array( $amount, $currency ) );
	}
}
