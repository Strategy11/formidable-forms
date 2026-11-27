<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$div_attrs = array(
	'class' => 'frm_grid_container show_paypal',
);

ob_start();
FrmTransLitePaymentsController::maybe_hide_payment_setting( 'paypal', $form_action->post_content['gateway'] );
$div_attrs['class'] .= ob_get_clean();
?>
<div <?php FrmAppHelper::array_to_html_params( $div_attrs, true ); ?>>
	<h3>
		<?php esc_html_e( 'Shipping Information', 'formidable' ); ?>
	</h3>

	<p class="frm6">
		<label for="<?php echo esc_attr( $action_control->get_field_id( 'shipping_email' ) ); ?>">
			<?php esc_html_e( 'Email', 'formidable' ); ?>
		</label>
		<input type="text" name="<?php echo esc_attr( $action_control->get_field_name( 'shipping_email' ) ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'shipping_email' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['shipping_email'] ?? '' ); ?>" class="frm_not_email_to large-text" />
	</p>
	<p class="frm6">
		<label for="<?php echo esc_attr( $action_control->get_field_id( 'shipping_address' ) ); ?>">
			<?php esc_html_e( 'Address', 'formidable' ); ?>
		</label>
		<?php
		$action_control->show_fields_dropdown(
			$field_dropdown_atts,
			array(
				'name'           => 'shipping_address',
				'allowed_fields' => 'address',
			)
		);
		?>
	</p>
	<p class="frm6">
		<label for="<?php echo esc_attr( $action_control->get_field_id( 'shipping_first_name' ) ); ?>">
			<?php esc_html_e( 'First Name', 'formidable' ); ?>
		</label>
		<?php $action_control->show_fields_dropdown( $field_dropdown_atts, array( 'name' => 'shipping_first_name' ) ); ?>
	</p>
	<p class="frm6">
		<label for="<?php echo esc_attr( $action_control->get_field_id( 'shipping_last_name' ) ); ?>">
			<?php esc_html_e( 'Last Name', 'formidable' ); ?>
		</label>
		<?php $action_control->show_fields_dropdown( $field_dropdown_atts, array( 'name' => 'shipping_last_name' ) ); ?>
	</p>
</div>
