<?php

class test_FrmPayPalLiteLegacyMigration extends FrmUnitTest {

	public function test_single_payment_maps_amount_currency_and_conditions() {
		$notes = array();
		$value = FrmPayPalLiteLegacyMigrationHelper::convert_settings(
			array(
				'paypal_type'         => '_xclick',
				'paypal_amount_field' => 123,
				'currency'            => 'USD',
				'conditions'          => array(
					'hide_field'      => array( 45 ),
					'hide_field_cond' => array( '==' ),
					'hide_opt'        => array( 'yes' ),
				),
			),
			1,
			$notes
		);
		$this->assertSame( array( 'paypal' ), $value['gateway'], 'The gateway is PayPal Commerce.' );
		$this->assertSame( 'single', $value['type'], 'Single payments keep their type.' );
		$this->assertSame( '[123]', $value['amount'], 'Field amounts become shortcodes.' );
		$this->assertSame( 'usd', $value['currency'], 'Currency uses the Commerce format.' );
		$this->assertSame( 45, $value['conditions'][0]['hide_field'], 'Legacy condition fields are preserved.' );
		$this->assertSame( 'yes', $value['conditions'][0]['hide_opt'], 'Legacy condition values are preserved.' );
	}

	public function test_subscription_maps_interval_and_reports_trial_changes() {
		$notes = array();
		$value = FrmPayPalLiteLegacyMigrationHelper::convert_settings(
			array(
				'paypal_type'      => '_xclick-subscriptions',
				'paypal_item_name' => 'Membership',
				'paypal_amount'    => '12.50',
				'repeat_time'      => 'W',
				'repeat_num'       => 2,
				'trial'            => 1,
				'trial_time'       => 'M',
				'trial_num'        => 3,
				'trial_amount'     => '2.75',
			),
			1,
			$notes
		);
		$this->assertSame( 'recurring', $value['type'], 'Subscriptions remain recurring.' );
		$this->assertSame( 'week', $value['interval'], 'The billing period is preserved.' );
		$this->assertSame( 2, $value['interval_count'], 'The billing interval count is preserved.' );
		$this->assertSame( '12.50', $value['amount'], 'Fixed amounts are preserved.' );
		$this->assertSame( 90, $value['trial_interval_count'], 'Month trials convert to days.' );
		$this->assertCount( 2, $notes, 'Trial approximation and paid trial changes are disclosed.' );
	}

	public function test_donations_report_the_conversion() {
		$notes = array();
		$value = FrmPayPalLiteLegacyMigrationHelper::convert_settings(
			array(
				'paypal_type'   => '_donations',
				'paypal_amount' => '5',
			),
			1,
			$notes
		);
		$this->assertSame( 'single', $value['type'], 'Donations become one-time payments.' );
		$this->assertCount( 1, $notes, 'The donation conversion is disclosed.' );
	}

	public function test_after_payment_rows_are_copied() {
		$notes = array();
		$value = FrmPayPalLiteLegacyMigrationHelper::convert_settings(
			array(
				'paypal_type'   => '_xclick',
				'paypal_amount' => '5',
				'change_field'  => array(
					3 => array(
						'id'     => '12',
						'status' => 'complete',
						'value'  => 'Paid [25]',
					),
					5 => array(
						'id'     => '13',
						'status' => 'failed',
						'value'  => 'Failed',
					),
					7 => array(
						'id'     => '',
						'status' => 'complete',
						'value'  => 'No field',
					),
					8 => array(
						'id'     => '14',
						'status' => '',
						'value'  => 'No status',
					),
				),
			),
			1,
			$notes
		);
		$this->assertSame(
			array(
				array(
					'id'     => 12,
					'status' => 'complete',
					'value'  => 'Paid [25]',
				),
				array(
					'id'     => 13,
					'status' => 'failed',
					'value'  => 'Failed',
				),
			),
			$value['change_field'],
			'Complete rows are copied with their statuses and shortcodes, and incomplete rows are dropped.'
		);
	}

	public function test_missing_after_payment_rows_are_not_added() {
		$notes = array();
		$value = FrmPayPalLiteLegacyMigrationHelper::convert_settings(
			array(
				'paypal_type'  => '_xclick',
				'change_field' => array(
					array(
						'id'     => '',
						'status' => '',
						'value'  => '',
					),
				),
			),
			1,
			$notes
		);
		$this->assertArrayNotHasKey( 'change_field', $value, 'Empty rows add no After Payment setting.' );
		$this->assertSame( array(), $notes, 'Empty rows add no notes.' );
	}

	public function test_after_payment_rows_need_a_supporting_pro_version() {
		$this->assertFalse( is_callable( 'FrmProTransLiteController::change_fields_after_payment' ), 'This test needs Pro without After Payment support.' );

		$method = array( 'FrmPayPalLiteLegacyMigrationHelper', 'get_after_payment_blocker' );
		$rows   = array(
			'change_field' => array(
				array(
					'id'     => 12,
					'status' => 'complete',
					'value'  => 'Paid',
				),
			),
		);

		$this->assertSame( '', $this->run_private_method( $method, array( array() ) ), 'Actions without After Payment rows can always convert.' );
		$this->assertStringContainsString( 'Activate Formidable Pro', $this->run_private_method( $method, array( $rows ) ), 'Without Pro, the conversion is blocked.' );

		add_filter( 'frm_pro_installed', '__return_true' );
		$blocker = $this->run_private_method( $method, array( $rows ) );
		remove_filter( 'frm_pro_installed', '__return_true' );
		$this->assertStringContainsString( 'Update Formidable Pro', $blocker, 'With an outdated Pro, the conversion asks for an update.' );
	}

	public function test_invalid_actions_are_rejected() {
		$result = FrmPayPalLiteLegacyMigrationHelper::migrate( 0 );
		$this->assertWPError( $result, 'Missing actions are rejected before conversion.' );
		$this->assertSame( 'frm_paypal_invalid_action', $result->get_error_code(), 'The rejection identifies the invalid action.' );
	}
}
