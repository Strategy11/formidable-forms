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

	protected function field_settings_for_type() {
		return array(
			'size' => true,
		);
	}
}
