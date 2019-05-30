<p class="frm_no_top_margin">
	<label for="frm_submit_style">
		<input type="checkbox" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_style' ) ); ?>" id="frm_submit_style" <?php checked( $style->post_content['submit_style'], 1 ); ?> value="1" />
		<?php esc_html_e( 'Disable submit button styling', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Note: If disabled, you may not see the change take effect until you make 2 more styling changes or click "Update Options".', 'formidable' ); ?>"></span>
	</label>
</p>

<p class="frm4 frm_first frm_form_field">
	<label for="frm_submit_font_size"><?php esc_html_e( 'Size', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_font_size' ) ); ?>" id="frm_submit_font_size" value="<?php echo esc_attr( $style->post_content['submit_font_size'] ); ?>"  size="3" />
</p>

<p class="frm4 frm_form_field">
	<label for="frm_submit_width"><?php esc_html_e( 'Width', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_width' ) ); ?>" id="frm_submit_width" value="<?php echo esc_attr( $style->post_content['submit_width'] ); ?>"  size="5" />
</p>

<p class="frm4 frm_form_field">
	<label for="frm_submit_height"><?php esc_html_e( 'Height', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_height' ) ); ?>" id="frm_submit_height" value="<?php echo esc_attr( $style->post_content['submit_height'] ); ?>"  size="5" />
</p>

<p class="frm4 frm_first frm_form_field">
	<label for="frm_submit_weight"><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_weight' ) ); ?>" id="frm_submit_weight">
		<?php foreach ( FrmStyle::get_bold_options() as $value => $name ) { ?>
			<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $style->post_content['submit_weight'], $value ); ?>><?php echo esc_html( $name ); ?></option>
		<?php } ?>
	</select>
</p>

<p class="frm4 frm_form_field">
	<label for="frm_submit_border_radius"><?php esc_html_e( 'Corners', 'formidable' ); ?></label>
	<input type="text" value="<?php echo esc_attr( $style->post_content['submit_border_radius'] ); ?>" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_border_radius' ) ); ?>" id="frm_submit_border_radius" size="4"/>
</p>

<p class="frm4 frm_first frm_form_field">
	<label for="frm_submit_bg_color"><?php esc_html_e( 'BG color', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_bg_color' ) ); ?>" id="frm_submit_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_bg_color'] ); ?>" />
</p>

<p class="frm4 frm_form_field">
	<label for="frm_submit_text_color"><?php esc_html_e( 'Text', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_text_color' ) ); ?>" id="frm_submit_text_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_text_color'] ); ?>" />
</p>

<p class="frm4 frm_first frm_form_field">
	<label for="frm_submit_border_color"><?php esc_html_e( 'Border', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_border_color' ) ); ?>" id="frm_submit_border_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_border_color'] ); ?>" />
</p>

<p class="frm4 frm_form_field">
	<label for="frm_submit_border_width"><?php esc_html_e( 'Thickness', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_border_width' ) ); ?>" id="frm_submit_border_width" value="<?php echo esc_attr( $style->post_content['submit_border_width'] ); ?>" size="4" />
</p>

<p class="frm4 frm_end frm_form_field">
	<label for="frm_submit_shadow_color"><?php esc_html_e( 'Shadow', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_shadow_color' ) ); ?>" id="frm_submit_shadow_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_shadow_color'] ); ?>" />
</p>

<p class="frm_clear">
	<label for="frm_submit_bg_img"><?php esc_html_e( 'BG Image', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_bg_img' ) ); ?>" id="frm_submit_bg_img" value="<?php echo esc_attr( $style->post_content['submit_bg_img'] ); ?>"  />
</p>

<p class="frm4 frm_first frm_form_field">
	<label for="frm_submit_margin"><?php esc_html_e( 'Margin', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_margin' ) ); ?>" id="frm_submit_margin" value="<?php echo esc_attr( $style->post_content['submit_margin'] ); ?>" size="6" />
</p>

<p class="frm4 frm_form_field">
	<label for="frm_submit_padding"><?php esc_html_e( 'Padding', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_padding' ) ); ?>" id="frm_submit_padding" value="<?php echo esc_attr( $style->post_content['submit_padding'] ); ?>" size="6" />
</p>

<h4 class="frm_clear">
	<span><?php esc_html_e( 'On Hover', 'formidable' ); ?></span>
</h4>
<p class="frm4 frm_first frm_form_field">
	<label for="frm_submit_hover_bg_color"><?php esc_html_e( 'BG color', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_hover_bg_color' ) ); ?>" id="frm_submit_hover_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_hover_bg_color'] ); ?>" />
</p>

<p class="frm4 frm_form_field">
	<label for="frm_submit_hover_color"><?php esc_html_e( 'Text', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_hover_color' ) ); ?>" id="frm_submit_hover_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_hover_color'] ); ?>" />
</p>

<p class="frm4 frm_end frm_form_field">
	<label for="frm_submit_hover_border_color"><?php esc_html_e( 'Border', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_hover_border_color' ) ); ?>" id="frm_submit_hover_border_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_hover_border_color'] ); ?>" />
</p>

<h4 class="frm_clear">
	<span><?php esc_html_e( 'On Click', 'formidable' ); ?></span>
</h4>
<p class="frm4 frm_first frm_form_field">
	<label for="frm_submit_active_bg_color"><?php esc_html_e( 'BG color', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_active_bg_color' ) ); ?>" id="frm_submit_active_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_active_bg_color'] ); ?>" />
</p>

<p class="frm4 frm_form_field">
	<label for="frm_submit_active_color"><?php esc_html_e( 'Text', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_active_color' ) ); ?>" id="frm_submit_active_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_active_color'] ); ?>" />
</p>

<p class="frm4 frm_end frm_form_field">
	<label for="frm_submit_active_border_color"><?php esc_html_e( 'Border', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_active_border_color' ) ); ?>" id="frm_submit_active_border_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_active_border_color'] ); ?>" />
</p>
