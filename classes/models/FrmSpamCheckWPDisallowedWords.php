<?php
/**
 * Spam check using WordPress disallowed words
 *
 * @since 6.21
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSpamCheckWPDisallowedWords extends FrmSpamCheck {

	public function check() {
		$mod_keys = trim( $this->get_disallowed_words() );
		if ( empty( $mod_keys ) ) {
			return false;
		}

		$values  = $this->values;
		$content = FrmEntriesHelper::entry_array_to_string( $values );

		FrmEntryValidate::prepare_values_for_spam_check( $values );
		$ip         = FrmAppHelper::get_ip_address();
		$user_agent = FrmAppHelper::get_server_value( 'HTTP_USER_AGENT' );
		$user_info  = FrmEntryValidate::get_spam_check_user_info( $values );

		return $this->do_check_wp_disallowed_words(
			$user_info['comment_author'],
			$user_info['comment_author_email'],
			$user_info['comment_author_url'],
			$content,
			$ip,
			$user_agent
		);
	}

	/**
	 * For WP 5.5 compatibility.
	 *
	 * @return string
	 */
	private function get_disallowed_words() {
		$keys = get_option( 'disallowed_keys' );
		if ( false === $keys ) {
			// Fallback for WP < 5.5.
			// phpcs:ignore WordPress.WP.DeprecatedParameterValues.Found
			$keys = get_option( 'blacklist_keys' );
		}
		return $keys;
	}

	/**
	 * For WP 5.5 compatibility.
	 *
	 * @return bool Return `true` if contains disallowed words.
	 */
	private function do_check_wp_disallowed_words( $author, $email, $url, $content, $ip, $user_agent ) {
		if ( function_exists( 'wp_check_comment_disallowed_list' ) ) {
			return wp_check_comment_disallowed_list( $author, $email, $url, $content, $ip, $user_agent );
		}
		// phpcs:ignore WordPress.WP.DeprecatedFunctions.wp_blacklist_checkFound
		return wp_blacklist_check( $author, $email, $url, $content, $ip, $user_agent );
	}

	protected function is_enabled() {
		return apply_filters( 'frm_check_blacklist', true, $this->values );
	}

	protected function get_spam_message() {
		return __( 'Your entry appears to be blocked spam!', 'formidable' );
	}
}
