<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm6 frm_first frm_form_field">
	<label class="frm_help" title="<?php esc_attr_e( 'This will add !important to many of the lines in the Formidable styling to make sure it will be used.', 'formidable' ); ?>">
		<input type="checkbox" name="<?php echo esc_attr( $frm_style->get_field_name( 'important_style' ) ); ?>" id="frm_important_style" value="1" <?php checked( $style->post_content['important_style'], 1 ); ?> />
		<?php esc_html_e( 'Override theme styling', 'formidable' ); ?>
	</label>
</p>

<p class="frm6 frm_form_field">
	<label class="frm_help" title="<?php esc_attr_e( 'This will center your form on the page where it is published if the form width is less than the available width on the page.', 'formidable' ); ?>">
		<input type="checkbox" name="<?php echo esc_attr( $frm_style->get_field_name( 'center_form' ) ); ?>" id="frm_center_form" value="1" <?php checked( $style->post_content['center_form'], 1 ); ?> />
		<?php esc_html_e( 'Center form on page', 'formidable' ); ?>
	</label>
</p>

<p class="frm4 frm_first frm_form_field">
	<label><?php esc_html_e( 'Alignment', 'formidable' ); ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name( 'form_align' ) ); ?>" id="frm_form_align">
		<option value="left" <?php selected( $style->post_content['form_align'], 'left' ); ?>>
			<?php esc_html_e( 'left', 'formidable' ); ?>
		</option>
		<option value="right" <?php selected( $style->post_content['form_align'], 'right' ); ?>>
			<?php esc_html_e( 'right', 'formidable' ); ?>
		</option>
		<option value="center" <?php selected( $style->post_content['form_align'], 'center' ); ?>>
			<?php esc_html_e( 'center', 'formidable' ); ?>
		</option>
	</select>
</p>

<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Max Width', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'form_width' ) ); ?>" value="<?php echo esc_attr( $style->post_content['form_width'] ); ?>"/>
</p>

<p class="frm4 frm_form_field frm_end">
	<label><?php esc_html_e( 'Background', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'fieldset_bg_color' ) ); ?>" id="frm_fieldset_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['fieldset_bg_color'] ); ?>" size="4" <?php do_action( 'frm_style_settings_input_atts', 'fieldset_bg_color' ); ?> />
</p>

<?php
do_action( 'frm_style_settings_general_section_after_background', compact( 'frm_style', 'style' ) );
if ( ! FrmAppHelper::pro_is_installed() ) {
	?>
		<div class="frm_image_preview_wrapper" data-upgrade="<?php esc_attr_e( 'Background image styles', 'formidable' ); ?>" data-medium="background-image">
			<button type="button" class="frm_choose_image_box frm_button frm_no_style_button frm_noallow">
				<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_upload_icon' ); ?>
				<?php esc_attr_e( 'Upload background image', 'formidable' ); ?>
			</button>
		</div>
	<?php
}
?>

<p class="frm4 frm_first frm_form_field">
	<label><?php esc_html_e( 'Border', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'fieldset' ) ); ?>" id="frm_fieldset" value="<?php echo esc_attr( $style->post_content['fieldset'] ); ?>" size="4" />
</p>

<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Color', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'fieldset_color' ) ); ?>" id="frm_fieldset_color" class="hex" value="<?php echo esc_attr( $style->post_content['fieldset_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'fieldset_color' ); ?> />
</p>

<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Padding', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'fieldset_padding' ) ); ?>" id="frm_fieldset_padding" value="<?php echo esc_attr( $style->post_content['fieldset_padding'] ); ?>" size="4" />
</p>

<p>
	<label><?php esc_html_e( 'Font Family', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'font' ) ); ?>" id="frm_font" value="<?php echo esc_attr( $style->post_content['font'] ); ?>"  placeholder="<?php esc_attr_e( 'Leave blank to inherit from theme', 'formidable' ); ?>" class="frm_full_width" />
</p>

<p class="frm6 frm_first frm_form_field">
	<label><?php esc_html_e( 'Direction', 'formidable' ); ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name( 'direction' ) ); ?>" id="frm_direction">
		<option value="ltr" <?php selected( $style->post_content['direction'], 'ltr' ); ?>>
			<?php esc_html_e( 'Left to Right', 'formidable' ); ?>
		</option>
		<option value="rtl" <?php selected( $style->post_content['direction'], 'rtl' ); ?>>
			<?php esc_html_e( 'Right to Left', 'formidable' ); ?>
		</option>
	</select>
</p>
