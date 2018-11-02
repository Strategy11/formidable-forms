<div id="form_global_settings" class="wrap">
	<h1><?php esc_html_e( 'Global Settings', 'formidable' ); ?></h1>

	<?php require( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' ); ?>

	<div class="metabox-holder">
		<div class="postbox">
			<div class="inside frm_license_box">
				<h2><?php esc_html_e( 'License', 'formidable' ); ?></h2>
				<hr/>
				<p class="howto"><?php esc_html_e( 'Your license key provides access to automatic updates.' ); ?></p>

				<?php do_action( 'frm_before_settings' ); ?>
			</div>
		</div>
	</div>

    <div id="poststuff" class="metabox-holder">
    <div id="post-body">
        <div class="meta-box-sortables">
        <div class="categorydiv postbox" id="frm-categorydiv">
        <div class="inside frm-help-tabs">
        <div id="contextual-help-back"></div>
        <div id="contextual-help-columns">
        <div class="contextual-help-tabs">
        <ul class="frm-category-tabs">
			<?php $a = FrmAppHelper::simple_get( 't', 'sanitize_title', 'general_settings' ); ?>
        	<li <?php echo ( $a == 'general_settings' ) ? 'class="tabs active"' : '' ?>>
				<a href="#general_settings" class="frm_cursor_pointer"><?php esc_html_e( 'General', 'formidable' ) ?></a>
			</li>
			<?php foreach ( $sections as $sec_name => $section ) { ?>
				<li <?php echo ( $a == $sec_name . '_settings' ) ? 'class="tabs active starttab"' : '' ?>>
					<a href="#<?php echo esc_attr( $sec_name ) ?>_settings" data-frmajax="<?php echo esc_attr( isset( $section['ajax'] ) ? $section['ajax'] : '' ) ?>">
						<?php echo esc_html( isset( $section['name'] ) ? $section['name'] : ucfirst( $sec_name ) ); ?>
					</a>
				</li>
            <?php } ?>
        </ul>
        </div>

	<form name="frm_settings_form" method="post" class="frm_settings_form" action="?page=formidable-settings<?php echo esc_html( $a ? '&amp;t=' . $a : '' ); ?>">
        <input type="hidden" name="frm_action" value="process-form" />
        <input type="hidden" name="action" value="process-form" />
		<?php wp_nonce_field( 'process_form_nonce', 'process_form' ); ?>

        <div class="general_settings tabs-panel <?php echo esc_attr( $a === 'general_settings' ? 'frm_block' : 'frm_hidden' ); ?>">
            <p class="submit">
				<input class="button-primary" type="submit" value="<?php esc_attr_e( 'Update Options', 'formidable' ) ?>" />
            </p>

			<h3><?php esc_html_e( 'Styling & Scripts', 'formidable' ); ?></h3>

			<p><label class="frm_left_label"><?php esc_html_e( 'Load form styling', 'formidable' ) ?></label>
                <select id="frm_load_style" name="frm_load_style">
					<option value="all" <?php selected( $frm_settings->load_style, 'all' ) ?>>
						<?php esc_html_e( 'on every page of your site', 'formidable' ) ?>
					</option>
					<option value="dynamic" <?php selected( $frm_settings->load_style, 'dynamic' ) ?>>
						<?php esc_html_e( 'only on applicable pages', 'formidable' ) ?>
					</option>
					<option value="none" <?php selected( $frm_settings->load_style, 'none' ) ?>>
						<?php esc_html_e( 'Don\'t use form styling on any page', 'formidable' ) ?>
					</option>
                </select>
            </p>

            <p>
				<label for="frm_use_html">
					<input type="checkbox" id="frm_use_html" name="frm_use_html" value="1" <?php checked( $frm_settings->use_html, 1 ); ?>> <?php esc_html_e( 'Use HTML5 in forms', 'formidable' ); ?>
				</label>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'We recommend using HTML 5 for your forms. It adds some nifty options like placeholders, patterns, and autocomplete.', 'formidable' ) ?>"></span>
            </p>

			<p>
				<label for="frm_old_css">
					<input type="checkbox" id="frm_old_css" name="frm_old_css" value="1" <?php checked( $frm_settings->old_css, 1 ); ?>>
					<?php esc_html_e( 'Do not use CSS Grids for form layouts', 'formidable' ); ?>
				</label>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Form layouts built using CSS grids that are not fully supported by older browsers like Internet Explorer. Leave this box unchecked for your layouts to look best in current browsers, but show in a single column in older browsers.', 'formidable' ); ?>"></span>
			</p>

			<?php do_action( 'frm_style_general_settings', $frm_settings ); ?>

			<h3><?php esc_html_e( 'User Permissions', 'formidable' ); ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Select users that are allowed access to Formidable. Without access to View Forms, users will be unable to see the Formidable menu.', 'formidable' ) ?>"></span>
			</h3>
            <table class="form-table">
				<?php
				foreach ( $frm_roles as $frm_role => $frm_role_description ) {
					$role_field_name = $frm_role . '[]';
					?>
                <tr>
                    <td class="frm_left_label"><label><?php echo esc_html( $frm_role_description ) ?></label></td>
                    <td><?php FrmAppHelper::wp_roles_dropdown( $role_field_name, $frm_settings->$frm_role, 'multiple' ) ?></td>
                </tr>
                <?php } ?>
            </table>

			<h3><?php esc_html_e( 'reCAPTCHA', 'formidable' ); ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'reCAPTCHA is a free, accessible CAPTCHA service that helps to digitize books while blocking spam on your blog. reCAPTCHA asks commenters to retype two words scanned from a book to prove that they are a human. This verifies that they are not a spambot.', 'formidable' ) ?>" ></span>
			</h3>

			<p class="howto">
				<?php echo wp_kses_post( sprintf( __( 'reCAPTCHA requires a Site and Private API key. Sign up for a %1$sfree reCAPTCHA key%2$s.', 'formidable' ), '<a href="' . esc_url( 'https://www.google.com/recaptcha/' ) . '" target="_blank">', '</a>' ) ); ?>
			</p>

			<p><label class="frm_left_label"><?php esc_html_e( 'Site Key', 'formidable' ) ?></label>
			<input type="text" name="frm_pubkey" id="frm_pubkey" size="42" value="<?php echo esc_attr( $frm_settings->pubkey ); ?>" placeholder="<?php esc_attr_e( 'Optional', 'formidable' ); ?>" /></p>

			<p><label class="frm_left_label"><?php esc_html_e( 'Secret Key', 'formidable' ) ?></label>
			<input type="text" name="frm_privkey" id="frm_privkey" size="42" value="<?php echo esc_attr( $frm_settings->privkey ); ?>" placeholder="<?php esc_attr_e( 'Optional', 'formidable' ); ?>" /></p>

			<p><label class="frm_left_label"><?php esc_html_e( 'reCAPTCHA Type', 'formidable' ) ?></label>
			<select name="frm_re_type" id="frm_re_type">
				<option value="" <?php selected( $frm_settings->re_type, '' ) ?>>
					<?php esc_html_e( 'Checkbox (V2)', 'formidable' ); ?>
				</option>
				<option value="invisible" <?php selected( $frm_settings->re_type, 'invisible' ) ?>>
					<?php esc_html_e( 'Invisible', 'formidable' ); ?>
				</option>
            </select></p>

			<p><label class="frm_left_label"><?php esc_html_e( 'reCAPTCHA Language', 'formidable' ) ?></label>
			<select name="frm_re_lang" id="frm_re_lang">
				<option value="" <?php selected( $frm_settings->re_lang, '' ) ?>><?php esc_html_e( 'Browser Default', 'formidable' ); ?></option>
			    <?php foreach ( $captcha_lang as $lang => $lang_name ) { ?>
				<option value="<?php echo esc_attr( $lang ); ?>" <?php selected( $frm_settings->re_lang, $lang ); ?>><?php echo esc_html( $lang_name ); ?></option>
                <?php } ?>
            </select></p>

			<p>
				<label class="frm_left_label"><?php esc_html_e( 'Multiple reCaptchas', 'formidable' ) ?></label>
				<label for="frm_re_multi">
					<input type="checkbox" name="frm_re_multi" id="frm_re_multi" value="1" <?php checked( $frm_settings->re_multi, 1 ) ?> />
					<?php esc_html_e( 'Allow multiple reCaptchas to be used on a single page', 'formidable' ) ?>
				</label>
			</p>

			<h3><?php esc_html_e( 'Default Messages', 'formidable' ); ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'You can override the success message and submit button settings on individual forms.', 'formidable' ) ?>"></span>
			</h3>

			<p>
				<label class="frm_left_label"><?php esc_html_e( 'Failed/Duplicate Entry', 'formidable' ); ?>
					<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'The message seen when a form is submitted and passes validation, but something goes wrong.', 'formidable' ) ?>" ></span>
				</label>
                <input type="text" id="frm_failed_msg" name="frm_failed_msg" class="frm_with_left_label" value="<?php echo esc_attr( $frm_settings->failed_msg ) ?>" />
			</p>

			<p>
				<label class="frm_left_label"><?php esc_html_e( 'Blank Field', 'formidable' ); ?>
					<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'The message seen when a required field is left blank.', 'formidable' ) ?>" ></span>
				</label>
				<input type="text" id="frm_blank_msg" name="frm_blank_msg" class="frm_with_left_label" value="<?php echo esc_attr( $frm_settings->blank_msg ) ?>" />
			</p>

			<p>
				<label class="frm_left_label"><?php esc_html_e( 'Incorrect Field', 'formidable' ); ?>
					<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'The message seen when a field response is either incorrect or missing.', 'formidable' ) ?>" ></span>
				</label>
				<input type="text" id="frm_invalid_msg" name="frm_invalid_msg" class="frm_with_left_label" value="<?php echo esc_attr( $frm_settings->invalid_msg ) ?>" />
			</p>

