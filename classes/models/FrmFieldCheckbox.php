<?php

/**
 * @since 2.03.05
 */
class FrmFieldCheckbox extends FrmFieldAbstractWithOptions {

	/**
	 * @var string
	 * @since 2.03.05
	 */
	protected $type = 'checkbox';

	public function __construct( $id ) {
		parent::__construct( $id );

		$this->nice_name = __( 'Checkboxes', 'formidable' );
	}
}