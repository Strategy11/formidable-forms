<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<hr class="frm12"/>
<h2 class="frm12 frm-mb-0"><?php esc_html_e( 'Quick Settings', 'formidable' ); ?></h2>

<p class="frm12"><?php esc_html_e( 'Essential presets for a quick start. Explore advanced settings for more options', 'formidable' ); ?>.</p>

<div class="frm5 frm_form_field frm-mt-md">
	<label 
		for="frm_style_qsettings_submit_bg_color"
		class="frm-style-item-heading"><?php esc_html_e( 'Primary', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field frm-mt-md">
	<?php
	new FrmPrimaryColorStyleComponent(
		null,
		$style->post_content['submit_bg_color'],
		array(
			'id'          => 'frm_style_qsettings_submit_bg_color',
			'frm_style'   => $frm_style,
			'style'       => $style,
			'action_slug' => 'submit_bg_color',
			'will_change' => array(
				$frm_style->get_field_name( 'submit_bg_color' ),
				$frm_style->get_field_name( 'slider_color' ),
				$frm_style->get_field_name( 'border_color_active' ),
				$frm_style->get_field_name( 'submit_border_color' ),
				$frm_style->get_field_name( 'progress_active_bg_color' ),
				$frm_style->get_field_name( 'date_band_color' ),
				$frm_style->get_field_name( 'date_head_bg_color' ),
				$frm_style->get_field_name( 'toggle_on_color' ),
				$frm_style->get_field_name( 'submit_active_border_color' ),
				$frm_style->get_field_name( 'submit_active_bg_color' ),
				$frm_style->get_field_name( 'submit_hover_bg_color' ),
				$frm_style->get_field_name( 'submit_hover_border_color' ),
				$frm_style->get_field_name( 'toggle_on_color' ),

			),
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_style_qsettings_text_color"
		class="frm-style-item-heading"><?php esc_html_e( 'Field Text', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmPrimaryColorStyleComponent(
		null,
		$style->post_content['text_color'],
		array(
			'id'          => 'frm_style_qsettings_text_color',
			'frm_style'   => $frm_style,
			'style'       => $style,
			'action_slug' => 'text_color',
			'will_change' => array(
				$frm_style->get_field_name( 'text_color' ),
				$frm_style->get_field_name( 'section_color' ),
				$frm_style->get_field_name( 'check_label_color' ),
			),
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_style_qsettings_border_color"
		class="frm-style-item-heading"><?php esc_html_e( 'Field Border', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmPrimaryColorStyleComponent(
		null,
		$style->post_content['border_color'],
		array(
			'id'          => 'frm_style_qsettings_border_color',
			'frm_style'   => $frm_style,
			'style'       => $style,
			'action_slug' => 'border_color',
			'will_change' => array(
				$frm_style->get_field_name( 'border_color' ),
			),
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_style_qsettings_submit_text_color"
		class="frm-style-item-heading"><?php esc_html_e( 'Button Text', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmPrimaryColorStyleComponent(
		null,
		$style->post_content['submit_text_color'],
		array(
			'id'          => 'frm_style_qsettings_submit_text_color',
			'frm_style'   => $frm_style,
			'style'       => $style,
			'action_slug' => 'submit_text_color',
			'will_change' => array(
				$frm_style->get_field_name( 'submit_text_color' ),
			),
		)
	);
	?>
</div>

<hr class="frm12"/>

<div class="frm5 frm_form_field">
	<label 
		for="frm_field_margin"
		class="frm-style-item-heading"><?php esc_html_e( 'Vertical Spacing', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmSliderStyleComponent(
		null,
		$style->post_content['field_margin'],
		array(
			'id'          => 'frm_field_margin',
			'max_value'   => 100,
			'will_change' => array(
				$frm_style->get_field_name( 'field_margin' ),
			),
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_field_pad"
		class="frm-style-item-heading"><?php esc_html_e( 'Input Field Padding', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmSliderStyleComponent(
		null,
		$style->post_content['field_pad'],
		array(
			'id'          => 'frm_field_pad',
			'max_value'   => 100,
			'will_change' => array(
				$frm_style->get_field_name( 'field_pad' ),
			),
		)
	);
	?>
</div>
<hr class="frm12"/>
<div class="frm5 frm_form_field">
	<label 
		for="frm_base_font_size"
		class="frm-style-item-heading"><?php esc_html_e( 'Base Font Size', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	if ( ! FrmStylesHelper::is_advanced_settings() ) {
		// This is displayed only in "Quick Settings" and has a default value of "false." It is updated via JavaScript when the Base Font Size slider is adjusted.
		// When set to false, the sizes in "Advanced Settings" will not be modified.
		?>
		<input type="hidden" name="<?php echo esc_attr( $frm_style->get_field_name( 'use_base_font_size' ) ); ?>" value="false" />
	<?php } ?>
	<?php
	new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'base_font_size' ),
		$style->post_content['base_font_size'],
		array(
			'id'          => 'frm_base_font_size',
			'max_value'   => 100,
			'not_show_in' => 'advanced-settings',
			'classname'   => 'frm-base-font-size',
		)
	);
	?>
</div>

<hr class="frm12"/>

<div class="frm5 frm_form_field">
	<label 
		for="frm_field_shape"
		class="frm-style-item-heading"><?php esc_html_e( 'Field Shape', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field frm-sm-z-index">
	<?php
	new FrmFieldShapeStyleComponent(
		$frm_style->get_field_name( 'field_shape_type' ),
		$style->post_content['field_shape_type'],
		array(
			'id' => 'frm_field_shape',
		)
	);
	?>
</div>

<div class="frm5 frm_form_field frm_hidden" data-frm-element="field-shape-corner-radius">
	<label 
		for="frm_border_radius"
		class="frm-style-item-heading"><?php esc_html_e( 'Corner Radius', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field frm_hidden frm-md-z-index" data-frm-element="field-shape-corner-radius">
	<?php
	new FrmSliderStyleComponent(
		null,
		$style->post_content['border_radius'],
		array(
			'id'          => 'frm_border_radius',
			'max_value'   => 50,
			'will_change' => array(
				$frm_style->get_field_name( 'border_radius' ),
			),
		)
	);
	?>
</div>

<hr class="frm12"/>

<a id="frm-style-advanced-settings-button" class="frm-button-secondary frm-mt-md frmcenter" href="#"><?php esc_html_e( 'Show advanced settings', 'formidable' ); ?></a>