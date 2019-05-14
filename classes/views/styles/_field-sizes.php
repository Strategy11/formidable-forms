<p class="frm4 frm_first frm_form_field">
	<label><?php esc_html_e( 'Size', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'field_font_size' ) ); ?>" id="frm_field_font_size" value="<?php echo esc_attr( $style->post_content['field_font_size'] ); ?>" />
</p>

<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Height', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'field_height' ) ); ?>" id="frm_field_height" value="<?php echo esc_attr( $style->post_content['field_height'] ); ?>" />
</p>

<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Width', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'field_width' ) ); ?>" id="frm_field_width" value="<?php echo esc_attr( $style->post_content['field_width'] ); ?>" />
</p>

<p class="frm_clear frm_no_bottom_margin">
	<label><input type="checkbox" name="<?php echo esc_attr( $frm_style->get_field_name( 'auto_width' ) ); ?>" id="frm_auto_width" value="1" <?php checked( $style->post_content['auto_width'], 1 ); ?> />
	<?php esc_html_e( 'Automatic width for drop-down fields', 'formidable' ); ?></label>
</p>

<p class="frm4 frm_first frm_form_field">
	<label><?php esc_html_e( 'Padding', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'field_pad' ) ); ?>" id="frm_field_pad" value="<?php echo esc_attr( $style->post_content['field_pad'] ); ?>" />
</p>

<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Margin', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'field_margin' ) ); ?>" id="frm_field_margin" value="<?php echo esc_attr( $style->post_content['field_margin'] ); ?>" />
</p>

<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Corners', 'formidable' ); ?> <span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Formidable uses CSS3 border-radius for corner rounding, which is not currently supported by Internet Explorer.', 'formidable' ); ?>" ></span></label>
	<input type="text" value="<?php echo esc_attr( $style->post_content['border_radius'] ); ?>" name="<?php echo esc_attr( $frm_style->get_field_name( 'border_radius' ) ); ?>" id="frm_border_radius" />
</p>

<p class="frm4 frm_first frm_form_field">
	<label><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name( 'field_weight' ) ); ?>" id="frm_field_weight">
		<?php foreach ( FrmStyle::get_bold_options() as $value => $name ) { ?>
			<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $style->post_content['field_weight'], $value ); ?>><?php echo esc_attr( $name ); ?></option>
		<?php } ?>
	</select>
</p>
