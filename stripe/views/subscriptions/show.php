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
				'label'      => __( 'View Subscription', 'formidable' ),
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
							<span><?php esc_html_e( 'Subscription', 'formidable' ); ?></span>
						</h3>

						<table class="frm-alt-table">
							<tbody>
								<tr>
									<th scope="row"><?php esc_html_e( 'Status', 'formidable' ); ?>:</th>
									<td><?php echo esc_html( FrmTransLiteAppHelper::show_status( $subscription->status ) ); ?></td>
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

								<?php if ( ! empty( $subscription->sub_id ) ) { ?>
									<tr>
										<th scope="row"><?php esc_html_e( 'Receipt', 'formidable' ); ?>:</th>
										<td>
											<?php FrmTransLiteSubscriptionsController::show_receipt_link( $subscription ); ?>
										</td>
									</tr>
								<?php } ?>

								<?php if ( isset( $subscription->test ) ) { ?>
								<tr valign="top">
									<th scope="row"><?php esc_html_e( 'Mode', 'formidable' ); ?>:</th>
									<td><?php echo esc_html( FrmTransLiteAppHelper::get_test_mode_display_string( $subscription ) ); ?></td>
								</tr>
								<?php } ?>

								<tr>
									<th scope="row"><?php esc_html_e( 'Billing Cycle', 'formidable' ); ?>:</th>
									<td><?php echo esc_html( FrmTransLiteAppHelper::format_billing_cycle( $subscription ) ); ?></td>
								</tr>

								<tr>
									<th scope="row"><?php esc_html_e( 'Next Payment Date', 'formidable' ); ?>:</th>
									<td>
										<?php echo esc_html( FrmTransLiteAppHelper::format_the_date( $subscription->next_bill_date, $date_format ) ); ?>
									</td>
								</tr>

								<?php FrmTransLiteAppHelper::show_in_table( $subscription->fail_count, __( 'Fail Count', 'formidable' ) ); ?>

								<?php
								if ( $subscription->meta_value ) {
									$subscription->meta_value = maybe_unserialize( $subscription->meta_value );
									?>
								<tr>
									<th scope="row"><?php esc_html_e( 'Payment Status Updates', 'formidable' ); ?>:</th>
									<td>

									<?php foreach ( $subscription->meta_value as $metas ) { ?>
										<table class="widefat" style="border:none;">
										<?php

										foreach ( $metas as $key => $meta ) {
											?>
										<tr>
											<th><?php echo esc_html( $key ); ?></th>
											<td><?php echo esc_html( $meta ); ?></td>
										</tr>
											<?php
										}
										?>
										</table>
										<br/>
									<?php } ?>

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
					<h3><?php esc_html_e( 'Subscription Details', 'formidable' ); ?></h3>
					<div class="inside">
						<div class="submitbox">
							<div id="minor-publishing" style="border:none;">
								<div class="misc-pub-section">
									<?php FrmTransLiteSubscriptionsController::load_sidebar_actions( $subscription ); ?>
									<div class="clear"></div>
								</div>
							</div>

							<div id="misc-pub-section">
								<a class="submitdelete deletion" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'frm_action', 'destroy' ) ) ); ?>" data-frmverify="<?php echo esc_attr__( 'Permanently delete this subscription?', 'formidable' ); ?>" data-frmverify-btn="frm-button-red" title="<?php esc_attr_e( 'Delete', 'formidable' ); ?>">
									<?php FrmAppHelper::icon_by_class( 'frm_icon_font frm_delete_icon' ); ?>
									<span class="frm_link_label">
										<?php esc_html_e( 'Delete Subscription', 'formidable' ); ?>
									</span>
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
