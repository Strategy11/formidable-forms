<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$product_type_value = $form_action->post_content['product_type'] ?? '';
?>
<p class="frm_trans_sub_opts frm6 show_paypal<?php FrmTransLitePaymentsController::maybe_hide_payment_setting( 'paypal', $form_action->post_content['gateway'] ); ?> <?php echo $form_action->post_content['type'] === 'recurring' ? '' : 'frm_hidden'; ?>">
	<label for="<?php echo esc_attr( $action_control->get_field_id( 'product_type' ) ); ?>">
		<?php esc_html_e( 'Product Type', 'formidable' ); ?>
	</label>
	<select id="<?php echo esc_attr( $action_control->get_field_id( 'product_type' ) ); ?>" name="<?php echo esc_attr( $action_control->get_field_name( 'product_type' ) ); ?>">
		<option value="SERVICE" <?php selected( $product_type_value, 'SERVICE' ); ?>><?php esc_html_e( 'Service', 'formidable' ); ?></option>
		<option value="DIGITAL" <?php selected( $product_type_value, 'DIGITAL' ); ?>><?php esc_html_e( 'Digital', 'formidable' ); ?></option>
		<option value="PHYSICAL" <?php selected( $product_type_value, 'PHYSICAL' ); ?>><?php esc_html_e( 'Physical', 'formidable' ); ?></option>
	</select>
</p>
