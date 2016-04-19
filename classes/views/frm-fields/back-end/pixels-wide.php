<tr>
	<td class="frm_150_width">
		<label><?php _e( 'Field Size', 'formidable' ) ?></label>
	</td>
	<td>
		<input type="text" name="field_options[size_<?php echo esc_attr( $field['id'] ) ?>]" value="<?php echo esc_attr( $field['size'] ); ?>" size="5" />
		<span class="howto"><?php _e( 'pixels wide', 'formidable' ) ?></span>
		<?php
		if ( $display_max ) {
			include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/max.php' );
		}
		?>
	</td>
</tr>
