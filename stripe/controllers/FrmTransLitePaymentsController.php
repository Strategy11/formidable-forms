<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLitePaymentsController extends FrmTransLiteCRUDController {

	/**
	 * @return void
	 */
	public static function menu() {
		if ( FrmTransLiteAppHelper::should_fallback_to_paypal() ) {
			return;
		}

		$frm_settings = FrmAppHelper::get_settings();

		// Remove the PayPal submenu (PayPal payments will just appear in the regular Payments page).
		remove_action( 'admin_menu', 'FrmPaymentsController::menu', 26 );

		if ( in_array( FrmAppHelper::simple_get( 'action' ), array( 'edit', 'new' ), true ) && is_callable( 'FrmPaymentsController::route' ) ) {
			// Use the PayPal addon for add new and edit routing if it is active.
			// This is required to support the "edit" link when using the Stripe Lite table view.
			// It is also required for the "Add New" button to work on the payments table page.
			$menu_route = 'FrmPaymentsController::route';
		} else {
			$menu_route = 'FrmTransLitePaymentsController::route';
		}

		add_submenu_page( 'formidable', $frm_settings->menu . ' | Payments', 'Payments', 'frm_view_entries', 'formidable-payments', $menu_route );
	}

	/**
	 * @return void
	 */
	public static function route() {
		$action = isset( $_REQUEST['frm_action'] ) ? 'frm_action' : 'action';
		$action = FrmAppHelper::get_param( $action, '', 'get', 'sanitize_title' );
		$type   = FrmAppHelper::get_param( 'type', '', 'get', 'sanitize_title' );

		$class_name = $type === 'subscriptions' ? 'FrmTransLiteSubscriptionsController' : 'FrmTransLitePaymentsController';
		if ( method_exists( $class_name, $action ) ) {
			$class_name::$action();
			return;
		}

		FrmTransLiteListsController::route( $action );
	}

	/**
	 * @param object $payment
	 * @return void
	 */
	public static function load_sidebar_actions( $payment ) {
		FrmTransLiteActionsController::actions_js();

		$date_format = __( 'M j, Y @ G:i', 'formidable' );
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
		$link   = esc_html( $payment->receipt_id );
		$paysys = $payment->paysys;

		if ( $payment->receipt_id !== 'None' && self::should_filter_receipt_link( $paysys ) ) {
			/**
			 * Filter a receipt link for a specific gateway.
			 * For example, Stripe uses frm_pay_stripe_receipt.
			 *
			 * @param string $link
			 */
			$link = apply_filters( 'frm_pay_' . $paysys . '_receipt', $link );
		}

		echo FrmAppHelper::kses( $link, array( 'a' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @param string $paysys
	 *
	 * @return bool
	 */
	private static function should_filter_receipt_link( $paysys ) {
		$allowed_types = array( 'stripe', 'authnet_aim' );
		return in_array( $paysys, $allowed_types, true );
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
	 * Show a link to a payment entry (unless it is deleted).
	 *
	 * @param object $payment
	 * @return void
	 */
	public static function show_entry_link( $payment ) {
		$entry = FrmDb::get_col( 'frm_items', array( 'id' => $payment->item_id ) );

		if ( ! $entry ) {
			// translators: %d: Entry ID.
			echo esc_html( sprintf( __( '%d (Deleted)', 'formidable' ), $payment->item_id ) );
			return;
		}

		?>
		<a href="?page=formidable-entries&amp;action=show&amp;frm_action=show&amp;id=<?php echo absint( $payment->item_id ); ?>">
			<?php echo absint( $payment->item_id ); ?>
		</a>
		<?php
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
			$link  = '<a href="' . esc_url( $link ) . '" class="frm_trans_ajax_link" data-frmverify="' . esc_attr( $confirm ) . '">';
			$link .= esc_html__( 'Refund', 'formidable' );
			$link .= '</a>';
		}

		$paysys = $payment->paysys;
		if ( self::should_filter_refund_link( $paysys ) ) {
			/**
			 * Filter the refund link for a specific gateway.
			 * For example, Stripe uses frm_pay_stripe_refund_link.
			 *
			 * @param string $link
			 * @param object $payment
			 */
			$link = apply_filters( 'frm_pay_' . $paysys . '_refund_link', $link, $payment );
		}

		return $link;
	}

	/**
	 * @param string $paysys
	 *
	 * @return bool
	 */
	private static function should_filter_refund_link( $paysys ) {
		$allowed_types = array( 'stripe', 'authnet_aim' );
		return in_array( $paysys, $allowed_types, true );
	}

	/**
	 * Process the ajax request to refund a payment.
	 *
	 * @return void
	 */
	public static function refund_payment() {
		FrmAppHelper::permission_check( 'frm_edit_entries' );
		check_ajax_referer( 'frm_trans_ajax', 'nonce' );

		$payment_id = FrmAppHelper::get_param( 'payment_id', '', 'get', 'absint' );
		if ( ! $payment_id ) {
			wp_die( esc_html__( 'Oops! No payment was selected for refund.', 'formidable' ) );
		}

		$frm_payment = new FrmTransLitePayment();
		$payment     = $frm_payment->get_one( $payment_id );
		$refunded    = FrmStrpLiteAppHelper::call_stripe_helper_class( 'refund_payment', $payment->receipt_id );

		if ( $refunded ) {
			self::change_payment_status( $payment, 'refunded' );
			$message = __( 'Refunded', 'formidable' );
		} else {
			$message = __( 'Failed', 'formidable' );
		}

		wp_die( esc_html( $message ) );
	}

	/**
	 * Update the status of a payment.
	 *
	 * @param object $payment
	 * @param string $status
	 * @return void
	 */
	public static function change_payment_status( $payment, $status ) {
		if ( $status === $payment->status ) {
			// The payment status has not actually changed.
			return;
		}

		$frm_payment = new FrmTransLitePayment();
		$frm_payment->update( $payment->id, array( 'status' => $status ) );
		FrmTransLiteActionsController::trigger_payment_status_change( compact( 'status', 'payment' ) );
	}
}
