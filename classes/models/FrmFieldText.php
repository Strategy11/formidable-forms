<?php

/**
 * @since 2.03.05
 */
class FrmFieldText extends FrmFieldAbstract {

	/**
	 * @var string
	 * @since 2.03.05
	 */
	protected $type = 'text';

	public function __construct( $id ) {
		parent::__construct( $id );

		$this->nice_name = __( 'Single Line Text', 'formidable' );
	}
}