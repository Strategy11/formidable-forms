<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
class FrmDropdownStyleComponent extends FrmStyleComponent {

	public $view_name = 'dropdown';

	public function __construct( $field_name, $field_value, $data ) {

		$this->data        = $data;
		$this->field_name  = $field_name;
		$this->field_value = $field_value;

		parent::get_instance();
		
		$this->load_view( $this->data );
	}
}
