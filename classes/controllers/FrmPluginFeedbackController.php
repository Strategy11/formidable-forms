<?php
/**
 * Plugin Feedback Controller (Lite).
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Collects an NPS score and feedback from Lite users after install.
 *
 * Independent of FrmProPluginFeedbackController (Pro): the two plugins run their
 * own eligibility checks and never inherit from each other. They intentionally
 * share the same option key so a submission in either one suppresses the other's
 * prompt for the same year if the site upgrades or downgrades mid-year.
 *
 * @since 6.26.1
 */
class FrmPluginFeedbackController {

	/**
	 * Shared with Pro so a Lite submission suppresses Pro's prompt after upgrade.
	 *
	 * @var string
	 */
	const PLUGIN_FEEDBACK_OPTION_KEY = 'frm-plugin-feedback';

	/**
	 * Marks a submission as originating from Lite when read from the shared option.
	 *
	 * @var string
	 */
	const SOURCE = 'lite';

	/**
	 * @var int
	 */
	const INSTALL_AGE_THRESHOLD_DAYS = 90;

	/**
	 * Minimum gap enforced between prompts, regardless of calendar year, so a
	 * late-year submission can't be followed by another prompt days later.
	 *
	 * @var int
	 */
	const MIN_MONTHS_BETWEEN_PROMPTS = 3;

	/**
	 * @var array
	 */
	protected static $plugin_feedback;

	/**
	 * @var int
	 */
	protected static $current_year;

	/**
	 * @return void
	 */
	public static function load_admin_hooks() {
		if ( ! self::should_show_plugin_feedback() ) {
			return;
		}

		$class = self::class;

		add_filter( 'frm_should_show_floating_links', '__return_false' );
		add_action( 'admin_enqueue_scripts', array( $class, 'enqueue_assets' ) );
		add_action( 'admin_footer', array( $class, 'show_plugin_feedback' ), 1 );
	}

	/**
	 * @return bool
	 */
	protected static function should_show_plugin_feedback() {
		return self::passes_common_gates() && self::passes_product_specific_gates();
	}

	/**
	 * @return bool
	 */
	protected static function passes_common_gates() {
		if ( ! current_user_can( 'frm_change_settings' ) ) {
			return false;
		}

		if ( self::is_local_environment() ) {
			return false;
		}

		if ( self::pro_is_blocking() ) {
			return false;
		}

		return is_network_admin() ? false : FrmAppHelper::is_formidable_admin();
	}

	/**
	 * @return bool
	 */
	protected static function passes_product_specific_gates() {
		return self::has_reached_install_age_threshold()
		&& self::has_reached_january_25th()
		&& ! self::has_recently_been_prompted();
	}

	/**
	 * Matches Pro: the survey only opens for the year starting January 25th.
	 *
	 * @return bool
	 */
	protected static function has_reached_january_25th() {
		return wp_date( 'm-d' ) >= '01-25';
	}

	/**
	 * Enforces a minimum gap since the last prompt (submitted or dismissed),
	 * independent of the January 25th window, so a late-year prompt can't
	 * be immediately followed by another one when the year rolls over.
	 *
	 * @return bool
	 */
	protected static function has_recently_been_prompted() {
		$plugin_feedback = self::get_plugin_feedback();
		$last_prompted   = isset( $plugin_feedback['last_prompted'] ) ? (int) $plugin_feedback['last_prompted'] : 0;

		if ( ! $last_prompted ) {
			return false;
		}

		return time() - $last_prompted < self::MIN_MONTHS_BETWEEN_PROMPTS * MONTH_IN_SECONDS;
	}

	/**
	 * Lite goes fully silent once Pro is active; Pro runs its own,
	 * license-based feedback flow independently.
	 *
	 * @return bool
	 */
	protected static function pro_is_blocking() {
		return FrmAppHelper::pro_is_included();
	}

	/**
	 * @return bool
	 */
	protected static function is_local_environment() {
		return in_array( wp_get_environment_type(), array( 'local', 'development' ), true );
	}

	/**
	 * The frm_first_activation option is only set on a genuinely fresh install, so
	 * a site that predates the option never gets it. A missing option means an old,
	 * established site rather than a new one. Mirrors FrmApiHelper::is_first_30().
	 *
	 * @return bool
	 */
	protected static function has_reached_install_age_threshold() {
		$install_time = get_option( 'frm_first_activation' );

		if ( false === $install_time ) {
			return true;
		}

		return time() - (int) $install_time >= self::INSTALL_AGE_THRESHOLD_DAYS * DAY_IN_SECONDS;
	}

