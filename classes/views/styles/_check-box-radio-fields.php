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
		$frm_style->get_field_name( 'check_label_color' ),
		$style->post_content['check_label_color'],
		array(
			'id'          => 'frm_check_label_color',
			'action_slug' => 'check_label_color',
		)
	); 
	?>
</div>

<div class="frm5 frm_form_field">
	<label class="frm-style-item-heading"><?php esc_html_e( 'Font Size', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'check_font_size' ),
		$style->post_content['check_font_size'],
		array(
			'id'        => 'frm_check_font_size',
			'max_value' => 100
		)
	); ?>
</div>

<div class="frm5 frm_form_field">
	<label class="frm-style-item-heading"><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php new FrmDropdownStyleComponent(
		$frm_style->get_field_name( 'check_weight' ),
		$style->post_content['check_weight'],
		array(
			'id'      => 'frm_check_weight',
			'options' => FrmStyle::get_bold_options(),
		)
	); ?>
</div>

<div class="frm5 frm_form_field">
	<label class="frm-style-item-heading"><?php esc_html_e( 'Check Box', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php new FrmDropdownStyleComponent(
		$frm_style->get_field_name( 'check_align' ),
		$style->post_content['check_align'],
		array(
			'id'      => 'frm_check_align',
			'options' => array(
				'block'  => esc_html__( 'Multiple Rows', 'formidable' ),
				'inline' => esc_html__( 'Single Row', 'formidable' ),
			),
		)
	); ?>
</div>

<div class="frm5 frm_form_field">
	<label class="frm-style-item-heading"><?php esc_html_e( 'Radio', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php new FrmDropdownStyleComponent(
		$frm_style->get_field_name( 'radio_align' ),
		$style->post_content['radio_align'],
		array(
			'id'      => 'frm_radio_align',
			'options' => array(
				'block'  => esc_html__( 'Multiple Rows', 'formidable' ),
				'inline' => esc_html__( 'Single Row', 'formidable' ),
			),
		)
	); ?>
</div>
<?php /*
<p class="frm6 frm_first frm_form_field">
	<label><?php esc_html_e( 'Radio', 'formidable' ); ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name( 'radio_align' ) ); ?>" id="frm_radio_align">
		<option value="block" <?php selected( $style->post_content['radio_align'], 'block' ); ?>>
			<?php esc_html_e( 'Multiple Rows', 'formidable' ); ?>
		</option>
		<option value="inline" <?php selected( $style->post_content['radio_align'], 'inline' ); ?>>
			<?php esc_html_e( 'Single Row', 'formidable' ); ?>
		</option>
	</select>
</p>

<p class="frm6 frm_form_field">
	<label><?php esc_html_e( 'Check Box', 'formidable' ); ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name( 'check_align' ) ); ?>" id="frm_check_align">
		<option value="block" <?php selected( $style->post_content['check_align'], 'block' ); ?>>
			<?php esc_html_e( 'Multiple Rows', 'formidable' ); ?>
		</option>
		<option value="inline" <?php selected( $style->post_content['check_align'], 'inline' ); ?>>
			<?php esc_html_e( 'Single Row', 'formidable' ); ?>
		</option>
	</select>
</p>

<p class="frm4 frm_first frm_form_field">
	<label><?php esc_html_e( 'Color', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'check_label_color' ) ); ?>" id="frm_check_label_color" class="hex" value="<?php echo esc_attr( $style->post_content['check_label_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'check_label_color' ); ?> />
</p>
<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name( 'check_weight' ) ); ?>" id="frm_check_weight">
		<?php foreach ( FrmStyle::get_bold_options() as $value => $name ) { ?>
		<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $style->post_content['check_weight'], $value ); ?>>
			<?php echo esc_html( $name ); ?>
		</option>
		<?php } ?>
	</select>
</p>
<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Size', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'check_font_size' ) ); ?>" id="frm_check_font_size" value="<?php echo esc_attr( $style->post_content['check_font_size'] ); ?>"  size="3" />
</p>
*/ ?>