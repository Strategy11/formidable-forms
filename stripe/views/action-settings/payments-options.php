<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$stripe_connected = FrmStrpLiteConnectHelper::at_least_one_mode_is_setup();
$square_connected = FrmSquareLiteConnectHelper::at_least_one_mode_is_setup();
$paypal_connected = FrmPayPalLiteConnectHelper::at_least_one_mode_is_setup();

if ( $stripe_connected ) {
	FrmStrpLiteAppHelper::fee_education( 'stripe-action-tip', $form_action->post_content['gateway'] );
}

if ( $square_connected ) {
	FrmSquareLiteAppHelper::fee_education( 'square-action-tip', $form_action->post_content['gateway'] );
}

if ( $paypal_connected ) {
	FrmPayPalLiteAppHelper::fee_education( 'paypal-action-tip', $form_action->post_content['gateway'] );
}

if ( ! $stripe_connected && ! $square_connected && ! $paypal_connected ) {
	FrmStrpLiteAppHelper::not_connected_warning();
}
?>

<?php FrmTransLiteAppHelper::show_gateway_buttons( $gateways, $form_action, $action_control ); ?>

<p class="frm6">
	<label>
		<?php esc_html_e( 'Payment Type', 'formidable' ); ?>
	</label>
	<select name="<?php echo esc_attr( $action_control->get_field_name( 'type' ) ); ?>" class="frm_trans_type">
		<option value="single" <?php selected( $form_action->post_content['type'], 'one_time' ); ?>><?php esc_html_e( 'One-time Payment', 'formidable' ); ?></option>
		<option value="recurring" <?php selected( $form_action->post_content['type'], 'recurring' ); ?>><?php esc_html_e( 'Recurring', 'formidable' ); ?></option>
	</select>
</p>

