<li id="frm_field_id_<?php echo esc_attr( $field['id'] ); ?>" class="<?php echo esc_attr( $li_classes ) ?>" data-fid="<?php echo esc_attr( $field['id'] ) ?>" data-formid="<?php echo esc_attr( 'divider' == $field['type'] ? $field['form_select'] : $field['form_id'] ); ?>" data-ftype="<?php echo esc_attr( $display['type'] ) ?>">
<?php if ( $field['type'] == 'divider' ) { ?>
<div class="divider_section_only">
<?php } ?>

    <a href="javascript:void(0);" class="frm_bstooltip alignright frm-show-hover frm-move frm-hover-icon frm_icon_font frm_move_icon" title="<?php esc_attr_e( 'Move Field', 'formidable' ) ?>"> </a>
    <a href="#" class="frm_bstooltip alignright frm-show-hover frm-hover-icon frm_icon_font frm_delete_icon frm_delete_field" title="<?php esc_attr_e( 'Delete Field', 'formidable' ) ?>"> </a>
    <a href="#" class="frm_bstooltip alignright frm-show-hover frm-hover-icon frm_icon_font frm_duplicate_icon" title="<?php ( $field['type'] === 'divider' ) ? esc_attr_e( 'Duplicate Section', 'formidable' ) : esc_attr_e( 'Duplicate Field', 'formidable' ) ?>"> </a>
	<input type="hidden" name="frm_fields_submitted[]" value="<?php echo esc_attr( $field['id'] ) ?>" />
	<?php do_action( 'frm_extra_field_actions', $field['id'] ); ?>
    <?php if ( $display['required'] ) { ?>
    <span id="require_field_<?php echo esc_attr( $field['id'] ); ?>">
		<a href="javascript:void(0);" class="frm_req_field frm_action_icon frm_required_icon frm_icon_font alignleft frm_required<?php echo (int) $field['required'] ?>" id="req_field_<?php echo esc_attr( $field['id'] ); ?>" title="Click to Mark as <?php echo FrmField::is_required( $field ) ? 'not ' : ''; ?>Required"></a>
    </span>
	<?php } ?>
	<label class="<?php echo esc_attr( $field['type'] === 'end_divider' ? '' : 'frm_ipe_field_label' ); ?> frm_primary_label <?php echo esc_attr( $field['type'] === 'break' ? 'button' : '' ); ?>" id="field_label_<?php echo esc_attr( $field['id'] ); ?>"><?php
		echo ( $field['name'] === '' ) ? esc_html__( '(no label)', 'formidable' ) : FrmAppHelper::kses( force_balance_tags( $field['name'] ), 'all' ); // WPCS: XSS ok.
	?></label>
	<input type="hidden" name="field_options[name_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['name'] ); ?>" />

<div id="field_<?php echo esc_attr( $field['id'] ) ?>_inner_container" class="frm_inner_field_container">
<div class="frm_form_fields" data-ftype="<?php echo esc_attr( $display['type'] ) ?>">
<?php

$field_obj->show_on_form_builder();

if ( $display['clear_on_focus'] ) {
	FrmFieldsHelper::clear_on_focus_html( $field, $display );
	do_action( 'frm_extra_field_display_options', $field );
}

