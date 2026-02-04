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
		<option value="off" <?php selected( $pay_later_value, 'off' ); ?>><?php esc_html_e( 'Always Disable', 'formidable' ); ?></option>
	</select>
</p>
