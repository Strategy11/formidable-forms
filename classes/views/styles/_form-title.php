<p class="frm6 frm_first frm_form_field">
	<label><?php esc_html_e( 'Size', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'title_size' ) ); ?>" id="frm_title_size" value="<?php echo esc_attr( $style->post_content['title_size'] ); ?>" />
</p>

<p class="frm6 frm_end frm_form_field">
	<label><?php esc_html_e( 'Color', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'title_color' ) ); ?>" id="frm_title_color" class="hex" value="<?php echo esc_attr( $style->post_content['title_color'] ); ?>" />
</p>
<p class="frm6 frm_first frm_form_field">
	<label><?php esc_html_e( 'Margin Top', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'title_margin_top' ) ); ?>" id="frm_title_margin_top" value="<?php echo esc_attr( $style->post_content['title_margin_top'] ); ?>" size="4" />
</p>
<p class="frm6 frm_form_field">
	<label><?php esc_html_e( 'Margin Bottom', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'title_margin_bottom' ) ); ?>" id="frm_title_margin_bottom" value="<?php echo esc_attr( $style->post_content['title_margin_bottom'] ); ?>" size="4" />
</p>
