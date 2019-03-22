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
	 * Randomize the first send to prevent our servers from crashing.
	 *
	 * @since 3.06.04
	 */
	public static function schedule_send() {
		if ( wp_next_scheduled( 'formidable_send_usage' ) ) {
			return;
		}

		$tracking = array(
			'day'      => rand( 0, 6 ) * DAY_IN_SECONDS,
			'hour'     => rand( 0, 23 ) * HOUR_IN_SECONDS,
			'minute'   => rand( 0, 59 ) * MINUTE_IN_SECONDS,
			'second'   => rand( 0, 59 ),
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
	 */
	public static function send_snapshot() {
		$usage = new FrmUsage();
		$usage->send_snapshot();
	}
}
