<?php

class FrmAntiSpamController {

	/**
	 * Checks if given entry values is spam.
	 *
	 * @param array $values Entry values.
	 * @param array $posted_fields Posted fields.
	 *
	 * @return bool Return `true` if is spam.
	 */
	public static function is_spam( $values, $posted_fields) {
		return self::is_blacklist_spam( $values, $posted_fields ) ||
			self::is_stopforumspam_spam( $values, $posted_fields ) ||
			self::is_wp_comment_spam( $values, $posted_fields );
	}

	private static function is_stopforumspam_spam( $values, $posted_fields ) {
		$spam_check = new FrmSpamCheckStopforumspam( $values, $posted_fields );
		return $spam_check->is_spam();
	}

	private static function is_wp_comment_spam( $values, $posted_fields ) {
		$spam_check = new FrmSpamCheckUseWPComments( $values, $posted_fields );
		return $spam_check->is_spam();
	}

	private static function is_blacklist_spam( $values, $posted_fields ) {
		$check = new FrmBlacklistSpamCheck( $values['form_id'] );;
		$check->set_values( $values );
		return ! $check->validate();
	}

	/**
	 * Gets spam message.
	 *
	 * @return string
	 */
	public static function get_spam_message() {
		return __( 'Your entry appears to be spam!', 'formidable' );
	}

	/**
	 * Extracts email addresses from string.
	 *
	 * @param string $str String.
	 * @return string[]
	 */
	public static function extract_emails_from_string( $str ) {
		preg_match_all( '/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i', $str, $matches );
		return isset( $matches[0] ) ? $matches[0] : array();
	}

	public static function get_whitelist_ip() {
		return array( '', '127.0.0.1' );
	}
}
