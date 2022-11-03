<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<p class="howto">
	<?php
	if ( $captcha === 'recaptcha' ) {
		printf(
			/* translators: %1$s: Start link HTML, %2$s: End link HTML */
			esc_html__( 'reCAPTCHA requires a Site and Private API key. Sign up for a %1$sfree reCAPTCHA key%2$s.', 'formidable' ),
			'<a href="' . esc_url( 'https://www.google.com/recaptcha/' ) . '" target="_blank">',
			'</a>'
		);
	} else {
		printf(
			/* translators: %1$s: Start link HTML, %2$s: End link HTML */
			esc_html__( 'hCAPTCHA requires a Site and Private API key. Sign up for a %1$sfree hCAPTCHA key%2$s.', 'formidable' ),
			'<a href="' . esc_url( 'https://www.hcaptcha.com/signup-interstitial' ) . '" target="_blank">',
			'</a>'
		);
	}
	?>
</p>
<?php
$prefix = $captcha === 'recaptcha' ? '' : 'hcaptcha_';
?>
<p class="frm6 frm_form_field">
	<label class="frm_help" for="frm_<?php echo esc_attr( $prefix ); ?>pubkey" title="
	<?php
	if ( $captcha === 'recaptcha' ) {
		esc_attr_e( 'reCAPTCHA is a free, accessible CAPTCHA service that helps to digitize books while blocking spam on your blog. reCAPTCHA asks commenters to retype two words scanned from a book to prove that they are a human. This verifies that they are not a spambot.', 'formidable' );
	} else {
		esc_attr_e( 'hCaptcha is an anti-bot solution that protects user privacy and rewards websites. It is a privacy-focused drop-in replacement for reCAPTCHA.', 'formidable' );
	}
	?>">
		<?php esc_html_e( 'Site Key', 'formidable' ); ?>
	</label>
	<input type="text" name="frm_<?php echo esc_html( $prefix ); ?>pubkey" id="frm_<?php echo esc_html( $prefix ); ?>pubkey" size="42" value="<?php echo esc_attr( $frm_settings->{$prefix . 'pubkey'} ); ?>" />
</p>

<p class="frm6 frm_form_field">
	<label for="frm_<?php echo esc_attr( $prefix ); ?>privkey">
		<?php esc_html_e( 'Secret Key', 'formidable' ); ?>
	</label>
	<input type="text" name="frm_<?php echo esc_html( $prefix ); ?>privkey" id="frm_<?php echo esc_html( $prefix ); ?>privkey" size="42" value="<?php echo esc_attr( $frm_settings->{$prefix . 'privkey'} ); ?>" />
</p>
