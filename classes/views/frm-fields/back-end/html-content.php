<p>
	<label>
		<?php esc_html_e( 'Content', 'formidable' ); ?>
	</label>
	<textarea name="field_options[description_<?php echo absint( $field['id'] ); ?>]" rows="8"><?php
	echo FrmAppHelper::esc_textarea( $field['description'] ); // WPCS: XSS ok.
	?></textarea>
</p>
