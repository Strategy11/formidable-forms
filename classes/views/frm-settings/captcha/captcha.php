<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<h3 class="frm-settings-section-heading"><?php esc_html_e( 'Captcha', 'formidable' ); ?></h3>
<p class="frm_primary_label" id="frm_captcha_type_label">
	<?php esc_html_e( 'Select captcha type', 'formidable' ); ?>
</p>
<div class="frm_captchas frm-long-icon-buttons" role="tablist" aria-labelledby="frm_captcha_type_label">
	<input type="radio" name="frm_active_captcha" id="frm-recaptcha" value="recaptcha" data-frmhide="#hcaptcha_settings,#turnstile_settings" data-frmshow="#recaptcha_settings" <?php checked( $frm_settings->active_captcha, 'recaptcha' ); ?> />
	<label for="frm-recaptcha" id="frm-recaptcha-tab" role="tab" tabindex="<?php echo 'recaptcha' === $frm_settings->active_captcha ? '0' : '-1'; ?>" aria-selected="<?php echo 'recaptcha' === $frm_settings->active_captcha ? 'true' : 'false'; ?>" aria-controls="recaptcha_settings">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 38 38"><path fill="#1C3AA9" d="M38.6 19a19.8 19.8 0 0 0 0-.7V2.8L34.3 7a19.1 19.1 0 0 0-30.1.6l7 7a9.3 9.3 0 0 1 2.9-3.1c1.2-1 3-1.8 5.3-1.8l.7.1a9.3 9.3 0 0 1 7.1 4.3l-5 5h16.4"/><path fill="#4285F4" d="M19.3 0H3l4.3 4.3a19.1 19.1 0 0 0 .5 30l7.1-7a9.3 9.3 0 0 1-3.2-2.8c-1-1.2-1.7-3-1.7-5.4v-.6a9.3 9.3 0 0 1 4.4-7.1l5 5V0"/><path fill="#ABABAB" d="M.3 19.1v16.4l4.3-4.3a19.1 19.1 0 0 0 30-.5l-7-7.2a9.3 9.3 0 0 1-2.8 3.2c-1.3 1-3 1.8-5.4 1.8a2 2 0 0 1-.7-.1 9.3 9.3 0 0 1-7-4.3l4.9-5H.3"/></svg>
		<?php esc_html_e( 'reCAPTCHA', 'formidable' ); ?>
	</label>
	<input type="radio" name="frm_active_captcha" id="frm-hcaptcha" value="hcaptcha" data-frmhide="#recaptcha_settings,#turnstile_settings" data-frmshow="#hcaptcha_settings" <?php checked( $frm_settings->active_captcha, 'hcaptcha' ); ?> />
	<label for="frm-hcaptcha" id="frm-hcaptcha-tab" role="tab" tabindex="<?php echo 'hcaptcha' === $frm_settings->active_captcha ? '0' : '-1'; ?>" aria-selected="<?php echo 'hcaptcha' === $frm_settings->active_captcha ? 'true' : 'false'; ?>" aria-controls="hcaptcha_settings">
		<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 38 38"><path fill="#0074BF" d="M28.8 33.3H24V38h4.8v-4.8Z" opacity=".5"/><path fill="#0074BF" d="M24 33.3h-4.8V38H24v-4.8Zm-4.8 0h-4.7V38h4.8v-4.8Z" opacity=".7"/><path fill="#0074BF" d="M14.5 33.3H9.7V38h4.8v-4.8Z" opacity=".5"/><path fill="#0082BF" d="M33.5 28.5h-4.8v4.8h4.8v-4.8Z" opacity=".7"/><path fill="#0082BF" d="M28.8 28.5H24v4.8h4.8v-4.8Z" opacity=".8"/><path fill="#0082BF" d="M24 28.5h-4.8v4.8H24v-4.8Zm-4.8 0h-4.7v4.8h4.8v-4.8Z"/><path fill="#0082BF" d="M14.5 28.5H9.7v4.8h4.8v-4.8Z" opacity=".8"/><path fill="#0082BF" d="M9.8 28.5H5v4.8h4.8v-4.8Z" opacity=".7"/><path fill="#008FBF" d="M38.3 23.8h-4.8v4.7h4.8v-4.8Z" opacity=".5"/><path fill="#008FBF" d="M33.5 23.8h-4.8v4.7h4.8v-4.8Z" opacity=".8"/><path fill="#008FBF" d="M28.8 23.8H24v4.7h4.8v-4.8Zm-4.8 0h-4.8v4.7H24v-4.8Zm-4.8 0h-4.7v4.7h4.8v-4.8Zm-4.7 0H9.7v4.7h4.8v-4.8Z"/><path fill="#008FBF" d="M9.8 23.8H5v4.7h4.8v-4.8Z" opacity=".8"/><path fill="#008FBF" d="M5 23.8H.2v4.7H5v-4.8Z" opacity=".5"/><path fill="#009DBF" d="M38.3 19h-4.8v4.8h4.8V19Z" opacity=".7"/><path fill="#009DBF" d="M33.5 19h-4.8v4.8h4.8V19Zm-4.8 0H24v4.8h4.8V19ZM24 19h-4.8v4.8H24V19Zm-4.8 0h-4.7v4.8h4.8V19Zm-4.7 0H9.7v4.8h4.8V19Zm-4.8 0H5v4.8h4.8V19Z"/><path fill="#009DBF" d="M5 19H.2v4.8H5V19Z" opacity=".7"/><path fill="#00ABBF" d="M38.3 14.3h-4.8V19h4.8v-4.8Z" opacity=".7"/><path fill="#00ABBF" d="M33.5 14.3h-4.8V19h4.8v-4.8Zm-4.8 0H24V19h4.8v-4.8Zm-4.7 0h-4.8V19H24v-4.8Zm-4.8 0h-4.7V19h4.8v-4.8Zm-4.7 0H9.7V19h4.8v-4.8Zm-4.8 0H5V19h4.8v-4.8Z"/><path fill="#00ABBF" d="M5 14.3H.2V19H5v-4.8Z" opacity=".7"/><path fill="#00B9BF" d="M38.3 9.5h-4.8v4.8h4.8V9.4Z" opacity=".5"/><path fill="#00B9BF" d="M33.5 9.5h-4.8v4.8h4.8V9.4Z" opacity=".8"/><path fill="#00B9BF" d="M28.8 9.5H24v4.8h4.8V9.4Zm-4.8 0h-4.8v4.8H24V9.4Zm-4.8 0h-4.7v4.8h4.8V9.4Zm-4.7 0H9.7v4.8h4.8V9.4Z"/><path fill="#00B9BF" d="M9.8 9.5H5v4.8h4.8V9.4Z" opacity=".8"/><path fill="#00B9BF" d="M5 9.5H.2v4.8H5V9.4Z" opacity=".5"/><path fill="#00C6BF" d="M33.5 4.8h-4.8v4.7h4.8V4.7Z" opacity=".7"/><path fill="#00C6BF" d="M28.8 4.8H24v4.7h4.8V4.7Z" opacity=".8"/><path fill="#00C6BF" d="M24 4.8h-4.8v4.7H24V4.7Zm-4.8 0h-4.7v4.7h4.8V4.7Z"/><path fill="#00C6BF" d="M14.5 4.8H9.7v4.7h4.8V4.7Z" opacity=".8"/><path fill="#00C6BF" d="M9.8 4.8H5v4.7h4.8V4.7Z" opacity=".7"/><path fill="#00D4BF" d="M28.8 0H24v4.8h4.8V0Z" opacity=".5"/><path fill="#00D4BF" d="M24 0h-4.8v4.8H24V0Zm-4.8 0h-4.7v4.8h4.8V0Z" opacity=".7"/><path fill="#00D4BF" d="M14.5 0H9.7v4.8h4.8V0Z" opacity=".5"/><path fill="#fff" d="m12.8 17.5 1.3-3c.4-.7.4-1.7-.1-2.2a1 1 0 0 0-.3-.2 1.5 1.5 0 0 0-1.2-.1 2 2 0 0 0-1.1.8L8.9 19c-.7 1.9-.4 5.4 2.2 8 2.8 2.7 6.8 3.4 9.3 1.5l.3-.2 7.9-6.6c.3-.3 1-1 .4-1.7-.5-.7-1.4-.2-1.8 0l-4.5 3.3a.2.2 0 0 1-.3 0c-.1-.1-.2-.5 0-.6l7-6c.5-.5.6-1.2.1-1.8-.4-.5-1.2-.5-1.8 0l-6.2 4.9a.3.3 0 0 1-.4 0v-.1c-.1-.2-.2-.4 0-.5l7-6.9a1.4 1.4 0 0 0 .1-2 1.3 1.3 0 0 0-1-.3c-.3 0-.6.1-1 .4l-7.1 6.8c-.2.1-.5 0-.6-.2v-.2l5.6-6.3c.5-.5.6-1.4 0-2-.4-.5-1.3-.5-1.9 0l-8.4 9.3c-.3.3-.8.3-1 .2v-.5Z"/></svg>
		<?php esc_html_e( 'hCaptcha', 'formidable' ); ?>
	</label>
	<input type="radio" name="frm_active_captcha" id="frm-turnstile" value="turnstile" data-frmhide="#recaptcha_settings,#hcaptcha_settings" data-frmshow="#turnstile_settings" <?php checked( $frm_settings->active_captcha, 'turnstile' ); ?> />
	<label for="frm-turnstile" id="frm-turnstile-tab" role="tab" tabindex="<?php echo 'turnstile' === $frm_settings->active_captcha ? '0' : '-1'; ?>" aria-selected="<?php echo 'turnstile' === $frm_settings->active_captcha ? 'true' : 'false'; ?>" aria-controls="turnstile_settings">
		<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 65.6 30"><g clip-path="url(#turnstileClipPath)"><path fill="#FBAD41" d="M52.7 13H52a.4.4 0 0 0-.3.3l-1 3.3c-.3 1.4-.2 2.7.5 3.6.6.9 1.6 1.4 2.9 1.5l5 .3c.2 0 .3 0 .4.2a.5.5 0 0 1 0 .4.6.6 0 0 1-.5.4l-5.3.3c-2.8.2-5.9 2.5-7 5.3l-.3 1a.3.3 0 0 0 .2.4h18a.5.5 0 0 0 .5-.4 13.1 13.1 0 0 0 .5-3.5 13 13 0 0 0-12.9-13"/><path fill="#F6821F" d="m44.8 29.6.3-1.2c.4-1.4.3-2.7-.4-3.6a3.6 3.6 0 0 0-2.9-1.5L18.2 23a.5.5 0 0 1-.4-.2.5.5 0 0 1 0-.4.6.6 0 0 1 .5-.4l23.9-.4a8.5 8.5 0 0 0 7-5.2l1.3-3.6a1 1 0 0 0 0-.5C49 5.3 42.9 0 35.5 0c-6.9 0-12.7 4.5-14.8 10.7a7 7 0 0 0-4.9-1.4 7 7 0 0 0-6.2 6.3 7.1 7.1 0 0 0 .2 2.5A10 10 0 0 0 0 29.6a.5.5 0 0 0 .5.4h43.7a.6.6 0 0 0 .5-.4"/></g><defs><clipPath id="turnstileClipPath"><path fill="#FFF" d="M0 0h204v30H0z"/></clipPath></defs></svg>
		<?php esc_html_e( 'Turnstile', 'formidable' ); ?>
	</label>
</div>

<div class="frm_note_style frm-with-icon frm-mb-0 frm_hidden">
	<?php FrmAppHelper::icon_by_class( 'frmfont frm_tooltip_icon' ); ?>
	<span><?php esc_html_e( 'Changing the captcha type here will replace it in every form that uses it.', 'formidable' ); ?></span>
</div>

<?php
$captcha_settings_attrs = array(
	'id'              => 'recaptcha_settings',
	'role'            => 'tabpanel',
	'aria-labelledby' => 'frm-recaptcha-tab',
);

if ( 'recaptcha' !== $frm_settings->active_captcha ) {
	$captcha_settings_attrs['class'] = 'frm_hidden';
}
?>
<div<?php FrmAppHelper::array_to_html_params( $captcha_settings_attrs, true ); ?>>
	<?php
	$captcha = 'recaptcha';
	require FrmAppHelper::plugin_path() . '/classes/views/frm-settings/captcha/captcha_summary.php';
	?>
	<div class="frm_grid_container">
		<?php
		require FrmAppHelper::plugin_path() . '/classes/views/frm-settings/captcha/captcha_keys.php';
		?>

		<p class="frm6 frm_form_field">
			<label for="frm_re_type">
				<?php esc_html_e( 'reCAPTCHA type', 'formidable' ); ?>
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
				<?php esc_html_e( 'reCAPTCHA language', 'formidable' ); ?>
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
			<label for="frm_re_threshold">
				<?php esc_html_e( 'reCAPTCHA threshold', 'formidable' ); ?>
				<?php FrmAppHelper::tooltip_icon( __( 'For reCAPTCHA v3, scores range from 0.0 for likely bots to 1.0 for likely humans. A higher threshold blocks more submissions. The default is 0.5.', 'formidable' ), array( 'data-container' => 'body' ) ); ?>
			</label>
			<input name="frm_re_threshold" id="frm_re_threshold" class="widefat" type="number" step="0.1" max="1" min="0" value="<?php echo esc_attr( $frm_settings->re_threshold ); ?>" />
		</p>

		<p>
			<label>
				<input type="checkbox" name="frm_re_multi" id="frm_re_multi"
				value="1" <?php checked( $frm_settings->re_multi, 1 ); ?> />
				<?php esc_html_e( 'Allow multiple reCAPTCHAs to be used on a single page', 'formidable' ); ?>
			</label>
		</p>
	</div>
	</details>
</div>

<?php
$captcha_settings_attrs = array(
	'id'              => 'hcaptcha_settings',
	'role'            => 'tabpanel',
	'aria-labelledby' => 'frm-hcaptcha-tab',
);

if ( 'hcaptcha' !== $frm_settings->active_captcha ) {
	$captcha_settings_attrs['class'] = 'frm_hidden';
}
?>
<div<?php FrmAppHelper::array_to_html_params( $captcha_settings_attrs, true ); ?>>
	<?php
	$captcha = 'hcaptcha';
	require FrmAppHelper::plugin_path() . '/classes/views/frm-settings/captcha/captcha_summary.php';
	?>
	<div class="frm_grid_container">
		<?php
		require FrmAppHelper::plugin_path() . '/classes/views/frm-settings/captcha/captcha_keys.php';
		?>
	</div>
	</details>
</div>

<?php
$captcha_settings_attrs = array(
	'id'              => 'turnstile_settings',
	'role'            => 'tabpanel',
	'aria-labelledby' => 'frm-turnstile-tab',
);

if ( 'turnstile' !== $frm_settings->active_captcha ) {
	$captcha_settings_attrs['class'] = 'frm_hidden';
}
?>
<div<?php FrmAppHelper::array_to_html_params( $captcha_settings_attrs, true ); ?>>
	<?php
	$captcha = 'turnstile';
	require FrmAppHelper::plugin_path() . '/classes/views/frm-settings/captcha/captcha_summary.php';
	?>
	<div class="frm_grid_container">
		<?php
		require FrmAppHelper::plugin_path() . '/classes/views/frm-settings/captcha/captcha_keys.php';
		?>
	</div>
	</details>
</div>

<h3 class="frm-settings-section-heading"><?php esc_html_e( 'Spam checks', 'formidable' ); ?></h3>
<?php
$spam_handling        = FrmSpamEntriesHelper::sanitize_handling( $frm_settings->spam_handling );
$akismet_active       = function_exists( 'akismet_http_post' );
$hidden_spam_sources  = $akismet_active ? array() : array( 'akismet', 'akismet_discard' );
$optional_sources     = FrmSpamEntriesHelper::get_optional_sources();
$spam_source_tooltips = array(
	'captcha'             => __( 'Choose whether CAPTCHA failures prevent submission or allow the form to be submitted as a spam entry. Saved spam entries include the CAPTCHA error details.', 'formidable' ),
	'honeypot'            => __( 'Detect bots that fill out an invisible field meant to stay empty.', 'formidable' ),
	'antispam'            => __( 'Detect submissions that fail the JavaScript anti-spam check, such as bots that do not run JavaScript.', 'formidable' ),
	'no_ip'               => __( 'Choose how to handle submissions when the submitter IP address cannot be detected.', 'formidable' ),
	'akismet'             => __( 'Akismet flagged this submission as spam, but it may be a false positive. Save it as a spam entry to review it later, or reject it with an error message.', 'formidable' ),
	'akismet_discard'     => __( 'Akismet marked this submission as highly likely to be spam and recommended discarding it. Reject it with an error message without saving an entry, or save it as a spam entry if you prefer to review it.', 'formidable' ),
	'denylist'            => __( 'Check submitted text against the built-in list of spam words and your custom disallowed words.', 'formidable' ),
	'wp_disallowed_words' => __( 'Choose how to handle submissions matching the Disallowed Comment Keys in WordPress Settings > Discussion.', 'formidable' ),
	'wp_comments'         => __( 'Match the submitter IP address, email, or website against recent WordPress comments marked as spam.', 'formidable' ),
	'stopforumspam'       => __( 'Choose how to handle submissions with an IP address or email flagged by the StopForumSpam service.', 'formidable' ),
);
?>
<details class="frm-spam-disclosure frm-mb-sm">
	<summary>
		<svg class="frmsvg frm-spam-disclosure-chevron" aria-hidden="true" focusable="false"><use href="#frm_arrowdown6_icon"></use></svg>
		<?php esc_html_e( 'Customize how spam submissions are handled', 'formidable' ); ?>
	</summary>
	<p class="howto">
		<?php esc_html_e( 'Choose which checks run and what happens when one flags a new submission.', 'formidable' ); ?>
	</p>
	<div class="frm_grid_container frm-spam-handling-settings">
		<?php
		// Checks that can be turned off go last, after the ones that always run.
		$spam_sources = FrmSpamEntriesHelper::get_sources();
		$spam_sources = array_diff_key( $spam_sources, $optional_sources ) + array_intersect_key( $spam_sources, $optional_sources );

		foreach ( $spam_sources as $spam_source => $spam_source_details ) {
			?>
			<?php
			$handling_name = 'frm_spam_handling[' . $spam_source . ']';

			if ( in_array( $spam_source, $hidden_spam_sources, true ) ) {
				?>
				<input type="hidden" name="<?php echo esc_attr( $handling_name ); ?>" value="<?php echo esc_attr( $spam_handling[ $spam_source ] ); ?>" />
				<?php
				continue;
			}

			$select_id = 'frm_spam_handling_' . $spam_source;
			$setting   = $optional_sources[ $spam_source ] ?? '';
			$is_off    = $setting && ! $frm_settings->$setting;
			?>
			<p class="frm6 frm_form_field">
				<?php if ( $setting ) { ?>
					<label>
						<input type="checkbox" name="frm_<?php echo esc_attr( $setting ); ?>" value="1" data-spam-check="<?php echo esc_attr( $select_id ); ?>" <?php checked( ! $is_off ); ?> />
						<span id="<?php echo esc_attr( $select_id ); ?>_label"><?php echo esc_html( $spam_source_details['label'] ); ?></span>
						<?php
						if ( isset( $spam_source_tooltips[ $spam_source ] ) ) {
							FrmAppHelper::tooltip_icon( $spam_source_tooltips[ $spam_source ], array( 'data-container' => 'body' ) );
						}
						?>
					</label>
				<?php } else { ?>
					<label for="<?php echo esc_attr( $select_id ); ?>">
						<?php
						echo esc_html( $spam_source_details['label'] );

						if ( isset( $spam_source_tooltips[ $spam_source ] ) ) {
							FrmAppHelper::tooltip_icon( $spam_source_tooltips[ $spam_source ], array( 'data-container' => 'body' ) );
						}
						?>
					</label>
				<?php
				}//end if
 ?>
			</p>
			<p class="frm6 frm_form_field">
				<select id="<?php echo esc_attr( $select_id ); ?>" name="<?php echo esc_attr( $handling_name ); ?>" <?php echo $setting ? 'aria-labelledby="' . esc_attr( $select_id ) . '_label"' : ''; ?> <?php disabled( $is_off ); ?> class="<?php echo $is_off ? 'frm_hidden' : ''; ?>">
					<option value="<?php echo esc_attr( FrmSpamEntriesHelper::SAVE ); ?>" <?php selected( $spam_handling[ $spam_source ], FrmSpamEntriesHelper::SAVE ); ?>>
						<?php esc_html_e( 'Save as a spam entry', 'formidable' ); ?>
					</option>
					<option value="<?php echo esc_attr( FrmSpamEntriesHelper::BLOCK ); ?>" <?php selected( $spam_handling[ $spam_source ], FrmSpamEntriesHelper::BLOCK ); ?>>
						<?php esc_html_e( 'Reject with an error message', 'formidable' ); ?>
					</option>
				</select>
			</p>
		<?php
		}//end foreach
 ?>
	</div>
</details>

<div class="frm-denylist-settings <?php echo $frm_settings->denylist_check ? '' : 'frm_hidden'; ?>">
	<details class="frm-spam-disclosure frm-mb-sm">
		<summary>
			<svg class="frmsvg frm-spam-disclosure-chevron" aria-hidden="true" focusable="false"><use href="#frm_arrowdown6_icon"></use></svg>
			<?php esc_html_e( 'Customize denylist words', 'formidable' ); ?>
		</summary>
		<p class="howto">
			<?php esc_html_e( 'Enter one word or phrase per line. Allowed words are never flagged, even when they are on the denylist.', 'formidable' ); ?>
		</p>
		<p>
			<label for="frm-disallowed-words">
				<?php esc_html_e( 'Custom disallowed words', 'formidable' ); ?>
			</label>
			<textarea id="frm-disallowed-words" name="frm_disallowed_words"><?php echo esc_textarea( $frm_settings->disallowed_words ); ?></textarea>
		</p>

		<p>
			<label for="frm-allowed-words">
				<?php esc_html_e( 'Custom allowed words', 'formidable' ); ?>
			</label>
			<textarea id="frm-allowed-words" name="frm_allowed_words"><?php echo esc_textarea( $frm_settings->allowed_words ); ?></textarea>
		</p>

		<?php
		$transient = get_transient( 'frm_recent_spam_detected' );

		if ( $transient ) {
			?>
			<div class="frm_note_style">
				<strong><?php esc_html_e( 'Spam keywords detected recently:', 'formidable' ); ?></strong>
				<i><?php echo esc_html( implode( ', ', $transient ) ); ?></i>
			</div>
			<?php
		}
		?>
	</details>
</div><!-- End .frm-denylist-settings -->

<?php
if ( $akismet_active ) {
	?>
	<div class="frm-spam-integration">
		<div class="frm-spam-integration-body">
			<svg class="frm-spam-integration-logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 44 44" aria-hidden="true" focusable="false"><rect width="44" height="44" fill="#1858D8" rx="6"/><path fill="#fff" fill-rule="evenodd" d="m29.746 28.31-6.392-16.797c-.152-.397-.305-.672-.789-.675-.673 0-1.408.611-1.746 1.316l-7.378 16.154c-.072.16-.143.311-.214.454-.5.995-1.045 1.546-2.357 1.626a.399.399 0 0 0-.16.033l-.01.004a.399.399 0 0 0-.23.392v.01c0 .054.01.106.03.155l.004.01a.416.416 0 0 0 .394.252h6.212a.417.417 0 0 0 .307-.12.416.416 0 0 0 .124-.305.398.398 0 0 0-.105-.302.399.399 0 0 0-.294-.127c-.757 0-2.197-.062-2.197-1.164.02-.318.103-.63.245-.916l1.399-3.152c.52-1.163 1.654-1.163 2.572-1.163h5.843c.023 0 .044 0 .062.003.13.014.16.081.214.242l1.534 4.07a2.857 2.857 0 0 1 .216 1.04c0 .054-.003.104-.01.153-.09.726-.831.887-1.49.887a.4.4 0 0 0-.294.127l-.007.008-.007.008a.401.401 0 0 0-.092.286v.01c0 .054.01.106.03.155l.005.01a.42.42 0 0 0 .395.252h7.011a.413.413 0 0 0 .279-.13.412.412 0 0 0 .11-.297.387.387 0 0 0-.09-.294.388.388 0 0 0-.277-.135c-1.448-.122-2.295-.643-2.847-2.08Zm-11.985-5.844 2.847-6.304c.361-.728.659-1.486.889-2.265 0-.06.03-.092.06-.092s.061.032.061.091c.02.122.045.247.073.374.197.888.584 1.878.914 2.723l.176.453 1.684 4.529a.927.927 0 0 1 .092.4.473.473 0 0 1-.009.094c-.041.202-.228.272-.602.272h-6.063c-.122 0-.184-.03-.184-.092a.36.36 0 0 1 .062-.183Zm17.107-.721c0 .786-.446 1.231-1.25 1.231-.806 0-1.125-.409-1.125-1.034 0-.786.465-1.231 1.25-1.231.785 0 1.125.427 1.125 1.034ZM9.629 23.002c.803 0 1.25-.447 1.25-1.231 0-.607-.343-1.036-1.128-1.036-.785 0-1.25.447-1.25 1.231 0 .625.325 1.036 1.128 1.036Z" clip-rule="evenodd"/></svg>
			<div class="frm-spam-integration-text">
				<span class="frm-spam-integration-name"><?php esc_html_e( 'Akismet', 'formidable' ); ?></span>
				<p><?php esc_html_e( 'Formidable Forms works with Akismet. Enable it in the Spam settings for each form.', 'formidable' ); ?></p>
			</div>
			<a href="<?php echo esc_url( FrmAppHelper::get_doc_url( 'add-spam-protection', 'spam-settings' ) . '#kb-akismet' ); ?>" class="frm-with-icon" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'View docs', 'formidable' ); ?>
				<span class="screen-reader-text"><?php esc_html_e( 'for Akismet (opens in a new tab)', 'formidable' ); ?></span>
				<svg class="frmsvg" aria-hidden="true" focusable="false"><use href="#frm_external_link_icon"></use></svg>
			</a>
		</div>
	</div>
	<?php
}
?>

