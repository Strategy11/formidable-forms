<tr>
	<td colspan="2"><?php esc_html_e( 'Content', 'formidable' ) ?><br/>
		<textarea name="field_options[description_<?php echo absint( $field['id'] ) ?>]" class="frm_98_width" rows="8"><?php
	echo FrmAppHelper::esc_textarea( $field['description'] ); // WPCS: XSS ok.
	?></textarea>
	</td>
</tr>
