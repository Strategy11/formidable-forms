<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$pay_later_value = $form_action->post_content['pay_later'] ?? 'auto';
?>
<div class="show_paypal<?php FrmTransLitePaymentsController::maybe_hide_payment_setting( 'paypal', $form_action->post_content['gateway'] ); ?>">
	<div class="frm_grid_container">
		<h3><?php esc_html_e( 'PayPal Settings', 'formidable' ); ?></h3>

		<p class="frm6">
			<label for="<?php echo esc_attr( $action_control->get_field_id( 'layout' ) ); ?>">
				<?php esc_html_e( 'Layout', 'formidable' ); ?>
			</label>
			<?php $layout_value = $form_action->post_content['layout'] ?? 'card_and_checkout'; ?>
			<select id="<?php echo esc_attr( $action_control->get_field_id( 'layout' ) ); ?>" name="<?php echo esc_attr( $action_control->get_field_name( 'layout' ) ); ?>">
				<option value="card_and_checkout" <?php selected( $layout_value, 'card_and_checkout' ); ?>><?php esc_html_e( 'Card and checkout buttons', 'formidable' ); ?></option>
				<option value="checkout_only" <?php selected( $layout_value, 'checkout_only' ); ?>><?php esc_html_e( 'Checkout buttons only', 'formidable' ); ?></option>
				<option value="card_only" <?php selected( $layout_value, 'card_only' ); ?>><?php esc_html_e( 'Card only', 'formidable' ); ?></option>
			</select>
		</p>
		<p class="frm6">
			<label for="<?php echo esc_attr( $action_control->get_field_id( 'pay_later' ) ); ?>">
				<?php esc_html_e( 'Pay Later', 'formidable' ); ?>
			</label>
			<select id="<?php echo esc_attr( $action_control->get_field_id( 'pay_later' ) ); ?>" name="<?php echo esc_attr( $action_control->get_field_name( 'pay_later' ) ); ?>">
				<option value="auto" <?php selected( $pay_later_value, 'auto' ); ?>><?php esc_html_e( 'Automatic', 'formidable' ); ?></option>
				<option value="no-messaging" <?php selected( $pay_later_value, 'no-messaging' ); ?>><?php esc_html_e( 'Turn off messaging', 'formidable' ); ?></option>
				<option value="off" <?php selected( $pay_later_value, 'off' ); ?>><?php esc_html_e( 'Always Disable', 'formidable' ); ?></option>
			</select>
		</p>
		<p class="frm6">
			<label for="<?php echo esc_attr( $action_control->get_field_id( 'entry_data_sync' ) ); ?>">
				<?php esc_html_e( 'Order Sync', 'formidable' ); ?>
			</label>
			<?php $entry_data_sync_value = $form_action->post_content['entry_data_sync'] ?? 'overwrite'; ?>
			<select id="<?php echo esc_attr( $action_control->get_field_id( 'entry_data_sync' ) ); ?>" name="<?php echo esc_attr( $action_control->get_field_name( 'entry_data_sync' ) ); ?>">
				<option value="overwrite" <?php selected( $entry_data_sync_value, 'overwrite' ); ?>><?php esc_html_e( 'Overwrite entry data', 'formidable' ); ?></option>
				<option value="new_fields" <?php selected( $entry_data_sync_value, 'new_fields' ); ?>><?php esc_html_e( 'Create new order data fields', 'formidable' ); ?></option>
			</select>

			<?php
			/**
			 * These hidden fields are required to prevent more than one set of order data fields from being created.
			 */
			?>
			<?php if ( ! empty( $form_action->post_content['paypal_order_email'] ) ) : ?>
				<input type="hidden" value="<?php echo esc_attr( $form_action->post_content['paypal_order_email'] ); ?>" name="<?php echo esc_attr( $action_control->get_field_name( 'paypal_order_email' ) ); ?>">
			<?php endif; ?>
			<?php if ( ! empty( $form_action->post_content['paypal_order_name'] ) ) : ?>
				<input type="hidden" value="<?php echo esc_attr( $form_action->post_content['paypal_order_name'] ); ?>" name="<?php echo esc_attr( $action_control->get_field_name( 'paypal_order_name' ) ); ?>">
			<?php endif; ?>
			<?php if ( ! empty( $form_action->post_content['paypal_order_address'] ) ) : ?>
				<input type="hidden" value="<?php echo esc_attr( $form_action->post_content['paypal_order_address'] ); ?>" name="<?php echo esc_attr( $action_control->get_field_name( 'paypal_order_address' ) ); ?>">
			<?php endif; ?>
		</p>
	</div>
</div>
