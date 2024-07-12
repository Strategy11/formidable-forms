<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
class FrmSliderStyleComponent extends FrmStyleComponent {

	public $view_name = 'slider';
	protected $data;
	public function __construct( $field_name, $field_value, $data ) {

		$this->data        = $data;
		$this->field_name  = $field_name;
		$this->field_value = $field_value;

		$this->data['unit_measurement'] = $this->detect_unit_measurement();
		$this->data['has-multiple-values'] = count( $this->get_values() ) > 1;

		$this->init_icon();
		$this->init_multiple_values();

		parent::get_instance();
		$this->load_view( $this->data );
	}

	private function init_multiple_values() {
		if ( ! $this->data['has-multiple-values'] ) {
			return;
		}

		$values = $this->get_values();
		$this->data['vertical'] = array(
			'value' => $values[0],
			'unit'  => $this->detect_unit_measurement( $values[0] )
		);
		$this->data['horizontal'] = array(
			'value' => $values[1],
			'unit'  => $this->detect_unit_measurement( $values[1] )
		);
	}

	private function get_values() {
		return explode( ' ', $this->field_value );
	}

	private function detect_unit_measurement( $value = null ) {
		if ( null === $value ) {
			$value = $this->field_value;
		}
		switch ( true ) {
			case preg_match( '/px$/', $value ):
				return 'px';
			case preg_match( '/%$/', $value ):
				return '%';
			case preg_match( '/em$/', $value ):
				return 'em';
			default:
				return 'px';
		}
	}

	private function init_icon() {

		if ( ! empty( $this->data['icon'] ) ) {
			return;
		}

		if ( empty ( $this->data['type'] ) ) {
			$this->data['icon'] = '';
			return;
		}

		switch ( $this->data['type'] ) {
			case 'vertical-margin':
				$this->data['icon'] = 'frm_icon_font frm-margin-top-bottom';
				return;

			case 'horizontal-margin':
				$this->data['icon'] = 'frm_icon_font frm-margin-left-right';
				return;

			default:
				$this->data['icon'] = '';
				return;
		}
	}
}
