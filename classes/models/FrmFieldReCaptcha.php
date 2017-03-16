<?php

/**
 * @since 2.03.05
 */
class FrmFieldReCaptcha extends FrmFieldAbstract {

	/**
	 * @var string
	 * @since 2.03.05
	 */
	protected $type = 'captcha';

	public function __construct( $id ) {
		parent::__construct( $id );

		$this->nice_name = __( 'reCAPTCHA', 'formidable' );
	}
}