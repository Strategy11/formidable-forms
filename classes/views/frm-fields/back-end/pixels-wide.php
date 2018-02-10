<tr>
	<td class="frm_150_width">
		<label for="field_options_size_<?php echo esc_attr( $field['id'] ) ?>">
			<?php esc_html_e( 'Field Size', 'formidable' ) ?>
		</label>
	</td>
	<td>
		<input type="text" name="field_options[size_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['size'] ); ?>" size="5" id="field_options_size_<?php echo esc_attr( $field['id'] ) ?>" aria-describedby="howto_size_<?php echo esc_attr( $field['id'] ) ?>" />
		<span class="howto" id="howto_size_<?php echo esc_attr( $field['id'] ) ?>">
			<?php esc_html_e( 'pixels wide', 'formidable' ) ?>
		</span>
		<?php
		if ( $display_max ) {
			include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/max.php' );
		}
		?>
	</td>
</tr>
