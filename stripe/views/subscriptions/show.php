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
						<div id="minor-publishing" style="border:none;">
							<div class="misc-pub-section">
								<?php FrmTransLiteSubscriptionsController::load_sidebar_actions( $subscription ); ?>
								<div class="clear"></div>
							</div>
						</div>

						<div id="major-publishing-actions">
							<div id="delete-action">                	    
								<a class="submitdelete deletion" href="<?php echo esc_url( add_query_arg( 'frm_action', 'destroy' ) ); ?>" onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to delete that subscription?', 'formidable' ); ?>');" title="<?php esc_attr_e( 'Delete', 'formidable' ); ?>">
									<?php esc_html_e( 'Delete', 'formidable' ); ?>
								</a>
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
					<h3 class="hndle"><span><?php esc_html_e( 'Subscription', 'formidable' ); ?></span></h3>
					<div class="inside">
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row"><?php esc_html_e( 'Status', 'formidable' ); ?>:</th>
									<td><?php echo esc_html( FrmTransLiteAppHelper::show_status( $subscription->status ) ); ?></td>
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
										<a href="?page=formidable-entries&amp;action=show&amp;frm_action=show&amp;id=<?php echo absint( $subscription->item_id ); ?>">
											<?php echo absint( $subscription->item_id ); ?>
										</a>
									</td>
								</tr>

								<?php FrmTransLiteAppHelper::show_in_table( $subscription->sub_id, __( 'Subscription', 'formidable' ) ); ?>

								<tr valign="top">
									<th scope="row"><?php esc_html_e( 'Billing Cycle', 'formidable' ); ?>:</th>
									<td><?php echo esc_html( FrmTransLiteAppHelper::format_billing_cycle( $subscription ) ); ?></td>
								</tr>

								<tr valign="top">
									<th scope="row"><?php esc_html_e( 'Next Payment Date', 'formidable' ); ?>:</th>
									<td>
										<?php echo esc_html( FrmTransLiteAppHelper::format_the_date( $subscription->next_bill_date, $date_format ) ); ?>
									</td>
								</tr>

								<?php FrmTransLiteAppHelper::show_in_table( $subscription->fail_count, __( 'Fail Count', 'formidable' ) ); ?>

								<?php // TODO We'd still want to show a Gateway here when the Payments plugin is active. ?> 

								<?php
								if ( $subscription->meta_value ) {
									$subscription->meta_value = maybe_unserialize( $subscription->meta_value );
									?>
								<tr valign="top">
									<th scope="row"><?php esc_html_e( 'Payment Status Updates', 'formidable' ); ?>:</th>
									<td>

									<?php foreach ( $subscription->meta_value as $k => $metas ) { ?>
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
