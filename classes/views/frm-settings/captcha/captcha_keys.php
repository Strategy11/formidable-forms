<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<p class="howto">
	<?php
	$settings = FrmCaptchaFactory::get_settings_object( $captcha );
	$prefix   = $settings->get_settings_prefix();

	printf(
		/* translators: %1$s: Captcha name, %2$s: Start link HTML, %3$s: End link HTML */
		esc_html__( '%1$s requires a Site and Private API key. Sign up for a %2$sfree %1$s key%3$s.', 'formidable' ),
		esc_html( $settings->get_name() ),
		'<a href="' . esc_url( $settings->get_documentation_url() ) . '" target="_blank">',
		'</a>'
	);
	?>
</p>
<p class="frm6 frm_form_field">
	<label class="frm_help" for="frm_<?php echo esc_attr( $prefix ); ?>pubkey" title="<?php echo esc_attr( $settings->get_site_key_tooltip() ); ?>">
		<?php esc_html_e( 'Site Key', 'formidable' ); ?>
	</label>
	<input type="text" name="frm_<?php echo esc_html( $prefix ); ?>pubkey" id="frm_<?php echo esc_html( $prefix ); ?>pubkey" size="42" value="<?php echo esc_attr( $settings->get_pubkey() ); ?>" />
</p>

<p class="frm6 frm_form_field">
	<label for="frm_<?php echo esc_attr( $prefix ); ?>privkey">
		<?php esc_html_e( 'Secret Key', 'formidable' ); ?>
	</label>
	<input type="text" name="frm_<?php echo esc_html( $prefix ); ?>privkey" id="frm_<?php echo esc_html( $prefix ); ?>privkey" size="42" value="<?php echo esc_attr( $settings->secret ); ?>" />
</p>
