<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
class FrmSliderStyleComponent extends FrmStyleComponent {

	/**
	 * The view file name.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	public $view_name = 'slider';

	/**
	 * The FrmStyleComponent data.
	 *
	 * @since x.x
	 *
	 * @var array
	 */
	protected $data;

	public function __construct( $field_name, $field_value, $data ) {

		$this->data        = $data;
		$this->field_name  = $field_name;
		$this->field_value = $field_value;

		$this->data['unit_measurement']    = $this->detect_unit_measurement();
		$this->data['has-multiple-values'] = count( $this->get_values() ) > 1;

		$this->init_icon();
		$this->init_multiple_values();

		parent::get_instance();
		$this->load_view( $this->data );
	}

	/**
	 * Init the slider multiple values data. It works with sliders which has multiple values only: top&bottom and left&right.
	 * This is used for cases when there are 4 sliders in the same field.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private function init_multiple_values() {
		if ( ! $this->data['has-multiple-values'] ) {
			return;
		}

		$values = $this->get_values();
		$top    = $values[0];
		$bottom = empty( $values[2] ) ? $values[0] : $values[2];
		$left   = empty( $values[3] ) ? $values[1] : $values[3];
		$right  = $values[1];

		$this->data['vertical'] = array(
			'value' => $top,
			'unit'  => $this->detect_unit_measurement( $top )
		);
		$this->data['horizontal'] = array(
			'value' => $right,
			'unit'  => $this->detect_unit_measurement( $right )
		);

		$this->data['top'] = array(
			'value' => $top,
			'unit'  => $this->detect_unit_measurement( $top )
		);

		$this->data['bottom'] = array(
			'value' => $bottom,
			'unit'  => $this->detect_unit_measurement( $bottom )
		);

		$this->data['left'] = array(
			'value' => $left,
			'unit'  => $this->detect_unit_measurement( $left )
		);

		$this->data['right'] = array(
			'value' => $right,
			'unit'  => $this->detect_unit_measurement( $right )
		);
	}

	/**
	 * Split the field value by space from string to an array.
	 * For instance: '10px 20px 30px 40px' will be converted to array( '10px', '20px', '30px', '40px' ).
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private function get_values() {
		return explode( ' ', $this->field_value );
	}

	/**
	 * Detect the unit measurement from the value.
	 * Possible values are: px, %, em.
	 *
	 * @since x.x
	 *
	 * @return string
	 */
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

	/**
	 * Init the field icon
	 *
	 * @since x.x
	 *
	 * @return array
	 */
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
