<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLiteSubscriptionsController extends FrmTransLiteCRUDController {

	/**
	 * @param object $subscription
	 * @return void
	 */
	public static function load_sidebar_actions( $subscription ) {
		$date_format = __( 'M j, Y @ G:i', 'formidable' );

		FrmTransLiteActionsController::actions_js();

		$frm_payment = new FrmTransLitePayment();
		$payments    = $frm_payment->get_all_by( $subscription->id, 'sub_id' );

		include FrmTransLiteAppHelper::plugin_path() . '/views/subscriptions/sidebar_actions.php';
	}

	/**
	 * @since 6.5
	 *
	 * @param object $subscription
	 * @return void
	 */
	public static function show_receipt_link( $subscription ) {
		$link = esc_html( $subscription->sub_id );
		if ( $subscription->sub_id !== 'None' ) {
			/**
			 * Filter a receipt link for a specific gateway.
			 * For example, Stripe uses frm_sub_stripe_receipt.
			 *
			 * @since 6.5
			 *
			 * @param string $link
			 */
			$link = apply_filters( 'frm_sub_' . $subscription->paysys . '_receipt', $link );
		}

		echo FrmAppHelper::kses( $link, array( 'a' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @param object $sub
	 * @param array  $atts
	 * @return void
	 */
	public static function show_cancel_link( $sub, $atts = array() ) {
		if ( ! isset( $sub->user_id ) ) {
			global $wpdb;
			$sub->user_id = $wpdb->get_var( $wpdb->prepare( 'SELECT user_id FROM ' . $wpdb->prefix . 'frm_items WHERE id=%d', $sub->item_id ) );
		}

		$link = self::cancel_link( $sub, $atts );
		FrmTransLiteAppHelper::echo_confirmation_link( $link );
	}

	/**
	 * @param object $sub
	 * @param array  $atts
	 * @return string
	 */
	public static function cancel_link( $sub, $atts = array() ) {
		$defaults = array(
			'cancel'   => __( 'Cancel', 'formidable' ),
			'canceled' => __( 'Canceled', 'formidable' ),
			'confirm'  => __( 'Are you sure you want to cancel that subscription?', 'formidable' ),
		);
		$atts     = array_merge( $defaults, $atts );

		if ( $sub->status === 'active' ) {
			$link  = admin_url( 'admin-ajax.php?action=frm_trans_cancel&sub=' . $sub->id . '&nonce=' . wp_create_nonce( 'frm_trans_ajax' ) );
			$link  = '<a href="' . esc_url( $link ) . '" class="frm_trans_ajax_link" data-frmverify="' . esc_attr( $atts['confirm'] ) . '" data-frmverify-btn="frm-button-red">';
			$link .= esc_html( $atts['cancel'] );
			$link .= '</a>';
		} else {
			$link = esc_html( $atts['canceled'] );
		}

		return $link;
	}

	/**
	 * Handle routing to cancel a subscription.
	 *
	 * @return void
	 */
	public static function cancel_subscription() {
		check_ajax_referer( 'frm_trans_ajax', 'nonce' );

		$sub_id = FrmAppHelper::get_param( 'sub', '', 'get', 'sanitize_text_field' );
		if ( $sub_id ) {
			$frm_sub = new FrmTransLiteSubscription();
			$sub     = $frm_sub->get_one( $sub_id );
			if ( $sub ) {
				$canceled = FrmStrpLiteAppHelper::call_stripe_helper_class( 'cancel_subscription', $sub->sub_id );
				if ( $canceled ) {
					self::change_subscription_status(
						array(
							'status' => 'future_cancel',
							'sub'    => $sub,
						)
					);

					$message = __( 'Canceled', 'formidable' );
				} else {
					$message = __( 'Failed', 'formidable' );
				}
			} else {
				$message = __( 'That subscription was not found', 'formidable' );
			}
		} else {
			$message = __( 'Oops! No subscription was selected for cancelation.', 'formidable' );
		}//end if

		echo esc_html( $message );
		wp_die();
	}

	/**
	 * @since 6.5, introduced in v1.12 of the Payments submodule.
	 *
	 * @param array $atts
	 * @return void
	 */
	public static function change_subscription_status( $atts ) {
		$frm_sub = new FrmTransLiteSubscription();
		$frm_sub->update( $atts['sub']->id, array( 'status' => $atts['status'] ) );
		$atts['sub']->status = $atts['status'];

		FrmTransLiteActionsController::trigger_subscription_status_change( $atts['sub'] );
	}

	/**
	 * @return string|null
	 */
	public static function list_subscriptions_shortcode() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$frm_sub       = new FrmTransLiteSubscription();
		$subscriptions = $frm_sub->get_all_for_user( get_current_user_id() );
		if ( empty( $subscriptions ) ) {
			return;
		}

		FrmTransLiteActionsController::actions_js();

		ob_start();
		include FrmTransLiteAppHelper::plugin_path() . '/views/subscriptions/list_shortcode.php';
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}
}
