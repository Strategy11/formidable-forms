<div class="frm-single-settings frm_hidden" id="frm-single-settings-<?php echo esc_attr( $field['id'] ); ?>" data-fid="<?php echo esc_attr( $field['id'] ); ?>">
	<input type="hidden" name="frm_fields_submitted[]" value="<?php echo esc_attr( $field['id'] ); ?>" />
	<input type="hidden" name="field_options[field_order_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['field_order'] ); ?>"/>

	<div class="frm-sub-label alignright">
		(ID <?php echo esc_html( $field['id'] ); ?>)
	</div>
	<h3 style="clear:none;">
		<?php echo esc_html( $field_types[ $field['type'] ]['name'] ); ?>
		<?php esc_html_e( 'Field', 'formidable' ); ?>
	</h3>

	<div class="frm_grid_container frm-collapse-me">
		<p>
			<label for="frm_name_<?php echo esc_attr( $field['id'] ); ?>">
				<?php esc_html_e( 'Field Label', 'formidable' ); ?>
			</label>
			<br/>
			<input type="text" name="field_options[name_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['name'] ); ?>" id="frm_name_<?php echo esc_attr( $field['id'] ); ?>" />
		</p>

		<?php if ( $display['description'] ) { ?>
			<p>
				<label for="frm_description_<?php echo esc_attr( $field['id'] ); ?>">
					<?php esc_html_e( 'Field Description', 'formidable' ); ?>
				</label>
				<br/>
				<textarea name="field_options[description_<?php echo esc_attr( $field['id'] ); ?>]" id="frm_description_<?php echo esc_attr( $field['id'] ); ?>" class="frm_long_input"><?php
				echo FrmAppHelper::esc_textarea( $field['description'] ); // WPCS: XSS ok.
				?></textarea>
			</p>
		<?php } ?>

		<p>
			<?php if ( $display['required'] ) { ?>
				<label for="frm_req_field_<?php echo esc_attr( $field['id'] ); ?>" class="frm_inline_label">
					<input type="checkbox" id="frm_req_field_<?php echo esc_attr( $field['id'] ); ?>" class="frm_req_field" name="field_options[required_<?php echo esc_attr( $field['id'] ); ?>]" value="1" <?php checked( $field['required'], 1 ); ?> />
					<?php esc_html_e( 'Required', 'formidable' ); ?>
				</label>
				<?php
			}

			if ( $display['unique'] ) {
				if ( ! isset( $field['unique'] ) ) {
					$field['unique'] = false;
				}
				?>
				<label for="frm_uniq_field_<?php echo esc_attr( $field['id'] ); ?>" class="frm_inline_label frm_help" title="<?php esc_attr_e( 'Unique: Do not allow the same response multiple times. For example, if one user enters \'Joe\', then no one else will be allowed to enter the same name.', 'formidable' ); ?>"><input type="checkbox" name="field_options[unique_<?php echo esc_attr( $field['id'] ); ?>]" id="frm_uniq_field_<?php echo esc_attr( $field['id'] ); ?>" value="1" <?php checked( $field['unique'], 1 ); ?> class="frm_mark_unique" />
					<?php esc_html_e( 'Unique', 'formidable' ); ?>
				</label>
				<?php
			}

			if ( $display['read_only'] ) {
				if ( ! isset( $field['read_only'] ) ) {
					$field['read_only'] = false;
				}
				?>
				<label for="frm_read_only_field_<?php echo esc_attr( $field['id'] ); ?>" class="frm_inline_label frm_help" title="<?php esc_attr_e( 'Read Only: Show this field but do not allow the field value to be edited from the front-end.', 'formidable' ); ?>" >
					<input type="checkbox" id="frm_read_only_field_<?php echo esc_attr( $field['id'] ); ?>" name="field_options[read_only_<?php echo esc_attr( $field['id'] ); ?>]" value="1" <?php checked( $field['read_only'], 1 ); ?>/>
					<?php esc_html_e( 'Read Only', 'formidable' ); ?>
				</label>
				<?php
			}

			do_action( 'frm_field_options_form_top', $field, $display, $values );
			?>
		</p>
		<?php if ( $display['required'] ) { ?>
			<div class="frm_required_details<?php echo esc_attr( $field['id'] . ( $field['required'] ? '' : ' frm_hidden' ) ); ?>">
				<span class="howto">
					<?php esc_html_e( 'Indicate required field with', 'formidable' ); ?>
				</span>
				<input type="text" name="field_options[required_indicator_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['required_indicator'] ); ?>" />
			</div>
		<?php } ?>
	</div>