	/**
	 * @return array
	 */
	protected static function get_config() {
		return array(
			'script'          => array(
				'handle' => 'formidable-lite-plugin-feedback',
				'url'    => FrmAppHelper::plugin_url() . '/js/plugin-feedback.js',
			),
			'style'           => array(
				'handle' => 'formidable-lite-plugin-feedback',
				'url'    => FrmAppHelper::plugin_url() . '/css/components/plugin-feedback.css',
			),
			'ajax'            => array(
				'submit'  => 'submit_lite_plugin_feedback',
				'dismiss' => 'dismiss_lite_plugin_feedback',
			),
			'remote'          => 'https://formidableforms.com/wp-admin/admin-ajax.php?action=frm_forms_preview&form=plugin-feedback-lite',
			'remote_form_key' => 'plugin-feedback-lite',
		);
	}

	/**
	 * @return void
	 */
	public static function enqueue_assets() {
		$config  = self::get_config();
		$version = FrmAppHelper::plugin_version();

		wp_enqueue_script( $config['script']['handle'], $config['script']['url'], array( 'formidable_dom' ), $version, true );
		wp_enqueue_style( $config['style']['handle'], $config['style']['url'], array(), $version );
	}

	/**
	 * @return void
	 */
	public static function show_plugin_feedback() {
		$current = self::get_current_year_feedback();
		$step    = isset( $current['nps-score'] ) ? 'reasons' : 'nps';
		$reasons = self::get_reasons();
		$config  = self::get_config();

		include FrmAppHelper::plugin_path() . '/classes/views/shared/plugin-feedback.php';
	}

	/**
	 * @return void
	 */
	public static function ajax_submit_plugin_feedback() {
		check_ajax_referer( 'frm_ajax', 'nonce' );
		FrmAppHelper::permission_check( 'frm_change_settings' );

		if ( self::pro_is_blocking() ) {
			wp_send_json_error( array( 'type' => 'pro-active' ) );
		}

		self::maybe_save_nps_and_send_response();
		self::submit_feedback_to_remote();
	}

	/**
	 * @return void
	 */
	public static function ajax_dismiss_plugin_feedback() {
		check_ajax_referer( 'frm_ajax', 'nonce' );
		FrmAppHelper::permission_check( 'frm_change_settings' );

		if ( self::pro_is_blocking() ) {
			wp_send_json_error( array( 'type' => 'pro-active' ) );
		}

		self::submit_feedback_to_remote();
	}

