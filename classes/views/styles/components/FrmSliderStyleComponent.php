<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
class FrmSliderStyleComponent extends FrmStyleComponent {

	/**
	 * The view file name.
	 *
	 * @since 6.14
	 *
	 * @var string
	 */
	protected $view_name = 'slider';

	/**
	 * The FrmStyleComponent data.
	 *
	 * @since 6.14
	 *
	 * @var array
	 */
	protected $data;

	public function __construct( $field_name, $field_value, $data ) {

		$this->init_field_data( $data, $field_name, $field_value );

		if ( true === $this->hide_component() ) {
			return;
		}

		$this->data['unit_measurement']    = $this->detect_unit_measurement();
		$this->data['has-multiple-values'] = count( $this->get_values() ) > 1;
		$this->data['units']               = $this->get_units_list( $data );
		$this->data['value_label']         = empty( $this->detect_unit_measurement() ) ? $field_value : (float) $field_value; 

		$this->init_defaults();
		$this->init_icon();
		$this->init_multiple_values();

		parent::get_instance();
		$this->load_view();
	}

	/**
	 * Retrieves the list of units for the slider style component.
	 *
	 * If the units array is empty in the provided data, it returns an array containing the default units: 'px', 'em', and '%'.
	 * Otherwise, it merges the units array from the provided data with the default units array and returns the result.
	 *
	 * @param array $data The data containing the units array.
	 * @return array The list of units for the slider style component.
	 */
	private function get_units_list( $data ) {
		if ( empty( $data['units'] ) ) {
			return array( '', 'px', 'em', '%' );
		}
		array_unshift( $data['units'], '' );
		return $data['units'];
	}

	/**
	 * Init components default values
	 *
	 * @since 6.14
	 *
	 * @return void
	 */
	private function init_defaults() {
		$this->data['max_value'] = empty( $this->data['max_value'] ) ? 100 : $this->data['max_value'];
	}

	/**
	 * Init the slider multiple values data. It works with sliders which has multiple values only: top&bottom and left&right.
	 * This is used for cases when there are 4 sliders in the same field.
	 *
	 * @since 6.14
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
			'unit'  => $this->detect_unit_measurement( $top ),
			'value' => empty( $this->detect_unit_measurement( $top ) ) ? $top : (float) $top,
		);

		$this->data['horizontal'] = array(
			'unit'  => $this->detect_unit_measurement( $right ),
			'value' => empty( $this->detect_unit_measurement( $right ) ) ? $right : (float) $right,
		);

		$this->data['top'] = array(
			'unit'  => $this->detect_unit_measurement( $top ),
			'value' => empty( $this->detect_unit_measurement( $top ) ) ? $top : (float) $top,
		);

		$this->data['bottom'] = array(
			'unit'  => $this->detect_unit_measurement( $bottom ),
			'value' => empty( $this->detect_unit_measurement( $bottom ) ) ? $bottom : (float) $bottom,
		);

		$this->data['left'] = array(
			'unit'  => $this->detect_unit_measurement( $left ),
			'value' => empty( $this->detect_unit_measurement( $left ) ) ? $left : (float) $left,
		);

		$this->data['right'] = array(
			'unit'  => $this->detect_unit_measurement( $right ),
			'value' => empty( $this->detect_unit_measurement( $right ) ) ? $right : (float) $right,
		);
	}

	/**
	 * Split the field value by space from string to an array.
	 * For instance: '10px 20px 30px 40px' will be converted to array( '10px', '20px', '30px', '40px' ).
	 *
	 * @since 6.14
	 *
	 * @return array
	 */
	private function get_values() {
		return explode( ' ', $this->field_value );
	}

	/**
	 * Detect the unit measurement from the value.
	 * Possible values are: "px", "%", "em" or empty ""
	 *
	 * @since 6.14
	 *
	 * @return string
	 */
	private function detect_unit_measurement( $value = null ) {
		if ( null === $value ) {
			$value = $this->field_value;
		}

		if ( preg_match( '/%$/', $value ) ) {
			return '%';
		}
		if ( preg_match( '/em$/', $value ) ) {
			return 'em';
		}
		if ( preg_match( '/px$/', $value ) ) {
			return 'px';
		}

		return '';
	}

	/**
	 * Init the field icon
	 *
	 * @since 6.14
	 *
	 * @return array
	 */
	private function init_icon() {

		if ( ! empty( $this->data['icon'] ) ) {
			return;
		}

		if ( empty( $this->data['type'] ) ) {
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
