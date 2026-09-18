<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Boots the MCP adapter and registers the Formidable MCP server.
 *
 * The MCP server and the Formidable abilities used to live entirely in the API
 * add-on. They are in Formidable now so a site can connect an AI assistant
 * without the add-on, and so each plugin can own the abilities for its own
 * features: Pro owns applications and entry editing, Views owns views.
 *
 * That leaves two copies of the same feature on a site running an add-on that
 * predates the move, and the Abilities API hard fails on a duplicate name, so
 * exactly one of them may register. The rule is that the older copy wins:
 * an add-on that does not know how to defer keeps everything, and Formidable
 * stays out of the way entirely until the add-on is updated. See
 * FrmAbilitiesController::another_plugin_owns_abilities().
 *
 * @since x.x
 */
class FrmMcpController {

	/**
	 * Server id, route, and route namespace of the Formidable MCP server.
	 *
	 * These match what the API add-on has always registered, so an assistant
	 * that was connected before the move keeps working against the same URL
	 * after it.
	 *
	 * @var string
	 */
	const SERVER_ID = 'formidable-mcp';

	/**
	 * @var string
	 */
	const ROUTE_NAMESPACE = 'mcp';

	/**
	 * Memoized is_enabled() result.
	 *
	 * @var bool|null
	 */
	private static $enabled;

	/**
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_hooks() {
		if ( self::api_addon_owns_mcp() ) {
			// An API add-on that predates the move runs its own adapter, server,
			// and abilities. Nothing here may register alongside it.
			return;
		}

		// The adapter is not loaded yet: it is loaded on init, once the MCP
		// setting is readable. Everything below only touches Formidable's own
		// route, so it is safe to hook either way and each callback answers for
		// itself.
		add_action( 'init', 'FrmMcpController::maybe_boot_mcp_adapter', 15 );

		// rest_pre_dispatch runs before the route is matched, which is the only
		// place left to answer once the MCP server has not been registered.
		add_filter( 'rest_pre_dispatch', 'FrmMcpController::explain_disabled_mcp', 10, 3 );

		FrmMcpConnection::load_hooks();

		// Answers a client that asks for an ability belonging to an add-on this
		// site does not have. Hooked here rather than alongside the abilities
		// because it decorates the adapter's own meta abilities, not Formidable's.
		FrmMcpMissingAbilityController::load_hooks();
	}

	/**
	 * Check whether an API add-on that predates the move still owns MCP.
	 *
	 * The add-on gained per domain deference at the same time Formidable gained
	 * the abilities, and announces it with FrmAPIAbilitiesController::defers_to().
	 * An older copy has the class but not the method, and registers the whole
	 * surface itself.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function api_addon_owns_mcp() {
		if ( ! class_exists( 'FrmAPIAbilitiesController' ) ) {
			return false;
		}

		return ! method_exists( 'FrmAPIAbilitiesController', 'defers_to' );
	}

	/**
	 * Check whether the MCP server and the Formidable abilities are turned on.
	 *
	 * Three answers, in order. A saved Formidable setting is the answer. With no
	 * Formidable setting saved yet, the API add-on's own toggle is inherited, so
	 * a site that already turned MCP on there does not silently lose it on
	 * update. With neither, MCP is off: it stays opt in for everyone else.
	 *
	 * Only call this on init or later. The settings are readable earlier, but
	 * the add-on class this falls back to is not.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function is_enabled() {
		if ( null !== self::$enabled ) {
			return self::$enabled;
		}

		$settings = FrmAppHelper::get_settings();

		if ( isset( $settings->mcp ) && null !== $settings->mcp && '' !== $settings->mcp ) {
			self::$enabled = (bool) $settings->mcp;
			return self::$enabled;
		}

		$inherited = self::api_addon_setting();

		if ( null !== $inherited ) {
			self::$enabled = $inherited;
			return self::$enabled;
		}

		/**
		 * Whether the MCP server is on for a site that has never saved the setting.
		 *
		 * Only the default is filtered. A site owner who has been to the MCP
		 * settings page has an explicit value stored, and that always wins over
		 * this, so turning MCP off stays off no matter what a filter says. This
		 * is for deciding what a site starts out as: a host that has already
		 * decided its customers should arrive with MCP ready can return true
		 * from a mu-plugin and skip the settings page entirely.
		 *
		 * Because the answer is memoized for the request, add this early
		 * (mu-plugins or plugins_loaded), not on a later hook.
		 *
		 * @since x.x
		 *
		 * @param bool $enabled Whether MCP defaults to on. Default false.
		 */
		self::$enabled = (bool) apply_filters( 'frm_mcp_enabled_by_default', false );

