<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$pay_later_value = $form_action->post_content['pay_later'] ?? 'auto';
?>
<p class="frm6 show_paypal<?php FrmTransLitePaymentsController::maybe_hide_payment_setting( 'paypal', $form_action->post_content['gateway'] ); ?>">
	<label for="<?php echo esc_attr( $action_control->get_field_id( 'pay_later' ) ); ?>">
		<?php esc_html_e( 'Pay Later', 'formidable' ); ?>
	</label>
	<select id="<?php echo esc_attr( $action_control->get_field_id( 'pay_later' ) ); ?>" name="<?php echo esc_attr( $action_control->get_field_name( 'pay_later' ) ); ?>">
		<option value="auto" <?php selected( $pay_later_value, 'auto' ); ?>><?php esc_html_e( 'Automatic', 'formidable' ); ?></option>
		<option value="no-messaging" <?php selected( $pay_later_value, 'no-messaging' ); ?>><?php esc_html_e( 'Turn off messaging', 'formidable' ); ?></option>
		<option value="off" <?php selected( $pay_later_value, 'off' ); ?>><?php esc_html_e( 'Always Disable', 'formidable' ); ?></option>
	</select>
</p>
<p class="frm6 show_paypal<?php FrmTransLitePaymentsController::maybe_hide_payment_setting( 'paypal', $form_action->post_content['gateway'] ); ?>">
	<label for="<?php echo esc_attr( $action_control->get_field_id( 'layout' ) ); ?>">
		<?php esc_html_e( 'Layout', 'formidable' ); ?>
	</label>
	<?php $layout_value = $form_action->post_content['layout'] ?? 'card_and_checkout'; ?>
	<select id="<?php echo esc_attr( $action_control->get_field_id( 'layout' ) ); ?>" name="<?php echo esc_attr( $action_control->get_field_name( 'layout' ) ); ?>">
		<option value="card_and_checkout" <?php selected( $layout_value, 'card_and_checkout' ); ?>><?php esc_html_e( 'Card and checkout button', 'formidable' ); ?></option>
		<option value="checkout_only" <?php selected( $layout_value, 'checkout_only' ); ?>><?php esc_html_e( 'Checkout button only', 'formidable' ); ?></option>
		<option value="card_only" <?php selected( $layout_value, 'card_only' ); ?>><?php esc_html_e( 'Card only', 'formidable' ); ?></option>
	</select>
</p>
