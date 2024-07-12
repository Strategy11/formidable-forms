<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="frm5 frm_form_field">
	<label class="frm-style-item-heading"><?php esc_html_e( 'Font Size', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'field_font_size' ),
		$style->post_content['field_font_size'],
		array(
			'id'        => 'frm_field_font_size',
			'max_value' => 100,
		)
	); ?>
</div>

<div class="frm5 frm_form_field">
	<label class="frm-style-item-heading"><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php new FrmDropdownStyleComponent(
		$frm_style->get_field_name( 'field_weight' ),
		$style->post_content['field_weight'],
		array(
			'id'      => 'frm_field_weight',
			'options' => FrmStyle::get_bold_options(),
		)
	); ?>
</div>

<div class="frm5 frm_form_field">
	<label class="frm-style-item-heading"><?php esc_html_e( 'Height', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'field_height' ),
		$style->post_content['field_height'],
		array(
			'id'        => 'frm_field_height',
			'max_value' => 100,
		)
	); ?>
</div>
<div class="frm5 frm_form_field">
	<label class="frm-style-item-heading"><?php esc_html_e( 'Width', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'field_width' ),
		$style->post_content['field_width'],
		array(
			'id'        => 'frm_field_width',
			'max_value' => 300,
		)
	); ?>
</div>
<div class="frm5 frm_form_field">
	<label class="frm-style-item-heading"><?php esc_html_e( 'Padding', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'field_pad' ),
		$style->post_content['field_pad'],
		array(
			'id'        => 'frm_field_pad',
			'type'		=> 'vertical-margin',
			'max_value' => 100,
		)
	); ?>
</div>
<div class="frm5 frm_form_field">
	<label class="frm-style-item-heading"><?php esc_html_e( 'Margin', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'field_margin' ),
		$style->post_content['field_margin'],
		array(
			'id'        => 'frm_field_margin',
			'type'		=> 'vertical-margin',
			'max_value' => 100,
		)
	); ?>
</div>
<div class="frm5 frm_form_field">
	<label class="frm-style-item-heading"><?php esc_html_e( 'Corner Radius', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'border_radius' ),
		$style->post_content['border_radius'],
		array(
			'id' => 'frm_border_radius',
			'max_value' => 50,
		)
	); ?>
</div>

<div class="frm5 frm_form_field">
	<label class="frm-style-item-heading"><?php esc_html_e( 'Auto Drop-downs Width', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
		FrmHtmlHelper::toggle(
			'auto_width',
			$frm_style->get_field_name( 'auto_width' ),
			array(
				'div_class' => 'with_frm_style frm_toggle',
				'checked'   => $style->post_content['auto_width'],
				'echo'      => true,
			)
	); ?>
</div>

<?php /*
<p class="frm4 frm_first frm_form_field">
	<label><?php esc_html_e( 'Size', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'field_font_size' ) ); ?>" id="frm_field_font_size" value="<?php echo esc_attr( $style->post_content['field_font_size'] ); ?>" />
</p>

<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Height', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'field_height' ) ); ?>" id="frm_field_height" value="<?php echo esc_attr( $style->post_content['field_height'] ); ?>" />
</p>

<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Width', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'field_width' ) ); ?>" id="frm_field_width" value="<?php echo esc_attr( $style->post_content['field_width'] ); ?>" />
</p>

<p class="frm_clear frm_no_bottom_margin">
	<label><input type="checkbox" name="<?php echo esc_attr( $frm_style->get_field_name( 'auto_width' ) ); ?>" id="frm_auto_width" value="1" <?php checked( $style->post_content['auto_width'], 1 ); ?> />
	<?php esc_html_e( 'Automatic width for drop-down fields', 'formidable' ); ?></label>
</p>

<p class="frm4 frm_first frm_form_field">
	<label><?php esc_html_e( 'Padding', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'field_pad' ) ); ?>" id="frm_field_pad" value="<?php echo esc_attr( $style->post_content['field_pad'] ); ?>" />
</p>

<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Bottom Margin', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'field_margin' ) ); ?>" id="frm_field_margin" value="<?php echo esc_attr( $style->post_content['field_margin'] ); ?>" />
</p>

<p class="frm4 frm_form_field">
	<label><?php esc_html_e( 'Corners', 'formidable' ); ?> <span class="frm_help frm_icon_font frm_tooltip_icon" title="<?php esc_attr_e( 'Formidable uses CSS3 border-radius for corner rounding, which is not currently supported by Internet Explorer.', 'formidable' ); ?>" ></span></label>
	<input type="text" value="<?php echo esc_attr( $style->post_content['border_radius'] ); ?>" name="<?php echo esc_attr( $frm_style->get_field_name( 'border_radius' ) ); ?>" id="frm_border_radius" />
</p>

<p class="frm4 frm_first frm_form_field">
	<label><?php esc_html_e( 'Weight', 'formidable' ); ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name( 'field_weight' ) ); ?>" id="frm_field_weight">
		<?php foreach ( FrmStyle::get_bold_options() as $value => $name ) { ?>
			<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $style->post_content['field_weight'], $value ); ?>><?php echo esc_html( $name ); ?></option>
		<?php } ?>
	</select>
</p>
*/ ?>