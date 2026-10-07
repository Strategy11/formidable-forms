<?php

/**
 * @group stripe
 */
#[\PHPUnit\Framework\Attributes\Group( 'stripe' )]
class test_FrmStrpLiteMergedUpdates extends FrmUnitTest {

	/**
	 * @var array The Connect requests made by the test.
	 */
	private $requests = array();

	/**
	 * @var object|null The mocked intent returned by Connect.
	 */
	private $intent;

	/**
	 * @var string The price returned when the test resolves an amount shortcode.
	 */
	private $price = '10';

	public function setUp(): void {
		parent::setUp();
		$db = new FrmTransLiteDb();
		$db->upgrade();
		FrmStrpLiteAppHelper::get_settings()->settings->test_mode = 1;

		foreach ( array( 'account_id', 'client_password', 'server_password', 'details_submitted' ) as $key ) {
			update_option( 'frm_strp_connect_' . $key . '_test', 'test_fixture' );
		}
		$this->set_private_property( 'FrmStrpLiteFormIntentHelper', 'payment_fields_cache', array() );
		add_filter( 'pre_http_request', array( $this, 'mock_connect' ), 10, 2 );
	}

	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'mock_connect' ), 10 );
		unset( $_POST['form'] );
		parent::tearDown();
	}

	/**
	 * @param mixed $response The response supplied by another filter.
	 * @param array $args The request options.
	 *
	 * @return array
	 */
	public function mock_connect( $response, $args ) {
		$body             = $args['body'];
		$this->requests[] = $body;
		$action           = $body['frm_strp_connect_action'];
		$data             = $this->intent;

		if ( 'create_intent' === $action || 'create_setup_intent' === $action ) {
			$id   = 'create_intent' === $action ? 'pi_fixture' : 'seti_fixture';
			$data = (object) array(
				'id'            => $id,
				'client_secret' => $id . '_secret_fixture',
			);
		} elseif ( 'get_customer' === $action ) {
			$data = (object) array( 'customer_id' => 'cus_fixture' );
		} elseif ( 'update_intent' === $action || 'process_event' === $action ) {
			$data = (object) array();
		}

		$http_response       = new \WpOrg\Requests\Response();
		$http_response->body = wp_json_encode(
			array(
				'success' => true,
				'data'    => $data,
			)
		);

		return array(
			'http_response' => new WP_HTTP_Requests_Response( $http_response, '' ),
			'headers'       => array(),
			'body'          => wp_json_encode(
				array(
					'success' => true,
					'data'    => $data,
				)
			),
			'response'      => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'       => array(),
		);
	}

	/**
	 * @param int $form_id The form receiving the action.
	 * @param string $type The payment frequency.
	 * @param string $amount
	 *
	 * @return WP_Post
	 */
	private function make_action( $form_id, $type = 'single', $amount = '10' ) {
		$action = $this->factory->post->create_and_get(
			array(
				'post_type'    => FrmFormActionsController::$action_post_type,
				'post_excerpt' => 'payment',
				'post_status'  => 'publish',
				'menu_order'   => $form_id,
				'post_content' => wp_json_encode(
					array(
						'gateway'  => array( 'stripe' ),
						'amount'   => $amount,
						'currency' => 'usd',
						'type'     => $type,
					)
				),
			)
		);
		FrmFormAction::clear_cache();
		return FrmFormAction::get_single_action_type( $action->ID, 'payment' );
	}

	public function test_ajax_hooks_only_register_connect_and_payment_handlers() {
		$this->run_private_method( array( 'FrmStrpLiteHooksController', 'load_ajax_hooks' ) );
		$this->assertFalse( has_action( 'wp_ajax_frm_strp_event' ) );
		$this->assertFalse( has_action( 'wp_ajax_nopriv_frm_strp_event' ) );
		$this->assertNotFalse( has_action( 'wp_ajax_frm_strp_process_events' ) );
		$this->assertSame( 10, has_action( 'wp_ajax_frm_failed_payment', 'FrmStrpLiteAuth::check_payment_status' ) );
	}

	public function test_one_intent_is_created_for_a_single_action() {
		$form = $this->factory->form->create_and_get();
		$this->make_action( $form->id );
		$this->assertNotEmpty( FrmStrpLiteActionsController::get_actions_before_submit( $form->id ), 'Actions must be visible to intent creation.' );
		$this->assertTrue( (bool) FrmStrpLiteAppHelper::stripe_is_configured(), 'Connect must be configured for this fixture.' );
		$intents = $this->run_private_method( array( 'FrmStrpLiteAuth', 'maybe_create_intents' ), array( $form->id ) );
		$this->assertCount( 1, $intents );
		$this->assertCount( 1, $this->requests );
	}

	public function test_cleanup_skips_intents_without_local_payment_records() {
		FrmStrpLitePaymentFailureHelper::cleanup_failed_intent( 'pi_unknown_secret_fixture' );
		$this->assertSame( array(), $this->requests );
	}

	public function test_cleanup_requires_matching_secret_and_verified_failure() {
		$form         = $this->factory->form->create_and_get();
		$entry        = $this->factory->entry->create_and_get( $this->factory->field->generate_entry_array( $form ) );
		$model        = new FrmTransLitePayment();
		$id           = $model->create(
			array(
				'item_id'    => $entry->id,
				'receipt_id' => 'pi_fixture',
				'status'     => 'pending',
				'paysys'     => 'stripe',
			)
		);
		$this->intent = (object) array(
			'id'                 => 'pi_fixture',
			'client_secret'      => 'pi_fixture_secret_' . wp_generate_password( 24, false ),
			'status'             => 'requires_payment_method',
			'last_payment_error' => (object) array( 'message' => 'Declined' ),
		);
		FrmStrpLitePaymentFailureHelper::cleanup_failed_intent( 'pi_fixture_secret_wrong' );
		$this->assertSame( 'pending', $model->get_one( $id )->status );
		$this->assertNotFalse( FrmEntry::getOne( $entry->id ) );
		FrmStrpLitePaymentFailureHelper::cleanup_failed_intent( $this->intent->client_secret );
		$this->assertSame( 'failed', $model->get_one( $id )->status );
		$this->assertNull( FrmEntry::getOne( $entry->id ) );
	}

	public function test_unowned_event_is_skipped_locally_without_acknowledging_connect() {
		$event = (object) array(
			'id'   => 'evt_unknown',
			'type' => 'charge.refunded',
			'data' => (object) array(
				'object' => (object) array(
					'id'     => 'ch_unknown',
					'object' => 'charge',
				),
			),
		);
		wp_cache_set( $event->id, $event, 'frm_strp' );
		$controller = new FrmStrpLiteEventsController();
		$this->run_private_method( array( $controller, 'process_event_ids' ), array( array( $event->id ) ) );
		$this->assertSame( array(), $this->requests );
		$this->assertContains( $event->id, get_option( FrmStrpLiteEventsController::$events_to_skip_option_name ) );
	}

	public function test_empty_style_context_has_all_appearance_keys() {
		$settings = $this->run_private_method( array( 'FrmStrpLiteActionsController', 'get_style_settings_for_form' ), array( 0 ) );
		$rules    = $this->run_private_method( array( 'FrmStrpLiteActionsController', 'get_appearance_rules' ), array( $settings ) );
		$this->assertArrayHasKey( '.Input', $rules );
		$this->assertArrayNotHasKey( 'fontFamily', $rules['.Input'] );
		$this->assertArrayNotHasKey( 'fontFamily', $rules['.Label'] );
	}

	public function test_successful_webhook_updates_the_description_without_a_return_visit() {
		$form                                = $this->factory->form->create_and_get();
		$action                              = $this->make_action( $form->id );
		$action->post_content['description'] = 'Order [id]';
		wp_update_post(
			array(
				'ID'           => $action->ID,
				'post_content' => wp_json_encode( $action->post_content ),
			)
		);
		FrmFormAction::clear_cache();
		$entry = $this->factory->entry->create_and_get( $this->factory->field->generate_entry_array( $form ) );
		$model = new FrmTransLitePayment();
		$id    = $model->create(
			array(
				'item_id'    => $entry->id,
				'action_id'  => $action->ID,
				'receipt_id' => 'pi_fixture',
				'status'     => 'pending',
				'paysys'     => 'stripe',
			)
		);
		$event = (object) array(
			'id'   => 'evt_paid_fixture',
			'type' => 'payment_intent.succeeded',
			'data' => (object) array(
				'object' => (object) array(
					'id'     => 'pi_fixture',
					'object' => 'payment_intent',
				),
			),
		);
		wp_cache_set( $event->id, $event, 'frm_strp' );
		$controller = new FrmStrpLiteEventsController();
		$this->run_private_method( array( $controller, 'process_event_ids' ), array( array( $event->id ) ) );
		$this->assertSame( 'complete', $model->get_one( $id )->status );
		$requests_by_action = array_column( $this->requests, null, 'frm_strp_connect_action' );
		$this->assertArrayHasKey( 'process_event', $requests_by_action );
		$this->assertSame( 'Order ' . $entry->id, $requests_by_action['update_intent']['data']['description'] );
	}

	public function test_missing_plan_and_price_errors_are_recognized() {
		foreach ( array( 'FrmStrpLiteConnectHelper', 'FrmStrpLiteSubscriptionHelper' ) as $class ) {
			$this->assertTrue( $this->run_private_method( array( $class, 'is_missing_plan_error' ), array( "No such price: 'missing'" ) ) );
			$this->assertTrue( $this->run_private_method( array( $class, 'is_missing_plan_error' ), array( "No such plan: 'missing'" ) ) );
			$this->assertFalse( $this->run_private_method( array( $class, 'is_missing_plan_error' ), array( 'Card declined' ) ) );
		}
	}

	public function test_formatted_data_preserves_escaped_values_and_rejects_malformed_inputs() {
		$value         = 'A "quoted" value with a \\slash';
		$_POST['form'] = wp_slash(
			wp_json_encode(
				array(
					array(
						'name'  => 'item_meta[25]',
						'value' => $value,
					),
				)
			)
		);
		$formatted     = $this->run_private_method( array( 'FrmStrpLiteAuth', 'get_formatted_form_data' ) );
		$this->assertSame( $value, $formatted['item_meta'][25] );

		foreach ( array( 'invalid json', 'null', '[{}]', '{"form_id":1}' ) as $invalid ) {
			$_POST['form'] = $invalid;
			$this->assertSame( array(), $this->run_private_method( array( 'FrmStrpLiteAuth', 'get_formatted_form_data' ) ) );
		}
	}

	public function test_static_amount_updates_skip_connect() {
		$form = $this->factory->form->create_and_get();
		$this->make_action( $form->id );
		$intents = array( 'pi_fixture_secret_fixture' );
		$args    = array( $form->id, &$intents, array( 'form_id' => $form->id ) );
		$this->run_private_method( array( 'FrmStrpLiteAuth', 'update_intent_pricing' ), $args );
		$this->assertSame( array(), $this->requests );
		$this->assertFalse( $intents[0]['changed'] );
	}

	/**
	 * @param mixed $value The value being filtered through frm_content.
	 *
	 * @return mixed
	 */
	public function replace_price_shortcode( $value ) {
		return '[price]' === $value ? $this->price : $value;
	}

	public function test_dynamic_pricing_only_updates_changed_verified_intents() {
		$form         = $this->factory->form->create_and_get();
		$action       = $this->make_action( $form->id, 'single', '[price]' );
		$this->intent = (object) array(
			'id'            => 'pi_fixture',
			'client_secret' => 'pi_fixture_secret_' . wp_generate_password( 24, false ),
			'amount'        => 1000,
			'metadata'      => (object) array( 'action' => $action->ID ),
		);
		add_filter( 'frm_content', array( $this, 'replace_price_shortcode' ) );

		try {
			$intents = array( $this->intent->client_secret );
			$args    = array( $form->id, &$intents, array( 'form_id' => $form->id ) );
			$this->run_private_method( array( 'FrmStrpLiteAuth', 'update_intent_pricing' ), $args );
			$this->assertSame( array( 'get_intent' ), array_column( $this->requests, 'frm_strp_connect_action' ) );
			$this->assertFalse( $intents[0]['changed'] );

			$this->price    = '25';
			$this->requests = array();
			$intents        = array( $this->intent->client_secret );
			$this->run_private_method( array( 'FrmStrpLiteAuth', 'update_intent_pricing' ), $args );
			$this->assertSame( array( 'get_intent', 'update_intent' ), array_column( $this->requests, 'frm_strp_connect_action' ) );
			$this->assertTrue( $intents[0]['changed'] );
		} finally {
			remove_filter( 'frm_content', array( $this, 'replace_price_shortcode' ) );
		}
	}

	public function test_setup_intent_retry_reuses_the_verified_object() {
		$form                             = $this->factory->form->create_and_get();
		$action                           = $this->make_action( $form->id, 'recurring' );
		$intent                           = (object) array(
			'id'            => 'seti_fixture',
			'client_secret' => 'seti_fixture_secret_' . wp_generate_password( 24, false ),
			'status'        => 'requires_payment_method',
		);
		$_POST[ 'frmintent' . $form->id ] = array( $intent->client_secret );
		$model                            = new FrmTransLitePayment();
		$id                               = $model->create(
			array(
				'receipt_id' => $intent->id,
				'status'     => 'pending',
				'paysys'     => 'stripe',
			)
		);

		try {
			$args = array( $form->id, $action, $intent );
			$this->assertSame( $intent->id, $this->run_private_method( array( 'FrmStrpLiteLinkController', 'verify_intent' ), $args ) );
			$this->assertSame( 'failed', $model->get_one( $id )->status );
			$this->assertSame( array(), $this->requests );

			$intent->status = 'succeeded';
			$this->assertFalse( $this->run_private_method( array( 'FrmStrpLiteLinkController', 'verify_intent' ), $args ) );
		} finally {
			unset( $_POST[ 'frmintent' . $form->id ] );
		}
	}

	public function test_intent_waits_for_the_payment_field_page_when_pro_is_available() {
		$form   = $this->factory->form->create_and_get();
		$action = $this->make_action( $form->id );
		$this->factory->field->create(
			array(
				'form_id'     => $form->id,
				'type'        => 'credit_card',
				'field_order' => 3,
			)
		);

		if ( ! is_callable( 'FrmProFieldsHelper::field_on_current_page' ) ) {
			$this->assertTrue( FrmStrpLiteFormIntentHelper::requires_intent_on_load( $action ) );
			return;
		}

		global $frm_vars;
		$original_vars = $frm_vars;

		try {
			$frm_vars['prev_page'] = array( $form->id => 0 );
			$frm_vars['next_page'] = array( $form->id => 2 );
			$this->assertFalse( FrmStrpLiteFormIntentHelper::requires_intent_on_load( $action ) );
			$frm_vars['next_page'][ $form->id ] = 999999;
			$this->assertTrue( FrmStrpLiteFormIntentHelper::requires_intent_on_load( $action ) );
		} finally {
			$frm_vars = $original_vars;
		}
	}
}
