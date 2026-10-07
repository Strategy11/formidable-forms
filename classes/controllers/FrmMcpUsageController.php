<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Reports MCP usage to Formidable's usage tracking.
 *
 * Nothing new is stored for this. The endpoints that were called and the
 * clients that called them are counted in the flows data, and the weekly
 * snapshot only reports the current state of the site, read from the MCP
 * setting and from the connection activity FrmMcpConnection already keeps.
 *
 * Only the endpoint name and the client name are recorded. The arguments of a
 * request, the entries, fields, and form data they carry, and the response are
 * all read past and never stored, and nothing recorded identifies a user.
 *
 * The flow keys and snapshot fields are the ones the API add-on reported
 * before the MCP server moved into Formidable, so the numbers carry on from
 * where the add-on left them.
 *
 * @since x.x
 */
class FrmMcpUsageController {

	/**
	 * Flow key counting how often each MCP endpoint was called.
	 *
	 * @var string
	 */
	const ENDPOINT_FLOW = 'mcp_endpoint';

	/**
	 * Flow key recording which MCP clients connected.
	 *
	 * @var string
	 */
	const CLIENT_FLOW = 'mcp_client';

	/**
	 * Flow value used for endpoints that are not a registered ability or tool.
	 *
	 * @var string
	 */
	const OTHER_ENDPOINT = 'other';

	/**
	 * Maximum length of a recorded client name.
	 *
	 * @var int
	 */
	const MAX_NAME_LENGTH = 100;

	/**
	 * Number of days without a request after which a site is no longer using MCP.
	 *
	 * @var int
	 */
	const ACTIVE_DAYS = 30;

	/**
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_hooks() {
		add_action( 'frm_mcp_request', 'FrmMcpUsageController::track_request', 10, 2 );
		add_filter( 'frm_usage_snapshot', 'FrmMcpUsageController::add_snapshot_data' );
	}

	/**
	 * Count one MCP request in the flows data.
	 *
	 * Nothing is counted unless the site opted in to usage tracking, so no
	 * option is written that the site never sends. An API add-on that still
	 * counts these requests itself is left to do so, so each request is only
	 * counted once.
	 *
	 * @since x.x
	 * @see action hook frm_mcp_request
	 *
	 * @param array  $body     Decoded JSON-RPC request body.
	 * @param string $endpoint Endpoint name, or an empty string for protocol requests.
	 *
	 * @return void
	 */
	public static function track_request( $body, $endpoint ) {
		if ( ! FrmUsageController::tracking_allowed() || self::api_addon_tracks_requests() ) {
			return;
		}

		self::add_flow( self::ENDPOINT_FLOW, self::endpoint_flow_value( $endpoint ) );
		self::add_flow( self::CLIENT_FLOW, self::client_flow_value( $body ) );
	}

	/**
	 * Check whether the API add-on counts MCP requests itself.
	 *
	 * The add-on release that defers the MCP server to Formidable still hooks
	 * its own counter to frm_mcp_request.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	private static function api_addon_tracks_requests() {
		return false !== has_action( 'frm_mcp_request', array( 'FrmAPIUsageController', 'track_request' ) );
	}

	/**
	 * Add the current MCP state to the weekly usage snapshot.
	 *
	 * Every value is a single field at the top level of the snapshot, the way
	 * the rest of the snapshot keeps its own counts and flags. What was called,
	 * and by which client, is counted in the flows data instead of being
	 * repeated here.
	 *
	 * @since x.x
	 * @see filter hook frm_usage_snapshot
	 *
	 * @param array $snapshot Usage snapshot data.
	 *
	 * @return array
	 */
	public static function add_snapshot_data( $snapshot ) {
		$connections  = FrmMcpConnection::get_connections();
		$last_request = self::last_request( $connections );

		$snapshot['mcp_enabled']         = (int) FrmMcpController::is_enabled();
		$snapshot['using_mcp']           = (int) ( $last_request >= time() - ( self::ACTIVE_DAYS * DAY_IN_SECONDS ) );
		$snapshot['mcp_connected_users'] = count( array_unique( wp_list_pluck( $connections, 'user_login' ) ) );
		$snapshot['mcp_last_request']    = $last_request ? gmdate( 'c', $last_request ) : '';

		return $snapshot;
	}

	/**
	 * Get the flow value for one endpoint.
	 *
	 * Endpoint names come from the request body, so anything that is not a
	 * registered ability or one of the adapter's own tools is counted as other.
	 * That keeps a client from filling the flows data with names of its own.
	 *
	 * @since x.x
	 *
	 * @param string $endpoint Endpoint name, or an empty string for protocol requests.
	 *
	 * @return string
	 */
	private static function endpoint_flow_value( $endpoint ) {
		if ( ! is_string( $endpoint ) || '' === $endpoint ) {
			return '';
		}

		if ( in_array( $endpoint, self::known_tools(), true ) ) {
			return $endpoint;
		}

		return function_exists( 'wp_has_ability' ) && wp_has_ability( $endpoint ) ? $endpoint : self::OTHER_ENDPOINT;
	}

	/**
	 * Get the tools the MCP adapter registers alongside the abilities.
	 *
	 * @since x.x
	 *
	 * @return array<int, string>
	 */
	private static function known_tools() {
		return array(
			'mcp-adapter-discover-abilities',
			'mcp-adapter-execute-ability',
			'mcp-adapter-get-ability-info',
		);
	}

	/**
	 * Get the flow value for the client in an initialize handshake.
	 *
	 * Every other request returns an empty string, so a client is recorded once
	 * per connection rather than once per request. The name is the one the
	 * client sent, like claude-code or cursor-vscode, rather than the label the
	 * settings page groups it under, so the data keeps which app it was.
	 *
	 * @since x.x
	 *
	 * @param array $body Decoded JSON-RPC request body.
	 *
	 * @return string
	 */
	private static function client_flow_value( $body ) {
		if ( ! is_array( $body ) || ! isset( $body['method'] ) || 'initialize' !== $body['method'] ) {
			return '';
		}

		$client_info = isset( $body['params']['clientInfo'] ) && is_array( $body['params']['clientInfo'] ) ? $body['params']['clientInfo'] : array();

		if ( ! isset( $client_info['name'] ) || ! is_string( $client_info['name'] ) ) {
			return '';
		}

		return substr( sanitize_text_field( $client_info['name'] ), 0, self::MAX_NAME_LENGTH );
	}

	/**
	 * Get the most recent MCP request time of any user.
	 *
	 * @since x.x
	 *
	 * @param array<int, array{last_request: int}> $connections Connection rows from FrmMcpConnection::get_connections().
	 *
	 * @return int Unix timestamp, or 0 when nothing has connected.
	 */
	private static function last_request( $connections ) {
		if ( ! $connections ) {
			return 0;
		}

		// Rows are sorted by the most recent request first.
		$latest = reset( $connections );

		return $latest['last_request'];
	}

	/**
	 * Count one flow event.
	 *
	 * @since x.x
	 *
	 * @param string $key   Flow key.
	 * @param string $value Name of the event to count.
	 *
	 * @return void
	 */
	private static function add_flow( $key, $value ) {
		if ( '' === $value ) {
			return;
		}

		FrmUsageController::update_flows_data( $key, $value );
	}
}
