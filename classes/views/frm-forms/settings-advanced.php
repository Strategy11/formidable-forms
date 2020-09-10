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
		<textarea id="frm_form_description" name="description" cols="50" rows="4"><?php echo FrmAppHelper::esc_textarea( $values['description'] ); // WPCS: XSS ok. ?></textarea>
	</p>


<?php if ( ! $values['is_template'] ) { ?>
	<p class="frm6 frm_form_field">
		<label>
			<?php esc_html_e( 'Embed Shortcode', 'formidable' ); ?>
		</label>
		<input type="text" readonly="readonly" class="frm_select_box" value="[formidable id=<?php echo esc_attr( $values['id'] ); ?>]" />
	</p>
	<p class="frm6 frm_form_field">
		<label>&nbsp;</label>
		<input type="text" readonly="readonly" class="frm_select_box" value="[formidable id=<?php echo esc_attr( $values['id'] ); ?> title=true description=true]" />
	</p>

	<a href="#edit_frm_shortcode" class="edit-frm_shortcode hide-if-no-js" tabindex='4'><?php esc_html_e( 'Insert with PHP', 'formidable' ); ?></a>
	<p id="frm_shortcodediv" class="hide-if-js">
		<label>
			<?php esc_html_e( 'Embed in Template', 'formidable' ); ?>
		</label>
			<input type="text" readonly="readonly" class="frm_select_box frm_insert_in_template" value="&lt;?php echo FrmFormsController::get_form_shortcode( array( 'id' => <?php echo absint( $values['id'] ); ?>, 'title' => false, 'description' => false ) ); ?&gt;" />
	</p>
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

<!--On Submit Section-->

<p class="frm4 frm_form_field">
			<select name="options[success_action]" id="success_action">
				<option value="message" <?php selected( $values['success_action'], 'message' ); ?>>
					<?php esc_html_e( 'Show Message', 'formidable' ); ?>
				</option>
				<option value="redirect" <?php selected( $values['success_action'], 'redirect' ); ?>>
					<?php esc_html_e( 'Redirect to URL', 'formidable' ); ?>
				</option>
				<option value="page" <?php selected( $values['success_action'], 'page' ); ?>>
					<?php esc_html_e( 'Show Page Content', 'formidable' ); ?>
				</option>
			</select>
</p>
<p class="frm8 frm_form_field">
			<span class="frm_has_shortcodes success_action_redirect_box success_action_box<?php echo ( $values['success_action'] == 'redirect' ) ? '' : ' frm_hidden'; ?>">
				<input type="text" name="options[success_url]" id="success_url" value="<?php echo esc_attr( isset( $values['success_url'] ) ? $values['success_url'] : '' ); ?>" placeholder="http://example.com" />
			</span>

			<span class="success_action_page_box success_action_box<?php echo esc_attr( $values['success_action'] === 'page' ? '' : ' frm_hidden' ); ?>">

				<?php
				FrmAppHelper::maybe_autocomplete_pages_options(
					array(
						'field_name'  => 'options[success_page_id]',
						'page_id'     => isset( $values['success_page_id'] ) ? $values['success_page_id'] : '',
						'placeholder' => __( 'Select a Page', 'formidable' ),
					)
				);
				?>
			</span>
</p>

<p class="frm_show_form_opt success_action_message_box success_action_box<?php echo esc_attr( $values['success_action'] == 'message' ? '' : ' frm_hidden' ); ?>">
	<label for="show_form" class="frm_inline_block">
		<input type="checkbox" name="options[show_form]" id="show_form" value="1" <?php checked( $values['show_form'], 1 ); ?> />
		<?php esc_html_e( 'Show the form with the confirmation message', 'formidable' ); ?>
	</label>
</p>

<table class="form-table">
	<tr>
		<td colspan="2">
			<label for="no_save" class="frm_inline_block">
				<input type="checkbox" name="options[no_save]" id="no_save" value="1" <?php checked( $values['no_save'], 1 ); ?> />
				<?php esc_html_e( 'Do not store entries submitted from this form', 'formidable' ); ?>
			</label>
		</td>
	</tr>
	<?php if ( function_exists( 'akismet_http_post' ) ) { ?>
		<tr>
			<td colspan="2"><?php esc_html_e( 'Use Akismet to check entries for spam for', 'formidable' ); ?>
				<select name="options[akismet]">
					<option value="">
						<?php esc_html_e( 'no one', 'formidable' ); ?>
					</option>
					<option value="1" <?php selected( $values['akismet'], 1 ); ?>>
						<?php esc_html_e( 'everyone', 'formidable' ); ?>
					</option>
					<option value="logged" <?php selected( $values['akismet'], 'logged' ); ?>>
						<?php esc_html_e( 'visitors who are not logged in', 'formidable' ); ?>
					</option>
				</select>
			</td>
		</tr>
	<?php } ?>
</table>

<!--AJAX Section-->
<h3><?php esc_html_e( 'AJAX', 'formidable' ); ?>
	<span class="frm_help frm_icon_font frm_tooltip_icon" data-placement="right" title="<?php esc_attr_e( 'Make stuff happen in the background without a page refresh', 'formidable' ); ?>" ></span>
</h3>
<?php FrmTipsHelper::pro_tip( 'get_form_settings_tip', 'p' ); ?>
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
	<tr>
		<td>
			<label for="js_validate" class="frm_inline_block">
				<input type="checkbox" name="options[js_validate]" id="js_validate" value="1" <?php checked( $values['js_validate'], 1 ); ?> />
				<?php esc_html_e( 'Validate this form with javascript', 'formidable' ); ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Required fields, email format, and number format can be checked instantly in your browser. You may want to turn this option off if you have any customizations to remove validation messages on certain fields.', 'formidable' ); ?>"></span>
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
			<textarea id="success_msg" name="options[success_msg]" cols="50" rows="2" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea( $values['success_msg'] ); // WPCS: XSS ok. ?></textarea>
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