?>
<div class="clear"></div>
</div>
<?php if ( $display['description'] ) { ?>
    <div class="frm_ipe_field_desc description <?php echo esc_attr( $field['description'] === '' ? 'frm-show-click' : '' ); ?>" id="field_description_<?php echo esc_attr( $field['id'] ); ?>"><?php
		echo ( $field['description'] === '' ) ? esc_html__( '(Click to add description)', 'formidable' ) : FrmAppHelper::kses( force_balance_tags( $field['description'] ), 'all' ); // WPCS: XSS ok.
	?></div>
    <input type="hidden" name="field_options[description_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['description'] ); ?>" />

<?php } ?>
</div>
<?php if ( $display['conf_field'] ) { ?>
<div id="frm_conf_field_<?php echo esc_attr( $field['id'] ) ?>_container" class="frm_conf_field_container frm_form_fields frm_conf_details<?php echo esc_attr( $field['id'] . ( $field['conf_field'] ? '' : ' frm_hidden' ) ); ?>">
    <div id="frm_conf_field_<?php echo esc_attr( $field['id'] ) ?>_inner_container" class="frm_inner_conf_container">
		<div class="frm_form_fields">
			<input type="text" id="conf_field_<?php echo esc_attr( $field['field_key'] ) ?>" name="field_options[conf_input_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['conf_input'] ); ?>" class="dyn_default_value" />
		</div>
    	<div id="conf_field_description_<?php echo esc_attr( $field['id'] ) ?>" class="frm_ipe_field_conf_desc description <?php echo ( $field['conf_desc'] === '' ) ? 'frm-show-click' : '' ?>"><?php
			echo ( $field['conf_desc'] === '' ) ? esc_html__( '(Click to add description)', 'formidable' ) : FrmAppHelper::kses( force_balance_tags( $field['conf_desc'] ), 'all' ); // WPCS: XSS ok.
		?></div>
    	<input type="hidden" name="field_options[conf_desc_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['conf_desc'] ); ?>" />
</div>
	<?php if ( $display['clear_on_focus'] ) { ?>
        <div class="alignleft">
			<?php FrmFieldsHelper::clear_on_focus_html( $field, $display, '_conf' ); ?>
        </div>
    <?php } ?>
</div>
<div class="clear"></div>
<?php
}

if ( in_array( $field['type'], array( 'select', 'radio', 'checkbox' ) ) ) {
?>
    <div class="frm-show-click frm_small_top_margin">
	<?php

	if ( isset( $field['post_field'] ) && $field['post_field'] === 'post_category' ) {
		echo '<p class="howto" id="frm_has_hidden_options_' . esc_attr( $field['id'] ) . '">' . FrmFieldsHelper::get_term_link( $field['taxonomy'] ) . '</p>'; // WPCS: XSS ok.
	} elseif ( ! isset( $field['post_field'] ) || ! in_array( $field['post_field'], array( 'post_category' ) ) ) {
?>
        <div id="frm_add_field_<?php echo esc_attr( $field['id'] ); ?>">
            <a href="javascript:void(0);" data-opttype="single" class="button frm_cb_button frm_add_opt" data-clicks="0"><?php esc_html_e( 'Add Option', 'formidable' ) ?></a>

            <?php if ( FrmAppHelper::pro_is_installed() ) { ?>
				<a href="javascript:void(0);" id="other_button_<?php echo esc_attr( $field['id'] ); ?>" data-opttype="other" data-ftype="<?php echo esc_attr( $field['type'] ) ?>" class="button frm_cb_button frm_add_opt<?php echo ( in_array( $field['type'], array( 'radio', 'select' ) ) && $field['other'] == true ? ' frm_hidden' : '' ); ?>" data-clicks="0">
					<?php esc_html_e( 'Add "Other"', 'formidable' ) ?>
				</a>
                <input type="hidden" value="<?php echo esc_attr( $field['other'] ); ?>" id="other_input_<?php echo esc_attr( $field['id'] ); ?>" name="field_options[other_<?php echo esc_attr( $field['id'] ); ?>]">
            <?php
            }

			if ( ! isset( $field['post_field'] ) || $field['post_field'] != 'post_category' ) {
            ?>
			<a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=frm_import_choices&field_id=' . $field['id'] . '&TB_iframe=1' ) ) ?>" title="<?php echo esc_attr( FrmAppHelper::truncate( strip_tags( str_replace( '"', '&quot;', $field['name'] ) ), 20 ) . ' ' . __( 'Field Choices', 'formidable' ) ); ?>" class="thickbox frm_orange">
				<?php esc_html_e( 'Bulk Edit Options', 'formidable' ); ?>
			</a>
            <?php } ?>
        </div>
<?php
    }
?>
    </div>
<?php
}

do_action( 'frm_before_field_options', $field );

