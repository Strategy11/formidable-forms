<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$frm_settings      = FrmAppHelper::get_settings();
$active_captcha    = $frm_settings->active_captcha;
$captcha_not_setup = $active_captcha === 'recaptcha' && empty( $frm_settings->pubkey ) || $active_captcha === 'hcaptcha' && empty( $frm_settings->hcaptcha_pubkey );

if ( $captcha_not_setup ) {
	$image_name = 'captcha_not_setup';
} elseif ( $active_captcha === 'recaptcha' && $frm_settings->re_type === 'v3' ) {
	$image_name = 'recaptcha_v3';
} else {
	$image_name = $active_captcha;
}

?>
<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/' . $image_name . '.png' ); ?>" class="<?php echo esc_attr( $image_name ); ?>_placeholder" alt="<?php echo esc_attr( $image_name ); ?>"/>
<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="1" />
