<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<div class="frm5 frm_form_field">
	<label 
		for="frm_form_desc_size"
		class="frm-style-item-heading"><?php esc_html_e( 'Font Size', 'formidable' ); ?>
	</label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'form_desc_size' ),
		$style->post_content['form_desc_size'],
		array(
			'id'        => 'frm_form_desc_size',
			'max_value' => 100,
		)
	);
	?>
</div>

<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Color', 'formidable' ); ?></label></div>
<div class="frm7 frm_form_field">
	<?php
	new FrmColorpickerStyleComponent(
		$frm_style->get_field_name( 'form_desc_color' ),
		$style->post_content['form_desc_color'],
		array(
			'id'          => 'frm_form_desc_color',
			'action_slug' => 'form_desc_color',
		)
	);
	?>
</div>

<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Margin', 'formidable' ); ?></label></div>
<div class="frm7 frm_form_field">
	<?php
	new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'form_desc_margin_top' ),
		$style->post_content['form_desc_margin_top'],
		array(
			'id'                 => 'frm_form_desc_margin_top',
			'type'               => 'vertical-margin',
			'max_value'          => 100,
			'independent_fields' => array(
				array(
					'name'  => $frm_style->get_field_name( 'form_desc_margin_top' ),
					'value' => $style->post_content['form_desc_margin_top'],
					'id'    => 'frm_form_desc_margin_top',
					'type'  => 'top',
				),
				array(
					'name'  => $frm_style->get_field_name( 'form_desc_margin_bottom' ),
					'value' => $style->post_content['form_desc_margin_bottom'],
					'id'    => 'frm_form_desc_margin_bottom',
					'type'  => 'bottom',
				),
			),
		)
	);
	?>
</div>

<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Padding', 'formidable' ); ?></label></div>
<div class="frm7 frm_form_field">
	<?php
	new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'form_desc_padding' ),
		$style->post_content['form_desc_padding'],
		array(
			'id'        => 'frm_form_desc_padding',
			'type'      => 'vertical-margin',
			'max_value' => 100,
		)
	);
	?>
</div>