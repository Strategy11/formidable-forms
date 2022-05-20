<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$frm_settings = FrmAppHelper::get_settings();
if ( empty( $frm_settings->pubkey ) ) {
	?>
<div class="howto frm_no_captcha_text"><?php
	/* translators: %1$s: Link HTML, %2$s: End link */
	printf( esc_html__( 'Your captcha will not appear on your form until you %1$sset up%2$s the Site and Secret Keys', 'formidable' ), '<a href="?page=formidable-settings" target="_blank">', '</a>' );
?></div>
<?php } ?>
<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/recaptcha.png' ); ?>" class="recaptcha_placeholder" alt="reCaptcha"/>
<input type="hidden" name="<?php echo esc_attr( $field_name ); ?>" value="1" />