	/**
	 * @return void
	 */
	protected static function maybe_save_nps_and_send_response() {
		if ( ! isset( $_POST['nps-score'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		$nps_score = (int) FrmAppHelper::get_post_param( 'nps-score', 10, 'absint' );

		if ( $nps_score < 0 || $nps_score > 10 ) {
			wp_send_json_error( array( 'type' => 'invalid-nps' ) );
		}

		self::set_current_year_feedback( 'nps-score', $nps_score );
		wp_send_json_success( array( 'message' => __( 'Feedback score saved successfully.', 'formidable' ) ) );
	}

	/**
	 * @return void
	 */
	protected static function submit_feedback_to_remote() {
		$current = self::get_current_year_feedback();

		if ( ! isset( $current['nps-score'] ) ) {
			self::set_current_year_feedback( 'submitted', true );
			self::record_prompt_timestamp();
			wp_send_json_success( array( 'message' => __( 'Feedback dismissed successfully.', 'formidable' ) ) );
		}

		$remote_response = wp_remote_post(
			self::get_config()['remote'],
			array(
				'timeout' => 30,
				'body'    => http_build_query( self::build_remote_body() ),
			)
		);

		if ( is_wp_error( $remote_response ) ) {
			wp_send_json_error(
				array(
					'type'    => 'server-error',
					'message' => __( 'Failed to submit feedback to remote service.', 'formidable' ),
				)
			);
		}

		$response_code = wp_remote_retrieve_response_code( $remote_response );

		if ( WP_Http::OK !== $response_code ) {
			wp_send_json_error(
				array(
					'type'    => 'server-error',
					'message' => __( 'Remote service returned an error.', 'formidable' ),
				)
			);
		}

		self::set_current_year_feedback( 'submitted', true );
		self::record_prompt_timestamp();
		wp_send_json_success( array( 'message' => __( 'Feedback submitted successfully.', 'formidable' ) ) );
	}

	/**
	 * @return void
	 */
	protected static function record_prompt_timestamp() {
		self::get_plugin_feedback();
		self::$plugin_feedback['last_prompted'] = time();
		update_option( self::PLUGIN_FEEDBACK_OPTION_KEY, self::$plugin_feedback, false );
	}

	/**
	 * Maps feedback field keys to the field codes formidableforms.com expects.
	 *
	 * @return array
	 */
	protected static function build_remote_body() {
		$map = array(
			'nps'     => 'NPS',
			'reasons' => 'RSN',
			'details' => 'DTL',
			'url'     => 'URL',
			'source'  => 'SRC',
			'version' => 'VER',
		);

		$feedback = self::get_current_year_feedback();

		$body = array(
			'l'            => base64_encode( (string) get_option( 'frm-usage-uuid' ) ),
			'form_key'     => self::get_config()['remote_form_key'] ?? '',
			'frm_action'   => 'create',
			'form_id'      => 0,
			'item_key'     => '',
			'item_meta[0]' => '',
		);

		$values = array(
			'nps'     => $feedback['nps-score'] ?? '',
			'reasons' => self::format_reasons_list( self::get_posted_reasons() ),
			'details' => FrmAppHelper::get_post_param( 'details', '' ),
			'url'     => site_url(),
			'source'  => self::SOURCE,
			'version' => FrmAppHelper::plugin_version(),
		);

		foreach ( $values as $key => $value ) {
			$body[ 'item_meta[' . $map[ $key ] . ']' ] = $value;
		}

		return $body;
	}

	/**
	 * @return array
	 */
	protected static function get_posted_reasons() {
		$reasons = json_decode( FrmAppHelper::get_post_param( 'reasons', '[]' ), true );
		$reasons = rest_sanitize_value_from_schema(
			$reasons,
			array(
				'type'  => 'array',
				'items' => array(
					'enum' => array_keys( self::get_reasons() ),
					'type' => 'string',
				),
			)
		);

		if ( ! $reasons && ! FrmAppHelper::get_post_param( 'dismissed', false, 'rest_sanitize_boolean' ) ) {
			wp_send_json_error( array( 'type' => 'invalid-reasons' ) );
		}

		return is_array( $reasons ) ? $reasons : array();
	}

	/**
	 * @param array $reason_keys
	 *
	 * @return string
	 */
	protected static function format_reasons_list( $reason_keys ) {
		if ( ! $reason_keys ) {
			return '';
		}

		$reasons           = self::get_reasons();
		$formatted_reasons = array_map(
			static function ( $key ) use ( $reasons ) {
				return '- ' . $reasons[ $key ];
			},
			$reason_keys
		);

		return implode( "\n", $formatted_reasons );
	}

	/**
	 * @return array
	 */
	protected static function get_plugin_feedback() {
		if ( self::$plugin_feedback ) {
			return self::$plugin_feedback;
		}

		$plugin_feedback = get_option( self::PLUGIN_FEEDBACK_OPTION_KEY );

		if ( ! is_array( $plugin_feedback ) ) {
			$plugin_feedback = array();
		}

		if ( ! isset( $plugin_feedback[ self::get_current_year() ] ) ) {
			$plugin_feedback[ self::get_current_year() ] = array(
				'submitted' => false,
				'source'    => self::SOURCE,
			);
		}

		self::$plugin_feedback = $plugin_feedback;

		return self::$plugin_feedback;
	}

	/**
	 * @return array
	 */
	protected static function get_current_year_feedback() {
		$year_feedback = self::get_plugin_feedback()[ self::get_current_year() ];
		return is_array( $year_feedback ) ? $year_feedback : array();
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return void
	 */
	protected static function set_current_year_feedback( $key, $value ) {
		self::get_plugin_feedback();
		self::$plugin_feedback[ self::get_current_year() ][ $key ]   = $value;
		self::$plugin_feedback[ self::get_current_year() ]['source'] = self::SOURCE;
		update_option( self::PLUGIN_FEEDBACK_OPTION_KEY, self::$plugin_feedback, false );
	}

	/**
	 * @return int
	 */
	protected static function get_current_year() {
		if ( self::$current_year ) {
			return self::$current_year;
		}

		self::$current_year = (int) wp_date( 'Y' );
		return self::$current_year;
	}

	/**
	 * Not translatable: sent to a remote service.
	 *
	 * @return array
	 */
	protected static function get_reasons() {
		return array(
			'pricing'          => 'Pricing and plans',
			'form-builder'     => 'Form builder flexibility',
			'customization'    => 'Customization options',
			'integrations'     => 'Integrations',
			'advanced-fields'  => 'Advanced fields',
			'customer-support' => 'Customer support',
			'templates'        => 'Template selection',
			'performance'      => 'Performance/Speed',
			'calculations'     => 'Calculations & formulas',
			'documentation'    => 'Documentation / tutorials',
		);
	}
}
