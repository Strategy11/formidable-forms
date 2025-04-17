<?php

abstract class FrmSpamCheck {

	protected $values;

	protected $posted_fields;

	public function __construct( $values, $posted_fields ) {
		$this->values = $values;
		$this->posted_fields = $posted_fields;
	}

	public function is_spam() {
		if ( ! $this->is_enabled() ) {
			return false;
		}

		return $this->check();
	}

	/**
	 * Checks for spam.
	 *
	 * @return bool Return `true` if this is spam.
	 */
	abstract protected function check();

	protected function is_enabled() {
		return true;
	}

	protected function check_whole_values_contain_spam_words() {

	}

	protected function check_single_value_exactly_spam_words() {

	}

	protected function check_domain_in_email_address() {

	}

	protected function check_ip_address() {

	}
}
