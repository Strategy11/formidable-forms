<?php

/**
 * @covers FrmEmailSummaryHelper
 */
#[\PHPUnit\Framework\Attributes\CoversClass( FrmEmailSummaryHelper::class )]
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

		$this->assertArrayHasKey( 'last_monthly', $options );
		$this->assertArrayHasKey( 'last_yearly', $options );
		$this->assertArrayHasKey( 'renewal_date', $options );
	}

	public function test_get_date_obj() {
		$date = $this->run_private_method(
			array( 'FrmEmailSummaryHelper', 'get_date_obj' ),
			array( '2023-08-13' )
		);

		$this->assertInstanceOf( \DateTime::class, $date );
		$this->assertSame( '2023-08-13', $date->format( 'Y-m-d' ) );

		$this->assertFalse(
			$this->run_private_method(
				array( 'FrmEmailSummaryHelper', 'get_date_obj' ),
				array( '2023-08-' )
			)
		);
	}

	public function test_get_date_diff() {
		$this->assertSame(
			4,
			$this->run_private_method(
				array( 'FrmEmailSummaryHelper', 'get_date_diff' ),
				array( '2023-08-12', '2023-08-16' )
			)
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
		$this->assertSame(
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

		$this->assertSame( $expected, FrmEmailSummaryHelper::should_send_emails() );

		// Yearly was sent less than 1 month ago.
		$options['last_yearly'] = FrmEmailSummaryHelper::get_date_from_today( '-29 days' );
		$this->save_options( $options );
		$this->assertSame( array(), FrmEmailSummaryHelper::should_send_emails() );

		// Yearly was sent less than 1 month ago, and monthly was sent over 1 month ago.
		$options['last_monthly'] = FrmEmailSummaryHelper::get_date_from_today( '-30 days' );
		$this->save_options( $options );
		$this->assertSame( array(), FrmEmailSummaryHelper::should_send_emails() );

		// Both yearly and monthly were sent over 1 month ago.
		$options['last_monthly'] = FrmEmailSummaryHelper::get_date_from_today( '-31 days' );
		$options['last_yearly']  = $options['last_monthly'];
		$this->save_options( $options );
		$this->assertSame( array( 'monthly' ), FrmEmailSummaryHelper::should_send_emails() );

		// Monthly was sent over 1 month ago, yearly was sent over 1 year ago.
		$options['last_yearly'] = FrmEmailSummaryHelper::get_date_from_today( '-365 days' );
		$this->save_options( $options );
		$this->assertSame( array( 'yearly' ), FrmEmailSummaryHelper::should_send_emails() );

		// Nothing set, except renewal date is coming.
		$options = array(
			'last_monthly' => '',
			'last_yearly'  => '',
			'last_license' => '',
			'renewal_date' => FrmEmailSummaryHelper::get_date_from_today( '+1 day' ),
		);
		$this->save_options( $options );
		$this->assertSame( array( 'yearly' ), FrmEmailSummaryHelper::should_send_emails() );

		// Nothing set, renewal date is coming in more than 45 days.
		$options['renewal_date'] = FrmEmailSummaryHelper::get_date_from_today( '+46 days' );
		$this->save_options( $options );
		$this->assertSame( array( 'monthly' ), FrmEmailSummaryHelper::should_send_emails() );

		// Renewal date is coming, but monthly was sent less than 30 days ago.
		$options['last_monthly'] = FrmEmailSummaryHelper::get_date_from_today( '-29 days' );
		$options['renewal_date'] = FrmEmailSummaryHelper::get_date_from_today( '+1 day' );
		$this->save_options( $options );
		$this->assertSame( array(), FrmEmailSummaryHelper::should_send_emails() );

		// Renewal date is coming, but yearly was sent less than 1 year ago.
		$options['last_monthly'] = '';
		$options['last_yearly']  = FrmEmailSummaryHelper::get_date_from_today( '-300 days' );
		$this->save_options( $options );
		$this->assertSame( array( 'monthly' ), FrmEmailSummaryHelper::should_send_emails() );

		// Renewal date is coming in more than 45 days, but yearly was sent more than 1 year ago.
		$options['last_yearly'] = FrmEmailSummaryHelper::get_date_from_today( '-365 days' );
		$this->save_options( $options );
		$this->assertSame( array( 'yearly' ), FrmEmailSummaryHelper::should_send_emails() );
	}

	private function save_options( $options ) {
		$this->run_private_method(
			array( 'FrmEmailSummaryHelper', 'save_options' ),
			array( $options )
		);
	}

	public function test_maybe_remove_recipients_from_api() {
		// Clear the cache so our fake response gets used.
		$api = new FrmFormApi();
		delete_option( $api->get_cache_key() );

		add_filter(
			'pre_http_request',
			function ( $pre, $parsed_args, $url ) {
				if ( ! str_contains( $url, 'formidableforms.com' ) ) {
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
		$this->assertSame( '', $recipients );

		$recipients = 'test@example.com,recipient2@example.com';
		FrmEmailSummaryHelper::maybe_remove_recipients_from_api( $recipients );
		$this->assertSame( 'recipient2@example.com', $recipients );
	}

	public function test_get_top_forms() {
		$form_a = $this->factory->form->create_and_get();
		$form_b = $this->factory->form->create_and_get();

		$this->factory->entry->create( $this->factory->field->generate_entry_array( $form_a ) );
		$this->factory->entry->create( $this->factory->field->generate_entry_array( $form_a ) );
		$this->factory->entry->create( $this->factory->field->generate_entry_array( $form_b ) );

		$top_forms = FrmEmailSummaryHelper::get_top_forms( gmdate( 'Y-m-d', strtotime( '-1 day' ) ), gmdate( 'Y-m-d' ) );
		$by_form   = array();

		foreach ( $top_forms as $row ) {
			$by_form[ (int) $row->form_id ] = (int) $row->items_count;
		}

		$this->assertArrayHasKey( $form_a->id, $by_form );
		$this->assertArrayHasKey( $form_b->id, $by_form );
		$this->assertSame( 2, $by_form[ $form_a->id ] );
		$this->assertSame( 1, $by_form[ $form_b->id ] );
	}

	/**
	 * Only installed add-ons with a newer version are listed, by display name, as a list.
	 *
	 * @return void
	 */
	public function test_get_out_of_date_plugins() {
		$license = 'frm-test-license';
		$api     = new FrmFormApi( $license );
		delete_option( $api->get_cache_key() );

		add_filter( 'frm_pro_installed', '__return_true' );
		add_filter(
			'frm_installed_addons',
			function () use ( $license ) {
				return array(
					(object) array(
						'store_url' => 'https://formidableforms.com',
						'license'   => $license,
					),
				);
			}
		);
		add_filter(
			'pre_http_request',
			function ( $pre, $parsed_args, $url ) {
				if ( ! str_contains( $url, 'formidableforms.com' ) ) {
					return $pre;
				}

				$addon = static function ( $plugin, $version, $name ) {
					return array(
						'plugin'       => $plugin,
						'new_version'  => $version,
						'package'      => 'https://example.com/' . $plugin . '.zip',
						'display_name' => $name,
					);
				};

				return array(
					'response' => array(
						'code'    => 200,
						'message' => 'Fake response',
					),
					'body'     => json_encode(
						array(
							101 => $addon( 'frm-test-outdated/frm-test-outdated.php', '2.0', 'Outdated Add-On' ),
							102 => $addon( 'frm-test-current/frm-test-current.php', '1.0', 'Current Add-On' ),
							103 => $addon( 'frm-test-missing/frm-test-missing.php', '9.0', 'Missing Add-On' ),
							104 => $addon( 'frm-test-second/frm-test-second.php', '3.1', 'Second Outdated Add-On' ),
						)
					),
				);
			},
			10,
			3
		);

		// get_plugins() returns this cached list instead of reading the plugins directory.
		wp_cache_set(
			'plugins',
			array(
				'' => array(
					'frm-test-outdated/frm-test-outdated.php' => array( 'Version' => '1.0' ),
					'frm-test-current/frm-test-current.php'   => array( 'Version' => '1.0' ),
					'frm-test-second/frm-test-second.php' => array( 'Version' => '3.0' ),
				),
			),
			'plugins'
		);

		$plugins = FrmEmailSummaryHelper::get_out_of_date_plugins();

		wp_cache_delete( 'plugins', 'plugins' );
		delete_option( $api->get_cache_key() );

		$this->assertSame( array( 'Outdated Add-On', 'Second Outdated Add-On' ), $plugins );
	}

	/**
	 * Lite on its own never reports add-on updates.
	 *
	 * @return void
	 */
	public function test_get_out_of_date_plugins_is_empty_without_pro() {
		add_filter( 'frm_pro_installed', '__return_false', 99 );
		$this->assertSame( array(), FrmEmailSummaryHelper::get_out_of_date_plugins() );
	}
}
