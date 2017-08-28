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
	protected $has_for_label = true;

	protected function input_html() {
		return $this->multiple_input_html();
	}

	protected function field_settings_for_type() {
		return array(
			'default_blank' => false,
		);
	}

	protected function new_field_settings() {
		return array(
			'options' => serialize( array(
				__( 'Option 1', 'formidable' ),
				__( 'Option 2', 'formidable' ),
			) ),
		);
	}

	protected function prepare_import_value( $value, $atts ) {
		return $this->get_multi_opts_for_import( $value );
	}
}
