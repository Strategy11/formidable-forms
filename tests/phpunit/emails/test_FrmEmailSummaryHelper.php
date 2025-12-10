<?php

class test_FrmEmailSummaryHelper extends FrmUnitTest {

	public static function wpSetUpBeforeClass() {
		$_POST = array();
		self::empty_tables();
		self::frm_install();
	}

	public function test_get_options() {
		$options = $this->run_private_method(
			array( 'FrmEmailSummaryHelper', 'get_options' )
		);

		$this->assertTrue( isset( $options['last_monthly'] ) );
		$this->assertTrue( isset( $options['last_yearly'] ) );
		$this->assertTrue( isset( $options['renewal_date'] ) );
	}

	public function test_get_date_obj() {
		$date = $this->run_private_method(
			array( 'FrmEmailSummaryHelper', 'get_date_obj' ),
			array( '2023-08-13' )
		);

		$this->assertTrue( $date instanceof DateTime );
		$this->assertEquals( $date->format( 'Y-m-d' ), '2023-08-13' );

		$this->assertFalse(
			$this->run_private_method(
				array( 'FrmEmailSummaryHelper', 'get_date_obj' ),
				array( '2023-08-' )
			)
		);
	}

	public function test_get_date_diff() {
		$this->assertEquals(
			$this->run_private_method(
				array( 'FrmEmailSummaryHelper', 'get_date_diff' ),
				array( '2023-08-12', '2023-08-16' )
			),
			4
		);

		$this->assertFalse(
			$this->run_private_method(
				array( 'FrmEmailSummaryHelper', 'get_date_diff' ),
				array( '2023-08-', '2023-08-16' )
			)
		);
	}

	public function test_get_earliest_form_created_date() {
		$form = FrmForm::getAll( array(), 'id ASC', 1 );
		$this->assertNotEmpty( $form );
		$this->assertEquals(
			$form->created_at,
			$this->run_private_method(
				array( 'FrmEmailSummaryHelper', 'get_earliest_form_created_date' )
			)
		);
	}

	public function test_should_send_email() {
		// Nothing set yet.
		$options = array(
			'last_monthly' => '',
			'last_yearly'  => '',
			'renewal_date' => '',
		);
		$this->save_options( $options );

		// Test against the actual renewal date when running GitHub workflow.
		$renewal_date = $this->run_private_method(
			array( 'FrmEmailSummaryHelper', 'get_renewal_date' )
		);

		if ( FrmEmailSummaryHelper::get_date_from_today( '+' . FrmEmailSummaryHelper::BEFORE_RENEWAL_PERIOD . ' days' ) < $renewal_date ) {
			$expected = array( 'monthly' );
		} else {
			$expected = array( 'yearly' );
		}

		$this->assertEquals( $expected, FrmEmailSummaryHelper::should_send_emails() );

		// Yearly was sent less than 1 month ago.
		$options['last_yearly'] = FrmEmailSummaryHelper::get_date_from_today( '-29 days' );
		$this->save_options( $options );
		$this->assertEquals( array(), FrmEmailSummaryHelper::should_send_emails() );

		// Yearly was sent less than 1 month ago, and monthly was sent over 1 month ago.
		$options['last_monthly'] = FrmEmailSummaryHelper::get_date_from_today( '-30 days' );
		$this->save_options( $options );
		$this->assertEquals( array(), FrmEmailSummaryHelper::should_send_emails() );

		// Both yearly and monthly were sent over 1 month ago.
		$options['last_monthly'] = FrmEmailSummaryHelper::get_date_from_today( '-31 days' );
		$options['last_yearly']  = $options['last_monthly'];
		$this->save_options( $options );
		$this->assertEquals( array( 'monthly' ), FrmEmailSummaryHelper::should_send_emails() );

		// Monthly was sent over 1 month ago, yearly was sent over 1 year ago.
		$options['last_yearly'] = FrmEmailSummaryHelper::get_date_from_today( '-365 days' );
		$this->save_options( $options );
		$this->assertEquals( array( 'yearly' ), FrmEmailSummaryHelper::should_send_emails() );

		// Nothing set, except renewal date is coming.
		$options = array(
			'last_monthly' => '',
			'last_yearly'  => '',
			'last_license' => '',
			'renewal_date' => FrmEmailSummaryHelper::get_date_from_today( '+1 day' ),
		);
		$this->save_options( $options );
		$this->assertEquals( array( 'yearly' ), FrmEmailSummaryHelper::should_send_emails() );

		// Nothing set, renewal date is coming in more than 45 days.
		$options['renewal_date'] = FrmEmailSummaryHelper::get_date_from_today( '+46 days' );
		$this->save_options( $options );
		$this->assertEquals( array( 'monthly' ), FrmEmailSummaryHelper::should_send_emails() );

		// renewal date is coming, but monthly was sent less than 30 days ago.
		$options['last_monthly'] = FrmEmailSummaryHelper::get_date_from_today( '-29 days' );
		$options['renewal_date'] = FrmEmailSummaryHelper::get_date_from_today( '+1 day' );
		$this->save_options( $options );
		$this->assertEquals( array(), FrmEmailSummaryHelper::should_send_emails() );

		// renewal date is coming, but yearly was sent less than 1 year ago.
		$options['last_monthly'] = '';
		$options['last_yearly']  = FrmEmailSummaryHelper::get_date_from_today( '-300 days' );
		$this->save_options( $options );
		$this->assertEquals( array( 'monthly' ), FrmEmailSummaryHelper::should_send_emails() );

		// renewal date is coming in more than 45 days, but yearly was sent more than 1 year ago.
		$options['last_yearly'] = FrmEmailSummaryHelper::get_date_from_today( '-365 days' );
		$this->save_options( $options );
		$this->assertEquals( array( 'yearly' ), FrmEmailSummaryHelper::should_send_emails() );
	}

	private function save_options( $options ) {
		$this->run_private_method(
			array( 'FrmEmailSummaryHelper', 'save_options' ),
			array( $options )
		);
	}

	/**
	 * @covers FrmEmailSummaryHelper::maybe_remove_recipients_from_api
	 */
	public function test_maybe_remove_recipients_from_api() {
		// Clear the cache so our fake response gets used.
		$api = new FrmFormApi();
		delete_option( $api->get_cache_key() );

		add_filter(
			'pre_http_request',
			function ( $pre, $parsed_args, $url ) {
				if ( strpos( $url, 'formidableforms.com' ) === false ) {
					return $pre;
				}

				return array(
					'response' => array(
						'code'    => 200,
						'message' => 'Fake response',
					),
					'body'     => json_encode( array( 'no_emails' => 'test@example.com' ) ),
				);
			},
			10,
			3
		);

		$recipients = 'test@example.com';
		FrmEmailSummaryHelper::maybe_remove_recipients_from_api( $recipients );
		$this->assertEquals( '', $recipients );

		$recipients = 'test@example.com,recipient2@example.com';
		FrmEmailSummaryHelper::maybe_remove_recipients_from_api( $recipients );
		$this->assertEquals( 'recipient2@example.com', $recipients );
	}
}
