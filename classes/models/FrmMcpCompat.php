<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Guards everything Formidable does with the vendored MCP adapter.
 *
 * The adapter ships inside this plugin in lib/vendor, but the plugin is not the
 * only thing that can put WP\MCP classes on a site. The standalone MCP Adapter
 * plugin, the Formidable API add-on, and any other plugin that vendors the same
 * package register their own autoloaders, and whichever one is asked for the
 * class first is the copy the whole site then uses. So class_exists() says
 * nothing about which version answered, and the adapter is still pre 1.0, where
 * a minor bump is free to change the API this plugin calls.
 *
 * Every MCP entry point goes through here instead. load_adapter() decides
 * whether the vendored autoloader may run at all, and is_usable() confirms the
 * copy that won provides what this plugin calls, on a site that provides the
 * Abilities API both of them are built on. When any of that says no, MCP stays
 * off and the rest of the plugin keeps working.
 *
 * The checks are all capability checks, never a supported version range. The
 * adapter's VERSION constant is not a reliable statement about its API: v0.3.0
 * shipped with the constant still reading 0.1.0, and upstream only corrected it
 * in v0.4.0. WooCommerce vendors v0.3.0, so on any site running it the adapter
 * introduces itself as 0.1.0 while every class and signature this plugin
 * touches is present. A range keyed on that number would have turned MCP off
 * there for nothing.
 *
 * One wrong constant in one release is a thin reason to distrust the number in
 * general, so that is not the argument. The argument is that the number is a
 * proxy for the question actually being asked, and the real question is small
 * enough to ask directly: does this adapter have the classes and the
 * create_server() signature this plugin calls. Asking that cannot go stale the
 * way a range does, on either end, so the version is kept for notices only.
 *
 * @since x.x
 */
class FrmMcpCompat {

	/**
	 * Adapter class every check is made against.
	 *
	 * @var string
	 */
	const ADAPTER_CLASS = 'WP\\MCP\\Core\\McpAdapter';

	/**
	 * Classes the plugin names as strings and the adapter has to provide.
	 *
	 * The first three are handed to create_server(). The rest back the tool
	 * names passed alongside them: DefaultServerFactory is what registers the
	 * meta abilities, and the three ability classes are where the names the
	 * plugin asks for are declared. None of them are imported anywhere, so a
	 * rename would otherwise surface as a WP_Error at registration time,
	 * nowhere near the cause.
	 *
	 * @var array
	 */
	const REQUIRED_CLASSES = array(
		'WP\\MCP\\Transport\\HttpTransport',
		'WP\\MCP\\Infrastructure\\ErrorHandling\\ErrorLogMcpErrorHandler',
		'WP\\MCP\\Infrastructure\\Observability\\NullMcpObservabilityHandler',
		'WP\\MCP\\Servers\\DefaultServerFactory',
		'WP\\MCP\\Abilities\\DiscoverAbilitiesAbility',
		'WP\\MCP\\Abilities\\GetAbilityInfoAbility',
		'WP\\MCP\\Abilities\\ExecuteAbilityAbility',
	);

	/**
	 * Parameters FrmMcpController passes to create_server(), in order.
	 *
	 * The call is positional and long, so the names are checked rather than
	 * only the count. A signature that was reordered, or that renamed a
	 * parameter into a different meaning, accepts the same number of arguments
	 * while doing something else with them, and that is exactly the break a
	 * version range used to stand in for.
	 *
	 * @var array
	 */
	const CREATE_SERVER_PARAMS = array(
		'server_id',
		'server_route_namespace',
		'server_route',
		'server_name',
		'server_description',
		'server_version',
		'mcp_transports',
		'error_handler',
		'observability_handler',
		'tools',
	);

