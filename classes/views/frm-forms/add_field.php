<?php

$display = apply_filters('frm_display_field_options', array(
    'type' => $field['type'], 'field_data' => $field,
    'required' => true, 'unique' => false, 'read_only' => false,
    'description' => true, 'options' => true, 'label_position' => true,
    'invalid' => false, 'size' => false, 'clear_on_focus' => false,
    'default_blank' => true, 'css' => true, 'conf_field' => false,
	'max' => true, 'captcha_size' => false,
));

$li_classes = 'form-field edit_form_item frm_field_box frm_top_container frm_not_divider edit_field_type_' . $display['type'];
$li_classes = apply_filters('frm_build_field_class', $li_classes, $field );

if ( isset( $values ) && isset( $values['ajax_load'] ) && $values['ajax_load'] && isset( $count ) && $count > 10 && ! in_array( $field['type'], array( 'divider', 'end_divider' ) ) ) {
?>
<li id="frm_field_id_<?php echo esc_attr( $field['id'] ); ?>" class="<?php echo esc_attr( $li_classes ) ?> frm_field_loading" data-fid="<?php echo esc_attr( $field['id'] ) ?>" data-formid="<?php echo esc_attr( 'divider' == $field['type'] ? $field['form_select'] : $field['form_id'] ); ?>" data-ftype="<?php echo esc_attr( $display['type'] ) ?>">
<img src="<?php echo FrmAppHelper::plugin_url() ?>/images/ajax_loader.gif" alt="<?php esc_attr_e( 'Loading', 'formidable' ) ?>" />
<span class="frm_hidden_fdata frm_hidden"><?php echo htmlspecialchars(json_encode($field)) ?></span>
</li>
<?php
   return;
}

$frm_settings = FrmAppHelper::get_settings();

if ( ! isset( $frm_all_field_selection ) ) {
    if ( isset($frm_field_selection) && isset($pro_field_selection) ) {
        $frm_all_field_selection = array_merge($frm_field_selection, $pro_field_selection);
    } else {
		$pro_field_selection = FrmField::pro_field_selection();
		$frm_all_field_selection = array_merge( FrmField::field_selection(), $pro_field_selection );
    }
}

$disabled_fields = FrmAppHelper::pro_is_installed() ? array() : $pro_field_selection;


if ( ! isset( $ajax ) ) {
    $li_classes .= ' ui-state-default widgets-holder-wrap'; ?>
<li id="frm_field_id_<?php echo esc_attr( $field['id'] ); ?>" class="<?php echo esc_attr( $li_classes ) ?>" data-fid="<?php echo esc_attr( $field['id'] ) ?>" data-formid="<?php echo ( 'divider' == $field['type'] ) ? esc_attr( $field['form_select'] ) : esc_attr( $field['form_id'] ); ?>" data-ftype="<?php echo esc_attr( $display['type'] ) ?>">
<?php
}

if ( $field['type'] == 'divider' ) { ?>
<div class="divider_section_only">
<?php
}
?>

    <a href="javascript:void(0);" class="frm_bstooltip alignright frm-show-hover frm-move frm-hover-icon frm_icon_font frm_move_icon" title="<?php esc_attr_e( 'Move Field', 'formidable' ) ?>"> </a>
    <a href="#" class="frm_bstooltip alignright frm-show-hover frm-hover-icon frm_icon_font frm_delete_icon frm_delete_field" title="<?php esc_attr_e( 'Delete Field', 'formidable' ) ?>"> </a>
    <a href="#" class="frm_bstooltip alignright frm-show-hover frm-hover-icon frm_icon_font frm_duplicate_icon" title="<?php ( $field['type'] == 'divider' ) ? esc_attr_e( 'Duplicate Section', 'formidable' ) : esc_attr_e( 'Duplicate Field', 'formidable' ) ?>"> </a>
    <input type="hidden" name="frm_fields_submitted[]" value="<?php echo esc_attr($field['id']) ?>" />
    <?php do_action('frm_extra_field_actions', $field['id']); ?>
    <?php if ( $display['required'] ) { ?>
    <span id="require_field_<?php echo esc_attr( $field['id'] ); ?>">
		<a href="javascript:void(0);" class="frm_req_field frm_action_icon frm_required_icon frm_icon_font alignleft frm_required<?php echo (int) $field['required'] ?>" id="req_field_<?php echo esc_attr( $field['id'] ); ?>" title="Click to Mark as <?php echo FrmField::is_required( $field ) ? 'not ' : ''; ?>Required"></a>
    </span>
    <?php }

    ?>
    <label class="<?php echo ( $field['type'] == 'end_divider' ) ? '' : 'frm_ipe_field_label'; ?> frm_primary_label <?php echo ( $field['type'] == 'break' ) ? 'button': ''; ?>" id="field_label_<?php echo esc_attr( $field['id'] ); ?>"><?php echo ( $field['name'] == '' ) ? __( '(no label)', 'formidable' ) : force_balance_tags( $field['name'] ); ?></label>


