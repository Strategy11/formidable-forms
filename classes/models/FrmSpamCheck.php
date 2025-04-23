<?php

abstract class FrmSpamCheck {

	protected $values;

	protected $posted_fields;

	public function __construct( $values ) {
		$this->values        = $values;
		$this->posted_fields = FrmField::get_all_for_form( $values['form_id'] );
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
}
