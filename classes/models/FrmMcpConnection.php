<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Tracks MCP connection activity per user.
 *
 * Every request to the Formidable MCP server is recorded against the
 * authenticated WordPress user, keeping the time of the last request and a
 * short list of the endpoints (abilities, tools, resources, prompts) that were
 * called most recently. The summary is shown on the MCP global settings page.
 *
 * The same requests fire the frm_mcp_request action, which is how the API
 * add-on feeds them into Formidable's usage tracking without this class having
 * to know the add-on exists.
 *
 * @since x.x
 */
class FrmMcpConnection {

	/**
	 * User meta key holding the MCP activity for one user.
	 *
	 * This is deliberately the key the API add-on has always written, so a site
	 * that connected before Formidable owned the MCP server keeps its history
	 * instead of showing an empty table after the update.
	 *
	 * @var string
	 */
	const META_KEY = 'frm_api_mcp_activity';

	/**
	 * REST route of the Formidable MCP server registered in FrmMcpController::register_mcp_server().
	 *
	 * @var string
	 */
	const MCP_ROUTE = '/mcp/formidable-mcp';

	/**
	 * Maximum number of endpoints remembered per user.
	 *
	 * @var int
	 */
	const MAX_ENDPOINTS = 10;

	/**
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_hooks() {
		add_filter( 'rest_request_before_callbacks', 'FrmMcpConnection::log_mcp_request', 10, 3 );
	}

	/**
	 * Record MCP requests as they are dispatched by the REST server.
	 *
	 * Runs on every REST request and ignores everything that is not an
	 * authenticated request to the Formidable MCP route. Requests already
	 * rejected by an earlier filter are not recorded. The response is
	 * always returned unchanged.
	 *
	 * @since x.x
	 * @see filter hook rest_request_before_callbacks
	 *
	 * @param mixed                         $response Result to send to the client.
	 * @param array                         $handler  Route handler used for the request.
	 * @param WP_REST_Request<array<mixed>> $request  Request used to generate the response.
	 *
	 * @return mixed
	 */
	public static function log_mcp_request( $response, $handler, $request ) {
		if ( is_wp_error( $response ) || ! $request instanceof WP_REST_Request || self::MCP_ROUTE !== untrailingslashit( $request->get_route() ) ) {
			return $response;
		}

		$user_id = get_current_user_id();

		if ( ! $user_id ) {
			return $response;
		}

		$body = $request->get_json_params();

		if ( ! is_array( $body ) ) {
			$body = array();
		}

		$endpoint = self::get_endpoint( $body );

		self::log_request( $user_id, $endpoint );

		/**
		 * Fires for every authenticated request to the Formidable MCP server.
		 *
		 * The API add-on hooks this to count the endpoint and the client in
		 * Formidable's usage tracking, which is where this lived before the MCP
		 * server moved into Formidable itself.
		 *
		 * @since x.x
		 *
		 * @param array  $body     Decoded JSON-RPC request body.
		 * @param string $endpoint Endpoint name, or an empty string for protocol requests.
		 */
		do_action( 'frm_mcp_request', $body, $endpoint );

		return $response;
	}

	/**
	 * Get the endpoint name from a JSON-RPC request body.
	 *
	 * For ability executions this is the ability name (formidable-forms/list-forms),
	 * for other tool calls the tool name, and for resource and prompt requests
	 * the resource URI or prompt name. Protocol requests like initialize and
	 * tools/list return an empty string so they only bump the last request time.
	 *
	 * @since x.x
	 *
	 * @param array $body Decoded JSON-RPC request body.
	 *
	 * @return string
	 */
	private static function get_endpoint( $body ) {
		$method = isset( $body['method'] ) && is_string( $body['method'] ) ? $body['method'] : '';
		$params = isset( $body['params'] ) && is_array( $body['params'] ) ? $body['params'] : array();

		if ( 'tools/call' === $method ) {
			if ( isset( $params['arguments']['ability_name'] ) && is_string( $params['arguments']['ability_name'] ) ) {
				return $params['arguments']['ability_name'];
			}

			return isset( $params['name'] ) && is_string( $params['name'] ) ? $params['name'] : '';
		}

		if ( 'resources/read' === $method ) {
			return isset( $params['uri'] ) && is_string( $params['uri'] ) ? $params['uri'] : '';
		}

		if ( 'prompts/get' === $method ) {
			return isset( $params['name'] ) && is_string( $params['name'] ) ? $params['name'] : '';
		}

		return '';
	}

	/**
	 * Record one MCP request for a user.
	 *
	 * @since x.x
	 *
	 * @param int    $user_id  ID of the connecting user.
	 * @param string $endpoint Endpoint name, or an empty string for protocol requests.
	 *
	 * @return void
	 */
	public static function log_request( $user_id, $endpoint = '' ) {
		$now      = time();
		$activity = self::get_activity( $user_id );

		$activity['last_request'] = $now;

		if ( '' !== $endpoint ) {
			$endpoint = substr( sanitize_text_field( $endpoint ), 0, 200 );

			if ( isset( $activity['endpoints'][ $endpoint ] ) ) {
				$activity['endpoints'][ $endpoint ]['time'] = $now;
				++$activity['endpoints'][ $endpoint ]['count'];
			} else {
				$activity['endpoints'][ $endpoint ] = array(
					'time'  => $now,
					'count' => 1,
				);
			}

			$activity['endpoints'] = self::cap_endpoints( $activity['endpoints'] );
		}

		update_user_meta( $user_id, self::META_KEY, $activity );
	}

