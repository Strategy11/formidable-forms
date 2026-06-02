<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
class FrmFieldShapeStyleComponent extends FrmStyleComponent {

	/**
	 * The view file name.
	 *
	 * @since 6.14
	 *
	 * @var string
	 */
	protected $view_name = 'field-shape';

	/**
	 * Construct the FrmFieldShapeStyleComponent.
	 *
	 * @since 6.14
	 *
	 * @param string $field_name  Field name attribute.
	 * @param mixed  $field_value Current field value.
	 * @param array  $data        Additional component data.
	 */
	public function __construct( $field_name, $field_value, $data ) {
		$this->init( $data, $field_name, $field_value );
	}
}
