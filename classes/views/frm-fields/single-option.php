<li id="frm_delete_field_<?php echo esc_attr( $field['id'] . '-' . $opt_key ); ?>_container" class="frm_single_option">
	<input type="text" name="field_options[options_<?php echo esc_attr( $field['id'] ); ?>][<?php echo esc_attr( $opt_key ); ?>][label]" value="<?php echo esc_attr( $opt ); ?>" class="field_<?php echo esc_attr( $field['id'] ); ?>_option <?php echo esc_attr( $field['separate_value'] ? 'frm_with_key' : '' ); ?>" id="<?php echo esc_attr( $html_id . '-' . $opt_key ); ?>" />

	<span class="frm_option_key field_<?php echo esc_attr( $field['id'] ); ?>_option_key<?php echo esc_attr( $field['separate_value'] ? '' : ' frm_hidden' ); ?>">
		<input type="text" name="field_options[options_<?php echo esc_attr( $field['id'] ); ?>][<?php echo esc_attr( $opt_key ); ?>][value]" id="field_key_<?php echo esc_attr( $field['id'] . '-' . $opt_key ); ?>" value="<?php echo esc_attr( $field_val ); ?>" />
	</span>

	<a href="javascript:void(0)" class="frm_icon_font frm_remove_tag" data-fid="<?php echo esc_attr( $field['id'] ); ?>"> </a>
	<a href="javascript:void(0);" data-opttype="single" class="frm_cb_button frm_add_opt frm_icon_font frm_add_tag" data-clicks="0"></a>
</li>
