<span class="frm-style-component frm-colorpicker">
	<input type="text" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $component['id'] )?>" class="hex" value="<?php echo esc_attr( $field_value ); ?>" <?php do_action( 'frm_style_settings_input_atts', $component['action_slug'] ); ?> />
</span>