<?php

class FrmStrpLiteUnitTest extends FrmUnitTest {

	public static function wpSetUpBeforeClass() {
		parent::wpSetUpBeforeClass();
		self::empty_tables();
		self::frm_install();
	}

	/**
	 * @var string Used to limit the number of accounts when testing: the static scope variables stick around from test to test.
	 */
	protected static $shared_account_id;

	/**
	 * @var string Used to limit the number of accounts when testing: the static scope variables stick around from test to test.
	 */
	protected static $shared_connect_url;

	/**
	 * @var string Used to limit the number of accounts when testing: the static scope variables stick around from test to test.
	 */
	protected static $shared_client_side_token;

	/**
	 * @var string Used to limit the number of accounts when testing: the static scope variables stick around from test to test.
	 */
	protected static $shared_server_side_token;

	/**
	 * @var string The active customer we're testing with (might be null if no customer has been created)
	 */
	protected $customer_id;

	/**
	 * @var string The active test account id.
	 */
	protected $account_id;

	/**
	 * @var array
	 */
	protected $plan_options;

	/**
	 * @var WP_Post|null Used in self::get_simple_stripe_action.
	 */
	protected $action;

	/**
	 * @var array
	 */
	protected $subscription_charge_options;

	protected $use_test_credit_card_number;

	public function setUp(): void {
		parent::setUp();

		// Reset settings so modified settings in one test don't break other tests.
		$this->set_private_property( 'FrmStrpLiteAppHelper', 'settings', null );
	}

	protected function initialize_connect_api( $user_id = 1 ) {
		wp_set_current_user( $user_id );

		if ( 1 === $user_id ) {
			$this->set_user_by_role( 'administrator' );
		}

		update_option( 'frm_strp_connect_details_submitted_test', true, 'no' );
		FrmStrpLiteAppHelper::should_use_stripe_connect(); // this line loads FrmStrpLiteConnectApiAdapter if it does not exist.

		if ( ! empty( self::$shared_account_id ) ) {
			return $this->reuse_static_account_details();
		}

		$initialized = $this->run_private_method( array( 'FrmStrpLiteConnectHelper', 'initialize' ), array() );

		if ( ! $initialized ) {
			$this->fail();
		}

		$this->set_static_account_details_for_reuse( $initialized );
		return $initialized;
	}

	/**
	 * Fake the payload on repeat calls to avoid creating too many accounts.
	 */
	private function reuse_static_account_details() {
		$initialized              = new stdClass();
		$initialized->account_id  = self::$shared_account_id;
		$initialized->connect_url = self::$shared_connect_url;
		$initialized->password    = self::$shared_server_side_token;
		$this->account_id         = self::$shared_account_id;
		update_option( 'frm_strp_connect_account_id_test', self::$shared_account_id, 'no' );
		update_option( 'frm_strp_connect_client_password_test', self::$shared_client_side_token, 'no' );
		update_option( 'frm_strp_connect_server_password_test', self::$shared_server_side_token, 'no' );
		return $initialized;
	}

	/**
	 * @param object $initialized
	 */
	private function set_static_account_details_for_reuse( $initialized ) {
		self::$shared_account_id        = $initialized->account_id;
		self::$shared_connect_url       = $initialized->connect_url;
		self::$shared_server_side_token = $initialized->password;
		self::$shared_client_side_token = get_option( 'frm_strp_connect_client_password_test' );
		$this->account_id               = $initialized->account_id;
	}

	protected function get_customer( $options = array() ) {
		$this->include_adapter();
		return FrmStrpLiteConnectApiAdapter::get_customer( $options );
	}

	protected function include_adapter() {
		if ( ! class_exists( 'FrmStrpLiteConnectApiAdapter' ) ) {
			require dirname( __DIR__ ) . '/helpers/FrmStrpLiteConnectApiAdapter.php';
		}
	}

	protected function add_card( $customer_id ) {
		$stripe = $this->get_authenticated_stripe_client();
		$card   = $stripe->customers->createSource(
			$customer_id,
			array(
				'source' => 'tok_mastercard',
			),
			$this->get_stripe_account_id_details()
		);
		$stripe->customers->update(
			$customer_id,
			array(
				'default_source' => $card->id,
			),
			$this->get_stripe_account_id_details()
		);
		return $card;
	}

	protected function get_stripe_account_id_details() {
		return array(
			'stripe_account' => self::$shared_account_id,
		);
	}

	protected function get_plan_options() {
		$unique          = uniqid();
		$default_options = array(
			'amount'            => 1000,
			'interval'          => 'year',
			'interval_count'    => 1,
			'currency'          => 'usd',
			'id'                => 'my_annual_test_subscription_' . $unique,
			'name'              => 'My Annual Test Subscription (' . $unique . ')',
			'trial_period_days' => 10,
		);
		return array_filter(
			array_merge(
				$default_options,
				$this->plan_options ?? array()
			)
		);
	}

	protected function create_payment_intent_with_action_id( $action_id ) {
		$stripe = $this->get_authenticated_stripe_client();
		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		return $stripe->paymentIntents->create( $this->get_new_charge_data( $action_id ) );
	}

