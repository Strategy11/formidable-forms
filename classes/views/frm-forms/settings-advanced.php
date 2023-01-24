<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="howto">
	<?php esc_html_e( 'Modify the basic form settings here.', 'formidable' ); ?>
</p>

<div class="frm_grid_container">
	<p class="frm6 frm_form_field">
		<label for="frm_form_name">
			<?php esc_html_e( 'Form Title', 'formidable' ); ?>
		</label>
		<input type="text" id="frm_form_name" name="name" value="<?php echo esc_attr( $values['name'] ); ?>" />
	</p>

	<p class="frm6 frm_form_field">
		<label for="frm_form_key">
			<?php esc_html_e( 'Form Key', 'formidable' ); ?>
		</label>
		<input type="text" id="frm_form_key" name="form_key" value="<?php echo esc_attr( $values['form_key'] ); ?>" />
	</p>

	<p>
		<label for="frm_form_description">
			<?php esc_html_e( 'Form Description', 'formidable' ); ?>
		</label>
		<textarea id="frm_form_description" name="description" cols="50" rows="4"><?php echo FrmAppHelper::esc_textarea( $values['description'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></textarea>
	</p>

	<p class="frm8 frm_form_field">
		<label for="show_title" class="frm_inline_block">
			<input type="checkbox" name="options[show_title]" id="show_title" value="1" <?php checked( $values['show_title'], 1 ); ?> />
			<?php esc_html_e( 'Show the form title', 'formidable' ); ?>
		</label>
	</p>

	<p class="frm8 frm_form_field">
		<label for="show_description" class="frm_inline_block">
			<input type="checkbox" name="options[show_description]" id="show_description" value="1" <?php checked( $values['show_description'], 1 ); ?> />
			<?php esc_html_e( 'Show the form description', 'formidable' ); ?>
		</label>
	</p>

<?php if ( ! $values['is_template'] ) { ?>
	<?php $first_h3 = ''; ?>

	<?php if ( has_action( 'frm_settings_buttons' ) ) { ?>
		<h3 class="<?php echo esc_attr( $first_h3 ); ?>">
			<?php esc_html_e( 'Form Settings', 'formidable' ); ?>
		</h3>
		<div class="misc-pub-section">
			<?php do_action( 'frm_settings_buttons', $values ); ?>
			<div class="clear"></div>
		</div>
		<?php $first_h3 = ''; ?>
	<?php } ?>
<?php } ?>

<h3 class="<?php echo esc_attr( $first_h3 ); ?>">
	<?php esc_html_e( 'On Submit', 'formidable' ); ?>
	<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Choose what will happen after the user submits this form.', 'formidable' ); ?>"></span>
</h3>

<?php
// Show a temporary message. This can be removed after the date is passed.
if ( time() < strtotime( '2023-03-07' ) ) {
	FrmTipsHelper::show_tip(
		array(
			'tip'  => __( 'New: The actions after submit have moved.', 'formidable' ),
			'call' => __( 'Go to the Form Actions', 'formidable' ),
			'page' => add_query_arg( 't', 'email_settings' ),
		),
		'p'
	);
}
?>

<p class="frm8 frm_form_field">
	<label for="no_save" class="frm_inline_block">
		<input type="checkbox" name="options[no_save]" id="no_save" value="1" <?php checked( $values['no_save'], 1 ); ?> />
		<?php esc_html_e( 'Do not store entries submitted from this form', 'formidable' ); ?>
	</label>
</p>

<?php
is_callable( 'self::render_spam_settings' ) && self::render_spam_settings( $values );
FrmTipsHelper::pro_tip( 'get_form_settings_tip', 'p' );
?>

<!--AJAX Section-->
<h3><?php esc_html_e( 'AJAX', 'formidable' ); ?>
	<span class="frm_help frm_icon_font frm_tooltip_icon" data-placement="right" title="<?php esc_attr_e( 'Make stuff happen in the background without a page refresh', 'formidable' ); ?>" ></span>
</h3>

<table class="form-table">
	<tr>
		<td>
			<label for="ajax_load" class="frm_inline_block">
				<input type="checkbox" name="options[ajax_load]" id="ajax_load" value="1"<?php echo ( $values['ajax_load'] ) ? ' checked="checked"' : ''; ?> /> <?php esc_html_e( 'Load and save form builder page with AJAX', 'formidable' ); ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Recommended for long forms.', 'formidable' ); ?>"></span>
			</label>
		</td>
	</tr>
	<?php do_action( 'frm_add_form_ajax_options', $values ); ?>
	<?php if ( ! FrmAppHelper::pro_is_installed() ) { ?>
		<tr>
			<td>
				<label data-upgrade="<?php esc_attr_e( 'AJAX Form Submissions', 'formidable' ); ?>" data-medium="ajax" data-message="<?php esc_attr_e( 'Want to submit forms without reloading the page?', 'formidable' ); ?>">
					<input type="checkbox" disabled="disabled" />
					<span class="frm_noallow"><?php esc_html_e( 'Submit this form with AJAX', 'formidable' ); ?></span>
					<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Submit the form without refreshing the page.', 'formidable' ); ?>"></span>
				</label>
			</td>
		</tr>
	<?php } ?>
	<tr>
		<td>
			<label for="js_validate" class="frm_inline_block">
				<input type="checkbox" name="options[js_validate]" id="js_validate" value="1" <?php checked( $values['js_validate'], 1 ); ?> />
				<?php esc_html_e( 'Validate this form with javascript', 'formidable' ); ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Required fields, email format, and number format can be checked instantly in your browser. You may want to turn this option off if you have any customizations to remove validation messages on certain fields.', 'formidable' ); ?>" data-container="body"></span>
			</label>
		</td>
	</tr>
</table>

<!--Permissions Section-->
<?php do_action( 'frm_add_form_perm_options', $values ); ?>

<!--Message Section-->
<h3 id="frm_messages_header" class="<?php echo esc_attr( ( ( isset( $values['edit_action'] ) && $values['edit_action'] === 'message' && isset( $values['editable'] ) && $values['editable'] == 1 ) || $values['success_action'] === 'message' || isset( $values['save_draft'] ) && $values['save_draft'] == 1 ) ? '' : 'frm_hidden' ); ?>">
	<?php esc_html_e( 'Messages', 'formidable' ); ?>
	<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Set up your confirmation messages.', 'formidable' ); ?>" ></span>
</h3>
<table class="form-table frm-fields">
	<tr class="success_action_message_box success_action_box<?php echo esc_attr( $values['success_action'] === 'message' ? '' : ' frm_hidden' ); ?>">
		<td class="frm_has_shortcodes frm_has_textarea">
			<label for="success_msg"><?php esc_html_e( 'On Submit', 'formidable' ); ?></label>
			<textarea id="success_msg" name="options[success_msg]" cols="50" rows="2" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea( $values['success_msg'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></textarea>
		</td>
	</tr>
	<?php do_action( 'frm_add_form_msg_options', $values ); ?>
</table>

<!--Misc Section-->
<?php if ( has_action( 'frm_additional_form_options' ) ) { ?>
<h3><?php esc_html_e( 'Miscellaneous', 'formidable' ); ?></h3>
<table class="form-table">
	<?php do_action( 'frm_additional_form_options', $values ); ?>
</table>
<?php } ?>

</div>
