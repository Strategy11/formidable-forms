<p>
	<label for="frm_description_<?php echo esc_attr( $field['id'] ); ?>">
		<?php esc_html_e( 'Field Description', 'formidable' ); ?>
	</label>
	<textarea name="field_options[description_<?php echo esc_attr( $field['id'] ); ?>]" class="frm_long_input" data-changeme="field_description_<?php echo esc_attr( $field['id'] ); ?>"><?php
		echo FrmAppHelper::esc_textarea( $field['description'] ); // WPCS: XSS ok.
	?></textarea>
</p>
