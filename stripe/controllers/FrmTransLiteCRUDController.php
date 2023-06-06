<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLiteCRUDController {

	/**
	 * Show a table of either payments for subscriptions.
	 *
	 * @param int $id
	 * @return void
	 */
	public static function show( $id = 0 ) {
		if ( ! $id ) {
			$id = FrmAppHelper::get_param( 'id', 0, 'get', 'sanitize_text_field' );
			if ( ! $id ) {
				wp_die( esc_html__( 'Please select a payment to view', 'formidable' ) );
			}
		}

		$table_name = self::table_name();

		global $wpdb;
		$payment = $wpdb->get_row( $wpdb->prepare( "SELECT p.*, e.user_id FROM {$wpdb->prefix}frm_" . $table_name . " p LEFT JOIN {$wpdb->prefix}frm_items e ON (p.item_id = e.id) WHERE p.id=%d", $id ) );

		$date_format = get_option( 'date_format' );
		$user_name   = FrmTransLiteAppHelper::get_user_link( $payment->user_id );

		if ( $table_name !== 'payments' ) {
			$subscription = $payment;
		}

		include FrmTransLiteAppHelper::plugin_path() . '/views/' . $table_name . '/show.php';
	}

	/**
	 * Handle routing for deleting a payment.
	 *
	 * @return void
	 */
	public static function destroy() {
		FrmAppHelper::permission_check( 'administrator' );

		$message     = '';
		$frm_payment = self::the_class();
		if ( $frm_payment->destroy( FrmAppHelper::get_param( 'id' ) ) ) {
			$message = __( 'Payment was Successfully Deleted', 'formidable' );
		}

		FrmTransLiteListsController::display_list( compact( 'message' ) );
	}

	/**
	 * @return void
	 */
	public static function edit() {
		$id = FrmAppHelper::get_param( 'id' );
		self::get_edit_vars( $id );
	}

	/**
	 * Handle routing to update a payment.
	 *
	 * @return void
	 */
	public static function update() {
		FrmAppHelper::permission_check( 'administrator' );

		$id          = FrmAppHelper::get_param( 'id' );
		$message     = '';
		$error       = '';
		$frm_payment = self::the_class();
		if ( $frm_payment->update( $id, $_POST ) ) {
			$message = __( 'Payment was Successfully Updated', 'formidable' );
		} else {
			$error = __( 'There was a problem updating that payment', 'formidable' );
		}

		self::get_edit_vars( $id, $error, $message );
	}

	/**
	 * @param mixed  $id
	 * @param mixed  $errors
	 * @param string $message
	 * @return void
	 */
	public static function get_edit_vars( $id, $errors = '', $message = '' ) {
		if ( ! $id ) {
			die( esc_html__( 'Please select a payment to view', 'formidable' ) );
		}

		if ( ! current_user_can( 'frm_edit_entries' ) ) {
			self::show( $id );
			return;
		}

		$table_name = self::table_name();

		global $wpdb;
		$payment = $wpdb->get_row( $wpdb->prepare( "SELECT p.*, e.user_id FROM {$wpdb->prefix}frm_" . $table_name . " p LEFT JOIN {$wpdb->prefix}frm_items e ON (p.item_id = e.id) WHERE p.id=%d", $id ) );

		$currency = FrmTransLiteAppHelper::get_action_setting( 'currency', array( 'payment' => $payment ) );
		$currency = FrmCurrencyHelper::get_currency( $currency );

		if ( $_POST && isset( $_POST['receipt_id'] ) ) {
			foreach ( $payment as $var => $val ) {
				if ( $var === 'id' ) {
					continue;
				}
				$var           = sanitize_text_field( $var );
				$val           = sanitize_text_field( $val );
				$payment->$var = FrmAppHelper::get_param( $var, $val, 'post', 'sanitize_text_field' );
			}
		}

		include FrmTransLiteAppHelper::plugin_path() . '/views/' . $table_name . '/edit.php';
	}

	/**
	 * @return string
	 */
	private static function table_name() {
		$allowed = array( 'payments', 'subscriptions' );
		$default = reset( $allowed );
		$name    = FrmAppHelper::get_param( 'type', $default, 'get', 'sanitize_text_field' );

		if ( ! in_array( $name, $allowed ) ) {
			$name = $default;
		}
		return $name;
	}

	/**
	 * @return FrmTransLiteSubscription|FrmTransLitePayment
	 */
	private static function the_class() {
		$class_name = self::table_name() === 'subscriptions' ? 'FrmTransLiteSubscription' : 'FrmTransLitePayment';
		return new $class_name();
	}
}
