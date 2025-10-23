<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 2.03.05
 */
class FrmFieldOption {

	/**
	 * @var int|string
	 *
	 * @since 2.03.05
	 */
	protected $option_key;

	/**
	 * @var array|string
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
		$this->option     = $option;
		$this->set_option_label();
		$this->set_saved_value();
	}

	/**
	 * Set the option label
	 *
	 * @since 2.03.05
	 *
	 * @return void
	 */
	private function set_option_label() {
		if ( is_array( $this->option ) ) {
			$this->option_label = ( $this->option['label'] ?? reset( $this->option ) );
		} else {
			$this->option_label = $this->option;
		}
	}

	/**
	 * Set the saved value
	 *
	 * @since 2.03.05
	 *
	 * @return void
	 */
	protected function set_saved_value() {
		$this->saved_value = $this->option_label;
	}

	/**
	 * Print a single option
	 *
	 * @since 2.03.05
	 *
	 * @param string $selected_value     The value of the option to be selected.
	 * @param int    $truncate           Truncate the option label if true.
	 * @param bool   $use_value_as_label Use the option value as the label if true.
	 *
	 * @return void
	 */
	public function print_single_option( $selected_value, $truncate, $use_value_as_label = false ) {
		if ( '' === $this->saved_value && ! $use_value_as_label ) {
			return;
		}

		if ( $use_value_as_label && '' === trim( $this->option_label ) ) {
			$label = '' !== (string) $this->saved_value ? $this->saved_value : FrmAppHelper::get_no_label_text();
		} else {
			$label = $this->option_label;
		}

		echo '<option value="' . esc_attr( $this->saved_value ) . '"';
		selected( esc_attr( $selected_value ), esc_attr( $this->saved_value ) );
		// TODO: add hook that can add attributes to option text
		echo '>';
		echo esc_html( FrmAppHelper::truncate( $label, $truncate ) ) . '</option>';
	}
}
