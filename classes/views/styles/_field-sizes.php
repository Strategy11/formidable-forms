<div class="field-group clearfix frm-first-row">
	<label><?php _e( 'Size', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('field_font_size') ) ?>" id="frm_field_font_size" value="<?php echo esc_attr( $style->post_content['field_font_size'] ) ?>" />
</div>

<div class="field-group clearfix frm-first-row">
	<label><?php _e( 'Height', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('field_height') ) ?>" id="frm_field_height" value="<?php echo esc_attr( $style->post_content['field_height'] ) ?>" />
</div>

<div class="field-group clearfix frm-first-row">
	<label><?php _e( 'Width', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('field_width') ) ?>" id="frm_field_width" value="<?php echo esc_attr( $style->post_content['field_width'] ) ?>" />
</div>

<div class="clear"></div>
<p class="frm_no_bottom_margin">
    <label><input type="checkbox" name="<?php echo esc_attr( $frm_style->get_field_name('auto_width') ) ?>" id="frm_auto_width" value="1" <?php checked( $style->post_content['auto_width'], 1 ) ?> />
	<?php _e( 'Automatic Width for drop-down fields', 'formidable' ) ?></label>
</p>

<div class="field-group clearfix">
	<label><?php _e( 'Padding', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('field_pad') ) ?>" id="frm_field_pad" value="<?php echo esc_attr( $style->post_content['field_pad'] ) ?>" />
</div>

<div class="field-group clearfix">
	<label><?php _e( 'Margin', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('field_margin') ) ?>" id="frm_field_margin" value="<?php echo esc_attr( $style->post_content['field_margin'] ) ?>" />
</div>

<div class="field-group clearfix">
	<label><?php _e( 'Corners', 'formidable' ) ?> <span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Formidable uses CSS3 border-radius for corner rounding, which is not currently supported by Internet Explorer.', 'formidable' ) ?>" ></span></label>
	<input type="text" value="<?php echo esc_attr( $style->post_content['border_radius'] ) ?>" name="<?php echo esc_attr( $frm_style->get_field_name('border_radius') ) ?>" id="frm_border_radius" />
</div>

<div class="field-group clearfix frm-first-row">
	<label><?php _e( 'Weight', 'formidable' ) ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name('field_weight') ) ?>" id="frm_field_weight">
		<?php foreach ( FrmStyle::get_bold_options() as $value => $name ) { ?>
			<option value="<?php echo esc_attr( $value ) ?>" <?php selected( $style->post_content['field_weight'], $value ) ?>><?php echo esc_attr( $name ) ?></option>
		<?php } ?>
	</select>
</div>
