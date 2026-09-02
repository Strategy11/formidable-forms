<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Answers an MCP client that asks for an ability this site does not have.
 *
 * A client works from the ability name, not from the plugin that provides it,
 * so asking for formidable-forms/create-view on a site without Views is an easy
 * mistake to make. All the MCP adapter can say back is that the name was not
 * found, which reads as "that ability does not exist" rather than "install the
 * add-on that has it". Worse, nothing is recorded, so the same call keeps
 * failing with nobody able to say why.
 *
 * This intercepts the two adapter meta abilities that take an ability name, and
 * replaces that answer with one naming the add-on and the next step. What the
 * answer should be comes from FrmMcpAbilityRegistry.
 *
 * The hook point is wp_register_ability_args, which is the only place that runs
 * early enough. Inside the adapter, a tools/call checks permissions before it
 * reaches mcp_adapter_pre_tool_call, and the missing name is already an error by
 * then, so the documented pre-call filter never sees it. Decorating the two
 * callbacks as the abilities are registered gets in front of both.
 *
 * @since x.x
 */
class FrmMcpMissingAbilityController {

	/**
	 * The original callbacks of each decorated ability, keyed by ability name.
	 *
	 * @var array
	 */
	private static $originals = array();

	/**
	 * @since x.x
	 *
	 * @return void
	 */
	public static function load_hooks() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			// The Abilities API is part of WordPress 7.0. Without it there is
			// nothing registering, and nothing to answer for.
			return;
		}

		add_filter( 'wp_register_ability_args', 'FrmMcpMissingAbilityController::decorate_ability', 10, 2 );
	}

	/**
	 * Map each adapter meta ability to the callbacks that stand in front of it.
	 *
	 * Both of these take an ability name from the client, which is what makes
	 * them the two places a name Formidable can explain shows up.
	 *
	 * @since x.x
	 *
	 * @return array Ability names mapped to a permission and an execute callback.
	 */
	private static function wrappers() {
		return array(
			'mcp-adapter/execute-ability'  => array(
				'permission' => 'FrmMcpMissingAbilityController::check_execute_permission',
				'execute'    => 'FrmMcpMissingAbilityController::run_execute',
			),
			'mcp-adapter/get-ability-info' => array(
				'permission' => 'FrmMcpMissingAbilityController::check_info_permission',
				'execute'    => 'FrmMcpMissingAbilityController::run_info',
			),
		);
	}

	/**
	 * Put Formidable's callbacks in front of an adapter meta ability.
	 *
	 * Anything unexpected is left alone. The adapter owns these abilities, and a
	 * shape this does not recognize is a version that has moved on, where the
	 * right thing to do is nothing at all rather than break execution.
	 *
	 * @since x.x
	 * @see filter hook wp_register_ability_args
	 *
	 * @param array  $args Arguments accepted by wp_register_ability().
	 * @param string $name Ability name being registered, with its namespace.
	 *
	 * @return array
	 */
	public static function decorate_ability( $args, $name ) {
		$wrappers = self::wrappers();

		if ( ! is_array( $args ) || ! isset( $wrappers[ $name ] ) ) {
			return $args;
		}

		if ( empty( $args['permission_callback'] ) || empty( $args['execute_callback'] ) ) {
			return $args;
		}

		self::$originals[ $name ] = array(
			'permission' => $args['permission_callback'],
			'execute'    => $args['execute_callback'],
		);

		$args['permission_callback'] = $wrappers[ $name ]['permission'];
		$args['execute_callback']    = $wrappers[ $name ]['execute'];

		return $args;
	}

	/**
	 * Permission callback standing in for mcp-adapter/execute-ability.
	 *
	 * @since x.x
	 *
	 * @param array $input Input parameters, holding ability_name and parameters.
	 *
	 * @return mixed The adapter's answer, or true when only the ability name was wrong.
	 */
	public static function check_execute_permission( $input = array() ) {
		return self::check_permission( 'mcp-adapter/execute-ability', $input );
	}

	/**
	 * Permission callback standing in for mcp-adapter/get-ability-info.
	 *
	 * @since x.x
	 *
	 * @param array $input Input parameters, holding ability_name.
	 *
	 * @return mixed The adapter's answer, or true when only the ability name was wrong.
	 */
	public static function check_info_permission( $input = array() ) {
		return self::check_permission( 'mcp-adapter/get-ability-info', $input );
	}

	/**
	 * Execute callback standing in for mcp-adapter/execute-ability.
	 *
	 * @since x.x
	 *
	 * @param array $input Input parameters, holding ability_name and parameters.
	 *
	 * @return mixed|WP_Error
	 */
	public static function run_execute( $input = array() ) {
		$error = self::error_for( $input );
		return $error ? $error : self::call_original( 'mcp-adapter/execute-ability', 'execute', $input );
	}

	/**
	 * Execute callback standing in for mcp-adapter/get-ability-info.
	 *
	 * @since x.x
	 *
	 * @param array $input Input parameters, holding ability_name.
	 *
	 * @return mixed|WP_Error
	 */
	public static function run_info( $input = array() ) {
		$error = self::error_for( $input );
		return $error ? $error : self::call_original( 'mcp-adapter/get-ability-info', 'execute', $input );
	}

	/**
	 * Let a call through when the only thing wrong with it is the ability name.
	 *
	 * The adapter answers ability_not_found only after it has checked that the
	 * caller is logged in and holds the capability its execution layer needs, so
	 * that code means the request was allowed and the name was not there.
	 * Returning true hands the call to the execute callback, which is where a
	 * message can be returned. Every other answer is passed through untouched,
	 * so nothing here loosens a permission check.
	 *
	 * @since x.x
	 *
	 * @param string $name  Name of the meta ability being called.
	 * @param array  $input Input parameters, holding the requested ability_name.
	 *
	 * @return mixed Whatever the adapter's own callback answered, or true when only the ability name was wrong.
	 */
	private static function check_permission( $name, $input ) {
		$result = self::call_original( $name, 'permission', $input );

		if ( ! is_wp_error( $result ) || 'ability_not_found' !== $result->get_error_code() ) {
			return $result;
		}

		return self::should_answer( $input ) ? true : $result;
	}

	/**
	 * Hand the call back to the adapter's own callback.
	 *
	 * @since x.x
	 *
	 * @param string $name  Name of the meta ability being called.
	 * @param string $which Either permission or execute.
	 * @param array  $input Input parameters passed to the ability.
	 *
	 * @return mixed|WP_Error
	 */
	private static function call_original( $name, $which, $input ) {
		$original = self::$originals[ $name ][ $which ] ?? null;

		if ( ! $original || ! is_callable( $original ) ) {
			// Only reachable if something unhooked the adapter's callback after
			// registration. Nothing here can answer for it.
			return new WP_Error(
				'frm_mcp_ability_unusable',
				__( 'The MCP server could not run that request. Reload the connection and try again.', 'formidable' ),
				array( 'status' => 500 )
			);
		}

		return call_user_func( $original, $input );
	}

	/**
	 * Check whether Formidable should be answering for this request at all.
	 *
	 * @since x.x
	 *
	 * @param array $input Input parameters, holding the requested ability_name.
	 *
	 * @return bool
	 */
	private static function should_answer( $input ) {
		$name = self::requested_name( $input );

		if ( '' === $name || wp_has_ability( $name ) ) {
			return false;
		}

		// The Formidable MCP server reaches every ability on the site, so an
		// unregistered name is as likely to be another plugin's as ours. Only
		// Formidable's own namespace is Formidable's to explain: answering for
		// a WooCommerce name would put a frm_ error code and a Formidable log
		// entry on a miss that has nothing to do with Formidable. Anything else
		// falls through to the adapter's own not-found answer.
		if ( ! FrmMcpAbilityRegistry::owns( $name ) ) {
			return false;
		}

		// With the Formidable abilities turned off, an add-on install is not the
		// answer to anything and the adapter's own message is the honest one.
		return FrmAbilitiesController::is_active();
	}

	/**
	 * Build the answer for a request naming an ability this site cannot reach.
	 *
	 * @since x.x
	 *
	 * @param array $input Input parameters, holding the requested ability_name.
	 *
	 * @return WP_Error|false False when the request is one to leave alone.
	 */
	private static function error_for( $input ) {
		if ( ! self::should_answer( $input ) ) {
			return false;
		}

		$name   = self::requested_name( $input );
		$status = FrmMcpAbilityRegistry::status( $name );

		self::log( $name, $status );

		return FrmMcpAbilityRegistry::error( $name, $status );
	}

	/**
	 * Read the ability name the client asked for.
	 *
	 * @since x.x
	 *
	 * @param array $input Input parameters passed to the meta ability.
	 *
	 * @return string Empty when the input carries no usable name.
	 */
	private static function requested_name( $input ) {
		if ( ! is_array( $input ) || empty( $input['ability_name'] ) || ! is_string( $input['ability_name'] ) ) {
			return '';
		}

		return $input['ability_name'];
	}

	/**
	 * Record that an ability was asked for and could not be reached.
	 *
	 * Written through the same logger the rest of Formidable uses, so a site
	 * with the Formidable Logs add-on collects these alongside everything else.
	 * Without that add-on, the message only reaches the PHP error log while
	 * WP_DEBUG is on: an assistant retrying a call it cannot make is not a
	 * reason to fill a production log.
	 *
	 * @since x.x
	 *
	 * @param string $ability_name Ability name the client asked for.
	 * @param string $status       Status from FrmMcpAbilityRegistry::status().
	 *
	 * @return void
	 */
	private static function log( $ability_name, $status ) {
		if ( self::logged_recently( $ability_name, $status ) ) {
			return;
		}

		FrmTransLiteLog::log_message(
			'Formidable MCP ability unavailable',
			$ability_name . ' was requested over MCP and is not registered on this site (' . $status . ').',
			defined( 'WP_DEBUG' ) && WP_DEBUG
		);
	}

	/**
	 * Check whether this ability and status were already logged, and claim the window if not.
	 *
	 * A client that cannot make a call tends to make it again immediately, and
	 * every attempt has the same cause, so one line per ability per window says
	 * everything the repeats would.
	 *
	 * @since x.x
	 *
	 * @param string $ability_name Ability name the client asked for.
	 * @param string $status       Status from FrmMcpAbilityRegistry::status().
	 *
	 * @return bool
	 */
	private static function logged_recently( $ability_name, $status ) {
		$key = 'frm_mcp_missing_' . md5( $ability_name . '|' . $status );

		if ( get_transient( $key ) ) {
			return true;
		}

		set_transient( $key, time(), 15 * MINUTE_IN_SECONDS );

		return false;
	}
}
