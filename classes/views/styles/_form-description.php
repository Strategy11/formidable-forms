<div class="field-group clearfix frm-half frm-first-row">
    <label><?php _e( 'Size', 'formidable' ) ?></label>
    <input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('form_desc_size') ) ?>" id="frm_form_desc_size" value="<?php echo esc_attr( $style->post_content['form_desc_size'] ) ?>" />
</div>

<div class="field-group clearfix frm-half frm-first-row">
    <label><?php _e( 'Color', 'formidable' ) ?></label>
    <input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('form_desc_color') ) ?>" id="frm_form_desc_color" class="hex" value="<?php echo esc_attr( $style->post_content['form_desc_color'] ) ?>" />
</div>
<div class="field-group clearfix frm-half">
    <label><?php _e( 'Margin Top', 'formidable' ) ?></label>
    <input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('form_desc_margin_top') ) ?>" id="frm_form_desc_margin_top" value="<?php echo esc_attr( $style->post_content['form_desc_margin_top'] ) ?>" size="4" />
</div>
<div class="field-group clearfix frm-half">
    <label><?php _e( 'Margin Bottom', 'formidable' ) ?></label>
    <input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('form_desc_margin_bottom') ) ?>" id="frm_form_desc_margin_bottom" value="<?php echo esc_attr( $style->post_content['form_desc_margin_bottom'] ) ?>" size="4" />
</div>
