<?php

/**
 * @since 3.0
 */
class FrmFieldTextarea extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'textarea';

	protected function field_settings_for_type() {
		return array(
			'size'           => true,
			'clear_on_focus' => true,
		);
	}

	protected function extra_field_opts() {
		return array(
			'max' => '5',
		);
	}

	/**
	 * @param string $name
	 */
	public function show_on_form_builder( $name = '' ) {
		$size = FrmField::get_option( $this->field, 'size' );
		$size_html = $size ? ' style="width:' . esc_attr( $size . ( is_numeric( $size ) ? 'px' : '' ) ) . '";' : '';

		$max = FrmField::get_option( $this->field, 'max' );
		$default_value = FrmAppHelper::esc_textarea( force_balance_tags( $this->get_field_column( 'default_value' ) ) );

		echo '<textarea name="' . esc_attr( $this->html_name( $name ) ) . '" ' . // WPCS: XSS ok.
			$size_html // WPCS: XSS ok.
			. ' rows="' . esc_attr( $max ) . '" ' .
			'id="' . esc_attr( $this->html_id() ) . '" class="dyn_default_value">' .
			$default_value // WPCS: XSS ok.
			. '</textarea>';
	}

	protected function prepare_display_value( $value, $atts ) {
		FrmFieldsHelper::run_wpautop( $atts, $value );

		return $value;
	}

	/**
	 * @param array $args
	 * @param array $shortcode_atts
	 *
	 * @return string
	 */
	public function front_field_input( $args, $shortcode_atts ) {
		$input_html = $this->get_field_input_html_hook( $this->field );
		$this->add_aria_description( $args, $input_html );
		$rows = ( $this->field['max'] ) ? 'rows="' . esc_attr( $this->field['max'] ) . '" ' : '';

		return '<textarea name="' . esc_attr( $args['field_name'] ) . '" id="' . esc_attr( $args['html_id'] ) . '" ' .
			$rows . $input_html . '>' .
			FrmAppHelper::esc_textarea( $this->field['value'] ) .
			'</textarea>';
	}
}
