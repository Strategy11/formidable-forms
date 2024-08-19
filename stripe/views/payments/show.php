<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

?>
<div class="frm_wrap">
	<div>
		<?php
		FrmAppHelper::get_admin_header(
			array(
				'label'      => __( 'View Payment', 'formidable' ),
				'form'       => $form_id,
				'hide_title' => true,
			)
		);
		?>

		<div class="columns-2">

		<div id="post-body-content" class="frm-fields">

			<div class="wrap frm-with-margin frm_form_fields">
				<div class="postbox">

					<h3 class="hndle">
						<span><?php esc_html_e( 'Payment', 'formidable' ); ?></span>
					</h3>

					<table class="frm-alt-table">
						<tbody>
							<tr>
								<th scope="row"><?php esc_html_e( 'Status', 'formidable' ); ?>:</th>
								<td><?php echo esc_html( FrmTransLiteAppHelper::show_status( FrmTransLiteAppHelper::get_payment_status( $payment ) ) ); ?></td>
							</tr>

							<tr>
								<th scope="row"><?php esc_html_e( 'User', 'formidable' ); ?>:</th>
								<td>
									<?php echo wp_kses_post( $user_name ); ?>
								</td>
							</tr>

							<tr>
								<th scope="row"><?php esc_html_e( 'Entry', 'formidable' ); ?>:</th>
								<td>
									<?php FrmTransLitePaymentsController::show_entry_link( $payment ); ?>
								</td>
							</tr>

							<?php if ( ! empty( $payment->receipt_id ) ) { ?>
							<tr>
								<th scope="row"><?php esc_html_e( 'Receipt', 'formidable' ); ?>:</th>
								<td>
									<?php FrmTransLitePaymentsController::show_receipt_link( $payment ); ?>
								</td>
							</tr>
							<?php } ?>

							<?php FrmTransLiteAppHelper::show_in_table( $payment->invoice_id, __( 'Invoice #', 'formidable' ) ); ?>

							<?php if ( ! empty( $payment->sub_id ) ) { ?>
								<tr>
									<th scope="row"><?php esc_html_e( 'Subscription', 'formidable' ); ?>:</th>
									<td>
										<a href="?page=formidable-payments&amp;action=show&amp;type=subscriptions&amp;id=<?php echo absint( $payment->sub_id ); ?>">
											<?php esc_html_e( 'View Subscription', 'formidable' ); ?>
										</a>
									</td>
								</tr>
							<?php } ?>

							<tr>
								<th scope="row"><?php esc_html_e( 'Amount', 'formidable' ); ?>:</th>
								<td><?php echo esc_html( FrmTransLiteAppHelper::formatted_amount( $payment ) ); ?></td>
							</tr>

							<?php if ( isset( $payment->test ) ) { ?>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Mode', 'formidable' ); ?>:</th>
								<td><?php echo esc_html( FrmTransLiteAppHelper::get_test_mode_display_string( $payment ) ); ?></td>
							</tr>
							<?php } ?>

							<?php if ( $payment->expire_date && $payment->expire_date !== '0000-00-00' ) { ?>
							<tr>
								<th scope="row"><?php esc_html_e( 'Payment Dates', 'formidable' ); ?>:</th>
								<td>
									<?php echo esc_html( FrmTransLiteAppHelper::format_the_date( $payment->begin_date, $date_format ) ); ?> -
									<?php echo esc_html( FrmTransLiteAppHelper::format_the_date( $payment->expire_date, $date_format ) ); ?>
								</td>
							</tr>
							<?php } ?>

							<?php
							if ( $payment->meta_value ) {
								$payment->meta_value = maybe_unserialize( $payment->meta_value );
								?>
							<tr>
								<th scope="row"><?php esc_html_e( 'Payment Status Updates', 'formidable' ); ?>:</th>
								<td>

								<?php
								foreach ( $payment->meta_value as $k => $metas ) {
									if ( ! $metas ) {
										continue;
									}
									?>
									<table class="widefat" style="border:none;overflow-wrap:break-word;word-break:break-word;">
										<?php
										if ( is_array( $metas ) ) {
											foreach ( $metas as $key => $meta ) {
												?>
											<tr>
												<th><?php echo esc_html( $key ); ?></th>
												<td><?php echo esc_html( $meta ); ?></td>
											</tr>
												<?php
											}
										} else {
											?>
											<tr>
												<th><?php echo esc_html( $k ); ?></th>
												<td><?php echo esc_html( $metas ); ?></td>
											</tr>
											<?php
										}
										?>
									</table>
									<br/>
									<?php
								}//end foreach
								?>

								</td>
							</tr>
								<?php
							}//end if
							?>
						</tbody>
						</table>
				</div>
			</div>
		</div>

		<div class="frm-right-panel">
			<div class="frm_with_icons frm_no_print">
				<h3>
					<?php esc_html_e( 'Payment Details', 'formidable' ); ?>
				</h3>
				<div class="inside">
					<?php FrmTransLitePaymentsController::load_sidebar_actions( $payment ); ?>
					<div class="clear"></div>

					<div class="misc-pub-section">
						<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'frm_action', 'destroy' ) ) ); ?>" data-frmverify="<?php echo esc_attr__( 'Permanently delete this payment?', 'formidable' ); ?>" data-frmverify-btn="frm-button-red" title="<?php esc_attr_e( 'Delete', 'formidable' ); ?>">
							<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_delete_icon' ); ?>
							<span class="frm_link_label">
								<?php esc_html_e( 'Delete Payment', 'formidable' ); ?>
							</span>
						</a>
						<div class="clear"></div>
					</div>
				</div>
			</div>
		</div>
		</div>
	</div>
</div>
