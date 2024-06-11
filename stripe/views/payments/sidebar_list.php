<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="postbox frm_with_icons">
	<h3>
		<span><?php esc_html_e( 'Payment Details', 'formidable' ); ?></span>
	</h3>
	<div class="inside">
		<?php
		foreach ( $payments as $payment ) {
			if ( empty( $payment->status ) && ! empty( $payment->completed ) ) {
				// PayPal fallback.
				$payment->status = 'complete';
			}

			if ( $payment->status === 'complete' ) {
				$entry_total  += $payment->amount;
				$total_payment = $payment;
			}
			?>
			<div class="misc-pub-section">
				<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_calendar_icon' ); ?>
				<span>
					<?php esc_html_e( 'Created:', 'formidable' ); ?>
				</span>
				<span>
					<b><a href="?page=formidable-payments&action=show&id=<?php echo absint( $payment->id ); ?>" title="<?php esc_attr_e( 'Show Payment', 'formidable' ); ?>">
						<?php echo esc_html( FrmAppHelper::get_localized_date( $date_format, $payment->created_at ) ); ?>
					</a></b>
				</span>
			</div>
			<div class="misc-pub-section">
				<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_total_icon' ); ?>
				<span>
					<?php esc_html_e( 'Amount:', 'formidable' ); ?>
				</span>
				<span>
					<b><?php echo esc_html( FrmTransLiteAppHelper::formatted_amount( $payment ) ); ?></b>
				</span>
				<?php do_action( 'frm_pay_' . $payment->paysys . '_refund_link', $payment ); ?>
			</div>
			<div class="misc-pub-section">
				<?php
				FrmAppHelper::icon_by_class( 'frm_icon_font ' . ( $payment->status === 'complete' ? 'frm_check1_icon' : 'frm_x_icon' ) );
				?>
				<span>
					<?php esc_html_e( 'Status:', 'formidable' ); ?>
				</span>
				<span>
					<b><?php echo esc_html( FrmTransLiteAppHelper::show_status( $payment->status ) ); ?></b>
				</span>
			</div>
			<?php
		}//end foreach
		?>

		<?php foreach ( $subscriptions as $sub ) { ?>
			<div class="misc-pub-section">
				<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_repeater_icon' ); ?>
				<span>
					<a href="?page=formidable-payments&amp;action=show&amp;type=subscriptions&amp;id=<?php echo absint( $sub->id ); ?>">
						<?php esc_html_e( 'Subscription:', 'formidable' ); ?>
						<?php echo esc_html( FrmTransLiteAppHelper::format_billing_cycle( $sub ) ); ?>
					</a>
				</span>
			</div>

			<?php if ( $sub->status === 'active' ) { ?>
				<div class="misc-pub-section">
					<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_x_icon' ); ?>
					<?php
					FrmTransLiteSubscriptionsController::show_cancel_link(
						$sub,
						array(
							'cancel' => __( 'Cancel Subscription', 'formidable' ),
						)
					);
					?>
				</div>
			<?php } ?>
			<?php
		}//end foreach
		?>

		<?php
		if ( $entry_total ) {
			$total_payment->amount = $entry_total;
			?>
			<div class="misc-pub-section">
				<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_product_icon' ); ?>
				<span>
					<?php esc_html_e( 'Total Paid:', 'formidable' ); ?>
				</span>
				<span>
					<b><?php echo esc_html( FrmTransLiteAppHelper::formatted_amount( $total_payment ) ); ?></b>
				</span>
			</div>
		<?php } ?>
	</div>
</div>
