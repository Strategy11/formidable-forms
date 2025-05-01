<?php
/**
 * Anti-spam controller
 *
 * @package Formidable
 * @since x.x
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmAntiSpamController {

	/**
	 * Checks if given entry values is spam.
	 *
	 * @param array $values Entry values.
	 *
	 * @return bool Return `true` if is spam.
	 */
	public static function is_spam( $values ) {
		return self::contains_wp_disallowed_words( $values ) ||
			self::is_denylist_spam( $values ) ||
			self::is_stopforumspam_spam( $values ) ||
			self::is_wp_comment_spam( $values );
	}

	private static function is_stopforumspam_spam( $values ) {
		$spam_check = new FrmSpamCheckStopforumspam( $values );
		return $spam_check->is_spam();
	}

	private static function is_wp_comment_spam( $values ) {
		$spam_check = new FrmSpamCheckUseWPComments( $values );
		return $spam_check->is_spam();
	}

	public static function contains_wp_disallowed_words( $values ) {
		$spam_check = new FrmSpamCheckWPDisallowedWords( $values );
		return $spam_check->is_spam();
	}

	public static function is_denylist_spam( $values ) {
		$spam_check = new FrmSpamCheckDenylist( $values );
		return $spam_check->is_spam();
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
	 * Extracts email addresses from values.
	 *
	 * @param array $values Values to check.
	 * @return string[]
	 */
	public static function extract_emails_from_values( $values ) {
		$values = FrmAppHelper::maybe_json_encode( $values );
		preg_match_all( '/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i', $values, $matches );
		return $matches[0];
	}

	/**
	 * Gets allowed IP addresses.
	 *
	 * @return string[]
	 */
	public static function get_allowed_ips() {
		/**
		 * Filter the allowed IP addresses.
		 *
		 * @since x.x
		 *
		 * @params string[] $allowed_ips Allowed IP addresses.
		 */
		return apply_filters( 'frm_allowed_ips', array( '', '127.0.0.1' ) );
	}
}