<?php if ( in_array( $field['type'], array( 'select', 'radio', 'checkbox' ) ) ) { ?>
	<h3>
		<?php echo esc_html( $field_types[ $field['type'] ]['name'] ); ?>
		<?php esc_html_e( 'Options', 'formidable' ); ?>
	</h3>
	<div class="frm_grid_container frm-collapse-me">
	<?php
	if ( isset( $field['post_field'] ) && $field['post_field'] == 'post_category' ) {
		$type = $field['type'];
		do_action( 'frm_after_checkbox', compact( 'field', 'field_name', 'type' ) );

		echo '<p class="howto" id="frm_has_hidden_options_' . esc_attr( $field['id'] ) . '">' . FrmFieldsHelper::get_term_link( $field['taxonomy'] ) . '</p>'; // WPCS: XSS ok.
	} else {
		?>
		<a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=frm_import_choices&field_id=' . $field['id'] . '&TB_iframe=1' ) ); ?>" title="<?php echo esc_attr( FrmAppHelper::truncate( strip_tags( str_replace( '"', '&quot;', $field['name'] ) ), 20 ) . ' ' . __( 'Field Choices', 'formidable' ) ); ?>" class="thickbox frm_orange">
			<?php esc_html_e( 'Bulk Edit Options', 'formidable' ); ?>
		</a>
		<?php do_action( 'frm_add_multiple_opts_labels', $field ); ?>
		<ul id="frm_field_<?php echo esc_attr( $field['id'] ); ?>_opts" class="frm_sortable_field_opts frm_clear<?php echo ( count( $field['options'] ) > 10 ) ? ' frm_field_opts_list' : ''; ?>">
			<?php FrmFieldsHelper::show_single_option( $field ); ?>
		</ul>

		<?php if ( FrmAppHelper::pro_is_installed() ) { ?>
			<div class="frm_small_top_margin">
				<div id="frm_add_field_<?php echo esc_attr( $field['id'] ); ?>">
					<a href="javascript:void(0);" id="other_button_<?php echo esc_attr( $field['id'] ); ?>" data-opttype="other" data-ftype="<?php echo esc_attr( $field['type'] ); ?>" class="frm_cb_button frm_add_opt<?php echo ( in_array( $field['type'], array( 'radio', 'select' ) ) && $field['other'] == true ? ' frm_hidden' : '' ); ?>" data-clicks="0">
						<span class="frm_add_tag frm_icon_font"></span>
						<?php esc_html_e( 'Other', 'formidable' ); ?>
					</a>
				</div>
			</div>

			<input type="hidden" value="<?php echo esc_attr( $field['other'] ); ?>" id="other_input_<?php echo esc_attr( $field['id'] ); ?>" name="field_options[other_<?php echo esc_attr( $field['id'] ); ?>]" />
			<?php
		}
	}
}

if ( $display['clear_on_focus'] ) {
	FrmFieldsHelper::clear_on_focus_html( $field, $display );
	do_action( 'frm_extra_field_display_options', $field );
}

