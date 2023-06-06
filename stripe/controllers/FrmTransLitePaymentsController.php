<?php

class FrmTransLitePaymentsController extends FrmTransLiteCRUDController {

	/**
	 * @return void
	 */
	public static function menu() {
		$frm_settings = FrmAppHelper::get_settings();

		remove_action( 'admin_menu', 'FrmPaymentsController::menu', 26 ); // Removes the PayPal submenu (PayPal payments will just appear in the regular Payments page).
		add_submenu_page( 'formidable', $frm_settings->menu . ' | Payments', 'Payments', 'frm_view_entries', 'formidable-payments', 'FrmTransLitePaymentsController::route' );
	}

	/**
	 * @return void
	 */
	public static function route() {
		$action = isset( $_REQUEST['frm_action'] ) ? 'frm_action' : 'action';
		$action = FrmAppHelper::get_param( $action, '', 'get', 'sanitize_title' );
		$type   = FrmAppHelper::get_param( 'type', '', 'get', 'sanitize_title' );

		$class_name = $type === 'subscriptions' ? 'FrmTransLiteSubscriptionsController' : 'FrmTransLitePaymentsController';
		if ( $action === 'new' ) {
			self::new_payment();
		} elseif ( method_exists( $class_name, $action ) ) {
			$class_name::$action();
		} else {
			FrmTransLiteListsController::route( $action );
		}
	}

	/**
	 * @return void
	 */
	private static function new_payment() {
		self::get_new_vars();
	}

	/**
	 * @return void
	 */
	private static function create() {
		$frm_payment = new FrmTransLitePayment();
		$id          = $frm_payment->create( $_POST );

		if ( ! $id ) {
			$message = __( 'There was a problem creating that payment', 'formidable' );
			self::get_new_vars( $message );
			return;
		}

		$message = __( 'Payment was Successfully Created', 'formidable' );
		self::get_edit_vars( $id, '', $message );
	}

	/**
	 * @param string $error
	 *
	 * @return void
	 */
	private static function get_new_vars( $error = '' ) {
		global $wpdb;

		$frm_payment  = new FrmTransLitePayment();
		$get_defaults = $frm_payment->get_defaults();
		$defaults     = array();
		foreach ( $get_defaults as $name => $values ) {
			$defaults[ $name ] = $values['default'];
		}
		$defaults['paysys'] = 'manual';

		$payment = (object) array();
		foreach ( $defaults as $var => $default ) {
			$payment->$var = FrmAppHelper::get_param( $var, $default, 'post', 'sanitize_text_field' );
		}

		$currency = FrmCurrencyHelper::get_currency( 'usd' );

		include FrmTransLiteAppHelper::plugin_path() . '/views/payments/new.php';
	}

	/**
	 * @param object $payment
	 * @return void
	 */
	public static function load_sidebar_actions( $payment ) {
		FrmTransLiteActionsController::actions_js();

		$icon        = $payment->status === 'complete' ? 'yes' : 'no-alt';
		$date_format = __( 'M j, Y @ G:i' );
		$created_at  = FrmAppHelper::get_localized_date( $date_format, $payment->created_at );
		include FrmTransLiteAppHelper::plugin_path() . '/views/payments/sidebar_actions.php';
	}

	/**
	 * Echo a receipt link.
	 *
	 * @param object $payment
	 * @return void
	 */
	public static function show_receipt_link( $payment ) {
		$link = esc_html( $payment->receipt_id );
		if ( $payment->receipt_id !== 'None' ) {
			/**
			 * Filter a receipt link for a specific gateway.
			 * For example, Stripe uses frm_pay_stripe_receipt.
			 *
			 * @param string $link
			 */
			$link = apply_filters( 'frm_pay_' . $payment->paysys . '_receipt', $link );
		}

		echo FrmAppHelper::kses( $link, array( 'a' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Echo a refund link.
	 *
	 * @param object $payment
	 * @return void
	 */
	public static function show_refund_link( $payment ) {
		$link = self::refund_link( $payment );
		FrmTransLiteAppHelper::echo_confirmation_link( $link );
	}

	/**
	 * Get a refund link.
	 *
	 * @param object $payment
	 * @return string
	 */
	public static function refund_link( $payment ) {
		if ( $payment->status === 'refunded' ) {
			$link = esc_html__( 'Refunded', 'formidable' );
		} else {
			$confirm = __( 'Are you sure you want to refund that payment?', 'formidable' );

			$link  = admin_url( 'admin-ajax.php?action=frm_trans_refund&payment_id=' . $payment->id . '&nonce=' . wp_create_nonce( 'frm_trans_ajax' ) );
			$link  = '<a href="' . esc_url( $link ) . '" class="frm_trans_ajax_link" data-deleteconfirm="' . esc_attr( $confirm ) . '">';
			$link .= esc_html__( 'Refund', 'formidable' );
			$link .= '</a>';
		}

		/**
		 * Filter the refund link for a specific gateway.
		 * For example, Stripe uses frm_pay_stripe_refund_link.
		 *
		 * @param string $link
		 * @param object $payment
		 */
		$link = apply_filters( 'frm_pay_' . $payment->paysys . '_refund_link', $link, $payment );

		return $link;
	}

	/**
	 * Process the ajax request to refund a payment.
	 *
	 * @return void
	 */
	public static function refund_payment() {
		// TODO If this isn't Lite, use the Payments submodule.

		FrmAppHelper::permission_check( 'frm_edit_entries' );
		check_ajax_referer( 'frm_trans_ajax', 'nonce' );

		$payment_id = FrmAppHelper::get_param( 'payment_id', '', 'get', 'sanitize_text_field' );
		if ( $payment_id ) {
			$frm_payment = new FrmTransLitePayment();
			$payment     = $frm_payment->get_one( $payment_id );

			$class_name = FrmTransLiteAppHelper::get_setting_for_gateway( $payment->paysys, 'class' );
			$refunded   = FrmStrpLiteAppHelper::call_stripe_helper_class( 'refund_payment', $payment->receipt_id );
			if ( $refunded ) {
				self::change_payment_status( $payment, 'refunded' );
				$message = __( 'Refunded', 'formidable' );
			} else {
				$message = __( 'Failed', 'formidable' );
			}
		} else {
			$message = __( 'Oops! No payment was selected for refund.', 'formidable' );
		}

		echo esc_html( $message );
		wp_die();
	}

	/**
	 * Update the status of a payment.
	 *
	 * @param object $payment
	 * @param string $status
	 * @return void
	 */
	public static function change_payment_status( $payment, $status ) {
		$frm_payment = new FrmTransLitePayment();
		if ( $status != $payment->status ) {
			$frm_payment->update( $payment->id, array( 'status' => $status ) );
			FrmTransLiteActionsController::trigger_payment_status_change( compact( 'status', 'payment' ) );
		}
	}
}
