<?php

/**
 * @since 3.0
 */
class FrmFieldCheckbox extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'checkbox';

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $holds_email_values = true;

	protected function input_html() {
		return $this->multiple_input_html();
	}

	protected function include_form_builder_file() {
		return FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/field-multiple.php';
	}

	protected function field_settings_for_type() {
		return array(
			'default_blank' => false,
		);
	}

	protected function new_field_settings() {
		return array(
			'options' => serialize(
				array(
					__( 'Option 1', 'formidable' ),
					__( 'Option 2', 'formidable' ),
				)
			),
		);
	}

	protected function extra_field_opts() {
		$form_id = $this->get_field_column( 'form_id' );
		return array(
			'align' => FrmStylesController::get_style_val( 'check_align', ( empty( $form_id ) ? 'default' : $form_id ) ),
		);
	}

	protected function include_front_form_file() {
		return FrmAppHelper::plugin_path() . '/classes/views/frm-fields/front-end/checkbox-field.php';
	}

	protected function show_readonly_hidden() {
		return true;
	}

	protected function prepare_import_value( $value, $atts ) {
		return $this->get_multi_opts_for_import( $value );
	}
}
