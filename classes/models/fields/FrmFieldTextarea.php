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

	protected function prepare_display_value( $value, $atts ) {
		return $this->run_wpautop( $atts, $value );
	}
}
