<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// Opens the collapsible settings for one captcha provider; captcha.php closes it.
// Open until both keys are saved, so a new setup shows its fields and a configured one stays out of the way.
$settings = FrmCaptchaFactory::get_settings_object( $captcha );
$keys_set = $settings->get_pubkey() && $settings->secret;
?>
<details class="frm-spam-disclosure frm-mb-sm" <?php echo $keys_set ? '' : 'open'; ?>>
	<summary>
		<svg class="frmsvg frm-spam-disclosure-chevron" aria-hidden="true" focusable="false"><use href="#frm_arrowdown6_icon"></use></svg>
		<?php
		/* translators: %s: Captcha name */
		echo esc_html( sprintf( __( '%s settings', 'formidable' ), $settings->get_name() ) );
		?>
	</summary>
