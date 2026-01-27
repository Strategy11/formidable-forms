<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

?>
<table class="form-table" style="width: 400px;">
	<tr class="form-field">
		<td>
			<?php esc_html_e( 'Test Mode', 'formidable' ); ?>
		</td>
		<td>
			<label>
				<input type="checkbox" name="frm_paypal_test_mode" id="frm_paypal_test_mode" value="1" <?php checked( $settings->settings->test_mode, 1 ); ?> />
				<?php esc_html_e( 'Use the PayPal test mode', 'formidable' ); ?>
			</label>
		</td>
	</tr>
</table>

<div>
	<div class="frm_grid_container">
		<?php

		$modes = array( 'live', 'test' );

		foreach ( $modes as $mode ) {
			FrmPayPalLiteConnectHelper::render_settings_for_mode( $mode );
		}
		?>
	</div>
</div>
<?php if ( ! is_ssl() ) { ?>
	<div>
		<em>
	<?php esc_html_e( 'Your site is not using SSL. Before using PayPal to collect payments, you will need to install an SSL certificate on your site.', 'formidable' ); // phpcs:ignore SlevomatCodingStandard.Files.LineLength.LineTooLong ?>
		</em>
	</div>
<?php } ?>
