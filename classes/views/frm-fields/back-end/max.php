<p class="frm_half frm_form_field">
	<label for="field_options_max_<?php echo esc_attr( $field['id'] ); ?>">
		<?php
		if ( 'textarea' === $field['type'] || 'rte' === $field['type'] ) {
			esc_html_e( 'Rows', 'formidable' );
		} else {
			esc_html_e( 'Max Characters', 'formidable' );
		}
		?>
	</label>
	<br/>
	<input type="text" name="field_options[max_<?php echo esc_attr( $field['id'] ); ?>]" value="<?php echo esc_attr( $field['max'] ); ?>" id="field_options_max_<?php echo esc_attr( $field['id'] ); ?>" size="5" />
</p>
