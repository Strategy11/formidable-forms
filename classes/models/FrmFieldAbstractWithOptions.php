<?php

/**
 * @since 2.03.05
 */
abstract class FrmFieldAbstractWithOptions extends FrmFieldAbstract {

	/**
	 * @var FrmFieldOptions
	 * @since 2.03.05
	 */
	protected $options = null;

	public function __construct( $id ) {
		parent::__construct( $id );

		$this->set_options();
	}

	/**
	 * Set the options property
	 *
	 * @since 2.03.05
	 */
	protected function set_options() {
		$this->options = new FrmFieldOptions( $this );
	}

	/**
	 * Display the field value selector
	 * Used in field conditional logic, action conditional logic, MailChimp action, etc.
	 *
	 * @since 2.03.05
	 *
	 * @param string $html_name
	 * @param string $selected_value
	 * @param string $source
	 */
	public function display_field_value_selector( $html_name, $selected_value, $source ) {
		$this->options->display_field_value_selector( $html_name, $selected_value, '' );
	}

}