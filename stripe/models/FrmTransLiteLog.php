<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLiteLog {

	/**
	 * @todo
	 *
	 * @param string $text
	 * @return void
	 */
	public static function log_message( $text ) {
		error_log( $text );
	}
}
