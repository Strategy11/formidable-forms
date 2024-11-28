<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-style-tabs-wrapper">
	<div class="frm-tabs-delimiter">
		<span data-initial-width="169" class="frm-tabs-active-underline frm-first"></span>
	</div>
	<div class="frm-tabs-navs">
		<ul class="frm-flex-box">
			<li class="frm-active"><?php esc_html_e( 'General', 'formidable' ); ?></li>
			<li><?php esc_html_e( 'Required Indicator', 'formidable' ); ?></li>
		</ul>
	</div>
	<div class="frm-tabs-container">
		<div class="frm-tabs-slide-track frm-flex-box">
			<div class="frm-active">
				<div class="frm_grid_container">
					<div class="frm5 frm_form_field">
						<label 
							for="frm_label_color"
							class="frm-style-item-heading"><?php esc_html_e( 'Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'label_color' ),
							$style->post_content['label_color'],
							array(
								'id'          => 'frm_label_color',
								'action_slug' => 'label_color',
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label 
							for="frm_font_size"
							class="frm-style-item-heading"><?php esc_html_e( 'Font Size', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'font_size' ),
							$style->post_content['font_size'],
							array(
								'id'        => 'frm_font_size',
								'max_value' => 100,
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label 
							for="frm_required_weight"
							class="frm-style-item-heading"><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmDropdownStyleComponent(
							$frm_style->get_field_name( 'weight' ),
							$style->post_content['weight'],
							array(
								'id'      => 'frm_required_weight',
								'options' => FrmStyle::get_bold_options(),
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label 
							for="frm_position"
							class="frm-style-item-heading"><?php esc_html_e( 'Position', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmDropdownStyleComponent(
							$frm_style->get_field_name( 'position' ),
							$style->post_content['position'],
							array(
								'id'      => 'frm_position',
								'options' => FrmStylesHelper::get_css_label_positions(),
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label 
							for="frm_label_align"
							class="frm-style-item-heading"><?php esc_html_e( 'Align', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field frm-sm-z-index">
						<?php
						new FrmAlignStyleComponent(
							$frm_style->get_field_name( 'align' ),
							$style->post_content['align'],
							array(
								'id'      => 'frm_label_align',
								'options' => array( 'left', 'right' ),
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label 
							for="frm_width"
							class="frm-style-item-heading"><?php esc_html_e( 'Width', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field frm-md-z-index">
						<?php
						new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'width' ),
							$style->post_content['width'],
							array(
								'id'        => 'frm_width',
								'max_value' => 300,
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label 
							for="frm_label_padding"
							class="frm-style-item-heading"><?php esc_html_e( 'Padding', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'label_padding' ),
							$style->post_content['label_padding'],
							array(
								'id'        => 'frm_label_padding',
								'type'      => 'vertical-margin',
								'max_value' => 100,
							)
						);
						?>
					</div>
				</div>
			</div>

			<div>
				<div class="frm_grid_container">
					<div class="frm5 frm_form_field">
						<label 
							for="frm_required_color"
							class="frm-style-item-heading"><?php esc_html_e( 'Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'required_color' ),
							$style->post_content['required_color'],
							array(
								'id'          => 'frm_required_color',
								'action_slug' => 'required_color',
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label 
							for="frm_required_weight"
							class="frm-style-item-heading"><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmDropdownStyleComponent(
							$frm_style->get_field_name( 'required_weight' ),
							$style->post_content['required_weight'],
							array(
								'id'      => 'frm_required_weight',
								'options' => FrmStyle::get_bold_options(),
							)
						);
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
