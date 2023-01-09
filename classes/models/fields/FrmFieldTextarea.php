<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

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
		$max  = FrmField::get_option( $this->field, 'max' );

		echo '<textarea name="' . esc_attr( $this->html_name( $name ) ) . '" rows="' . esc_attr( $max ) . '" id="' . esc_attr( $this->html_id() ) . '" class="dyn_default_value"';

		if ( $size ) {
			if ( is_numeric( $size ) ) {
				$size .= 'px';
			}
			$style = 'width:' . $size . ';';
			echo ' style="' . esc_attr( $style ) . '"';
		}

		echo '>';
		echo FrmAppHelper::esc_textarea( force_balance_tags( $this->get_field_column( 'default_value' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</textarea>';
	}

	protected function prepare_display_value( $value, $atts ) {
		FrmFieldsHelper::run_wpautop( $atts, $value );

		return $value;
	}

	public function show_placeholder_setting( $field, $field_obj, $default_name, $default_value ) {
		echo '<textarea name="' . esc_attr( $default_name ) . '" class="default-value-field" id="frm_default_value_' . esc_attr( $field['id'] ) . '" rows="3" data-changeme="field_' . esc_attr( $field['field_key'] ) . '">' .
		FrmAppHelper::esc_textarea( $default_value ) . // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'</textarea>';
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
