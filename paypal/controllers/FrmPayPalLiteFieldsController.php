<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmPayPalLiteFieldsController {

	/**
	 * Hide the email, name, and address fields if they are marked as PayPal order fields.
	 *
	 * @since x.x
	 *
	 * @param bool   $show_normal_field_type Whether to show the field.
	 * @param string $type                   The field type.
	 * @param object $field                  The field object.
	 *
	 * @return bool
	 */
	public static function hide_paypal_order_fields( $show_normal_field_type, $type, $field ) {
		if ( ! in_array( $type, array( 'email', 'name', 'address' ), true ) ) {
			return $show_normal_field_type;
		}

		$is_paypal_order_field = FrmField::get_option( $field, 'is_paypal_order_field' );

		return $is_paypal_order_field ? false : $show_normal_field_type;
	}

	/**
	 * @param array $field The field settings.
	 * @param array $display The display settings for the field.
	 * @param array $values The values associated with the field.
	 *
	 * @return void
	 */
	public static function add_paypal_order_field_note( $field, $display, $values ) {
		// Add a note about PayPal order fields here.
		if ( FrmField::get_option( $field, 'is_paypal_order_field' ) ) {
			echo '<div class="frm_note_style">This is a PayPal order field. It is automatically populated when a payment is processed, and is automatically excluded from the form HTML.</div>';
		}
	}
}