	/**
	 * Abilities API functions the adapter and this plugin call.
	 *
	 * The adapter is vendored, but the Abilities API it is built on is not: it
	 * comes from WordPress core in 7.0, or from the Abilities API feature plugin
	 * on an older release. Nothing about the adapter says whether that API is
	 * there, so on a site without it every class and signature check passes and
	 * create_server() still dies on an undefined function once it reaches
	 * McpComponentRegistry. The functions are checked directly for the same
	 * reason the rest of this class checks capabilities instead of versions.
	 *
	 * @var array
	 */
	const REQUIRED_ABILITY_FUNCTIONS = array(
		'wp_register_ability',
		'wp_register_ability_category',
		'wp_get_ability',
		'wp_get_abilities',
	);

	/**
	 * Lowest PHP the vendored packages parse on, as a PHP_VERSION_ID.
	 *
	 * The packages use PHP 7.4 syntax, and Composer's generated
	 * lib/vendor/composer/platform_check.php raises E_USER_ERROR below that.
	 * Formidable itself still supports PHP 7.0, so this is the one place the
	 * two floors differ and the gap has to be checked rather than assumed.
	 *
	 * @var int
	 */
	const MIN_PHP_ID = 70400;

	/**
	 * Whether load_adapter() has already run, and what it decided.
	 *
	 * @var bool|null
	 */
	private static $loaded;

	/**
	 * Memoized unsupported_reason() result.
	 *
	 * @var string|null
	 */
	private static $reason;

	/**
	 * Load the vendored adapter and report whether it can be used.
	 *
	 * Call this once MCP is known to be wanted, never at plugin load time. The
	 * autoloader is only required here so a site can switch the whole adapter
	 * off through the MCP setting, which is the one lever a site owner has when
	 * the vendored code is the thing causing trouble.
	 *
	 * @since x.x
	 *
	 * @return bool True when the adapter is loaded and looks compatible.
	 */
	public static function load_adapter() {
		if ( null !== self::$loaded ) {
			return self::$loaded;
		}

		self::require_autoloader();

		// Anything that asked before the autoloader ran was told there is no
		// adapter, and that answer is memoized. Drop it so the checks below see
		// the classes that just became available.
		self::$reason = null;
		self::$loaded = self::is_usable();

		return self::$loaded;
	}

	/**
	 * Require the vendored Composer autoloader when it is safe to do so.
	 *
	 * Three things are checked first. Another copy of the adapter that is
	 * already loaded wins whatever this does, so the vendored autoloader is
	 * left out of the way rather than added as a second source for the same
	 * classes. Below PHP 7.4 the generated platform check turns every request
	 * into a fatal error, so it is never reached. A missing file means the
	 * plugin was built without lib/vendor, which should turn MCP off instead of
	 * taking the site down.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function require_autoloader() {
		if ( class_exists( self::ADAPTER_CLASS ) ) {
			return;
		}

		if ( PHP_VERSION_ID < self::MIN_PHP_ID ) {
			return;
		}

		// Without the Abilities API the adapter cannot do anything for this
		// plugin, so it is left out of the request rather than loaded to fail
		// later.
		if ( array() !== self::missing_ability_functions() ) {
			return;
		}

		$autoloader = FrmAppHelper::plugin_path() . '/lib/vendor/autoload.php';

		if ( ! file_exists( $autoloader ) ) {
			return;
		}

		require_once $autoloader;
	}

	/**
	 * Check that the loaded adapter is one this plugin can drive.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function is_usable() {
		return '' === self::unsupported_reason();
	}

	/**
	 * Explain why the adapter cannot be used, for a notice or a log line.
	 *
	 * @since x.x
	 *
	 * @return string Empty string when the adapter is usable.
	 */
	public static function unsupported_reason() {
		if ( null === self::$reason ) {
			self::$reason = self::detect_unsupported_reason();
		}

		return self::$reason;
	}

