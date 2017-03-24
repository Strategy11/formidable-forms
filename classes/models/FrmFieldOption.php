<?php

/**
 * @since 2.03.05
 */
class FrmFieldOption {

	/**
	 * @var FrmFieldSettings
	 * @since 2.03.05
	 */
	protected $field_settings = null;

	/**
	 * @var string
	 * @since 2.03.05
	 */
	protected $saved_value = '';

	/**
	 * @var string
	 * @since 2.03.05
	 */
	protected $option_label = '';

	/**
	 * @var FrmFieldSettings
	 */
	protected $field = null;

	public function __construct( $field_settings, $option_key, $option ) {
		$this->field_settings = $field_settings;
		$this->set_option_label( $option );
		$this->saved_value = $this->option_label;
	}

	/**
	 * Set the option label
	 *
	 * @since 2.03.05
	 *
	 * @param array|string $option
	 */
	private function set_option_label( $option ) {
		if ( is_array( $option ) ) {
			$this->option_label = ( isset( $option['label'] ) ? $option['label'] : reset( $option ) );
		} else {
			$this->option_label = $option;
		}
	}

	/**
	 * Print a single option
	 *
	 * @since 2.03.05
	 *
	 * @param string $selected_value
	 * @param int $truncate
	 */
	public function print_single_option( $selected_value, $truncate ) {
		echo '<option value="' . esc_attr( $this->saved_value ) . '"';
		selected( esc_attr( $selected_value ), esc_attr( $this->saved_value ) );
		// TODO: add hook that can add attributes to option text
		echo '>';
		echo FrmAppHelper::truncate( $this->option_label, $truncate ) . '</option>';
	}

}