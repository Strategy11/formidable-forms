<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<?php /*
<p class="frm6 frm_first frm_form_field">
	<label><?php esc_html_e( 'Size', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'title_size' ) ); ?>" id="frm_title_size" value="<?php echo esc_attr( $style->post_content['title_size'] ); ?>" />
</p>

<p class="frm6 frm_end frm_form_field">
	<label><?php esc_html_e( 'Color', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'title_color' ) ); ?>" id="frm_title_color" class="hex" value="<?php echo esc_attr( $style->post_content['title_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'title_color' ); ?> />
</p>
<p class="frm6 frm_first frm_form_field">
	<label><?php esc_html_e( 'Margin Top', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'title_margin_top' ) ); ?>" id="frm_title_margin_top" value="<?php echo esc_attr( $style->post_content['title_margin_top'] ); ?>" size="4" />
</p>
<p class="frm6 frm_form_field">
	<label><?php esc_html_e( 'Margin Bottom', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'title_margin_bottom' ) ); ?>" id="frm_title_margin_bottom" value="<?php echo esc_attr( $style->post_content['title_margin_bottom'] ); ?>" size="4" />
</p>
*/?>

<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Color', 'formidable' ); ?></label></div>
<div class="frm7 frm_form_field">
	<?php new FrmColorpickerStyleComponent(
		$frm_style->get_field_name( 'title_color' ),
		$style->post_content['title_color'],
		array(
			'id'          => 'frm_fieldset_color',
			'action_slug' => 'title_color',
		)
	); 
	?>
</div>

<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Font Size', 'formidable' ); ?></label></div>
<div class="frm7 frm_form_field">
	<?php new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'title_size' ),
		$style->post_content['title_size'],
		array(
			'id'        => 'frm_title_size',
			'max_value' => 100,
		)
	); ?>
</div>

<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Margin', 'formidable' ); ?></label></div>
<div class="frm7 frm_form_field">
	<?php new FrmSliderStyleComponent(
		null,
		$style->post_content['title_margin_top'],
		array(
			'id'                 => 'frm_title_margins',
			'type'		         => 'vertical-margin',
			'max_value'          => 100,
			'independent_fields' => array(
				array(
					'name'  => $frm_style->get_field_name( 'title_margin_top' ),
					'value' => $style->post_content['title_margin_top'],
					'id'    => 'frm_title_margin_top',
					'type'  => 'top',
				),
				array(
					'name'  => $frm_style->get_field_name( 'title_margin_bottom' ),
					'value' => $style->post_content['title_margin_bottom'],
					'id'    => 'frm_title_margin_bottom',
					'type'  => 'bottom',
				),
			)
		)
	); ?>
</div>