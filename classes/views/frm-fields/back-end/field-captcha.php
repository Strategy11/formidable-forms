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
$extension  = 'turnstile' === $image_name ? 'gif' : 'png';

/**
 * Filters the form builder preview image for the active captcha type.
 *
 * @since x.x
 *
 * @param string $image_url  Preview image URL.
 * @param string $image_name The active captcha type, or recaptcha_v3.
 */
$image_url = apply_filters( 'frm_captcha_builder_image_url', FrmAppHelper::plugin_url() . '/images/' . $image_name . '.' . $extension, $image_name );
?>
<img src="<?php echo esc_url( $image_url ); ?>" class="<?php echo esc_attr( $image_name ); ?>_placeholder" alt="<?php echo esc_attr( $image_name ); ?>"/>
<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="1" />
