<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="misc-pub-section">
	<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_calendar_icon' ); ?>
	<span id="timestamp" class="frm_link_label">
		<?php
		printf(
			// translators: %s: Timestamp.
			esc_html__( 'Created on: %1$s', 'formidable' ),
			'<b>' . esc_html( FrmAppHelper::get_localized_date( $date_format, $subscription->created_at ) ) . '</b>'
		);
		?>
	</span>
</div>

<?php foreach ( $payments as $payment ) { ?>
	<div class="misc-pub-section">
		<?php
		FrmAppHelper::icon_by_class( 'frm_icon_font ' . ( $payment->status === 'complete' ? 'frm_check1_icon' : 'frm_x_icon' ) );
		?>
		<span class="frm_link_label">
			<?php echo esc_html( FrmTransLiteAppHelper::formatted_amount( $payment ) ); ?>
			<a href="?page=formidable-payments&action=show&id=<?php echo absint( $payment->id ); ?>">
				<?php echo esc_html( FrmAppHelper::get_localized_date( $date_format, $payment->created_at ) ); ?>
			</a>
		</span>
	</div>
<?php } ?>

<?php if ( $subscription->status === 'active' ) { ?>
	<div class="misc-pub-section">
		<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_x_icon' ); ?>
		<span class="frm_link_label">
			<?php
			FrmTransLiteSubscriptionsController::show_cancel_link(
				$subscription,
				array(
					'cancel' => __( 'Cancel Subscription', 'formidable' ),
				)
			);
			?>
		</span>
	</div>
<?php } ?>
