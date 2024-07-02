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
			<li class="frm-active"><?php esc_html_e( 'Default', 'formidable' );?></li>
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
						<label class="frm-style-item-heading"><?php esc_html_e( 'Background', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
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
						<label class="frm-style-item-heading"><?php esc_html_e( 'Font Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
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
						<label class="frm-style-item-heading"><?php esc_html_e( 'Border', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
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
						<label class="frm-style-item-heading"><?php esc_html_e( 'Border Width', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'field_border_width' ),
							(int) $style->post_content['field_border_width'],
							array( 'id' => 'frm_field_border_width' )
						); ?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Border Style', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmDropdownStyleComponent(
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
						); ?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php _e( 'Box Shadow', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
							FrmHtmlHelper::toggle(
								'remove_box_shadow',
								$frm_style->get_field_name( 'remove_box_shadow' ),
								array(
									'div_class' => 'with_frm_style frm_toggle',
									'checked'   => ! $style->post_content['remove_box_shadow'],
									'echo'      => true,
								)
						); ?>
					</div>
				</div>
			</div>

			<div>
				<div class="frm_grid_container">
					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Background', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
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
						<label class="frm-style-item-heading"><?php esc_html_e( 'Border', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
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
						<label class="frm-style-item-heading"><?php _e( 'Box Shadow', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php
							FrmHtmlHelper::toggle(
								'remove_box_shadow_active',
								$frm_style->get_field_name( 'remove_box_shadow_active' ),
								array(
									'div_class' => 'with_frm_style frm_toggle',
									'checked'   => ! $style->post_content['remove_box_shadow_active'],
									'echo'      => true,
								)
						); ?>
					</div>
				</div>
			</div>

			<div>
				<div class="frm_grid_container">
					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Background', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
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
						<label class="frm-style-item-heading"><?php esc_html_e( 'Font', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
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
						<label class="frm-style-item-heading"><?php esc_html_e( 'Border', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
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
						<label class="frm-style-item-heading"><?php esc_html_e( 'Background', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
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
						<label class="frm-style-item-heading"><?php esc_html_e( 'Font', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
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
						<label class="frm-style-item-heading"><?php esc_html_e( 'Border', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'border_color_error' ),
							$style->post_content['border_color_error'],
							array(
								'id'          => 'frm_border_color_error',
								'action_slug' => 'border_color_error',
							)
						); 
						?>
					</div>

					<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Border Width', 'formidable' ); ?></label></div>
					<div class="frm7 frm_form_field">
						<?php new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'border_width_error' ),
							(int) $style->post_content['border_width_error'],
							array( 'id' => 'frm_border_width_error' )
						); ?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Style', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmDropdownStyleComponent(
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
						); ?>
					</div>

				</div>
			</div>
		</div>
	</div>
</div>
<?php /*
			<p class="frm4 frm_first frm_form_field">
				<label class="background"><?php esc_html_e( 'BG color', 'formidable' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'bg_color' ) ); ?>" id="frm_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['bg_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'bg_color' ); ?> />
			</p>
			<p class="frm4 frm_form_field">
				<label><?php esc_html_e( 'Text', 'formidable' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'text_color' ) ); ?>" id="frm_text_color" class="hex" value="<?php echo esc_attr( $style->post_content['text_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'text_color' ); ?> />
			</p>
			<p class="frm4 frm_first frm_form_field">
				<label><?php esc_html_e( 'Border', 'formidable' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'border_color' ) ); ?>" id="frm_border_color" class="hex" value="<?php echo esc_attr( $style->post_content['border_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'border_color' ); ?> />
			</p>
			<p class="frm4 frm_form_field">
				<label><?php esc_html_e( 'Thickness', 'formidable' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'field_border_width' ) ); ?>" id="frm_field_border_width" value="<?php echo esc_attr( $style->post_content['field_border_width'] ); ?>" size="4" />
			</p>
			<p class="frm4 frm_form_field">
				<label><?php esc_html_e( 'Style', 'formidable' ); ?></label>
				<select name="<?php echo esc_attr( $frm_style->get_field_name( 'field_border_style' ) ); ?>" id="frm_field_border_style">
					<option value="solid" <?php selected( $style->post_content['field_border_style'], 'solid' ); ?>>
						<?php esc_html_e( 'solid', 'formidable' ); ?>
					</option>
					<option value="dotted" <?php selected( $style->post_content['field_border_style'], 'dotted' ); ?>>
						<?php esc_html_e( 'dotted', 'formidable' ); ?>
					</option>
					<option value="dashed" <?php selected( $style->post_content['field_border_style'], 'dashed' ); ?>>
						<?php esc_html_e( 'dashed', 'formidable' ); ?>
					</option>
					<option value="double" <?php selected( $style->post_content['field_border_style'], 'double' ); ?>>
						<?php esc_html_e( 'double', 'formidable' ); ?>
					</option>
				</select>
			</p>

			<p class="frm_clear frm_no_bottom_margin">
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $frm_style->get_field_name( 'remove_box_shadow' ) ); ?>" id="frm_remove_box_shadow" value="1" <?php checked( $style->post_content['remove_box_shadow'], 1 ); ?> />
					<?php esc_html_e( 'Remove box shadow', 'formidable' ); ?>
				</label>
			</p>

			<h4><span><?php esc_html_e( 'Active Style', 'formidable' ); ?></span></h4>
			<p class="frm4 frm_first frm_form_field">
				<label class="background"><?php esc_html_e( 'BG color', 'formidable' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'bg_color_active' ) ); ?>" id="frm_bg_color_active" class="hex" value="<?php echo esc_attr( $style->post_content['bg_color_active'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'bg_color_active' ); ?> />
			</p>
			<p class="frm4 frm_form_field">
				<label><?php esc_html_e( 'Border', 'formidable' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'border_color_active' ) ); ?>" id="frm_border_color_active" class="hex" value="<?php echo esc_attr( $style->post_content['border_color_active'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'border_color_active' ); ?> />
			</p>

			<p class="frm_clear frm_no_bottom_margin">
				<label>
					<input type="checkbox" name="<?php echo esc_attr( $frm_style->get_field_name( 'remove_box_shadow_active' ) ); ?>" id="frm_remove_box_shadow_active" value="1" <?php checked( $style->post_content['remove_box_shadow_active'], 1 ); ?> />
					<?php esc_html_e( 'Remove box shadow', 'formidable' ); ?>
				</label>
			</p>

			<h4><span><?php esc_html_e( 'Error Style', 'formidable' ); ?></span></h4>
			<p class="frm4 frm_first frm_form_field">
				<label class="background"><?php esc_html_e( 'BG color', 'formidable' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'bg_color_error' ) ); ?>" id="frm_bg_color_error" class="hex" value="<?php echo esc_attr( $style->post_content['bg_color_error'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'bg_color_error' ); ?> />
			</p>
			<p class="frm4 frm_form_field">
				<label><?php esc_html_e( 'Text', 'formidable' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'text_color_error' ) ); ?>" id="frm_text_color_error" class="hex" value="<?php echo esc_attr( $style->post_content['text_color_error'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'text_color_error' ); ?> />
			</p>
			<p class="frm4 frm_first frm_form_field">
				<label><?php esc_html_e( 'Border', 'formidable' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'border_color_error' ) ); ?>" id="frm_border_color_error" class="hex" value="<?php echo esc_attr( $style->post_content['border_color_error'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'border_color_error' ); ?> />
			</p>
			<p class="frm4 frm_first frm_form_field">
				<label><?php esc_html_e( 'Thickness', 'formidable' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'border_width_error' ) ); ?>" id="frm_border_width_error" value="<?php echo esc_attr( $style->post_content['border_width_error'] ); ?>" size="4" />
			</p>
			<p class="frm4 frm_form_field">
				<label><?php esc_html_e( 'Style', 'formidable' ); ?></label>
				<select name="<?php echo esc_attr( $frm_style->get_field_name( 'border_style_error' ) ); ?>" id="frm_border_style_error">
					<option value="solid" <?php selected( $style->post_content['border_style_error'], 'solid' ); ?>>
						<?php esc_html_e( 'solid', 'formidable' ); ?>
					</option>
					<option value="dotted" <?php selected( $style->post_content['border_style_error'], 'dotted' ); ?>>
						<?php esc_html_e( 'dotted', 'formidable' ); ?>
					</option>
					<option value="dashed" <?php selected( $style->post_content['border_style_error'], 'dashed' ); ?>>
						<?php esc_html_e( 'dashed', 'formidable' ); ?>
					</option>
					<option value="double" <?php selected( $style->post_content['border_style_error'], 'double' ); ?>>
						<?php esc_html_e( 'double', 'formidable' ); ?>
					</option>
				</select>
			</p>

			<h4 class="frm_clear">
				<span><?php esc_html_e( 'Read Only Style', 'formidable' ); ?></span>
			</h4>
			<p class="frm4 frm_first frm_form_field">
				<label class="background"><?php esc_html_e( 'BG color', 'formidable' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'bg_color_disabled' ) ); ?>" id="frm_bg_color_disabled" class="hex" value="<?php echo esc_attr( $style->post_content['bg_color_disabled'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'bg_color_disabled' ); ?> />
			</p>
			<p class="frm4 frm_form_field">
				<label><?php esc_html_e( 'Text', 'formidable' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'text_color_disabled' ) ); ?>" id="frm_text_color_disabled" class="hex" value="<?php echo esc_attr( $style->post_content['text_color_disabled'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'text_color_disabled' ); ?> />
			</p>
			<p class="frm4 frm_end frm_form_field">
				<label><?php esc_html_e( 'Border', 'formidable' ); ?></label>
				<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'border_color_disabled' ) ); ?>" id="frm_border_color_disabled" class="hex" value="<?php echo esc_attr( $style->post_content['border_color_disabled'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'border_color_disabled' ); ?> />
			</p>
*/ ?>
