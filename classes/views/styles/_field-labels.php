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
			<li class="frm-active"><?php esc_html_e( 'General', 'formidable' );?></li>
			<li><?php esc_html_e( 'Required Indicator', 'formidable' ); ?></li>
		</ul>
	</div>
	<div class="frm-tabs-container">
		<div class="frm-tabs-slide-track frm-flex-box">
			<div class="frm-active">
				<div class="frm_grid_container">
					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
							$frm_style->get_field_name( 'label_color' ),
							$style->post_content['label_color'],
							array(
								'id'         => 'frm_label_color',
								'action_slug' => 'label_color',
							)
						); 
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Font Size', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'font_size' ),
							(int) $style->post_content['font_size'],
							array( 'id' => 'frm_font_size' )
						); ?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmDropdownStyleComponent(
							$frm_style->get_field_name( 'weight' ),
							$style->post_content['weight'],
							array(
								'id'      => 'frm_required_weight',
								'options' => FrmStyle::get_bold_options(),
							)
						); ?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Position', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmDropdownStyleComponent(
							$frm_style->get_field_name( 'position' ),
							$style->post_content['position'],
							array(
								'id' 	  => 'frm_position',
								'options' => FrmStylesHelper::get_css_label_positions(),
							)
						); ?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Align', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmAlignStyleComponent(
							$frm_style->get_field_name( 'align' ),
							$style->post_content['align'],
							array(
								'options' => array( 'left', 'right' )
							),
						);
						?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Width', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'width' ),
							$style->post_content['width'],
							array( 'id' => 'frm_width' )
						); ?>
					</div>

					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Padding', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmSliderStyleComponent(
							$frm_style->get_field_name( 'label_padding' ),
							$style->post_content['label_padding'],
							array( 'id' => 'frm_label_padding' )
						); ?>
					</div>
				</div>
			</div>

			<div>
				<div class="frm_grid_container">
					<div class="frm5 frm_form_field">
						<label class="frm-style-item-heading"><?php esc_html_e( 'Color', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmColorpickerStyleComponent(
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
						<label class="frm-style-item-heading"><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
					</div>
					<div class="frm7 frm_form_field">
						<?php new FrmDropdownStyleComponent(
							$frm_style->get_field_name( 'required_weight' ),
							$style->post_content['required_weight'],
							array(
								'id'      => 'frm_required_weight',
								'options' => FrmStyle::get_bold_options(),
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
	<label><?php esc_html_e( 'Color', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'label_color' ) ); ?>" id="frm_label_color" class="hex" value="<?php echo esc_attr( $style->post_content['label_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'label_color' ); ?> />
</p>
<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name( 'weight' ) ); ?>" id="frm_weight">
		<?php foreach ( FrmStyle::get_bold_options() as $value => $name ) { ?>
		<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $style->post_content['weight'], $value ); ?>><?php echo esc_html( $name ); ?></option>
		<?php } ?>
	</select>
</p>
<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Size', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'font_size' ) ); ?>" id="frm_font_size" value="<?php echo esc_attr( $style->post_content['font_size'] ); ?>"  size="3" />
</p>
*/ ?>
<?php /*
<p class="frm4 frm_first frm_form_field">
	<label><?php esc_html_e( 'Position', 'formidable' ); ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name( 'position' ) ); ?>" id="frm_position">
		<?php foreach ( FrmStylesHelper::get_css_label_positions() as $pos => $pos_label ) { ?>
			<option value="<?php echo esc_attr( $pos ); ?>" <?php selected( $style->post_content['position'], $pos ); ?>><?php echo esc_html( $pos_label ); ?></option>
		<?php } ?>
	</select>
</p>
*/ ?>
<?php /*
<p class="frm4 frm_form_field">
	<label for="frm_align"><?php esc_html_e( 'Align', 'formidable' ); ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name( 'align' ) ); ?>" id="frm_align">
		<option value="left" <?php selected( $style->post_content['align'], 'left' ); ?>>
			<?php esc_html_e( 'left', 'formidable' ); ?>
		</option>
		<option value="right" <?php selected( $style->post_content['align'], 'right' ); ?>>
			<?php esc_html_e( 'right', 'formidable' ); ?>
		</option>
	</select>
</p>
*/ ?>
<?php /*
<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Width', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'width' ) ); ?>" id="frm_width" value="<?php echo esc_attr( $style->post_content['width'] ); ?>" />
</p>
*/?>
<?php /*
<p class="frm4 frm_first frm_form_field">
	<label><?php esc_html_e( 'Padding', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'label_padding' ) ); ?>" id="frm_label_padding" value="<?php echo esc_attr( $style->post_content['label_padding'] ); ?>" />
</p>

<h4 class="frm_clear">
	<span><?php esc_html_e( 'Required Indicator', 'formidable' ); ?></span>
</h4>

<p class="frm4 frm_first frm_form_field">
	<label class="background"><?php esc_html_e( 'Color', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'required_color' ) ); ?>" id="frm_required_color" class="hex" value="<?php echo esc_attr( $style->post_content['required_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'required_color' ); ?> />
</p>

<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name( 'required_weight' ) ); ?>" id="frm_required_weight">
		<?php foreach ( FrmStyle::get_bold_options() as $value => $name ) { ?>
		<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $style->post_content['required_weight'], $value ); ?>><?php echo esc_html( $name ); ?></option>
		<?php } ?>
	</select>
</p>
*/ ?>
