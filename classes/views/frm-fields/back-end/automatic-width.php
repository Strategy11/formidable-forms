<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm6 frm_form_field frm_multiple_cont_<?php echo absint( $field['id'] ); ?>">
	<label for="size_<?php echo esc_attr( $field['id'] ); ?>">
		<input type="checkbox" name="field_options[size_<?php echo esc_attr( $field['id'] ); ?>]" id="size_<?php echo esc_attr( $field['id'] ); ?>" value="1" <?php echo FrmField::is_option_true( $field, 'size' ) ? 'checked="checked"' : ''; ?> />
		<?php esc_html_e( 'Automatic width', 'formidable' ); ?>
	</label>
</p>
