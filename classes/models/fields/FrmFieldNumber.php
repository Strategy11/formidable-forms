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
		return array(
			'size'           => true,
            'clear_on_focus' => true,
            'invalid'        => true,
		);
	}
}
