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
		$supported = is_callable( 'FrmProTransLiteController::change_fields_after_payment' ) || class_exists( 'FrmTransActionsController' );
		$method    = array( 'FrmPayPalLiteLegacyMigrationHelper', 'get_after_payment_blocker' );
		$rows      = array(
			'change_field' => array(
				array(
					'id'     => 12,
					'status' => 'complete',
					'value'  => 'Paid',
				),
			),
		);

		$this->assertSame( '', $this->run_private_method( $method, array( array() ) ), 'Actions without After Payment rows can always convert.' );
		$blocker = $this->run_private_method( $method, array( $rows ) );

		if ( $supported ) {
			$this->assertSame( '', $blocker, 'A loaded After Payment integration should allow conversion.' );
			return;
		}
		$this->assertStringContainsString( 'Activate Formidable Pro', $blocker, 'Without Pro, the conversion is blocked.' );

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

	/**
	 * Converts the saved action, moves only create emails and prevents a duplicate conversion.
	 */
	public function test_migrate_preserves_subscription_and_email_settings() {
		update_option( 'frm_paypal_connect_merchant_id_test', 'migration_fixture', false );
		update_option( 'frm_paypal_connect_merchant_id_live', 'migration_fixture', false );
		$form_id                      = $this->factory->form->create();
		$legacy_id                    = wp_insert_post(
			array(
				'post_type'    => FrmFormActionsController::$action_post_type,
				'post_excerpt' => 'paypal',
				'post_status'  => 'publish',
				'post_title'   => 'Gold membership',
				'menu_order'   => $form_id,
				'post_content' => wp_json_encode(
					array(
						'paypal_type'       => '_xclick-subscriptions',
						'paypal_amount'     => '9.99',
						'paypal_item_name'  => 'Gold',
						'currency'          => 'USD',
						'repeat_time'       => 'M',
						'paypal_stop_email' => 1,
					)
				),
			)
		);
		$email_control                = FrmFormActionsController::get_form_actions( 'email' );
		$email                        = $email_control->prepare_new( $form_id );
		$email->post_content['event'] = array( 'create', 'update', 'payment-success' );
		$email_id                     = $email_control->save_settings( $email );
		$other                        = $email_control->prepare_new( $form_id );
		$other->post_content['event'] = array( 'update' );
		$other_id                     = $email_control->save_settings( $other );
		FrmFormAction::clear_cache();

		$result = FrmPayPalLiteLegacyMigrationHelper::migrate( $legacy_id );
		$this->assertIsArray( $result, 'A valid legacy action should convert.' );
		$payment  = get_post( $result['action_id'] );
		$settings = json_decode( $payment->post_content, true );
		$this->assertSame( 'payment', $payment->post_excerpt, 'The new action should use Commerce.' );
		$this->assertSame( array( 'paypal' ), $settings['gateway'], 'The converted action should use PayPal.' );
		$this->assertSame( 'recurring', $settings['type'], 'Subscriptions should remain recurring.' );
		$this->assertSame( '9.99', $settings['amount'], 'The saved amount should survive conversion.' );
		$this->assertSame( 'month', $settings['interval'], 'The billing period should survive conversion.' );
		$this->assertSame( 'draft', get_post_status( $legacy_id ), 'The legacy action should be disabled.' );
		$moved = json_decode( get_post( $email_id )->post_content, true );
		$this->assertSame( array( 'update', 'payment-success' ), $moved['event'], 'Only create should move, without duplicating payment-success.' );
		$unchanged = json_decode( get_post( $other_id )->post_content, true );
		$this->assertSame( array( 'update' ), $unchanged['event'], 'Emails without a create event should remain unchanged.' );
		$again = FrmPayPalLiteLegacyMigrationHelper::migrate( $legacy_id );
		$this->assertWPError( $again, 'A second conversion should be blocked.' );
		$this->assertSame( 'frm_paypal_migration_blocked', $again->get_error_code(), 'The existing Commerce action should prevent duplicates.' );
	}
}
