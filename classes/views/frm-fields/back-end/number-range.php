<p class="frm8 frm_first frm_form_field frm-number-range">
	<label for="frm_format_<?php echo esc_attr( $field['field_key'] ); ?>" class="frm_help" title="<?php esc_attr_e( 'Set the number range the field validation should allow. Browsers that support the HTML5 number field require a number range to determine the numbers seen when clicking the arrows next to the field.', 'formidable' ); ?>">
		<?php esc_html_e( 'Number Range', 'formidable' ); ?>
	</label>
	<span class="frm_grid_container">
		<span class="frm5 frm_form_field frm-range-min">
			<input type="text" name="field_options[minnum_<?php echo absint( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['minnum'] ); ?>" />
		</span>
		<span class="frm5 frm_last frm_form_field">
			<input type="text" name="field_options[maxnum_<?php echo absint( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['maxnum'] ); ?>" />
		</span>
	</span>
</p>
<p class="frm3 frm_last frm_form_field">
	<label for="frm_step_<?php echo esc_attr( $field['field_key'] ); ?>">
		<?php esc_html_e( 'Step', 'formidable' ); ?>
	</label>
	<input type="text" name="field_options[step_<?php echo absint( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['step'] ); ?>" id="frm_step_<?php echo esc_attr( $field['field_key'] ); ?>" />
</p>
