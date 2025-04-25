<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @todo Show opt-in popup after plugin activation.
 * @since 3.06.04
 */
class FrmUsageController {

	/**
	 * Option name of flows data.
	 *
	 * @since 6.16.1
	 *
	 * @var string
	 */
	const FLOWS_ACTION_NAME = 'frm_usage_tracking_flows';

	/**
	 * Randomize the first send to prevent our servers from crashing.
	 *
	 * @since 3.06.04
	 *
	 * @return void
	 */
	public static function schedule_send() {
		$timestamp = wp_next_scheduled( 'formidable_send_usage' );
		if ( ! self::tracking_allowed() ) {
			if ( $timestamp ) {
				// Remove the scheduled event if it's not allowed and it's scheduled.
				wp_unschedule_event( $timestamp, 'formidable_send_usage' );
			}
			return;
		}

		if ( $timestamp ) {
			return;
		}

		$tracking = array(
			'day'    => rand( 0, 6 ) * DAY_IN_SECONDS,
			'hour'   => rand( 0, 23 ) * HOUR_IN_SECONDS,
			'minute' => rand( 0, 59 ) * MINUTE_IN_SECONDS,
			'second' => rand( 0, 59 ),
		);

		$offset    = array_sum( $tracking );
		$init_send = strtotime( 'next sunday' ) + $offset;

		wp_schedule_event( $init_send, 'weekly', 'formidable_send_usage' );
	}

	/**
	 * Adds once weekly to the existing schedules.
	 *
	 * @since 3.06.04
	 */
	public static function add_schedules( $schedules = array() ) {
		$schedules['weekly'] = array(
			'interval' => DAY_IN_SECONDS * 7,
			'display'  => __( 'Once Weekly', 'formidable' ),
		);
		return $schedules;
	}

	/**
	 * Checks if tracking is allowed.
	 *
	 * @since 6.16.3
	 *
	 * @return bool
	 */
	public static function tracking_allowed() {
		$settings = FrmAppHelper::get_settings();
		return $settings->tracking;
	}

	/**
	 * @since 3.06.04
	 *
	 * @return void
	 */
	public static function send_snapshot() {
		$usage = new FrmUsage();
		$usage->send_snapshot();
	}

	/**
	 * Loads scripts.
	 *
	 * @since 6.16.1
	 */
	public static function load_scripts() {
		if ( self::is_forms_list_page() || FrmAppHelper::is_admin_page( 'formidable-form-templates' ) ) {
			wp_enqueue_script( 'frm-usage-tracking', FrmAppHelper::plugin_url() . '/js/admin/usage-tracking.js', array( 'formidable_dom' ), FrmAppHelper::$plug_version, true );
		}
	}

	/**
	 * Checks if is forms list page.
	 *
	 * @since 6.16.1
	 *
	 * @return bool
	 */
	private static function is_forms_list_page() {
		if ( ! FrmAppHelper::on_form_listing_page() ) {
			return false;
		}

		// Exclude Trash page.
		$form_type = FrmAppHelper::simple_get( 'form_type' );
		return $form_type && 'published' === $form_type;
	}

	/**
	 * AJAX handler to track flows.
	 *
	 * @since 6.16.1
	 */
	public static function ajax_track_flows() {
		FrmAppHelper::permission_check( 'frm_view_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$key   = FrmAppHelper::get_post_param( 'key', '', 'sanitize_text_field' );
		$value = FrmAppHelper::get_post_param( 'value', '', 'sanitize_text_field' );

		self::update_flows_data( $key, $value );

		wp_send_json_success();
	}

	/**
	 * Updates flows data.
	 *
	 * @since 6.16.1
	 *
	 * @param string $key   Flow key.
	 * @param string $value Flow value.
	 *
	 * @return void
	 */
	public static function update_flows_data( $key, $value ) {
		$flows_data = self::get_flows_data();

		if ( '' === $key || '' === $value ) {
			return;
		}

		if ( ! isset( $flows_data[ $key ] ) ) {
			$flows_data[ $key ] = array();
		}

		if ( ! isset( $flows_data[ $key ][ $value ] ) ) {
			$flows_data[ $key ][ $value ] = 0;
		}

		$flows_data[ $key ][ $value ]++;
		update_option( self::FLOWS_ACTION_NAME, $flows_data );
	}

	/**
	 * Get flows data.
	 *
	 * @since 6.16.1
	 *
	 * @return array
	 */
	public static function get_flows_data() {
		return get_option( self::FLOWS_ACTION_NAME, array() );
	}
}
