<?php

/**
 * @group stripe
 */
class test_FrmStrpLiteActionsController extends FrmUnitTest {

	/**
	 * @covers FrmStrpLiteActionsController::replace_email_shortcode
	 */
	public function test_replace_email_shortcode() {
		$this->set_current_user_to_1();
		$email_string = '[email]';
		$this->assertSame( 'admin@example.org', $this->replace_email_shortcode( $email_string ) );

		$this->use_frm_role( 'loggedout' );
		$this->assertSame( '', $this->replace_email_shortcode( $email_string ) );
	}

	/**
	 * @param string $email
	 *
	 * @return string
	 */
	private function replace_email_shortcode( $email ) {
		return $this->run_private_method( array( 'FrmStrpLiteActionsController', 'replace_email_shortcode' ), array( $email ) );
	}

	/**
	 * A submission with no setup intent of its own must not look one up.
	 *
	 * @covers FrmStrpLiteActionsController::get_customer_id_from_posted_setup_intents
	 */
	public function test_get_customer_id_from_posted_setup_intents() {
		$this->assertFalse( FrmStrpLiteActionsController::get_customer_id_from_posted_setup_intents( 9 ) );

		$_POST['frmintent9'] = array();
		$this->assertFalse( FrmStrpLiteActionsController::get_customer_id_from_posted_setup_intents( 9 ) );

		// A one time payment posts a payment intent, not a setup intent.
		$_POST['frmintent9'] = array( 'pi_123_secret_456' );
		$this->assertFalse( FrmStrpLiteActionsController::get_customer_id_from_posted_setup_intents( 9 ) );

		unset( $_POST['frmintent9'] );
	}

	/**
	 * Two actions that differ only by trial length must not share a plan id.
	 *
	 * @covers FrmStrpLiteActionsController::create_plan_id
	 */
	public function test_create_plan_id_includes_the_trial() {
		$settings = array(
			'description'          => 'Monthly membership',
			'amount'               => '10.00',
			'interval'             => 'month',
			'interval_count'       => 1,
			'currency'             => 'usd',
			'trial_interval_count' => 0,
		);

		$no_trial = FrmStrpLiteActionsController::create_plan_id( $settings );

		$settings['trial_interval_count'] = 14;
		$two_week_trial                   = FrmStrpLiteActionsController::create_plan_id( $settings );

		$settings['trial_interval_count'] = 30;
		$one_month_trial                  = FrmStrpLiteActionsController::create_plan_id( $settings );

		$this->assertNotEquals( $no_trial, $two_week_trial );
		$this->assertNotEquals( $two_week_trial, $one_month_trial );
		$this->assertSame( 'monthly-membership_1000_1month_usd_14', $two_week_trial );

		// The same settings always produce the same id, so an existing plan is reused.
		$this->assertSame( $one_month_trial, FrmStrpLiteActionsController::create_plan_id( $settings ) );
	}

	/**
	 * A setting array without a trial value should still produce an id.
	 *
	 * @covers FrmStrpLiteActionsController::create_plan_id
	 */
	public function test_create_plan_id_without_a_trial_setting() {
		$settings = array(
			'description'    => 'Monthly membership',
			'amount'         => '10.00',
			'interval'       => 'month',
			'interval_count' => 1,
			'currency'       => 'usd',
		);

		$this->assertSame( 'monthly-membership_1000_1month_usd', FrmStrpLiteActionsController::create_plan_id( $settings ) );
	}
}