if ( $display['options'] ) {
?>
    <div class="widget">
        <div class="widget-top">
            <div class="widget-title-action">
                <button type="button" class="widget-action hide-if-no-js" aria-expanded="false">
                    <span class="toggle-indicator" aria-hidden="true"></span>
                </button>
            </div>
            <div class="widget-title">
				<h3><?php esc_html_e( 'Field Settings', 'formidable' ) ?> (ID <?php echo (int) $field['id'] ?>)</h3>
			</div>
        </div>
    	<div class="widget-inside">
            <table class="form-table frm_clear_none">
				<?php $field_types = FrmFieldsHelper::get_field_types( $field['type'] ); ?>
				<tr><td class="frm_150_width"><label><?php esc_html_e( 'Field Type', 'formidable' ) ?></label></td>
                    <td>
                <select <?php echo ( count( $field_types ) === 1 ? 'disabled="disabled"' : 'name="field_options[type_' . esc_attr( $field['id'] ) . ']"' ); ?>>
                    <?php foreach ( $field_types as $fkey => $ftype ) { ?>
						<option value="<?php echo esc_attr( $fkey ) ?>" <?php echo ( $fkey === $field['type'] ) ? ' selected="selected"' : ''; ?> <?php echo array_key_exists( $fkey, $disabled_fields ) ? 'disabled="disabled"' : ''; ?>>
							<?php echo esc_html( is_array( $ftype ) ? $ftype['name'] : $ftype ); ?> 
						</option>
                    <?php
						unset( $fkey, $ftype );
					}
					?>
                </select>

                <?php if ( $display['required'] ) { ?>
					<label for="frm_req_field_<?php echo esc_attr( $field['id'] ) ?>" class="frm_inline_label">
						<input type="checkbox" id="frm_req_field_<?php echo esc_attr( $field['id'] ) ?>" class="frm_req_field" name="field_options[required_<?php echo esc_attr( $field['id'] ) ?>]" value="1" <?php checked( $field['required'], 1 ) ?> />
						<?php esc_html_e( 'Required', 'formidable' ); ?>
					</label>
                <?php
                }

				if ( $display['unique'] ) {
                    if ( ! isset( $field['unique'] ) ) {
                        $field['unique'] = false;
                    }
					?>
                <label for="frm_uniq_field_<?php echo esc_attr( $field['id'] ) ?>" class="frm_inline_label frm_help" title="<?php esc_attr_e( 'Unique: Do not allow the same response multiple times. For example, if one user enters \'Joe\', then no one else will be allowed to enter the same name.', 'formidable' ) ?>"><input type="checkbox" name="field_options[unique_<?php echo esc_attr( $field['id'] ) ?>]" id="frm_uniq_field_<?php echo esc_attr( $field['id'] ) ?>" value="1" <?php checked( $field['unique'], 1 ); ?> class="frm_mark_unique" />
					<?php esc_html_e( 'Unique', 'formidable' ); ?>
				</label>
                <?php
                }

				if ( $display['read_only'] ) {
                    if ( ! isset( $field['read_only'] ) ) {
                        $field['read_only'] = false;
					}
					?>
				<label for="frm_read_only_field_<?php echo esc_attr( $field['id'] ) ?>" class="frm_inline_label frm_help" title="<?php esc_attr_e( 'Read Only: Show this field but do not allow the field value to be edited from the front-end.', 'formidable' ) ?>" >
					<input type="checkbox" id="frm_read_only_field_<?php echo esc_attr( $field['id'] ) ?>" name="field_options[read_only_<?php echo esc_attr( $field['id'] ) ?>]" value="1" <?php checked( $field['read_only'], 1 ) ?>/>
					<?php esc_html_e( 'Read Only', 'formidable' ); ?>
				</label>
				<?php
				}

                do_action( 'frm_field_options_form_top', $field, $display, $values );

				if ( $display['required'] ) {
				?>
                <div class="frm_required_details<?php echo esc_attr( $field['id'] . ( $field['required'] ? '' : ' frm_hidden' ) ); ?>">
                    <span class="howto"><?php esc_html_e( 'Indicate required field with', 'formidable' ); ?></span>
                    <input type="text" name="field_options[required_indicator_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['required_indicator'] ); ?>" />
                </div>
                <?php } ?>
                    </td>
                </tr>
				<tr>
					<td class="frm_150_width">
						<div class="hide-if-no-js edit-slug-box frm_help" title="<?php esc_attr_e( 'The field key can be used as an alternative to the field ID in many cases.', 'formidable' ) ?>">
                            <?php esc_html_e( 'Field Key', 'formidable' ); ?>
						</div>
					</td>
					<td>
						<input type="text" name="field_options[field_key_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['field_key'] ); ?>" />
					</td>
				</tr>

                <?php if ( $display['css'] ) { ?>
                <tr><td><label><?php esc_html_e( 'CSS layout classes', 'formidable' ) ?></label>
					<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Add a CSS class to the field container. Use our predefined classes to align multiple fields in single row.', 'formidable' ) ?>" ></span>
                    </td>
                    <td><input type="text" name="field_options[classes_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['classes'] ) ?>" id="frm_classes_<?php echo esc_attr( $field['id'] ) ?>" class="frm_classes frm_long_input" />
                    </td>
                </tr>
                <?php } ?>

                <?php if ( $display['label_position'] ) { ?>
					<tr>
						<td class="frm_150_width"><label><?php esc_html_e( 'Label Position', 'formidable' ) ?></label></td>
						<td>
							<select name="field_options[label_<?php echo esc_attr( $field['id'] ) ?>]">
								<option value=""<?php selected( $field['label'], '' ); ?>>
									<?php esc_html_e( 'Default', 'formidable' ) ?>
								</option>
								<?php foreach ( FrmStylesHelper::get_single_label_positions() as $pos => $pos_label ) { ?>
									<?php
									if ( ! $display['clear_on_focus'] && 'inside' === $pos ) {
										// don't allow inside labels for fields without placeholders
										continue;
									}
									?>
									<option value="<?php echo esc_attr( $pos ) ?>"<?php selected( $field['label'], $pos ); ?>>
										<?php echo esc_html( $pos_label ) ?>
									</option>
								<?php } ?>
							</select>
						</td>
					</tr>
				<?php
                }

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

				if ( $display['show_image'] ) {
				?>
					<tr>
						<td class="frm_150_width">
							<label><?php esc_html_e( 'Show URL image', 'formidable' ) ?></label>
						</td>
						<td>
							<label for="frm_show_image_<?php echo esc_attr( $field['id'] ) ?>">
								<input type="checkbox" id="frm_show_image_<?php echo esc_attr( $field['id'] ) ?>" name="field_options[show_image_<?php echo esc_attr( $field['id'] ) ?>]" value="1" <?php checked( $field['show_image'], 1 ) ?> />
								<?php esc_html_e( 'If this URL points to an image, show to image on the entries listing page.', 'formidable' ); ?>
							</label>
						</td>
					</tr>
				<?php
				}

				if ( $display['captcha_size'] && $frm_settings->re_type !== 'invisible' ) {
				?>
                <tr><td><label><?php esc_html_e( 'ReCaptcha Type', 'formidable' ) ?></label>
					<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Set the size of the captcha field. The compact option is best if your form is in a small area.', 'formidable' ) ?>" ></span>
                    </td>
                    <td>
					<select name="field_options[captcha_size_<?php echo esc_attr( $field['id'] ) ?>]">
						<option value="normal" <?php selected( $field['captcha_size'], 'normal' ); ?>>
							<?php esc_html_e( 'Normal', 'formidable' ) ?>
						</option>
						<option value="compact" <?php selected( $field['captcha_size'], 'compact' ); ?>>
							<?php esc_html_e( 'Compact', 'formidable' ) ?>
						</option>
                    </select>
                    </td>
                </tr>
				<tr>
					<td>
						<label for="captcha_theme_<?php echo esc_attr( $field['field_key'] ) ?>">
							<?php esc_html_e( 'reCAPTCHA Color', 'formidable' ) ?>
						</label>
					</td>
					<td>
						<select name="field_options[captcha_theme_<?php echo esc_attr( $field['id'] ) ?>]" id="captcha_theme_<?php echo esc_attr( $field['field_key'] ) ?>">
							<option value="light" <?php selected( $field['captcha_theme'], 'light' ); ?>>
								<?php esc_html_e( 'Light', 'formidable' ) ?>
							</option>
							<option value="dark" <?php selected( $field['captcha_theme'], 'dark' ); ?>>
								<?php esc_html_e( 'Dark', 'formidable' ) ?>
							</option>
						</select>
					</td>
				</tr>
                <?php
				}

				if ( $display['format'] ) {
					FrmFieldsController::show_format_option( $field );
				}

				if ( $display['range'] ) {
					include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/number-range.php' );
				} elseif ( $field['type'] == 'html' ) {
					include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/html-content.php' );
				}

				$field_obj->show_options( $field, $display, $values );
				do_action( 'frm_field_options_form', $field, $display, $values );

				if ( $display['required'] || $display['invalid'] || $display['unique'] || $display['conf_field'] ) {
                ?>
					<tr class="frm_validation_msg <?php echo ( $display['invalid'] || $field['required'] || FrmField::is_option_true( $field, 'unique' ) || FrmField::is_option_true( $field, 'conf_field' ) ) ? '' : 'frm_hidden'; ?>">
					<td colspan="2">
                    <div class="menu-settings">
                    <h3 class="frm_no_bg"><?php esc_html_e( 'Validation', 'formidable' ) ?></h3>

                    <div class="frm_validation_box">
						<?php
						if ( $display['required'] ) {
						?>
                        <p class="frm_required_details<?php echo esc_attr( $field['id'] . ( $field['required'] ? '' : ' frm_hidden' ) ); ?>"><label><?php esc_html_e( 'Required', 'formidable' ) ?></label>
                            <input type="text" name="field_options[blank_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['blank'] ); ?>" />
                        </p>
                        <?php
                        }

						if ( $display['invalid'] ) {
							$hidden = FrmField::is_field_type( $field, 'text' ) && ! FrmField::is_option_true( $field, 'format' );
						?>
						<p class="frm_invalid_msg<?php echo esc_attr( $field['id'] . ( $hidden ? ' frm_hidden' : '' ) ); ?>">
							<label><?php esc_html_e( 'Invalid Format', 'formidable' ) ?></label>
							<input type="text" name="field_options[invalid_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['invalid'] ); ?>" />
						</p>
                        <?php
						}

						if ( $display['unique'] ) {
						?>
                        <p class="frm_unique_details<?php echo esc_attr( $field['id'] . ( $field['unique'] ? '' : ' frm_hidden' ) ); ?>">
                            <label><?php esc_html_e( 'Unique', 'formidable' ) ?></label>
                            <input type="text" name="field_options[unique_msg_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['unique_msg'] ); ?>" />
                        </p>
                        <?php
                        }

						if ( $display['conf_field'] ) {
						?>
                        <p class="frm_conf_details<?php echo esc_attr( $field['id'] . ( $field['conf_field'] ? '' : ' frm_hidden' ) ); ?>">
                            <label><?php esc_html_e( 'Confirmation', 'formidable' ) ?></label>
                            <input type="text" name="field_options[conf_msg_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['conf_msg'] ); ?>" />
                        </p>
						<?php
						}
						?>
                    </div>
                    </div>
                    </td>
                    </tr>
                <?php } ?>

            </table>
        </div>
    </div>
<?php
}

if ( 'divider' === $field['type'] ) {
?>
</div>
<div class="frm_no_section_fields">
	<p class="howto"><?php esc_html_e( 'Drag fields from your form or the sidebar into this section', 'formidable' ) ?></p>
</div>
<ul class="start_divider frm_sorting">
<?php
} elseif ( 'end_divider' === $field['type'] ) {
?>
</ul>
<?php
}

if ( $field['type'] !== 'divider' ) {
?>
</li>
<?php
}
