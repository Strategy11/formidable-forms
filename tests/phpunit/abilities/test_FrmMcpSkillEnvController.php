<?php

/**
 * Tests for the downloadable MCP skill configuration and its credential scope.
 */
class test_FrmMcpSkillEnvController extends FrmUnitTest {

	/**
	 * Check submitted toggle states and authorization boundaries for downloads.
	 *
	 * @return void
	 */
	public function test_download_saves_submitted_mcp_setting() {
		foreach ( self::get_download_scenarios() as $scenario ) {
			$this->check_download_settings( $scenario[0], $scenario[1], $scenario[2], $scenario[3], $scenario[4] );
		}
	}

	/**
	 * Downloading saves only the submitted MCP toggle after authorization.
	 *
	 * Application Passwords are disabled to stop before the file response exits.
	 *
	 * @param int    $initial     Saved MCP value before downloading.
	 * @param array  $posted      Submitted MCP settings.
	 * @param int    $expected    Saved MCP value after downloading.
	 * @param string $role        Role of the downloading user.
	 * @param bool   $valid_nonce Whether the download nonce is valid.
	 *
	 * @return void
	 */
	private function check_download_settings( $initial, $posted, $expected, $role, $valid_nonce ) {
		$admin_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );
		FrmAppHelper::maybe_add_permissions();
		$user_id = $this->factory->user->create( array( 'role' => $role ) );
		wp_set_current_user( $user_id );

		$settings      = FrmAppHelper::get_settings();
		$original_mcp  = $settings->mcp;
		$original_post = $_POST;
		$settings->mcp = $initial;
		$settings->store();
		FrmMcpController::reset();
		$this->assertSame( (bool) $initial, FrmMcpController::is_enabled(), 'The initial MCP setting should be cached.' );
		$original_currency = $settings->currency;

		$_POST = $posted;
		$_POST[ FrmMcpSkillEnvController::DOWNLOAD_ACTION . '_nonce' ] = $valid_nonce ? wp_create_nonce( FrmMcpSkillEnvController::DOWNLOAD_ACTION ) : 'invalid';
		add_filter( 'wp_is_application_passwords_available', '__return_false' );