	/**
	 * Drop the oldest endpoints once the list grows past the limit.
	 *
	 * @since x.x
	 *
	 * @param array<string, array{time: int, count: int}> $endpoints Endpoint names mapped to time and count data.
	 *
	 * @return array<string, array{time: int, count: int}>
	 */
	private static function cap_endpoints( $endpoints ) {
		$endpoint_count = count( $endpoints );

		while ( $endpoint_count > self::MAX_ENDPOINTS ) {
			$oldest_key  = '';
			$oldest_time = PHP_INT_MAX;

			foreach ( $endpoints as $endpoint => $endpoint_data ) {
				if ( $endpoint_data['time'] >= $oldest_time ) {
					continue;
				}

				$oldest_time = $endpoint_data['time'];
				$oldest_key  = $endpoint;
			}

			unset( $endpoints[ $oldest_key ] );
			--$endpoint_count;
		}

		return $endpoints;
	}

	/**
	 * Get the stored MCP activity for one user.
	 *
	 * @since x.x
	 *
	 * @param int $user_id ID of the user to look up.
	 *
	 * @return array{last_request: int, endpoints: array<string, array{time: int, count: int}>}
	 */
	private static function get_activity( $user_id ) {
		$activity = get_user_meta( $user_id, self::META_KEY, true );

		if ( ! is_array( $activity ) ) {
			$activity = array();
		}

		$endpoints = array();

		if ( isset( $activity['endpoints'] ) && is_array( $activity['endpoints'] ) ) {
			foreach ( $activity['endpoints'] as $endpoint => $endpoint_data ) {
				if ( ! is_string( $endpoint ) || ! is_array( $endpoint_data ) ) {
					continue;
				}

				$endpoints[ $endpoint ] = array(
					'time'  => isset( $endpoint_data['time'] ) ? max( 0, (int) $endpoint_data['time'] ) : 0,
					'count' => isset( $endpoint_data['count'] ) ? max( 0, (int) $endpoint_data['count'] ) : 1,
				);
			}
		}

		return array(
			'last_request' => isset( $activity['last_request'] ) ? max( 0, (int) $activity['last_request'] ) : 0,
			'endpoints'    => $endpoints,
		);
	}

	/**
	 * Get the MCP connection summary for every user with recorded activity.
	 *
	 * Rows are sorted by the most recent request first, and each row's
	 * endpoints are sorted by the most recently called first, limited to
	 * those called within the last month.
	 *
	 * @since x.x
	 *
	 * @return array<int, array{user_login: string, display_name: string, last_request: int, endpoints: array<string, array{time: int, count: int}>}>
	 */
	public static function get_connections() {
		$users = get_users(
			array(
				'meta_key' => self::META_KEY,
			)
		);

		$connections = array();

		foreach ( $users as $user ) {
			if ( ! $user instanceof WP_User ) {
				continue;
			}

			$activity = self::get_activity( $user->ID );

			if ( ! $activity['last_request'] ) {
				continue;
			}

			$activity['endpoints'] = self::filter_recent_endpoints( $activity['endpoints'] );

			uasort( $activity['endpoints'], 'FrmMcpConnection::compare_endpoint_time' );

			$connections[] = array(
				'user_login'   => $user->user_login,
				'display_name' => $user->display_name,
				'last_request' => $activity['last_request'],
				'endpoints'    => $activity['endpoints'],
			);
		}//end foreach

		usort( $connections, 'FrmMcpConnection::compare_last_request' );

		return $connections;
	}

	/**
	 * Sort two endpoint rows so the most recently called comes first.
	 *
	 * @since x.x
	 *
	 * @param array{time: int, count: int} $a First endpoint's time and count data.
	 * @param array{time: int, count: int} $b Second endpoint's time and count data.
	 *
	 * @return int
	 */
	public static function compare_endpoint_time( $a, $b ) {
		return $b['time'] - $a['time'];
	}

	/**
	 * Sort two connection rows so the most recent request comes first.
	 *
	 * @since x.x
	 *
	 * @param array $a First connection row.
	 * @param array $b Second connection row.
	 *
	 * @return int
	 */
	public static function compare_last_request( $a, $b ) {
		return $b['last_request'] - $a['last_request'];
	}

	/**
	 * Keep only the endpoints called within the last month.
	 *
	 * @since x.x
	 *
	 * @param array<string, array{time: int, count: int}> $endpoints Endpoint names mapped to time and count data.
	 *
	 * @return array<string, array{time: int, count: int}>
	 */
	private static function filter_recent_endpoints( $endpoints ) {
		$cutoff = strtotime( '-1 month' );
		$recent = array();

		foreach ( $endpoints as $endpoint => $endpoint_data ) {
			if ( $endpoint_data['time'] >= $cutoff ) {
				$recent[ $endpoint ] = $endpoint_data;
			}
		}

		return $recent;
	}
}
