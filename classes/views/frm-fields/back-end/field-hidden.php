<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<input type="text" id="<?php echo esc_attr( $html_id ); ?>" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $field['default_value'] ); ?>" class="dyn_default_value" />
<p class="howto frm_clear">
	<?php esc_html_e( 'Note: This field will not show in the form. Enter the value to be hidden.', 'formidable' ); ?>
</p>
