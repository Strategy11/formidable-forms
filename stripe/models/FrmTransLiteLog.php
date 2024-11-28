<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLiteLog {

	/**
	 * @since 6.12 The $is_error parameter was added.
	 *
	 * @param string $title
	 * @param string $text
	 * @param bool   $is_error When this is false, the message will not be logged to the error log if the logging add-on is unavailable.
	 * @return void
	 */
	public static function log_message( $title, $text, $is_error = true ) {
		if ( ! class_exists( 'FrmLog' ) ) {
			if ( $is_error ) {
				error_log( $title . ': ' . $text );
			}
			return;
		}

		$log = new FrmLog();
		$log->add(
			array(
				'title'   => $title,
				'content' => $text,
			)
		);
	}
}
