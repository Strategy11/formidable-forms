<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTextToggleStyleComponent extends FrmStyleComponent {

	/**
	 * The view file name.
	 *
	 * @since 6.24
	 *
	 * @var string
	 */
	protected $view_name = 'text-toggle';

	/**
	 * Construct FrmTextToggleStyleComponent.
	 *
	 * @since 6.24
	 */
	public function __construct( $field_name, $field_value, $data ) {
		$this->init( $data, $field_name, $field_value );
	}
}
