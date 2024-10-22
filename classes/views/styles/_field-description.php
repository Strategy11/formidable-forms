<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm5 frm_form_field">
	<label 
		for="frm_description_color"
		class="frm-style-item-heading"><?php esc_html_e( 'Color', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmColorpickerStyleComponent(
		$frm_style->get_field_name( 'description_color' ),
		$style->post_content['description_color'],
		array(
			'id'          => 'frm_description_color',
			'action_slug' => 'description_color',
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_description_font_size"
		class="frm-style-item-heading"><?php esc_html_e( 'Font Size', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'description_font_size' ),
		$style->post_content['description_font_size'],
		array(
			'id'        => 'frm_description_font_size',
			'max_value' => 300,
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_description_weight"
		class="frm-style-item-heading"><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmDropdownStyleComponent(
		$frm_style->get_field_name( 'description_weight' ),
		$style->post_content['description_weight'],
		array(
			'id'      => 'frm_description_weight',
			'options' => FrmStyle::get_bold_options(),
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_description_style"
		class="frm-style-item-heading"><?php esc_html_e( 'Style', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmDropdownStyleComponent(
		$frm_style->get_field_name( 'description_style' ),
		$style->post_content['description_style'],
		array(
			'id'      => 'frm_description_style',
			'options' => array(
				'normal' => esc_html__( 'normal', 'formidable' ),
				'italic' => esc_html__( 'italic', 'formidable' ),
			),
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_description_align"
		class="frm-style-item-heading"><?php esc_html_e( 'Align', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field frm-sm-z-index">
	<?php
	new FrmAlignStyleComponent(
		$frm_style->get_field_name( 'description_align' ),
		$style->post_content['description_align'],
		array(
			'id'      => 'frm_description_align',
			'options' => array( 'left', 'right' ),
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_description_margin"
		class="frm-style-item-heading"><?php esc_html_e( 'Margin', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field frm-md-z-index">
	<?php
	new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'description_margin' ),
		$style->post_content['description_margin'],
		array(
			'id'        => 'frm_description_margin',
			'type'      => 'vertical-margin',
			'max_value' => 100,
		)
	);
	?>
</div>