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
	 * @since x.x
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
		if ( wp_next_scheduled( 'formidable_send_usage' ) ) {
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
	 */
	public static function load_scripts() {
		// TODO: check page.
		wp_enqueue_script( 'frm-usage-tracking', FrmAppHelper::plugin_url() . '/js/admin/usage-tracking.js', array( 'formidable_dom' ), FrmAppHelper::$plug_version, true );
	}

	/**
	 * AJAX handler to track flows.
	 *
	 * @since x.x
	 */
	public static function ajax_track_flows() {
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$flows_data = self::get_flows_data();
		$key   = FrmAppHelper::get_post_param( 'key', '', 'sanitize_text_field' );
		$value = FrmAppHelper::get_post_param( 'value', '', 'sanitize_text_field' );

		if ( ! isset( $flows_data[ $key ] ) ) {
			$flows_data[ $key ] = array();
		}

		if ( ! isset( $flows_data[ $key ][ $value ] ) ) {
			$flows_data[ $key ][ $value ] = 0;
		}

		$flows_data[ $key ][ $value ]++;
		update_option( self::FLOWS_ACTION_NAME, $flows_data );

		wp_send_json_success();
	}

	/**
	 * Get flows data.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	public static function get_flows_data() {
		return get_option( self::FLOWS_ACTION_NAME, array() );
	}
}
