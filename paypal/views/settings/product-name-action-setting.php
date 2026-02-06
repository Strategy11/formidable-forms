<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

?>
<p class="show_paypal<?php FrmTransLitePaymentsController::maybe_hide_payment_setting( 'paypal', $form_action->post_content['gateway'] ); ?>">
	<label for="<?php echo esc_attr( $action_control->get_field_id( 'product_name' ) ); ?>">
		<?php esc_html_e( 'Product Name', 'formidable' ); ?>
	</label>
	<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'product_name' ) ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'product_name' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['product_name'] ); ?>" class="frm_not_email_subject large-text" />
</p>
