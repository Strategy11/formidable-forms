<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

abstract class FrmValidate {

	/**
	 * @var int $form_id
	 */
	protected $form_id;

	/**
	 * @var object $form
	 */
	protected $form;

	/**
	 * @param int $form_id
	 */
	public function __construct( $form_id ) {
		$this->form_id = $form_id;
	}

	/**
	 * @return object $form
	 */
	protected function get_form() {
		if ( ! isset( $this->form ) ) {
			$this->form = FrmForm::getOne( $this->form_id );
		}
		return $this->form;
	}

	/**
	 * @return bool
	 */
	protected function is_option_on() {
		$form = $this->get_form();
		$key  = $this->get_option_key();
		return ! empty( $form->options[ $key ] ) && 'off' !== $form->options[ $key ];
	}

	/**
	 * @return bool
	 */
	abstract public function validate();

	/**
	 * Track the form option key used for is_option_on function.
	 *
	 * @return string
	 */
	abstract protected function get_option_key();
}
