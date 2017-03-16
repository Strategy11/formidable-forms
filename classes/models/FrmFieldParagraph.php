<?php

/**
 * @since 2.03.05
 */
class FrmFieldParagraph extends FrmFieldAbstract {

	/**
	 * @var string
	 * @since 2.03.05
	 */
	protected $type = 'textarea';

	public function __construct( $id ) {
		parent::__construct( $id );

		$this->nice_name = __( 'Paragraph Text', 'formidable' );
	}
}