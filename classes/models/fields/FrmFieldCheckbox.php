<?php

/**
 * @since 3.0
 */
class FrmFieldCheckbox extends FrmFieldMultiple {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'checkbox';

	protected function new_field_settings() {
		return array(
			'options' => serialize( array(
				__( 'Option 1', 'formidable' ),
				__( 'Option 2', 'formidable' ),
			) ),
		);
	}
}
