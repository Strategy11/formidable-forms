<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This view is used on the style page to render a single custom theme card.

$frm_style = new FrmStyle( 'default' );
$default_style = $frm_style->get_one();

$params = array(
	'class' => 'frm6 with_frm_style frm_style_' . $style->post_name,
	'style' => FrmStylesHelper::get_style_param_for_card( $style ),
);
?>
<div <?php FrmAppHelper::array_to_html_params( $params, true ); ?>>
	<div style="padding: 10px; flex: 1;">
		<div class="frm_form_field form-field" style="margin-bottom: <?php echo esc_attr( $default_style->post_content['field_margin'] ); ?>">
			<label class="frm_primary_label"><?php esc_html_e( 'Text field', 'formidable' ); ?>
			<input type="text" value="<?php esc_attr_e( 'This is sample text', 'formidable' ); ?>" />
		</div>
		<div class="frm_submit">
			<input type="submit" disabled="disabled" class="frm_full_opacity" value="<?php esc_attr_e( 'Submit', 'formidable' ); ?>" style="margin: 0; max-width: 100%; font-size: <?php echo esc_attr( $default_style->post_content['submit_font_size'] ); ?> !important; height: unset !important; padding: <?php echo esc_attr( $default_style->post_content['submit_padding'] ); ?> !important;" />
		</div>
	</div>
	<div style="border-top: 1px solid #E9EAEA; padding: 10px; color: var(--label-color);">
		<?php echo esc_html( $style->post_title ); ?>
	</div>
</div>
