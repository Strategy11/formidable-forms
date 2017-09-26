<tr>
	<td colspan="2"><?php _e( 'Content', 'formidable-pro' ) ?><br/>
		<textarea name="field_options[description_<?php echo absint( $field['id'] ) ?>]" class="frm_98_width" rows="8"><?php
	echo FrmAppHelper::esc_textarea( $field['description'] );
	?></textarea>
	</td>
</tr>