<h3 class="frm-settings-section-heading frm-spam-entries-heading"><?php esc_html_e( 'Spam entries', 'formidable' ); ?></h3>
<p class="howto">
	<?php esc_html_e( 'Saved spam entries go to the Spam tab of the entries list. They never trigger form actions, and the submitter sees the normal success message.', 'formidable' ); ?>
</p>
<?php
$incompatible_addons = FrmSpamEntriesHelper::get_incompatible_addons();

if ( $incompatible_addons ) {
	?>
	<div class="frm_warning_style frm-spam-entries-warning">
		<?php
		printf(
			/* translators: %s: Names of active Formidable add-ons that need an update. */
			esc_html__( 'Update %s to save spam entries. Until then, flagged submissions are rejected.', 'formidable' ),
			esc_html( wp_sprintf_l( '%l', $incompatible_addons ) )
		);
		?>
	</div>
	<?php
}
?>

<?php
if ( FrmAppHelper::pro_is_installed() ) {
	/**
	 * Fires after the spam entries settings, so Pro can add the spam cleanup setting.
	 *
	 * @since x.x
	 *
	 * @param FrmSettings $frm_settings
	 */
	do_action( 'frm_spam_entries_settings', $frm_settings );
	return;
}

?>
<div class="frm_grid_container">
	<p class="frm6 frm_form_field">
		<label for="frm_spam_retention_days_upsell">
			<?php esc_html_e( 'Delete spam entries automatically', 'formidable' ); ?>
			<?php FrmAppHelper::tooltip_icon( __( 'Automatically delete saved spam entries after the selected number of days. This requires Formidable Pro.', 'formidable' ), array( 'data-container' => 'body' ) ); ?>
		</label>
		<span class="frm-spam-retention-upsell">
			<select id="frm_spam_retention_days_upsell" disabled>
				<option><?php esc_html_e( 'After 30 days', 'formidable' ); ?></option>
			</select>
			<a href="#" class="frm_show_upgrade" data-upgrade="<?php esc_attr_e( 'Automatic spam cleanup', 'formidable' ); ?>" data-medium="spam-cleanup">
				<?php esc_html_e( 'Upgrade to Pro', 'formidable' ); ?>
			</a>
		</span>
	</p>
</div>
