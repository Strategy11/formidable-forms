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
		$this->assertCount( 3, $notes, 'Trial approximation, paid trial changes and existing subscriptions are disclosed.' );
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
		$supported = $this->run_private_method( array( 'FrmPayPalLiteLegacyMigrationHelper', 'after_payment_is_supported' ) );
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

	public function test_missing_currency_uses_the_legacy_global_currency() {
		$notes = array();
		update_option( 'frm_paypal_options', (object) array( 'currency' => 'EUR' ), false );
		$value = FrmPayPalLiteLegacyMigrationHelper::convert_settings( array( 'paypal_type' => '_xclick' ), 1, $notes );
		$this->assertSame( 'eur', $value['currency'], 'Actions without a currency use the PayPal add-on currency.' );

		delete_option( 'frm_paypal_options' );
		$value = FrmPayPalLiteLegacyMigrationHelper::convert_settings( array( 'paypal_type' => '_xclick' ), 1, $notes );
		$this->assertSame( 'usd', $value['currency'], 'Without any legacy currency, USD is used like in the PayPal add-on.' );
	}

	/**
	 * A second active legacy action would keep charging through PayPal Standard next to the new action.
	 */
	public function test_migrate_is_blocked_while_another_legacy_action_is_active() {
		update_option( 'frm_paypal_connect_merchant_id_test', 'migration_fixture', false );
		update_option( 'frm_paypal_connect_merchant_id_live', 'migration_fixture', false );
		$form_id   = $this->factory->form->create();
		$legacy    = array(
			'post_type'    => FrmFormActionsController::$action_post_type,
			'post_excerpt' => 'paypal',
			'post_status'  => 'publish',
			'menu_order'   => $form_id,
			'post_content' => wp_json_encode(
				array(
					'paypal_type'   => '_xclick',
					'paypal_amount' => '5',
					'currency'      => 'USD',
				)
			),
		);
		$legacy_id = wp_insert_post( $legacy );
		$other_id  = wp_insert_post( array( 'post_excerpt' => 'paypal-legacy' ) + $legacy );
		FrmFormAction::clear_cache();

		$result = FrmPayPalLiteLegacyMigrationHelper::migrate( $legacy_id );
		$this->assertWPError( $result, 'Another active legacy action should block the conversion.' );
		$this->assertSame( 'frm_paypal_migration_blocked', $result->get_error_code(), 'The conversion is blocked, not failed.' );
		$this->assertSame( 'publish', get_post_status( $legacy_id ), 'The legacy action should stay on when blocked.' );
		$this->assertFalse( FrmFormAction::form_has_action_type( $form_id, 'payment' ), 'No payment action should be created when blocked.' );

		wp_update_post(
			array(
				'ID'          => $other_id,
				'post_status' => 'draft',
			)
		);
		FrmFormAction::clear_cache();
		$this->assertIsArray( FrmPayPalLiteLegacyMigrationHelper::migrate( $legacy_id ), 'A turned off legacy action should not block the conversion.' );
	}

	/**
	 * Commerce is rolled back if WordPress refuses to turn the legacy action off.
	 */
	public function test_migrate_rolls_back_when_legacy_action_cannot_be_disabled() {
		update_option( 'frm_paypal_connect_merchant_id_test', 'migration_fixture', false );
		update_option( 'frm_paypal_connect_merchant_id_live', 'migration_fixture', false );
		$form_id   = $this->factory->form->create();
		$legacy_id = wp_insert_post(
			array(
				'post_type'    => FrmFormActionsController::$action_post_type,
				'post_excerpt' => 'paypal',
				'post_status'  => 'publish',
				'menu_order'   => $form_id,
				'post_content' => wp_json_encode(
					array(
						'paypal_type'   => '_xclick',
						'paypal_amount' => '5',
						'currency'      => 'USD',
					)
				),
			)
		);
		$fail      = static function ( $empty, $post ) use ( $legacy_id ) {
			return $empty || ( (int) ( $post['ID'] ?? 0 ) === $legacy_id && 'draft' === $post['post_status'] );
		};

		add_filter( 'wp_insert_post_empty_content', $fail, 10, 2 );
		try {
			$result = FrmPayPalLiteLegacyMigrationHelper::migrate( $legacy_id );
		} finally {
			remove_filter( 'wp_insert_post_empty_content', $fail, 10 );
		}

		$this->assertWPError( $result );
		$this->assertSame( 'frm_paypal_migration_failed', $result->get_error_code() );
		$this->assertSame( 'publish', get_post_status( $legacy_id ) );
		$this->assertFalse( FrmFormAction::form_has_action_type( $form_id, 'payment' ) );
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
		$paid                         = $email_control->prepare_new( $form_id );
		$paid->post_content['event']  = array( 'paypal', 'paypal-failed' );
		$paid_id                      = $email_control->save_settings( $paid );
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
		$retriggered = json_decode( get_post( $paid_id )->post_content, true );
		$this->assertSame(
			array( 'paypal', 'paypal-failed', 'payment-success', 'payment-failed' ),
			$retriggered['event'],
			'Legacy payment events should gain the matching payment events.'
		);
		$again = FrmPayPalLiteLegacyMigrationHelper::migrate( $legacy_id );
		$this->assertWPError( $again, 'A second conversion should be blocked.' );
		$this->assertSame( 'frm_paypal_migration_blocked', $again->get_error_code(), 'The existing Commerce action should prevent duplicates.' );
	}

	/**
	 * The Successful Payment trigger never runs when conditional logic skips the payment,
	 * so moving the emails would stop them for entries that do not pay.
	 */
	public function test_migrate_leaves_emails_alone_when_payment_is_conditional() {
		update_option( 'frm_paypal_connect_merchant_id_test', 'migration_fixture', false );
		update_option( 'frm_paypal_connect_merchant_id_live', 'migration_fixture', false );
		$form_id                      = $this->factory->form->create();
		$legacy_id                    = wp_insert_post(
			array(
				'post_type'    => FrmFormActionsController::$action_post_type,
				'post_excerpt' => 'paypal',
				'post_status'  => 'publish',
				'post_title'   => 'Optional donation',
				'menu_order'   => $form_id,
				'post_content' => wp_json_encode(
					array(
						'paypal_type'       => '_xclick',
						'paypal_amount'     => '5',
						'currency'          => 'USD',
						'paypal_stop_email' => 1,
						'conditions'        => array(
							'send_stop' => 'send',
							'any_all'   => 'any',
							array(
								'hide_field'      => 45,
								'hide_field_cond' => '==',
								'hide_opt'        => 'yes',
							),
						),
					)
				),
			)
		);
		$email_control                = FrmFormActionsController::get_form_actions( 'email' );
		$email                        = $email_control->prepare_new( $form_id );
		$email->post_content['event'] = array( 'create' );
		$email_id                     = $email_control->save_settings( $email );
		FrmFormAction::clear_cache();

		$result = FrmPayPalLiteLegacyMigrationHelper::migrate( $legacy_id );
		$this->assertIsArray( $result, 'A conditional legacy action should convert.' );
		$unchanged = json_decode( get_post( $email_id )->post_content, true );
		$this->assertSame( array( 'create' ), $unchanged['event'], 'Emails should keep sending for entries that skip the payment.' );
		$this->assertStringContainsString( 'conditional logic', implode( ' ', $result['notes'] ), 'The unchanged emails should be disclosed.' );
	}
}