?>

	<?php do_action( 'frm_before_field_options', $field ); ?>

	<h3><?php esc_html_e( 'Advanced', 'formidable' ); ?></h3>
	<div class="frm_grid_container frm-collapse-me">
		<?php if ( $display['label_position'] ) { ?>
			<p>
				<label><?php esc_html_e( 'Label Position', 'formidable' ); ?></label>
				<br/>
				<select name="field_options[label_<?php echo esc_attr( $field['id'] ); ?>]">
					<option value=""<?php selected( $field['label'], '' ); ?>>
						<?php esc_html_e( 'Default', 'formidable' ); ?>
					</option>
					<?php
					foreach ( FrmStylesHelper::get_single_label_positions() as $pos => $pos_label ) {
						if ( ! $display['clear_on_focus'] && 'inside' === $pos ) {
							// Don't allow inside labels for fields without placeholders.
							continue;
						}
						?>
						<option value="<?php echo esc_attr( $pos ); ?>"<?php selected( $field['label'], $pos ); ?>>
							<?php echo esc_html( $pos_label ); ?>
						</option>
					<?php } ?>
				</select>
			</p>
		<?php } ?>

		<?php if ( $display['css'] ) { ?>
			<p>
				<label for="frm_classes_<?php echo esc_attr( $field['id'] ); ?>" class="frm_help" title="<?php esc_attr_e( 'Add a CSS class to the field container. Use our predefined classes to align multiple fields in single row.', 'formidable' ); ?>">
					<?php esc_html_e( 'CSS Layout Classes', 'formidable' ); ?>
				</label>
				<br/>
				<input type="text" name="field_options[classes_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['classes'] ); ?>" id="frm_classes_<?php echo esc_attr( $field['id'] ); ?>" class="frm_classes" />
			</p>
		<?php } ?>

		<?php
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
		?>

		<p>
			<label for="field_options_field_key_<?php echo esc_attr( $field['id'] ); ?>" class="frm_help" title="<?php esc_attr_e( 'The field key can be used as an alternative to the field ID in many cases.', 'formidable' ); ?>">
				<?php esc_html_e( 'Field Key', 'formidable' ); ?>
			</label>
			<br/>
			<input type="text" name="field_options[field_key_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['field_key'] ); ?>" id="field_options_field_key_<?php echo esc_attr( $field['id'] ); ?>"/>
		</p>

		<?php if ( count( $field_types ) > 1  ) { ?>
			<p>
				<label for="field_options_type_<?php echo esc_attr( $field['id'] ); ?>">
					<?php esc_html_e( 'Field Type', 'formidable' ); ?>
				</label>
				<br/>
				<select name="field_options[type_<?php esc_attr( $field['id'] ) ?>]" id="field_options_type_<?php echo esc_attr( $field['id'] ); ?>">
					<?php foreach ( $field_types as $fkey => $ftype ) { ?>
						<option value="<?php echo esc_attr( $fkey ); ?>" <?php echo ( $fkey === $field['type'] ) ? ' selected="selected"' : ''; ?> <?php echo array_key_exists( $fkey, $disabled_fields ) ? 'disabled="disabled"' : ''; ?>>
							<?php echo esc_html( is_array( $ftype ) ? $ftype['name'] : $ftype ); ?> 
						</option>
						<?php
						unset( $fkey, $ftype );
					}
					?>
				</select>
			</p>
		<?php } ?>

		<?php if ( $display['show_image'] ) { ?>
			<p>
				<label for="frm_show_image_<?php echo esc_attr( $field['id'] ); ?>">
					<input type="checkbox" id="frm_show_image_<?php echo esc_attr( $field['id'] ); ?>" name="field_options[show_image_<?php echo esc_attr( $field['id'] ); ?>]" value="1" <?php checked( $field['show_image'], 1 ); ?> />
					<?php esc_html_e( 'If this URL points to an image, show to image on the entries listing page.', 'formidable' ); ?>
				</label>
			</p>
		<?php } ?>

		<?php if ( $display['captcha_size'] && $frm_settings->re_type !== 'invisible' ) { ?>
			<p class="frm6 frm_first frm_form_field">
				<label for="field_options_captcha_size_<?php echo esc_attr( $field['id'] ); ?>" class="frm_help" title="<?php esc_attr_e( 'Set the size of the captcha field. The compact option is best if your form is in a small area.', 'formidable' ); ?>">
					<?php esc_html_e( 'ReCaptcha Type', 'formidable' ); ?>
				</label>
				<br/>
				<select name="field_options[captcha_size_<?php echo esc_attr( $field['id'] ); ?>]" id="field_options_captcha_size_<?php echo esc_attr( $field['id'] ); ?>">
					<option value="normal" <?php selected( $field['captcha_size'], 'normal' ); ?>>
						<?php esc_html_e( 'Normal', 'formidable' ); ?>
					</option>
					<option value="compact" <?php selected( $field['captcha_size'], 'compact' ); ?>>
						<?php esc_html_e( 'Compact', 'formidable' ); ?>
					</option>
				</select>
			</p>
			<p class="frm6 frm_form_field">
				<label for="captcha_theme_<?php echo esc_attr( $field['field_key'] ); ?>">
					<?php esc_html_e( 'reCAPTCHA Color', 'formidable' ); ?>
				</label>
				<br/>
				<select name="field_options[captcha_theme_<?php echo esc_attr( $field['id'] ); ?>]" id="captcha_theme_<?php echo esc_attr( $field['field_key'] ); ?>">
					<option value="light" <?php selected( $field['captcha_theme'], 'light' ); ?>>
						<?php esc_html_e( 'Light', 'formidable' ); ?>
					</option>
					<option value="dark" <?php selected( $field['captcha_theme'], 'dark' ); ?>>
						<?php esc_html_e( 'Dark', 'formidable' ); ?>
					</option>
				</select>
			</p>
		<?php } ?>
	</div>

	<table class="form-table frm_clear_none">
		<?php

		if ( $display['format'] ) {
			FrmFieldsController::show_format_option( $field );
		}

		if ( $display['range'] ) {
			include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/number-range.php' );
		} elseif ( $field['type'] === 'html' ) {
			include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/html-content.php' );
		}

		$field_obj->show_options( $field, $display, $values );
		do_action( 'frm_field_options_form', $field, $display, $values );
		?>
	</table>

	<?php if ( $display['required'] || $display['invalid'] || $display['unique'] || $display['conf_field'] ) { ?>
		<div class="frm_validation_msg <?php echo ( $display['invalid'] || $field['required'] || FrmField::is_option_true( $field, 'unique' ) || FrmField::is_option_true( $field, 'conf_field' ) ) ? '' : 'frm_hidden'; ?>">

			<h3><?php esc_html_e( 'Validation Messages', 'formidable' ); ?></h3>

			<div class="frm_validation_box frm-collapse-me">
				<?php if ( $display['required'] ) { ?>
					<p class="frm_required_details<?php echo esc_attr( $field['id'] . ( $field['required'] ? '' : ' frm_hidden' ) ); ?>">
						<label for="field_options_blank_<?php echo esc_attr( $field['id'] ); ?>">
							<?php esc_html_e( 'Required', 'formidable' ); ?>
						</label>
						<br/>
						<input type="text" name="field_options[blank_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['blank'] ); ?>" id="field_options_blank_<?php echo esc_attr( $field['id'] ); ?>"/>
					</p>
				<?php } ?>

				<?php
				if ( $display['invalid'] ) {
					$hidden = FrmField::is_field_type( $field, 'text' ) && ! FrmField::is_option_true( $field, 'format' );
					?>
					<p class="frm_invalid_msg<?php echo esc_attr( $field['id'] . ( $hidden ? ' frm_hidden' : '' ) ); ?>">
						<label for="field_options_invalid_<?php echo esc_attr( $field['id'] ); ?>">
							<?php esc_html_e( 'Invalid Format', 'formidable' ); ?>
						</label>
						<br/>
						<input type="text" name="field_options[invalid_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['invalid'] ); ?>" id="field_options_invalid_<?php echo esc_attr( $field['id'] ); ?>"/>
					</p>
					<?php
				}

				if ( $display['unique'] ) {
					?>
					<p class="frm_unique_details<?php echo esc_attr( $field['id'] . ( $field['unique'] ? '' : ' frm_hidden' ) ); ?>">
						<label for="field_options_unique_msg_<?php echo esc_attr( $field['id'] ); ?>">
							<?php esc_html_e( 'Unique', 'formidable' ); ?>
						</label>
						<br/>
						<input type="text" name="field_options[unique_msg_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['unique_msg'] ); ?>" id="field_options_unique_msg_<?php echo esc_attr( $field['id'] ); ?>" />
					</p>
					<?php
				}

				if ( $display['conf_field'] ) {
					?>
					<p class="frm_conf_details<?php echo esc_attr( $field['id'] . ( $field['conf_field'] ? '' : ' frm_hidden' ) ); ?>">
						<label for="field_options_conf_msg_<?php echo esc_attr( $field['id'] ); ?>">
							<?php esc_html_e( 'Confirmation', 'formidable' ); ?>
						</label>
						<br/>
						<input type="text" name="field_options[conf_msg_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['conf_msg'] ); ?>" id="field_options_conf_msg_<?php echo esc_attr( $field['id'] ); ?>" />
					</p>
					<?php
				}
				?>
			</div>
		</div>
	<?php } ?>

	<?php if ( $display['conf_field'] ) { ?>
		<input type="hidden" name="field_options[conf_desc_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['conf_desc'] ); ?>" />
	<?php } ?>
</div>
