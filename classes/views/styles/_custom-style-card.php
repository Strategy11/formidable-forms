<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
// This view is used on the style page to render a single custom theme card.

$params = array(
	'class' => 'frm6 with_frm_style frm_style_' . $style->post_name,
	'style' => FrmStylesHelper::get_style_param_for_card( $style ),
);
?>
<div <?php FrmAppHelper::array_to_html_params( $params, true ); ?>>
	<div class="frm_form_field form-field">
		<?php /* <img style="max-width: 100%; border-radius: 6px;" src="<?php echo esc_url( $style['icon'][0] ); ?>" /> */ ?>

		<label class="frm_primary_label"><?php esc_html_e( 'Text field', 'formidable' ); ?> <span class="frm_required">*</span></label>
		<input type="text" value="<?php esc_attr_e( 'This is sample text', 'formidable' ); ?>"/>
		<?php
			/**
			 * It really just shows a few basic styles so maybe we can just style it on the fly:
			 *    - Text input styles (font size, color, border, background color, etc)
			 *    - Submit button styles (color, font size, background color, border?)
			 */
		?>
		<?php echo $style->post_title; ?>
	</div>
	<div class="frm_submit">
		<input type="submit" disabled="disabled" class="frm_full_opacity" value="<?php esc_attr_e( 'Submit', 'formidable' ); ?>" />
	</div>
</div>
