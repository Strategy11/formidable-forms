<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-single-settings frm_hidden frm-fields frm-type-<?php echo esc_attr( $field['type'] ); ?>" id="frm-single-settings-<?php echo esc_attr( $field['id'] ); ?>" data-fid="<?php echo esc_attr( $field['id'] ); ?>">
	<input type="hidden" name="frm_fields_submitted[]" value="<?php echo esc_attr( $field['id'] ); ?>" />
	<input type="hidden" name="field_options[field_order_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['field_order'] ); ?>"/>

	<div class="frm-sub-label alignright">
		(ID <?php echo esc_html( $field['id'] ); ?>)
	</div>
	<h3>
		<?php
		printf(
			/* translators: %s: Field type */
			esc_html__( '%s Field', 'formidable' ),
			esc_html( $type_name )
		);
		?>
	</h3>

	<div class="frm_grid_container frm-collapse-me">
		<?php
		if ( $field['type'] === 'captcha' && ! FrmFieldCaptcha::should_show_captcha() ) {
			?>
			<div class="frm_builder_captcha frm_warning_style">
				<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_alert_icon' ); ?>
				<div><b><?php echo esc_html__( 'Setup a captcha', 'formidable' ); ?></b>
					<p>
						<?php
						/* translators: %1$s: Link HTML, %2$s: End link */
						printf( esc_html__( 'Your captcha will not appear on your form until you %1$sset up%2$s the Site and Secret Keys', 'formidable' ), '<a href="?page=formidable-settings" target="_blank">', '</a>' );
						?>
					</p>
				</div>
			</div>
		<?php } ?>
		<?php if ( $display['label'] ) { ?>
		<p>
			<label for="frm_name_<?php echo esc_attr( $field['id'] ); ?>">
				<?php echo esc_html( apply_filters( 'frm_builder_field_label', __( 'Field Label', 'formidable' ), $field ) ); ?>
			</label>
			<input type="text" name="field_options[name_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['name'] ); ?>" id="frm_name_<?php echo esc_attr( $field['id'] ); ?>" data-changeme="field_label_<?php echo esc_attr( $field['id'] ); ?>" />
		</p>
		<?php } else { ?>
			<input type="hidden" name="field_options[name_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['name'] ); ?>" id="frm_name_<?php echo esc_attr( $field['id'] ); ?>" />
		<?php } ?>

		<p class="frm-hide-empty">
			<?php if ( $display['required'] ) { ?>
				<label for="frm_req_field_<?php echo esc_attr( $field['id'] ); ?>" class="frm_inline_label">
					<input type="checkbox" id="frm_req_field_<?php echo esc_attr( $field['id'] ); ?>" class="frm_req_field" name="field_options[required_<?php echo esc_attr( $field['id'] ); ?>]" value="1" <?php checked( $field['required'], 1 ); ?> />
					<?php esc_html_e( 'Required', 'formidable' ); ?>
				</label>
				<?php
			}

			if ( $display['unique'] ) {
				?>
				<label for="frm_uniq_field_<?php echo esc_attr( $field['id'] ); ?>" class="frm_inline_label frm_help" title="<?php esc_attr_e( 'Unique: Do not allow the same response multiple times. For example, if one user enters \'Joe\', then no one else will be allowed to enter the same name.', 'formidable' ); ?>"><input type="checkbox" name="field_options[unique_<?php echo esc_attr( $field['id'] ); ?>]" id="frm_uniq_field_<?php echo esc_attr( $field['id'] ); ?>" value="1" <?php checked( $field['unique'], 1 ); ?> class="frm_mark_unique" />
					<?php esc_html_e( 'Unique', 'formidable' ); ?>
				</label>
				<?php
			}

			if ( $display['read_only'] ) {
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

		<?php
		if ( $display['range'] ) {
			include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/number-range.php' );
		}

		$field_obj->show_primary_options( compact( 'field', 'display', 'values' ) );

		?>

		<?php if ( $display['css'] ) { ?>
			<p class="frm-has-modal">
				<label for="frm_classes_<?php echo esc_attr( $field['id'] ); ?>" class="frm_help" title="<?php esc_attr_e( 'Add a CSS class to the field container. Use our predefined classes to align multiple fields in single row.', 'formidable' ); ?>">
					<?php esc_html_e( 'CSS Layout Classes', 'formidable' ); ?>
				</label>
				<span class="frm-with-right-icon">
					<?php
					FrmAppHelper::icon_by_class(
						'frm_icon_font frm_more_horiz_solid_icon frm-show-inline-modal',
						array(
							'data-open' => 'frm-layout-classes-box',
							'title'     => esc_attr__( 'Toggle Options', 'formidable' ),
						)
					);
					?>
					<input type="text" name="field_options[classes_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['classes'] ); ?>" class="frm_classes" id="frm_classes_<?php echo esc_attr( $field['id'] ); ?>" data-changeme="frm_field_id_<?php echo esc_attr( $field['id'] ); ?>" data-changeatt="class" data-sep=" " data-shortcode="0" />
				</span>
			</p>
		<?php } ?>
	</div>

<?php

$field_obj->show_field_choices( compact( 'field', 'display', 'values' ) );

if ( $display['clear_on_focus'] ) {
	do_action( 'frm_extra_field_display_options', $field );
}

/**
 * Fires after printing the primary options section of field.
 *
 * @since 5.0.04 Added `$display` and `$values`.
 *
 * @param array        $field     Field data.
 * @param FrmFieldType $field_obj Field type object.
 * @param array        $display   Display data.
 * @param array        $values    Field values.
 */
do_action( 'frm_before_field_options', $field, compact( 'field_obj', 'display', 'values' ) );

?>

	<h3 class="frm-collapsed">
		<?php esc_html_e( 'Advanced', 'formidable' ); ?>
		<i class="frm_icon_font frm_arrowdown6_icon"></i>
	</h3>
	<div class="frm_grid_container frm-collapse-me">

		<?php if ( $display['default'] ) { ?>
			<div class="frm-has-modal">
				<?php if ( count( $default_value_types ) > 1 ) { ?>
				<span class="frm-default-switcher">
					<?php foreach ( $default_value_types as $def_name => $link ) { ?>
					<a href="#" title="<?php echo esc_attr( $link['title'] ); ?>" class="<?php echo esc_attr( $link['class'] ); ?>" data-toggleclass="frm_hidden frm-open"
						<?php foreach ( $link['data'] as $data_key => $data_value ) { ?>
							data-<?php echo esc_attr( $data_key ); ?>="<?php echo esc_attr( $data_value . ( substr( $data_value, -1 ) === '-' ? $field['id'] : '' ) ); ?>"
						<?php } ?>
						<?php if ( isset( $link['data']['frmshow'] ) ) { ?>
							data-frmhide=".frm-inline-modal,.default-value-section-<?php echo esc_attr( $field['id'] ); ?>"
						<?php } ?>
						>
						<?php FrmAppHelper::icon_by_class( $link['icon'] ); ?>
					</a>
					<?php } ?>
				</span>
				<?php } ?>

				<p class="frm-has-modal default-value-section-<?php echo esc_attr( $field['id'] . ( isset( $default_value_types['default_value']['current'] ) ? '' : ' frm_hidden' ) ); ?>" id="default-value-for-<?php echo esc_attr( $field['id'] ); ?>">
					<label for="frm_default_value_<?php echo esc_attr( $field['id'] ); ?>">
						<?php esc_html_e( 'Default Value', 'formidable' ); ?>
					</label>
					<span class="frm-with-right-icon">
						<?php
						$special_default = ( isset( $field['post_field'] ) && $field['post_field'] === 'post_category' ) || $field['type'] === 'data';
						FrmAppHelper::icon_by_class(
							'frm_icon_font frm_more_horiz_solid_icon frm-show-inline-modal',
							array(
								'data-open' => $special_default ? 'frm-tax-box-' . $field['id'] : 'frm-smart-values-box',
								'title'     => esc_attr__( 'Toggle Options', 'formidable' ),
							)
						);
						unset( $special_default );

						if ( isset( $display['default_value'] ) && $display['default_value'] ) {
							$default_name  = 'field_options[dyn_default_value_' . $field['id'] . ']';
							$default_value = isset( $field['dyn_default_value'] ) ? $field['dyn_default_value'] : '';
						} else {
							$default_name  = 'default_value_' . $field['id'];
							$default_value = $field['default_value'];
						}
						$field_obj->default_value_to_string( $default_value );

						if ( $display['type'] === 'textarea' || $display['type'] === 'rte' ) {
							?>
							<textarea name="<?php echo esc_attr( $default_name ); ?>" class="default-value-field" id="frm_default_value_<?php echo esc_attr( $field['id'] ); ?>" rows="3" data-changeme="field_<?php echo esc_attr( $field['field_key'] ); ?>"><?php
								echo FrmAppHelper::esc_textarea( $default_value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							?></textarea>
							<?php
						} else {
							?>
							<input type="text" name="<?php echo esc_attr( $default_name ); ?>" value="<?php echo esc_attr( $default_value ); ?>" id="frm_default_value_<?php echo esc_attr( $field['id'] ); ?>" class="default-value-field" data-changeme="field_<?php echo esc_attr( $field['field_key'] ); ?>" data-changeatt="value" data-sep="<?php echo esc_attr( $field_obj->displayed_field_type( $field ) ? ',' : '' ); ?>" />
							<?php
						}
						?>
					</span>
				</p>
				<?php do_action( 'frm_default_value_setting', compact( 'field', 'display', 'default_value_types' ) ); ?>
			</div>
		<?php } ?>

		<?php $field_obj->show_after_default( compact( 'field', 'display' ) ); ?>

		<?php if ( $display['clear_on_focus'] ) { ?>
			<p>
				<label for="frm_placeholder_<?php echo esc_attr( $field['id'] ); ?>">
					<?php esc_html_e( 'Placeholder Text', 'formidable' ); ?>
				</label>
				<?php
				if ( $display['type'] === 'textarea' || $display['type'] === 'rte' ) {
					?>
					<textarea name="field_options[placeholder_<?php echo esc_attr( $field['id'] ); ?>]" id="frm_placeholder_<?php echo esc_attr( $field['id'] ); ?>" rows="3" data-changeme="field_<?php echo esc_attr( $field['field_key'] ); ?>" data-changeatt="placeholder"><?php
						echo FrmAppHelper::esc_textarea( $field['placeholder'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?></textarea>
					<?php
				} else {
					?>
					<input type="text" name="field_options[placeholder_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['placeholder'] ); ?>" id="frm_placeholder_<?php echo esc_attr( $field['id'] ); ?>" data-changeme="field_<?php echo esc_attr( $field['field_key'] ); ?>" data-changeatt="placeholder" />
					<?php
				}
				?>
			</p>
		<?php } ?>

		<?php
		if ( $display['description'] ) {
			include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/field-description.php' );
		}

		// Field Size
		if ( $display['size'] && ! in_array( $field['type'], array( 'select', 'data', 'time' ) ) ) {
			$display_max = $display['max'];
			include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/pixels-wide.php' );
		}
		?>

		<?php if ( $display['show_image'] ) { ?>
			<p>
				<label for="frm_show_image_<?php echo esc_attr( $field['id'] ); ?>">
					<input type="checkbox" id="frm_show_image_<?php echo esc_attr( $field['id'] ); ?>" name="field_options[show_image_<?php echo esc_attr( $field['id'] ); ?>]" value="1" <?php checked( $field['show_image'], 1 ); ?> />
					<?php esc_html_e( 'If this URL points to an image, show to image on the entries listing page.', 'formidable' ); ?>
				</label>
			</p>
		<?php } ?>

		<?php if ( $display['captcha_size'] && $frm_settings->re_type !== 'invisible' && $frm_settings->active_captcha === 'recaptcha' ) { ?>
			<p class="frm6 frm_first frm_form_field">
				<label for="field_options_captcha_size_<?php echo esc_attr( $field['id'] ); ?>" class="frm_help" title="<?php esc_attr_e( 'Set the size of the captcha field. The compact option is best if your form is in a small area.', 'formidable' ); ?>">
					<?php esc_html_e( 'ReCaptcha Type', 'formidable' ); ?>
				</label>
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

		<?php
		if ( $display['format'] ) {
			FrmFieldsController::show_format_option( $field );
		}
		?>

		<?php do_action( 'frm_field_options', compact( 'field', 'display', 'values' ) ); ?>

		<?php if ( $display['required'] ) { ?>
			<p class="frm6 frm_form_field frm_required_details<?php echo esc_attr( $field['id'] . ( $field['required'] ? '' : ' frm_hidden' ) ); ?>">
				<label>
					<?php esc_html_e( 'Required Field Indicator', 'formidable' ); ?>
				</label>
				<input type="text" name="field_options[required_indicator_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['required_indicator'] ); ?>" />
			</p>
		<?php } ?>

		<?php if ( $display['label_position'] ) { ?>
			<p class="frm6 frm_form_field">
				<label><?php esc_html_e( 'Label Position', 'formidable' ); ?></label>
				<select name="field_options[label_<?php echo esc_attr( $field['id'] ); ?>]">
					<option value="" <?php selected( $field['label'], '' ); ?>>
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
					<?php if ( $field['type'] === 'divider' ) { ?>
						<option value="center" <?php selected( $field['label'], 'center' ); ?>>
							<?php esc_html_e( 'Center', 'formidable' ); ?>
						</option>
					<?php } ?>
				</select>
			</p>
		<?php } ?>

		<p class="frm6 frm_form_field">
			<label for="field_options_field_key_<?php echo esc_attr( $field['id'] ); ?>" class="frm_help" title="<?php esc_attr_e( 'The field key can be used as an alternative to the field ID in many cases.', 'formidable' ); ?>">
				<?php esc_html_e( 'Field Key', 'formidable' ); ?>
			</label>
			<input type="text" name="field_options[field_key_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['field_key'] ); ?>" id="field_options_field_key_<?php echo esc_attr( $field['id'] ); ?>"/>
		</p>

		<?php if ( count( $field_types ) > 1 ) { ?>
			<p class="frm6 frm_form_field">
				<label for="field_options_type_<?php echo esc_attr( $field['id'] ); ?>">
					<?php esc_html_e( 'Field Type', 'formidable' ); ?>
				</label>
				<select name="field_options[type_<?php echo esc_attr( $field['id'] ); ?>]" id="field_options_type_<?php echo esc_attr( $field['id'] ); ?>">
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
		<?php } else { ?>
			<input type="hidden" id="field_options_type_<?php echo esc_attr( $field['id'] ); ?>" value="<?php echo esc_attr( $field['type'] ); ?>" />
		<?php } ?>

		<table class="form-table frm_no_top_margin">
			<?php $field_obj->show_options( $field, $display, $values ); ?>
			<?php do_action( 'frm_field_options_form', $field, $display, $values ); ?>
		</table>
	</div>

	<?php if ( $display['required'] || $display['invalid'] || $display['unique'] || $display['conf_field'] ) { ?>
		<?php
		$hidden_invalid = FrmField::is_field_type( $field, 'text' ) && ! FrmField::is_option_true( $field, 'format' ) && FrmField::is_option_empty( $field, 'max' );
		$has_validation = ( ( $display['invalid'] && ! $hidden_invalid ) || $field['required'] || FrmField::is_option_true( $field, 'unique' ) || FrmField::is_option_true( $field, 'conf_field' ) );
		?>
		<div class="frm_validation_msg <?php echo esc_attr( $has_validation ? '' : 'frm_hidden' ); ?>">
			<h3 class="frm-collapsed">
				<?php esc_html_e( 'Validation Messages', 'formidable' ); ?>
				<i class="frm_icon_font frm_arrowdown6_icon"></i>
			</h3>

			<div class="frm_validation_box frm-collapse-me">
				<?php if ( $display['required'] ) { ?>
					<p class="frm_required_details<?php echo esc_attr( $field['id'] . ( $field['required'] ? '' : ' frm_hidden' ) ); ?>">
						<label for="field_options_blank_<?php echo esc_attr( $field['id'] ); ?>">
							<?php esc_html_e( 'Required', 'formidable' ); ?>
						</label>
						<input type="text" name="field_options[blank_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['blank'] ); ?>" id="field_options_blank_<?php echo esc_attr( $field['id'] ); ?>"/>
					</p>
				<?php } ?>

				<?php if ( $display['invalid'] ) { ?>
					<p class="frm_invalid_msg<?php echo esc_attr( $field['id'] . ( $hidden_invalid ? ' frm_hidden' : '' ) ); ?>">
						<label for="field_options_invalid_<?php echo esc_attr( $field['id'] ); ?>">
							<?php esc_html_e( 'Invalid Format', 'formidable' ); ?>
						</label>
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
						<input type="text" name="field_options[conf_msg_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['conf_msg'] ); ?>" id="field_options_conf_msg_<?php echo esc_attr( $field['id'] ); ?>" />
					</p>
					<?php
				}
				?>
			</div>
		</div>
	<?php } ?>

	<?php do_action( 'frm_after_field_options', compact( 'field', 'display', 'values' ) ); ?>

	<?php if ( $display['conf_field'] ) { ?>
		<input type="hidden" name="field_options[conf_desc_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['conf_desc'] ); ?>" />
	<?php } ?>
</div>
