<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.5, introduced in v3.1 of the Stripe add on.
 */
class FrmStrpLitePaymentTypeHandler {

	/**
	 * @var array
	 */
	private static $types_by_action_id = array();

	/**
	 * When no payment method types are added, default to automatic.
	 *
	 * @since 6.5, introduced in v3.1 of the Stripe add on.
	 *
	 * @param WP_Post $action
	 * @return bool
	 */
	public static function should_use_automatic_payment_methods( $action ) {
		return ! self::get_payment_method_types( $action );
	}

	/**
	 * Get the payment method types for a target Stripe action.
	 *
	 * @since 6.5, introduced in v3.1 of the Stripe add on.
	 *
	 * @param WP_Post $action
	 * @return string[] An empty array is treated as automatic.
	 */
	public static function get_payment_method_types( $action ) {
		if ( ! isset( self::$types_by_action_id[ $action->ID ] ) ) {
			self::$types_by_action_id[ $action->ID ] = self::get_filtered_payment_method_types( $action );
		}
		return self::$types_by_action_id[ $action->ID ];
	}

	/**
	 * @since 6.5, introduced in v3.1 of the Stripe add on.
	 *
	 * @param WP_Post $action
	 * @return string[]
	 */
	private static function get_filtered_payment_method_types( $action ) {
		/**
		 * Allow users to filter payment method types to add possible other options like "us_bank_account".
		 * An empty array is treated as automatic.
		 *
		 * @since 6.5, introduced in v3.1 of the Stripe add on.
		 *
		 * @param array<string> $payment_method_types
		 * @param array $args {
		 *     @type WP_Post $action
		 * }
		 */
		$payment_method_types = apply_filters(
			'frm_stripe_payment_method_types',
			array(),
			array(
				'action'  => $action,
				'form_id' => $action->menu_order,
			)
		);

		if ( ! is_array( $payment_method_types ) ) {
			_doing_it_wrong( __FUNCTION__, 'Payment method types should be an array or the string "automatic". All other values are invalid.', '3.1' );
			// Fallback to automatic when an invalid value is used.
			$payment_method_types = array();
		}

		return $payment_method_types;
	}
}
