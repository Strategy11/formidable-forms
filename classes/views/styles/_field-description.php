<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm5 frm_form_field">
	<label class="frm-style-item-heading"><?php esc_html_e( 'Color', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php new FrmColorpickerStyleComponent(
		$frm_style->get_field_name( 'description_color' ),
		$style->post_content['description_color'],
		array(
			'id'          => 'frm_description_color',
			'action_slug' => 'description_color'
		)
	); 
	?>
</div>

<div class="frm5 frm_form_field">
	<label class="frm-style-item-heading"><?php esc_html_e( 'Font Size', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'description_font_size' ),
		(int) $style->post_content['description_font_size'],
		array( 'id' => 'frm_description_align' )
	); ?>
</div>

<div class="frm5 frm_form_field">
	<label class="frm-style-item-heading"><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php new FrmDropdownStyleComponent(
		$frm_style->get_field_name( 'description_weight' ),
		$style->post_content['description_weight'],
		array(
			'id'      => 'frm_description_weight',
			'options' => FrmStyle::get_bold_options(),
		)
	); ?>
</div>

<div class="frm5 frm_form_field">
	<label class="frm-style-item-heading"><?php esc_html_e( 'Style', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php new FrmDropdownStyleComponent(
		$frm_style->get_field_name( 'description_style' ),
		$style->post_content['description_style'],
		array(
			'id'      => 'frm_description_weight',
			'options' => array(
				'normal' => esc_html__( 'normal', 'formidable' ),
				'italic' => esc_html__( 'italic', 'formidable' ),
			),
		)
	); ?>
</div>

<div class="frm5 frm_form_field">
	<label class="frm-style-item-heading"><?php esc_html_e( 'Align', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php new FrmAlignStyleComponent(
		$frm_style->get_field_name( 'description_align' ),
		$style->post_content['description_align'],
		array(
			'options' => array( 'left', 'right' )
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label class="frm-style-item-heading"><?php esc_html_e( 'Margin', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'description_margin' ),
		$style->post_content['description_margin'],
		array( 'id' => 'frm_description_margin' )
	); ?>
</div>

<?php /*
<p class="frm4 frm_first frm_form_field">
	<label><?php esc_html_e( 'Color', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'description_color' ) ); ?>" id="frm_description_color" class="hex" value="<?php echo esc_attr( $style->post_content['description_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'description_color' ); ?> />
</p>
<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name( 'description_weight' ) ); ?>" id="frm_description_weight">
		<?php foreach ( FrmStyle::get_bold_options() as $value => $name ) { ?>
		<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $style->post_content['description_weight'], $value ); ?>><?php echo esc_html( $name ); ?></option>
		<?php } ?>
	</select>
</p>
<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Style', 'formidable' ); ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name( 'description_style' ) ); ?>" id="frm_description_style">
		<option value="normal" <?php selected( $style->post_content['description_style'], 'normal' ); ?>>
			<?php esc_html_e( 'normal', 'formidable' ); ?>
		</option>
		<option value="italic" <?php selected( $style->post_content['description_style'], 'italic' ); ?>>
			<?php esc_html_e( 'italic', 'formidable' ); ?>
		</option>
	</select>
</p>

<p class="frm4 frm_first frm_form_field">
	<label><?php esc_html_e( 'Size', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'description_font_size' ) ); ?>" id="frm_description_font_size" value="<?php echo esc_attr( $style->post_content['description_font_size'] ); ?>"  size="3" />
</p>
<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Align', 'formidable' ); ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name( 'description_align' ) ); ?>" id="frm_description_align">
		<option value="left" <?php selected( $style->post_content['description_align'], 'left' ); ?>>
			<?php esc_html_e( 'left', 'formidable' ); ?>
		</option>
		<option value="right" <?php selected( $style->post_content['description_align'], 'right' ); ?>>
			<?php esc_html_e( 'right', 'formidable' ); ?>
		</option>
	</select>
</p>
<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Margin', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'description_margin' ) ); ?>" id="frm_description_margin" value="<?php echo esc_attr( $style->post_content['description_margin'] ); ?>"  size="3" />
</p>
*/ ?>