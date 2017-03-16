<?php

/**
 * @since 2.03.05
 */
class FrmFieldOptions {

	/**
	 * @var FrmFieldAbstractWithOptions
	 * @since 2.03.05
	 */
	protected $field = null;

	/**
	 * @var FrmFieldSettings
	 */
	protected $field_settings = null;

	/**
	 * @var array
	 * @since 2.03.05
	 */
	protected $options = array();

	/**
	 * FrmFieldOptions constructor.
	 *
	 * @param FrmFieldAbstract $field
	 */
	public function __construct( $field ) {
		$this->field = $field;
		$this->field_settings = $field->get_settings();
		$this->set_options();
	}

	/**
	 * Set the raw_options property
	 *
	 * @since 2.03.05
	 */
	protected function set_options() {
		$serialized_options = $this->field->get_db_row()->options;
		$this->options      = maybe_unserialize( $serialized_options );
	}

	/**
	 * Check if object has any options
	 *
	 * @since 2.03.05
	 *
	 * @return bool
	 */
	private function has_options() {
		return ! empty( $this->options );
	}

	/**
	 * Display the field value selector (dropdown or text field)
	 * Used in field conditional logic, action conditional logic, MailChimp action, et.
	 *
	 * @since 2.03.05
	 *
	 * @param string $html_name
	 * @param string $selected_value
	 * @param string $first_option
	 */
	public function display_field_value_selector( $html_name, $selected_value, $first_option ) {
		if ( $this->has_options() ) {
			$this->display_field_value_dropdown_selector( $html_name, $selected_value, $first_option );
		} else {
			FrmFieldsHelper::print_field_value_text_box( $html_name, $selected_value );
		}
	}

	/**
	 * Display the field value dropdown selector
	 *
	 * @since 2.03.05
	 *
	 * @param string $html_name
	 * @param string $first_option
	 * @param string $selected_value
	 */
	protected function display_field_value_dropdown_selector( $html_name, $first_option, $selected_value ) {
		echo '<select name="' . esc_attr( $html_name ) . '">';
		echo '<option value="">' . esc_attr( $first_option ) . '</option>';

		if ( ! empty( $this->options ) ) {
			foreach ( $this->options as $key => $value ) {
				if ( $value == '' ) {
					continue;
				}

				$option = $this->get_single_field_option( $key, $value );
				$option->print_single_option( $selected_value, 25 );
			}
		}

		echo '</select>';
	}

	/**
	 * Get an instance of FrmFieldOption
	 *
	 * @since 2.03.05
	 *
	 * @param string $key
	 * @param string $value
	 *
	 * @return FrmFieldOption
	 */
	protected function get_single_field_option( $key, $value ) {
		return new FrmFieldOption( $this->field_settings, $key, $value );
	}

}