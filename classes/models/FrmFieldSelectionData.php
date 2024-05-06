<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * This is a simple data object for field selection data.
 * Used in the form builder.
 *
 * @since 6.9.1
 */
class FrmFieldSelectionData {

	/**
	 * @var array
	 */
	public $all_field_types;

	/**
	 * @var array
	 */
	public $disabled_fields;

	public function __construct() {
		$pro_field_selection   = FrmField::pro_field_selection();
		$this->all_field_types = array_merge( $pro_field_selection, FrmField::field_selection() );
		$this->disabled_fields = FrmAppHelper::pro_is_installed() ? array() : $pro_field_selection;
	}
}
