<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This view is used on the style page to render a single custom theme card.

$class_name = 'frm_style_' . $style->post_name;
$params     = array(
	'class'          => 'frm6 with_frm_style frm_style_card ' . $class_name,
	'style'          => FrmStylesHelper::get_style_param_for_card( $style ),
	'data-classname' => $class_name,
);

if ( $active_style->ID === $style->ID ) {
	$params['class'] .= ' frm_active_style_card';
}
?>
<div <?php FrmAppHelper::array_to_html_params( $params, true ); ?>>
	<div class="frm_style_card_preview">
		<div class="frm_form_field form-field" style="margin-bottom: <?php echo esc_attr( $default_style->post_content['field_margin'] ); ?>">
			<label class="frm_primary_label"><?php esc_html_e( 'Text field', 'formidable' ); ?>
			<input type="text" value="<?php esc_attr_e( 'This is sample text', 'formidable' ); ?>" />
		</div>
		<div class="frm_submit">
			<input type="submit" disabled="disabled" class="frm_full_opacity" value="<?php esc_attr_e( 'Submit', 'formidable' ); ?>" style="font-size: <?php echo esc_attr( $default_style->post_content['submit_font_size'] ); ?> !important; padding: <?php echo esc_attr( $default_style->post_content['submit_padding'] ); ?> !important;" />
		</div>
	</div>
	<div>
		<?php echo esc_html( $style->post_title ); ?>
	</div>
</div>
