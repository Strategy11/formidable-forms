<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Create and manage credentials for the Formidable MCP skill's HTTP helper.
 *
 * @since x.x
 */
class FrmMcpSkillEnvController {
	/**
	 * Marks skill downloads so only their passwords get the MCP scope and revoke controls.
	 *
	 * @since x.x
	 * @var string
	 */
	const APP_ID          = '2ad44c9b-985f-4bf6-a473-1541da1d1bd9';
	const DOWNLOAD_ACTION = 'frm_mcp_download_env';
	const REVOKE_ACTION   = 'frm_mcp_revoke_skill_password';

	/**
	 * Register the download and the restrictions for issued passwords.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_hooks() {
		add_action( 'admin_post_' . self::DOWNLOAD_ACTION, 'FrmMcpSkillEnvController::download' );
		add_action( 'admin_post_' . self::REVOKE_ACTION, 'FrmMcpSkillEnvController::revoke' );
		add_action( 'wp_authenticate_application_password_errors', 'FrmMcpSkillEnvController::restrict_password_use', 10, 4 );
		add_filter( 'rest_pre_dispatch', 'FrmMcpSkillEnvController::restrict_rest_route', 4, 3 );
	}

	/**
	 * Create a fresh password and send its only plaintext copy as an env file.
	 *
	 * @since x.x
	 * @see action hook admin_post_frm_mcp_download_env
	 *
	 * @return void
	 */
	public static function download() {
		$user_id = get_current_user_id();

		if ( ! FrmAppHelper::current_user_can( 'frm_change_settings' ) || ! current_user_can( 'create_app_password', $user_id ) ) {
			wp_die( esc_html__( 'You do not have permission to create an MCP application password.', 'formidable' ), '', array( 'response' => 403 ) );
		}

		self::verify_nonce( self::DOWNLOAD_ACTION );

		if (
			! FrmMcpController::is_enabled() ||
			! FrmMcpCompat::is_usable() ||
			! wp_is_application_passwords_available_for_user( $user_id ) ||
			( 'https' !== wp_parse_url( home_url(), PHP_URL_SCHEME ) && 'local' !== wp_get_environment_type() )
		) {
			wp_die( esc_html__( 'Enable the Formidable MCP server and Application Passwords before downloading this file.', 'formidable' ), '', array( 'response' => 403 ) );
		}

		$name    = 'Formidable MCP skill ' . gmdate( 'Y-m-d H:i:s' ) . ' ' . wp_generate_password( 4, false, false );
		$created = WP_Application_Passwords::create_new_application_password(
			$user_id,
			array(
				'name'   => $name,
				'app_id' => self::APP_ID,
			)
		);

		if ( is_wp_error( $created ) ) {
			wp_die( esc_html( $created->get_error_message() ), '', array( 'response' => 500 ) );
		}

		$contents = self::build_env( home_url(), wp_get_current_user()->user_login, $created[0] );
		nocache_headers();
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename="frm-mcp.env"' );
		header( 'Cache-Control: private, no-store, max-age=0' );
		header( 'X-Content-Type-Options: nosniff' );
		echo $contents; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Shell-quoted file download, never HTML.
		exit;
	}

	/**
	 * Revoke one of this user's Formidable MCP skill passwords.
	 *
	 * @since x.x
	 * @see action hook admin_post_frm_mcp_revoke_skill_password
	 *
	 * @return void
	 */
	public static function revoke() {
		$user_id = get_current_user_id();

		if ( ! FrmAppHelper::current_user_can( 'frm_change_settings' ) || ! current_user_can( 'delete_app_password', $user_id ) ) {
			wp_die( esc_html__( 'You do not have permission to revoke an MCP application password.', 'formidable' ), '', array( 'response' => 403 ) );
		}

		self::verify_nonce( self::REVOKE_ACTION );
		$uuid = FrmAppHelper::simple_get( 'frm_mcp_password_uuid', 'sanitize_text_field' );
		$item = WP_Application_Passwords::get_user_application_password( $user_id, $uuid );

		if ( ! $item || self::APP_ID !== $item['app_id'] ) {
			wp_die( esc_html__( 'This MCP application password was not found.', 'formidable' ), '', array( 'response' => 404 ) );
		}

		$deleted = WP_Application_Passwords::delete_application_password( $user_id, $uuid );

		if ( is_wp_error( $deleted ) ) {
			wp_die( esc_html( $deleted->get_error_message() ), '', array( 'response' => 500 ) );
		}

		wp_safe_redirect( admin_url( 'admin.php?page=formidable-settings&t=' . FrmMcpSettingsController::TAB_ANCHOR ) );
		exit;
	}

	/**
	 * Check the action-specific POST nonce.
	 *
	 * @since x.x
	 *
	 * @param string $action Download or revoke action.
	 *
	 * @return void
	 */
	private static function verify_nonce( $action ) {
		$nonce = FrmAppHelper::get_post_param( $action . '_nonce', '', 'sanitize_text_field' );

		if ( ! wp_verify_nonce( $nonce, $action ) ) {
			wp_die( esc_html__( 'The request expired. Please try again.', 'formidable' ), '', array( 'response' => 403 ) );
		}
	}

