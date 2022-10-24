<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$frm_settings      = FrmAppHelper::get_settings();
$active_captcha    = $frm_settings->active_captcha;
$captcha_not_setup = $active_captcha === 'recaptcha' && empty( $frm_settings->pubkey ) || $active_captcha === 'hcaptcha' && empty( $frm_settings->hcaptcha_pubkey );
$image_name        = $captcha_not_setup ? 'captcha_not_setup' : $frm_settings->active_captcha;
?>
<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/' . $image_name . '.png' ); ?>" class="<?php echo esc_attr( $image_name ); ?>_placeholder" alt="<?php echo esc_attr( $image_name ); ?>"/>
<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="1" />
