<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2><?php esc_html_e( 'Payments', 'formidable' ); ?></h2>

	<?php include FrmAppHelper::plugin_path() . '/classes/views/shared/errors.php'; ?>

	<div id="poststuff" class="metabox-holder has-right-sidebar">
		<div class="inner-sidebar">
			<div id="submitdiv" class="postbox ">
				<h3 class="hndle"><span><?php esc_html_e( 'Payment Details', 'formidable' ); ?></span></h3>
				<div class="inside">
					<div class="submitbox">
						<div id="major-publishing-actions">
							<div id="delete-action">                	    
								<a class="submitdelete deletion" href="<?php echo esc_url( add_query_arg( 'frm_action', 'destroy' ) ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete that payment?', 'formidable' ); ?>');" title="<?php esc_attr_e( 'Delete', 'formidable' ); ?>">
									<?php esc_html_e( 'Delete', 'formidable' ); ?>
								</a>
							</div>

							<div id="publishing-action">
								<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-payments&action=edit&id=' . $payment->id ) ); ?>" class="button-primary"><?php esc_html_e( 'Edit', 'formidable' ); ?></a>
							</div>
							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div id="post-body">
			<div id="post-body-content">
				<div class="postbox">
					<div class="handlediv"><br/></div>
					<h3 class="hndle"><span><?php esc_html_e( 'Payment', 'formidable' ); ?></span></h3>
					<div class="inside">
						<table class="form-table">
							<tbody>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Status', 'formidable' ); ?>:</th>
								<td><?php echo esc_html( FrmTransLiteAppHelper::show_status( $payment->status ) ); ?></td>
							</tr>

							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'User', 'formidable' ); ?>:</th>
								<td>
									<?php echo wp_kses_post( $user_name ); ?>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Entry', 'formidable' ); ?>:</th>
								<td>
									<a href="?page=formidable-entries&amp;action=show&amp;frm_action=show&amp;id=<?php echo absint( $payment->item_id ); ?>">
										<?php echo absint( $payment->item_id ); ?>
									</a>
								</td>
							</tr>

							<?php if ( ! empty( $payment->receipt_id ) ) { ?>
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Receipt', 'formidable' ); ?>:</th>
								<td>
									<?php FrmTransLitePaymentsController::show_receipt_link( $payment ); ?>
								</td>
							</tr>
							<?php } ?>

							<?php FrmTransLiteAppHelper::show_in_table( $payment->invoice_id, __( 'Invoice #', 'formidable' ) ); ?>

							<?php if ( ! empty( $payment->sub_id ) ) { ?>
								<tr valign="top">
									<th scope="row"><?php esc_html_e( 'Subscription', 'formidable' ); ?>:</th>
									<td>
										<a href="?page=formidable-payments&amp;action=show&amp;type=subscriptions&amp;id=<?php echo absint( $payment->sub_id ); ?>">
											<?php esc_html_e( 'View Subscription', 'formidable' ); ?>
										</a>
									</td>
								</tr>
							<?php } ?>

							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Amount', 'formidable' ); ?>:</th>
								<td><?php echo esc_html( FrmTransLiteAppHelper::formatted_amount( $payment ) ); ?></td>
							</tr>

							<?php if ( $payment->expire_date && $payment->expire_date != '0000-00-00' ) { ?>
							<tr valign="top">
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
							<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Payment Status Updates', 'formidable' ); ?>:</th>
								<td>

								<?php
								foreach ( $payment->meta_value as $k => $metas ) {
									if ( empty( $metas ) ) {
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
								<?php } ?>

								</td>
							</tr>
								<?php
							}
							?>
						</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
