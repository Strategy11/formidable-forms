<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$image_name = FrmFieldCaptcha::get_captcha_image_name();
?>
<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/' . $image_name . '.png' ); ?>" class="<?php echo esc_attr( $image_name ); ?>_placeholder" alt="<?php echo esc_attr( $image_name ); ?>"/>
<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="1" />
