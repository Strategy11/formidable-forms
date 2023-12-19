<?php
/**
 * Cron controller
 *
 * @since 6.3.2
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmCronController
 */
class FrmCronController {

	/**
	 * Gets all cron events.
	 *
	 * @since 6.3.2
	 *
	 * @return string[]
	 */
	private static function get_events() {
		return array(
			'formidable_send_usage' => 'weekly',
			'frm_daily_event'       => 'daily',
			'frm_payment_cron'      => 'daily',
		);
	}

	/**
	 * Schedules cron events.
	 *
	 * @since 6.7
	 */
	public static function schedule_events() {
		$events = self::get_events();

		// These are scheduled in another place.
		unset( $events['formidable_send_usage'] );
		unset( $events['frm_payment_cron'] );

		foreach ( $events as $event => $recurrence ) {
			if ( ! wp_next_scheduled( $event ) ) {
				wp_schedule_event( time(), $recurrence, $event );
			}
		}
	}

	/**
	 * Removes all cron events.
	 *
	 * @since 6.3.2
	 *
	 * @return void
	 */
	public static function remove_crons() {
		$events = self::get_events();

		foreach ( $events as $event => $recurrence ) {
			$timestamp = wp_next_scheduled( $event );
			if ( false !== $timestamp ) {
				wp_unschedule_event( $timestamp, $event );
			}
		}
	}
}
