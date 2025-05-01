<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmSpamCheckStopforumspam extends FrmSpamCheck {

	protected function check() {
		$ip_address   = FrmAppHelper::get_ip_address();
		$whitelist_ip = FrmAntiSpamController::get_allowed_ips();
		$request_data = array();

		if ( ! in_array( $ip_address, $whitelist_ip, true ) ) {
			$request_data['ip'] = $ip_address;
		}

		if ( $request_data ) {
			$response = $this->send_request( $request_data );

			if ( $this->response_is_spam( $response ) ) {
				return true;
			}
		}

		$emails = FrmAntiSpamController::extract_emails_from_values( $this->values['item_meta'] );
		if ( ! $emails ) {
			return false;
		}

		unset( $request_data['ip'] );
		$request_data['email'] = $emails;
		$response              = $this->send_request( $request_data );

		return $this->response_is_spam( $response );
	}

	protected function is_enabled() {
		$form = FrmForm::getOne( $this->values['form_id'] );
		return $form && ! empty( $form->options['stopforumspam'] );
	}

	private function send_request( $request_data ) {
		$url = add_query_arg( $request_data, 'https://api.stopforumspam.org/api' );

		$response = wp_remote_get( $url );

		return wp_remote_retrieve_body( $response );
	}

	private function response_is_spam( $response ) {
		return false !== strpos( $response, '<appears>yes</appears>' ) || false !== strpos( $response, '<appears>1</appears>' );
	}
}
