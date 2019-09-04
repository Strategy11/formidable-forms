<?php

/**
 * @since 3.0
 */
class FrmFieldSelect extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'select';

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $holds_email_values = true;

	protected function include_form_builder_file() {
		return $this->include_front_form_file();
	}

	protected function field_settings_for_type() {
		return array(
			'clear_on_focus' => true,
			'size' => true,
		);
	}

	protected function new_field_settings() {
		return array(
			'options' => serialize(
				array(
					'',
					__( 'Option 1', 'formidable' ),
				)
			),
		);
	}

	/**
	 * Get the type of field being displayed.
	 *
	 * @since 4.02.01
	 * @return array
	 */
	public function displayed_field_type( $field ) {
		return array(
			$this->type => true,
		);
	}

	/**
	 * @since 4.0
	 * @param array $args - Includes 'field', 'display', and 'values'
	 */
	public function show_extra_field_choices( $args ) {
		$this->auto_width_setting( $args );
	}

	protected function include_front_form_file() {
		return FrmAppHelper::plugin_path() . '/classes/views/frm-fields/front-end/dropdown-field.php';
	}

	protected function show_readonly_hidden() {
		return true;
	}

	protected function prepare_import_value( $value, $atts ) {
		if ( FrmField::is_option_true( $this->field, 'multiple' ) ) {
			$value = $this->get_multi_opts_for_import( $value );
		}

		return $value;
	}

	/**
	 * @since 4.0.04
	 */
	public function sanitize_value( &$value ) {
		FrmAppHelper::sanitize_value( 'sanitize_text_field', $value );
	}
}
