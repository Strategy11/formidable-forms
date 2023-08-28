<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLiteLog {

	/**
	 * @param string $title
	 * @param string $text
	 * @return void
	 */
	public static function log_message( $title, $text ) {
		if ( ! class_exists( 'FrmLog' ) ) {
			error_log( $title . ': ' . $text );
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
