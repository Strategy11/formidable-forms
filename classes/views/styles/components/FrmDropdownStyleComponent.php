<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
class FrmDropdownStyleComponent extends FrmStyleComponent {

	/**
	 * The view file name.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	protected $view_name = 'dropdown';

	/**
	 * Construct FrmDropdownStyleComponent.
	 *
	 * @since x.x
	 */
	public function __construct( $field_name, $field_value, $data ) {
		$this->init( $data, $field_name, $field_value );
	}
}
