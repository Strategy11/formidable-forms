<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="howto">
	<?php
	printf(
		/* translators: %1$s: Start link HTML, %2$s: End link HTML */
		esc_html__( 'reCAPTCHA requires a Site and Private API key. Sign up for a %1$sfree reCAPTCHA key%2$s.', 'formidable' ),
		'<a href="' . esc_url( 'https://www.google.com/recaptcha/' ) . '" target="_blank">',
		'</a>'
	);
	?>
</p>

<div class="frm_grid_container">
<p class="frm6 frm_form_field">
	<label class="frm_help" for="frm_pubkey" title="<?php esc_attr_e( 'reCAPTCHA is a free, accessible CAPTCHA service that helps to digitize books while blocking spam on your blog. reCAPTCHA asks commenters to retype two words scanned from a book to prove that they are a human. This verifies that they are not a spambot.', 'formidable' ); ?>">
		<?php esc_html_e( 'Site Key', 'formidable' ); ?>
	</label>
	<input type="text" name="frm_pubkey" id="frm_pubkey" size="42" value="<?php echo esc_attr( $frm_settings->pubkey ); ?>" />
</p>

<p class="frm6 frm_form_field">
	<label for="frm_privkey">
		<?php esc_html_e( 'Secret Key', 'formidable' ); ?>
	</label>
	<input type="text" name="frm_privkey" id="frm_privkey" size="42" value="<?php echo esc_attr( $frm_settings->privkey ); ?>" />
</p>

<p class="frm6 frm_form_field">
	<label for="frm_re_type">
		<?php esc_html_e( 'reCAPTCHA Type', 'formidable' ); ?>
	</label>
	<select name="frm_re_type" id="frm_re_type">
		<option value="" <?php selected( $frm_settings->re_type, '' ); ?>>
			<?php esc_html_e( 'Checkbox (V2)', 'formidable' ); ?>
		</option>
		<option value="invisible" <?php selected( $frm_settings->re_type, 'invisible' ); ?>>
			<?php esc_html_e( 'Invisible', 'formidable' ); ?>
		</option>
		<option value="v3" <?php selected( $frm_settings->re_type, 'v3' ); ?>>
			<?php esc_html_e( 'v3', 'formidable' ); ?>
		</option>
	</select>
</p>

<p class="frm6 frm_form_field">
	<label for="frm_re_lang">
		<?php esc_html_e( 'reCAPTCHA Language', 'formidable' ); ?>
	</label>
	<select name="frm_re_lang" id="frm_re_lang">
		<option value="" <?php selected( $frm_settings->re_lang, '' ); ?>>
			<?php esc_html_e( 'Browser Default', 'formidable' ); ?>
		</option>
		<?php foreach ( $captcha_lang as $lang => $lang_name ) { ?>
			<option value="<?php echo esc_attr( $lang ); ?>" <?php selected( $frm_settings->re_lang, $lang ); ?>>
				<?php echo esc_html( $lang_name ); ?>
			</option>
		<?php } ?>
	</select>
</p>

<p id="frm_captcha_threshold_container" class="frm6 frm_form_field <?php echo 'v3' === $frm_settings->re_type ? '' : 'frm_hidden'; ?>">
	<label for="frm_re_type">
		<?php esc_html_e( 'reCAPTCHA Threshold', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'A score of 0 is likely to be a bot and a score of 1 is likely not a bot. Setting a lower threshold will allow more bots, but it will also stop fewer real users.', 'formidable' ); ?>"></span>
	</label>
	<span style="vertical-align:top;">0</span>
	<input name="frm_re_threshold" id="frm_re_threshold" type="range" step="0.1" max="1" min="0" value="<?php echo esc_attr( $frm_settings->re_threshold ); ?>" />
	<span style="vertical-align:top;">1</span>
</p>

<p>
	<label for="frm_re_multi">
		<input type="checkbox" name="frm_re_multi" id="frm_re_multi"
		value="1" <?php checked( $frm_settings->re_multi, 1 ); ?> />
		<?php esc_html_e( 'Allow multiple reCAPTCHAs to be used on a single page', 'formidable' ); ?>
	</label>
</p>
</div>
