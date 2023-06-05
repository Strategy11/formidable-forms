<div class="postbox">
	<h3 class="hndle"><span><?php esc_html_e( 'Payment Details', 'formidable' ); ?></span></h3>

	<div class="inside">
		<input type="hidden" name="action" value="<?php echo esc_attr( $form_action ); ?>" />

		<table class="form-table">
			<tbody>
				<tr class="form-field">
					<th scope="row"><?php esc_html_e( 'Status', 'formidable' ); ?></th>
					<td>
						<select name="status">
							<?php foreach ( FrmTransLiteAppHelper::get_payment_statuses() as $status => $label ) { ?>
								<option value="<?php echo esc_attr( $status ); ?>" <?php selected( $status, $payment->status ); ?>>
									<?php echo esc_attr( $label ); ?>
								</option>
							<?php } ?>
						</select>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Entry ID', 'formidable' ); ?></th>
					<td>
						#<input type="number" name="item_id" value="<?php echo esc_attr( $payment->item_id ); ?>" />
						<?php if ( $payment->item_id ) { ?>
							<a href="<?php echo esc_url( '?page=formidable-entries&frm_action=show&action=show&id=' . $payment->item_id ); ?>"><?php esc_html_e( 'View Entry', 'formidable' ); ?></a>
						<?php } ?>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Receipt', 'formidable' ); ?></th>
					<td>
						<?php if ( $payment->paysys !== 'manual' && ! empty( $payment->receipt_id ) ) { ?>
							<?php FrmTransLitePaymentsController::show_receipt_link( $payment ); ?>
							<input type="hidden" name="receipt_id" value="<?php echo esc_attr( $payment->receipt_id ); ?>" />
						<?php } else { ?>
							<input type="text" name="receipt_id" value="<?php echo esc_attr( $payment->receipt_id ); ?>" />
						<?php } ?>

						<?php if ( ! empty( $payment->sub_id ) ) { ?>
							<p>
								<?php esc_html_e( 'Subscription Number:', 'formidable' ); ?>
								<?php echo esc_html( $payment->sub_id ); ?>
							</p>
						<?php } ?>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Amount', 'formidable' ); ?></th>
					<td>
						<?php echo esc_html( $currency['symbol_left'] ); ?>
						<input type="text" name="amount" value="<?php echo esc_attr( $payment->amount ); ?>" />
						<?php echo esc_html( $currency['symbol_right'] ); ?>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Date', 'formidable' ); ?></th>
					<td><input type="text" name="begin_date" class="" value="<?php echo esc_attr( $payment->begin_date ); ?>" /></td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Expiration Date', 'formidable' ); ?></th>
					<td>
						<input type="text" name="expire_date" value="<?php echo esc_attr( $payment->expire_date ); ?>" />
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><?php esc_html_e( 'Payment Method', 'formidable' ); ?></th>
					<td>
						<select name="paysys">
							<?php foreach ( FrmTransLiteAppHelper::get_gateways() as $name => $gateway ) { ?>
							<option value="<?php echo esc_attr( $name ); ?>" <?php selected( $payment->paysys, $name ); ?>>
								<?php echo esc_html( $gateway['label'] ); ?>
							</option>
							<?php } ?>
						</select>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
