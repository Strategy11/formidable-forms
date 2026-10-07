<?php

/**
 * Tests for the downloadable MCP skill configuration and its credential scope.
 */
class test_FrmMcpSkillEnvController extends FrmUnitTest {

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
