<?php

/**
 * @group stripe
 */
class test_FrmStrpLiteEventsController extends FrmUnitTest {

	/**
	 * Option that stripe_connect_is_setup( 'test' ) reads.
	 *
	 * @var string
	 */
	private $connected_option = 'frm_strp_connect_details_submitted_test';

	/**
	 * @var string
	 */
	private $hook = 'frm_strp_pull_connect_events';

	/**
	 * @var string
	 */
	private $lock = 'frm_strp_connect_pull_lock';

	public function tear_down() {
		delete_option( $this->connected_option );
		delete_transient( $this->lock );
		wp_clear_scheduled_hook( $this->hook );
		remove_all_filters( 'frm_strp_connect_pull_interval' );
		parent::tear_down();
	}

	/**
	 * @covers FrmStrpLiteEventsController::maybe_schedule_connect_pull
	 */
	public function test_schedules_hourly_when_stripe_connected() {
		update_option( $this->connected_option, 1 );

		FrmStrpLiteEventsController::maybe_schedule_connect_pull();

		$event = wp_get_scheduled_event( $this->hook );
		$this->assertNotFalse( $event, 'Backstop cron should be scheduled when Stripe is connected.' );
		$this->assertSame( 'hourly', $event->schedule );
	}

	/**
	 * @covers FrmStrpLiteEventsController::maybe_schedule_connect_pull
	 */
	public function test_clears_schedule_when_stripe_not_connected() {
		delete_option( $this->connected_option );
		wp_schedule_event( time(), 'hourly', $this->hook );
		$this->assertNotFalse( wp_next_scheduled( $this->hook ) );

		FrmStrpLiteEventsController::maybe_schedule_connect_pull();

		$this->assertFalse(
			wp_next_scheduled( $this->hook ),
			'Backstop cron should be cleared when Stripe is not connected (self-heal).'
		);
	}

	/**
	 * @covers FrmStrpLiteEventsController::maybe_schedule_connect_pull
	 */
	public function test_reschedules_when_interval_filter_changes() {
		update_option( $this->connected_option, 1 );

		FrmStrpLiteEventsController::maybe_schedule_connect_pull();
		$this->assertSame( 'hourly', wp_get_scheduled_event( $this->hook )->schedule );

		add_filter(
			'frm_strp_connect_pull_interval',
			function () {
				return 'twicedaily';
			}
		);
		FrmStrpLiteEventsController::maybe_schedule_connect_pull();

		$this->assertSame( 'twicedaily', wp_get_scheduled_event( $this->hook )->schedule );
	}

	/**
	 * @covers FrmStrpLiteEventsController::get_connect_pull_recurrence
	 */
	public function test_recurrence_falls_back_to_hourly_for_unregistered_schedule() {
		add_filter(
			'frm_strp_connect_pull_interval',
			function () {
				return 'not_a_real_schedule';
			}
		);

		$recurrence = $this->run_private_method(
			array( 'FrmStrpLiteEventsController', 'get_connect_pull_recurrence' ),
			array()
		);

		$this->assertSame( 'hourly', $recurrence );
	}

	/**
	 * @covers FrmStrpLiteEventsController::get_connect_pull_recurrence
	 */
	public function test_recurrence_honours_registered_custom_schedule() {
		add_filter(
			'cron_schedules',
			function ( $schedules ) {
				$schedules['frm_test_five_min'] = array(
					'interval' => 300,
					'display'  => 'Every 5 minutes (test)',
				);
				return $schedules;
			}
		);
		add_filter(
			'frm_strp_connect_pull_interval',
			function () {
				return 'frm_test_five_min';
			}
		);

		$recurrence = $this->run_private_method(
			array( 'FrmStrpLiteEventsController', 'get_connect_pull_recurrence' ),
			array()
		);

		$this->assertSame( 'frm_test_five_min', $recurrence );
	}

	/**
	 * A locked pull must return early and leave the lock in place, so a cron run
	 * cannot process events concurrently with a relay-triggered pull.
	 *
	 * @covers FrmStrpLiteEventsController::run_scheduled_pull
	 */
	public function test_run_scheduled_pull_noops_while_locked() {
		set_transient( $this->lock, 1, 2 * MINUTE_IN_SECONDS );

		FrmStrpLiteEventsController::run_scheduled_pull();

		$this->assertNotFalse(
			get_transient( $this->lock ),
			'A locked pull must return early without releasing the lock.'
		);
	}
}
