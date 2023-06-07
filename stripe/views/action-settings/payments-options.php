<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// TODO Move this into the controller.
$currencies = FrmCurrencyHelper::get_currencies();
?>

<?php // Don't just always include this when the Payments submodule is active. ?>
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

	<?php
	$cc_field = $this->maybe_show_fields_dropdown(
		$field_dropdown_atts,
		array(
			'name' => 'credit_card',
			'allowed_fields' => 'credit_card',
		)
	);
	if ( $cc_field['field_count'] === 1 ) {
		?>
		<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'credit_card' ) ); ?>" value="<?php echo esc_attr( $cc_field['field_id'] ); ?>" />
	<?php } else { ?>
	<p class="<?php echo esc_attr( $classes['credit_card'] ); ?> frm6">
		<label for="<?php echo esc_attr( $this->get_field_id( 'credit_card' ) ); ?>">
			<?php esc_html_e( 'Credit Card', 'formidable' ); ?>
		</label>
		<?php
		$this->show_fields_dropdown(
			$field_dropdown_atts,
			array(
				'name' => 'credit_card',
				'allowed_fields' => 'credit_card',
			)
		);
		?>
	</p>
	<?php } ?>

	<p class="frm6">
		<label>
			<?php esc_html_e( 'Payment Type', 'formidable' ); ?>
		</label>
		<select name="<?php echo esc_attr( $this->get_field_name( 'type' ) ); ?>" class="frm_trans_type">
			<option value="single" <?php selected( $form_action->post_content['type'], 'one_time' ); ?>><?php esc_html_e( 'One-time Payment', 'formidable' ); ?></option>
			<option value="recurring" <?php selected( $form_action->post_content['type'], 'recurring' ); ?>><?php esc_html_e( 'Recurring', 'formidable' ); ?></option>
		</select>
	</p>

	<p class="frm_trans_sub_opts frm6 <?php echo $form_action->post_content['type'] === 'recurring' ? '' : 'frm_hidden'; ?>">
		<label>
			<?php esc_html_e( 'Repeat Every', 'formidable' ); ?>
		</label>
		<input type="number" name="<?php echo esc_attr( $this->get_field_name( 'interval_count' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['interval_count'] ); ?>" max="90" min="1" step="1" />
		<select name="<?php echo esc_attr( $this->get_field_name( 'interval' ) ); ?>" class="auto_width">
			<?php foreach ( FrmTransLiteAppHelper::get_repeat_times() as $k => $v ) { ?>
				<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $form_action->post_content['interval'], $k ); ?>><?php echo esc_html( $v ); ?></option>
			<?php } ?>
		</select>
		<input type="hidden" name="<?php echo esc_attr( $this->get_field_name( 'payment_count' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['payment_count'] ); ?>" />
	</p>

	<p class="frm_trans_sub_opts frm6 <?php echo $form_action->post_content['type'] === 'recurring' ? '' : 'frm_hidden'; ?>">
		<label>
			<?php esc_html_e( 'Trial Period', 'formidable' ); ?>
		</label>
		<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'trial_interval_count' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['trial_interval_count'] ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'trial_interval_count' ) ); ?>" class="frm_not_email_subject auto_width" />
		<?php esc_html_e( 'day(s)', 'formidable' ); ?>
	</p>

	<?php // TODO Keep the capture setting in the Payments submodule ?>

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

	<?php // TODO Still support gateways in the Payments submodule ?>

	<?php
	do_action(
		'frm_pay_show_stripe_options',
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

	<p class="<?php echo esc_attr( $classes['billing_address'] ); ?> frm6">
		<label for="<?php echo esc_attr( $action_control->get_field_id( 'billing_address' ) ); ?>">
			<?php esc_html_e( 'Address', 'formidable' ); ?>
		</label>
		<?php
		$action_control->show_fields_dropdown(
			$field_dropdown_atts,
			array(
				'name'           => 'billing_address',
				'allowed_fields' => 'address',
			)
		);
		?>
	</p>
	<p class="<?php echo esc_attr( $classes['billing_first_name'] ); ?> frm6">
		<label for="<?php echo esc_attr( $this->get_field_id( 'billing_first_name' ) ); ?>">
			<?php esc_html_e( 'First Name', 'formidable' ); ?>
		</label>
		<?php $this->show_fields_dropdown( $field_dropdown_atts, array( 'name' => 'billing_first_name' ) ); ?>
	</p>
	<p class="<?php echo esc_attr( $classes['billing_last_name'] ); ?> frm6">
		<label for="<?php echo esc_attr( $this->get_field_id( 'billing_last_name' ) ); ?>">
			<?php esc_html_e( 'Last Name', 'formidable' ); ?>
		</label>
		<?php $this->show_fields_dropdown( $field_dropdown_atts, array( 'name' => 'billing_last_name' ) ); ?>
	</p>
</div>
