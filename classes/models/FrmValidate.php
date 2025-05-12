<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

abstract class FrmValidate {

	/**
	 * @var int
	 */
	protected $form_id;

	/**
	 * @var object
	 */
	protected $form;

	/**
	 * @since 6.21
	 * @var string
	 */
	protected $option_type = 'form';

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
		if ( empty( $this->form ) ) {
			$this->form = FrmForm::getOne( $this->form_id );
		}
		return $this->form;
	}

	/**
	 * @return bool
	 */
	protected function is_option_on() {
		$key = $this->get_option_key();
		if ( 'global' === $this->option_type ) {
			$frm_settings = FrmAppHelper::get_settings();
			return ! empty( $frm_settings->$key );
		}

		$form = $this->get_form();
		return ! empty( $form->options[ $key ] ) && 'off' !== $form->options[ $key ];
	}

	/**
	 * @return bool|string
	 */
	abstract public function validate();

	/**
	 * Track the form option key used for is_option_on function.
	 *
	 * @return string
	 */
	abstract protected function get_option_key();
}
