<?php

/**
 * @since 2.03.05
 */
class FrmFieldWebsite extends FrmFieldAbstract {

	/**
	 * @var string
	 * @since 2.03.05
	 */
	protected $type = 'url';

	public function __construct( $id ) {
		parent::__construct( $id );

		$this->nice_name = __( 'Website/URL', 'formidable' );
	}
}