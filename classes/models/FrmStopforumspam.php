<?php

class FrmStopforumspam extends FrmValidate {

	private $values;

	private $posted_fields;

	public function get_option_key() {
		return 'stopforumspam';
	}

	public function validate() {
		if ( ! $this->is_option_on() || ! $this->check_filter() ) {
			return true;
		}
		return ! $this->is_spam();
	}

	public function set_values( $values ) {
		$this->values = $values;
	}

	public function set_posted_fields( $posted_fields ) {
		$this->posted_fields = $posted_fields;
	}

	private function check_filter() {
		$form = $this->get_form();
		return apply_filters( 'frm_run_stopforumspam', true, compact( 'form' ) );
	}

	private function is_spam() {
		$ip_address   = FrmAppHelper::get_ip_address();
		$ips_to_allow = array( '', '127.0.0.1'  );
		$request_data = array();

		if ( ! in_array( $ip_address, $ips_to_allow, true ) ) {
			$request_data['ip'] = $ip_address;
		}

		$response = $this->send_request( $request_data );

		if ( $this->response_is_spam( $response ) ) {
			return true;
		}

		$emails = $this->extract_emails_from_string( json_encode( $this->values['item_meta'] ) );
		if ( ! $emails ) {
			return false;
		}

		unset( $request_data['ip'] );
		$request_data['email'] = $emails;
		$response = $this->send_request( $request_data );
		return $this->response_is_spam( $response );
	}

	private function send_request( $request_data ) {
		$response = wp_remote_get(
			'http://api.stopforumspam.org/api',
			array(
				'body' => $request_data,
			)
		);
		return wp_remote_retrieve_body( $response );

	}

	private function extract_emails_from_string( $str ) {
		preg_match_all( '/[\._a-zA-Z0-9-]+@[\._a-zA-Z0-9-]+/i', $str, $matches );
		return isset( $matches[0] ) ? $matches[0] : array();
	}

	private function add_email_to_request( &$request_data ) {
		$email = $this->values['item_meta'][ $this->posted_fields['email'] ];
		if ( is_email( $email ) ) {
			$request_data['email'] = $email;
		}
		return $request_data;
	}

	private function response_is_spam( $response ) {
		return false !== strpos( $response, '<appears>yes</appears>' ) || false !== strpos( $response, '<appears>1</appears>' );
	}
}
