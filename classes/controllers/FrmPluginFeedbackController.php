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
 * Collects an NPS score and qualitative feedback from Lite users ~20 days after install,
 * unless Formidable Pro is loaded (in which case Pro's own survey takes over).
 *
 * @since 6.26.1
 */
class FrmPluginFeedbackController {

	/**
	 * Shared with Pro so a Lite submission suppresses Pro's prompt after upgrade.
	 *
	 * @var string
	 */
	const PLUGIN_FEEDBACK_META_KEY = 'frm-plugin-feedback';

	/**
	 * Marks a submission as originating from Lite when read from shared user meta.
	 *
	 * @var string
	 */
	const SOURCE = 'lite';

	/**
	 * @var int
	 */
	private static $user_id;

	/**
	 * @var array
	 */
	private static $plugin_feedback;

	/**
	 * @var int
	 */
	private static $current_year;

	/**
	 * @return void
	 */
	public static function load_admin_hooks() {
		self::$user_id = get_current_user_id();

		if ( ! self::should_show_plugin_feedback() ) {
			return;
		}

		add_filter( 'frm_should_show_floating_links', '__return_false' );
		add_action( 'admin_enqueue_scripts', self::class . '::enqueue_assets' );
		add_action( 'admin_footer', self::class . '::show_plugin_feedback', 1 );
	}

	/**
	 * @return bool
	 */
	private static function should_show_plugin_feedback() {
		if ( ! self::$user_id ) {
			return false;
		}

		if ( ! current_user_can( 'frm_change_settings' ) ) {
			return false;
		}

		if ( self::is_local_environment() ) {
			return false;
		}

		if ( FrmAppHelper::pro_is_included() ) {
			return false;
		}

		if ( is_network_admin() ) {
			return false;
		}

		if ( ! FrmAppHelper::is_formidable_admin() ) {
			return false;
		}

		if ( ! self::has_reached_install_age_threshold() ) {
			return false;
		}

		$current = self::get_current_year_feedback();
		if ( ! empty( $current['submitted'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	private static function is_local_environment() {
		return in_array( wp_get_environment_type(), array( 'local', 'development' ), true );
	}

	/**
	 * @return bool
	 */
	private static function has_reached_install_age_threshold() {
		$install_time = (int) get_option( 'frm_first_activation' );
		if ( ! $install_time ) {
			return false;
		}

		$threshold_days = (int) apply_filters( 'frm_lite_plugin_feedback_threshold_days', 20 );
		return ( time() - $install_time ) >= ( $threshold_days * DAY_IN_SECONDS );
	}

	/**
	 * @return void
	 */
	public static function enqueue_assets() {
		$version = FrmAppHelper::plugin_version();

		wp_enqueue_script( 'formidable-lite-plugin-feedback', FrmAppHelper::plugin_url() . '/js/plugin-feedback.js', array( 'formidable_dom' ), $version, true );
		wp_enqueue_style( 'formidable-lite-plugin-feedback', FrmAppHelper::plugin_url() . '/css/components/plugin-feedback.css', array(), $version );
	}

	/**
	 * @return void
	 */
	public static function show_plugin_feedback() {
		$current = self::get_current_year_feedback();
		$step    = isset( $current['nps-score'] ) ? 'reasons' : 'nps';
		$reasons = self::get_reasons();

		include FrmAppHelper::plugin_path() . '/classes/views/shared/plugin-feedback.php';
	}

	/**
	 * @return void
	 */
	public static function ajax_submit_plugin_feedback() {
		check_ajax_referer( 'frm_ajax', 'nonce' );
		FrmAppHelper::permission_check( 'frm_change_settings' );

		if ( FrmAppHelper::pro_is_included() ) {
			wp_send_json_error( array( 'type' => 'pro-active' ) );
		}

		self::$user_id = get_current_user_id();

		self::maybe_save_nps_and_send_response();
		self::submit_feedback_to_remote();
	}

	/**
	 * @return void
	 */
	public static function ajax_dismiss_plugin_feedback() {
		check_ajax_referer( 'frm_ajax', 'nonce' );
		FrmAppHelper::permission_check( 'frm_change_settings' );

		if ( FrmAppHelper::pro_is_included() ) {
			wp_send_json_error( array( 'type' => 'pro-active' ) );
		}

		self::$user_id = get_current_user_id();

		self::submit_feedback_to_remote();
	}

	/**
	 * @return void
	 */
	private static function maybe_save_nps_and_send_response() {
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
	private static function submit_feedback_to_remote() {
		$current = self::get_current_year_feedback();

		if ( ! isset( $current['nps-score'] ) ) {
			self::set_current_year_feedback( 'submitted', true );
			wp_send_json_success( array( 'message' => __( 'Feedback dismissed successfully.', 'formidable' ) ) );
		}

		$remote_response = wp_remote_post(
			'https://formidableforms.com/wp-admin/admin-ajax.php?action=frm_forms_preview&form=plugin-feedback-lite',
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
		wp_send_json_success( array( 'message' => __( 'Feedback submitted successfully.', 'formidable' ) ) );
	}

	/**
	 * Builds the payload sent to formidableforms.com. form_id and item_meta keys
	 * are placeholders until Marketing finalizes the destination form; swapping them
	 * should be a small edit here.
	 *
	 * @return array
	 */
	private static function build_remote_body() {
		$feedback = self::get_current_year_feedback();
		$nps      = isset( $feedback['nps-score'] ) ? $feedback['nps-score'] : '';

		return array(
			'l'              => base64_encode( (string) get_option( 'frm-usage-uuid' ) ),
			'form_key'       => 'plugin-feedback-lite',
			'frm_action'     => 'create',
			'form_id'        => 0,
			'item_key'       => '',
			'item_meta[0]'   => '',
			'item_meta[NPS]' => $nps,
			'item_meta[RSN]' => self::format_reasons_list( self::get_posted_reasons() ),
			'item_meta[DTL]' => FrmAppHelper::get_post_param( 'details', '' ),
			'item_meta[URL]' => site_url(),
			'item_meta[SRC]' => self::SOURCE,
			'item_meta[VER]' => FrmAppHelper::plugin_version(),
		);
	}

	/**
	 * @return array
	 */
	private static function get_posted_reasons() {
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

		return $reasons;
	}

	/**
	 * @param array $reason_keys
	 * @return string
	 */
	private static function format_reasons_list( $reason_keys ) {
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
	private static function get_plugin_feedback() {
		if ( self::$plugin_feedback ) {
			return self::$plugin_feedback;
		}

		self::$plugin_feedback = get_user_meta( self::$user_id, self::PLUGIN_FEEDBACK_META_KEY, true );

		if ( ! is_array( self::$plugin_feedback ) ) {
			self::$plugin_feedback = array(
				self::get_current_year() => array(
					'submitted' => false,
					'source'    => self::SOURCE,
				),
			);
		} elseif ( ! isset( self::$plugin_feedback[ self::get_current_year() ] ) ) {
			self::$plugin_feedback[ self::get_current_year() ] = array(
				'submitted' => false,
				'source'    => self::SOURCE,
			);
		}

		return self::$plugin_feedback;
	}

	/**
	 * @return array
	 */
	private static function get_current_year_feedback() {
		return self::get_plugin_feedback()[ self::get_current_year() ];
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return void
	 */
	private static function set_current_year_feedback( $key, $value ) {
		self::get_plugin_feedback();
		self::$plugin_feedback[ self::get_current_year() ][ $key ]      = $value;
		self::$plugin_feedback[ self::get_current_year() ]['source']    = self::SOURCE;
		update_user_meta( self::$user_id, self::PLUGIN_FEEDBACK_META_KEY, self::$plugin_feedback );
	}

	/**
	 * @return int
	 */
	private static function get_current_year() {
		if ( self::$current_year ) {
			return self::$current_year;
		}

		self::$current_year = (int) wp_date( 'Y' );
		return self::$current_year;
	}

	/**
	 * English-only — sent to a remote service, so intentionally not translatable.
	 *
	 * @return array
	 */
	private static function get_reasons() {
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
