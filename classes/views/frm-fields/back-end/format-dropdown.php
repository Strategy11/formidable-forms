<?php
/**
 * @package Formidable
 * @since x.x
 *
 * @var array        $field Field data.
 * @var array        $args  Includes 'field', 'display', and 'values' settings.
 * @var FrmFieldType $this  Field type object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$field_id   = $field['id'];
$field_type = $field['type'];
$format     = FrmField::get_option( $field, 'format' );
?>
<p class="frm6 frm_form_field frm-format-dropdown">
	<label for="format_dropdown_<?php echo esc_attr( $field_id ); ?>">
		<?php esc_html_e( 'Format', 'formidable' ); ?>
	</label>

	<select
		name="field_options[format_dropdown_<?php echo esc_attr( $field_id ); ?>]"
		id="frm_format_dropdown_<?php echo esc_attr( $field_id ); ?>"
		class="frm_format_dropdown frm_select_with_upgrade frm_select_with_dependency"
		data-field-id="<?php echo intval( $field_id ); ?>"
	>
		<option value="none" <?php selected( $format, '' ); ?>>
			<?php esc_html_e( 'None', 'formidable' ); ?>
		</option>

		<?php
		$number_option_text = in_array( $field_type, array( 'number', 'range' ) ) ? esc_html__( 'Custom', 'formidable' ) : esc_html__( 'Number', 'formidable' );
		$number_option      = '
			<option value="" class="frm_show_upgrade frm_noallow" data-upgrade="' . esc_attr__( 'Format number field', 'formidable' ) . '" data-medium="format-number-field">
				' . $number_option_text . '
			</option>';

		/**
		 * Filter the number option HTML for the format dropdown.
		 *
		 * @since x.x
		 *
		 * @param string $number_option       The HTML for the number option.
		 * @param string $number_option_text The text for the number option.
		 * @param array  $field              The field array.
		 * @return string The filtered HTML for the number option.
		 */
		$number_option = apply_filters( 'frm_print_format_number_option', $number_option, $number_option_text, $field );

		$add_allowed_html = function ( $allowed_html ) {
			$allowed_html['option']['data-dependency'] = true;
			return $allowed_html;
		};

		add_filter( 'frm_striphtml_allowed_tags', $add_allowed_html );
		FrmAppHelper::kses_echo( $number_option, array( 'option' ) );
		remove_filter( 'frm_striphtml_allowed_tags', $add_allowed_html );
		?>

		<?php if ( 'text' === $field_type ) { ?>
			<option value="custom" data-dependency="#frm-field-format-custom-<?php echo esc_attr( $field_id ); ?>" <?php selected( ! empty( $format ) && 'currency' !== $format, true ); ?>>
				<?php esc_html_e( 'Custom', 'formidable' ); ?>
			</option>
		<?php } ?>
	</select>
</p>

<?php
/**
 * Fires after the format dropdown template is rendered.
 *
 * @since x.x
 *
 * @param array $field The field array.
 */
do_action( 'frm_after_format_dropdown_template', $field );
