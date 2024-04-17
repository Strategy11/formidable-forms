<?php
/**
 * HTML for an 'International' option in a dropdown.
 *
 * @package Formidable
 * @since 6.9
 *
 * @var array        $field Field array.
 * @var array        $args  Includes 'field', 'display', and 'values'.
 * @var FrmFieldPhone $this  Field type object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$field_id = $field['id'];
$format   = FrmField::get_option( $field, 'format' );
?>
<p class="frm6 frm6_followed frm_form_field frm-phone-type">
	<label for="phone_type_<?php echo esc_attr( $field_id ); ?>">
		<?php esc_html_e( 'Format', 'formidable' ); ?>
	</label>

	<select
		name="field_options[phone_type_<?php echo esc_attr( $field_id ); ?>]"
		id="phone_type_<?php echo esc_attr( $field_id ); ?>"
		class="frm_phone_type_dropdown frm_select_with_upgrade frm_select_with_dependency"
		data-field-id="<?php echo intval( $field_id ); ?>"
	>
		<option value="none" <?php selected( $format, '' ); ?>>
			<?php esc_html_e( 'None', 'formidable' ); ?>
		</option>
		<?php $this->print_international_option(); ?>
		<option value="custom" data-dependency="#frm-phone-field-custom-format-<?php echo esc_attr( $field_id ); ?>" <?php selected( ! empty( $format ) && 'international' !== $format, true ); ?>>
			<?php esc_html_e( 'Custom', 'formidable' ); ?>
		</option>
	</select>
</p>
