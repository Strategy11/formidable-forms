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

	protected function new_field_settings() {
		return array(
			'options' => serialize( array(
				'',
				__( 'Option 1', 'formidable' ),
			) ),
		);
	}
}
