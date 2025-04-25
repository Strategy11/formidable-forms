<?php

class FrmSpamCheckUseWPComments extends FrmSpamCheck {

	protected function check() {
		$spam_comments = get_comments( array( 'status' => 'spam' ) );
		if ( ! is_array( $spam_comments ) ) {
			return false;
		}

		$ip_address = FrmAppHelper::get_ip_address();
		$item_meta  = FrmAppHelper::array_flatten( $this->values['item_meta'] );

		foreach ( $spam_comments as $comment ) {
			if ( $ip_address === $comment->comment_author_IP ) {
				return true;
			}

			foreach ( $item_meta as $value ) {
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
