<?php

/**
 * @since 2.03.05
 */
class FrmFieldDropdown extends FrmFieldAbstract {

	/**
	 * @var string
	 * @since 2.03.05
	 */
	protected $type = 'select';

	public function __construct( $id ) {
		parent::__construct( $id );

		$this->nice_name = __( 'Dropdown', 'formidable' );
	}

}