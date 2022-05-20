<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
		<h4><span><?php esc_html_e( 'Success Messages', 'formidable' ); ?></span></h4>
		<p class="frm4 frm_first frm_form_field">
			<label><?php esc_html_e( 'BG color', 'formidable' ); ?></label>
			<input name="<?php echo esc_attr( $frm_style->get_field_name( 'success_bg_color' ) ); ?>" id="frm_success_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['success_bg_color'] ); ?>" type="text" <?php do_action( 'frm_style_settings_input_atts', 'success_bg_color' ); ?> />
		</p>
		<p class="frm4 frm_form_field">
			<label><?php esc_html_e( 'Border', 'formidable' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'success_border_color' ) ); ?>" id="frm_success_border_color" class="hex" value="<?php echo esc_attr( $style->post_content['success_border_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'success_border_color' ); ?> />
		</p>
		<p class="frm4 frm_end frm_form_field">
			<label><?php esc_html_e( 'Text', 'formidable' ); ?></label>
			<input name="<?php echo esc_attr( $frm_style->get_field_name( 'success_text_color' ) ); ?>" id="frm_success_text_color" class="hex" value="<?php echo esc_attr( $style->post_content['success_text_color'] ); ?>" type="text" <?php do_action( 'frm_style_settings_input_atts', 'success_text_color' ); ?> />
		</p>
		<p class="frm4 frm_first frm_form_field">
			<label><?php esc_html_e( 'Size', 'formidable' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'success_font_size' ) ); ?>" id="frm_success_font_size" value="<?php echo esc_attr( $style->post_content['success_font_size'] ); ?>"  size="3" />
		</p>

		<h4 class="frm_clear">
			<span><?php esc_html_e( 'Error Messages', 'formidable' ); ?></span>
		</h4>
		<p class="frm4 frm_first frm_form_field">
			<label><?php esc_html_e( 'BG color', 'formidable' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'error_bg' ) ); ?>" id="frm_error_bg" class="hex" value="<?php echo esc_attr( $style->post_content['error_bg'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'error_bg' ); ?> />
		</p>
		<p class="frm4 frm_form_field">
			<label><?php esc_html_e( 'Border', 'formidable' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'error_border' ) ); ?>" id="frm_error_border" class="hex" value="<?php echo esc_attr( $style->post_content['error_border'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'error_border' ); ?> />
		</p>
		<p class="frm4 frm_end frm_form_field">
			<label><?php esc_html_e( 'Text', 'formidable' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'error_text' ) ); ?>" id="frm_error_text" class="hex" value="<?php echo esc_attr( $style->post_content['error_text'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'error_text' ); ?> />
		</p>

		<p class="frm4 frm_first frm_form_field">
			<label><?php esc_html_e( 'Size', 'formidable' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'error_font_size' ) ); ?>" id="frm_error_font_size" value="<?php echo esc_attr( $style->post_content['error_font_size'] ); ?>"  size="3" />
		</p>
