<tr>
	<td style="width:150px">
		<label><?php esc_html_e( 'Number Range', 'formidable' ) ?>
			<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Browsers that support the HTML5 number field require a number range to determine the numbers seen when clicking the arrows next to the field.', 'formidable' ) ?>" ></span>
		</label>
	</td>
	<td>
		<input type="text" name="field_options[minnum_<?php echo absint( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['minnum'] ); ?>" size="5" />
		<span class="howto"><?php esc_html_e( 'minimum', 'formidable' ) ?></span>
		<input type="text" name="field_options[maxnum_<?php echo absint( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['maxnum'] ); ?>" size="5" />
		<span class="howto"><?php esc_html_e( 'maximum', 'formidable' ) ?></span>
		<input type="text" name="field_options[step_<?php echo absint( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['step'] ); ?>" size="5" />
		<span class="howto"><?php esc_html_e( 'step', 'formidable' ) ?></span>
	</td>
</tr>
