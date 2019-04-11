<p class="frm-has-modal">
	<label for="frm_format_<?php echo esc_attr( $field['field_key'] ); ?>" class="frm_help" title="<?php esc_attr_e( 'Insert the format you would like to accept. Use a regular expression starting with ^ or an exact format like (999)999-9999.', 'formidable' ); ?>">
		<?php esc_html_e( 'Format', 'formidable' ); ?>
	</label>
	<span class="frm-with-right-icon">
		<i class="frm-show-inline-modal fas fa-ellipsis-h" data-open="frm-input-mask-box"></i>
		<input type="text" class="frm_long_input frm_format_opt" value="<?php echo esc_attr( $field['format'] ); ?>" name="field_options[format_<?php echo absint( $field['id'] ); ?>]" id="frm_format_<?php echo absint( $field['id'] ); ?>" />
	</span>
</p>
