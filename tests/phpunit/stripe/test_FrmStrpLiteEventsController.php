<?php

class test_FrmStrpLiteEventsController extends FrmStrpLiteUnitTest {

	/**
	 * @var FrmStrpLiteEventsController|null
	 */
	private $controller;

	/**
	 * @var stdClass|null
	 */
	private $form;

	/**
	 * @covers FrmStrpLiteEventsController::process_connect_events
	 * @covers FrmStrpLiteEventsController::should_skip_event
	 * @covers FrmStrpLiteEventsController::count_failed_event
	 * @covers FrmStrpLiteEventsController::track_handled_event
	 * @covers FrmStrpLiteConnectHelper::get_event
	 */
	public function test_process_connect_events() {
		$option_name = FrmStrpLiteEventsController::$events_to_skip_option_name;

		$this->assertFalse( get_option( $option_name ), 'Events should not be skipped before this function has ran.' );

		// Fake an event object and set it to the cache.
		// This also helps to test that the cache actually works.
		// Stripe events expire and can't be generated automatically during Unit Testing.
		$charge = new stdClass(); // Just leave the charge blank. This goal isn't to actually handle an event with this test.

		$invoice         = new stdClass();
		$invoice->charge = $charge;

		// This is just a partial object that has what we need for the code to run.
		$event               = new stdClass();
		$event->object       = 'event';
		$event->id           = 'evt_1CiPtv2eZvKYlo2CcUZsDcO6';
		$event->type         = ''; // Just leave this blank. This goal isn't to actually handle an event with this test.
		$event->livemode     = false;
		$event->data         = new stdClass();
		$event->data->object = $invoice;

		wp_cache_set( $event->id, $event, 'frm_strp' );

		// Confirm that FrmStrpLiteConnectHelper::get_event cache works separately.
		$helper_event = FrmStrpLiteConnectHelper::get_event( $event->id );
		$this->assertTrue( is_object( $helper_event ) );
		$this->assertEquals( $event->id, $helper_event->id );
		unset( $helper_event );

		// Set up the event controller for processing events.
		$this->controller = new FrmStrpLiteEventsController();

		$this->assert_should_skip_event( false, $event->id );

		// Process the event.
		$event_ids  = array( $event->id );
		$this->run_private_method( array( $this->controller, 'process_event_ids' ), array( $event_ids ) );

		$option = get_option( $option_name );
		$this->assertEquals( array( $event->id ), $option, 'The event should be included as a skipped event now.' );

		$this->assert_should_skip_event( true, $event->id );

		// Fake a broken event error message.
		$invalid_event_id = 'evt_1CiPsl2eZvKYlo2CVVyt3LKy';
		$event            = 'Stripe event does not exist';
		wp_cache_set( $invalid_event_id, $event, 'frm_strp' );

		$this->run_private_method( array( $this->controller, 'process_event_ids' ), array( array( $invalid_event_id ) ) );

		$this->delete_last_process_transient( $invalid_event_id );
		$this->assert_should_skip_event( false, $invalid_event_id, 'A failed event should be allowed to retry again.' );

		$this->delete_last_process_transient( $invalid_event_id );
		$this->run_private_method( array( $this->controller, 'process_event_ids' ), array( array( $invalid_event_id ) ) );

		$this->delete_last_process_transient( $invalid_event_id );
		$this->run_private_method( array( $this->controller, 'process_event_ids' ), array( array( $invalid_event_id ) ) );

		$this->assert_should_skip_event( true, $invalid_event_id, 'After several repeated attempts, stop trying to retrieve Stripe event.' );
	}

	private function assert_should_skip_event( $expected, $event_id, $message = '' ) {
		$should_skip_event = $this->run_private_method( array( $this->controller, 'should_skip_event' ), array( $event_id ) );
		$this->assertEquals( $expected, $should_skip_event, $message );
	}

	private function delete_last_process_transient( $event_id ) {
		delete_transient( 'frm_last_process_' . $event_id ); // This is to simulate the 60 seconds in between events so we don't need to wait.
	}

	/**
	 * @covers FrmStrpLiteEventsController::prepare_from_invoice
	 * @covers FrmStrpLiteEventsController::maybe_cancel_subscription
	 * @covers FrmStrpLiteActionsController::trigger_recurring_payment
	 */
	public function test_maybe_cancel_subscription() {
		$this->initialize_connect_api();
		$this->add_basic_shortcodes_for_testing();

		$this->form = $this->factory->form->create_and_get();
		$field_id   = FrmDb::get_var( 'frm_fields', array( 'form_id' => $this->form->id ) );

		// Make assertions where the subscription is expected to be cancelled.
		$this->make_payment_limit_assertion( 'future_cancel', 2 );
		$this->make_payment_limit_assertion( 'future_cancel', '[return2]' );
		// Test a field ID shortcode.
		// In create_a_test_entry we set this item meta value to 2.
		$this->make_payment_limit_assertion( 'future_cancel', '[' . $field_id . ']' );

		// Make assertions where the subscription should still be active.
		$this->make_payment_limit_assertion( 'active', 3 );
		$this->make_payment_limit_assertion( 'active', '[return3]' );
	}

	private function add_basic_shortcodes_for_testing() {
		add_shortcode(
			'return2',
			function() {
				return 2;
			}
		);
		add_shortcode(
			'return3',
			function() {
				return 3;
			}
		);
	}

