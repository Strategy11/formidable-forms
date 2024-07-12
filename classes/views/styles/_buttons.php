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
			<li class="frm-active"><?php esc_html_e( 'Default', 'formidable' );?></li>
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
						<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_bg_img' ) ); ?>" id="frm_submit_bg_img" value="<?php echo esc_attr( $style->post_content['submit_bg_img'] ); ?>"  />
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Background Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'submit_bg_color' ),
							$style->post_content['submit_bg_color'],
							array(
								'id'          => 'frm_submit_bg_color',
								'action_slug' => 'submit_bg_color',
							)
						); 
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Font Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
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
						<?php new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'submit_font_size' ),
							$style->post_content['submit_font_size'],
							array(
								'id'        => 'frm_submit_font_size',
								'max_value' => 100
							)
						); ?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmDropdownStyleComponent(
							$frm_style->get_field_name( 'submit_weight' ),
							$style->post_content['submit_weight'],
							array(
								'id'      => 'frm_submit_weight',
								'options' => FrmStyle::get_bold_options(),
							)
						); ?>
					</div>

					<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Width', 'formidable' ); ?></label></div>
					<div class="frm7 frm_form_field">
						<?php new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'submit_width' ),
							$style->post_content['submit_width'],
							array(
								'id'        => 'frm_submit_width',
								'max_value' => 300,
							)
						); ?>
					</div>

					<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Height', 'formidable' ); ?></label></div>
					<div class="frm7 frm_form_field">
						<?php new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'submit_height' ),
							$style->post_content['submit_height'],
							array(
								'id'        => 'frm_submit_height',
								'max_value' => 300,
							)
						); ?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Border Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
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
						<?php new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'submit_border_width' ),
							$style->post_content['submit_border_width'],
							array(
								'id'        => 'frm_submit_border_width',
								'max_value' => 25,
							)
						); ?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Shadow', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
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
						<?php new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'submit_border_radius' ),
							$style->post_content['submit_border_radius'],
							array(
								'id'        => 'frm_submit_border_radius',
								'max_value' => 50,
							)
						); ?>
					</div>

					<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Margin', 'formidable' ); ?></label></div>
					<div class="frm7 frm_form_field">
						<?php new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'submit_margin' ),
							$style->post_content['submit_margin'],
							array(
								'id'        => 'frm_submit_margin',
								'type'		=> 'vertical-margin',
								'max_value' => 100
							)
						); ?>
					</div>

					<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Padding', 'formidable' ); ?></label></div>
					<div class="frm7 frm_form_field">
						<?php new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'submit_padding' ),
							$style->post_content['submit_padding'],
							array(
								'id'        => 'frm_submit_padding',
								'type'		=> 'vertical-margin',
								'max_value' => 100
							)
						); ?>
					</div>

				</div>
			</div>
			<div class="frm-active">
				<div class="frm_grid_container">
					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'BG Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
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
						<?php new FrmColorpickerStyleComponent(
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
						<?php new FrmColorpickerStyleComponent(
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
						<?php new FrmColorpickerStyleComponent(
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
						<?php new FrmColorpickerStyleComponent(
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
						<?php new FrmColorpickerStyleComponent(
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

<?php /*
<p class="frm-mt-0">
	<label for="frm_submit_style">
		<input type="checkbox" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_style' ) ); ?>" id="frm_submit_style" <?php checked( $style->post_content['submit_style'], 1 ); ?> value="1" />
		<?php esc_html_e( 'Disable submit button styling', 'formidable' ); ?>
		<span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Note: If disabled, you may not see the change take effect until you make 2 more styling changes or click "Update Options".', 'formidable' ); ?>"></span>
	</label>
</p>

<p class="frm4 frm_first frm_form_field">
	<label for="frm_submit_font_size"><?php esc_html_e( 'Size', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_font_size' ) ); ?>" id="frm_submit_font_size" value="<?php echo esc_attr( $style->post_content['submit_font_size'] ); ?>"  size="3" />
</p>

<p class="frm4 frm_form_field">
	<label for="frm_submit_width"><?php esc_html_e( 'Width', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_width' ) ); ?>" id="frm_submit_width" value="<?php echo esc_attr( $style->post_content['submit_width'] ); ?>"  size="5" />
</p>

<p class="frm4 frm_form_field">
	<label for="frm_submit_height"><?php esc_html_e( 'Height', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_height' ) ); ?>" id="frm_submit_height" value="<?php echo esc_attr( $style->post_content['submit_height'] ); ?>"  size="5" />
</p>

<p class="frm4 frm_first frm_form_field">
	<label for="frm_submit_weight"><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_weight' ) ); ?>" id="frm_submit_weight">
		<?php foreach ( FrmStyle::get_bold_options() as $value => $name ) { ?>
			<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $style->post_content['submit_weight'], $value ); ?>><?php echo esc_html( $name ); ?></option>
		<?php } ?>
	</select>
</p>

<p class="frm4 frm_form_field">
	<label for="frm_submit_border_radius"><?php esc_html_e( 'Corners', 'formidable' ); ?></label>
	<input type="text" value="<?php echo esc_attr( $style->post_content['submit_border_radius'] ); ?>" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_border_radius' ) ); ?>" id="frm_submit_border_radius" size="4"/>
</p>

<p class="frm4 frm_first frm_form_field">
	<label for="frm_submit_bg_color"><?php esc_html_e( 'BG color', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_bg_color' ) ); ?>" id="frm_submit_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_bg_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'submit_bg_color' ); ?> />
</p>

<p class="frm4 frm_form_field">
	<label for="frm_submit_text_color"><?php esc_html_e( 'Text', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_text_color' ) ); ?>" id="frm_submit_text_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_text_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'submit_text_color' ); ?> />
</p>

<p class="frm4 frm_first frm_form_field">
	<label for="frm_submit_border_color"><?php esc_html_e( 'Border', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_border_color' ) ); ?>" id="frm_submit_border_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_border_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'submit_border_color' ); ?> />
</p>

<p class="frm4 frm_form_field">
	<label for="frm_submit_border_width"><?php esc_html_e( 'Thickness', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_border_width' ) ); ?>" id="frm_submit_border_width" value="<?php echo esc_attr( $style->post_content['submit_border_width'] ); ?>" size="4" />
</p>

<p class="frm4 frm_end frm_form_field">
	<label for="frm_submit_shadow_color"><?php esc_html_e( 'Shadow', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_shadow_color' ) ); ?>" id="frm_submit_shadow_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_shadow_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'submit_shadow_color' ); ?> />
</p>

<p class="frm_clear">
	<label for="frm_submit_bg_img"><?php esc_html_e( 'BG Image', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_bg_img' ) ); ?>" id="frm_submit_bg_img" value="<?php echo esc_attr( $style->post_content['submit_bg_img'] ); ?>"  />
</p>

<p class="frm4 frm_first frm_form_field">
	<label for="frm_submit_margin"><?php esc_html_e( 'Margin', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_margin' ) ); ?>" id="frm_submit_margin" value="<?php echo esc_attr( $style->post_content['submit_margin'] ); ?>" size="6" />
</p>

<p class="frm4 frm_form_field">
	<label for="frm_submit_padding"><?php esc_html_e( 'Padding', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_padding' ) ); ?>" id="frm_submit_padding" value="<?php echo esc_attr( $style->post_content['submit_padding'] ); ?>" size="6" />
</p>

<h4 class="frm_clear">
	<span><?php esc_html_e( 'On Hover', 'formidable' ); ?></span>
</h4>
<p class="frm4 frm_first frm_form_field">
	<label for="frm_submit_hover_bg_color"><?php esc_html_e( 'BG color', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_hover_bg_color' ) ); ?>" id="frm_submit_hover_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_hover_bg_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'submit_hover_bg_color' ); ?> />
</p>

<p class="frm4 frm_form_field">
	<label for="frm_submit_hover_color"><?php esc_html_e( 'Text', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_hover_color' ) ); ?>" id="frm_submit_hover_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_hover_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'submit_hover_color' ); ?> />
</p>

<p class="frm4 frm_end frm_form_field">
	<label for="frm_submit_hover_border_color"><?php esc_html_e( 'Border', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_hover_border_color' ) ); ?>" id="frm_submit_hover_border_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_hover_border_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'submit_hover_border_color' ); ?> />
</p>

<h4 class="frm_clear">
	<span><?php esc_html_e( 'On Click', 'formidable' ); ?></span>
</h4>
<p class="frm4 frm_first frm_form_field">
	<label for="frm_submit_active_bg_color"><?php esc_html_e( 'BG color', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_active_bg_color' ) ); ?>" id="frm_submit_active_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_active_bg_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'submit_active_bg_color' ); ?> />
</p>

<p class="frm4 frm_form_field">
	<label for="frm_submit_active_color"><?php esc_html_e( 'Text', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_active_color' ) ); ?>" id="frm_submit_active_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_active_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'submit_active_color' ); ?> />
</p>

<p class="frm4 frm_end frm_form_field">
	<label for="frm_submit_active_border_color"><?php esc_html_e( 'Border', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'submit_active_border_color' ) ); ?>" id="frm_submit_active_border_color" class="hex" value="<?php echo esc_attr( $style->post_content['submit_active_border_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'submit_active_border_color' ); ?> />
</p>
*/ ?>