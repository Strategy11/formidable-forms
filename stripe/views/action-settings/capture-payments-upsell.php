<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<p class="frm_gateway_no_recur frm6 frm_show_upgrade frm_noallow" data-upgrade="<?php esc_attr_e( 'Additional Stripe settings', 'formidable' ); ?>" <?php FrmAppHelper::array_to_html_params( $upgrade_params, true ); ?>>
	<label>
		<?php esc_html_e( 'Capture Payment', 'formidable' ); ?>
	</label>
	<select disabled style="pointer-events: none;">
		<option><?php esc_html_e( 'When entry is submitted', 'formidable' ); ?></option>
	</select>
</p>