<div class="frm_grid_container">
	<p>
		<label for="<?php echo esc_attr( $action_control->get_field_id( 'description' ) ); ?>">
			<?php esc_html_e( 'Description', 'formidable' ); ?>
		</label>
		<input type="text" name="<?php echo esc_attr( $action_control->get_field_name( 'description' ) ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'description' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['description'] ); ?>" class="frm_not_email_subject large-text" />
	</p>

	<p class="frm6">
		<label for="<?php echo esc_attr( $action_control->get_field_id( 'amount' ) ); ?>">
			<?php esc_html_e( 'Amount', 'formidable' ); ?>
		</label>
		<input type="text" value="<?php echo esc_attr( $form_action->post_content['amount'] ); ?>" name="<?php echo esc_attr( $action_control->get_field_name( 'amount' ) ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'amount' ) ); ?>" class="frm_not_email_subject large-text" />
	</p>

	<p class="frm6">
		<label for="<?php echo esc_attr( $action_control->get_field_id( 'currency' ) ); ?>">
			<?php esc_html_e( 'Currency', 'formidable' ); ?>
		</label>
		<?php FrmTransLiteAppHelper::show_currency_dropdown( $action_control->get_field_id( 'currency' ), $action_control->get_field_name( 'currency' ), $form_action->post_content ); ?>
	</p>

	<?php $cc_field_id = $action_control->get_credit_card_field_id( $field_dropdown_atts ); ?>
	<input type="hidden" name="<?php echo esc_attr( $action_control->get_field_name( 'credit_card' ) ); ?>" value="<?php echo esc_attr( $cc_field_id ); ?>" />

	<?php $action_control->echo_capture_payment_upsell( $form_action->post_content['gateway'] ); ?>

	<div class="frm_trans_sub_opts <?php echo $form_action->post_content['type'] === 'recurring' ? '' : 'frm_hidden'; ?>">
		<div class="frm_grid_container">
			<h3><?php esc_html_e( 'Recurring Payment Settings', 'formidable' ); ?></h3>

			<?php
			/**
			 * Include PayPal-specific subscription settings (Product Name and Product Type).
			 * These are only shown when PayPal is the selected gateway.
			 */
			if ( FrmPayPalLiteConnectHelper::at_least_one_mode_is_setup() ) {
				?>
				<p class="frm6 show_paypal<?php FrmTransLitePaymentsController::maybe_hide_payment_setting( 'paypal', $form_action->post_content['gateway'] ); ?>">
					<label for="<?php echo esc_attr( $action_control->get_field_id( 'product_name' ) ); ?>">
						<?php esc_html_e( 'Product Name', 'formidable' ); ?>
					</label>
					<input type="text" name="<?php echo esc_attr( $action_control->get_field_name( 'product_name' ) ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'product_name' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['product_name'] ?? '' ); ?>" class="frm_not_email_subject large-text" />
				</p>

				<?php
				$product_type_value = $form_action->post_content['product_type'] ?? '';
				?>
				<p class="frm6 show_paypal<?php FrmTransLitePaymentsController::maybe_hide_payment_setting( 'paypal', $form_action->post_content['gateway'] ); ?>">
					<label for="<?php echo esc_attr( $action_control->get_field_id( 'product_type' ) ); ?>">
						<?php esc_html_e( 'Product Type', 'formidable' ); ?>
					</label>
					<select id="<?php echo esc_attr( $action_control->get_field_id( 'product_type' ) ); ?>" name="<?php echo esc_attr( $action_control->get_field_name( 'product_type' ) ); ?>">
						<option value="SERVICE" <?php selected( $product_type_value, 'SERVICE' ); ?>><?php esc_html_e( 'Service', 'formidable' ); ?></option>
						<option value="DIGITAL" <?php selected( $product_type_value, 'DIGITAL' ); ?>><?php esc_html_e( 'Digital', 'formidable' ); ?></option>
						<option value="PHYSICAL" <?php selected( $product_type_value, 'PHYSICAL' ); ?>><?php esc_html_e( 'Physical', 'formidable' ); ?></option>
					</select>
				</p>
				<?php
			}
			?>

			<p class="frm6">
				<label>
					<?php esc_html_e( 'Repeat', 'formidable' ); ?>
				</label>
				<span>
					<span class="frm_grid_container">
						<input class="frm6" type="number" name="<?php echo esc_attr( $action_control->get_field_name( 'interval_count' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['interval_count'] ); ?>" max="90" min="1" step="1" />
						<select class="frm6" name="<?php echo esc_attr( $action_control->get_field_name( 'interval' ) ); ?>" class="auto_width">
							<?php foreach ( $repeat_times as $k => $v ) { ?>
								<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $form_action->post_content['interval'], $k ); ?>><?php echo esc_html( $v ); ?></option>
							<?php } ?>
						</select>
						<input type="hidden" name="<?php echo esc_attr( $action_control->get_field_name( 'payment_count' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['payment_count'] ); ?>" />
					</span>
				</span>
			</p>

			<?php
			/*
			Note: The Repeat Cadence setting is added with JavaScript.
			This hidden input is added so the JS knows what value is set.
			*/
			?>
			<input type="hidden" class="frm-repeat-cadence-value" value="<?php echo esc_attr( $form_action->post_content['repeat_cadence'] ?? 'DAILY' ); ?>" />

			<p class="frm6">
				<label>
					<?php esc_html_e( 'Recurring Payment Limit', 'formidable' ); ?>
				</label>
				<input type="text" name="<?php echo esc_attr( $action_control->get_field_name( 'payment_limit' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['payment_limit'] ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'payment_limit' ) ); ?>" class="frm_not_email_subject" />
			</p>

			<p class="frm6">
				<label>
					<?php esc_html_e( 'Trial Period', 'formidable' ); ?>
				</label>
				<input type="text" name="<?php echo esc_attr( $action_control->get_field_name( 'trial_interval_count' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['trial_interval_count'] ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'trial_interval_count' ) ); ?>" class="frm_not_email_subject" />
				<?php esc_html_e( 'day(s)', 'formidable' ); ?>
			</p>
		</div>
	</div>

	<?php
	FrmStrpLiteActionsController::add_action_options(
		array(
			'form_action'    => $form_action,
			'action_control' => $action_control,
		)
	);

	FrmPayPalLiteActionsController::add_action_options(
		array(
			'form_action'    => $form_action,
			'action_control' => $action_control,
		)
	);
	?>
</div>
<?php FrmPayPalLiteActionsController::add_button_settings_section( $action_control, $form_action ); ?>
<div class="frm_grid_container">
	<h3>
		<?php esc_html_e( 'Customer Information', 'formidable' ); ?>
	</h3>

	<p class="frm6">
		<label for="<?php echo esc_attr( $action_control->get_field_id( 'email' ) ); ?>">
			<?php esc_html_e( 'Email', 'formidable' ); ?>
		</label>
		<input type="text" name="<?php echo esc_attr( $action_control->get_field_name( 'email' ) ); ?>" id="<?php echo esc_attr( $action_control->get_field_id( 'email' ) ); ?>" value="<?php echo esc_attr( $form_action->post_content['email'] ); ?>" class="frm_not_email_to large-text" />
	</p>

	<?php
	/**
	 * Trigger an action so Pro can include an Address dropdown.
	 *
	 * @since 6.5
	 *
	 * @param array $args {
	 *
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
		<label for="<?php echo esc_attr( $action_control->get_field_id( 'billing_first_name' ) ); ?>">
			<?php esc_html_e( 'First Name', 'formidable' ); ?>
		</label>
		<?php $action_control->show_fields_dropdown( $field_dropdown_atts, array( 'name' => 'billing_first_name' ) ); ?>
	</p>
	<p class="frm6">
		<label for="<?php echo esc_attr( $action_control->get_field_id( 'billing_last_name' ) ); ?>">
			<?php esc_html_e( 'Last Name', 'formidable' ); ?>
		</label>
		<?php $action_control->show_fields_dropdown( $field_dropdown_atts, array( 'name' => 'billing_last_name' ) ); ?>
	</p>
</div>
