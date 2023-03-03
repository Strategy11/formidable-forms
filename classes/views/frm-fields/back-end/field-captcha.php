<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! FrmFieldCaptcha::should_show_captcha() ) {
	?>
<span class="frm-with-icon frm-not-set frm_note_style">
	<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_report_problem_solid_icon' ); ?>
	<?php esc_attr_e( 'This field is not set up yet.', 'formidable' ); ?>
</span>
	<?php
	return;
}

$image_name = FrmFieldCaptcha::get_captcha_image_name();
?>
<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/' . $image_name . '.png' ); ?>" class="<?php echo esc_attr( $image_name ); ?>_placeholder" alt="<?php echo esc_attr( $image_name ); ?>"/>
<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="1" />
