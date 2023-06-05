<?php

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
