<?php
/**
 * Spam check using WordPress disallowed words
 *
 * @since 6.21
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSpamCheckWPDisallowedWords extends FrmSpamCheck {

	/**
	 * @return bool
	 */
	public function check() {
		$mod_keys = trim( get_option( 'disallowed_keys' ) );

		if ( ! $mod_keys ) {
			return false;
		}

		$values  = $this->values;
		$content = FrmEntriesHelper::entry_array_to_string( $values );

		FrmEntryValidate::prepare_values_for_spam_check( $values );
		$ip         = FrmAppHelper::get_ip_address();
		$user_agent = FrmAppHelper::get_server_value( 'HTTP_USER_AGENT' );
		$user_info  = FrmEntryValidate::get_spam_check_user_info( $values );

		return wp_check_comment_disallowed_list(
			$user_info['comment_author'],
			$user_info['comment_author_email'],
			$user_info['comment_author_url'],
			$content,
			$ip,
			$user_agent
		);
	}

	protected function is_enabled() {
		return apply_filters( 'frm_check_blacklist', true, $this->values );
	}

	protected function get_spam_message() {
		return __( 'Your entry appears to be blocked spam!', 'formidable' );
	}
}
