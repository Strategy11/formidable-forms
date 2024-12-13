<?php
/**
 * @package Formidable
 * @since x.x
 *
 * @var array        $field Field array.
 * @var array        $args  Includes 'field', 'display', and 'values' settings.
 * @var FrmFieldType $this  Field type object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$field_id        = $field['id'];
$field_type      = $field['type'];
$is_number_field = 'number' === $field_type;
$format          = FrmField::get_option( $field, 'format' );
?>
<p class="frm6 frm_form_field frm-format-type">
	<label for="format_type_<?php echo esc_attr( $field_id ); ?>">
		<?php esc_html_e( 'Format', 'formidable' ); ?>
	</label>

	<select
		name="field_options[format_type_<?php echo esc_attr( $field_id ); ?>]"
		id="format_type_<?php echo esc_attr( $field_id ); ?>"
		class="frm_format_type_dropdown frm_select_with_upgrade frm_select_with_dependency"
		data-field-id="<?php echo intval( $field_id ); ?>"
	>
		<option value="none" <?php selected( $format, '' ); ?>>
			<?php esc_html_e( 'None', 'formidable' ); ?>
		</option>

		<?php
		$number_option       = '
			<option value="" class="frm_show_upgrade frm_noallow" data-upgrade="' . esc_attr__( 'Format number field', 'formidable' ) . '" data-medium="format-number-field">
				' . ( ! $is_number_field ? esc_html__( 'Number', 'formidable' ) : esc_html__( 'Custom', 'formidable' ) ) . '
			</option>';

		/**
		 * Filter the number option HTML for the format dropdown.
		 *
		 * @since x.x
		 *
		 * @param string $number_option   The HTML for the number option.
		 * @param array  $field           The field array.
		 * @param string $is_number_field Indicates whether the field is a number field ('true' or 'false').
		 * @return string Filtered HTML for the number option.
		 */
		echo apply_filters( 'frm_print_format_number_option', $number_option, $field, $is_number_field ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>

		<?php if ( 'text' === $field_type ) { ?>
			<option value="custom" data-dependency="#frm-field-format-custom-<?php echo esc_attr( $field_id ); ?>" <?php selected( ! empty( $format ) && 'number' !== $format, true ); ?>>
				<?php esc_html_e( 'Custom', 'formidable' ); ?>
			</option>
		<?php } ?>
	</select>
</p>

<?php
/**
 * Fires after the format type template is rendered.
 *
 * @since x.x
 *
 * @param array $field The field array.
 */
do_action( 'frm_after_format_type_template', $field );
?>
