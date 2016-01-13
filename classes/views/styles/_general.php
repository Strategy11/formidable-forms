<p>
    <label><input type="checkbox" name="<?php echo esc_attr( $frm_style->get_field_name('center_form') ) ?>" id="frm_center_form" value="1" <?php checked($style->post_content['center_form'], 1) ?> />
	    <?php _e( 'Center form on page', 'formidable' ) ?> <span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'This will center your form on the page where it is published if the form width is less than the available width on the page.', 'formidable' ) ?>" ></span>
	</label>
</p>

<div class="field-group clearfix frm-first-row">
	<label><?php _e( 'Alignment', 'formidable' ) ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name('form_align') ) ?>" id="frm_form_align">
		<option value="left" <?php selected($style->post_content['form_align'], 'left') ?>><?php _e( 'left', 'formidable' ) ?></option>
		<option value="right" <?php selected($style->post_content['form_align'], 'right') ?>><?php _e( 'right', 'formidable' ) ?></option>
		<option value="center" <?php selected($style->post_content['form_align'], 'center') ?>><?php _e( 'center', 'formidable' ) ?></option>
	</select>
</div>

<div class="field-group clearfix frm-first-row">
	<label><?php _e( 'Max Width', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('form_width') ) ?>" value="<?php echo esc_attr( $style->post_content['form_width'] ) ?>"/>
</div>

<div class="field-group clearfix frm-first-row">
	<label><?php _e( 'Background', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('fieldset_bg_color') ) ?>" id="frm_fieldset_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['fieldset_bg_color'] ) ?>" size="4" />
</div>

<div class="field-group field-group-border clearfix">
	<label><?php _e( 'Border', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('fieldset') ) ?>" id="frm_fieldset" value="<?php echo esc_attr( $style->post_content['fieldset'] ) ?>" size="4" />
</div>

<div class="field-group clearfix">
	<label><?php _e( 'Color', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('fieldset_color') ) ?>" id="frm_fieldset_color" class="hex" value="<?php echo esc_attr( $style->post_content['fieldset_color'] ) ?>" />
</div>

<div class="field-group clearfix">
	<label><?php _e( 'Padding', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('fieldset_padding') ) ?>" id="frm_fieldset_padding" value="<?php echo esc_attr( $style->post_content['fieldset_padding'] ) ?>" size="4" />
</div>

<div id="frm_gen_font_box" class="field-group clearfix">
	<label><?php _e( 'Font Family', 'formidable' ) ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name('font') ) ?>" id="frm_font" value="<?php echo esc_attr( $style->post_content['font'] ) ?>"  class="frm_full_width" />
</div>

<div class="field-group clearfix frm-half">
	<label><?php _e( 'Direction', 'formidable' ) ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name('direction') ) ?>" id="frm_direction">
		<option value="ltr" <?php selected($style->post_content['direction'], 'ltr') ?>><?php _e( 'Left to Right', 'formidable' ) ?></option>
		<option value="rtl" <?php selected($style->post_content['direction'], 'rtl') ?>><?php _e( 'Right to Left', 'formidable' ) ?></option>
	</select>
</div>

<div class="clear"></div>
<p class="frm_no_bottom_margin">
    <label><input type="checkbox" name="<?php echo esc_attr( $frm_style->get_field_name('important_style') ) ?>" id="frm_important_style" value="1" <?php checked($style->post_content['important_style'], 1) ?> />
	    <?php _e( 'Override theme styling', 'formidable' ) ?> <span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'This will add !important to many of the lines in the Formidable styling to make sure it will be used.', 'formidable' ) ?>" ></span>
	</label>
</p>
