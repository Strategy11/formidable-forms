<li id="frm_delete_field_<?php echo esc_attr( $field['id'] ); ?>-<?php echo esc_attr( $opt_key ) ?>_container" class="frm_single_option">
    <a href="javascript:void(0)" class="frm_single_visible_hover frm_icon_font frm_delete_icon" data-fid="<?php echo esc_attr( $field['id'] ); ?>"> </a>
    <?php if ( $field['type'] != 'select' ) { ?>
        <input type="<?php echo esc_attr( $field['type'] ) ?>" name="<?php echo esc_attr( $field_name ) ?><?php echo ( $field['type'] == 'checkbox' ) ? '[]' : ''; ?>" value="<?php echo esc_attr($field_val) ?>"<?php echo isset( $checked ) ? $checked : ''; ?>/>
    <?php } ?>
	<label class="frm_ipe_field_option field_<?php echo esc_attr( $field['id'] ) ?>_option <?php echo $field['separate_value'] ? 'frm_with_key' : ''; ?>" id="<?php echo esc_attr( $html_id . '-' . $opt_key ) ?>"><?php echo ($opt == '') ? __( '(Blank)', 'formidable' ) : $opt ?></label>
    <span class="frm_option_key field_<?php echo esc_attr( $field['id'] ) ?>_option_key<?php echo $field['separate_value'] ? '' : ' frm_hidden'; ?>">
		<label class="frm-show-click frm_ipe_field_option_key" id="field_key_<?php echo esc_attr( $field['id'] . '-' . $opt_key ) ?>"><?php echo ( $field_val == '' ) ? esc_html__( '(Blank)', 'formidable' ) : $field_val ?></label>
    </span>
</li>
<?php
unset($field_val, $opt, $opt_key);
