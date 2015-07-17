<div class="field-group clearfix frm-half frm-first-row">
	<label><?php _e( 'Radio', 'formidable' ) ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name('radio_align') ) ?>" id="frm_radio_align">
        <option value="block" <?php selected($style->post_content['radio_align'], 'block') ?>><?php _e( 'Multiple Rows' , 'formidable' ) ?></option>
        <option value="inline" <?php selected($style->post_content['radio_align'], 'inline') ?>><?php _e( 'Single Row' , 'formidable' ) ?></option>
	</select>
</div>

<div class="field-group clearfix frm-half frm-first-row">
	<label><?php _e( 'Check Box', 'formidable' ) ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name('check_align') ) ?>" id="frm_check_align">
        <option value="block" <?php selected($style->post_content['check_align'], 'block') ?>><?php _e( 'Multiple Rows' , 'formidable' ) ?></option>
        <option value="inline" <?php selected($style->post_content['check_align'], 'inline') ?>><?php _e( 'Single Row' , 'formidable' ) ?></option>
	</select>
</div>

<div class="field-group field-group-background clearfix">
	<label><?php _e( 'Color', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('check_label_color') ) ?>" id="frm_check_label_color" class="hex" value="<?php echo esc_attr( $style->post_content['check_label_color'] ) ?>" />
</div>
<div class="field-group clearfix">
	<label><?php _e( 'Weight', 'formidable' ) ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name('check_weight') ) ?>" id="frm_check_weight">
		<?php foreach ( FrmStyle::get_bold_options() as $value => $name ) { ?>
		<option value="<?php echo esc_attr( $value ) ?>" <?php selected( $style->post_content['check_weight'], $value ) ?>><?php echo $name ?></option>
		<?php } ?>
	</select>
</div>
<div class="field-group clearfix">
	<label><?php _e( 'Size', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('check_font_size') ) ?>" id="frm_check_font_size" value="<?php echo esc_attr( $style->post_content['check_font_size'] ) ?>"  size="3" />
</div>
