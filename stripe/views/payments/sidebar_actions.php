<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div id="misc-publishing-actions">
	<div class="misc-pub-section curtime misc-pub-curtime">
		<span id="timestamp">
			<?php
			printf(
				// translators: %s: Timestamp.
				esc_html__( 'Created on: %1$s', 'formidable' ),
				'<b>' . esc_html( $created_at ) . '</b>'
			);
			?>
		</span>
	</div>

	<?php if ( $payment->status === 'complete' && ! empty( $payment->receipt_id ) ) { ?>
		<div class="misc-pub-section">
			<span class="dashicons dashicons-cart wp-media-buttons-icon"></span>
			<?php esc_html_e( 'Payment:', 'formidable' ); ?>
			<?php FrmTransLitePaymentsController::show_refund_link( $payment ); ?>
		</div>
	<?php } ?>

</div>
