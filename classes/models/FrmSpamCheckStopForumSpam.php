<?php
/**
 * Check spam using stopforumspam API
 *
 * @since 6.21
 *
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Class FrmSpamCheckStopForumSpam
 */
class FrmSpamCheckStopForumSpam extends FrmSpamCheck {

	/**
	 * Checks spam.
	 *
	 * @return bool
	 */
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

	/**
	 * Checks if this spam check is enabled.
	 *
	 * @return bool
	 */
	protected function is_enabled() {
		$form = FrmForm::getOne( $this->values['form_id'] );
		return $form && ! empty( $form->options['stopforumspam'] );
	}

	/**
	 * Sends API request.
	 *
	 * @param array $request_data Request data.
	 * @return string
	 */
	private function send_request( $request_data ) {
		/**
		 * Filters the data to be passed to the stopforumspam request URL.
		 *
		 * @since 6.21
		 *
		 * @param array $request_data Request data.
		 * @param array $args         Contains `values`.
		 */
		$request_data = apply_filters( 'frm_stopforumspam_request_data', $request_data, array( 'values' => $this->values ) );

		/**
		 * Filters the stopforumspam API URL.
		 *
		 * @since 6.21
		 *
		 * @param string $api_url API URL.
		 * @param array  $args    Contains `values`.
		 */
		$api_url = apply_filters( 'frm_stopforumspam_api_url', 'https://api.stopforumspam.org/api', array( 'values' => $this->values ) );

		$url = add_query_arg( $request_data, $api_url );

		$response = wp_remote_get( $url, array( 'timeout' => 15 ) );

		return wp_remote_retrieve_body( $response );
	}

	/**
	 * Checks if the response is spam.
	 *
	 * @param string $response Response body.
	 * @return bool
	 */
	private function response_is_spam( $response ) {
		if ( ! $response ) {
			// Request failed or error happened.
			return false;
		}

		return false !== strpos( $response, '<appears>yes</appears>' ) || false !== strpos( $response, '<appears>1</appears>' );
	}
}
