<?php

/**
 * @group stripe
 */
class test_FrmTransLiteCRUDController extends FrmUnitTest {

	/**
	 * @covers FrmTransLiteCRUDController::get_payment_row
	 */
	public function test_get_payment_row() {
		( new FrmTransLiteDb() )->upgrade();

		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		$form       = $this->factory->form->create_and_get();
		$entry      = $this->factory->entry->create_and_get( $this->factory->field->generate_entry_array( $form ) );
		$payment    = new FrmTransLitePayment();
		$payment_id = $payment->create(
			array(
				'receipt_id'  => 'rcpt_123',
				'item_id'     => $entry->id,
				'action_id'   => 1,
				'amount'      => 25.00,
				'status'      => 'complete',
				'paysys'      => 'stripe',
				'created_at'  => gmdate( 'Y-m-d H:i:s' ),
				'begin_date'  => gmdate( 'Y-m-d' ),
				'expire_date' => gmdate( 'Y-m-d' ),
			)
		);

		$this->assertGreaterThan( 0, $payment_id );

		$row = $this->run_private_method( array( 'FrmTransLiteCRUDController', 'get_payment_row' ), array( $payment_id ) );

		$this->assertEquals( $payment_id, $row->id );
		$this->assertSame( 'rcpt_123', $row->receipt_id );
		$this->assertEquals( $entry->user_id, $row->user_id );
	}
}
