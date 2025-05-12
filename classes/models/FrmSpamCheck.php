<?php
/**
 * Spam check abstract class
 *
 * @since 6.21
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

abstract class FrmSpamCheck {

	/**
	 * Entry values.
	 *
	 * @var array
	 */
	protected $values;

	public function __construct( $values ) {
		$this->values = $values;
	}

	/**
	 * Checks if is spam.
	 *
	 * @return bool|string Return the spam message or `false` if is not spam.
	 */
	public function is_spam() {
		if ( ! $this->is_enabled() ) {
			return false;
		}

		$is_spam = $this->check();
		if ( ! $is_spam ) {
			return false;
		}
		return $this->get_spam_message();
	}

	/**
	 * Checks for spam.
	 *
	 * @return bool Return `true` if this is spam.
	 */
	abstract protected function check();

	/**
	 * Checks if the check is enabled.
	 *
	 * @return bool
	 */
	protected function is_enabled() {
		return true;
	}

	/**
	 * Gets spam message.
	 *
	 * @return string If this is empty string, a default message will be used.
	 */
	protected function get_spam_message() {
		return FrmAntiSpamController::get_default_spam_message();
	}
}
