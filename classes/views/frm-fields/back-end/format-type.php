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

$field_id = $field['id'];
$format   = FrmField::get_option( $field, 'format' );
?>
<p class="frm6 frm6_followed frm_form_field frm-format-type">
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

		<?php FrmFieldFactory::get_field_type( $field['type'] )->print_format_number_option( $field ); ?>

		<option value="custom" data-dependency="#frm-field-format-custom-<?php echo esc_attr( $field_id ); ?>" <?php selected( ! empty( $format ) && 'number' !== $format, true ); ?>>
			<?php esc_html_e( 'Custom', 'formidable' ); ?>
		</option>
	</select>
</p>