<div id="field_<?php echo esc_attr( $field['id'] ) ?>_inner_container" class="frm_inner_field_container">
<div class="frm_form_fields" data-ftype="<?php echo esc_attr( $display['type'] ) ?>">
<?php
include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/show-build.php' );

if ( $display['clear_on_focus'] ) {
	FrmFieldsHelper::clear_on_focus_html( $field, $display );
	do_action( 'frm_extra_field_display_options', $field );
}

?>
<div class="clear"></div>
</div>
<?php
if ( $display['description'] ) { ?>
    <div class="frm_ipe_field_desc description <?php echo ($field['description'] == '') ? 'frm-show-click' : '' ?>" id="field_description_<?php echo esc_attr( $field['id'] ); ?>"><?php echo ($field['description'] == '') ? __( '(Click to add description)', 'formidable' ) : force_balance_tags( $field['description'] ); ?></div>
    <input type="hidden" name="field_options[description_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['description'] ); ?>" />

<?php } ?>
</div> <?php //End field_x_inner_container div

if ( $display['conf_field'] ) { ?>
<div id="frm_conf_field_<?php echo esc_attr( $field['id'] ) ?>_container" class="frm_conf_field_container frm_form_fields frm_conf_details<?php echo esc_attr( $field['id'] . ( $field['conf_field'] ? '' : ' frm_hidden' ) ); ?>">
    <div id="frm_conf_field_<?php echo esc_attr( $field['id'] ) ?>_inner_container" class="frm_inner_conf_container">
		<div class="frm_form_fields">
			<input type="text" id="conf_field_<?php echo esc_attr( $field['field_key'] ) ?>" name="field_options[conf_input_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['conf_input'] ); ?>" class="dyn_default_value" />
		</div>
    	<div id="conf_field_description_<?php echo esc_attr( $field['id'] ) ?>" class="frm_ipe_field_conf_desc description <?php echo ($field['conf_desc'] == '') ? 'frm-show-click' : '' ?>"><?php
			echo ($field['conf_desc'] == '') ? __( '(Click to add description)', 'formidable' ) : force_balance_tags($field['conf_desc']); ?></div>
    	<input type="hidden" name="field_options[conf_desc_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['conf_desc'] ); ?>" />
</div>
	<?php if ( $display['clear_on_focus'] ) { ?>
        <div class="alignleft">
			<?php FrmFieldsHelper::clear_on_focus_html( $field, $display, '_conf' ); ?>
        </div>
    <?php } ?>
</div>
<div class="clear"></div>
<?php }

