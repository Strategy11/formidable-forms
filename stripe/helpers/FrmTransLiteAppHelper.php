<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLiteAppHelper {

	/**
	 * @var bool|null
	 */
	private static $should_fallback_to_paypal;

	/**
	 * @return string
	 */
	public static function plugin_path() {
		return FrmAppHelper::plugin_path() . '/stripe/';
	}

	/**
	 * @return string
	 */
	public static function plugin_url() {
		return FrmAppHelper::plugin_url() . '/stripe/';
	}

	/**
	 * @return string
	 */
	public static function plugin_folder() {
		return basename( self::plugin_path() );
	}

	/**
	 * Check if the payments table has been created.
	 * This includes either the frm_trans_db_version option (used in Stripe Lite and the Payments submodule) or frm_pay_db_version option (from the PayPal add on).
	 *
	 * @since 6.5
	 * @since 6.5.1 A check for the PayPal add on option
	 * @since 6.5.1 This function was renamed and moved from FrmStrpLiteAppController::payments_are_installed and made public.
	 *
	 * @return bool
	 */
	public static function payments_table_exists() {
		$db     = new FrmTransLiteDb();
		$option = get_option( $db->db_opt_name );
		if ( false !== $option ) {
			return true;
		}

		if ( class_exists( 'FrmPaymentsController' ) && isset( FrmPaymentsController::$db_opt_name ) ) {
			$option = get_option( FrmPaymentsController::$db_opt_name );
			if ( false !== $option ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get a payment status label.
	 *
	 * @param string $status The lowercase payment status value.
	 * @return string
	 */
	public static function show_status( $status ) {
		$statuses = array_merge( self::get_payment_statuses(), self::get_subscription_statuses() );
		return isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status;
	}

	/**
	 * Get Payment status from a payment with support for PayPal backward compatibility.
	 *
	 * @param stdClass $payment
	 * @return string
	 */
	public static function get_payment_status( $payment ) {
		if ( $payment->status ) {
			return $payment->status;
		}
		// PayPal fallback.
		return ! empty( $payment->completed ) ? 'complete' : 'pending';
	}

	/**
	 * @return string[]
	 */
	public static function get_payment_statuses() {
		return array(
			'authorized' => __( 'Authorized', 'formidable' ),
			'pending'    => __( 'Pending', 'formidable' ),
			'complete'   => __( 'Completed', 'formidable' ),
			'failed'     => __( 'Failed', 'formidable' ),
			'refunded'   => __( 'Refunded', 'formidable' ),
			'canceled'   => __( 'Canceled', 'formidable' ),
			'processing' => __( 'Processing', 'formidable' ),
		);
	}

	/**
	 * @return string[]
	 */
	public static function get_subscription_statuses() {
		return array(
			'pending'       => __( 'Pending', 'formidable' ),
			'active'        => __( 'Active', 'formidable' ),
			'future_cancel' => __( 'Canceled', 'formidable' ),
			'canceled'      => __( 'Canceled', 'formidable' ),
			'void'          => __( 'Void', 'formidable' ),
		);
	}

	/**
	 * Add a note to payment data that will get saved to the payment meta.
	 * This is called when processing events in the Stripe add on.
	 *
	 * @param array  $payment_values
	 * @param string $message
	 * @return void
	 */
	public static function add_note_to_payment( &$payment_values, $message = '' ) {
		if ( ! $message ) {
			$message = sprintf(
				// translators: %s: Payment status.
				__( 'Payment %s', 'formidable' ),
				$payment_values['status']
			);
		}
		$payment_values['meta_value'] = isset( $payment_values['meta_value'] ) ? $payment_values['meta_value'] : array();
		$payment_values['meta_value'] = self::add_meta_to_payment( $payment_values['meta_value'], $message );
	}

	/**
	 * @param array|string $meta_value
	 * @param string       $note
	 *
	 * @return array
	 */
	public static function add_meta_to_payment( $meta_value, $note ) {
		$meta_value   = (array) maybe_unserialize( $meta_value );
		$meta_value[] = array(
			'message' => $note,
			'date'    => gmdate( 'Y-m-d H:i:s' ),
		);
		return $meta_value;
	}

	/**
	 * @param string $option
	 * @param array  $atts
	 */
	public static function get_action_setting( $option, $atts ) {
		$settings = self::get_action_settings( $atts );
		$value    = isset( $settings[ $option ] ) ? $settings[ $option ] : '';
		return $value;
	}

	/**
	 * @param array $atts
	 */
	public static function get_action_settings( $atts ) {
		if ( ! isset( $atts['payment'] ) ) {
			return array();
		}

		$atts['payment'] = (array) $atts['payment'];
		if ( empty( $atts['payment']['action_id'] ) ) {
			return array();
		}

		$form_action = FrmTransLiteAction::get_single_action_type( $atts['payment']['action_id'], 'payment' );
		if ( ! $form_action ) {
			return array();
		}

		return $form_action->post_content;
	}

	/**
	 * Allow entry values, default values, and other shortcodes
	 *
	 * @param array $atts Includes value (required), form, entry.
	 * @return int|string
	 */
	public static function process_shortcodes( $atts ) {
		$value = $atts['value'];
		if ( strpos( $value, '[' ) === false ) {
			return $value;
		}

		if ( is_callable( 'FrmProFieldsHelper::replace_non_standard_formidable_shortcodes' ) ) {
			FrmProFieldsHelper::replace_non_standard_formidable_shortcodes( array(), $value );
		}

		if ( ! empty( $atts['entry'] ) ) {
			if ( ! isset( $atts['form'] ) ) {
				$atts['form'] = FrmForm::getOne( $atts['entry']->form_id );
			}
			$value = apply_filters( 'frm_content', $value, $atts['form'], $atts['entry'] );
		}

		$value = do_shortcode( $value );
		return $value;
	}

	/**
	 * @param object $sub
	 * @return string
	 */
	public static function format_billing_cycle( $sub ) {
		$amount   = self::formatted_amount( $sub );
		$interval = self::get_repeat_label_from_value( $sub->time_interval, $sub->interval_count );
		if ( $sub->interval_count == 1 ) {
			$amount = $amount . '/' . $interval;
		} else {
			$amount = $amount . ' every ' . $sub->interval_count . ' ' . $interval;
		}
		return $amount;
	}

	/**
	 * @return array
	 */
	public static function get_repeat_times() {
		return array(
			'day'   => __( 'day(s)', 'formidable' ),
			'week'  => __( 'week(s)', 'formidable' ),
			'month' => __( 'month(s)', 'formidable' ),
			'year'  => __( 'year(s)', 'formidable' ),
		);
	}

	/**
	 * @since 6.5, introduced in v1.16 of the Payments submodule.
	 *
	 * @param int $number
	 * @return array
	 */
	private static function get_plural_repeat_times( $number ) {
		return array(
			'day'   => _n( 'day', 'days', $number, 'formidable' ),
			'week'  => _n( 'week', 'weeks', $number, 'formidable' ),
			'month' => _n( 'month', 'months', $number, 'formidable' ),
			'year'  => _n( 'year', 'years', $number, 'formidable' ),
		);
	}

	/**
	 * @since 6.5, introduced in v1.16 of the Payments submodule.
	 *
	 * @param string $value
	 * @param int    $number
	 * @return string
	 */
	public static function get_repeat_label_from_value( $value, $number ) {
		$times = self::get_plural_repeat_times( $number );
		if ( isset( $times[ $value ] ) ) {
			$value = $times[ $value ];
		}
		return $value;
	}

	public static function formatted_amount( $payment ) {
		$currency = '';
		$amount   = $payment;

		if ( is_object( $payment ) || is_array( $payment ) ) {
			$payment  = (array) $payment;
			$amount   = $payment['amount'];
			$currency = self::get_action_setting( 'currency', array( 'payment' => $payment ) );
		}

		if ( ! $currency ) {
			$currency = 'usd';
		}

		$currency = FrmCurrencyHelper::get_currency( $currency );

		self::format_amount_for_currency( $currency, $amount );

		return $amount;
	}

	/**
	 * Gets amount and currency from payment object or amount.
	 *
	 * @since 6.7
	 *
	 * @param array|float|object|string $payment Payment object, payment array or amount.
	 * @return array Return the array with the first element is the amount, the second one is the currency value.
	 */
	public static function get_amount_and_currency_from_payment( $payment ) {
		$currency = '';
		$amount   = $payment;

		if ( is_object( $payment ) || is_array( $payment ) ) {
			$payment  = (array) $payment;
			$amount   = $payment['amount'];
			$currency = self::get_action_setting( 'currency', array( 'payment' => $payment ) );
		}

		if ( ! $currency ) {
			$currency = 'usd';
		}

		return array( $amount, $currency );
	}

	/**
	 * @param array $currency
	 * @param float $amount
	 * @return void
	 */
	public static function format_amount_for_currency( $currency, &$amount ) {
		$amount       = number_format( $amount, $currency['decimals'], $currency['decimal_separator'], $currency['thousand_separator'] );
		$left_symbol  = $currency['symbol_left'] . $currency['symbol_padding'];
		$right_symbol = $currency['symbol_padding'] . $currency['symbol_right'];
		$amount       = $left_symbol . $amount . $right_symbol;
	}

	/**
	 * @return string
	 */
	public static function get_date_format() {
		$date_format = 'm/d/Y';
		if ( class_exists( 'FrmProAppHelper' ) ) {
			$frmpro_settings = FrmProAppHelper::get_settings();
			if ( $frmpro_settings ) {
				$date_format = $frmpro_settings->date_format;
			}
		} else {
			$date_format = get_option( 'date_format' );
		}

		return $date_format;
	}

	/**
	 * @param string $date
	 * @param string $format
	 * @return string
	 */
	public static function format_the_date( $date, $format = '' ) {
		if ( empty( $format ) ) {
			$format = self::get_date_format();
		}
		return date_i18n( $format, strtotime( $date ) );
	}

	/**
	 * Set a user id for current payment if a user is logged in.
	 *
	 * @return int
	 */
	public static function get_user_id_for_current_payment() {
		$user_id = 0;
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
		}
		return $user_id;
	}

	/**
	 * @param int $user_id
	 * @return string
	 */
	public static function get_user_link( $user_id ) {
		$user_link = esc_html__( 'Guest', 'formidable' );
		if ( $user_id ) {
			$user = get_userdata( $user_id );
			if ( $user ) {
				$user_link = '<a href="' . esc_url( admin_url( 'user-edit.php?user_id=' . $user_id ) ) . '">' . esc_html( $user->display_name ) . '</a>';
			}
		}
		return $user_link;
	}

	/**
	 * @param mixed  $value
	 * @param string $label
	 * @return void
	 */
	public static function show_in_table( $value, $label ) {
		if ( ! empty( $value ) ) { ?>
			<tr>
				<th scope="row"><?php echo esc_html( $label ); ?>:</th>
				<td>
					<?php echo esc_html( $value ); ?>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Echo a link that includes a data-deleteconfirm attribute.
	 * This includes refund links and links to cancel a subscription.
	 *
	 * @since 6.5
	 *
	 * @param string $link
	 * @return void
	 */
	public static function echo_confirmation_link( $link ) {
		$filter = __CLASS__ . '::allow_deleteconfirm_data_attribute';
		add_filter( 'frm_striphtml_allowed_tags', $filter );
		echo FrmAppHelper::kses( $link, array( 'a' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		remove_filter( 'frm_striphtml_allowed_tags', $filter );
	}

	/**
	 * Allow the data-deleteconfirm attribute for confirmation links.
	 * The attribute is used for the confirmation message.
	 *
	 * @since 6.5
	 *
	 * @param array $allowed
	 * @return array
	 */
	public static function allow_deleteconfirm_data_attribute( $allowed ) {
		$allowed['a']['data-deleteconfirm'] = true;
		$allowed['a']['data-frmverify']     = true;
		$allowed['a']['data-frmverify-btn'] = true;
		return $allowed;
	}

	/**
	 * Formats non zero-decimal currencies.
	 *
	 * @since 6.5
	 *
	 * @param int|string $amount
	 * @param WP_Post    $action
	 *
	 * @return string
	 */
	public static function get_formatted_amount_for_currency( $amount, $action ) {
		if ( ! isset( $action->post_content['currency'] ) ) {
			return $amount;
		}

		$currency = FrmCurrencyHelper::get_currency( $action->post_content['currency'] );
		if ( ! empty( $currency['decimals'] ) ) {
			$amount = number_format( $amount / 100, 2, '.', '' );
		}

		return $amount;
	}

	/**
	 * @return bool
	 */
	public static function should_fallback_to_paypal() {
		if ( isset( self::$should_fallback_to_paypal ) ) {
			return self::$should_fallback_to_paypal;
		}

		if ( ! class_exists( 'FrmPaymentsController' ) || ! isset( FrmPaymentsController::$db_opt_name ) ) {
			self::$should_fallback_to_paypal = false;
			return false;
		}

		$db     = new FrmTransLiteDb();
		$option = get_option( $db->db_opt_name );
		if ( false !== $option ) {
			// Don't fallback to PayPal if Stripe migrations have run.
			self::$should_fallback_to_paypal = false;
			return false;
		}

		$option                          = get_option( FrmPaymentsController::$db_opt_name );
		self::$should_fallback_to_paypal = false !== $option;

		return self::$should_fallback_to_paypal;
	}

	/**
	 * Get a human readable translated 'Test' or 'Live' string if the column value is defined.
	 * Old payments will just output an empty string.
	 *
	 * @since 6.6
	 *
	 * @param stdClass $payment
	 * @return string
	 */
	public static function get_test_mode_display_string( $payment ) {
		if ( ! isset( $payment->test ) ) {
			return '';
		}
		return $payment->test ? __( 'Test', 'formidable' ) : __( 'Live', 'formidable' );
	}
}
