<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
class FrmAlignStyleComponent extends FrmStyleComponent {

	/**
	 * The view file name.
	 *
	 * @since 6.14
	 *
	 * @var string
	 */
	protected $view_name = 'align';

	/**
	 * Construct the FrmAlignStyleComponent.
	 *
	 * @since 6.14
	 */
	public function __construct( $field_name, $field_value, $data ) {
		$this->init( $data, $field_name, $field_value );
	}

	/**
	 * Get the wrapper classname.
	 *
	 * @since 6.14
	 *
	 * @return string
	 */
	protected function get_wrapper_class_name() {
		$class  = $this->get_default_wrapper_class_names();
		$class .= ' frm-align-component frm-radio-component';

		if ( empty( $this->data['options'] ) || 2 !== count( $this->data['options'] ) ) {
			return $class;
		}

		return $class . ' frm-2-options';
	}
}
