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
	protected static $user_id;

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
		if ( ! static::should_show_plugin_feedback() ) {
			return;
		}

		$user_id = get_current_user_id();
		$class   = get_called_class();

		add_filter( 'frm_should_show_floating_links', '__return_false' );
		add_action( 'admin_enqueue_scripts', array( $class, 'enqueue_assets' ) );
		add_action( 'admin_footer', array( $class, 'show_plugin_feedback' ), 1 );
	}

	/**
	 * @return bool
	 */
	protected static function should_show_plugin_feedback() {
		return static::passes_common_gates() && static::passes_product_specific_gates();
	}

	/**
	 * @return bool
	 */
	protected static function passes_common_gates() {
		if ( ! static::$user_id ) {
			return false;
		}

		if ( ! current_user_can( 'frm_change_settings' ) ) {
			return false;
		}

		if ( static::is_local_environment() ) {
			return false;
		}

		if ( static::pro_is_blocking() ) {
			return false;
		}

		if ( is_network_admin() ) {
			return false;
		}

		if ( ! FrmAppHelper::is_formidable_admin() ) {
			return false;
		}

		$current = static::get_current_year_feedback();
		return ! empty( $current['submitted'] ) ? false : true;
	}

	/**
	 * @return bool
	 */
	protected static function passes_product_specific_gates() {
		return static::has_reached_install_age_threshold();
	}

	/**
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
	 * @return bool
	 */
	protected static function has_reached_install_age_threshold() {
		$install_time = (int) get_option( 'frm_first_activation' );
		if ( ! $install_time ) {
			return false;
		}

		$threshold_days = (int) apply_filters( 'frm_lite_plugin_feedback_threshold_days', 20 );
		return time() - $install_time >= $threshold_days * DAY_IN_SECONDS;
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
		$config  = static::get_config();
		$version = FrmAppHelper::plugin_version();

		wp_enqueue_script( $config['script']['handle'], $config['script']['url'], array( 'formidable_dom' ), $version, true );
		wp_enqueue_style( $config['style']['handle'], $config['style']['url'], array(), $version );
	}

	/**
	 * @return void
	 */
	public static function show_plugin_feedback() {
		$current = static::get_current_year_feedback();
		$step    = isset( $current['nps-score'] ) ? 'reasons' : 'nps';
		$reasons = static::get_reasons();
		$config  = static::get_config();

		include FrmAppHelper::plugin_path() . '/classes/views/shared/plugin-feedback.php';
	}

	/**
	 * @return void
	 */
	public static function ajax_submit_plugin_feedback() {
		check_ajax_referer( 'frm_ajax', 'nonce' );
		FrmAppHelper::permission_check( 'frm_change_settings' );

		if ( static::pro_is_blocking() ) {
			wp_send_json_error( array( 'type' => 'pro-active' ) );
		}

		static::$user_id = get_current_user_id();

		static::maybe_save_nps_and_send_response();
		static::submit_feedback_to_remote();
	}

	/**
	 * @return void
	 */
	public static function ajax_dismiss_plugin_feedback() {
		check_ajax_referer( 'frm_ajax', 'nonce' );
		FrmAppHelper::permission_check( 'frm_change_settings' );

		if ( static::pro_is_blocking() ) {
			wp_send_json_error( array( 'type' => 'pro-active' ) );
		}

		static::$user_id = get_current_user_id();

		static::submit_feedback_to_remote();
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

		static::set_current_year_feedback( 'nps-score', $nps_score );
		wp_send_json_success( array( 'message' => __( 'Feedback score saved successfully.', 'formidable' ) ) );
	}

	/**
	 * @return void
	 */
	protected static function submit_feedback_to_remote() {
		$current = static::get_current_year_feedback();

		if ( ! isset( $current['nps-score'] ) ) {
			static::set_current_year_feedback( 'submitted', true );
			static::after_submission_commit();
			wp_send_json_success( array( 'message' => __( 'Feedback dismissed successfully.', 'formidable' ) ) );
		}

		$remote_response = wp_remote_post(
			static::get_config()['remote'],
			array(
				'timeout' => 30,
				'body'    => http_build_query( static::build_remote_body() ),
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

		static::set_current_year_feedback( 'submitted', true );
		static::after_submission_commit();
		wp_send_json_success( array( 'message' => __( 'Feedback submitted successfully.', 'formidable' ) ) );
	}

	/**
	 * @return array
	 */
	protected static function field_map() {
		return array(
			'nps'     => 'NPS',
			'reasons' => 'RSN',
			'details' => 'DTL',
			'url'     => 'URL',
			'source'  => 'SRC',
			'version' => 'VER',
		);
	}

	/**
	 * @return array
	 */
	protected static function build_remote_body() {
		$map      = static::field_map();
		$config   = static::get_config();
		$feedback = static::get_current_year_feedback();

		$body = array(
			'l'            => base64_encode( (string) static::get_remote_identifier() ),
			'form_key'     => isset( $config['remote_form_key'] ) ? $config['remote_form_key'] : '',
			'frm_action'   => 'create',
			'form_id'      => static::get_remote_form_id(),
			'item_key'     => '',
			'item_meta[0]' => '',
		);

		$values = array(
			'nps'     => isset( $feedback['nps-score'] ) ? $feedback['nps-score'] : '',
			'reasons' => static::format_reasons_list( static::get_posted_reasons() ),
			'details' => FrmAppHelper::get_post_param( 'details', '' ),
			'url'     => site_url(),
			'source'  => static::SOURCE,
			'version' => FrmAppHelper::plugin_version(),
		);

		foreach ( $values as $key => $value ) {
			if ( ! isset( $map[ $key ] ) ) {
				continue;
			}

			$body[ 'item_meta[' . $map[ $key ] . ']' ] = $value;
		}

		return $body;
	}

	/**
	 * @return string
	 */
	protected static function get_remote_identifier() {
		return (string) get_option( 'frm-usage-uuid' );
	}

	/**
	 * @return int
	 */
	protected static function get_remote_form_id() {
		return 0;
	}

	/**
	 * @return void
	 */
	protected static function after_submission_commit() {
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
					'enum' => array_keys( static::get_reasons() ),
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
	protected static function format_reasons_list( $reason_keys ) {
		if ( ! $reason_keys ) {
			return '';
		}

		$reasons           = static::get_reasons();
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
		if ( static::$plugin_feedback ) {
			return static::$plugin_feedback;
		}

		static::$plugin_feedback = get_user_meta( static::$user_id, static::PLUGIN_FEEDBACK_META_KEY, true );

		if ( ! is_array( static::$plugin_feedback ) ) {
			static::$plugin_feedback = array(
				static::get_current_year() => array(
					'submitted' => false,
					'source'    => static::SOURCE,
				),
			);
		} elseif ( ! isset( static::$plugin_feedback[ static::get_current_year() ] ) ) {
			static::$plugin_feedback[ static::get_current_year() ] = array(
				'submitted' => false,
				'source'    => static::SOURCE,
			);
		}

		return static::$plugin_feedback;
	}

	/**
	 * @return array
	 */
	protected static function get_current_year_feedback() {
		return static::get_plugin_feedback()[ static::get_current_year() ];
	}

	/**
	 * @param string $key
	 * @param mixed  $value
	 * @return void
	 */
	protected static function set_current_year_feedback( $key, $value ) {
		static::get_plugin_feedback();
		static::$plugin_feedback[ static::get_current_year() ][ $key ]   = $value;
		static::$plugin_feedback[ static::get_current_year() ]['source'] = static::SOURCE;
		update_user_meta( static::$user_id, static::PLUGIN_FEEDBACK_META_KEY, static::$plugin_feedback );
	}

	/**
	 * @return int
	 */
	protected static function get_current_year() {
		if ( static::$current_year ) {
			return static::$current_year;
		}

		static::$current_year = (int) wp_date( 'Y' );
		return static::$current_year;
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