	/**
	 * Work out whether anything about the loaded adapter blocks MCP.
	 *
	 * @since x.x
	 *
	 * @return string Empty string when nothing blocks it.
	 */
	private static function detect_unsupported_reason() {
		if ( PHP_VERSION_ID < self::MIN_PHP_ID ) {
			return sprintf(
				/* translators: %s: minimum PHP version, such as 7.4 */
				__( 'The Formidable MCP server needs PHP %s or newer.', 'formidable' ),
				'7.4'
			);
		}

		if ( array() !== self::missing_ability_functions() ) {
			return __( 'The WordPress Abilities API the MCP server is built on is not available on this site. It is part of WordPress 7.0 and newer.', 'formidable' );
		}

		if ( ! class_exists( self::ADAPTER_CLASS ) ) {
			return __( 'The MCP adapter could not be loaded.', 'formidable' );
		}

		if ( ! method_exists( self::ADAPTER_CLASS, 'instance' ) ) {
			return __( 'The MCP adapter on this site cannot be booted, because it has no instance() method.', 'formidable' );
		}

		$missing = self::missing_classes();

		if ( array() !== $missing ) {
			return sprintf(
				/* translators: %s: comma separated list of PHP class names */
				__( 'The MCP adapter on this site is missing classes the Formidable MCP server needs: %s.', 'formidable' ),
				implode( ', ', $missing )
			);
		}

		return self::create_server_mismatch();
	}

	/**
	 * List the functions in REQUIRED_ABILITY_FUNCTIONS this site does not have.
	 *
	 * @since x.x
	 *
	 * @return array Function names that are expected but absent.
	 */
	private static function missing_ability_functions() {
		$missing = array();

		foreach ( self::REQUIRED_ABILITY_FUNCTIONS as $function_name ) {
			if ( ! function_exists( $function_name ) ) {
				$missing[] = $function_name;
			}
		}

		return $missing;
	}

	/**
	 * List the classes in REQUIRED_CLASSES that the loaded adapter does not have.
	 *
	 * @since x.x
	 *
	 * @return array Class names that are expected but absent.
	 */
	private static function missing_classes() {
		$missing = array();

		foreach ( self::REQUIRED_CLASSES as $class_name ) {
			if ( ! class_exists( $class_name ) ) {
				$missing[] = $class_name;
			}
		}

		return $missing;
	}

	/**
	 * Check that create_server() still takes the arguments the plugin passes.
	 *
	 * The call is positional and long, so a reordered or trimmed signature
	 * would raise an ArgumentCountError or quietly register the server with the
	 * wrong values. Both the arity and the parameter names are checked, since
	 * this is what stands in for the version range the plugin used to gate on.
	 *
	 * @since x.x
	 *
	 * @return string Empty string when the signature accepts the call.
	 */
	private static function create_server_mismatch() {
		if ( ! method_exists( self::ADAPTER_CLASS, 'create_server' ) ) {
			return __( 'The MCP adapter on this site has no create_server() method.', 'formidable' );
		}

		$method = new ReflectionMethod( self::ADAPTER_CLASS, 'create_server' );
		$passed = count( self::CREATE_SERVER_PARAMS );

		if ( $method->getNumberOfParameters() < $passed || $method->getNumberOfRequiredParameters() > $passed ) {
			return __( 'The create_server() method in the MCP adapter on this site does not take the arguments the Formidable MCP server passes.', 'formidable' );
		}

		// The declared names are collected first, then walked from the expected
		// list rather than the other way round. Reflection hands back an
		// unbounded position, and comparing it against the count is not enough
		// for a static analyzer to accept it as an offset into a fixed list, so
		// the loop is driven by the list whose keys are known instead.
		$declared = array();

		foreach ( $method->getParameters() as $parameter ) {
			$declared[] = $parameter->getName();
		}

		foreach ( self::CREATE_SERVER_PARAMS as $position => $expected_name ) {
			$declared_name = $declared[ $position ] ?? '';

			if ( $expected_name !== $declared_name ) {
				return sprintf(
					/* translators: 1: parameter name the Formidable MCP server expects, 2: parameter name the adapter declares */
					__(
					/* translators: 1: parameter name the Formidable MCP server expects, 2: parameter name the adapter declares */
						'The MCP adapter on this site declares create_server() with its arguments in a different order. It declares %2$s where %1$s was expected.',
						'formidable'
					),
					$expected_name,
					$declared_name
				);
			}
		}

		return '';
	}

