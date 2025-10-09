<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.0
 */
class FrmHtmlHelper {

	/**
	 * Create a toggle and either echo or return the string.
	 * This is intended for use on admin pages only. The CSS is included in frm_admin.css.
	 *
	 * @since 6.0
	 *
	 * @param string $id
	 * @param string $name
	 * @param array  $args {
	 *     Any extra arguments.
	 *
	 *     @type bool|null $echo True if you want the toggle to echo. False if you want it to return an HTML string.
	 * }
	 *
	 * @return string|void
	 */
	public static function toggle( $id, $name, $args ) {
		wp_enqueue_script( 'formidable_settings' );
		return FrmAppHelper::clip(
			// @phpstan-ignore-next-line
			function () use ( $id, $name, $args ) {
				require FrmAppHelper::plugin_path() . '/classes/views/shared/toggle.php';
			},
			$args['echo'] ?? false
		);
	}

	/**
	 * Echo a dropdown option.
	 * This is useful to avoid closing and opening PHP to echo <option> tags which leads to extra whitespace.
	 * Avoiding whitespace saves 5KB of HTML for an international address field with a country dropdown with 252 options.
	 *
	 * @since 6.3.1
	 *
	 * @param string $option   The string used as the option label.
	 * @param bool   $selected True if the option should be selected.
	 * @param array  $params   Other HTML params for the option.
	 * @return void
	 */
	public static function echo_dropdown_option( $option, $selected, $params = array() ) {
		echo '<option ';
		FrmAppHelper::array_to_html_params( $params, true );
		selected( $selected );
		echo '>';
		echo esc_html( $option === '' ? ' ' : $option );
		echo '</option>';
	}

	/**
	 * Renders a number input with unit selector.
	 *
	 * @since 6.24
	 *
	 * @param array $args {
	 *     Optional. Arguments to customize the unit input.
	 *
	 *     @type array  $field_attrs        Attributes for the hidden input storing the field value.
	 *     @type array  $input_number_attrs Attributes for the visible number input.
	 *     @type array  $units              Available units for selection. Default is ['px', '%', 'em'].
	 *     @type string $value              Initial value with optional unit (e.g. '10px', '50%').
	 * }
	 *
	 * @return void
	 */
	public static function echo_unit_input( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'field_attrs'        => array(),
				'input_number_attrs' => array(),
				'units'              => array( '', 'px', '%', 'em' ),
				'default_unit'       => 'px',
				'value'              => '',
			)
		);

		$units = $args['units'];
		$value = $args['value'];

		// Extract unit and number value if value is a string
		if ( '' !== $value && ! is_numeric( $value ) ) {
			$pattern = '/^([0-9.]*)(' . implode( '|', array_map( 'preg_quote', $units ) ) . ')?$/';
			preg_match( $pattern, $value, $matches );
			$selected_unit = $matches[2] ?? '';
			if ( ! empty( $matches[1] ) ) {
				$value = $matches[1];
			}
		}

		$input_number_attrs          = array_merge(
			$args['input_number_attrs'],
			array(
				'type'  => ! empty( $selected_unit ) ? 'number' : 'text',
				'value' => $value,
				'class' => trim( 'frm-unit-input-control ' . ( $args['input_number_attrs']['class'] ?? '' ) ),
			)
		);

		$hidden_value = $args['value'];
		if ( is_numeric( $hidden_value ) ) {
			$hidden_value .= $args['default_unit'];
		}
		?>
		<span class="frm-unit-input">
			<input type="hidden" value="<?php echo esc_attr( $hidden_value ); ?>" <?php FrmAppHelper::array_to_html_params( $args['field_attrs'], true ); ?> />
			<input <?php FrmAppHelper::array_to_html_params( $input_number_attrs, true ); ?> />
			<span class="frm-input-group-suffix">
				<select aria-label="<?php echo esc_attr__( 'Select unit', 'formidable' ); ?>" tabindex="0">
					<?php
					foreach ( $units as $unit ) {
						self::echo_dropdown_option( $unit, $unit === ( $selected_unit ?? $args['default_unit'] ), array( 'value' => $unit ) );
					}
					?>
				</select>
			</span>
		</span>
		<?php
	}
}
