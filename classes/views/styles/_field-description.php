<div class="field-group field-group-background clearfix frm-first-row">
	<label><?php _e( 'Color', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('description_color') ) ?>" id="frm_description_color" class="hex" value="<?php echo esc_attr( $style->post_content['description_color'] ) ?>" />
</div>
<div class="field-group clearfix frm-first-row">
	<label><?php _e( 'Weight', 'formidable' ) ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name('description_weight') ) ?>" id="frm_description_weight">
		<?php foreach ( FrmStyle::get_bold_options() as $value => $name ) { ?>
		<option value="<?php echo esc_attr( $value ) ?>" <?php selected( $style->post_content['description_weight'], $value ) ?>><?php echo $name ?></option>
		<?php } ?>
	</select>
</div>
<div class="field-group clearfix frm-first-row">
	<label><?php _e( 'Style', 'formidable' ) ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name('description_style') ) ?>" id="frm_description_style">
		<option value="normal" <?php selected($style->post_content['description_style'], 'normal') ?>><?php _e( 'normal', 'formidable' ) ?></option>
		<option value="italic" <?php selected($style->post_content['description_style'], 'italic') ?>><?php _e( 'italic', 'formidable' ) ?></option>
	</select>
</div>
<div class="field-group clearfix">
	<label><?php _e( 'Size', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('description_font_size') ) ?>" id="frm_description_font_size" value="<?php echo esc_attr( $style->post_content['description_font_size'] ) ?>"  size="3" />
</div>

<div class="field-group clearfix">
	<label><?php _e( 'Align', 'formidable' ) ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name('description_align') ) ?>" id="frm_description_align">
		<option value="left" <?php selected($style->post_content['description_align'], 'left') ?>><?php _e( 'left', 'formidable' ) ?></option>
		<option value="right" <?php selected($style->post_content['description_align'], 'right') ?>><?php _e( 'right', 'formidable' ) ?></option>
	</select>
</div>
<div class="clear"></div>
