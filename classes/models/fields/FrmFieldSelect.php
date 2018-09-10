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
		return FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/dropdown-field.php';
	}

	protected function field_settings_for_type() {
		return array(
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
}
