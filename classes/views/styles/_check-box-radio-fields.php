<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm5 frm_form_field">
	<label 
		for="frm_check_label_color"
		class="frm-style-item-heading"><?php esc_html_e( 'Color', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmColorpickerStyleComponent(
		$frm_style->get_field_name( 'check_label_color' ),
		$style->post_content['check_label_color'],
		array(
			'id'          => 'frm_check_label_color',
			'action_slug' => 'check_label_color',
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_check_font_size"
		class="frm-style-item-heading"><?php esc_html_e( 'Font Size', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'check_font_size' ),
		$style->post_content['check_font_size'],
		array(
			'id'        => 'frm_check_font_size',
			'max_value' => 100,
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_check_weight"
		class="frm-style-item-heading"><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmDropdownStyleComponent(
		$frm_style->get_field_name( 'check_weight' ),
		$style->post_content['check_weight'],
		array(
			'id'      => 'frm_check_weight',
			'options' => FrmStyle::get_bold_options(),
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_check_align"
		class="frm-style-item-heading"><?php esc_html_e( 'Check Box', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmDropdownStyleComponent(
		$frm_style->get_field_name( 'check_align' ),
		$style->post_content['check_align'],
		array(
			'id'      => 'frm_check_align',
			'options' => array(
				'block'  => esc_html__( 'Multiple Rows', 'formidable' ),
				'inline' => esc_html__( 'Single Row', 'formidable' ),
			),
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_radio_align"
		class="frm-style-item-heading"><?php esc_html_e( 'Radio', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmDropdownStyleComponent(
		$frm_style->get_field_name( 'radio_align' ),
		$style->post_content['radio_align'],
		array(
			'id'      => 'frm_radio_align',
			'options' => array(
				'block'  => esc_html__( 'Multiple Rows', 'formidable' ),
				'inline' => esc_html__( 'Single Row', 'formidable' ),
			),
		)
	);
	?>
</div>