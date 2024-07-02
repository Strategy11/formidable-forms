<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
class FrmSliderStyleComponent extends FrmStyleComponent {

	public $view_name = 'slider';

	public function __construct( $field_name, $field_value, $data ) {

		$this->data        = $data;
		$this->field_name  = $field_name;
		$this->field_value = $field_value;

		parent::get_instance( $field_name, $field_value, $data );
		
		$this->load_view();
	}

	private function init_icon() {
		if ( isset( $this->data['icon'] ) ) {
			return;
		}

		if ( ! isset( $this->data['type'] ) ) {
			$this->data['icon'] = '';
			return;
		}

		switch ( $this->data['type'] ) {
			case 'vertical-margin':
				$this->data['icon'] = FrmAppHelper::icon_by_class( 'frm_icon_font frm-margin-top-bottom', array( 'echo' => false ) );
				return;

			case 'horizontal-margin':
				$this->data['icon'] = FrmAppHelper::icon_by_class( 'frm_icon_font frm-margin-left-right', array( 'echo' => false ) );
				return;

			default:
				$this->data['icon'] = '';
				return;
		}
	}
}
