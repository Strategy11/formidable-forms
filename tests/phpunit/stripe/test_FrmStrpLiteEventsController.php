<?php

/**
 * @group stripe
 *
 * @covers FrmStrpLiteEventsController
 */
#[\PHPUnit\Framework\Attributes\Group( 'stripe' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmStrpLiteEventsController::class )]
class test_FrmStrpLiteEventsController extends FrmUnitTest {

	public function tearDown(): void {
		remove_all_filters( 'frm_strp_active_mode' );
		remove_all_filters( 'pre_http_request' );
		parent::tearDown();
	}

	public function test_reset_customer() {
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		update_user_meta( $user_id, '_frmstrp_customer_id_test', 'cus_abc123' );
		update_user_meta( $user_id, 'unrelated_meta', 'cus_abc123' );

		$controller = new FrmStrpLiteEventsController();
		$this->set_private_property( $controller, 'invoice', (object) array( 'id' => 'cus_abc123' ) );
		$this->run_private_method( array( $controller, 'reset_customer' ), array() );

		// The customer meta is deleted with a direct query, so drop the cached values.
		clean_user_cache( $user_id );

		$this->assertSame( '', get_user_meta( $user_id, '_frmstrp_customer_id_test', true ) );
		$this->assertSame( 'cus_abc123', get_user_meta( $user_id, 'unrelated_meta', true ) );
	}

	/**
	 * @covers FrmStrpLiteConnectHelper::get_event
	 */
	public function test_get_event_cache_is_separate_per_mode() {
		$event_id             = 'evt_shared_id';
		$live_event           = $this->create_event( $event_id );
		$live_event->livemode = true;
		$test_event           = $this->create_event( $event_id );
		$test_event->livemode = false;

		wp_cache_set( 'live_' . $event_id, $live_event, 'frm_strp' );
		wp_cache_set( 'test_' . $event_id, $test_event, 'frm_strp' );

		$this->assertTrue( FrmStrpLiteConnectHelper::get_event( $event_id, 'live' )->livemode );
		$this->assertFalse( FrmStrpLiteConnectHelper::get_event( $event_id, 'test' )->livemode );
	}

	/**
	 * Events polled from one mode must be fetched and reported as processed with that mode's credentials.
	 *
	 * @covers FrmStrpLiteEventsController::process_event_ids
	 */
	public function test_process_event_ids_uses_polled_mode() {
		// The site is live, so anything that falls back to the active mode would use the wrong credentials.
		add_filter(
			'frm_strp_active_mode',
			function () {
				return 'live';
			}
		);

		$event_id = 'evt_polled_from_test';
		wp_cache_set( 'test_' . $event_id, $this->create_event( $event_id ), 'frm_strp' );
		$this->set_connect_account( 'test' );

		$requests   = $this->record_connect_requests();
		$controller = new FrmStrpLiteEventsController();
		$this->run_private_method( array( $controller, 'process_event_ids' ), array( array( $event_id ), 'test' ) );

		$this->assertSame( array( 'process_event' => 'test' ), $requests->getArrayCopy() );
		$this->assertContains( $event_id, get_option( FrmStrpLiteEventsController::$events_to_skip_option_name ) );
	}

	/**
	 * @covers FrmStrpLiteConnectApiAdapter::cancel_subscription_without_customer_check
	 */
	public function test_cancel_subscription_without_customer_check_uses_mode() {
		if ( ! class_exists( 'FrmStrpLiteConnectApiAdapter' ) ) {
			require FrmAppHelper::plugin_path() . '/stripe/helpers/FrmStrpLiteConnectApiAdapter.php';
		}

		// The site is live, so anything that falls back to the active mode would use the wrong credentials.
		add_filter(
			'frm_strp_active_mode',
			function () {
				return 'live';
			}
		);

		$this->set_connect_account( 'test' );
		$this->set_connect_account( 'live' );

		$requests = $this->record_connect_requests();

		$this->assertTrue( FrmStrpLiteConnectApiAdapter::cancel_subscription_without_customer_check( 'sub_test', 'test' ) );
		$this->assertSame( array( 'cancel_subscription' => 'test' ), $requests->getArrayCopy() );
	}

	/**
	 * @param string $event_id
	 *
	 * @return stdClass
	 */
	private function create_event( $event_id ) {
		$event               = new stdClass();
		$event->object       = 'event';
		$event->id           = $event_id;
		$event->type         = ''; // Leave this blank so no payment is updated.
		$event->data         = new stdClass();
		$event->data->object = (object) array( 'object' => 'invoice' );

		return $event;
	}

	/**
	 * Store an account id so requests in this mode are sent to the connect server.
	 *
	 * @param string $mode
	 *
	 * @return void
	 */
	private function set_connect_account( $mode ) {
		$option_name = $this->run_private_method( array( 'FrmStrpLiteConnectHelper', 'get_account_id_option_name' ), array( $mode ) );
		update_option( $option_name, 'acct_' . $mode, false );
	}

	/**
	 * Intercept connect server requests and record the mode each action was sent with.
	 *
	 * @return ArrayObject<string, string>
	 */
	private function record_connect_requests() {
		$requests = new ArrayObject();

		add_filter(
			'pre_http_request',
			function ( $preempt, $args ) use ( $requests ) {
				if ( ! isset( $args['body']['frm_strp_connect_action'] ) ) {
					return $preempt;
				}

				$requests[ $args['body']['frm_strp_connect_action'] ] = $args['body']['frm_strp_connect_mode'];
				$response              = new WpOrg\Requests\Response();
				$response->status_code = 200;
				$response->body        = wp_json_encode(
					array(
						'success' => true,
						'data'    => array( 'ok' => 1 ),
					)
				);

				return array(
					'http_response' => new WP_HTTP_Requests_Response( $response ),
				);
			},
			10,
			2
		);

		return $requests;
	}
}