<?php if ( FrmAppHelper::pro_is_installed() ) { ?>
			<p>
				<label class="frm_left_label"><?php esc_html_e( 'Unique Value', 'formidable' ); ?>
					<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'The message seen when a user selects a value in a unique field that has already been used.', 'formidable' ) ?>" ></span>
				</label>
            	<input type="text" id="frm_unique_msg" name="frm_unique_msg" class="frm_with_left_label" value="<?php echo esc_attr( $frm_settings->unique_msg ) ?>" />
			</p>
<?php } else { ?>
			<input type="hidden" id="frm_unique_msg" name="frm_unique_msg" value="<?php echo esc_attr( $frm_settings->unique_msg ) ?>" />
			<input type="hidden" id="frm_login_msg" name="frm_login_msg" class="frm_with_left_label" value="<?php echo esc_attr( $frm_settings->login_msg ) ?>" />
<?php } ?>

		<p>
			<label class="frm_left_label"><?php esc_html_e( 'Success Message', 'formidable' ); ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'The default message seen after a form is submitted.', 'formidable' ) ?>" ></span>
			</label>
			<input type="text" id="frm_success_msg" name="frm_success_msg" class="frm_with_left_label" value="<?php echo esc_attr( $frm_settings->success_msg ); ?>" />
		</p>

		<p>
			<label class="frm_left_label"><?php esc_html_e( 'Submit Button Text', 'formidable' ); ?></label>
			<input type="text" value="<?php echo esc_attr( $frm_settings->submit_value ) ?>" id="frm_submit_value" name="frm_submit_value" class="frm_with_left_label" />
		</p>

		<?php do_action( 'frm_settings_form', $frm_settings ); ?>

        <?php if ( ! FrmAppHelper::pro_is_installed() ) { ?>
			<div class="clear"></div>
			<h3><?php esc_html_e( 'Miscellaneous', 'formidable' ) ?></h3>
			<input type="hidden" name="frm_menu" id="frm_menu" value="<?php echo esc_attr( $frm_settings->menu ) ?>" />
			<input type="hidden" name="frm_mu_menu" id="frm_mu_menu" value="<?php echo esc_attr( $frm_settings->mu_menu ) ?>" />
		<?php } ?>

		<p>
			<label class="frm_left_label"><?php esc_html_e( 'IP storage', 'formidable' ); ?></label>
			<label for="frm_no_ips">
				<input type="checkbox" name="frm_no_ips" id="frm_no_ips" value="1" <?php checked( $frm_settings->no_ips, 1 ) ?> />
				<?php esc_html_e( 'Do not store IPs with form submissions. Check this box if you are in the UK.', 'formidable' ) ?>
			</label>

		</p>

    </div>

        <?php
		foreach ( $sections as $sec_name => $section ) {
			if ( $a === $sec_name . '_settings' ) {
			?>
<style type="text/css">.<?php echo esc_attr( $sec_name ) ?>_settings{display:block;}</style><?php } ?>
			<div id="<?php echo esc_attr( $sec_name ) ?>_settings" class="<?php echo esc_attr( $sec_name ) ?>_settings tabs-panel <?php echo esc_attr( $a === $sec_name . '_settings' ? 'frm_block' : 'frm_hidden' ); ?>">
				<?php if ( isset( $section['ajax'] ) ) { ?>
					<div class="frm_ajax_settings_tab frm_<?php echo esc_attr( $sec_name ) ?>_settings_ajax">
						<span class="spinner"></span>
					</div>
					<?php
				} else {
					if ( isset( $section['class'] ) ) {
						call_user_func( array( $section['class'], $section['function'] ) );
					} else {
						call_user_func( ( isset( $section['function'] ) ? $section['function'] : $section ) );
					}
				}
				?>
            </div>
		<?php } ?>

        <p class="alignright frm_uninstall">
			<a href="javascript:void(0)" id="frm_uninstall_now"><?php esc_html_e( 'Uninstall Formidable', 'formidable' ) ?></a>
            <span class="spinner frm_spinner"></span>
        </p>
        <p class="submit">
			<input class="button-primary" type="submit" value="<?php esc_attr_e( 'Update Options', 'formidable' ) ?>" />
        </p>

    </form>
    </div>
    </div>
    </div>
    </div>
</div>

</div>

<?php do_action( 'frm_after_settings' ); ?>
</div>
