<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

?>
<p class="frm6 frm_first show_paypal<?php FrmTransLitePaymentsController::maybe_hide_payment_setting( 'paypal', $form_action->post_content['gateway'] ); ?>">
	<?php
	FrmHtmlHelper::toggle(
		$action_control->get_field_id( 'pay_later' ),
		$action_control->get_field_name( 'pay_later' ),
		array(
			'checked'     => ! empty( $form_action->post_content['pay_later'] ),
			'echo'        => true,
			'show_labels' => true,
			'off_label'   => __( 'Pay Later', 'formidable' ),
		)
	);
	?>
</p>
