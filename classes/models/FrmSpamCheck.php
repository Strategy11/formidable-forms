<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

abstract class FrmSpamCheck {

	protected $values;

	public function __construct( $values ) {
		$this->values = $values;
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
