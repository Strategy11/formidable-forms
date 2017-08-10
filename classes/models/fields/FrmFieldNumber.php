<?php

/**
 * @since 3.0
 */
class FrmFieldNumber extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'number';

	protected function field_settings_for_type() {
		$settings = array(
			'size'           => true,
			'clear_on_focus' => true,
			'invalid'        => true,
		);

		$frm_settings = FrmAppHelper::get_settings();
		if ( $frm_settings->use_html ) {
			$settings['max'] = false;
		}

		return $settings;
	}

	protected function extra_field_opts() {
		return array(
			'minnum' => 0,
			'maxnum' => 9999999,
			'step'   => 'any',
		);
	}
}
