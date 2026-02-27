<?php
/**
 * PayPal button settings view
 *
 * @package Formidable
 *
 * @since x.x
 *
 * @var array $settings PayPal button settings array
 * @var array $args     Additional arguments for the view
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

// Extract button settings with defaults
$button_color         = $form_action->post_content['button_color'] ?? 'default';
$button_label         = $form_action->post_content['button_label'] ?? 'paypal';
$button_border_radius = $form_action->post_content['button_border_radius'] ?? 10;
?>
<div class="show_paypal<?php FrmTransLitePaymentsController::maybe_hide_payment_setting( 'paypal', $form_action->post_content['gateway'] ); ?>">
	<div class="frm_grid_container">
		<h3><?php esc_html_e( 'PayPal Button Settings', 'formidable' ); ?></h3>

		<p class="frm4">
			<label>
				<?php esc_html_e( 'Color', 'formidable' ); ?>
			</label>
			<select name="<?php echo esc_attr( $action_control->get_field_name( 'button_color' ) ); ?>">
				<option value="default" <?php selected( $button_color, 'default' ); ?>><?php esc_html_e( 'Use Defaults', 'formidable' ); ?></option>
				<option value="gold" <?php selected( $button_color, 'gold' ); ?>><?php esc_html_e( 'Gold', 'formidable' ); ?></option>
				<option value="blue" <?php selected( $button_color, 'blue' ); ?>><?php esc_html_e( 'Blue', 'formidable' ); ?></option>
				<option value="silver" <?php selected( $button_color, 'silver' ); ?>><?php esc_html_e( 'Silver', 'formidable' ); ?></option>
				<option value="white" <?php selected( $button_color, 'white' ); ?>><?php esc_html_e( 'White', 'formidable' ); ?></option>
				<option value="black" <?php selected( $button_color, 'black' ); ?>><?php esc_html_e( 'Black', 'formidable' ); ?></option>
			</select>
		</p>

		<p class="frm4">
			<label>
				<?php esc_html_e( 'Label', 'formidable' ); ?>
			</label>
			<select name="<?php echo esc_attr( $action_control->get_field_name( 'button_label' ) ); ?>">
				<option value="paypal" <?php selected( $button_label, 'paypal' ); ?>><?php esc_html_e( 'PayPal', 'formidable' ); ?></option>
				<option value="checkout" <?php selected( $button_label, 'checkout' ); ?>><?php esc_html_e( 'PayPal Checkout', 'formidable' ); ?></option>
				<option value="buynow" <?php selected( $button_label, 'buynow' ); ?>><?php esc_html_e( 'Buy Now', 'formidable' ); ?></option>
				<option value="pay" <?php selected( $button_label, 'pay' ); ?>><?php esc_html_e( 'Pay with PayPal', 'formidable' ); ?></option>
			</select>
		</p>

		<p class="frm4">
			<label>
				<?php esc_html_e( 'Border Radius', 'formidable' ); ?>
			</label>
			<?php
			FrmHtmlHelper::echo_unit_input(
				array(
					'value'              => (int) $button_border_radius,
					'field_attrs'        => array(
						'id'   => 'button_border_radius',
						'name' => $action_control->get_field_name( 'button_border_radius' ),
					),
					'input_number_attrs' => array(
						'class' => 'frm-w-full',
					),
					'units'              => array( 'px' ),
				)
			);
			?>
		</p>
	</div>
</div>
