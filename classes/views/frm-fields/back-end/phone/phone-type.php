<?php
/**
 * HTML for an 'International' option in a dropdown.
 *
 * @package Formidable
 * @since x.x
 *
 * @var array        $field Field array.
 * @var array        $args  Includes 'field', 'display', and 'values'.
 * @var FrmFieldPhone $this  Field type object.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$field_id   = $field['id'];
$phone_type = FrmField::get_option( $field, 'phone_type' );
?>
<p class="frm6 frm6_followed frm_form_field frm-phone-type">
	<label for="phone_type_<?php echo esc_attr( $field_id ); ?>">
		<?php esc_html_e( 'Format', 'formidable' ); ?>
	</label>

	<select
		name="field_options[phone_type_<?php echo esc_attr( $field_id ); ?>]"
		id="phone_type_<?php echo esc_attr( $field_id ); ?>"
		class="frm_phone_type_dropdown frm_select_with_premium frm_select_with_dependency"
		data-field-id="<?php echo intval( $field_id ); ?>"
	>
		<option value="none" <?php selected( $phone_type, 'none' ); ?>>
			<?php esc_html_e( 'None', 'formidable' ); ?>
		</option>
		<?php $this->get_international_option(); ?>
		<option value="custom" data-dependency="<?php echo esc_attr( '#frm-phone-field-custom-format-' . $field_id ); ?>" <?php selected( $phone_type, 'custom' ); ?>>
			<?php esc_html_e( 'Custom', 'formidable' ); ?>
		</option>
	</select>
</p>
