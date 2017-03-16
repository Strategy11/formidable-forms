<?php

/**
 * @since 2.03.05
 */
class FrmFieldGeneric extends FrmFieldAbstract {

	/**
	 * @var string
	 * @since 2.03.05
	 */
	protected $type = 'generic';

	public function __construct( $id ) {
		parent::__construct( $id );

		$this->nice_name = __( 'Generic Field', 'formidable' );
	}
}