	/**
	 * @param array $metadata
	 *
	 * @return object
	 */
	protected function create_payment_intent( $metadata = array() ) {
		$customer_id    = $this->get_customer_id();
		$payment_method = $this->create_payment_method();
		$new_charge     = array(
			'customer'       => $customer_id,
			'currency'       => 'usd',
			'amount'         => 1000,
			'payment_method' => $payment_method->id,
			'confirm'        => true,
			'metadata'       => $metadata,
		);
		return FrmStrpLiteConnectHelper::run_new_charge( $new_charge );
	}

	/**
	 * @return string $customer_id
	 */
	protected function get_customer_id() {
		$options = array();
		return FrmStrpLiteConnectHelper::get_customer_id( $options );
	}

	protected function get_new_charge_data( $action_id = 0 ) {
		$new_charge = array(
			'amount'               => 1000,
			'currency'             => 'usd',
			'payment_method_types' => array( 'card' ),
			'confirm'              => true,
			'capture_method'       => 'manual',
			'payment_method'       => $this->create_payment_method()->id,
		);

		if ( $action_id ) {
			$new_charge['metadata'] = array(
				'action' => $action_id,
			);
		}
		return $new_charge;
	}

	/**
	 * @return array
	 */
	protected function get_test_credit_card() {
		return array(
			'number'    => $this->get_test_credit_card_number(),
			'exp_month' => 12,
			'exp_year'  => gmdate( 'Y' ),
			'cvc'       => '314',
		);
	}

	/**
	 * @return string
	 */
	protected function get_test_credit_card_number() {
		return $this->use_test_credit_card_number ?? '4242424242424242';
	}

	/**
	 * Make sure that the frm_payments table gets created.
	 */
	protected function run_migrations() {
		$db = new FrmTransDb();
		$db->upgrade();
	}

	protected function return_url( $entry ) {
		$atts = array(
			'entry' => $entry,
		);
		return $this->run_private_method( array( 'FrmStrpLiteAuth', 'return_url' ), array( $atts ) );
	}

	protected function create_subscription() {
		$this->customer_id = $this->get_customer_id();
		$this->add_card( $this->customer_id );
		$plan       = $this->get_plan_options();
		$plan_id    = FrmStrpLiteConnectHelper::maybe_create_plan( $plan );
		$new_charge = $this->get_subscription_charge_options( $this->customer_id, $plan_id );
		return FrmStrpLiteConnectHelper::create_subscription( $new_charge );
	}

	private function get_subscription_charge_options( $customer_id, $plan_id ) {
		$default_options = array(
			'customer'         => $customer_id,
			'plan'             => $plan_id,
			'payment_behavior' => 'allow_incomplete',
			'expand'           => array( 'latest_invoice.payment_intent' ),
			'off_session'      => true,
		);
		return array_filter(
			array_merge(
				$default_options,
				$this->subscription_charge_options ?? array()
			)
		);
	}

	/**
	 * @return PaymentMethod
	 */
	protected function create_payment_method() {
		$stripe = $this->get_authenticated_stripe_client();

		$card_details = array(
			'type' => 'card',
			'card' => $this->get_test_credit_card(),
		);

		$account_details = $this->get_stripe_account_id_details();

		// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
		return $stripe->paymentMethods->create( $card_details, $account_details );
	}

	/**
	 * @return array
	 */
	protected function prepare_new_charge_array() {
		$customer_id = $this->get_customer_id();
		$this->add_card( $customer_id );
		return array(
			'customer' => $customer_id,
			'currency' => 'usd',
			'amount'   => 1000,
			'capture'  => false,
		);
	}

	protected function assert_run_new_charge( $charge ) {
		$this->assertTrue( is_object( $charge ) );
		$this->assertTrue( ! empty( $charge->id ) );
		$this->assertTrue( isset( $charge->object ) );
		$this->assertEquals( 'charge', $charge->object );
		$this->assertTrue( ! empty( $charge->status ) );
	}

	protected function assert_maybe_create_plan( $plan_id ) {
		$this->assertTrue( is_string( $plan_id ) );
		$this->assertTrue( ! empty( $plan_id ) );
	}

	protected function assert_create_subscription( $subscription ) {
		$this->assertTrue( is_object( $subscription ) );
		$this->assertTrue( ! empty( $subscription->current_period_start ) );
		$this->assertTrue( ! empty( $subscription->current_period_end ) );
		$this->assertTrue( ! empty( $subscription->status ) );
		$this->assertIsString( $subscription->status );
		$this->assertEquals( 'trialing', $subscription->status );
		$this->assertTrue( ! empty( $subscription->latest_invoice ) );
		$this->assertTrue( ! empty( $subscription->latest_invoice->status ) );
		$this->assertIsString( $subscription->latest_invoice->status );
		$this->assertEquals( 'paid', $subscription->latest_invoice->status );
		$this->assertTrue( ! empty( $subscription->latest_invoice->lines ) );
		$this->assertTrue( ! empty( $subscription->latest_invoice->lines->data ) );
		$this->assertIsArray( $subscription->latest_invoice->lines->data );
		$this->assertArrayHasKey( 0, $subscription->latest_invoice->lines->data );
		$this->assertTrue( ! empty( $subscription->latest_invoice->lines->data[0]->period ) );
		$this->assertTrue( ! empty( $subscription->latest_invoice->lines->data[0]->period->end ) );
	}