		try {
			FrmMcpSkillEnvController::download();
			$this->fail( 'The download should stop before creating a credential.' );
		} catch ( WPDieException $exception ) {
			$this->assertSame( 403, $exception->getCode(), 'The blocked download should return a forbidden response.' );
			$this->assertSame( $expected, $settings->mcp, 'The submitted toggle should be saved only after authorization.' );
			$this->assertSame( $expected, get_option( 'frm_options' )->mcp, 'The MCP setting should persist for the next request.' );
			$this->assertSame( (bool) $expected, FrmMcpController::is_enabled(), 'The cached MCP setting should reflect the saved value.' );
			$this->assertSame( $original_currency, $settings->currency, 'Other settings should remain unchanged.' );
			$this->assertSame( array(), FrmMcpSkillEnvController::get_passwords( $user_id ), 'A blocked download should not create a credential.' );
		} finally {
			$_POST = $original_post;
			remove_filter( 'wp_is_application_passwords_available', '__return_false' );
			$settings->mcp = $original_mcp;
			$settings->store();
			FrmMcpController::reset();
		}
	}

	/**
	 * Submitted toggle states and authorization boundaries for downloads.
	 *
	 * @return array
	 */
	private static function get_download_scenarios() {
		$enabled = array(
			'frm_mcp_settings_shown' => 1,
			'frm_mcp'                => array( 1 ),
		);

		return array(
			'unsaved enabled toggle' => array( 0, $enabled, 1, 'administrator', true ),
			'unchecked toggle'       => array( 1, array( 'frm_mcp_settings_shown' => 1 ), 0, 'administrator', true ),
			'no settings submitted'  => array( 1, array(), 1, 'administrator', true ),
			'no settings marker'     => array( 0, array( 'frm_mcp' => array( 1 ) ), 0, 'administrator', true ),
			'invalid nonce'          => array( 0, $enabled, 0, 'administrator', false ),
			'unauthorized user'      => array( 0, $enabled, 0, 'subscriber', true ),
		);
	}

	/**
	 * Values containing shell syntax stay literal when the skill sources the file.
	 *
	 * @return void
	 */
	public function test_env_values_are_shell_quoted() {
		$method = new ReflectionMethod( 'FrmMcpSkillEnvController', 'build_env' );
		$method->setAccessible( true );
		$contents = $method->invoke( null, 'https://example.com/', "user'name", 'dummy$(echo unsafe)' );

		$this->assertStringContainsString( "SITE_URL='https://example.com'", $contents );
		$this->assertStringContainsString( "WP_USERNAME='user'\\''name'", $contents );
		$this->assertStringContainsString( "APPLICATION_PASSWORD='dummy$(echo unsafe)'", $contents );
	}

	/**
	 * The last setup step is only checked off once a skill password has been used.
	 *
	 * @return void
	 */
	public function test_last_used_reports_the_latest_use() {
		$this->assertSame( 0, FrmMcpSkillEnvController::get_last_used( array() ) );
		$this->assertSame( 0, FrmMcpSkillEnvController::get_last_used( array( array( 'last_used' => null ) ) ) );

		$passwords = array(
			array( 'last_used' => 100 ),
			array( 'last_used' => null ),
			array( 'last_used' => 300 ),
		);
		$this->assertSame( 300, FrmMcpSkillEnvController::get_last_used( $passwords ) );

		$this->assertStringStartsWith( 'Waiting', FrmMcpSkillEnvController::get_connection_message( 0 ) );
		$this->assertStringStartsWith( 'Connected', FrmMcpSkillEnvController::get_connection_message( time() - HOUR_IN_SECONDS ) );
	}

	/**
	 * The generated password cannot authenticate other REST routes or abilities.
	 *
	 * @return void
	 */
	public function test_skill_password_is_limited_to_formidable_mcp() {
		$user_id = $this->factory->user->create();
		$created = WP_Application_Passwords::create_new_application_password(
			$user_id,
			array(
				'name'   => 'Formidable MCP test',
				'app_id' => FrmMcpSkillEnvController::APP_ID,
			)
		);

		$this->assertIsArray( $created );
		wp_set_current_user( $user_id );
		global $wp_rest_application_password_uuid;
		$wp_rest_application_password_uuid = $created[1]['uuid'];
		$this->assertSame( $created[1]['uuid'], rest_get_authenticated_app_password() );

		$other_route = new WP_REST_Request( 'GET', '/wp/v2/users/me' );
		$denied      = FrmMcpSkillEnvController::restrict_rest_route( null, rest_get_server(), $other_route );
		$this->assertWPError( $denied );
		$this->assertSame( 403, $denied->get_error_data()['status'] );

		$mcp_request = new WP_REST_Request( 'POST', FrmMcpConnection::MCP_ROUTE );
		$mcp_request->set_header( 'Content-Type', 'application/json' );
		$mcp_request->set_body( wp_json_encode( array( 'method' => 'initialize' ) ) );
		$this->assertNull( FrmMcpSkillEnvController::restrict_rest_route( null, rest_get_server(), $mcp_request ) );

		$mcp_request->set_body(
			wp_json_encode(
				array(
					'method' => 'tools/call',
					'params' => array(
						'name'      => 'mcp-adapter-execute-ability',
						'arguments' => array( 'ability_name' => 'another-plugin/test' ),
					),
				)
			)
		);
		$denied = FrmMcpSkillEnvController::restrict_rest_route( null, rest_get_server(), $mcp_request );
		$this->assertWPError( $denied );
		$this->assertSame( 403, $denied->get_error_data()['status'] );

		$mcp_request->set_body(
			wp_json_encode(
				array(
					'method' => 'tools/call',
					'params' => array( 'name' => 'mcp-adapter-discover-abilities' ),
				)
			)
		);
		$this->assertWPError( FrmMcpSkillEnvController::restrict_rest_route( null, rest_get_server(), $mcp_request ) );

		$mcp_request->set_body(
			wp_json_encode(
				array(
					'method' => 'tools/call',
					'params' => array(
						'name'      => 'mcp-adapter-execute-ability',
						'arguments' => array( 'ability_name' => 'formidable-forms/list-forms' ),
					),
				)
			)
		);
		$this->assertNull( FrmMcpSkillEnvController::restrict_rest_route( null, rest_get_server(), $mcp_request ) );

		$mcp_request->set_body(
			wp_json_encode(
				array(
					'method' => 'resources/read',
					'params' => array( 'uri' => 'other-plugin://resource' ),
				)
			)
		);
		$this->assertWPError( FrmMcpSkillEnvController::restrict_rest_route( null, rest_get_server(), $mcp_request ) );

		$passwords = FrmMcpSkillEnvController::get_passwords( $user_id );
		$this->assertCount( 1, $passwords );
		$this->assertArrayNotHasKey( 'password', $passwords[0] );
		$wp_rest_application_password_uuid = null;
	}
}