	/**
	 * Get the version of the adapter that is loaded.
	 *
	 * For notices and support only, never for a compatibility decision. This is
	 * whatever the loaded copy declares, and v0.3.0 declares 0.1.0, so on a site
	 * running WooCommerce's copy it names an older release than the code around
	 * it. To identify a copy for certain, read the wordpress/mcp-adapter entry
	 * in the composer/installed.json of the vendor tree loaded_from() points into.
	 *
	 * @since x.x
	 *
	 * @return string Empty string when no adapter is loaded, or it has no version constant.
	 */
	public static function loaded_version() {
		if ( ! class_exists( self::ADAPTER_CLASS ) ) {
			return '';
		}

		$constant = self::ADAPTER_CLASS . '::VERSION';

		return defined( $constant ) ? constant( $constant ) : '';
	}

	/**
	 * Get the file the loaded adapter class came from.
	 *
	 * @since x.x
	 *
	 * @return string Empty string when no adapter is loaded, or the file is unknown.
	 */
	public static function loaded_from() {
		if ( ! class_exists( self::ADAPTER_CLASS ) ) {
			return '';
		}

		/** @var class-string $adapter_class */
		$adapter_class = self::ADAPTER_CLASS;
		$reflection    = new ReflectionClass( $adapter_class );
		$file          = $reflection->getFileName();

		return is_string( $file ) ? wp_normalize_path( $file ) : '';
	}

	/**
	 * Check whether the loaded adapter is the copy that ships in this plugin.
	 *
	 * A copy from somewhere else is the normal case on a site with more than one
	 * MCP plugin, and is not a problem by itself: whichever copy answers first
	 * is the one the whole site uses, and the capability checks cover what this
	 * plugin needs from it. It is only worth naming in a notice, where it
	 * explains why a working adapter sits in lib/vendor while a different one
	 * is running.
	 *
	 * @since x.x
	 *
	 * @return bool
	 */
	public static function is_bundled_copy() {
		$file = self::loaded_from();

		if ( '' === $file ) {
			return false;
		}

		$bundled = wp_normalize_path( FrmAppHelper::plugin_path() . '/lib/vendor/wordpress/mcp-adapter/' );

		return str_starts_with( $file, $bundled );
	}

	/**
	 * Name the plugin folder the loaded adapter came from.
	 *
	 * The path alone already appears in the notice, but the folder is what an
	 * administrator can act on, since it is what they would deactivate or
	 * update to change which copy wins.
	 *
	 * @since x.x
	 *
	 * @return string Empty string when the adapter is not inside a plugin.
	 */
	public static function loaded_by() {
		$file = self::loaded_from();

		if ( '' === $file ) {
			return '';
		}

		// Both paths go through realpath first. WP_PLUGIN_DIR is built from
		// ABSPATH, which keeps whatever shape the entry point gave it, so it can
		// carry a './' segment that a plain string comparison never matches.
		$plugins = realpath( WP_PLUGIN_DIR );
		$real    = realpath( $file );

		if ( false === $plugins || false === $real ) {
			return '';
		}

		$plugins = wp_normalize_path( $plugins ) . '/';
		$real    = wp_normalize_path( $real );

		if ( ! str_starts_with( $real, $plugins ) ) {
			return '';
		}

		$folder = strtok( substr( $real, strlen( $plugins ) ), '/' );

		return false === $folder ? '' : $folder;
	}

	/**
	 * Forget the memoized results.
	 *
	 * Only needed by tests, which move the adapter in and out of the checks
	 * within one process.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function reset() {
		self::$loaded = null;
		self::$reason = null;
	}
}
