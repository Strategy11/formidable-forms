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
		<option value="100" <?php selected($style->post_content['check_weight'], '100') ?>><?php _e( '100', 'formidable' ) ?></option>
		<option value="200" <?php selected($style->post_content['check_weight'], '200') ?>><?php _e( '200', 'formidable' ) ?></option>
		<option value="300" <?php selected($style->post_content['check_weight'], '300') ?>><?php _e( '300', 'formidable' ) ?></option>
		<option value="normal" <?php selected($style->post_content['check_weight'], 'normal') ?>><?php _e( 'normal', 'formidable' ) ?></option>
		<option value="500" <?php selected($style->post_content['check_weight'], '500') ?>><?php _e( '500', 'formidable' ) ?></option>
		<option value="600" <?php selected($style->post_content['check_weight'], '600') ?>><?php _e( '600', 'formidable' ) ?></option>
		<option value="bold" <?php selected($style->post_content['check_weight'], 'bold') ?>><?php _e( 'bold', 'formidable' ) ?></option>
		<option value="800" <?php selected($style->post_content['check_weight'], '800') ?>><?php _e( '800', 'formidable' ) ?></option>
		<option value="900" <?php selected($style->post_content['check_weight'], '900') ?>><?php _e( '900', 'formidable' ) ?></option>
	</select>
</div>
<div class="field-group clearfix">
	<label><?php _e( 'Size', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('check_font_size') ) ?>" id="frm_check_font_size" value="<?php echo esc_attr( $style->post_content['check_font_size'] ) ?>"  size="3" />
</div>
