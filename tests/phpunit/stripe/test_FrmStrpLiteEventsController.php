<?php

/**
 * @group stripe
 */
class test_FrmStrpLiteEventsController extends FrmUnitTest {

	/**
	 * @covers FrmStrpLiteEventsController::reset_customer
	 */
	public function test_reset_customer() {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		update_user_meta( $user_id, '_frmstrp_customer_id_test', 'cus_abc123' );
		update_user_meta( $user_id, 'unrelated_meta', 'cus_abc123' );

		$controller = new FrmStrpLiteEventsController();
		$this->set_private_property( $controller, 'invoice', (object) array( 'id' => 'cus_abc123' ) );
		$this->run_private_method( array( $controller, 'reset_customer' ), array() );

		// The customer meta is deleted with a direct query, so drop the cached values.
		clean_user_cache( $user_id );

		$this->assertSame( '', get_user_meta( $user_id, '_frmstrp_customer_id_test', true ) );
		$this->assertSame( 'cus_abc123', get_user_meta( $user_id, 'unrelated_meta', true ) );
	}
}
