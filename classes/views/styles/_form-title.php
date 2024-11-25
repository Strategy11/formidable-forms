<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm5 frm_form_field">
	<label 
		for="frm_fieldset_color"
		class="frm-style-item-heading"><?php esc_html_e( 'Color', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmColorpickerStyleComponent(
		$frm_style->get_field_name( 'title_color' ),
		$style->post_content['title_color'],
		array(
			'id'          => 'frm_fieldset_color',
			'action_slug' => 'title_color',
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_title_size"
		class="frm-style-item-heading"><?php esc_html_e( 'Font Size', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'title_size' ),
		$style->post_content['title_size'],
		array(
			'id'        => 'frm_title_size',
			'max_value' => 100,
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_title_margins"
		class="frm-style-item-heading"><?php esc_html_e( 'Margin', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmSliderStyleComponent(
		null,
		$style->post_content['title_margin_top'],
		array(
			'id'                 => 'frm_title_margins',
			'type'               => 'vertical-margin',
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
			),
		)
	);
	?>
</div>