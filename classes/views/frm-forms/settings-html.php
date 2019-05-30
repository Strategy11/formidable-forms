<div class="frm_field_html_box frm_top_container">
	<p>
		<label><?php esc_html_e( 'Form Classes', 'formidable' ); ?></label>
		<input type="text" name="options[form_class]" value="<?php echo esc_attr( $values['form_class'] ); ?>" />
	</p>
	<div class="clear"></div>

	<p class="frm_has_shortcodes">
		<label><?php esc_html_e( 'Before Fields', 'formidable' ); ?></label>
		<textarea name="options[before_html]" rows="4" id="before_html" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea( $values['before_html'] ); // WPCS: XSS ok. ?></textarea>
	</p>

	<div id="add_html_fields">
		<?php
		if ( isset( $values['fields'] ) ) {
			foreach ( $values['fields'] as $field ) {
				if ( FrmFieldFactory::field_has_html( $field['type'] ) ) {
					?>
					<p class="frm_has_shortcodes">
						<label><?php echo esc_html( $field['name'] ); ?></label>
						<textarea name="field_options[custom_html_<?php echo esc_attr( $field['id'] ); ?>]" rows="7" id="custom_html_<?php echo esc_attr( $field['id'] ); ?>" class="field_custom_html frm_long_input"><?php echo FrmAppHelper::esc_textarea( $field['custom_html'] ); // WPCS: XSS ok. ?></textarea>
					</p>
					<?php
				}
				unset( $field );
			}
		}
		?>
	</div>

	<p class="frm_has_shortcodes">
		<label><?php esc_html_e( 'After Fields', 'formidable' ); ?></label>
		<textarea name="options[after_html]" rows="3" id="after_html" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea( $values['after_html'] ); // WPCS: XSS ok. ?></textarea>
	</p>

	<p class="frm_has_shortcodes">
		<label><?php esc_html_e( 'Submit Button', 'formidable' ); ?></label>
		<textarea name="options[submit_html]" rows="3" id="submit_html" class="frm_long_input"><?php echo FrmAppHelper::esc_textarea( $values['submit_html'] ); // WPCS: XSS ok. ?></textarea>
	</p>
</div>
