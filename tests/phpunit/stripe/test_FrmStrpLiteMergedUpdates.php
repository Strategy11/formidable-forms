<?php

/**
 * Verify Stripe payment updates and appearance settings.
 *
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

	/**
	 * Configure the payment tables and mocked Connect requests.
	 */
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

	/**
	 * Remove the Connect mock and restore test state.
	 */
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

	/**
	 * Verify ajax hooks only register connect and payment handlers.
	 */
	public function test_ajax_hooks_only_register_connect_and_payment_handlers() {
		$this->run_private_method( array( 'FrmStrpLiteHooksController', 'load_ajax_hooks' ) );
		self::assertFalse( has_action( 'wp_ajax_frm_strp_event' ) );
		self::assertFalse( has_action( 'wp_ajax_nopriv_frm_strp_event' ) );
		self::assertNotFalse( has_action( 'wp_ajax_frm_strp_process_events' ) );
		self::assertSame( 10, has_action( 'wp_ajax_frm_failed_payment', 'FrmStrpLiteAuth::check_payment_status' ) );
	}

	/**
	 * Verify one intent is created for a single action.
	 */
	public function test_one_intent_is_created_for_a_single_action() {
		$form = $this->factory->form->create_and_get();
		$this->make_action( $form->id );
		self::assertNotEmpty( FrmStrpLiteActionsController::get_actions_before_submit( $form->id ), 'Actions must be visible to intent creation.' );
		self::assertNotEmpty( FrmStrpLiteAppHelper::stripe_is_configured(), 'Connect must be configured for this fixture.' );
		$intents = $this->run_private_method( array( 'FrmStrpLiteAuth', 'maybe_create_intents' ), array( $form->id ) );
		self::assertCount( 1, $intents );
		self::assertCount( 1, $this->requests );
	}

	/**
	 * Verify cleanup skips intents without local payment records.
	 */
	public function test_cleanup_skips_intents_without_local_payment_records() {
		FrmStrpLitePaymentFailureHelper::cleanup_failed_intent( 'pi_unknown_secret_fixture' );
		self::assertSame( array(), $this->requests );
	}

	/**
	 * Verify cleanup requires matching secret and verified failure.
	 */
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
		self::assertSame( 'pending', $model->get_one( $id )->status );
		self::assertNotFalse( FrmEntry::getOne( $entry->id ) );
		FrmStrpLitePaymentFailureHelper::cleanup_failed_intent( $this->intent->client_secret );
		self::assertSame( 'failed', $model->get_one( $id )->status );
		self::assertNull( FrmEntry::getOne( $entry->id ) );
	}

	/**
	 * Verify unowned event is skipped locally without acknowledging connect.
	 */
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
		wp_cache_set( FrmStrpLiteAppHelper::active_mode() . '_' . $event->id, $event, 'frm_strp' );
		$controller = new FrmStrpLiteEventsController();
		$this->run_private_method( array( $controller, 'process_event_ids' ), array( array( $event->id ) ) );
		self::assertSame( array(), $this->requests );
		self::assertContains( $event->id, get_option( FrmStrpLiteEventsController::$events_to_skip_option_name ) );
	}

	/**
	 * Verify empty style context has all appearance keys.
	 */
	public function test_empty_style_context_has_all_appearance_keys() {
		$settings = $this->run_private_method( array( 'FrmStrpLiteActionsController', 'get_style_settings_for_form' ), array( 0 ) );
		$rules    = $this->run_private_method( array( 'FrmStrpLiteActionsController', 'get_appearance_rules' ), array( $settings ) );
		self::assertArrayHasKey( '.Input', $rules );
		self::assertArrayNotHasKey( 'fontFamily', $rules['.Input'] );
		self::assertArrayNotHasKey( 'fontFamily', $rules['.Label'] );
		self::assertSame( '', $rules['.Input:focus']['borderColor'] );
	}

	/**
	 * Verify focus border color matches the style.
	 */
	public function test_focus_border_color_matches_the_style() {
		$settings                        = $this->run_private_method( array( 'FrmStrpLiteActionsController', 'get_style_settings_for_form' ), array( 0 ) );
		$settings['border_color_active'] = '#ff8000';
		$rules                           = $this->run_private_method( array( 'FrmStrpLiteActionsController', 'get_appearance_rules' ), array( $settings ) );
		self::assertSame( '#ff8000', $rules['.Input:focus']['borderColor'] );
	}

	/**
	 * Verify successful webhook updates the description without a return visit.
	 */
	public function test_successful_webhook_updates_the_description_without_a_return_visit() {
		$form                    = $this->factory->form->create_and_get();
		$action                  = $this->make_action( $form->id );
		$settings                = (array) $action->post_content;
		$settings['description'] = 'Order [id]';
		wp_update_post(
			array(
				'ID'           => $action->ID,
				'post_content' => wp_json_encode( $settings ),
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
		wp_cache_set( FrmStrpLiteAppHelper::active_mode() . '_' . $event->id, $event, 'frm_strp' );
		$controller = new FrmStrpLiteEventsController();
		$this->run_private_method( array( $controller, 'process_event_ids' ), array( array( $event->id ) ) );
		self::assertSame( 'complete', $model->get_one( $id )->status );
		$requests_by_action = array_column( $this->requests, null, 'frm_strp_connect_action' );
		self::assertArrayHasKey( 'process_event', $requests_by_action );
		self::assertSame( 'Order ' . $entry->id, $requests_by_action['update_intent']['data']['description'] );
	}

	/**
	 * Verify webhook description update uses the event mode.
	 */
	public function test_webhook_description_update_uses_the_event_mode() {
		$form                    = $this->factory->form->create_and_get();
		$action                  = $this->make_action( $form->id );
		$settings                = (array) $action->post_content;
		$settings['description'] = 'Order [id]';
		wp_update_post(
			array(
				'ID'           => $action->ID,
				'post_content' => wp_json_encode( $settings ),
			)
		);
		FrmFormAction::clear_cache();
		$entry = $this->factory->entry->create_and_get( $this->factory->field->generate_entry_array( $form ) );
		( new FrmTransLitePayment() )->create(
			array(
				'item_id'    => $entry->id,
				'action_id'  => $action->ID,
				'receipt_id' => 'pi_fixture',
				'status'     => 'pending',
				'paysys'     => 'stripe',
				'test'       => 1,
			)
		);
		$event = (object) array(
			'id'   => 'evt_test_mode_fixture',
			'type' => 'payment_intent.succeeded',
			'data' => (object) array(
				'object' => (object) array(
					'id'     => 'pi_fixture',
					'object' => 'payment_intent',
				),
			),
		);
		wp_cache_set( 'test_' . $event->id, $event, 'frm_strp' );

		// The override needs both modes connected.
		foreach ( array( 'account_id', 'client_password', 'server_password', 'details_submitted' ) as $key ) {
			update_option( 'frm_strp_connect_' . $key . '_live', 'live_fixture' );
		}

		// The site is live, so a description update that falls back to the active mode would use the wrong credentials.
		$force_live = function () {
			return 'live';
		};
		add_filter( 'frm_strp_active_mode', $force_live );
		$controller = new FrmStrpLiteEventsController();
		$this->run_private_method( array( $controller, 'process_event_ids' ), array( array( $event->id ), 'test' ) );
		remove_filter( 'frm_strp_active_mode', $force_live );

		$requests_by_action = array_column( $this->requests, null, 'frm_strp_connect_action' );
		self::assertArrayHasKey( 'update_intent', $requests_by_action );
		self::assertSame( 'test', $requests_by_action['update_intent']['frm_strp_connect_mode'] );
	}

	/**
	 * Verify return url uses the mode the payment was made in.
	 */
	public function test_return_url_uses_the_mode_the_payment_was_made_in() {
		$force_live = function () {
			return 'live';
		};
		add_filter( 'frm_strp_active_mode', $force_live );

		$this->run_private_method( array( 'FrmStrpLiteLinkController', 'use_payment_mode' ), array( (object) array( 'test' => '1' ) ) );
		self::assertSame( 'test', FrmStrpLiteAppHelper::active_mode(), 'A test payment should be returned to with test credentials on a live site.' );

		$this->run_private_method( array( 'FrmStrpLiteLinkController', 'use_payment_mode' ), array( (object) array( 'test' => '0' ) ) );
		self::assertSame( 'live', FrmStrpLiteAppHelper::active_mode(), 'A live payment should keep the live mode.' );

		remove_all_filters( 'frm_strp_active_mode' );
	}

	/**
	 * Verify missing plan and price errors are recognized.
	 */
	public function test_missing_plan_and_price_errors_are_recognized() {
		foreach ( array( 'FrmStrpLiteConnectHelper', 'FrmStrpLiteSubscriptionHelper' ) as $class ) {
			self::assertTrue( $this->run_private_method( array( $class, 'is_missing_plan_error' ), array( "No such price: 'missing'" ) ) );
			self::assertTrue( $this->run_private_method( array( $class, 'is_missing_plan_error' ), array( "No such plan: 'missing'" ) ) );
			self::assertFalse( $this->run_private_method( array( $class, 'is_missing_plan_error' ), array( 'Card declined' ) ) );
		}
	}

	/**
	 * Verify formatted data preserves escaped values and rejects malformed inputs.
	 */
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
		self::assertSame( $value, $formatted['item_meta'][25] );

		foreach ( array( 'invalid json', 'null', '[{}]', '{"form_id":1}' ) as $invalid ) {
			$_POST['form'] = $invalid;
			self::assertSame( array(), $this->run_private_method( array( 'FrmStrpLiteAuth', 'get_formatted_form_data' ) ) );
		}
	}

	/**
	 * Verify static amount updates skip connect.
	 */
	public function test_static_amount_updates_skip_connect() {
		$form = $this->factory->form->create_and_get();
		$this->make_action( $form->id );
		$intents = array( 'pi_fixture_secret_fixture' );
		$args    = array( $form->id, &$intents, array( 'form_id' => $form->id ) );
		$this->run_private_method( array( 'FrmStrpLiteAuth', 'update_intent_pricing' ), $args );
		self::assertSame( array(), $this->requests );
		self::assertSame( array( false ), array_column( $intents, 'changed' ) );
	}

	/**
	 * @param mixed $value The value being filtered through frm_content.
	 *
	 * @return mixed
	 */
	public function replace_price_shortcode( $value ) {
		return '[price]' === $value ? $this->price : $value;
	}

	/**
	 * Verify dynamic pricing only updates changed verified intents.
	 */
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
			self::assertSame( array( 'get_intent' ), array_column( $this->requests, 'frm_strp_connect_action' ) );
			self::assertSame( array( false ), array_column( $intents, 'changed' ) );

			$this->price    = '25';
			$this->requests = array();
			$intents        = array( $this->intent->client_secret );
			$this->run_private_method( array( 'FrmStrpLiteAuth', 'update_intent_pricing' ), $args );
			self::assertSame( array( 'get_intent', 'update_intent' ), array_column( $this->requests, 'frm_strp_connect_action' ) );
			self::assertSame( array( true ), array_column( $intents, 'changed' ) );
		} finally {
			remove_filter( 'frm_content', array( $this, 'replace_price_shortcode' ) );
		}
	}

	/**
	 * Verify setup intent retry reuses the verified object.
	 */
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
			self::assertSame( $intent->id, $this->run_private_method( array( 'FrmStrpLiteLinkController', 'verify_intent' ), $args ) );
			self::assertSame( 'failed', $model->get_one( $id )->status );
			self::assertSame( array(), $this->requests );

			$intent->status = 'succeeded';
			self::assertFalse( $this->run_private_method( array( 'FrmStrpLiteLinkController', 'verify_intent' ), $args ) );
		} finally {
			unset( $_POST[ 'frmintent' . $form->id ] );
		}
	}

	/**
	 * Verify cleanup marks every row on a verified failed intent.
	 */
	public function test_cleanup_marks_every_row_on_a_verified_failed_intent() {
		$form         = $this->factory->form->create_and_get();
		$model        = new FrmTransLitePayment();
		$entries      = array();
		$ids          = array();
		$this->intent = (object) array(
			'id'                 => 'pi_fixture',
			'client_secret'      => 'pi_fixture_secret_' . wp_generate_password( 24, false ),
			'status'             => 'requires_payment_method',
			'last_payment_error' => (object) array( 'message' => 'Declined' ),
		);

		foreach ( array( 'pending', 'failed' ) as $status ) {
			$entry     = $this->factory->entry->create_and_get( $this->factory->field->generate_entry_array( $form ) );
			$entries[] = $entry->id;
			$ids[]     = $model->create(
				array(
					'item_id'    => $entry->id,
					'receipt_id' => $this->intent->id,
					'status'     => $status,
					'paysys'     => 'stripe',
				)
			);
		}

		FrmStrpLitePaymentFailureHelper::cleanup_failed_intent( $this->intent->client_secret );

		foreach ( $ids as $index => $id ) {
			self::assertSame( 'failed', $model->get_one( $id )->status );
			self::assertNull( FrmEntry::getOne( $entries[ $index ] ) );
		}
	}

	/**
	 * Verify cleanup leaves rows alone when stripe reports no failure.
	 */
	public function test_cleanup_leaves_rows_alone_when_stripe_reports_no_failure() {
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
			'id'            => 'pi_fixture',
			'client_secret' => 'pi_fixture_secret_' . wp_generate_password( 24, false ),
			'status'        => 'processing',
		);

		FrmStrpLitePaymentFailureHelper::cleanup_failed_intent( $this->intent->client_secret );
		self::assertSame( 'pending', $model->get_one( $id )->status );
		self::assertNotNull( FrmEntry::getOne( $entry->id ) );
	}

	/**
	 * Verify verify intent rejects payment intents that already moved money.
	 */
	public function test_verify_intent_rejects_payment_intents_that_already_moved_money() {
		$form                             = $this->factory->form->create_and_get();
		$action                           = $this->make_action( $form->id );
		$secret                           = 'pi_fixture_secret_' . wp_generate_password( 24, false );
		$_POST[ 'frmintent' . $form->id ] = array( $secret );

		try {
			foreach ( array( 'succeeded', 'requires_capture', 'processing' ) as $status ) {
				$this->intent = (object) array(
					'id'            => 'pi_fixture',
					'client_secret' => $secret,
					'status'        => $status,
				);
				$verified     = $this->run_private_method( array( 'FrmStrpLiteLinkController', 'verify_intent' ), array( $form->id, $action ) );
				self::assertFalse( $verified, $status . ' must not be reused.' );
			}

			$this->intent->status = 'requires_payment_method';
			self::assertSame( 'pi_fixture', $this->run_private_method( array( 'FrmStrpLiteLinkController', 'verify_intent' ), array( $form->id, $action ) ) );
		} finally {
			unset( $_POST[ 'frmintent' . $form->id ] );
		}
	}

	/**
	 * Verify verify intent rejects an intent with a completed payment.
	 */
	public function test_verify_intent_rejects_an_intent_with_a_completed_payment() {
		$form         = $this->factory->form->create_and_get();
		$action       = $this->make_action( $form->id );
		$this->intent = (object) array(
			'id'            => 'pi_fixture',
			'client_secret' => 'pi_fixture_secret_' . wp_generate_password( 24, false ),
			'status'        => 'requires_payment_method',
		);
		$model        = new FrmTransLitePayment();
		$id           = $model->create(
			array(
				'receipt_id' => $this->intent->id,
				'status'     => 'complete',
				'paysys'     => 'stripe',
			)
		);

		$_POST[ 'frmintent' . $form->id ] = array( $this->intent->client_secret );

		try {
			self::assertFalse( $this->run_private_method( array( 'FrmStrpLiteLinkController', 'verify_intent' ), array( $form->id, $action ) ) );
			self::assertSame( 'complete', $model->get_one( $id )->status );
		} finally {
			unset( $_POST[ 'frmintent' . $form->id ] );
		}
	}

	/**
	 * Verify action for payment falls back to the form link action.
	 */
	public function test_action_for_payment_falls_back_to_the_form_link_action() {
		$form   = $this->factory->form->create_and_get();
		$action = $this->make_action( $form->id );
		$entry  = (object) array( 'form_id' => $form->id );
		$found  = $this->run_private_method( array( 'FrmStrpLiteLinkController', 'get_action_for_payment' ), array( (object) array( 'action_id' => $action->ID ), $entry ) );
		self::assertSame( $action->ID, $found->ID );

		$found = $this->run_private_method( array( 'FrmStrpLiteLinkController', 'get_action_for_payment' ), array( (object) array( 'action_id' => 0 ), $entry ) );
		self::assertSame( $action->ID, $found->ID, 'A payment without an action id should use the form Stripe action.' );
	}

	/**
	 * @param string $sub_id
	 *
	 * @return string The cached event id.
	 */
	private function cache_subscription_updated_event( $sub_id ) {
		$event = (object) array(
			'id'   => 'evt_sub_' . $sub_id,
			'type' => 'customer.subscription.updated',
			'data' => (object) array(
				'object' => (object) array(
					'id'                   => $sub_id,
					'object'               => 'subscription',
					'cancel_at_period_end' => true,
				),
			),
		);
		wp_cache_set( FrmStrpLiteAppHelper::active_mode() . '_' . $event->id, $event, 'frm_strp' );
		return $event->id;
	}

	/**
	 * Verify subscription update for an unknown subscription is not owned.
	 */
	public function test_subscription_update_for_an_unknown_subscription_is_not_owned() {
		$event_id   = $this->cache_subscription_updated_event( 'sub_unknown' );
		$controller = new FrmStrpLiteEventsController();
		$this->run_private_method( array( $controller, 'process_event_ids' ), array( array( $event_id ) ) );
		self::assertSame( array(), $this->requests );
		self::assertContains( $event_id, get_option( FrmStrpLiteEventsController::$events_to_skip_option_name ) );
	}

	/**
	 * Verify subscription update marks a known subscription for future cancel.
	 */
	public function test_subscription_update_marks_a_known_subscription_for_future_cancel() {
		$frm_sub = new FrmTransLiteSubscription();
		$sub_id  = $frm_sub->create(
			array(
				'sub_id' => 'sub_fixture',
				'status' => 'active',
				'paysys' => 'stripe',
			)
		);

		$event_id   = $this->cache_subscription_updated_event( 'sub_fixture' );
		$controller = new FrmStrpLiteEventsController();
		$this->run_private_method( array( $controller, 'process_event_ids' ), array( array( $event_id ) ) );
		self::assertSame( 'future_cancel', $frm_sub->get_one( $sub_id )->status );
		self::assertSame( array( 'process_event' ), array_column( $this->requests, 'frm_strp_connect_action' ) );
	}

	/**
	 * Verify intent waits for the payment field page when pro is available.
	 */
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

		if ( ! class_exists( 'FrmProFieldsHelper' ) ) {
			self::assertTrue( FrmStrpLiteFormIntentHelper::requires_intent_on_load( $action ) );
			return;
		}

		global $frm_vars;
		$original_vars = $frm_vars;

		try {
			$frm_vars['prev_page'] = array( $form->id => 0 );
			$frm_vars['next_page'] = array( $form->id => 2 );
			self::assertFalse( FrmStrpLiteFormIntentHelper::requires_intent_on_load( $action ) );
			$frm_vars['next_page'][ $form->id ] = 999999;
			self::assertTrue( FrmStrpLiteFormIntentHelper::requires_intent_on_load( $action ) );
		} finally {
			$frm_vars = $original_vars;
		}
	}
}
