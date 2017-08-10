<?php

/**
 * @since 3.0
 */
class FrmFieldRadio extends FrmFieldMultiple {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'radio';

	protected function new_field_settings() {
		return array(
			'options' => serialize( array(
				__( 'Option 1', 'formidable' ),
				__( 'Option 2', 'formidable' ),
			) ),
		);
	}
}
