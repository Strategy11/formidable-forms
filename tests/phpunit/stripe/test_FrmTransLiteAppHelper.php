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

	/**
	 * A gateway is connected per mode, so the state has to be read per mode as well.
	 *
	 * @covers FrmTransLiteAppHelper::get_gateway_connection_state
	 */
	public function test_get_gateway_connection_state_reads_each_mode_separately() {
		$this->connect_gateway( 'square', 'test' );

		$this->assertSame( 'connected', FrmTransLiteAppHelper::get_gateway_connection_state( 'square', 'test' ) );
		$this->assertSame( 'disconnected', FrmTransLiteAppHelper::get_gateway_connection_state( 'square', 'live' ) );
	}

	/**
	 * Stripe saves an account id before onboarding finishes, so those two steps are different states.
	 * The account status request is the thing that finishes onboarding, so 'incomplete' must not be
	 * treated the same as 'disconnected' or that request would never be sent.
	 *
	 * @covers FrmTransLiteAppHelper::get_gateway_connection_state
	 */
	public function test_get_gateway_connection_state_separates_an_unfinished_stripe_connection() {
		$this->assertSame( 'disconnected', FrmTransLiteAppHelper::get_gateway_connection_state( 'stripe', 'live' ) );

		update_option( 'frm_strp_connect_account_id_live', 'acct_123', false );
		$this->assertSame( 'incomplete', FrmTransLiteAppHelper::get_gateway_connection_state( 'stripe', 'live' ) );

		update_option( 'frm_strp_connect_details_submitted_live', true, false );
		$this->assertSame( 'connected', FrmTransLiteAppHelper::get_gateway_connection_state( 'stripe', 'live' ) );
	}

	/**
	 * An unknown gateway must not be reported as broken, or an add on gateway would be blocked
	 * from taking payments by a check that knows nothing about it.
	 *
	 * @covers FrmTransLiteAppHelper::get_gateway_connection_state
	 */
	public function test_get_gateway_connection_state_allows_an_unknown_gateway() {
		$this->assertSame( 'connected', FrmTransLiteAppHelper::get_gateway_connection_state( 'authorize_net', 'live' ) );
	}

	/**
	 * @covers FrmTransLiteAppHelper::get_gateway_connection_error
	 */
	public function test_get_gateway_connection_error_is_empty_when_connected() {
		$this->connect_gateway( 'paypal', 'live' );
		$this->assertSame( '', FrmTransLiteAppHelper::get_gateway_connection_error( 'paypal', 'live' ) );
	}

	/**
	 * Connecting test mode and then switching to live is the most common reason a payment stops
	 * working, and the API error for it says nothing about the mode. The message has to.
	 *
	 * @covers FrmTransLiteAppHelper::get_gateway_connection_error
	 */
	public function test_get_gateway_connection_error_names_the_mode_that_is_connected() {
		$this->connect_gateway( 'square', 'test' );

		$error = FrmTransLiteAppHelper::get_gateway_connection_error( 'square', 'live' );

		$this->assertStringContainsString( 'set to live mode', $error );
		$this->assertStringContainsString( 'only connected in test mode', $error );
	}

	/**
	 * @covers FrmTransLiteAppHelper::get_gateway_connection_error
	 */
	public function test_get_gateway_connection_error_when_no_mode_is_connected() {
		$error = FrmTransLiteAppHelper::get_gateway_connection_error( 'square', 'live' );

		$this->assertStringContainsString( 'Square is not connected', $error );
		$this->assertStringNotContainsString( 'test mode', $error );
	}

	/**
	 * Only a user who can open the settings page should be told to go there.
	 *
	 * @covers FrmTransLiteAppHelper::get_gateway_connection_error
	 */
	public function test_get_gateway_connection_error_only_points_admins_at_the_settings() {
		$this->set_current_user_to_1();
		$this->assertStringContainsString( 'Global Settings', FrmTransLiteAppHelper::get_gateway_connection_error( 'square', 'live' ) );

		$this->use_frm_role( 'loggedout' );
		$this->assertStringNotContainsString( 'Global Settings', FrmTransLiteAppHelper::get_gateway_connection_error( 'square', 'live' ) );
	}

	/**
	 * Save the credentials that mark a gateway as connected in one mode.
	 *
	 * @param string $gateway 'stripe', 'square', or 'paypal'.
	 * @param string $mode    'test' or 'live'.
	 *
	 * @return void
	 */
	private function connect_gateway( $gateway, $mode ) {
		if ( 'stripe' === $gateway ) {
			update_option( 'frm_strp_connect_account_id_' . $mode, 'acct_123', false );
			update_option( 'frm_strp_connect_details_submitted_' . $mode, true, false );
			return;
		}

		$prefix = 'square' === $gateway ? 'frm_square_connect' : 'frm_paypal_connect';
		update_option( $prefix . '_merchant_id_' . $mode, 'merchant_123', false );
	}
}
