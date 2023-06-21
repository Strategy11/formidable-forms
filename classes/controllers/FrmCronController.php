<?php
/**
 * Cron controller
 *
 * @since x.x
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
	 * @since x.x
	 *
	 * @return string[]
	 */
	private static function get_events() {
		return array(
			'formidable_send_usage' => 'weekly',
		);
	}


	/**
	 * Removes all cron events.
	 *
	 * @since x.x
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
