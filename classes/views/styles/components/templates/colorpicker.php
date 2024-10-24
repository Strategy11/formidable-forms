<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<span class="frm-style-component frm-colorpicker" tabindex="0">
	<input type="text" <?php echo esc_attr( $field_name ); ?> id="<?php echo esc_attr( $component['id'] ); ?>" class="hex" value="<?php echo esc_attr( $field_value ); ?>" <?php do_action( 'frm_style_settings_input_atts', $component['action_slug'] ); ?>/>
</span>