<?php
/**
 * CAPTCHA field form builder view.
 *
 * @package Formidable
 *
 * @var string $field_name HTML name attribute for the hidden input.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! FrmFieldCaptcha::should_show_captcha() ) {
	?>
<span class="frm-with-icon frm-not-set frm_note_style">
	<?php FrmAppHelper::icon_by_class( 'frmfont frm_report_problem_solid_icon' ); ?>
	<?php esc_attr_e( 'This field is not set up yet.', 'formidable' ); ?>
</span>
	<?php
	return;
}

$image_name = FrmFieldCaptcha::get_captcha_image_name();

if ( 'altcha' === $image_name && 'floating' === FrmAltchaHelper::get_settings()['display'] ) {
	// The floating layout is not shown in the field, so describe it instead of previewing a checkbox.
	?>
<span class="frm-altcha-floating-preview frm_note_style">
	<?php esc_html_e( 'ALTCHA is not shown in the form. It appears next to the Submit button while it checks the visitor.', 'formidable' ); ?>
</span>
<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="1" />
	<?php
	return;
}

$extension = 'turnstile' === $image_name ? 'gif' : 'png';
$image_url = FrmAppHelper::plugin_url() . '/images/' . $image_name . '.' . $extension;

if ( 'altcha' === $image_name ) {
	$image_url = FrmAltchaController::builder_image_url( $image_url, $image_name );
}
?>
<img src="<?php echo esc_url( $image_url ); ?>" class="<?php echo esc_attr( $image_name ); ?>_placeholder" alt="<?php echo esc_attr( $image_name ); ?>"/>
<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="1" />
