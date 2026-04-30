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

<?php
$debug_entries    = get_option( 'frm_paypal_debug_ids', array() );
$two_days_ago     = gmdate( 'Y-m-d H:i:s', time() - ( 2 * DAY_IN_SECONDS ) );
$recent_entries   = array_filter(
	is_array( $debug_entries ) ? $debug_entries : array(),
	function ( $entry ) use ( $two_days_ago ) {
		return ! empty( $entry['timestamp'] ) && $entry['timestamp'] >= $two_days_ago;
	}
);

if ( $recent_entries ) {
	?>
	<div style="margin-top: var(--gap-lg);">
		<h3><?php esc_html_e( 'Recent PayPal Debug IDs', 'formidable' ); ?></h3>
		<p class="frm-description">
			<?php esc_html_e( 'These debug IDs can be provided to PayPal support when troubleshooting payment issues.', 'formidable' ); ?>
		</p>
		<table class="widefat striped" style="max-width: 800px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Debug ID', 'formidable' ); ?></th>
					<th><?php esc_html_e( 'Error', 'formidable' ); ?></th>
					<th><?php esc_html_e( 'Context', 'formidable' ); ?></th>
					<th><?php esc_html_e( 'Time', 'formidable' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $recent_entries as $entry ) { ?>
					<tr>
						<td><code><?php echo esc_html( $entry['debug_id'] ); ?></code></td>
						<td><?php echo esc_html( $entry['error_message'] ); ?></td>
						<td><?php echo esc_html( $entry['context'] ); ?></td>
						<td><?php echo esc_html( $entry['timestamp'] ); ?></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
	<?php
}
?>
