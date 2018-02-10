<tr>
	<td><label><?php esc_html_e( 'Format', 'formidable' ) ?></label>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Insert the format you would like to accept. Use a regular expression starting with ^ or an exact format like (999)999-9999.', 'formidable' ) ?>" ></span>
	</td>
	<td><input type="text" class="frm_long_input frm_format_opt" value="<?php echo esc_attr( $field['format'] ) ?>" name="field_options[format_<?php echo absint( $field['id'] ) ?>]" id="frm_format_<?php echo absint( $field['id'] ) ?>" />
	</td>
</tr>
