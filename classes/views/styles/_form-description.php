<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<?php /*
<p class="frm6 frm_first frm_form_field">
	<label><?php esc_html_e( 'Size', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'form_desc_size' ) ); ?>" id="frm_form_desc_size" value="<?php echo esc_attr( $style->post_content['form_desc_size'] ); ?>" />
</p>

<p class="frm6 frm_end frm_form_field">
	<label><?php esc_html_e( 'Color', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'form_desc_color' ) ); ?>" id="frm_form_desc_color" class="hex" value="<?php echo esc_attr( $style->post_content['form_desc_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'form_desc_color' ); ?> />
</p>
<p class="frm6 frm_first frm_form_field">
	<label><?php esc_html_e( 'Margin Top', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'form_desc_margin_top' ) ); ?>" id="frm_form_desc_margin_top" value="<?php echo esc_attr( $style->post_content['form_desc_margin_top'] ); ?>" size="4" />
</p>
<p class="frm6 frm_form_field">
	<label><?php esc_html_e( 'Margin Bottom', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'form_desc_margin_bottom' ) ); ?>" id="frm_form_desc_margin_bottom" value="<?php echo esc_attr( $style->post_content['form_desc_margin_bottom'] ); ?>" size="4" />
</p>
<p class="frm6 frm_first frm_form_field">
	<label><?php esc_html_e( 'Padding', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'form_desc_padding' ) ); ?>" id="frm_form_desc_padding" value="<?php echo esc_attr( $style->post_content['form_desc_padding'] ); ?>" />
</p>
*/?>

<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php _e( 'Color', 'formidable' ); ?></label></div>
<div class="frm7 frm_form_field">
	<?php new FrmColorPickerStyleComponent(
		$frm_style->get_field_name( 'form_desc_color' ),
		$style->post_content['form_desc_color'],
		array(
			'id'          => 'frm_form_desc_color',
			'action_slug' => 'form_desc_color',
		)
	); 
	?>
</div>

<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php _e( 'Margin', 'formidable' ); ?></label></div>
<div class="frm7 frm_form_field">
	<?php new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'form_desc_margin_top' ),
		(int) $style->post_content['form_desc_margin_top'],
		array( 'id' => 'frm_form_desc_margin_top' )
	); ?>
</div>

<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php _e( 'Padding', 'formidable' ); ?></label></div>
<div class="frm7 frm_form_field">
	<?php new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'form_desc_padding' ),
		(int) $style->post_content['form_desc_padding'],
		array( 'id' => 'frm_form_desc_padding' )
	); ?>
</div>