<div class="field-group field-group-background clearfix frm-first-row">
	<label><?php _e( 'Color', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('description_color') ) ?>" id="frm_description_color" class="hex" value="<?php echo esc_attr( $style->post_content['description_color'] ) ?>" />
</div>
<div class="field-group clearfix frm-first-row">
	<label><?php _e( 'Weight', 'formidable' ) ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name('description_weight') ) ?>" id="frm_description_weight">
		<option value="100" <?php selected($style->post_content['description_weight'], '100') ?>><?php _e( '100', 'formidable' ) ?></option>
		<option value="200" <?php selected($style->post_content['description_weight'], '200') ?>><?php _e( '200', 'formidable' ) ?></option>
		<option value="300" <?php selected($style->post_content['description_weight'], '300') ?>><?php _e( '300', 'formidable' ) ?></option>
		<option value="normal" <?php selected($style->post_content['description_weight'], 'normal') ?>><?php _e( 'normal', 'formidable' ) ?></option>
		<option value="500" <?php selected($style->post_content['description_weight'], '500') ?>><?php _e( '500', 'formidable' ) ?></option>
		<option value="600" <?php selected($style->post_content['description_weight'], '600') ?>><?php _e( '600', 'formidable' ) ?></option>
		<option value="bold" <?php selected($style->post_content['description_weight'], 'bold') ?>><?php _e( 'bold', 'formidable' ) ?></option>
		<option value="800" <?php selected($style->post_content['description_weight'], '800') ?>><?php _e( '800', 'formidable' ) ?></option>
		<option value="900" <?php selected($style->post_content['description_weight'], '900') ?>><?php _e( '900', 'formidable' ) ?></option>
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