if ( in_array( $field['type'], array( 'select', 'radio', 'checkbox' ) ) ) { ?>
    <div class="frm-show-click frm_small_top_margin"><?php

    if ( isset($field['post_field']) && $field['post_field'] == 'post_category' ) {
		echo '<p class="howto">' . FrmFieldsHelper::get_term_link( $field['taxonomy'] ) . '</p>';
	} else if ( ! isset( $field['post_field'] ) || ! in_array( $field['post_field'], array( 'post_category' ) ) ) {
?>
        <div id="frm_add_field_<?php echo esc_attr( $field['id'] ); ?>">
            <a href="javascript:void(0);" data-opttype="single" class="button frm_cb_button frm_add_opt"><?php _e( 'Add Option', 'formidable' ) ?></a>

            <?php
			if ( FrmAppHelper::pro_is_installed() ) { ?>
				<a href="javascript:void(0);" id="other_button_<?php echo esc_attr( $field['id'] ); ?>" data-opttype="other" data-ftype="<?php echo esc_attr( $field['type'] ) ?>" class="button frm_cb_button frm_add_opt<?php echo ( in_array( $field['type'], array( 'radio', 'select' ) ) && $field['other'] == true ? ' frm_hidden' : '' ); ?>"><?php _e( 'Add "Other"', 'formidable' ) ?></a>
                <input type="hidden" value="<?php echo esc_attr( $field['other'] ); ?>" id="other_input_<?php echo esc_attr( $field['id'] ); ?>" name="field_options[other_<?php echo esc_attr( $field['id'] ); ?>]">
            <?php
            }

            if ( ! isset($field['post_field']) || $field['post_field'] != 'post_category' ) { ?>
			<a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=frm_import_choices&field_id=' . $field['id'] . '&TB_iframe=1' ) ) ?>" title="<?php echo esc_attr( FrmAppHelper::truncate( strip_tags( str_replace( '"', '&quot;', $field['name'] ) ), 20 ) . ' ' . __( 'Field Choices', 'formidable' ) ); ?>" class="thickbox frm_orange">
				<?php _e( 'Bulk Edit Options', 'formidable' ) ?>
			</a>
            <?php } ?>
        </div>
<?php
    }
?>
    </div>
<?php
}

do_action('frm_before_field_options', $field);

