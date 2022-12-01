<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<p class="howto">
	<?php
	$captcha_name = $captcha === 'recaptcha' ? 'reCAPTCHA' : 'hCaptcha';
	if ( $captcha === 'recaptcha' ) {
		$captcha_name = 'reCAPTCHA';
		$captcha_api  = 'https://www.google.com/recaptcha/';
	} else {
		$captcha_name = 'hCaptcha';
		$captcha_api  = 'https://www.hcaptcha.com/signup-interstitial';
	}
	printf(
		/* translators: %1$s: Captcha name, %2$s: Start link HTML, %3$s: End link HTML */
		esc_html__( '%1$s requires a Site and Private API key. Sign up for a %2$sfree %1$s key%3$s.', 'formidable' ),
		esc_html( $captcha_name ),
		'<a href="' . esc_url( $captcha_api ) . '" target="_blank">',
		'</a>'
	);
	?>
</p>
<?php
if ( $captcha === 'recaptcha' ) {
	$prefix = '';
	$title  = __( 'reCAPTCHA is a free, accessible CAPTCHA service that helps to digitize books while blocking spam on your blog. reCAPTCHA asks commenters to retype two words scanned from a book to prove that they are a human. This verifies that they are not a spambot.', 'formidable' );
} else {
	$prefix = 'hcaptcha_';
	$title  = __( 'hCaptcha is an anti-bot solution that protects user privacy and rewards websites. It is a privacy-focused drop-in replacement for reCAPTCHA.', 'formidable' );
}
?>
<p class="frm6 frm_form_field">
	<label class="frm_help" for="frm_<?php echo esc_attr( $prefix ); ?>pubkey" title="<?php echo esc_attr( $title ); ?>">
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
