<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm-style-tabs-wrapper">
	<div class="frm-tabs-delimiter">
		<span data-initial-width="193" class="frm-tabs-active-underline frm-first"></span>
	</div>
	<div class="frm-tabs-navs">
		<ul class="frm-flex-box">
			<li class="frm-active"><?php esc_html_e( 'Success', 'formidable' );?></li>
			<li><?php esc_html_e( 'Error', 'formidable' ); ?></li>
		</ul>
	</div>
	<div class="frm-tabs-container">
		<div class="frm-tabs-slide-track frm-flex-box">
			<div class="frm-active">
				<div class="frm_grid_container">
					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'BG Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'success_bg_color' ),
							$style->post_content['success_bg_color'],
							array(
								'id'          => 'frm_success_bg_color',
								'action_slug' => 'success_bg_color',
							)
						); 
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Border Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'success_border_color' ),
							$style->post_content['success_border_color'],
							array(
								'id'          => 'frm_success_border_color',
								'action_slug' => 'success_border_color',
							)
						); 
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Font Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'success_text_color' ),
							$style->post_content['success_text_color'],
							array(
								'id'          => 'frm_success_text_color',
								'action_slug' => 'success_text_color',
							)
						); 
						?>
					</div>

					<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Font Size', 'formidable' ); ?></label></div>
					<div class="frm7 frm_form_field">
						<?php new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'success_font_size' ),
							$style->post_content['success_font_size'],
							array(
								'id'        => 'frm_success_font_size',
								'max_value' => 100,
							)
						); ?>
					</div>
				</div>
			</div>

			<div>
				<div class="frm_grid_container">
					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'BG Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'error_bg' ),
							$style->post_content['error_bg'],
							array(
								'id'          => 'frm_error_bg',
								'action_slug' => 'error_bg',
							)
						); 
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Border Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'error_border' ),
							$style->post_content['error_border'],
							array(
								'id'          => 'frm_error_border',
								'action_slug' => 'error_border',
							)
						); 
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Font Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'error_text' ),
							$style->post_content['error_text'],
							array(
								'id'          => 'frm_error_text',
								'action_slug' => 'error_text',
							)
						); 
						?>
					</div>

					<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Font Size', 'formidable' ); ?></label></div>
					<div class="frm7 frm_form_field">
						<?php new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'error_font_size' ),
							$style->post_content['error_font_size'],
							array(
								'id'        => 'frm_error_font_size',
								'max_value' => 100,
							)
						); ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php /*

		<h4><span><?php esc_html_e( 'Success Messages', 'formidable' ); ?></span></h4>
		<p class="frm4 frm_first frm_form_field">
			<label><?php esc_html_e( 'BG color', 'formidable' ); ?></label>
			<input name="<?php echo esc_attr( $frm_style->get_field_name( 'success_bg_color' ) ); ?>" id="frm_success_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['success_bg_color'] ); ?>" type="text" <?php do_action( 'frm_style_settings_input_atts', 'success_bg_color' ); ?> />
		</p>
		<p class="frm4 frm_form_field">
			<label><?php esc_html_e( 'Border', 'formidable' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'success_border_color' ) ); ?>" id="frm_success_border_color" class="hex" value="<?php echo esc_attr( $style->post_content['success_border_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'success_border_color' ); ?> />
		</p>
		<p class="frm4 frm_end frm_form_field">
			<label><?php esc_html_e( 'Text', 'formidable' ); ?></label>
			<input name="<?php echo esc_attr( $frm_style->get_field_name( 'success_text_color' ) ); ?>" id="frm_success_text_color" class="hex" value="<?php echo esc_attr( $style->post_content['success_text_color'] ); ?>" type="text" <?php do_action( 'frm_style_settings_input_atts', 'success_text_color' ); ?> />
		</p>
		<p class="frm4 frm_first frm_form_field">
			<label><?php esc_html_e( 'Size', 'formidable' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'success_font_size' ) ); ?>" id="frm_success_font_size" value="<?php echo esc_attr( $style->post_content['success_font_size'] ); ?>"  size="3" />
		</p>

		<h4 class="frm_clear">
			<span><?php esc_html_e( 'Error Messages', 'formidable' ); ?></span>
		</h4>
		<p class="frm4 frm_first frm_form_field">
			<label><?php esc_html_e( 'BG color', 'formidable' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'error_bg' ) ); ?>" id="frm_error_bg" class="hex" value="<?php echo esc_attr( $style->post_content['error_bg'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'error_bg' ); ?> />
		</p>
		<p class="frm4 frm_form_field">
			<label><?php esc_html_e( 'Border', 'formidable' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'error_border' ) ); ?>" id="frm_error_border" class="hex" value="<?php echo esc_attr( $style->post_content['error_border'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'error_border' ); ?> />
		</p>
		<p class="frm4 frm_end frm_form_field">
			<label><?php esc_html_e( 'Text', 'formidable' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'error_text' ) ); ?>" id="frm_error_text" class="hex" value="<?php echo esc_attr( $style->post_content['error_text'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'error_text' ); ?> />
		</p>

		<p class="frm4 frm_first frm_form_field">
			<label><?php esc_html_e( 'Size', 'formidable' ); ?></label>
			<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'error_font_size' ) ); ?>" id="frm_error_font_size" value="<?php echo esc_attr( $style->post_content['error_font_size'] ); ?>"  size="3" />
		</p>
*/ ?>
