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

<?php
/**
 * Show warning if amount is empty.
 * Only show for existing actions (has ID), not new actions.
 *
 * @since x.x
 */
if ( ! empty( $form_action->ID ) && empty( $form_action->post_content['amount'] ) ) :
	?>
	<div class="frm_warning_style frm-with-icon">
		<?php FrmAppHelper::icon_by_class( 'frmfont frm_alert_icon', array( 'style' => 'width:24px' ) ); ?>
		<span><?php esc_html_e( 'Amount is required or payments will not work.', 'formidable' ); ?></span>
	</div>
<?php endif; ?>

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
			<?php esc_html_e( 'Amount', 'formidable' ); ?> <span class="frm_required">*</span>
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
			 * Show warning if PayPal is selected and product name is empty.
			 * Only show for existing actions (has ID), not new actions.
			 * Only show when payment type is recurring.
			 *
			 * @since x.x
			 */
			if ( ! empty( $form_action->ID ) && 'recurring' === $form_action->post_content['type'] && in_array( 'paypal', (array) $form_action->post_content['gateway'], true ) && empty( $form_action->post_content['product_name'] ) ) :
				?>
				<div class="frm_warning_style frm-with-icon frm-mt-0">
					<?php FrmAppHelper::icon_by_class( 'frmfont frm_alert_icon', array( 'style' => 'width:24px' ) ); ?>
					<span><?php esc_html_e( 'Product Name is required for PayPal recurring payments.', 'formidable' ); ?></span>
				</div>
			<?php endif; ?>

			<?php
			/**
			 * @since x.x
			 *
			 * @param array $args
			 */
			do_action(
				'frm_payments_settings_recurring_product_info',
				array(
					'form_action'    => $form_action,
					'action_control' => $action_control,
				)
			);
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
	/**
	 * Allow add-ons to inject settings after the Recurring Payment Settings section.
	 *
	 * @since x.x
	 *
	 * @param array $args {
	 *
	 *     @type object $form_action    The form action post object.
	 *     @type object $action_control The action controller object.
	 * }
	 */
	do_action(
		'frm_payments_settings_after_recurring',
		array(
			'form_action'    => $form_action,
			'action_control' => $action_control,
		)
	);
	?>
</div>
<div class="frm_grid_container">
	<?php
	$billing_label  = __( 'Billing Information', 'formidable' );
	$customer_label = __( 'Customer Information', 'formidable' );
	$is_paypal      = in_array( 'paypal', (array) $form_action->post_content['gateway'], true );
	?>
	<h3 class="frm-billing-section-heading" data-billing-label="<?php echo esc_attr( $billing_label ); ?>" data-customer-label="<?php echo esc_attr( $customer_label ); ?>">
		<?php echo esc_html( $is_paypal ? $billing_label : $customer_label ); ?>
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
	 *     @type WP_Post      $form_action         The form action post object.
	 *     @type FrmFormAction $action_control      The action controller object.
	 *     @type array         $field_dropdown_atts Attributes for field dropdown rendering.
	 * }
	 */
	do_action(
		'frm_stripe_lite_customer_info_after_email',
		compact( 'form_action', 'action_control', 'field_dropdown_atts' )
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

<?php
/**
 * Fires after the Customer Information section in payment action settings.
 * Used by Pro to add gateway-specific Shipping and Billing sections.
 *
 * @since x.x
 *
 * @param array $args {
 *     @type WP_Post        $form_action         The form action post object.
 *     @type FrmFormAction   $action_control      The action controller object.
 *     @type array           $field_dropdown_atts Attributes for field dropdown rendering.
 * }
 */
do_action(
	'frm_payment_settings_after_customer_info',
	compact( 'form_action', 'action_control', 'field_dropdown_atts' )
);
?>
