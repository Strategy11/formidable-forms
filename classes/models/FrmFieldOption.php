<?php

/**
 * @since 2.03.05
 */
class FrmFieldOption {

	/**
	 * @var string|int
	 *
	 * @since 2.03.05
	 */
	protected $option_key;

	/**
	 * @var string|array
	 *
	 * @since 2.03.05
	 */
	protected $option;

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

	public function __construct( $option_key, $option, $args = array() ) {
		$this->option_key = $option_key;
		$this->option = $option;
		$this->set_option_label();
		$this->set_saved_value();
	}

	/**
	 * Set the option label
	 *
	 * @since 2.03.05
	 */
	private function set_option_label() {
		if ( is_array( $this->option ) ) {
			$this->option_label = ( isset( $this->option['label'] ) ? $this->option['label'] : reset( $this->option ) );
		} else {
			$this->option_label = $this->option;
		}
	}

	/**
	 * Set the saved value
	 *
	 * @since 2.03.05
	 */
	protected function set_saved_value() {
		$this->saved_value = $this->option_label;
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
		if ( $this->saved_value !== '' ) {
			echo '<option value="' . esc_attr( $this->saved_value ) . '"';
			selected( esc_attr( $selected_value ), esc_attr( $this->saved_value ) );
			// TODO: add hook that can add attributes to option text
			echo '>';
			echo FrmAppHelper::truncate( $this->option_label, $truncate ) . '</option>';
		}
	}

}