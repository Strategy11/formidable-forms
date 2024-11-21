<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm5 frm_form_field">
	<label 
		for="frm_font"
		class="frm-style-item-heading"><?php esc_html_e( 'Font Family', 'formidable' ); ?></label>
</p>
<p class="frm7 frm_form_field">
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'font' ) ); ?>" id="frm_font" value="<?php echo esc_attr( $style->post_content['font'] ); ?>"  placeholder="<?php esc_attr_e( 'Inherit from theme', 'formidable' ); ?>" class="frm_full_width" />
</p>

<?php
new FrmBackgroundImageStyleComponent(
	$frm_style->get_field_name( 'fieldset_bg_color' ),
	$style->post_content['fieldset_bg_color'],
	array(
		'title'                       => __( 'Background', 'formidable' ),
		'id'                          => 'frm_fieldset_bg_color',
		'frm_style'                   => $frm_style,
		'style'                       => $style,
		'action_slug'                 => 'fieldset_bg_color',
		'image_id_input_name'         => 'bg_image_id',
		'include_additional_settings' => true,
	)
);
?>

<div class="frm5 frm_form_field">
	<label 
		for="frm_form_align"
		class="frm-style-item-heading"><?php esc_html_e( 'Alignment', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmAlignStyleComponent(
		$frm_style->get_field_name( 'form_align' ),
		$style->post_content['form_align'],
		array(
			'id' => 'frm_form_align',
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_fieldset_color"
		class="frm-style-item-heading"><?php esc_html_e( 'Border Color', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmColorpickerStyleComponent(
		$frm_style->get_field_name( 'fieldset_color' ),
		$style->post_content['fieldset_color'],
		array(
			'id'          => 'frm_fieldset_color',
			'action_slug' => 'fieldset_color',
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_fieldset"
		class="frm-style-item-heading"><?php esc_html_e( 'Border Width', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'fieldset' ),
		$style->post_content['fieldset'],
		array(
			'id'        => 'frm_fieldset',
			'max_value' => 25,
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_fieldset_padding"
		class="frm-style-item-heading"><?php esc_html_e( 'Padding', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'fieldset_padding' ),
		$style->post_content['fieldset_padding'],
		array(
			'id'        => 'frm_fieldset_padding',
			'type'      => 'vertical-margin',
			'max_value' => 100,
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_form_width"
		class="frm-style-item-heading"><?php esc_html_e( 'Form Width', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'form_width' ),
		$style->post_content['form_width'],
		array(
			'id'        => 'frm_form_width',
			'max_value' => 2000,
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_direction"
		class="frm-style-item-heading"><?php esc_html_e( 'Direction', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field">
	<?php
	new FrmDirectionStyleComponent(
		$frm_style->get_field_name( 'direction' ),
		$style->post_content['direction'],
		array(
			'id' => 'frm_direction',
		)
	);
	?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_important_style"
		class="frm-style-item-heading">
		<?php esc_html_e( 'Override Theme', 'formidable' ); ?>
		<?php FrmAppHelper::tooltip_icon( __( 'This will add !important to many of the lines in the Formidable styling to make sure it will be used', 'formidable' ) ); ?>
	</label>
</div>
<div class="frm7 frm_form_field frm-style-component">
	<?php
		FrmHtmlHelper::toggle(
			'frm_important_style',
			$frm_style->get_field_name( 'important_style' ),
			array(
				'div_class'       => 'with_frm_style frm_toggle',
				'checked'         => ! empty( $style->post_content['important_style'] ),
				'echo'            => true,
				'aria-label-attr' => __( 'Override Theme', 'formidable' ),
			)
		);
		?>
</div>

<div class="frm5 frm_form_field">
	<label 
		for="frm_center_form"
		class="frm-style-item-heading"><?php esc_html_e( 'Center Form', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field frm-style-component">
	<?php
		FrmHtmlHelper::toggle(
			'frm_center_form',
			$frm_style->get_field_name( 'center_form' ),
			array(
				'div_class'       => 'with_frm_style frm_toggle',
				'checked'         => ! empty( $style->post_content['center_form'] ),
				'echo'            => true,
				'aria-label-attr' => __( 'Center Form', 'formidable' ),
			)
		);
		?>
</div>
<div class="frm5 frm_form_field">
	<label 
		for="frm_style_class"
		class="frm-style-item-heading"><?php esc_html_e( 'Style Class', 'formidable' ); ?></label>
</div>
<div class="frm7 frm_form_field frm-style-component">
	<label class="frm-copy-text">.frm_style_<?php
		echo esc_html( $style->post_name );
		FrmAppHelper::icon_by_class( 'frm_icon_font frm-copy-icon' );
	?>
	</label>
</div>
