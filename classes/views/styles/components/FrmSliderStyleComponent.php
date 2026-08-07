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

	/**
	 * @param string $field_name
	 * @param mixed  $field_value
	 * @param array  $data
	 */
	public function __construct( $field_name, $field_value, $data ) {
		$this->init_field_data( $data, $field_name, $field_value );

		if ( true === $this->hide_component() ) {
			return;
		}

		$this->data['unit_measurement']    = $this->detect_unit_measurement();
		$this->data['has-multiple-values'] = count( $this->get_values() ) > 1;
		$this->data['units']               = $this->get_units_list( $data );
		$this->data['value_label']         = $this->detect_unit_measurement() ? (float) $field_value : $field_value;

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
	 *
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
		$this->data['max_value'] = ! empty( $this->data['max_value'] ) ? $this->data['max_value'] : 100;
	}

	/**
	 * Get the highest value a slider may be dragged to.
	 * A percentage runs from 0 to 100 whatever pixel maximum the component was given. A value that
	 * was already saved above the maximum still has to be reachable, otherwise the slider would sit
	 * at a different value than the one shown in the text box beside it.
	 *
	 * @since x.x
	 *
	 * @param string     $unit   The unit of measurement, for instance 'px', 'em' or '%'.
	 * @param float|null $number The value about to be shown, or null when the value is not a number.
	 *
	 * @return float
	 */
	private function get_max_for_unit( $unit, $number ) {
		$max = '%' === $unit ? 100.0 : (float) $this->data['max_value'];

		if ( null !== $number && $number > $max ) {
			return ceil( $number );
		}

		return $max;
	}

	/**
	 * Print a number the way an HTML attribute needs it, without a trailing run of zeros.
	 *
	 * @since x.x
	 *
	 * @param float $number The number to print.
	 *
	 * @return string
	 */
	private static function format_number( $number ) {
		return rtrim( rtrim( sprintf( '%.4F', $number ), '0' ), '.' );
	}

	/**
	 * Get the step a range input needs in order to hold the given value exactly.
	 * A range snaps its value to the step, so '1.5em' on the default step of 1 would move the
	 * handle to 2. Whole numbers keep stepping by one so the arrow keys stay useful.
	 *
	 * @since x.x
	 *
	 * @param float $number The value about to be shown.
	 *
	 * @return string
	 */
	private static function get_step_for_value( $number ) {
		$printed = self::format_number( $number );
		$dot     = strpos( $printed, '.' );

		if ( false === $dot ) {
			return '1';
		}

		$decimals = strlen( $printed ) - $dot - 1;
		return '0.' . str_repeat( '0', $decimals - 1 ) . '1';
	}

	/**
	 * Get the number at the start of a style value.
	 *
	 * @since x.x
	 *
	 * @param mixed $value The value to read, for instance '10px', 12.5 or 'auto'.
	 *
	 * @return float|null The number found, or null when the value has none.
	 */
	private static function get_numeric_value( $value ) {
		if ( ! preg_match( '/^\s*-?\d+(?:\.\d+)?/', (string) $value, $matches ) ) {
			return null;
		}

		return (float) $matches[0];
	}

	/**
	 * Get the accessible label describing one part of a multi part slider.
	 *
	 * @since x.x
	 *
	 * @param string $type The slider part, for instance 'top' or 'left'.
	 *
	 * @return string
	 */
	protected function get_label_for_type( $type ) {
		$labels = array(
			'vertical'   => __( 'Vertical value', 'formidable' ),
			'horizontal' => __( 'Horizontal value', 'formidable' ),
			'top'        => __( 'Top value', 'formidable' ),
			'bottom'     => __( 'Bottom value', 'formidable' ),
			'left'       => __( 'Left value', 'formidable' ),
			'right'      => __( 'Right value', 'formidable' ),
		);

		if ( isset( $labels[ $type ] ) ) {
			return $labels[ $type ];
		}

		return __( 'Field value', 'formidable' );
	}

	/**
	 * Print the range input a user drags to change a slider value.
	 * The max, the printed value and the disabled state all follow the unit that is currently selected,
	 * so the range never offers a value the unit does not allow.
	 *
	 * @since x.x
	 *
	 * @param string $label Accessible label describing what this slider controls.
	 * @param mixed  $value The current value. It may carry a unit ('10px') or be a keyword ('auto').
	 * @param string $unit  The unit of measurement selected for this slider.
	 *
	 * @return void
	 */
	protected function print_range_input( $label, $value, $unit ) {
		$number = self::get_numeric_value( $value );

		// A value like 'auto' has no position on the track, so the range is shown at zero and switched off.
		$is_disabled = null === $number;

		if ( $is_disabled || $number < 0 ) {
			$number = 0;
		}

		$max = $this->get_max_for_unit( $unit, $number );

		?>
		<input type="range" class="frm-slider" min="0" max="<?php echo esc_attr( self::format_number( $max ) ); ?>" step="<?php echo esc_attr( self::get_step_for_value( $number ) ); ?>" value="<?php echo esc_attr( self::format_number( $number ) ); ?>" aria-label="<?php echo esc_attr( $label ); ?>" <?php disabled( $is_disabled ); ?> />
		<?php
	}

	/**
	 * Init the slider multiple values data. It works with sliders which has multiple values only: top&bottom and left&right.
	 * This is used for cases when there are 4 sliders in the same field.
	 *
	 * @since 6.14
	 *
	 * @return void
	 */
	private function init_multiple_values() {
		if ( ! $this->data['has-multiple-values'] ) {
			return;
		}

		$values = $this->get_values();
		$top    = $values[0];
		$bottom = ! empty( $values[2] ) ? $values[2] : $values[0];
		$left   = ! empty( $values[3] ) ? $values[3] : $values[1];
		$right  = $values[1];

		$this->data['vertical'] = array(
			'unit'  => $this->detect_unit_measurement( $top ),
			'value' => $this->detect_unit_measurement( $top ) ? (float) $top : $top,
		);

		$this->data['horizontal'] = array(
			'unit'  => $this->detect_unit_measurement( $right ),
			'value' => $this->detect_unit_measurement( $right ) ? (float) $right : $right,
		);

		$this->data['top'] = array(
			'unit'  => $this->detect_unit_measurement( $top ),
			'value' => $this->detect_unit_measurement( $top ) ? (float) $top : $top,
		);

		$this->data['bottom'] = array(
			'unit'  => $this->detect_unit_measurement( $bottom ),
			'value' => $this->detect_unit_measurement( $bottom ) ? (float) $bottom : $bottom,
		);

		$this->data['left'] = array(
			'unit'  => $this->detect_unit_measurement( $left ),
			'value' => $this->detect_unit_measurement( $left ) ? (float) $left : $left,
		);

		$this->data['right'] = array(
			'unit'  => $this->detect_unit_measurement( $right ),
			'value' => $this->detect_unit_measurement( $right ) ? (float) $right : $right,
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
	 * @param string|null $value
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

		return preg_match( '/px$/', $value ) ? 'px' : '';
	}

	/**
	 * Init the field icon
	 *
	 * @since 6.14
	 *
	 * @return void
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
				$this->data['icon'] = 'frmfont frm-margin-top-bottom';
				return;

			case 'horizontal-margin':
				$this->data['icon'] = 'frmfont frm-margin-left-right';
				return;

			default:
				$this->data['icon'] = '';
				return;
		}
	}
}
