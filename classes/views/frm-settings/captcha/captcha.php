<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm_primary_label">
	<?php esc_html_e( 'Select Captcha Type', 'formidable' ); ?>
</p>
<div class="frm_captchas frm-long-icon-buttons">
	<input type="radio" name="frm_active_captcha" id="frm-recaptcha" value="recaptcha" data-frmhide="#hcaptcha_settings,#turnstile_settings" data-frmshow="#recaptcha_settings" <?php checked( $frm_settings->active_captcha, 'recaptcha' ); ?> />
	<label for="frm-recaptcha">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 38 38"><path fill="#1C3AA9" d="M38.6 19a19.8 19.8 0 0 0 0-.7V2.8L34.3 7a19.1 19.1 0 0 0-30.1.6l7 7a9.3 9.3 0 0 1 2.9-3.1c1.2-1 3-1.8 5.3-1.8l.7.1a9.3 9.3 0 0 1 7.1 4.3l-5 5h16.4"/><path fill="#4285F4" d="M19.3 0H3l4.3 4.3a19.1 19.1 0 0 0 .5 30l7.1-7a9.3 9.3 0 0 1-3.2-2.8c-1-1.2-1.7-3-1.7-5.4v-.6a9.3 9.3 0 0 1 4.4-7.1l5 5V0"/><path fill="#ABABAB" d="M.3 19.1v16.4l4.3-4.3a19.1 19.1 0 0 0 30-.5l-7-7.2a9.3 9.3 0 0 1-2.8 3.2c-1.3 1-3 1.8-5.4 1.8a2 2 0 0 1-.7-.1 9.3 9.3 0 0 1-7-4.3l4.9-5H.3"/></svg>
		<?php esc_html_e( 'reCAPTCHA', 'formidable' ); ?>
	</label>
	<input type="radio" name="frm_active_captcha" id="frm-hcaptcha" value="hcaptcha" data-frmhide="#recaptcha_settings,#turnstile_settings" data-frmshow="#hcaptcha_settings" <?php checked( $frm_settings->active_captcha, 'hcaptcha' ); ?> />
	<label for="frm-hcaptcha">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 38 38"><path fill="#0074BF" d="M28.8 33.3H24V38h4.8v-4.8Z" opacity=".5"/><path fill="#0074BF" d="M24 33.3h-4.8V38H24v-4.8Zm-4.8 0h-4.7V38h4.8v-4.8Z" opacity=".7"/><path fill="#0074BF" d="M14.5 33.3H9.7V38h4.8v-4.8Z" opacity=".5"/><path fill="#0082BF" d="M33.5 28.5h-4.8v4.8h4.8v-4.8Z" opacity=".7"/><path fill="#0082BF" d="M28.8 28.5H24v4.8h4.8v-4.8Z" opacity=".8"/><path fill="#0082BF" d="M24 28.5h-4.8v4.8H24v-4.8Zm-4.8 0h-4.7v4.8h4.8v-4.8Z"/><path fill="#0082BF" d="M14.5 28.5H9.7v4.8h4.8v-4.8Z" opacity=".8"/><path fill="#0082BF" d="M9.8 28.5H5v4.8h4.8v-4.8Z" opacity=".7"/><path fill="#008FBF" d="M38.3 23.8h-4.8v4.7h4.8v-4.8Z" opacity=".5"/><path fill="#008FBF" d="M33.5 23.8h-4.8v4.7h4.8v-4.8Z" opacity=".8"/><path fill="#008FBF" d="M28.8 23.8H24v4.7h4.8v-4.8Zm-4.8 0h-4.8v4.7H24v-4.8Zm-4.8 0h-4.7v4.7h4.8v-4.8Zm-4.7 0H9.7v4.7h4.8v-4.8Z"/><path fill="#008FBF" d="M9.8 23.8H5v4.7h4.8v-4.8Z" opacity=".8"/><path fill="#008FBF" d="M5 23.8H.2v4.7H5v-4.8Z" opacity=".5"/><path fill="#009DBF" d="M38.3 19h-4.8v4.8h4.8V19Z" opacity=".7"/><path fill="#009DBF" d="M33.5 19h-4.8v4.8h4.8V19Zm-4.8 0H24v4.8h4.8V19ZM24 19h-4.8v4.8H24V19Zm-4.8 0h-4.7v4.8h4.8V19Zm-4.7 0H9.7v4.8h4.8V19Zm-4.8 0H5v4.8h4.8V19Z"/><path fill="#009DBF" d="M5 19H.2v4.8H5V19Z" opacity=".7"/><path fill="#00ABBF" d="M38.3 14.3h-4.8V19h4.8v-4.8Z" opacity=".7"/><path fill="#00ABBF" d="M33.5 14.3h-4.8V19h4.8v-4.8Zm-4.8 0H24V19h4.8v-4.8Zm-4.7 0h-4.8V19H24v-4.8Zm-4.8 0h-4.7V19h4.8v-4.8Zm-4.7 0H9.7V19h4.8v-4.8Zm-4.8 0H5V19h4.8v-4.8Z"/><path fill="#00ABBF" d="M5 14.3H.2V19H5v-4.8Z" opacity=".7"/><path fill="#00B9BF" d="M38.3 9.5h-4.8v4.8h4.8V9.4Z" opacity=".5"/><path fill="#00B9BF" d="M33.5 9.5h-4.8v4.8h4.8V9.4Z" opacity=".8"/><path fill="#00B9BF" d="M28.8 9.5H24v4.8h4.8V9.4Zm-4.8 0h-4.8v4.8H24V9.4Zm-4.8 0h-4.7v4.8h4.8V9.4Zm-4.7 0H9.7v4.8h4.8V9.4Z"/><path fill="#00B9BF" d="M9.8 9.5H5v4.8h4.8V9.4Z" opacity=".8"/><path fill="#00B9BF" d="M5 9.5H.2v4.8H5V9.4Z" opacity=".5"/><path fill="#00C6BF" d="M33.5 4.8h-4.8v4.7h4.8V4.7Z" opacity=".7"/><path fill="#00C6BF" d="M28.8 4.8H24v4.7h4.8V4.7Z" opacity=".8"/><path fill="#00C6BF" d="M24 4.8h-4.8v4.7H24V4.7Zm-4.8 0h-4.7v4.7h4.8V4.7Z"/><path fill="#00C6BF" d="M14.5 4.8H9.7v4.7h4.8V4.7Z" opacity=".8"/><path fill="#00C6BF" d="M9.8 4.8H5v4.7h4.8V4.7Z" opacity=".7"/><path fill="#00D4BF" d="M28.8 0H24v4.8h4.8V0Z" opacity=".5"/><path fill="#00D4BF" d="M24 0h-4.8v4.8H24V0Zm-4.8 0h-4.7v4.8h4.8V0Z" opacity=".7"/><path fill="#00D4BF" d="M14.5 0H9.7v4.8h4.8V0Z" opacity=".5"/><path fill="#fff" d="m12.8 17.5 1.3-3c.4-.7.4-1.7-.1-2.2a1 1 0 0 0-.3-.2 1.5 1.5 0 0 0-1.2-.1 2 2 0 0 0-1.1.8L8.9 19c-.7 1.9-.4 5.4 2.2 8 2.8 2.7 6.8 3.4 9.3 1.5l.3-.2 7.9-6.6c.3-.3 1-1 .4-1.7-.5-.7-1.4-.2-1.8 0l-4.5 3.3a.2.2 0 0 1-.3 0c-.1-.1-.2-.5 0-.6l7-6c.5-.5.6-1.2.1-1.8-.4-.5-1.2-.5-1.8 0l-6.2 4.9a.3.3 0 0 1-.4 0v-.1c-.1-.2-.2-.4 0-.5l7-6.9a1.4 1.4 0 0 0 .1-2 1.3 1.3 0 0 0-1-.3c-.3 0-.6.1-1 .4l-7.1 6.8c-.2.1-.5 0-.6-.2v-.2l5.6-6.3c.5-.5.6-1.4 0-2-.4-.5-1.3-.5-1.9 0l-8.4 9.3c-.3.3-.8.3-1 .2v-.5Z"/></svg>
		<?php esc_html_e( 'hCaptcha', 'formidable' ); ?>
	</label>
	<input type="radio" name="frm_active_captcha" id="frm-turnstile" value="turnstile" data-frmhide="#recaptcha_settings,#hcaptcha_settings" data-frmshow="#turnstile_settings" <?php checked( $frm_settings->active_captcha, 'turnstile' ); ?> />
	<label for="frm-turnstile">
		<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 65.6 30"><g clip-path="url(#turnstileClipPath)"><path fill="#FBAD41" d="M52.7 13H52a.4.4 0 0 0-.3.3l-1 3.3c-.3 1.4-.2 2.7.5 3.6.6.9 1.6 1.4 2.9 1.5l5 .3c.2 0 .3 0 .4.2a.5.5 0 0 1 0 .4.6.6 0 0 1-.5.4l-5.3.3c-2.8.2-5.9 2.5-7 5.3l-.3 1a.3.3 0 0 0 .2.4h18a.5.5 0 0 0 .5-.4 13.1 13.1 0 0 0 .5-3.5 13 13 0 0 0-12.9-13"/><path fill="#F6821F" d="m44.8 29.6.3-1.2c.4-1.4.3-2.7-.4-3.6a3.6 3.6 0 0 0-2.9-1.5L18.2 23a.5.5 0 0 1-.4-.2.5.5 0 0 1 0-.4.6.6 0 0 1 .5-.4l23.9-.4a8.5 8.5 0 0 0 7-5.2l1.3-3.6a1 1 0 0 0 0-.5C49 5.3 42.9 0 35.5 0c-6.9 0-12.7 4.5-14.8 10.7a7 7 0 0 0-4.9-1.4 7 7 0 0 0-6.2 6.3 7.1 7.1 0 0 0 .2 2.5A10 10 0 0 0 0 29.6a.5.5 0 0 0 .5.4h43.7a.6.6 0 0 0 .5-.4"/></g><defs><clipPath id="turnstileClipPath"><path fill="#FFF" d="M0 0h204v30H0z"/></clipPath></defs></svg>
		<?php esc_html_e( 'Turnstile', 'formidable' ); ?>
	</label>
</div>

<div class="frm_note_style frm-with-icon frm-mb-0 frm_hidden">
	<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_tooltip_icon' ); ?>
	<span><?php esc_html_e( 'Changing the captcha type here will replace it in all any forms where it is used.', 'formidable' ); ?></span>
</div>

<div id="recaptcha_settings" class="frm_grid_container <?php echo esc_attr( 'recaptcha' === $frm_settings->active_captcha ? '' : 'frm_hidden' ); ?>">
	<h3><?php esc_html_e( 'reCAPTCHA Settings', 'formidable' ); ?></h3>
	<?php
	$captcha = 'recaptcha';
	require FrmAppHelper::plugin_path() . '/classes/views/frm-settings/captcha/captcha_keys.php';
	?>

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

<div id="hcaptcha_settings" class="frm_grid_container <?php echo esc_attr( 'hcaptcha' === $frm_settings->active_captcha ? '' : 'frm_hidden' ); ?>">
	<h3><?php esc_html_e( 'hCaptcha Settings', 'formidable' ); ?></h3>
	<?php
	$captcha = 'hcaptcha';
	require FrmAppHelper::plugin_path() . '/classes/views/frm-settings/captcha/captcha_keys.php';
	?>
</div>

<div id="turnstile_settings" class="frm_grid_container <?php echo esc_attr( 'turnstile' === $frm_settings->active_captcha ? '' : 'frm_hidden' ); ?>">
	<h3><?php esc_html_e( 'Turnstile Settings', 'formidable' ); ?></h3>
	<?php
	$captcha = 'turnstile';
	require FrmAppHelper::plugin_path() . '/classes/views/frm-settings/captcha/captcha_keys.php';
	?>
</div>
