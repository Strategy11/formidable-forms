<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm5 frm_form_field">
	<label class="frm-style-item-heading"><?php esc_html_e( 'Font Family', 'formidable' ); ?></label>
</p>
<p class="frm7 frm_form_field">
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'font' ) ); ?>" id="frm_font" value="<?php echo esc_attr( $style->post_content['font'] ); ?>"  placeholder="<?php esc_attr_e( 'Inherit from theme', 'formidable' ); ?>" class="frm_full_width" />
</p>

<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Background', 'formidable' ); ?></label></div>
<div class="frm7 frm_form_field">
	<?php new FrmBackgroundImageStyleComponent(
		$frm_style->get_field_name( 'fieldset_bg_color' ),
		$style->post_content['fieldset_bg_color'],
		array(
			'id'                  => 'frm_fieldset_bg_color',
			'frm_style'           => $frm_style,
			'style'               => $style,
			'action_slug'         => 'fieldset_bg_color',
			'image_id_input_name' => 'bg_image_id'
		)
	);
	?>
</div>

<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Alignment', 'formidable' ); ?></label></div>
<div class="frm7 frm_form_field">
	<?php new FrmAlignStyleComponent(
		$frm_style->get_field_name( 'form_align' ),
		$style->post_content['form_align'],
		array(),
	);
	?>
</div>

<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Border Color', 'formidable' ); ?></label></div>
<div class="frm7 frm_form_field">
	<?php new FrmColorpickerStyleComponent(
		$frm_style->get_field_name( 'fieldset_color' ),
		$style->post_content['fieldset_color'],
		array(
			'id'          => 'frm_fieldset_color',
			'action_slug' => 'fieldset_color',
		)
	); 
	?>
</div>

<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Border Width', 'formidable' ); ?></label></div>
<div class="frm7 frm_form_field">
	<?php new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'fieldset' ),
		$style->post_content['fieldset'],
		array(
			'id'        => 'frm_fieldset',
			'max_value' => 25,
		)
	); ?>
</div>

<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Padding', 'formidable' ); ?></label></div>
<div class="frm7 frm_form_field">
	<?php new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'fieldset_padding' ),
		$style->post_content['fieldset_padding'],
		array(
			'id'        => 'frm_fieldset_padding',
			'type'		=> 'vertical-margin',
			'max_value' => 100,
		)
	); ?>
</div>

<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Form Width', 'formidable' ); ?></label></div>
<div class="frm7 frm_form_field">
	<?php new FrmSliderStyleComponent(
		$frm_style->get_field_name( 'form_width' ),
		$style->post_content['form_width'],
		array(
			'id'        => 'frm_form_width',
			'max_value' => 2000,
		)
	); ?>
</div>

<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Direction', 'formidable' ); ?></label></div>
<div class="frm7 frm_form_field">
	<?php new FrmDirectionStyleComponent(
		$frm_style->get_field_name( 'direction' ),
		$style->post_content['direction'],
		array()
	) ?>
</div>

<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Override Theme', 'formidable' ); ?></label></div>
<div class="frm7 frm_form_field frm-style-component">
	<?php
		FrmHtmlHelper::toggle(
			'frm_important_style',
			$frm_style->get_field_name( 'important_style' ),
			array(
				'div_class' => 'with_frm_style frm_toggle',
				'checked'   => ! empty( $style->post_content['important_style'] ),
				'echo'      => true,
			)
	); ?>
</div>

<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Center Form', 'formidable' ); ?></label></div>
<div class="frm7 frm_form_field frm-style-component">
	<?php
		FrmHtmlHelper::toggle(
			'frm_center_form',
			$frm_style->get_field_name( 'center_form' ),
			array(
				'div_class' => 'with_frm_style frm_toggle',
				'checked'   => ! empty( $style->post_content['important_style'] ),
				'echo'      => true,
			)
	); ?>
</div>
<div class="frm5 frm_form_field"><label class="frm-style-item-heading"><?php esc_html_e( 'Style Class', 'formidable' ); ?></label></div>
<div class="frm7 frm_form_field frm-style-component">
	<label class="frm-copy-text">.frm_style_<?php echo esc_html( $style->post_name ); FrmAppHelper::icon_by_class( 'frm_icon_font frm-copy-icon' ) ?></label>
</div>

