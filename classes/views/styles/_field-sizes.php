<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm5 frm_form_field">
	<label 
		for="frm_field_font_size"
		class="frm-style-item-heading"><?php esc_html_e( 'Font Size', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'field_font_size' ),
		$style->post_content['field_font_size'],
		array(
			'id'        => 'frm_field_font_size',
			'max_value' => 100,
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_field_weight"
		class="frm-style-item-heading"><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmDropdownStyleComponent(
		$frm_style->get_field_name( 'field_weight' ),
		$style->post_content['field_weight'],
		array(
			'id'      => 'frm_field_weight',
			'options' => FrmStyle::get_bold_options(),
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_field_height"
		class="frm-style-item-heading"><?php esc_html_e( 'Height', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'field_height' ),
		$style->post_content['field_height'],
		array(
			'id'        => 'frm_field_height',
			'max_value' => 100,
		)
	);
	?>
</div>
<div class="frm5 frm_form_field">
	<label 
		for="frm_field_width"
		class="frm-style-item-heading"><?php esc_html_e( 'Width', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'field_width' ),
		$style->post_content['field_width'],
		array(
			'id'        => 'frm_field_width',
			'max_value' => 1400,
		)
	);
	?>
</div>
<div class="frm5 frm_form_field">
	<label 
		for="frm_field_pad"
		class="frm-style-item-heading"><?php esc_html_e( 'Padding', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'field_pad' ),
		$style->post_content['field_pad'],
		array(
			'id'        => 'frm_field_pad',
			'type'      => 'vertical-margin',
			'max_value' => 100,
		)
	);
	?>
</div>
<div class="frm5 frm_form_field">
	<label 
		for="frm_field_margin"
		class="frm-style-item-heading"><?php esc_html_e( 'Margin', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'field_margin' ),
		$style->post_content['field_margin'],
		array(
			'id'        => 'frm_field_margin',
			'type'      => 'vertical-margin',
			'max_value' => 100,
		)
	);
	?>
</div>
<div class="frm5 frm_form_field">
	<label 
		for="frm_border_radius"
		class="frm-style-item-heading"><?php esc_html_e( 'Corner Radius', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'border_radius' ),
		$style->post_content['border_radius'],
		array(
			'id'        => 'frm_border_radius',
			'max_value' => 50,
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_auto_dropdowns_width"
		class="frm-style-item-heading"><?php esc_html_e( 'Auto Drop-downs Width', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field frm-style-component">
	<?php
	FrmHtmlHelper::toggle(
		'frm_auto_dropdowns_width',
		$frm_style->get_field_name( 'auto_width' ),
		array(
			'div_class' => 'with_frm_style frm_toggle',
			'checked'   => $style->post_content['auto_width'],
			'echo'      => true,
		)
	);
	?>
</div>
