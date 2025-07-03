<?php
/**
 * Anti-spam controller
 *
 * @package Formidable
 * @since 6.21
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
	 * @return bool|string Return spam message if is spam or `false` if is not spam.
	 */
	public static function is_spam( $values ) {
		$methods = array(
			'contains_wp_disallowed_words',
			'is_denylist_spam',
			'is_stopforumspam_spam',
			'is_wp_comment_spam',
		);

		foreach ( $methods as $method ) {
			if ( ! is_callable( array( __CLASS__, $method ) ) ) {
				continue;
			}

			$is_spam = call_user_func( array( __CLASS__, $method ), $values );
			if ( $is_spam ) {
				return $is_spam;
			}
		}

		return false;
	}

	/**
	 * Checks spam using stopforumspam API.
	 *
	 * @param array $values Entry values.
	 *
	 * @return bool|string Return spam message if is spam or `false` if is not spam.
	 */
	private static function is_stopforumspam_spam( $values ) {
		$spam_check = new FrmSpamCheckStopForumSpam( $values );
		return $spam_check->is_spam();
	}

	/**
	 * Checks spam using WordPress spam comments.
	 *
	 * @param array $values Entry values.
	 *
	 * @return bool|string Return spam message if is spam or `false` if is not spam.
	 */
	private static function is_wp_comment_spam( $values ) {
		$spam_check = new FrmSpamCheckUseWPComments( $values );
		return $spam_check->is_spam();
	}

	/**
	 * Checks spam using WordPress disallowed words.
	 *
	 * @param array $values Entry values.
	 *
	 * @return bool|string Return spam message if is spam or `false` if is not spam.
	 */
	public static function contains_wp_disallowed_words( $values ) {
		$spam_check = new FrmSpamCheckWPDisallowedWords( $values );
		return $spam_check->is_spam();
	}

	/**
	 * Checks spam using denylist.
	 *
	 * @param array $values Entry values.
	 *
	 * @return bool|string Return spam message if is spam or `false` if is not spam.
	 */
	public static function is_denylist_spam( $values ) {
		$spam_check = new FrmSpamCheckDenylist( $values );
		return $spam_check->is_spam();
	}

	/**
	 * Gets spam message.
	 *
	 * @return string
	 */
	public static function get_default_spam_message() {
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
		 * @since 6.21
		 *
		 * @params string[] $allowed_ips Allowed IP addresses.
		 */
		return apply_filters( 'frm_allowed_ips', array( '', '127.0.0.1', '::1' ) );
	}
}