	protected function assert_get_customer_subscriptions( $subscriptions ) {
		$this->assertTrue( is_object( $subscriptions ) );
		$this->assertTrue( isset( $subscriptions->data ) );
		$this->assertTrue( is_array( $subscriptions->data ) );
		$this->assertEquals( 1, count( $subscriptions->data ) );
	}

	protected function assert_create_intent( $intent ) {
		$this->assertTrue( is_object( $intent ) );
		$this->assertTrue( ! empty( $intent->id ) );
		$this->assertTrue( ! empty( $intent->client_secret ) );
	}

	protected function assert_capture_intent( $captured ) {
		$this->assertTrue( is_object( $captured ) );
		$this->assertEquals( 'succeeded', $captured->status );
		$this->assertTrue( isset( $captured->charges ) );
		$this->assertTrue( is_object( $captured->charges ) );
		$this->assertTrue( is_array( $captured->charges->data ) );

		$charge = reset( $captured->charges->data );
		$this->assertTrue( ! empty( $charge->object ) );
		$this->assertTrue( ! empty( $charge->status ) );
		$this->assertTrue( isset( $charge->paid ) );
		$this->assertTrue( isset( $charge->captured ) );
	}

	protected function assert_get_intent( $payment_intent ) {
		$this->assertIsObject( $payment_intent );
		$this->assertTrue( ! empty( $payment_intent->id ) );
		$this->assertStringStartsWith( 'pi_', $payment_intent->id );
		$this->assertTrue( isset( $payment_intent->metadata ) );
		$this->assertTrue( ! empty( $payment_intent->metadata->key ) );
		$this->assertEquals( 'value', $payment_intent->metadata->key );
		$this->assertTrue( ! empty( $payment_intent->status ) );
		$this->assertTrue( ! empty( $payment_intent->amount ) );
		$this->assertTrue( ! empty( $payment_intent->client_secret ) );
		$this->assertStringStartsWith( 'pi_', $payment_intent->client_secret );
		$this->assertStringContainsString( '_secret_', $payment_intent->client_secret );
		$this->assertTrue( ! empty( $payment_intent->charges ) );
		$this->assertIsObject( $payment_intent->charges );
		$this->assertTrue( ! empty( $payment_intent->charges->data ) );
		$this->assertIsArray( $payment_intent->charges->data );
		$charge = reset( $payment_intent->charges->data );
		$this->assertIsObject( $charge );
		$this->assertTrue( ! empty( $charge->id ) );
		$this->assertStringStartsWith( 'ch_', $charge->id );
	}

	protected function assert_confirm_intent( $confirmed ) {
		$this->assertTrue( is_object( $confirmed ) );
		$this->assertTrue( ! empty( $confirmed->intent_id ) );
		$this->assertTrue( ! empty( $confirmed->next_action ) );
		$this->assertTrue( ! empty( $confirmed->next_action->redirect_to_url ) );
		$this->assertTrue( ! empty( $confirmed->next_action->redirect_to_url->url ) );
	}

	/**
	 * @param object $setup_intent
	 *
	 * @return void
	 */
	protected function assert_setup_intent( $setup_intent ) {
		$this->assertIsObject( $setup_intent );
		$this->assertTrue( ! empty( $setup_intent->id ) );
		$this->assertStringStartsWith( 'seti_', $setup_intent->id );
	}

	/**
	 * @param int $test_mode 1 or 0, defines if we're trying to use Stripe in test or live mode.
	 */
	protected function update_stripe_settings_test_mode_flag( $test_mode ) {
		$options            = new stdClass();
		$options->test_mode = $test_mode;
		$options->process   = 'after';
		update_option( 'frm_strp_options', $options, 'no' );

		$this->set_private_property( 'FrmStrpLiteAppHelper', 'settings', null );
	}

	/**
	 * @return WP_Post
	 */
	protected function get_stripe_action_with_a_plan() {
		$action                                    = $this->get_simple_stripe_action();
		$action->post_content['plan_id']           = $this->maybe_create_plan();
		$action->post_content['interval']          = 'year';
		$action->post_content['interval_count']    = 1;
		$action->post_content['trial_period_days'] = 10;
		return $action;
	}

	/**
	 * @return WP_Post
	 */
	protected function get_simple_stripe_action() {
		$action               = $this->factory->post->create_and_get();
		$action->post_content = array(
			'currency' => 'usd',
		);
		$this->action         = $action;
		return $action;
	}

	/**
	 * @return false|string
	 */
	protected function maybe_create_plan() {
		$this->get_customer();
		$plan = $this->get_plan_options();
		return FrmStrpLiteSubscriptionHelper::maybe_create_plan( $plan );
	}
}
