<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

FrmStrpLiteAppHelper::fee_education( 'settings' );
?>
<table class="form-table">
	<tr class="form-field">
		<td>
			<?php esc_html_e( 'Test Mode', 'formidable' ); ?>
		</td>
		<td>
			<label for="frm_strp_test_mode">
				<input type="checkbox" name="frm_strp_test_mode" id="frm_strp_test_mode" value="1" <?php checked( $settings->settings->test_mode, 1 ); ?> />
				<?php esc_html_e( 'Use the Stripe test mode', 'formidable' ); ?>
			</label>
			<?php if ( ! is_ssl() ) { ?>
				<br/><em><?php esc_html_e( 'Your site is not using SSL. Before using Stripe to collect live payments, you will need to install an SSL certificate on your site.', 'formidable' ); ?></em>
			<?php } ?>
		</td>
	</tr>

	<?php FrmStrpLiteConnectHelper::render_stripe_connect_settings_container(); ?>
</table>
