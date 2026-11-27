<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$wrapper_atts = array_merge(
	array(
		'class'        => 'show_stripe frm_gateway_no_recur frm6 frm_show_upgrade frm_noallow',
		'data-upgrade' => __( 'Additional Stripe settings', 'formidable' ),
	),
	$upgrade_params
);

if ( isset( $selected_gateway ) && 'stripe' !== $selected_gateway ) {
	$wrapper_atts['style'] = 'display: none;';
}
?>
<p <?php FrmAppHelper::array_to_html_params( $wrapper_atts, true ); ?>>
	<label>
		<?php esc_html_e( 'Capture Payment', 'formidable' ); ?>
	</label>
	<select disabled style="pointer-events: none;">
		<option><?php esc_html_e( 'When entry is submitted', 'formidable' ); ?></option>
	</select>
</p>
