<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Tracks MCP connection activity per user.
 *
 * Every request to the Formidable MCP server is recorded against the
 * authenticated WordPress user, keeping the time of the last request, a
 * short list of the endpoints (abilities, tools, resources, prompts) that were
 * called most recently, and the clients (Claude Code, Codex, and so on) that
 * made them. The summary is shown on the MCP global settings page.
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
	 * Maximum number of clients remembered per user.
	 *
	 * @var int
	 */
	const MAX_CLIENTS = 5;

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
		$client   = self::get_client( $body, $request, $user_id );

		self::log_request( $user_id, $endpoint, $client );

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
		 * @param string $client   Name the client sent in its initialize handshake, like claude-code, or an empty string when it is unknown.
		 */
		do_action( 'frm_mcp_request', $body, $endpoint, $client );

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
				$ability_name = $params['arguments']['ability_name'];

				// The Formidable MCP server reaches every ability registered on
				// the site, WooCommerce's and any other plugin's included, so an
				// ability name arriving here is not necessarily one of ours.
				// This table is Formidable's own activity log, so a name we do
				// not own is recorded as a protocol request: the connection
				// still counts, the endpoint is not listed.
				return self::is_formidable_ability( $ability_name ) ? $ability_name : '';
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
	 * Get the name of the client that made an MCP request.
	 *
	 * Clients name themselves only once, in the clientInfo of the initialize
	 * handshake. The MCP adapter keeps those parameters with the session it
	 * creates, so every later request is matched to its client through the
	 * Mcp-Session-Id header it carries.
	 *
	 * @since x.x
	 *
	 * @param array                         $body    Decoded JSON-RPC request body.
	 * @param WP_REST_Request<array<mixed>> $request Request used to generate the response.
	 * @param int                           $user_id ID of the connecting user.
	 *
	 * @return string The client name, like claude-code or codex-mcp-client, or an empty string when it is unknown.
	 */
	private static function get_client( $body, $request, $user_id ) {
		if ( isset( $body['method'] ) && 'initialize' === $body['method'] ) {
			$params = isset( $body['params'] ) && is_array( $body['params'] ) ? $body['params'] : array();
			return self::get_client_name( $params );
		}

		$session_id = $request->get_header( 'mcp-session-id' );

		if ( ! $session_id || ! class_exists( 'WP\MCP\Transport\Infrastructure\SessionManager' ) ) {
			return '';
		}

		$session = WP\MCP\Transport\Infrastructure\SessionManager::get_session( $user_id, $session_id );

		if ( ! is_array( $session ) || ! isset( $session['client_params'] ) || ! is_array( $session['client_params'] ) ) {
			return '';
		}

		return self::get_client_name( $session['client_params'] );
	}

	/**
	 * Get the client name from the parameters of an initialize handshake.
	 *
	 * @since x.x
	 *
	 * @param array $params Parameters of the initialize request.
	 *
	 * @return string
	 */
	private static function get_client_name( $params ) {
		if ( ! isset( $params['clientInfo']['name'] ) || ! is_string( $params['clientInfo']['name'] ) ) {
			return '';
		}

		return substr( sanitize_text_field( $params['clientInfo']['name'] ), 0, 100 );
	}

	/**
	 * Get the label and icon shown for a client name.
	 *
	 * The names are whatever each client sends, like claude-code or
	 * cursor-vscode, so a name from a known product is shown by the product
	 * name, and any other client is shown by the name it sent.
	 *
	 * @since x.x
	 *
	 * @param string $client Name the client sent in its initialize handshake.
	 *
	 * @return array{label: string, icon: string} The label, or an empty string when the name does not tell which client it was, and the images/mcp-{icon}.svg icon name, or an empty string when there is no icon.
	 */
	public static function get_client_details( $client ) {
		$name = strtolower( $client );

		if ( 'claude-code' === $name ) {
			return array(
				'label' => 'Claude Code',
				'icon'  => 'claude',
			);
		}

		// The skill's frm-mcp helper sends frm-mcp when it cannot tell which
		// assistant runs it, and releases before it could tell always sent
		// claude, whether Claude or Codex ran them. Claude's own apps send other
		// names, like claude-ai, so neither one says which assistant it was. The
		// empty label files them with the requests that named no client at all.
		if ( 'claude' === $name || 'frm-mcp' === $name ) {
			return array(
				'label' => '',
				'icon'  => '',
			);
		}

		// Name fragment => label and icon. Grok Build connects as grok-shell-{server},
		// and there is no Grok icon without a license notice, so it gets none.
		$products = array(
			'claude' => array(
				'label' => 'Claude',
				'icon'  => 'claude',
			),
			'codex'  => array(
				'label' => 'Codex',
				'icon'  => 'codex',
			),
			'cursor' => array(
				'label' => 'Cursor',
				'icon'  => 'cursor',
			),
			'grok'   => array(
				'label' => 'Grok',
				'icon'  => '',
			),
		);

		foreach ( $products as $fragment => $details ) {
			if ( str_contains( $name, $fragment ) ) {
				return $details;
			}
		}

		return array(
			'label' => $client,
			'icon'  => '',
		);
	}

	/**
	 * Check whether an ability name is one Formidable owns.
	 *
	 * A registered ability is judged by the category it registered into, which
	 * is what every Formidable ability carries, add-on abilities included. When
	 * the name is not registered the registry answers instead, so an ability
	 * belonging to an add-on this site does not have is still recognised as
	 * ours rather than being mistaken for another plugin's.
	 *
	 * @since x.x
	 *
	 * @param string $ability_name Full ability name, including its namespace.
	 *
	 * @return bool
	 */
	public static function is_formidable_ability( $ability_name ) {
		if ( '' === $ability_name ) {
			return false;
		}

		if ( function_exists( 'wp_get_ability' ) && wp_has_ability( $ability_name ) ) {
			$ability = wp_get_ability( $ability_name );

			if ( $ability && is_callable( array( $ability, 'get_category' ) ) ) {
				$category = $ability->get_category();

				if ( is_object( $category ) && is_callable( array( $category, 'get_name' ) ) ) {
					$category = $category->get_name();
				}

				return FrmAbilitiesController::CATEGORY === $category;
			}
		}

		return FrmMcpAbilityRegistry::owns( $ability_name );
	}

	/**
	 * Record one MCP request for a user.
	 *
	 * @since x.x
	 *
	 * @param int    $user_id  ID of the connecting user.
	 * @param string $endpoint Endpoint name, or an empty string for protocol requests.
	 * @param string $client   Name of the client that made the request, or an empty string when it is unknown.
	 *
	 * @return void
	 */
	public static function log_request( $user_id, $endpoint = '', $client = '' ) {
		$now      = time();
		$activity = self::get_activity( $user_id );

		$activity['last_request'] = $now;

		if ( '' !== $endpoint ) {
			$endpoint              = substr( sanitize_text_field( $endpoint ), 0, 200 );
			$activity['endpoints'] = self::count_use( $activity['endpoints'], $endpoint, $now, self::MAX_ENDPOINTS );
		}

		if ( '' !== $client ) {
			$client              = substr( sanitize_text_field( $client ), 0, 100 );
			$activity['clients'] = self::log_client_request( $activity['clients'], $client, $endpoint, $now );
		}

		update_user_meta( $user_id, self::META_KEY, $activity );
	}

	/**
	 * Record one request against the client that made it.
	 *
	 * Each client keeps its own endpoint list, so the settings page can show
	 * what each assistant asked for rather than one list for the whole user.
	 *
	 * @since x.x
	 *
	 * @param array<string, array{time: int, count: int, endpoints: array<string, array{time: int, count: int}>}> $clients  Client names mapped to their usage.
	 * @param string                                                                                              $client   Name of the client that made the request.
	 * @param string                                                                                              $endpoint Endpoint name, or an empty string for protocol requests.
	 * @param int                                                                                                 $now      Time of the request.
	 *
	 * @return array<string, array{time: int, count: int, endpoints: array<string, array{time: int, count: int}>}>
	 */
	private static function log_client_request( $clients, $client, $endpoint, $now ) {
		$client_data = $clients[ $client ] ?? array(
			'time'      => $now,
			'count'     => 0,
			'endpoints' => array(),
		);

		$client_data['time'] = $now;
		++$client_data['count'];

		if ( '' !== $endpoint ) {
			$client_data['endpoints'] = self::count_use( $client_data['endpoints'], $endpoint, $now, self::MAX_ENDPOINTS );
		}

		$clients[ $client ] = $client_data;

		return self::cap_items( $clients, self::MAX_CLIENTS );
	}

	/**
	 * Record one use of an endpoint or client, keeping only the most recent ones.
	 *
	 * @since x.x
	 *
	 * @param array<string, array{time: int, count: int}> $items Names mapped to time and count data.
	 * @param string                                      $name  Name of the endpoint or client that was used.
	 * @param int                                         $now   Time of the request.
	 * @param int                                         $max   Maximum number of names to keep.
	 *
	 * @return array<string, array{time: int, count: int}>
	 */
	private static function count_use( $items, $name, $now, $max ) {
		if ( isset( $items[ $name ] ) ) {
			$items[ $name ]['time'] = $now;
			++$items[ $name ]['count'];
		} else {
			$items[ $name ] = array(
				'time'  => $now,
				'count' => 1,
			);
		}

		return self::cap_items( $items, $max );
	}

	/**
	 * Drop the oldest names once the list grows past the limit.
	 *
	 * @since x.x
	 *
	 * @template T of array{time: int, count: int}
	 *
	 * @param array<string, T> $items Names mapped to time and count data.
	 * @param int              $max   Maximum number of names to keep.
	 *
	 * @return array<string, T>
	 */
	private static function cap_items( $items, $max ) {
		$item_count = count( $items );

		while ( $item_count > $max ) {
			$oldest_key  = '';
			$oldest_time = PHP_INT_MAX;

			foreach ( $items as $name => $item_data ) {
				if ( $item_data['time'] >= $oldest_time ) {
					continue;
				}

				$oldest_time = $item_data['time'];
				$oldest_key  = $name;
			}

			unset( $items[ $oldest_key ] );
			--$item_count;
		}

		return $items;
	}

	/**
	 * Get the stored MCP activity for one user.
	 *
	 * @since x.x
	 *
	 * @param int $user_id ID of the user to look up.
	 *
	 * @return array{last_request: int, endpoints: array<string, array{time: int, count: int}>, clients: array<string, array{time: int, count: int, endpoints: array<string, array{time: int, count: int}>}>}
	 */
	private static function get_activity( $user_id ) {
		$activity = get_user_meta( $user_id, self::META_KEY, true );

		if ( ! is_array( $activity ) ) {
			$activity = array();
		}

		return array(
			'last_request' => isset( $activity['last_request'] ) ? max( 0, (int) $activity['last_request'] ) : 0,
			'endpoints'    => self::clean_usage( $activity['endpoints'] ?? array() ),
			// Activity recorded before clients were tracked has no clients key.
			'clients'      => self::clean_clients( $activity['clients'] ?? array() ),
		);
	}

	/**
	 * Clean the stored client usage, including each client's endpoint list.
	 *
	 * @since x.x
	 *
	 * @param mixed $clients Stored client usage.
	 *
	 * @return array<string, array{time: int, count: int, endpoints: array<string, array{time: int, count: int}>}>
	 */
	private static function clean_clients( $clients ) {
		if ( ! is_array( $clients ) ) {
			return array();
		}

		$clean = array();

		foreach ( self::clean_usage( $clients ) as $client => $client_data ) {
			$client_data['endpoints'] = self::clean_usage( $clients[ $client ]['endpoints'] ?? array() );
			$clean[ $client ]         = $client_data;
		}

		return $clean;
	}

	/**
	 * Clean a stored list of names mapped to time and count data.
	 *
	 * @since x.x
	 *
	 * @param mixed $items Stored endpoint or client usage.
	 *
	 * @return array<string, array{time: int, count: int}>
	 */
	private static function clean_usage( $items ) {
		if ( ! is_array( $items ) ) {
			return array();
		}

		$clean = array();

		foreach ( $items as $name => $item_data ) {
			if ( ! is_string( $name ) || ! is_array( $item_data ) ) {
				continue;
			}

			$clean[ $name ] = array(
				'time'  => isset( $item_data['time'] ) ? max( 0, (int) $item_data['time'] ) : 0,
				'count' => isset( $item_data['count'] ) ? max( 0, (int) $item_data['count'] ) : 1,
			);
		}

		return $clean;
	}

	/**
	 * Get the MCP connection summary for every user with recorded activity.
	 *
	 * Each user gets one row per client they connected with. Rows are sorted
	 * by the most recent request first, and each row's endpoints are sorted by
	 * the most recently called first, limited to those called within the last
	 * month.
	 *
	 * @since x.x
	 *
	 * @return array<int, array{user_login: string, display_name: string, client: string, client_icon: string, last_request: int, endpoints: array<string, array{time: int, count: int}>}>
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

			foreach ( self::get_client_rows( $activity ) as $row ) {
				$connections[] = array_merge(
					array(
						'user_login'   => $user->user_login,
						'display_name' => $user->display_name,
					),
					$row
				);
			}
		}//end foreach

		usort( $connections, 'FrmMcpConnection::compare_last_request' );

		return $connections;
	}

	/**
	 * Build one connection row for each client a user connected with.
	 *
	 * Client names that share a label, like two Claude apps, are combined into
	 * one row. A user with no recent client, whose activity was recorded before
	 * clients were tracked or came from clients that did not name themselves,
	 * gets a single row with no client.
	 *
	 * @since x.x
	 *
	 * @param array{last_request: int, endpoints: array<string, array{time: int, count: int}>, clients: array<string, array{time: int, count: int, endpoints: array<string, array{time: int, count: int}>}>} $activity The user's stored MCP activity.
	 *
	 * @return array<int, array{client: string, client_icon: string, last_request: int, endpoints: array<string, array{time: int, count: int}>}>
	 */
	private static function get_client_rows( $activity ) {
		$rows = array();

		foreach ( self::filter_recent_endpoints( $activity['clients'] ) as $client => $client_data ) {
			$details = self::get_client_details( $client );
			$label   = $details['label'];

			if ( ! isset( $rows[ $label ] ) ) {
				$rows[ $label ] = array(
					'client'       => $label,
					'client_icon'  => $details['icon'],
					'last_request' => 0,
					'endpoints'    => array(),
				);
			}

			$rows[ $label ]['last_request'] = max( $rows[ $label ]['last_request'], $client_data['time'] );
			$rows[ $label ]['endpoints']    = self::merge_usage( $rows[ $label ]['endpoints'], $client_data['endpoints'] );
		}

		if ( ! $rows ) {
			$rows[] = array(
				'client'       => '',
				'client_icon'  => '',
				'last_request' => $activity['last_request'],
				'endpoints'    => $activity['endpoints'],
			);
		}

		foreach ( $rows as $key => $row ) {
			$endpoints = self::filter_recent_endpoints( $row['endpoints'] );
			uasort( $endpoints, 'FrmMcpConnection::compare_endpoint_time' );
			$rows[ $key ]['endpoints'] = $endpoints;
		}

		return array_values( $rows );
	}

	/**
	 * Combine two endpoint lists, keeping the latest time and the total count of each.
	 *
	 * @since x.x
	 *
	 * @param array<string, array{time: int, count: int}> $usage      Endpoint names mapped to time and count data.
	 * @param array<string, array{time: int, count: int}> $more_usage Endpoint names mapped to time and count data to add.
	 *
	 * @return array<string, array{time: int, count: int}>
	 */
	private static function merge_usage( $usage, $more_usage ) {
		foreach ( $more_usage as $name => $item_data ) {
			if ( ! isset( $usage[ $name ] ) ) {
				$usage[ $name ] = $item_data;
				continue;
			}

			$usage[ $name ]['time']   = max( $usage[ $name ]['time'], $item_data['time'] );
			$usage[ $name ]['count'] += $item_data['count'];
		}

		return $usage;
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
	 * Keep only the endpoints or clients used within the last month.
	 *
	 * @since x.x
	 *
	 * @template T of array{time: int, count: int}
	 *
	 * @param array<string, T> $endpoints Endpoint or client names mapped to time and count data.
	 *
	 * @return array<string, T>
	 */
	private static function filter_recent_endpoints( $endpoints ) {
		$cutoff = strtotime( '-1 month' );

		foreach ( $endpoints as $endpoint => $endpoint_data ) {
			if ( $endpoint_data['time'] < $cutoff ) {
				unset( $endpoints[ $endpoint ] );
			}
		}

		return $endpoints;
	}
}
