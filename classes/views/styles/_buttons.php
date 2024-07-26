<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-style-tabs-wrapper">
	<div class="frm-tabs-delimiter">
		<span data-initial-width="123" class="frm-tabs-active-underline frm-first"></span>
	</div>
	<div class="frm-tabs-navs">
		<ul class="frm-flex-box">
			<li class="frm-active"><?php esc_html_e( 'Default', 'formidable' ); ?></li>
			<li><?php esc_html_e( 'On Hover', 'formidable' ); ?></li>
			<li><?php esc_html_e( 'On Click', 'formidable' ); ?></li>
		</ul>
	</div>
	<div class="frm-tabs-container">
		<div class="frm-tabs-slide-track frm-flex-box">
			<div class="frm-active">
				<div class="frm_grid_container">
					<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Background Image', 'formidable' ); ?></label></div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmBackgroundImageStyleComponent(
							$frm_style->get_field_name( 'submit_bg_color' ),
							$style->post_content['submit_bg_color'],
							array(
								'id'                  => 'frm_submit_bg_color',
								'frm_style'           => $frm_style,
								'style'               => $style,
								'action_slug'         => 'submit_bg_color',
								'image_id_input_name' => 'submit_bg_img',
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Font Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'submit_text_color' ),
							$style->post_content['submit_text_color'],
							array(
								'id'          => 'frm_submit_text_color',
								'action_slug' => 'submit_text_color',
							)
						); 
						?>
					</div>

					<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Font Size', 'formidable' ); ?></label></div>
					<div class="frm7 frm_form_field">
						<?php 
						new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'submit_font_size' ),
							$style->post_content['submit_font_size'],
							array(
								'id'        => 'frm_submit_font_size',
								'max_value' => 100,
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmDropdownStyleComponent(
							$frm_style->get_field_name( 'submit_weight' ),
							$style->post_content['submit_weight'],
							array(
								'id'      => 'frm_submit_weight',
								'options' => FrmStyle::get_bold_options(),
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Width', 'formidable' ); ?></label></div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'submit_width' ),
							$style->post_content['submit_width'],
							array(
								'id'        => 'frm_submit_width',
								'max_value' => 300,
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Height', 'formidable' ); ?></label></div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'submit_height' ),
							$style->post_content['submit_height'],
							array(
								'id'        => 'frm_submit_height',
								'max_value' => 300,
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Border Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'submit_border_color' ),
							$style->post_content['submit_border_color'],
							array(
								'id'          => 'frm_submit_border_color',
								'action_slug' => 'submit_border_color',
							)
						); 
						?>
					</div>

					<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Border Width', 'formidable' ); ?></label></div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'submit_border_width' ),
							$style->post_content['submit_border_width'],
							array(
								'id'        => 'frm_submit_border_width',
								'max_value' => 25,
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Shadow', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'submit_shadow_color' ),
							$style->post_content['submit_shadow_color'],
							array(
								'id'          => 'frm_submit_shadow_color',
								'action_slug' => 'submit_shadow_color',
							)
						); 
						?>
					</div>

					<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Corner Radius', 'formidable' ); ?></label></div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'submit_border_radius' ),
							$style->post_content['submit_border_radius'],
							array(
								'id'        => 'frm_submit_border_radius',
								'max_value' => 50,
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Margin', 'formidable' ); ?></label></div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'submit_margin' ),
							$style->post_content['submit_margin'],
							array(
								'id'        => 'frm_submit_margin',
								'type'      => 'vertical-margin',
								'max_value' => 100,
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Padding', 'formidable' ); ?></label></div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'submit_padding' ),
							$style->post_content['submit_padding'],
							array(
								'id'        => 'frm_submit_padding',
								'type'      => 'vertical-margin',
								'max_value' => 100,
							)
						);
						?>
					</div>

				</div>
			</div>
			<div class="frm-active">
				<div class="frm_grid_container">
					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'BG Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'submit_hover_bg_color' ),
							$style->post_content['submit_hover_bg_color'],
							array(
								'id'          => 'frm_submit_hover_bg_color',
								'action_slug' => 'submit_hover_bg_color',
							)
						); 
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Font Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'submit_hover_color' ),
							$style->post_content['submit_hover_color'],
							array(
								'id'          => 'frm_submit_hover_color',
								'action_slug' => 'submit_hover_color',
							)
						); 
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Border Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'submit_hover_border_color' ),
							$style->post_content['submit_hover_border_color'],
							array(
								'id'          => 'frm_submit_hover_border_color',
								'action_slug' => 'submit_hover_border_color',
							)
						); 
						?>
					</div>

				</div>
			</div>
			<div class="frm-active">
				<div class="frm_grid_container">
					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'BG Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'submit_active_bg_color' ),
							$style->post_content['submit_active_bg_color'],
							array(
								'id'          => 'frm_submit_active_bg_color',
								'action_slug' => 'submit_active_bg_color',
							)
						); 
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Font Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'submit_active_color' ),
							$style->post_content['submit_active_color'],
							array(
								'id'          => 'frm_submit_active_color',
								'action_slug' => 'submit_active_color',
							)
						); 
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Border Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'submit_active_border_color' ),
							$style->post_content['submit_active_border_color'],
							array(
								'id'          => 'frm_submit_active_border_color',
								'action_slug' => 'submit_active_border_color',
							)
						); 
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>