	/**
	 * List only the current user's passwords created by this download flow.
	 *
	 * @since x.x
	 *
	 * @param int $user_id WordPress user ID.
	 *
	 * @return array<array> Password metadata without password hashes.
	 */
	public static function get_passwords( $user_id ) {
		$passwords = array();

		foreach ( WP_Application_Passwords::get_user_application_passwords( $user_id ) as $item ) {
			if ( self::APP_ID !== $item['app_id'] ) {
				continue;
			}

			$passwords[] = array(
				'uuid'      => $item['uuid'],
				'name'      => $item['name'],
				'created'   => $item['created'],
				'last_used' => $item['last_used'],
			);
		}

		return $passwords;
	}

	/**
	 * Block these passwords on XML-RPC and other non-REST transports.
	 *
	 * @since x.x
	 * @see action hook wp_authenticate_application_password_errors
	 *
	 * @param WP_Error $error    Authentication errors to add to.
	 * @param WP_User  $user     User being authenticated.
	 * @param array    $item     Application password metadata.
	 * @param string   $password Supplied application password.
	 *
	 * @return void
	 */
	public static function restrict_password_use( $error, $user, $item, $password ) {
		if ( self::APP_ID !== ( $item['app_id'] ?? '' ) ) {
			return;
		}

		if ( ! defined( 'REST_REQUEST' ) || ! REST_REQUEST ) {
			$error->add( 'frm_mcp_skill_rest_only', __( 'This application password can only be used with the Formidable MCP REST endpoint.', 'formidable' ) );
		}
	}

	/**
	 * Limit these passwords to the Formidable MCP route and abilities.
	 *
	 * @since x.x
	 * @see filter hook rest_pre_dispatch
	 *
	 * @param mixed                         $result  Earlier dispatch result.
	 * @param WP_REST_Server                $server  REST server.
	 * @param WP_REST_Request<array<mixed>> $request Incoming request.
	 *
	 * @return mixed
	 */
	public static function restrict_rest_route( $result, $server, $request ) {
		if ( ! function_exists( 'rest_get_authenticated_app_password' ) ) {
			return $result;
		}

		$uuid = rest_get_authenticated_app_password();

		if ( ! $uuid || ! get_current_user_id() ) {
			return $result;
		}

		$item = WP_Application_Passwords::get_user_application_password( get_current_user_id(), $uuid );

		if ( ! $item || self::APP_ID !== $item['app_id'] ) {
			return $result;
		}

		if ( FrmMcpConnection::MCP_ROUTE !== untrailingslashit( $request->get_route() ) || self::requests_other_ability( $request->get_json_params() ) ) {
			return new WP_Error( 'frm_mcp_skill_scope', __( 'This application password can only access Formidable MCP abilities.', 'formidable' ), array( 'status' => 403 ) );
		}

		return $result;
	}

	/**
	 * Allow protocol setup and Formidable ability calls for a scoped password.
	 *
	 * @since x.x
	 *
	 * @param mixed $body Decoded JSON-RPC request or batch.
	 *
	 * @return bool
	 */
	private static function requests_other_ability( $body ) {
		if ( ! is_array( $body ) ) {
			return true;
		}

		if ( isset( $body['method'] ) ) {
			if ( in_array( $body['method'], array( 'initialize', 'notifications/initialized', 'ping', 'tools/list' ), true ) ) {
				return false;
			}

			if ( 'tools/call' !== $body['method'] ) {
				return true;
			}

			$params = isset( $body['params'] ) && is_array( $body['params'] ) ? $body['params'] : array();
			$tool   = $params['name'] ?? '';

			if ( ! in_array( $tool, array( 'mcp-adapter-execute-ability', 'mcp-adapter-get-ability-info' ), true ) ) {
				return true;
			}

			$name = $params['arguments']['ability_name'] ?? '';
			return ! is_string( $name ) || ! FrmMcpConnection::is_formidable_ability( $name );
		}

		if ( ! $body ) {
			return true;
		}

		foreach ( $body as $message ) {
			if ( self::requests_other_ability( $message ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Format values as quoted shell assignments for the skill's env loader.
	 *
	 * @since x.x
	 *
	 * @param string $url      Base site URL.
	 * @param string $username WordPress login name.
	 * @param string $password Newly created application password.
	 *
	 * @return string Downloadable env file contents.
	 */
	private static function build_env( $url, $username, $password ) {
		return '# Formidable MCP skill configuration. Keep this file private and out of version control.' . "\n"
		. 'SITE_URL=' . self::shell_quote( untrailingslashit( $url ) ) . "\n"
		. 'WP_USERNAME=' . self::shell_quote( $username ) . "\n"
		. 'APPLICATION_PASSWORD=' . self::shell_quote( $password ) . "\n";
	}

	/**
	 * Quote a value for a POSIX shell file sourced by the skill.
	 *
	 * @since x.x
	 *
	 * @param string $value Assignment value.
	 *
	 * @return string Safely quoted value.
	 */
	private static function shell_quote( $value ) {
		return "'" . str_replace( "'", "'\\''", $value ) . "'";
	}
}
