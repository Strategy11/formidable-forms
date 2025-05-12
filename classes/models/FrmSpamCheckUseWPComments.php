<?php
/**
 * Spam check using WordPress spam comments
 *
 * @since 6.21
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSpamCheckUseWPComments extends FrmSpamCheck {

	protected function check() {
		$spam_comments = get_comments(
			array(
				'status'  => 'spam',
				// Reasonable limit to prevent performance issues.
				'number'  => 100,
				'orderby' => 'comment_date_gmt',
				'order'   => 'DESC',
			)
		);
		if ( ! is_array( $spam_comments ) ) {
			return false;
		}

		$ip_address      = FrmAppHelper::get_ip_address();
		$whitelist_ip    = FrmAntiSpamController::get_allowed_ips();
		$is_whitelist_ip = in_array( $ip_address, $whitelist_ip, true );
		$item_meta       = FrmAppHelper::array_flatten( $this->values['item_meta'] );

		foreach ( $spam_comments as $comment ) {
			if ( ! $is_whitelist_ip && $ip_address === $comment->comment_author_IP ) {
				return true;
			}

			foreach ( $item_meta as $value ) {
				if ( ! $value ) {
					continue;
				}

				if ( $value === $comment->comment_author_email || $value === $comment->comment_author_url ) {
					return true;
				}
			}
		}

		return false;
	}

	protected function is_enabled() {
		$frm_settings = FrmAppHelper::get_settings();
		return ! empty( $frm_settings->wp_spam_check );
	}
}
