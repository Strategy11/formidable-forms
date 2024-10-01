<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-style-tabs-wrapper">
	<div class="frm-tabs-delimiter">
		<span data-initial-width="88" class="frm-tabs-active-underline frm-first"></span>
	</div>
	<div class="frm-tabs-navs">
		<ul class="frm-flex-box">
			<li class="frm-active"><?php esc_html_e( 'Default', 'formidable' ); ?></li>
			<li><?php esc_html_e( 'Active', 'formidable' ); ?></li>
			<li><?php esc_html_e( 'Read Only', 'formidable' ); ?></li>
			<li><?php esc_html_e( 'Error', 'formidable' ); ?></li>
		</ul>
	</div>
	<div class="frm-tabs-container">
		<div class="frm-tabs-slide-track frm-flex-box">
			<div class="frm-active">
				<div class="frm_grid_container">
					<div class="frm5 frm_form_field">
						<label
							for="frm_bg_color"
							class="frm-style-item-heading"><?php esc_html_e( 'Background', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'bg_color' ),
							$style->post_content['bg_color'],
							array(
								'id'          => 'frm_bg_color',
								'action_slug' => 'bg_color',
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label
							for="frm_text_color"
							class="frm-style-item-heading"><?php esc_html_e( 'Field Text', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'text_color' ),
							$style->post_content['text_color'],
							array(
								'id'          => 'frm_text_color',
								'action_slug' => 'text_color',
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label
							for="frm_border_color"
							class="frm-style-item-heading"><?php esc_html_e( 'Border', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'border_color' ),
							$style->post_content['border_color'],
							array(
								'id'          => 'frm_border_color',
								'action_slug' => 'border_color',
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label
							for="frm_field_border_width"
							class="frm-style-item-heading"><?php esc_html_e( 'Border Width', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'field_border_width' ),
							$style->post_content['field_border_width'],
							array(
								'id'        => 'frm_field_border_width',
								'max_value' => 25,
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label
							for="frm_field_border_style"
							class="frm-style-item-heading"><?php esc_html_e( 'Border Style', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmDropdownStyleComponent(
							$frm_style->get_field_name( 'field_border_style' ),
							$style->post_content['field_border_style'],
							array(
								'id'      => 'frm_field_border_style',
								'options' => array(
									'solid'  => esc_html__( 'solid', 'formidable' ),
									'dotted' => esc_html__( 'dotted', 'formidable' ),
									'dashed' => esc_html__( 'dashed', 'formidable' ),
									'double' => esc_html__( 'double', 'formidable' ),
								),
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label
							for="frm_box_shadow"
							class="frm-style-item-heading"><?php esc_html_e( 'Remove Box Shadow', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field frm-style-component">
						<?php
						FrmHtmlHelper::toggle(
							'frm_box_shadow',
							$frm_style->get_field_name( 'remove_box_shadow' ),
							array(
								'div_class' => 'with_frm_style frm_toggle',
								'checked'   => ! empty( $style->post_content['remove_box_shadow'] ),
								'echo'      => true,
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
							for="frm_bg_color_active"
							class="frm-style-item-heading"><?php esc_html_e( 'Background', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'bg_color_active' ),
							$style->post_content['bg_color_active'],
							array(
								'id'          => 'frm_bg_color_active',
								'action_slug' => 'bg_color_active',
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label
							for="frm_border_color_active"
							class="frm-style-item-heading"><?php esc_html_e( 'Border', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'border_color_active' ),
							$style->post_content['border_color_active'],
							array(
								'id'          => 'frm_border_color_active',
								'action_slug' => 'border_color_active',
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Remove Box Shadow', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field frm-style-component">
						<?php
							FrmHtmlHelper::toggle(
								'remove_box_shadow_active',
								$frm_style->get_field_name( 'remove_box_shadow_active' ),
								array(
									'div_class'       => 'with_frm_style frm_toggle',
									'checked'         => ! empty( $style->post_content['remove_box_shadow_active'] ),
									'echo'            => true,
									'aria-label-attr' => __( 'Toggle box shadow', 'formidable' ),
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
							for="frm_bg_color_disabled"
							class="frm-style-item-heading"><?php esc_html_e( 'Background', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'bg_color_disabled' ),
							$style->post_content['bg_color_disabled'],
							array(
								'id'          => 'frm_bg_color_disabled',
								'action_slug' => 'bg_color_disabled',
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label
							for="frm_text_color_disabled"
							class="frm-style-item-heading"><?php esc_html_e( 'Field Text', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'text_color_disabled' ),
							$style->post_content['text_color_disabled'],
							array(
								'id'          => 'frm_text_color_disabled',
								'action_slug' => 'text_color_disabled',
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label
							for="frm_border_color_disabled"
							class="frm-style-item-heading"><?php esc_html_e( 'Border', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'border_color_disabled' ),
							$style->post_content['border_color_disabled'],
							array(
								'id'          => 'frm_border_color_disabled',
								'action_slug' => 'border_color_disabled',
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
							for="frm_bg_color_error"
							class="frm-style-item-heading"><?php esc_html_e( 'Background', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'bg_color_error' ),
							$style->post_content['bg_color_error'],
							array(
								'id'          => 'frm_bg_color_error',
								'action_slug' => 'bg_color_error',
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label
							for="frm_text_color_error"
							class="frm-style-item-heading"><?php esc_html_e( 'Field Text', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'text_color_error' ),
							$style->post_content['text_color_error'],
							array(
								'id'          => 'frm_text_color_error',
								'action_slug' => 'text_color_error',
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label
							for="frm_border_color_error"
							class="frm-style-item-heading"><?php esc_html_e( 'Border', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'border_color_error' ),
							$style->post_content['border_color_error'],
							array(
								'id'          => 'frm_border_color_error',
								'action_slug' => 'border_color_error',
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label
							for="frm_border_width_error"
							class="frm-style-item-heading"><?php esc_html_e( 'Border Width', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'border_width_error' ),
							$style->post_content['border_width_error'],
							array(
								'id'        => 'frm_border_width_error',
								'max_value' => 25,
							)
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label
							for="fborder_style_error"
							class="frm-style-item-heading"><?php esc_html_e( 'Style', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
						new FrmDropdownStyleComponent(
							$frm_style->get_field_name( 'border_style_error' ),
							$style->post_content['border_style_error'],
							array(
								'id'      => 'fborder_style_error',
								'options' => array(
									'solid'  => esc_html__( 'solid', 'formidable' ),
									'dotted' => esc_html__( 'dotted', 'formidable' ),
									'dashed' => esc_html__( 'dashed', 'formidable' ),
									'double' => esc_html__( 'double', 'formidable' ),
								),
							)
						);
						?>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>