	/**
	 * @param string     $expected_subscription_status
	 * @param string|int $repeat_limit
	 * @return void
	 */
	private function make_payment_limit_assertion( $expected_subscription_status, $repeat_limit ) {
		$success = $this->setup_first_recurring_payment( $this->create_payment_action_with_payment_limit( $repeat_limit ) );
		if ( ! $success ) {
			$this->fail();
		}

		$sub_id = $this->get_most_recent_subscription_id();
		if ( ! $sub_id ) {
			$this->fail();
		}

		// Get the ID of the first payment for the subscription.
		$payment_id = FrmDb::get_var(
			'frm_payments',
			array(
				'sub_id' => $sub_id,
			)
		);
		if ( ! $payment_id ) {
			$this->fail();
		}

		$frm_sub = new FrmTransLiteSubscription();
		$sub     = $frm_sub->get_one( $sub_id );

		// Give the first payment a charge ID and a complete status so the next payment counts as the 2nd payment.
		$frm_payment = new FrmTransLitePayment();
		$frm_payment->update(
			$payment_id,
			array(
				'receipt_id' => 'ch_' . uniqid(),
				'status'     => 'complete',
			)
		);

		// Create a second payment from a Stripe invoice object (simulating a webhook event).
		$payment = $this->prepare_from_invoice( $this->get_fake_stripe_invoice_object( $sub->sub_id ) );

		// Make assertion that subscription is now cancelled after reaching the payment limit.
		$sub = $frm_sub->get_one( $sub_id );
		$this->assertEquals( $expected_subscription_status, $sub->status );

		// Make assertions for prepare_from_invoice result.
		$this->assertIsObject( $payment );
		$this->assertEquals( 'complete', $payment->status );
		$this->assertEquals( 'stripe', $payment->paysys );
		$this->assertEquals( '1', $payment->test );
		$this->assertEquals( '10.00', $payment->amount );
	}

	/**
	 * @param WP_Post $action
	 * @return bool True on success.
	 */
	private function setup_first_recurring_payment( $action ) {
		$customer = $this->get_customer();
		$atts     = array(
			'customer' => $customer,
			'action'   => $action,
			'amount'   => 1000,
			'entry'    => $this->create_a_test_entry(),
		);

		$this->add_card( $customer->id );
		$this->run_migrations();
		$success = $this->trigger_recurring_payment( $atts );
		return $success;
	}

	/**
	 * Get an object that has the required properties to test Stripe events.
	 * This is the same structure as a Stripe invoice object, but without all of the data.
	 *
	 * @param string $sub_id Stripe subscription ID starting with "sub_".
	 * @return stdClass
	 */
	private function get_fake_stripe_invoice_object( $sub_id ) {
		$period        = new stdClass();
		$period->start = strtotime( gmdate( 'Y-01-01' ) );
		$period->end   = strtotime( gmdate( 'Y-02-01' ) );

		$line         = new stdClass();
		$line->amount = 1000;
		$line->period = $period;

		$invoice               = new stdClass();
		$invoice->subscription = $sub_id;
		$invoice->lines        = new stdClass();
		$invoice->lines->data  = array( $line );

		return $invoice;
	}

	/**
	 * Get the new subscription ID from the subscription table.
	 *
	 * @return int
	 */
	private function get_most_recent_subscription_id() {
		// Do not use FrmDb because of caching.
		global $wpdb;
		return (int) $wpdb->get_var( 'SELECT id FROM ' . $wpdb->prefix . 'frm_subscriptions ORDER BY id DESC LIMIT 1' );
	}

	/**
	 * @param int $payment_limit
	 * @return object|false|null
	 */
	private function create_payment_action_with_payment_limit( $payment_limit ) {
		$action_id = $this->factory->post->create(
			array(
				'menu_order'   => $this->form->id,
				'post_content' => json_encode(
					array(
						'currency'          => 'usd',
						'payment_limit'     => $payment_limit,
						'plan_id'           => $this->maybe_create_plan(),
						'interval'          => 'year',
						'interval_count'    => 1,
						'trial_period_days' => 0,
					)
				),
			)
		);
		return FrmFormAction::get_single_action_type( $action_id, 'payment' );
	}

	/**
	 * @return object
	 */
	private function create_a_test_entry() {
		$field_id                             = FrmDb::get_var( 'frm_fields', array( 'form_id' => $this->form->id ) );
		$entry_data                           = $this->factory->field->generate_entry_array( $this->form );
		$entry_data['item_meta'][ $field_id ] = 2;
		return $this->factory->entry->create_and_get( $entry_data );
	}

	private function run_private_strp_actions_controller_function( $function, ...$params ) {
		return $this->run_private_method( array( 'FrmStrpLiteActionsController', $function ), $params );
	}

	private function trigger_recurring_payment( $atts ) {
		return $this->run_private_strp_actions_controller_function( 'trigger_recurring_payment', $atts );
	}

	/**
	 * @param object $invoice
	 * @return object|array|false|null
	 */
	private function prepare_from_invoice( $invoice ) {
		$controller = new FrmStrpLiteEventsController();
		$this->set_private_property( $controller, 'invoice', $invoice );
		$this->set_private_property( $controller, 'charge', 'ch_123' );
		$this->set_private_property( $controller, 'status', 'complete' );
		$event           = new stdClass();
		$event->livemode = false;
		$this->set_private_property( $controller, 'event', $event );
		return $this->run_private_method( array( $controller, 'prepare_from_invoice' ) );
	}
}