<?php /*
<p class="frm6 frm_first frm_form_field">
	<label class="frm_help" title="<?php esc_attr_e( 'This will add !important to many of the lines in the Formidable styling to make sure it will be used.', 'formidable' ); ?>">
		<input type="checkbox" name="<?php echo esc_attr( $frm_style->get_field_name( 'important_style' ) ); ?>" id="frm_important_style" value="1" <?php checked( $style->post_content['important_style'], 1 ); ?> />
		<?php esc_html_e( 'Override theme styling', 'formidable' ); ?>
	</label>
</p>

<p class="frm6 frm_form_field">
	<label class="frm_help" title="<?php esc_attr_e( 'This will center your form on the page where it is published if the form width is less than the available width on the page.', 'formidable' ); ?>">
		<input type="checkbox" name="<?php echo esc_attr( $frm_style->get_field_name( 'center_form' ) ); ?>" id="frm_center_form" value="1" <?php checked( $style->post_content['center_form'], 1 ); ?> />
		<?php esc_html_e( 'Center form on page', 'formidable' ); ?>
	</label>
</p>

<p class="frm4 frm_first frm_form_field">
	<label for="frm_form_align"><?php esc_html_e( 'Alignment', 'formidable' ); ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name( 'form_align' ) ); ?>" id="frm_form_align">
		<option value="left" <?php selected( $style->post_content['form_align'], 'left' ); ?>>
			<?php esc_html_e( 'left', 'formidable' ); ?>
		</option>
		<option value="right" <?php selected( $style->post_content['form_align'], 'right' ); ?>>
			<?php esc_html_e( 'right', 'formidable' ); ?>
		</option>
		<option value="center" <?php selected( $style->post_content['form_align'], 'center' ); ?>>
			<?php esc_html_e( 'center', 'formidable' ); ?>
		</option>
	</select>
</p>

<p class="frm4 frm_form_field">
	<label for="frm_form_width"><?php esc_html_e( 'Max Width', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'form_width' ) ); ?>" id="frm_form_width" value="<?php echo esc_attr( $style->post_content['form_width'] ); ?>" />
</p>
*/ ?>
<?php /*
<p class="frm4 frm_form_field frm_end">
	<label for="frm_fieldset_bg_color"><?php esc_html_e( 'Background', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'fieldset_bg_color' ) ); ?>" id="frm_fieldset_bg_color" class="hex" value="<?php echo esc_attr( $style->post_content['fieldset_bg_color'] ); ?>" size="4" <?php do_action( 'frm_style_settings_input_atts', 'fieldset_bg_color' ); ?> />
</p>
*/?>
<?php /*
do_action( 'frm_style_settings_general_section_after_background', compact( 'frm_style', 'style' ) );
if ( ! FrmAppHelper::pro_is_installed() ) {
	?>
		<div class="frm_image_preview_wrapper" data-upgrade="<?php esc_attr_e( 'Background image styles', 'formidable' ); ?>" data-medium="background-image">
			<button type="button" class="frm_choose_image_box frm_button frm_no_style_button frm_noallow">
				<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_upload_icon' ); ?>
				<?php esc_attr_e( 'Upload background image', 'formidable' ); ?>
			</button>
		</div>
	<?php
} */
?>
<?php /*
<p class="frm4 frm_first frm_form_field">
	<label for="frm_fieldset"><?php esc_html_e( 'Border', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'fieldset' ) ); ?>" id="frm_fieldset" value="<?php echo esc_attr( $style->post_content['fieldset'] ); ?>" size="4" />
</p>
*/?>
<?php /*
<p class="frm4 frm_form_field">
	<label for="frm_fieldset_color"><?php esc_html_e( 'Color', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'fieldset_color' ) ); ?>" id="frm_fieldset_color" class="hex" value="<?php echo esc_attr( $style->post_content['fieldset_color'] ); ?>" <?php do_action( 'frm_style_settings_input_atts', 'fieldset_color' ); ?> />
</p>
*/?>
<?php /*
<p class="frm4 frm_form_field">
	<label for="frm_fieldset_padding"><?php esc_html_e( 'Padding', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'fieldset_padding' ) ); ?>" id="frm_fieldset_padding" value="<?php echo esc_attr( $style->post_content['fieldset_padding'] ); ?>" size="4" />
</p>*/?>
<?php /*
<p>
	<label for="frm_font"><?php esc_html_e( 'Font Family', 'formidable' ); ?></label>
	<input type="text" name="<?php echo esc_attr( $frm_style->get_field_name( 'font' ) ); ?>" id="frm_font" value="<?php echo esc_attr( $style->post_content['font'] ); ?>"  placeholder="<?php esc_attr_e( 'Leave blank to inherit from theme', 'formidable' ); ?>" class="frm_full_width" />
</p>
*/?>
<?php /*
<p class="frm6 frm_first frm_form_field">
	<label for="frm_direction"><?php esc_html_e( 'Direction', 'formidable' ); ?></label>
	<select name="<?php echo esc_attr( $frm_style->get_field_name( 'direction' ) ); ?>" id="frm_direction">
		<option value="ltr" <?php selected( $style->post_content['direction'], 'ltr' ); ?>>
			<?php esc_html_e( 'Left to Right', 'formidable' ); ?>
		</option>
		<option value="rtl" <?php selected( $style->post_content['direction'], 'rtl' ); ?>>
			<?php esc_html_e( 'Right to Left', 'formidable' ); ?>
		</option>
	</select>
</p>
*/?>
<?php /*
<p>
	<label><?php esc_html_e( 'Style Class', 'formidable' ); ?></label>
	<span>.frm_style_<?php echo esc_html( $style->post_name ); ?></span>
</p>
*/?>
