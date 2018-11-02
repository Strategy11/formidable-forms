<div id="form_settings_page" class="frm_wrap">
    <div id="poststuff" class="frm_page_container">

    <div id="post-body" class="metabox-holder columns-2">
    <div id="post-body-content">

	<?php
	FrmAppHelper::get_admin_header(
		array(
			'label' => __( 'Settings', 'formidable' ),
			'form'  => $form,
		)
	);

	// Add form messages
	require( FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php' );
	?>

<form method="post" class="frm_form_settings">
    <input type="hidden" name="id" id="form_id" value="<?php echo (int) $id; ?>" />
    <input type="hidden" name="frm_action" value="update_settings" />
	<?php wp_nonce_field( 'process_form_nonce', 'process_form' ); ?>

        <div class="meta-box-sortables">
        <div class="categorydiv postbox" id="frm-categorydiv">
        <div class="inside frm-help-tabs">
        <div id="contextual-help-back"></div>
        <div id="contextual-help-columns">
        <div class="contextual-help-tabs">
        <ul class="frm-category-tabs frm-form-setting-tabs">
			<?php $a = FrmAppHelper::simple_get( 't', 'sanitize_title', 'advanced_settings' ); ?>
			<li class="<?php echo esc_attr( 'advanced_settings' === $a ? 'tabs active' : '' ); ?>">
				<a href="#advanced_settings"><?php esc_html_e( 'General', 'formidable' ) ?></a>
			</li>
			<li class="<?php echo esc_attr( 'email_settings' === $a ? 'tabs active' : '' ); ?>">
				<a href="#email_settings"><?php esc_html_e( 'Form Actions', 'formidable' ); ?></a>
			</li>
			<li class="<?php echo esc_attr( 'html_settings' === $a ? 'class="tabs active"' : '' ); ?>">
				<a href="#html_settings"><?php esc_html_e( 'Customize HTML', 'formidable' ) ?></a>
			</li>
            <?php
			foreach ( $sections as $key => $section ) {
				if ( isset( $section['name'] ) ) {
					$sec_name = $section['name'];
					$sec_anchor = $section['anchor'];
				} else {
					$sec_name = $key;
					$sec_anchor = $key;
				}
				?>
				<li class="<?php echo esc_attr( $a === $sec_anchor . '_settings' ? 'tabs active' : '' ); ?>">
					<a href="#<?php echo esc_attr( $sec_anchor ) ?>_settings">
						<?php echo esc_html( ucfirst( $sec_name ) ); ?>
					</a>
				</li>
            <?php } ?>
        </ul>
        </div>
        <div class="advanced_settings tabs-panel <?php echo esc_attr( $a === 'advanced_settings' ? 'frm_block' : 'frm_hidden' ); ?>">

			<?php if ( ! $values['is_template'] ) { ?>
				<h3 id="frm_shortcode" class="<?php echo esc_attr( $first_h3 ) ?>">
					<?php esc_html_e( 'Form Shortcodes', 'formidable' ); ?>
				</h3>
				<a href="#edit_frm_shortcode" class="edit-frm_shortcode hide-if-no-js" tabindex='4'><?php esc_html_e( 'Show', 'formidable' ); ?></a>
				<div id="frm_shortcodediv" class="hide-if-js">
					<p class="howto"><?php esc_html_e( 'Insert on a page, post, or text widget', 'formidable' ); ?>:</p>
					<p><input type="text" readonly="readonly" class="frm_select_box" value="[formidable id=<?php echo esc_attr( $id ); ?>]" />
						<input type="text" readonly="readonly" class="frm_select_box" value="[formidable id=<?php echo esc_attr( $id ); ?> title=true description=true]" />
					</p>

					<p class="howto"><?php esc_html_e( 'Insert in a template', 'formidable' ); ?>:</p>
					<p><input type="text" readonly="readonly" class="frm_select_box frm_insert_in_template" value="&lt;?php echo FrmFormsController::get_form_shortcode( array( 'id' => <?php echo absint( $id ) ?>, 'title' => false, 'description' => false ) ); ?&gt;" /></p>

					<p><a href="#edit_frm_shortcode" class="cancel-frm_shortcode hide-if-no-js"><?php esc_html_e( 'Hide', 'formidable' ); ?></a></p>
				</div>
				<?php $first_h3 = ''; ?>

				<?php if ( has_action( 'frm_settings_buttons' ) ) { ?>
					<h3 class="<?php echo esc_attr( $first_h3 ) ?>">
						<?php esc_html_e( 'Form Settings', 'formidable' ); ?>
					</h3>
					<div class="misc-pub-section">
						<?php do_action( 'frm_settings_buttons', $values ); ?>
						<div class="clear"></div>
					</div>
					<?php $first_h3 = ''; ?>
				<?php } ?>
			<?php } ?>

			<h3 class="<?php echo esc_attr( $first_h3 ) ?>">
				<?php esc_html_e( 'On Submit', 'formidable' ); ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Choose what will happen after the user submits this form.', 'formidable' ); ?>"></span>
            </h3>

            <!--On Submit Section-->
            <table class="form-table">
                <tr>
                    <td class="frm_175_width">
                        <select name="options[success_action]" id="success_action">
							<option value="message" <?php selected( $values['success_action'], 'message' ); ?>>
								<?php esc_html_e( 'Show Message', 'formidable' ) ?>
							</option>
							<option value="redirect" <?php selected( $values['success_action'], 'redirect' ); ?>>
								<?php esc_html_e( 'Redirect to URL', 'formidable' ) ?>
							</option>
							<option value="page" <?php selected( $values['success_action'], 'page' ); ?>>
								<?php esc_html_e( 'Show Page Content', 'formidable' ) ?>
							</option>
                        </select>
                    </td>
                    <td>
                        <span class="success_action_redirect_box success_action_box<?php echo ( $values['success_action'] == 'redirect' ) ? '' : ' frm_hidden'; ?>">
							<input type="text" name="options[success_url]" id="success_url" value="<?php echo esc_attr( isset( $values['success_url'] ) ? $values['success_url'] : '' ); ?>" placeholder="http://example.com" />
                        </span>

                        <span class="success_action_page_box success_action_box<?php echo esc_attr( $values['success_action'] === 'page' ? '' : ' frm_hidden' ); ?>">
                            <label><?php esc_html_e( 'Use Content from Page', 'formidable' ); ?></label>
                            <?php FrmAppHelper::wp_pages_dropdown( 'options[success_page_id]', $values['success_page_id'] ) ?>
                        </span>

                    </td>
                </tr>
                <tr class="frm_show_form_opt success_action_message_box success_action_box<?php echo esc_attr( $values['success_action'] == 'message' ? '' : ' frm_hidden' ); ?>">
                    <td colspan="2">
						<label for="show_form">
							<input type="checkbox" name="options[show_form]" id="show_form" value="1" <?php checked( $values['show_form'], 1 ) ?> />
							<?php esc_html_e( 'Show the form with the confirmation message', 'formidable' ) ?>
						</label>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
						<label for="no_save">
							<input type="checkbox" name="options[no_save]" id="no_save" value="1" <?php checked( $values['no_save'], 1 ); ?> />
							<?php esc_html_e( 'Do not store entries submitted from this form', 'formidable' ) ?>
						</label>
                    </td>
                </tr>
				<?php if ( function_exists( 'akismet_http_post' ) ) { ?>
                <tr>
                    <td colspan="2"><?php esc_html_e( 'Use Akismet to check entries for spam for', 'formidable' ) ?>
						<select name="options[akismet]">
							<option value="">
								<?php esc_html_e( 'no one', 'formidable' ) ?>
							</option>
							<option value="1" <?php selected( $values['akismet'], 1 ) ?>>
								<?php esc_html_e( 'everyone', 'formidable' ) ?>
							</option>
							<option value="logged" <?php selected( $values['akismet'], 'logged' ) ?>>
								<?php esc_html_e( 'visitors who are not logged in', 'formidable' ) ?>
							</option>
						</select>
                    </td>
                </tr>
                <?php } ?>
            </table>

            <!--AJAX Section-->
			<h3><?php esc_html_e( 'AJAX', 'formidable' ) ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Make stuff happen in the background without a page refresh', 'formidable' ) ?>" ></span>
			</h3>
            <table class="form-table">
                <tr>
                    <td>
						<label for="ajax_load">
							<input type="checkbox" name="options[ajax_load]" id="ajax_load" value="1"<?php echo ( $values['ajax_load'] ) ? ' checked="checked"' : ''; ?> /> <?php esc_html_e( 'Load and save form builder page with AJAX', 'formidable' ) ?>
						</label>
						<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Recommended for long forms.', 'formidable' ) ?>" ></span>
                    </td>
                </tr>
                <?php do_action( 'frm_add_form_ajax_options', $values ); ?>
				<tr>
					<td>
						<label for="js_validate">
							<input type="checkbox" name="options[js_validate]" id="js_validate" value="1" <?php checked( $values['js_validate'], 1 ); ?> />
							<?php esc_html_e( 'Validate this form with javascript', 'formidable' ); ?>
						</label>
						<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Required fields, email format, and number format can be checked instantly in your browser. You may want to turn this option off if you have any customizations to remove validation messages on certain fields.', 'formidable' ) ?>"></span>
					</td>
				</tr>
            </table>
			<?php FrmTipsHelper::pro_tip( 'get_form_settings_tip', 'p' ); ?>

            <!--Permissions Section-->
			<?php do_action( 'frm_add_form_perm_options', $values ); ?>

            <!--Styling & Buttons Section-->
			<h3><?php esc_html_e( 'Styling & Buttons', 'formidable' ) ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Select a style for this form and set the text for your buttons.', 'formidable' ) ?>" ></span>
			</h3>
            <table class="form-table">
                <tr>
                    <td class="frm_left_label">
						<label for="custom_style"><?php esc_html_e( 'Style Template', 'formidable' ) ?></label>
					</td>
                    <td><select name="options[custom_style]" id="custom_style">
						<option value="1" <?php selected( $values['custom_style'], 1 ) ?>>
							<?php esc_html_e( 'Always use default', 'formidable' ) ?>
						</option>
                        <?php foreach ( $styles as $s ) { ?>
						<option value="<?php echo esc_attr( $s->ID ) ?>" <?php selected( $s->ID, $values['custom_style'] ) ?>>
							<?php echo esc_html( $s->post_title . ( empty( $s->menu_order ) ? '' : ' (' . __( 'default', 'formidable' ) . ')' ) ) ?>
						</option>
                        <?php } ?>
						<option value="0" <?php selected( $values['custom_style'], 0 ); selected( $values['custom_style'], '' ) ?>>
							<?php esc_html_e( 'Do not use Formidable styling', 'formidable' ) ?>
						</option>
                    </select></td>
                </tr>
                <tr>
                    <td><label><?php esc_html_e( 'Submit Button Text', 'formidable' ) ?></label></td>
					<td>
						<input type="text" name="options[submit_value]" value="<?php echo esc_attr( $values['submit_value'] ); ?>" />
					</td>
                </tr>
				<?php do_action( 'frm_add_form_button_options', $values ); ?>
            </table>

            <!--Message Section-->
			<h3 id="frm_messages_header" class="<?php echo esc_attr( ( ( isset( $values['edit_action'] ) && $values['edit_action'] === 'message' && isset( $values['editable'] ) && $values['editable'] == 1 ) || $values['success_action'] === 'message' || isset( $values['save_draft'] ) && $values['save_draft'] == 1 ) ? '' : 'frm_hidden' ); ?>">
				<?php esc_html_e( 'Messages', 'formidable' ); ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Set up your confirmation messages.', 'formidable' ) ?>" ></span>
			</h3>
            <table class="form-table">
                <tr class="success_action_message_box success_action_box<?php echo esc_attr( $values['success_action'] === 'message' ? '' : ' frm_hidden' ); ?>">
                    <td>
                        <div><?php esc_html_e( 'On Submit', 'formidable' ) ?></div>
						<textarea id="success_msg" name="options[success_msg]" cols="50" rows="2" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea( $values['success_msg'] ); // WPCS: XSS ok. ?></textarea>
                    </td>
                </tr>
				<?php do_action( 'frm_add_form_msg_options', $values ); ?>
            </table>

			<!--Misc Section-->
			<h3><?php esc_html_e( 'Miscellaneous', 'formidable' ); ?></h3>
			<table class="form-table">
				<tr>
					<td>
						<label for="frm_form_key"><?php esc_html_e( 'Form Key', 'formidable' ) ?></label>
					</td>
					<td>
						<input type="text" id="frm_form_key" name="form_key" class="frm_long_input" value="<?php echo esc_attr( $values['form_key'] ); ?>" />
					</td>
				</tr>
				<tr>
					<td>
						<label for="frm_form_description"><?php esc_html_e( 'Form Description', 'formidable' ) ?></label>
					</td>
					<td>
						<textarea id="frm_form_description" name="description" cols="50" rows="5" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea( $values['description'] ); // WPCS: XSS ok. ?></textarea>
					</td>
				</tr>
				<?php do_action( 'frm_additional_form_options', $values ); ?>
			</table>
		</div>


        <div id="frm_notification_settings" class="frm_email_settings email_settings tabs-panel widgets-holder-wrap <?php echo esc_attr( $a === 'email_settings' ? ' frm_block' : ' frm_hidden' ); ?>">
			<?php FrmTipsHelper::pro_tip( 'get_form_action_tip', 'p' ); ?>
            <div id="frm_email_addon_menu" class="manage-menus">
                <h3><?php esc_html_e( 'Add New Action', 'formidable' ) ?></h3>
                <ul class="frm_actions_list">
                <?php

                //For each add-on, add an li, class, and javascript function. If active, add an additional class.
				$included = false;
				foreach ( $action_controls as $action_control ) {
					$classes = ( isset( $action_control->action_options['active'] ) && $action_control->action_options['active'] ) ? 'frm_active_action ' : 'frm_inactive_action ';
					$classes .= $action_control->action_options['classes'];

					if ( ! $included && strpos( $classes, 'frm_show_upgrade' ) ) {
						$included = true;
						FrmAppController::include_upgrade_overlay();
					}
					$upgrade_label = sprintf( esc_html__( '%s form actions', 'formidable' ), $action_control->action_options['tooltip'] );
                    ?>
					<li>
						<a href="javascript:void(0)" class="frm_<?php echo esc_attr( $action_control->id_base ) ?>_action frm_bstooltip <?php echo esc_attr( $classes ); ?>" title="<?php echo esc_attr( $action_control->action_options['tooltip'] ) ?>" data-limit="<?php echo esc_attr( isset( $action_control->action_options['limit'] ) ? $action_control->action_options['limit'] : '99' ); ?>" data-actiontype="<?php echo esc_attr( $action_control->id_base ) ?>" data-upgrade="<?php echo esc_attr( $upgrade_label ); ?>" data-medium="settings-<?php echo esc_attr( $action_control->id_base ); ?>"></a>
					</li>
<?php
					unset( $actions_icon, $classes );
                }
                ?>
                </ul>
            </div>
            <div class="frm_no_actions">
                <div class="inner_actions">
					<img src="<?php echo esc_url( FrmAppHelper::plugin_url() . '/images/sketch_arrow1.png' ); ?>" alt=""/>
                    <div class="clear"></div>
                    <?php esc_html_e( 'Click an action to add it to this form', 'formidable' ) ?>
                </div>
            </div>
			<?php FrmFormActionsController::list_actions( $form, $values ); ?>
        </div>

        <div id="html_settings" class="tabs-panel <?php echo esc_attr( $a === 'html_settings' ) ? ' frm_block' : ' frm_hidden'; ?>">

            <div class="frm_field_html_box frm_top_container">
                <p>
					<label><?php esc_html_e( 'Form Classes', 'formidable' ) ?></label>
                    <input type="text" name="options[form_class]" value="<?php echo esc_attr( $values['form_class'] ) ?>" />
                </p>
                <div class="clear"></div>

                <p>
					<label><?php esc_html_e( 'Before Fields', 'formidable' ) ?></label>
					<textarea name="options[before_html]" rows="4" id="before_html" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea( $values['before_html'] ); // WPCS: XSS ok. ?></textarea>
				</p>

                <div id="add_html_fields">
                    <?php
					if ( isset( $values['fields'] ) ) {
						foreach ( $values['fields'] as $field ) {
							if ( FrmFieldFactory::field_has_html( $field['type'] ) ) {
							?>
                                <p>
									<label><?php echo esc_html( $field['name'] ) ?></label>
									<textarea name="field_options[custom_html_<?php echo esc_attr( $field['id'] ); ?>]" rows="7" id="custom_html_<?php echo esc_attr( $field['id'] ); ?>" class="field_custom_html frm_long_input"><?php echo FrmAppHelper::esc_textarea( $field['custom_html'] ); // WPCS: XSS ok. ?></textarea>
								</p>
                            <?php
                            }
							unset( $field );
                        }
                    }
					?>
                </div>

                <p><label><?php esc_html_e( 'After Fields', 'formidable' ) ?></label>
					<textarea name="options[after_html]" rows="3" id="after_html" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea( $values['after_html'] ); // WPCS: XSS ok. ?></textarea>
				</p>

                <p><label><?php esc_html_e( 'Submit Button', 'formidable' ) ?></label>
					<textarea name="options[submit_html]" rows="3" id="submit_html" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea( $values['submit_html'] ); // WPCS: XSS ok. ?></textarea>
				</p>
            </div>
        </div>

		<?php
		foreach ( $sections as $key => $section ) {
			if ( isset( $section['anchor'] ) ) {
				$sec_anchor = $section['anchor'];
			} else {
				$sec_anchor = $key;
			}
			?>
			<div id="<?php echo esc_attr( $sec_anchor ) ?>_settings" class="tabs-panel <?php echo ( $a === $sec_anchor . '_settings' ) ? ' frm_block' : ' frm_hidden'; ?>">
				<?php
				if ( isset( $section['class'] ) ) {
					call_user_func( array( $section['class'], $section['function'] ), $values );
				} else {
					call_user_func( ( isset( $section['function'] ) ? $section['function'] : $section ), $values );
				}
				?>
            </div>
        <?php } ?>

		<?php do_action( 'frm_add_form_option_section', $values ); ?>
        <div class="clear"></div>
        </div>
        </div>
</div>

</div>

<p>
	<button class="button-primary frm_button_submit" type="submit" >
		<?php esc_html_e( 'Update', 'formidable' ); ?>
	</button>
</p>
</form>


    </div>
	<?php require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/sidebar-settings.php' ); ?>
    </div>
</div>
</div>
