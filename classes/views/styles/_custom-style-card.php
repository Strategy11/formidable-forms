<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This view is used on the style page to render a single custom theme card.

$is_default_style = $style->ID === $default_style->ID;
$params           = FrmStylesHelper::get_params_for_style_card( $style, $default_style );

if ( $active_style->ID === $style->ID ) {
	$params['class'] .= ' frm_active_style_card';
}

$submit_button_styles = array(
	'font-size: ' . esc_attr( $default_style->post_content['submit_font_size'] ) . ' !important',
	'padding: ' . esc_attr( $default_style->post_content['submit_padding'] ) . ' !important',
);
$submit_button_params = array(
	'type'     => 'submit',
	'disabled' => 'disabled',
	'class'    => 'frm_full_opacity',
	'value'    => esc_attr__( 'Submit', 'formidable' ),
	'style'    => implode( ';', $submit_button_styles ),
);
?>
<div <?php FrmAppHelper::array_to_html_params( $params, true ); ?>>
	<div class="frm_style_card_preview">
		<div class="frm_form_field form-field" style="margin-bottom: <?php echo esc_attr( $default_style->post_content['field_margin'] ); ?>">
			<label class="frm_primary_label"><?php esc_html_e( 'Text field', 'formidable' ); ?>
			<input type="text" value="<?php esc_attr_e( 'This is sample text', 'formidable' ); ?>" />
		</div>
		<div class="frm_submit">
			<input <?php FrmAppHelper::array_to_html_params( $submit_button_params, true ); ?> />
		</div>
	</div>
	<div>
		<span class="frm_style_card_title"><?php echo esc_html( $style->post_title ); ?></span>
	</div>
</div>
