<?php

/**
 * Tests for the MCP usage tracking reported with the weekly snapshot.
 */
class test_FrmMcpUsageController extends FrmUnitTest {

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
		delete_option( FrmUsageController::FLOWS_ACTION_NAME );
		FrmAppHelper::get_settings()->update_setting( 'tracking', 1, 'absint' );
	}

	/**
	 * @return void
	 */
	public function tearDown(): void {
		FrmAppHelper::get_settings()->update_setting( 'tracking', 0, 'absint' );
		remove_action( 'frm_mcp_request', array( 'FrmAPIUsageController', 'track_request' ), 10 );
		delete_option( FrmUsageController::FLOWS_ACTION_NAME );
		parent::tearDown();
	}

	/**
	 * Nothing is written while the site has not opted in to usage tracking.
	 *
	 * @return void
	 */
	public function test_nothing_is_counted_without_tracking() {
		FrmAppHelper::get_settings()->update_setting( 'tracking', 0, 'absint' );

		FrmMcpUsageController::track_request( $this->initialize_body( 'claude-code' ), '' );
		FrmMcpUsageController::track_request( array( 'method' => 'tools/call' ), 'mcp-adapter-discover-abilities' );

		$this->assertSame( array(), FrmUsageController::get_flows_data() );
	}

	/**
	 * The client is counted once per handshake and the endpoint once per call.
	 *
	 * @return void
	 */
	public function test_counts_the_client_and_the_endpoint() {
		FrmMcpUsageController::track_request( $this->initialize_body( 'claude-code' ), '' );
		FrmMcpUsageController::track_request( array( 'method' => 'tools/call' ), 'mcp-adapter-discover-abilities' );
		FrmMcpUsageController::track_request( array( 'method' => 'tools/call' ), 'mcp-adapter-discover-abilities' );

		$flows = FrmUsageController::get_flows_data();

		$this->assertSame( array( 'claude-code' => 1 ), $flows[ FrmMcpUsageController::CLIENT_FLOW ] );
		$this->assertSame( array( 'mcp-adapter-discover-abilities' => 2 ), $flows[ FrmMcpUsageController::ENDPOINT_FLOW ] );
	}

	/**
	 * A name the site does not register is grouped, so a client cannot fill the flows data with its own names.
	 *
	 * @return void
	 */
	public function test_unknown_endpoint_is_counted_as_other() {
		FrmMcpUsageController::track_request( array( 'method' => 'tools/call' ), 'not-a-real/ability-name' );

		$flows = FrmUsageController::get_flows_data();

		$this->assertSame( array( FrmMcpUsageController::OTHER_ENDPOINT => 1 ), $flows[ FrmMcpUsageController::ENDPOINT_FLOW ] );
	}

	/**
	 * Only the initialize handshake names a client, so later requests add no client count.
	 *
	 * @return void
	 */
	public function test_client_is_only_read_from_initialize() {
		$body           = $this->initialize_body( 'codex' );
		$body['method'] = 'tools/list';

		FrmMcpUsageController::track_request( $body, '' );

		$this->assertArrayNotHasKey( FrmMcpUsageController::CLIENT_FLOW, FrmUsageController::get_flows_data() );
	}

	/**
	 * An API add-on that still counts requests itself keeps doing so, and Formidable does not count them twice.
	 *
	 * @return void
	 */
	public function test_leaves_counting_to_an_api_addon_that_counts() {
		add_action( 'frm_mcp_request', array( 'FrmAPIUsageController', 'track_request' ), 10, 2 );

		FrmMcpUsageController::track_request( $this->initialize_body( 'claude-code' ), '' );

		$this->assertSame( array(), FrmUsageController::get_flows_data() );
	}

	/**
	 * A user connected from two clients is one connected user, not two.
	 *
	 * @return void
	 */
	public function test_snapshot_counts_each_user_once() {
		$user_id = self::factory()->user->create();

		FrmMcpConnection::log_request( $user_id, 'formidable-forms/list-forms', 'claude-code' );
		FrmMcpConnection::log_request( $user_id, 'formidable-forms/list-forms', 'codex' );

		$snapshot = FrmMcpUsageController::add_snapshot_data( array() );

		$this->assertSame( 1, $snapshot['mcp_connected_users'] );
		$this->assertSame( 1, $snapshot['using_mcp'] );
		$this->assertNotSame( '', $snapshot['mcp_last_request'] );
		$this->assertContains( $snapshot['mcp_enabled'], array( 0, 1 ) );

		delete_user_meta( $user_id, FrmMcpConnection::META_KEY );
	}

	/**
	 * @param string $client Client name sent in the handshake.
	 *
	 * @return array
	 */
	private function initialize_body( $client ) {
		return array(
			'method' => 'initialize',
			'params' => array(
				'clientInfo' => array(
					'name'    => $client,
					'version' => '1.0',
				),
			),
		);
	}
}
