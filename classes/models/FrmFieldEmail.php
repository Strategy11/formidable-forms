<?php

/**
 * @since 2.03.05
 */
class FrmFieldEmail extends FrmFieldAbstract {

	/**
	 * @var string
	 * @since 2.03.05
	 */
	protected $type = 'email';

	public function __construct( $id ) {
		parent::__construct( $id );

		$this->nice_name = __( 'Email Address', 'formidable' );
	}
}