if ( $display['options'] ) { ?>
    <div class="widget">
        <div class="widget-top">
    	    <div class="widget-title-action"><a href="javascript:void(0);" class="widget-action"></a></div>
    		<div class="widget-title"><h4><?php _e( 'Field Options', 'formidable' ) ?> (ID <?php echo (int) $field['id'] ?>)</h4></div>
        </div>
    	<div class="widget-inside">
            <table class="form-table frm_clear_none">
                <?php $field_types = FrmFieldsHelper::get_field_types($field['type']); ?>
				<tr><td class="frm_150_width"><label><?php _e( 'Field Type', 'formidable' ) ?></label></td>
                    <td>
                <select <?php if ( count($field_types) == 1 ) { ?>disabled="disabled"<?php } else { ?>name="field_options[type_<?php echo esc_attr( $field['id'] ) ?>]"<?php } ?>>
                    <?php
					foreach ( $field_types as $fkey => $ftype ) { ?>
                        <option value="<?php echo esc_attr( $fkey ) ?>" <?php echo ( $fkey == $field['type'] ) ? ' selected="selected"' : ''; ?> <?php echo array_key_exists($fkey, $disabled_fields ) ? 'disabled="disabled"' : '';  ?>><?php echo is_array($ftype) ? $ftype['name'] : $ftype ?> </option>
                    <?php
						unset( $fkey, $ftype );
					} ?>
                </select>

                <?php
				if ( $display['required'] ) { ?>
					<label for="frm_req_field_<?php echo esc_attr( $field['id'] ) ?>" class="frm_inline_label">
						<input type="checkbox" id="frm_req_field_<?php echo esc_attr( $field['id'] ) ?>" class="frm_req_field" name="field_options[required_<?php echo esc_attr( $field['id'] ) ?>]" value="1" <?php checked( $field['required'], 1 ) ?> />
						<?php _e( 'Required', 'formidable' ) ?>
					</label>
                <?php
                }

				if ( $display['unique'] ) {
                    if ( ! isset( $field['unique'] ) ) {
                        $field['unique'] = false;
                    }
                ?>
                <label for="frm_uniq_field_<?php echo esc_attr( $field['id'] ) ?>" class="frm_inline_label frm_help" title="<?php esc_attr_e( 'Unique: Do not allow the same response multiple times. For example, if one user enters \'Joe\', then no one else will be allowed to enter the same name.', 'formidable' ) ?>"><input type="checkbox" name="field_options[unique_<?php echo esc_attr( $field['id'] ) ?>]" id="frm_uniq_field_<?php echo esc_attr( $field['id'] ) ?>" value="1" <?php checked( $field['unique'], 1 ); ?> class="frm_mark_unique" /> <?php _e( 'Unique', 'formidable' ) ?></label>
                <?php
                }

				if ( $display['read_only'] ) {
                    if ( ! isset( $field['read_only'] ) ) {
                        $field['read_only'] = false;
					}
                ?>
				<label for="frm_read_only_field_<?php echo esc_attr( $field['id'] ) ?>" class="frm_inline_label frm_help" title="<?php esc_attr_e( 'Read Only: Show this field but do not allow the field value to be edited from the front-end.', 'formidable' ) ?>" >
					<input type="checkbox" id="frm_read_only_field_<?php echo esc_attr( $field['id'] ) ?>" name="field_options[read_only_<?php echo esc_attr( $field['id'] ) ?>]" value="1" <?php checked( $field['read_only'], 1 ) ?>/>
					<?php _e( 'Read Only', 'formidable' ) ?>
				</label>
                <?php }

                do_action('frm_field_options_form_top', $field, $display, $values);

                ?>
                <?php
				if ( $display['required'] ) { ?>
                <div class="frm_required_details<?php echo esc_attr( $field['id'] . ( $field['required'] ? '' : ' frm_hidden' ) ); ?>">
                    <span class="howto"><?php _e( 'Indicate required field with', 'formidable' ) ?></span>
                    <input type="text" name="field_options[required_indicator_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['required_indicator'] ); ?>" />
                </div>
                <?php } ?>
                    </td>
                </tr>
				<tr>
					<td class="frm_150_width">
						<div class="hide-if-no-js edit-slug-box frm_help" title="<?php esc_attr_e( 'The field key can be used as an alternative to the field ID in many cases.', 'formidable' ) ?>">
                            <?php _e( 'Field Key', 'formidable' ) ?>
						</div>
					</td>
					<td>
						<input type="text" name="field_options[field_key_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['field_key'] ); ?>" />
					</td>
				</tr>

                <?php
				if ( $display['css'] ) { ?>
                <tr><td><label><?php _e( 'CSS layout classes', 'formidable' ) ?></label>
					<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Add a CSS class to the field container. Use our predefined classes to align multiple fields in single row.', 'formidable' ) ?>" ></span>
                    </td>
                    <td><input type="text" name="field_options[classes_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['classes'] ) ?>" id="frm_classes_<?php echo esc_attr( $field['id'] ) ?>" class="frm_classes frm_long_input" />
                    </td>
                </tr>
                <?php
				}

				if ( $display['label_position'] ) { ?>
                    <tr><td class="frm_150_width"><label><?php _e( 'Label Position', 'formidable' ) ?></label></td>
                        <td><select name="field_options[label_<?php echo esc_attr( $field['id'] ) ?>]">
                            <option value=""<?php selected($field['label'], ''); ?>><?php _e( 'Default', 'formidable' ) ?></option>
                            <option value="top"<?php selected($field['label'], 'top'); ?>><?php _e( 'Top', 'formidable' ) ?></option>
                            <option value="left"<?php selected($field['label'], 'left'); ?>><?php _e( 'Left', 'formidable' ) ?></option>
                            <option value="right"<?php selected($field['label'], 'right'); ?>><?php _e( 'Right', 'formidable' ) ?></option>
                            <option value="inline"<?php selected($field['label'], 'inline'); ?>><?php _e( 'Inline (left without a set width)', 'formidable' ) ?></option>
                            <option value="none"<?php selected($field['label'], 'none'); ?>><?php _e( 'None', 'formidable' ) ?></option>
                            <option value="hidden"<?php selected($field['label'], 'hidden'); ?>><?php _e( 'Hidden (but leave the space)', 'formidable' ) ?></option>
                        </select>
                        </td>
                    </tr>
                <?php }

				// Field Size
				if ( $display['size'] ) {
					if ( in_array( $field['type'], array( 'select', 'time', 'data' ) ) ) {
						if ( ! isset( $values['custom_style'] ) || $values['custom_style'] ) {
							include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/automatic-width.php' );
						}
					} else {
						$display_max = $display['max'];
						include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/pixels-wide.php' );
					}
				}

				if ( $display['captcha_size'] && $frm_settings->re_type != 'invisible' ) { ?>
                <tr><td><label><?php _e( 'ReCaptcha Type', 'formidable' ) ?></label>
					<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Set the size of the captcha field. The compact option is best if your form is in a small area.', 'formidable' ) ?>" ></span>
                    </td>
                    <td>
					<select name="field_options[captcha_size_<?php echo esc_attr( $field['id'] ) ?>]">
						<option value="normal" <?php selected( $field['captcha_size'], 'normal' ); ?>>
							<?php _e( 'Normal', 'formidable' ) ?>
						</option>
						<option value="compact" <?php selected( $field['captcha_size'], 'compact' ); ?>>
							<?php _e( 'Compact', 'formidable' ) ?>
						</option>
                    </select>
                    </td>
                </tr>
				<tr>
					<td>
						<label for="captcha_theme_<?php echo esc_attr( $field['field_key'] ) ?>"><?php _e( 'reCAPTCHA Color', 'formidable' ) ?></label>
					</td>
					<td>
						<select name="field_options[captcha_theme_<?php echo esc_attr( $field['id'] ) ?>]" id="captcha_theme_<?php echo esc_attr( $field['field_key'] ) ?>">
							<option value="light" <?php selected( $field['captcha_theme'], 'light' ); ?>><?php _e( 'Light', 'formidable' ) ?></option>
							<option value="dark" <?php selected( $field['captcha_theme'], 'dark' ); ?>><?php _e( 'Dark', 'formidable' ) ?></option>
						</select>
					</td>
				</tr>
                <?php
				} ?>
                <?php
				do_action( 'frm_' . $field['type'] . '_field_options_form', $field, $display, $values );
				do_action( 'frm_field_options_form', $field, $display, $values );

                if ( $display['required'] || $display['invalid'] || $display['unique'] || $display['conf_field'] ) { ?>
					<tr class="frm_validation_msg <?php echo ($display['invalid'] || $field['required'] || FrmField::is_option_true( $field, 'unique' ) || FrmField::is_option_true( $field, 'conf_field' ) ) ? '' : 'frm_hidden'; ?>">
					<td colspan="2">
                    <div class="menu-settings">
                    <h3 class="frm_no_bg"><?php _e( 'Validation', 'formidable' ) ?></h3>

                    <div class="frm_validation_box">
                        <?php
						if ( $display['required'] ) { ?>
                        <p class="frm_required_details<?php echo esc_attr( $field['id'] . ( $field['required'] ? '' : ' frm_hidden' ) ); ?>"><label><?php _e( 'Required', 'formidable' ) ?></label>
                            <input type="text" name="field_options[blank_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['blank'] ); ?>" />
                        </p>
                        <?php
                        }

						if ( $display['invalid'] ) { ?>
                            <p><label><?php _e( 'Invalid Format', 'formidable' ) ?></label>
                                <input type="text" name="field_options[invalid_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['invalid'] ); ?>" />
                            </p>
                        <?php
						}

						if ( $display['unique'] ) { ?>
                        <p class="frm_unique_details<?php echo esc_attr( $field['id'] . ( $field['unique'] ? '' : ' frm_hidden' ) ); ?>">
                            <label><?php _e( 'Unique', 'formidable' ) ?></label>
                            <input type="text" name="field_options[unique_msg_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['unique_msg'] ); ?>" />
                        </p>
                        <?php
                        }

						if ( $display['conf_field'] ) { ?>
                        <p class="frm_conf_details<?php echo esc_attr( $field['id'] . ( $field['conf_field'] ? '' : ' frm_hidden' ) ); ?>">
                            <label><?php _e( 'Confirmation', 'formidable' ) ?></label>
                            <input type="text" name="field_options[conf_msg_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['conf_msg'] ); ?>" />
                        </p>
                        <?php
                        } ?>
                    </div>
                    </div>
                    </td>
                    </tr>
                <?php } ?>

            </table>
        </div>
    </div>
<?php }

if ( $field['type'] == 'divider' ) { ?>
</div>
<div class="frm_no_section_fields">
	<p class="howto"><?php _e( 'Drag fields from your form or the sidebar into this section', 'formidable' ) ?></p>
</div>
<ul class="start_divider frm_sorting">
<?php
} else if ( $field['type'] == 'end_divider' ) { ?>
</ul>
<?php
}

if ( ! isset( $ajax ) && $field['type'] != 'divider' ) { ?>
</li>
<?php
}

unset($display);
