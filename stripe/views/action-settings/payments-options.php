<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

if ( ! FrmStrpLiteConnectHelper::at_least_one_mode_is_setup() ) {
	FrmStrpLiteAppHelper::not_connected_warning();
} else {
	FrmStrpLiteAppHelper::fee_education();
}
?>

<input type="hidden" value="stripe" name="<?php echo esc_attr( $this->get_field_name( 'gateway' ) ); ?>[]" />

<div class="frm_grid_container">
	<p>
		<label for="<?php echo esc_attr( $action_control->get_field_id( 'description' ) ); ?>">
			<?php esc_html_e( 'Description', 'formidable' ); ?>
		</label>
		<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'description' ) ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'description' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['description'] ); ?>" class="frm_not_email_subject large-text" />
	</p>

	<p class="frm6">
		<label for="<?php echo esc_attr( $action_control->get_field_id( 'amount' ) ); ?>">
			<?php esc_html_e( 'Amount', 'formidable' ); ?>
		</label>
		<input type="text" value="<?php echo esc_attr( $form_action->post_content['amount'] ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'amount' ) ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'amount' ) ); ?>" class="frm_not_email_subject large-text" />
	</p>

	<?php $cc_field_id = $this->get_credit_card_field_id( $field_dropdown_atts ); ?>
	<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'credit_card' ) ); ?>" value="<?php echo esc_attr( $cc_field_id ); ?>" />

	<p class="frm6">
		<label>
			<?php esc_html_e( 'Payment Type', 'formidable' ); ?>
		</label>
		<select name="<?php echo esc_attr( $this->get_field_name( 'type' ) ); ?>" class="frm_trans_type">
			<option value="single" <?php selected( $form_action->post_content['type'], 'one_time' ); ?>><?php esc_html_e( 'One-time Payment', 'formidable' ); ?></option>
			<option value="recurring" <?php selected( $form_action->post_content['type'], 'recurring' ); ?>><?php esc_html_e( 'Recurring', 'formidable' ); ?></option>
		</select>
	</p>

	<?php $this->echo_capture_payment_upsell(); ?>

	<p class="frm6 frm_trans_sub_opts <?php echo $form_action->post_content['type'] === 'recurring' ? '' : 'frm_hidden'; ?>">
		<label>
			<?php esc_html_e( 'Repeat Every', 'formidable' ); ?>
		</label>
		<span class="frm_grid_container">
			<input class="frm6" type="number" name="<?php echo esc_attr( $this->get_field_name( 'interval_count' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['interval_count'] ); ?>" max="90" min="1" step="1" />
			<select class="frm6" name="<?php echo esc_attr( $this->get_field_name( 'interval' ) ); ?>" class="auto_width">
				<?php foreach ( $repeat_times as $k => $v ) { ?>
					<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $form_action->post_content['interval'], $k ); ?>><?php echo esc_html( $v ); ?></option>
				<?php } ?>
			</select>
			<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'payment_count' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['payment_count'] ); ?>" />
		</span>
	</p>

	<p class="frm_trans_sub_opts frm6 <?php echo $form_action->post_content['type'] === 'recurring' ? '' : 'frm_hidden'; ?>">
		<label>
			<?php esc_html_e( 'Recurring Payment Limit', 'formidable' ); ?>
		</label>
		<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'payment_limit' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['payment_limit'] ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'payment_limit' ) ); ?>" class="frm_not_email_subject" />
	</p>

	<p class="frm_trans_sub_opts frm6 <?php echo $form_action->post_content['type'] === 'recurring' ? '' : 'frm_hidden'; ?>">
		<label>
			<?php esc_html_e( 'Trial Period', 'formidable' ); ?>
		</label>
		<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'trial_interval_count' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['trial_interval_count'] ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'trial_interval_count' ) ); ?>" class="frm_not_email_subject" />
		<?php esc_html_e( 'day(s)', 'formidable' ); ?>
	</p>

	<p class="frm6">
		<label for="<?php echo esc_attr( $this->get_field_id( 'currency' ) ); ?>">
			<?php esc_html_e( 'Currency', 'formidable' ); ?>
		</label>
		<select name="<?php echo esc_attr( $this->get_field_name( 'currency' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'currency' ) ); ?>">
			<?php foreach ( $currencies as $code => $currency ) { ?>
				<option value="<?php echo esc_attr( strtolower( $code ) ); ?>" <?php selected( $form_action->post_content['currency'], strtolower( $code ) ); ?>><?php echo esc_html( $currency['name'] . ' (' . strtoupper( $code ) . ')' ); ?></option>
				<?php
				unset( $currency, $code );
			}
			?>
		</select>
	</p>

	<?php
	FrmStrpLiteActionsController::add_action_options(
		array(
			'form_action'    => $form_action,
			'action_control' => $this,
		)
	);
	?>
</div>
<div class="frm_grid_container">
	<h3>
		<?php esc_html_e( 'Customer Information', 'formidable' ); ?>
	</h3>

	<p class="frm6">
		<label for="<?php echo esc_attr( $action_control->get_field_id( 'email' ) ); ?>">
			<?php esc_html_e( 'Email', 'formidable' ); ?>
		</label>
		<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'email' ) ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'email' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['email'] ); ?>" class="frm_not_email_to large-text" />
	</p>

	<?php
	/**
	 * Trigger an action so Pro can include an Address dropdown.
	 *
	 * @since 6.5
	 *
	 * @param array $args {
	 *     @type FrmFormAction $action_control
	 *     @type array         $field_dropdown_atts
	 * }
	 */
	do_action(
		'frm_stripe_lite_customer_info_after_email',
		compact( 'action_control', 'field_dropdown_atts' )
	);
	?>

	<p class="frm6">
		<label for="<?php echo esc_attr( $this->get_field_id( 'billing_first_name' ) ); ?>">
			<?php esc_html_e( 'First Name', 'formidable' ); ?>
		</label>
		<?php $this->show_fields_dropdown( $field_dropdown_atts, array( 'name' => 'billing_first_name' ) ); ?>
	</p>
	<p class="frm6">
		<label for="<?php echo esc_attr( $this->get_field_id( 'billing_last_name' ) ); ?>">
			<?php esc_html_e( 'Last Name', 'formidable' ); ?>
		</label>
		<?php $this->show_fields_dropdown( $field_dropdown_atts, array( 'name' => 'billing_last_name' ) ); ?>
	</p>
</div>
