<?php

/**
 * @since 2.03.05
 */
class FrmFieldRadio extends FrmFieldAbstractWithOptions {

	/**
	 * @var string
	 * @since 2.03.05
	 */
	protected $type = 'radio';

	public function __construct( $id ) {
		parent::__construct( $id );

		$this->nice_name = __( 'Radio Buttons', 'formidable' );
	}

}