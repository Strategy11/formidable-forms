<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>

<div class="misc-pub-section">
	<?php FrmAppHelper::icon_by_class( 'frmfont frm_calendar_icon' ); ?>
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

<?php if ( isset( $payment->status ) && $payment->status === 'complete' && ! empty( $payment->receipt_id ) ) { ?>
	<div class="misc-pub-section">
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_product_icon' ); ?>
		<?php esc_html_e( 'Payment:', 'formidable' ); ?>
		<?php FrmTransLitePaymentsController::show_refund_link( $payment ); ?>
	</div>
<?php } ?>

<?php
do_action( 'frm_pay_' . $payment->paysys . '_sidebar', $payment );
?>