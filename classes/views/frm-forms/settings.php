<div id="form_settings_page" class="wrap">
    <div class="frmicon icon32"><br/></div>
    <h2><?php _e( 'Settings', 'formidable' ) ?>
        <a href="#" class="add-new-h2 frm_invisible"></a>
    </h2>

	<?php
	// Add form messages
	require(FrmAppHelper::plugin_path() .'/classes/views/shared/errors.php');
	?>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
        <div id="post-body-content">
        <?php
            FrmAppController::get_form_nav($id, true);
        ?>

<form method="post" class="frm_form_settings">
    <input type="hidden" name="id" id="form_id" value="<?php echo (int) $id; ?>" />
    <input type="hidden" name="frm_action" value="update_settings" />

        <div class="meta-box-sortables">
        <div class="categorydiv postbox" id="frm-categorydiv">
        <h3 class="hndle"><span><?php echo __( 'Form Settings', 'formidable' ) ?></span></h3>
        <div class="inside frm-help-tabs">
        <div id="contextual-help-back"></div>
        <div id="contextual-help-columns">
        <div class="contextual-help-tabs">
        <ul class="frm-category-tabs frm-form-setting-tabs">
			<?php $a = FrmAppHelper::simple_get( 't', 'sanitize_title', 'advanced_settings' ); ?>
        	<li <?php echo ($a == 'advanced_settings') ? 'class="tabs active"' : '' ?>><a href="#advanced_settings"><?php _e( 'General', 'formidable' ) ?></a></li>
        	<li <?php echo ($a == 'email_settings') ? 'class="tabs active"' : '' ?>><a href="#email_settings"><?php _e( 'Form Actions', 'formidable' ); ?></a></li>
            <li <?php echo ($a == 'html_settings') ? 'class="tabs active"' : '' ?>><a href="#html_settings"><?php _e( 'Customize HTML', 'formidable' ) ?></a></li>
            <?php foreach ( $sections as $key => $section ) {
				if ( isset( $section['name'] ) ) {
					$sec_name = $section['name'];
					$sec_anchor = $section['anchor'];
				} else {
					$sec_anchor = $sec_name = $key;
				} ?>
                <li <?php echo ($a == $sec_anchor .'_settings') ? 'class="tabs active"' : '' ?>><a href="#<?php echo esc_attr( $sec_anchor ) ?>_settings"><?php echo ucfirst($sec_name) ?></a></li>
            <?php } ?>
        </ul>
        </div>
        <div class="advanced_settings tabs-panel <?php echo ($a == 'advanced_settings') ? 'frm_block' : 'frm_hidden' ?>">
			<?php FrmTipsHelper::pro_tip( 'get_form_settings_tip', 'p' ); ?>

			<h3 class="frm_first_h3"><?php _e( 'On Submit', 'formidable' ); ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Choose what will happen after the user submits this form.', 'formidable' );
				if ( ! FrmAppHelper::pro_is_installed() ) {
					esc_attr_e( ' Upgrade to Formidable Pro to get access to all options in the dropdown.', 'formidable' );
				} ?>" ></span>
            </h3>

            <!--On Submit Section-->
            <table class="form-table">
                <tr>
                    <td class="frm_175_width">
                        <select name="options[success_action]" id="success_action">
                            <option value="message" <?php selected($values['success_action'], 'message') ?>><?php _e( 'Show Message', 'formidable' )?></option>
                            <?php if ( FrmAppHelper::pro_is_installed() ) { ?>
                                <option value="redirect" <?php selected($values['success_action'], 'redirect');
                                ?>><?php _e( 'Redirect to URL', 'formidable' ) ?></option>
                                <option value="page" <?php selected($values['success_action'], 'page');
								?>><?php _e( 'Show Page Content', 'formidable' ) ?></option>
                            <?php } else { ?>
                            <option value="redirect" disabled="disabled" <?php selected($values['success_action'], 'redirect');
                            ?>><?php _e( 'Redirect to URL', 'formidable' ); echo ' '. __( '(Premium feature)', 'formidable' ); ?></option>
                            <option value="page" disabled="disabled" <?php selected($values['success_action'], 'page');
                            ?>><?php _e( 'Show Page Content', 'formidable' ); echo ' '. __( '(Premium feature)', 'formidable' ); ?></option>
                            <?php } ?>
                        </select>
                    </td>
                    <td>
                        <span class="success_action_redirect_box success_action_box<?php echo ($values['success_action'] == 'redirect') ? '' : ' frm_hidden'; ?>">
                            <input type="text" name="options[success_url]" id="success_url" value="<?php
							if ( isset( $values['success_url'] ) ) {
								echo esc_attr( $values['success_url'] );
							} ?>" placeholder="http://example.com" />
                        </span>

						<?php if ( FrmAppHelper::pro_is_installed() ) { ?>
                        <span class="success_action_page_box success_action_box<?php echo ($values['success_action'] == 'page') ? '' : ' frm_hidden'; ?>">
                            <label><?php _e( 'Use Content from Page', 'formidable' ) ?></label>
                            <?php FrmAppHelper::wp_pages_dropdown( 'options[success_page_id]', $values['success_page_id'] ) ?>
                        </span>
                        <?php } ?>
                    </td>
                </tr>
                <tr class="frm_show_form_opt success_action_message_box success_action_box<?php echo ($values['success_action'] == 'message') ? '' : ' frm_hidden'; ?>">
                    <td colspan="2">
						<label for="show_form"><input type="checkbox" name="options[show_form]" id="show_form" value="1" <?php checked( $values['show_form'], 1 ) ?> /> <?php _e( 'Show the form with the confirmation message', 'formidable' ) ?></label>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><label for="no_save"><input type="checkbox" name="options[no_save]" id="no_save" value="1" <?php checked($values['no_save'], 1); ?> /> <?php _e( 'Do not store entries submitted from this form', 'formidable' ) ?></label>
                    </td>
                </tr>
                <?php if ( function_exists( 'akismet_http_post') ) { ?>
                <tr>
                    <td colspan="2"><?php _e( 'Use Akismet to check entries for spam for', 'formidable' ) ?>
                        <select name="options[akismet]">
                            <option value=""><?php _e( 'no one', 'formidable' ) ?></option>
							<option value="1" <?php selected( $values['akismet'], 1 ) ?>><?php _e( 'everyone', 'formidable' ) ?></option>
							<option value="logged" <?php selected( $values['akismet'], 'logged' ) ?>><?php _e( 'visitors who are not logged in', 'formidable' ) ?></option>
                        </select>
                    </td>
                </tr>
                <?php } ?>
            </table>

            <!--AJAX Section-->
			<h3><?php _e( 'AJAX', 'formidable' ) ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Make stuff happen in the background without a page refresh', 'formidable' ) ?>" ></span>
			</h3>
            <table class="form-table">
                <tr>
                    <td>
						<label for="ajax_load">
							<input type="checkbox" name="options[ajax_load]" id="ajax_load" value="1"<?php echo ( $values['ajax_load'] ) ? ' checked="checked"' : ''; ?> /> <?php _e( 'Load and save form builder page with AJAX', 'formidable' ) ?>
						</label>
						<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Recommended for long forms.', 'formidable' ) ?>" ></span>
                    </td>
                </tr>
                <?php do_action('frm_add_form_ajax_options', $values); ?>
            </table>

            <!--Permissions Section-->
            <table class="form-table">
                <?php do_action('frm_add_form_perm_options', $values); ?>
            </table>

            <!--Styling & Buttons Section-->
			<h3><?php _e( 'Styling & Buttons', 'formidable' ) ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Select a style for this form and set the text for your buttons.', 'formidable' ) ?>" ></span>
			</h3>
            <table class="form-table">
                <tr>
                    <td class="frm_left_label"><label for="custom_style"><?php _e( 'Style Template', 'formidable' ) ?></label></td>
                    <td><select name="options[custom_style]" id="custom_style">
						<option value="1" <?php selected( $values['custom_style'], 1 ) ?>><?php _e( 'Always use default', 'formidable' ) ?></option>
                        <?php foreach ( $styles as $s ) { ?>
                        <option value="<?php echo esc_attr( $s->ID ) ?>" <?php selected( $s->ID, $values['custom_style'] ) ?>><?php echo esc_html( $s->post_title . ( empty( $s->menu_order ) ? '' : ' ('. __( 'default', 'formidable' ) .')' ) ) ?></option>
                        <?php } ?>
						<option value="0" <?php selected( $values['custom_style'], 0 ); selected( $values['custom_style'], '' ) ?>><?php _e( 'Do not use Formidable styling', 'formidable' ) ?></option>
                    </select></td>
                </tr>
                <tr>
                    <td><label><?php _e( 'Submit Button Text', 'formidable' ) ?></label></td>
                    <td><input type="text" name="options[submit_value]" value="<?php echo esc_attr($values['submit_value']); ?>" /></td>
                </tr>
				<?php do_action( 'frm_add_form_button_options', $values ); ?>
            </table>

            <!--Message Section-->
			<h3 id="frm_messages_header" class="<?php echo ( ( isset( $values['edit_action'] ) && $values['edit_action'] == 'message' && isset( $values['editable'] ) && $values['editable'] == 1 ) || $values['success_action'] == 'message' || $values['save_draft'] == 1 ) ? '' : 'frm_hidden'; ?>"><?php _e( 'Messages', 'formidable' ); ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Set up your confirmation messages.', 'formidable' ) ?>" ></span>
			</h3>
            <table class="form-table">
                <tr class="success_action_message_box success_action_box<?php echo ($values['success_action'] == 'message') ? '' : ' frm_hidden'; ?>">
                    <td>
                        <div><?php _e( 'On Submit', 'formidable' ) ?></div>
                        <textarea id="success_msg" name="options[success_msg]" cols="50" rows="2" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea($values['success_msg']); ?></textarea>
                    </td>
                </tr>
                <?php do_action('frm_add_form_msg_options', $values); ?>
            </table>

			<!--Misc Section-->
			<?php if ( has_action( 'frm_additional_form_options' ) ) { ?>
				<h3><?php _e( 'Miscellaneous', 'formidable' ); ?></h3>
				<table class="form-table">
					<?php do_action('frm_additional_form_options', $values); ?>
				</table>
				<?php } ?>

		</div>


        <div id="frm_notification_settings" class="frm_email_settings email_settings tabs-panel widgets-holder-wrap <?php echo ($a == 'email_settings') ? ' frm_block' : ' frm_hidden'; ?>">
			<?php FrmTipsHelper::pro_tip( 'get_form_action_tip', 'p' ); ?>
            <div id="frm_email_addon_menu" class="manage-menus">
                <h3><?php _e( 'Add New Action', 'formidable' ) ?></h3>
                <ul class="frm_actions_list">
                <?php

                //For each add-on, add an li, class, and javascript function. If active, add an additional class.
                foreach ( $action_controls as $action_control ) {
                    ?>
                    <li><a href="javascript:void(0)" class="frm_<?php echo esc_attr( $action_control->id_base ) ?>_action frm_bstooltip <?php
                    echo ( isset($action_control->action_options['active']) && $action_control->action_options['active']) ? 'frm_active_action ' : 'frm_inactive_action ';
                    echo esc_attr( $action_control->action_options['classes'] );
                    ?>" title="<?php echo esc_attr($action_control->action_options['tooltip']) ?>" data-limit="<?php echo isset($action_control->action_options['limit']) ? esc_attr( $action_control->action_options['limit'] ) : '99' ?>" data-actiontype="<?php echo esc_attr($action_control->id_base) ?>"></a></li>
<?php
                    unset($actions_icon);
                }
                ?>
                </ul>
            </div>
            <div class="frm_no_actions">
                <div class="inner_actions">
                    <img src="<?php echo FrmAppHelper::plugin_url() .'/images/sketch_arrow1.png'; ?>" alt=""/>
                    <div class="clear"></div>
                    <?php _e( 'Click an action to add it to this form', 'formidable' ) ?>
                </div>
            </div>
            <?php FrmFormActionsController::list_actions($form, $values); ?>
        </div>

        <div id="html_settings" class="tabs-panel <?php echo ($a == 'html_settings') ? ' frm_block' : ' frm_hidden'; ?>">

            <div class="frm_field_html_box frm_top_container">
                <p><label><?php _e( 'Form Classes', 'formidable' ) ?></label>
                    <input type="text" name="options[form_class]" value="<?php echo esc_attr($values['form_class']) ?>" />
                </p>
                <div class="clear"></div>

                <p><label><?php _e( 'Before Fields', 'formidable' ) ?></label>
                <textarea name="options[before_html]" rows="4" id="before_html" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea($values['before_html']) ?></textarea></p>

                <div id="add_html_fields">
                    <?php
					if ( isset( $values['fields'] ) ) {
						foreach ( $values['fields'] as $field ) {
							if ( apply_filters( 'frm_show_custom_html', true, $field['type'] ) ) { ?>
                                <p><label><?php echo esc_html( $field['name'] ) ?></label>
                                <textarea name="field_options[custom_html_<?php echo esc_attr( $field['id'] ) ?>]" rows="7" id="custom_html_<?php echo esc_attr( $field['id'] ) ?>" class="field_custom_html frm_long_input"><?php echo FrmAppHelper::esc_textarea($field['custom_html']) ?></textarea></p>
                            <?php }
                            unset($field);
                        }
                    } ?>
                </div>

                <p><label><?php _e( 'After Fields', 'formidable' ) ?></label>
                <textarea name="options[after_html]" rows="3" id="after_html" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea($values['after_html']) ?></textarea></p>

                <p><label><?php _e( 'Submit Button', 'formidable' ) ?></label>
                <textarea name="options[submit_html]" rows="3" id="submit_html" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea($values['submit_html']) ?></textarea></p>
            </div>
        </div>

		<?php foreach ( $sections as $key => $section ) {
			if ( isset( $section['anchor'] ) ) {
				$sec_anchor = $section['anchor'];
			} else {
				$sec_anchor = $key;
			} ?>
            <div id="<?php echo esc_attr( $sec_anchor ) ?>_settings" class="tabs-panel <?php echo ($a == $sec_anchor .'_settings') ? ' frm_block' : ' frm_hidden'; ?>"><?php
			if ( isset( $section['class'] ) ) {
				call_user_func( array( $section['class'], $section['function'] ), $values );
			} else {
				call_user_func( ( isset( $section['function'] ) ? $section['function'] : $section ), $values );
            } ?>
            </div>
        <?php } ?>

        <?php do_action('frm_add_form_option_section', $values); ?>
        <div class="clear"></div>
        </div>
        </div>
</div>

</div>

    <p>
        <input type="submit" value="<?php esc_attr_e( 'Update', 'formidable' ) ?>" class="button-primary" />
    </p>
    </form>


    </div>
    <?php require(FrmAppHelper::plugin_path() .'/classes/views/frm-forms/sidebar-settings.php'); ?>
    </div>
</div>
</div>
