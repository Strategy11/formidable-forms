<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="howto">
	<?php esc_html_e( 'These messages will be used by default for new forms. Many can be overridden in form or field settings.', 'formidable' ); ?>
</p>

<p>
	<label for="frm_failed_msg" class="frm_left_label"><?php esc_html_e( 'Failed/Duplicate Entry', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon"
			title="<?php esc_attr_e( 'The message seen when a form is submitted and passes validation, but something goes wrong.', 'formidable' ); ?>"></span>
	</label>
	<input type="text" id="frm_failed_msg" name="frm_failed_msg"
		class="frm_with_left_label"
		value="<?php echo esc_attr( $frm_settings->failed_msg ); ?>"/>
</p>

<p>
	<label for="frm_blank_msg" class="frm_left_label"><?php esc_html_e( 'Blank Field', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon"
			title="<?php esc_attr_e( 'The message seen when a required field is left blank.', 'formidable' ); ?>"></span>
	</label>
	<input type="text" id="frm_blank_msg" name="frm_blank_msg"
		class="frm_with_left_label"
		value="<?php echo esc_attr( $frm_settings->blank_msg ); ?>"/>
</p>

<p>
	<label for="frm_invalid_msg" class="frm_left_label"><?php esc_html_e( 'Incorrect Field', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon"
			title="<?php esc_attr_e( 'The message seen when a field response is either incorrect or missing.', 'formidable' ); ?>"></span>
	</label>
	<input type="text" id="frm_invalid_msg" name="frm_invalid_msg"
		class="frm_with_left_label"
		value="<?php echo esc_attr( $frm_settings->invalid_msg ); ?>"/>
</p>

<p>
	<label for="frm_admin_permission" class="frm_left_label"><?php esc_html_e( 'Requires Privileged Access', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon"
			title="<?php esc_attr_e( 'The message shown to users who do not have access to a resource.', 'formidable' ); ?>"></span>
	</label>
	<input type="text" id="frm_admin_permission" name="frm_admin_permission"
		class="frm_with_left_label"
		value="<?php echo esc_attr( $frm_settings->admin_permission ); ?>"/>
</p>

<?php if ( FrmAppHelper::pro_is_installed() ) { ?>
	<p>
		<label for="frm_unique_msg" class="frm_left_label"><?php esc_html_e( 'Unique Value', 'formidable' ); ?>
			<span class="frm_help frm_icon_font frm_tooltip_icon"
				title="<?php esc_attr_e( 'The message seen when a user selects a value in a unique field that has already been used.', 'formidable' ); ?>"></span>
		</label>
		<input type="text" id="frm_unique_msg" name="frm_unique_msg"
			class="frm_with_left_label"
			value="<?php echo esc_attr( $frm_settings->unique_msg ); ?>"/>
	</p>
<?php } else { ?>
	<input type="hidden" id="frm_unique_msg" name="frm_unique_msg"
		value="<?php echo esc_attr( $frm_settings->unique_msg ); ?>"/>
	<input type="hidden" id="frm_login_msg" name="frm_login_msg"
		class="frm_with_left_label"
		value="<?php echo esc_attr( $frm_settings->login_msg ); ?>"/>
<?php } ?>

<p>
	<label for="frm_success_msg" class="frm_left_label"><?php esc_html_e( 'Success Message', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon"
			title="<?php esc_attr_e( 'The default message seen after a form is submitted.', 'formidable' ); ?>"></span>
	</label>
	<input type="text" id="frm_success_msg" name="frm_success_msg"
		class="frm_with_left_label"
		value="<?php echo esc_attr( $frm_settings->success_msg ); ?>"/>
</p>

<p>
	<label for="frm_new_tab_msg" class="frm_left_label"><?php esc_html_e( 'Open In New Tab Message', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon"
			title="<?php esc_attr_e( 'The default message seen after opening the redirect URL in new tab when a form is submitted.', 'formidable' ); ?>"></span>
	</label>
	<input type="text" id="frm_new_tab_msg" name="frm_new_tab_msg"
		class="frm_with_left_label"
		value="<?php echo esc_attr( $frm_settings->new_tab_msg ); ?>"/>
</p>

<p>
	<label for="frm_submit_value" class="frm_left_label"><?php esc_html_e( 'Submit Button Text', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon"
			title="<?php esc_attr_e( 'The default label for the submit button.', 'formidable' ); ?>"></span>
	</label>
	<input type="text"
		value="<?php echo esc_attr( $frm_settings->submit_value ); ?>"
		id="frm_submit_value" name="frm_submit_value"
		class="frm_with_left_label"/>
</p>
