<?php
/**
 * Render the color control before its picker script initializes.
 *
 * @package Formidable
 *
 * @since x.x
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$input_attrs = array(
	'type'   => 'text',
	'id'     => $component['id'],
	'class'  => 'hex',
	'value'  => $field_value,
	'hidden' => 'hidden',
);

if ( $field_name ) {
	$input_attrs['name'] = substr( $field_name, 5 );
}
$color_label  = $field_value ? $field_value : __( 'Select Color', 'formidable' );
$swatch_color = $field_value ? $field_value : 'transparent';
?>
<span class="frm-block-color-picker">
	<button type="button" id="<?php echo esc_attr( $component['id'] ); ?>_visible" class="frm-block-color-toggle" aria-expanded="false" disabled>
		<span class="frm-block-color-swatch" aria-hidden="true">
			<svg viewBox="0 0 20 20" focusable="false"><rect width="20" height="20" fill="<?php echo esc_attr( $swatch_color ); ?>" /></svg>
		</span>
		<span class="frm-block-color-value"><?php echo esc_html( $color_label ); ?></span>
	</button>
	<input<?php FrmAppHelper::array_to_html_params( $input_attrs, true ); ?> <?php do_action( 'frm_style_settings_input_atts', $component['action_slug'] ); ?> />
</span>