		return self::$enabled;
	}

	/**
	 * Read the API add-on's MCP toggle, for a site that has not saved Formidable's yet.
	 *
	 * Public because the settings section asks the same question, to say on
	 * screen that the toggle is showing an inherited value.
	 *
	 * The option is read directly rather than through the add-on's own settings
	 * class. That class extends FrmSettings and fills in its own defaults, so
	 * asking it can never answer "nothing was saved", which is the whole
	 * question here. A site that never opened the add-on's settings page has no
	 * stored value and inherits nothing.
	 *
	 * @since x.x
	 *
	 * @return bool|null Null when the add-on has no stored MCP value to inherit.
	 */
	public static function api_addon_setting() {
		$options = get_option( 'frm_api_options' );

		if ( is_object( $options ) ) {
			// The option holds a serialized FrmAPISettings. The whole point of
			// this method is the case where the add-on is not running, and then
			// that unserializes to __PHP_Incomplete_Class, where reading a
			// property raises a warning and answers nothing. Casting reaches the
			// same values without needing the class.
			$options = (array) $options;
		}

		if ( ! is_array( $options ) || ! isset( $options['mcp'] ) ) {
			return null;
		}

		return (bool) $options['mcp'];
	}

	/**
	 * Forget the memoized setting.
	 *
	 * Needed after the global settings are saved, and by tests, which move the
	 * setting within one process.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function reset() {
		self::$enabled = null;
	}

	/**
	 * Boot the MCP Adapter unless the MCP setting is off.
	 *
	 * Booting the adapter also creates its default MCP server, so the whole
	 * boot is skipped when MCP is disabled, not just the server registration.
	 * The setting is checked before the adapter is even loaded, so turning MCP
	 * off keeps the vendored code out of the request entirely. That is what a
	 * site owner can reach for if the adapter is ever the thing breaking their
	 * site.
	 *
	 * This runs on init because the MCP setting cannot be read any earlier, and
	 * the adapter only needs to exist by rest_api_init.
	 *
	 * @since x.x
	 * @see action hook init
	 *
	 * @return void
	 */
	public static function maybe_boot_mcp_adapter() {
		if ( ! self::is_enabled() ) {
			return;
		}

		if ( ! FrmMcpCompat::load_adapter() ) {
			add_action( 'admin_notices', 'FrmMcpController::mcp_unsupported_notice' );
			return;
		}

		add_action( 'mcp_adapter_init', 'FrmMcpController::register_mcp_server' );

		WP\MCP\Core\McpAdapter::instance();
	}

	/**
	 * Register a dedicated Formidable MCP server at /wp-json/mcp/formidable-mcp.
	 *
	 * This intentionally does not touch the adapter's default server, so other
	 * plugins that rely on it (or register their own servers) are unaffected.
	 * The tools are the adapter's discovery and execution meta abilities, which
	 * resolve every ability flagged mcp.public, including all the
	 * formidable-forms abilities registered by Formidable, Pro, and Views.
	 * Those meta abilities are registered by the adapter while creating its
	 * default server, so this server expects the
	 * mcp_adapter_create_default_server filter to stay enabled.
	 *
	 * @since x.x
	 * @see action hook mcp_adapter_init
	 *
	 * @param WP\MCP\Core\McpAdapter $adapter The MCP Adapter instance.
	 *
	 * @return void
	 */
	public static function register_mcp_server( $adapter ) {
		if ( ! self::is_enabled() ) {
			return;
		}

		// maybe_boot_mcp_adapter() only hooks this once the adapter passed every
		// check, so this is here for an adapter booted by something else, which
		// fires mcp_adapter_init for everyone hooked to it.
		if ( ! FrmMcpCompat::is_usable() ) {
			return;
		}

		// FrmMcpCompat has already checked that this signature accepts the call,
		// so a throw here means an adapter that answered every capability check
		// behaves like something else. Catching it keeps a broken dependency
		// from taking down every REST request on the site.
		try {
			$result = $adapter->create_server(
				self::SERVER_ID,
				self::ROUTE_NAMESPACE,
				self::SERVER_ID,
				'Formidable MCP Server',
				'MCP server for Formidable Forms abilities discovery and execution.',
				'v' . FrmAppHelper::plugin_version(),
				array( 'WP\\MCP\\Transport\\HttpTransport' ),
				'WP\\MCP\\Infrastructure\\ErrorHandling\\ErrorLogMcpErrorHandler',
				'WP\\MCP\\Infrastructure\\Observability\\NullMcpObservabilityHandler',
				array(
					'mcp-adapter/discover-abilities',
					'mcp-adapter/get-ability-info',
					'mcp-adapter/execute-ability',
				)
			);
		} catch ( Throwable $e ) {
			_doing_it_wrong( __METHOD__, esc_html( 'The MCP adapter rejected the Formidable MCP server: ' . $e->getMessage() ), esc_html( FrmAppHelper::plugin_version() ) );
			return;
		}//end try

		if ( is_wp_error( $result ) ) {
			_doing_it_wrong( __METHOD__, esc_html( 'Failed to register the Formidable MCP server: ' . $result->get_error_message() ), esc_html( FrmAppHelper::plugin_version() ) );
		}
	}

	/**
	 * Tell an administrator why the MCP server is not running.
	 *
	 * Only shown when MCP is turned on and the adapter still cannot be used, so
	 * the setting reads as enabled while the route answers nothing. Everything
	 * else on the site keeps working, which is exactly why this would otherwise
	 * go unnoticed.
	 *
	 * @since x.x
	 * @see action hook admin_notices
	 *
	 * @return void
	 */
	public static function mcp_unsupported_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$reason = FrmMcpCompat::unsupported_reason();

		if ( '' === $reason ) {
			return;
		}

		echo '<div class="notice notice-warning"><p>';
		echo esc_html__( 'The Formidable MCP server is turned on but not running.', 'formidable' ) . ' ' . esc_html( $reason );

		$loaded_from = FrmMcpCompat::loaded_from();

		if ( '' !== $loaded_from && ! FrmMcpCompat::is_bundled_copy() ) {
			$loaded_by = FrmMcpCompat::loaded_by();

			echo ' ';

			if ( '' === $loaded_by ) {
				printf(
					/* translators: %s: absolute path to a PHP file */
					esc_html__( 'The adapter in use was loaded by another plugin, from %s.', 'formidable' ),
					'<code>' . esc_html( $loaded_from ) . '</code>'
				);
			} else {
				printf(
					/* translators: 1: plugin folder name, 2: absolute path to a PHP file */
					esc_html__( 'The adapter in use was loaded by the %1$s plugin, from %2$s.', 'formidable' ),
					'<code>' . esc_html( $loaded_by ) . '</code>',
					'<code>' . esc_html( $loaded_from ) . '</code>'
				);
			}
		}

		echo '</p></div>';
	}

	/**
	 * Tell a client the MCP setting is off instead of letting it read a bare 404.
	 *
	 * Turning MCP off skips the whole adapter boot, so the server route is never
	 * registered and there is nothing for rest_request_before_callbacks to catch.
	 * This runs on rest_pre_dispatch, which fires before the route is matched, so
	 * it can still answer for a route that does not exist.
	 *
	 * Two cases are answered. The Formidable server route is always safe, since
	 * it is ours whoever else is present. The namespace root is only answered
	 * while nothing else serves under it, so another plugin's MCP server is never
	 * shadowed by a Formidable setting.
	 *
	 * @since x.x
	 * @see filter hook rest_pre_dispatch
	 *
	 * @param mixed                         $result  Result to send to the client, or null to continue dispatching.
	 * @param WP_REST_Server                $server  Server instance handling the request.
	 * @param WP_REST_Request<array<mixed>> $request Request being dispatched.
	 *
	 * @return mixed
	 */
	public static function explain_disabled_mcp( $result, $server, $request ) {
		if ( null !== $result || ! $request instanceof WP_REST_Request || self::is_enabled() ) {
			return $result;
		}

		$route     = untrailingslashit( $request->get_route() );
		$is_ours   = FrmMcpConnection::MCP_ROUTE === $route;
		$is_unused = '/mcp' === $route && self::nothing_else_serves_mcp( $server );

		if ( ! $is_ours && ! $is_unused ) {
			return $result;
		}

		return new WP_Error(
			'frm_mcp_disabled',
			__( 'The Formidable MCP server is turned off. Turn on MCP Server in Formidable > Global Settings > MCP to use it.', 'formidable' ),
			array( 'status' => 403 )
		);
	}

	/**
	 * Check that no MCP server is registered under the mcp namespace.
	 *
	 * The namespace belongs to the MCP adapter rather than to Formidable, and
	 * other plugins can register their own servers on it, so the namespace root
	 * is only claimed while it is otherwise empty.
	 *
	 * @since x.x
	 *
	 * @param WP_REST_Server $server Server instance handling the request.
	 *
	 * @return bool
	 */
	private static function nothing_else_serves_mcp( $server ) {
		if ( ! is_object( $server ) || ! is_callable( array( $server, 'get_routes' ) ) ) {
			return false;
		}

		foreach ( array_keys( $server->get_routes() ) as $route ) {
			if ( '/mcp' !== $route && str_starts_with( $route, '/mcp/' ) ) {
				return false;
			}
		}

		return true;
	}
}
