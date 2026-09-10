<?php

/**
 * @group stripe
 */
class test_FrmTransLiteAppHelper extends FrmUnitTest {

	/**
	 * @covers FrmTransLiteAppHelper::get_user_id_for_current_payment
	 */
	public function test_get_user_id_for_current_payment_uses_the_logged_in_user() {
		$this->set_current_user_to_1();
		$this->assertSame( get_current_user_id(), FrmTransLiteAppHelper::get_user_id_for_current_payment() );

		$this->use_frm_role( 'loggedout' );
		$this->assertSame( 0, FrmTransLiteAppHelper::get_user_id_for_current_payment() );
	}

	/**
	 * An add on should be able to claim the payment for a user who isn't logged in yet.
	 *
	 * @covers FrmTransLiteAppHelper::get_user_id_for_current_payment
	 */
	public function test_frm_payment_user_id_filter() {
		$this->use_frm_role( 'loggedout' );

		add_filter( 'frm_payment_user_id', array( $this, 'return_registered_user_id' ) );
		$this->assertSame( 7, FrmTransLiteAppHelper::get_user_id_for_current_payment() );
		remove_filter( 'frm_payment_user_id', array( $this, 'return_registered_user_id' ) );

		// A negative or non numeric value should never reach the payment.
		add_filter( 'frm_payment_user_id', array( $this, 'return_invalid_user_id' ) );
		$this->assertSame( 0, FrmTransLiteAppHelper::get_user_id_for_current_payment() );
		remove_filter( 'frm_payment_user_id', array( $this, 'return_invalid_user_id' ) );

		// The filter shouldn't be able to take a payment away from the user who is logged in.
		$this->set_current_user_to_1();
		add_filter( 'frm_payment_user_id', array( $this, 'return_registered_user_id' ) );
		$this->assertSame( get_current_user_id(), FrmTransLiteAppHelper::get_user_id_for_current_payment() );
		remove_filter( 'frm_payment_user_id', array( $this, 'return_registered_user_id' ) );
	}

	/**
	 * Stands in for the Registration add on, which claims the payment for the user it just created.
	 *
	 * @param int $user_id The logged in user, or 0 for a guest.
	 *
	 * @return int
	 */
	public function return_registered_user_id( $user_id ) {
		return $user_id ? $user_id : 7;
	}

	/**
	 * @param int $user_id The logged in user, or 0 for a guest.
	 *
	 * @return string
	 */
	public function return_invalid_user_id( $user_id ) {
		return 'not-a-user';
	}
}
