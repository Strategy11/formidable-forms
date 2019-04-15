<p>
	<label for="frm_description_<?php echo esc_attr( $field['id'] ); ?>">
		<?php esc_html_e( 'Field Description', 'formidable' ); ?>
	</label>
	<textarea name="field_options[description_<?php echo esc_attr( $field['id'] ); ?>]" id="frm_description_<?php echo esc_attr( $field['id'] ); ?>" class="frm_long_input"><?php
		echo FrmAppHelper::esc_textarea( $field['description'] ); // WPCS: XSS ok.
	?></textarea>
</p>
