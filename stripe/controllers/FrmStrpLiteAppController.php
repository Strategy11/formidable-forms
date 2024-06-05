<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStrpLiteAppController {

	/**
	 * Flag to delete the previous pay entry.
	 *
	 * @since 2.08
	 *
	 * @var bool
	 */
	private static $delete_pay_entry = false;

	/**
	 * Install required tables.
	 *
	 * @param mixed $old_db_version
	 * @return void
	 */
	public static function install( $old_db_version = false ) {
		FrmTransLiteAppController::install( $old_db_version );
	}

	/**
	 * Remove Stripe database items after uninstall.
	 *
	 * @since 6.5, introduced in v2.07 of the Stripe add on.
	 *
	 * @return void
	 */
	public static function uninstall() {
		if ( ! current_user_can( 'administrator' ) ) {
			$frm_settings = FrmAppHelper::get_settings();
			wp_die( esc_html( $frm_settings->admin_permission ) );
		}

		$options_to_delete = array(
			FrmStrpLiteEventsController::$events_to_skip_option_name,
			'frm_strp_options',
		);

		$modes            = array( 'test', 'live' );
		$option_name_keys = array( 'account_id', 'client_password', 'server_password', 'details_submitted' );
		foreach ( $modes as $mode ) {
			foreach ( $option_name_keys as $key ) {
				$options_to_delete[] = 'frm_strp_connect_' . $key . '_' . $mode;
			}
		}

		foreach ( $options_to_delete as $option_name ) {
			delete_option( $option_name );
		}
	}

	/**
	 * Redirect to Stripe settings when payments are not yet installed
	 * and the payments page is accessed by its URL.
	 *
	 * @return void
	 */
	public static function maybe_redirect_to_stripe_settings() {
		if ( ! FrmAppHelper::is_admin_page( 'formidable-payments' ) || FrmTransLiteAppHelper::payments_table_exists() ) {
			return;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=formidable-settings&t=stripe_settings' ) );
		die();
	}

	/**
	 * Add the gateway for compatibility with the Payments submodule.
	 * This adds the Stripe checkbox option to the list of gateways.
	 *
	 * @param array $gateways
	 * @return array
	 */
	public static function add_gateway( $gateways ) {
		$gateways['stripe'] = array(
			'label'      => 'Stripe',
			'user_label' => __( 'Payment', 'formidable' ),
			'class'      => 'StrpLite',
			'recurring'  => true,
			'include'    => array(
				'billing_first_name',
				'billing_last_name',
				'credit_card',
				'billing_address',
			),
		);
		return $gateways;
	}

	/**
	 * Maybe add payment error to the form errors data.
	 *
	 * @since 6.5.1
	 *
	 * @param array $errors Errors data. Is empty array if no errors found.
	 * @param array $params Form params. See {@FrmForm::get_params()}.
	 * @return array
	 */
	public static function maybe_add_payment_error( $errors, $params ) {
		if ( intval( $params['posted_form_id'] ) !== intval( $params['form_id'] ) ) {
			return self::maybe_add_payment_error_on_redirect( $errors, (int) $params['form_id'] );
		}
		return $errors;
	}

	/**
	 * Handle Stripe Link redirect failures.
	 * When a payment fails, the entry is deleted, and the previous entry's values are loaded in the form.
	 *
	 * @since 6.5.1
	 *
	 * @param array $errors
	 * @param int   $form_id
	 * @return array
	 */
	private static function maybe_add_payment_error_on_redirect( $errors, $form_id ) {
		$details = FrmStrpLiteUrlParamHelper::get_details_for_form( $form_id );
		if ( ! is_array( $details ) ) {
			return $errors;
		}

		$entry          = $details['entry'];
		$intent         = $details['intent'];
		$payment        = $details['payment'];
		$payment_failed = FrmStrpLiteAuth::payment_failed( $payment, $intent );

		// Only add the payment error if the intent is incomplete.
		if ( ! $payment_failed ) {
			return $errors;
		}

		$cc_field_id = FrmDb::get_var(
			'frm_fields',
			array(
				'type'    => 'credit_card',
				'form_id' => $entry->form_id,
			)
		);
		if ( ! $cc_field_id ) {
			return $errors;
		}

		$is_setup_intent = 0 === strpos( $intent->id, 'seti_' );
		if ( $is_setup_intent ) {
			$errors[ 'field' . $cc_field_id ] = is_object( $intent->last_setup_error ) ? $intent->last_setup_error->message : '';
		} else {
			$errors[ 'field' . $cc_field_id ] = is_object( $intent->last_payment_error ) ? $intent->last_payment_error->message : '';
		}

		if ( ! $errors[ 'field' . $cc_field_id ] ) {
			$errors[ 'field' . $cc_field_id ] = 'Payment was not successfully processed.';
		}

		global $frm_vars;
		$frm_vars['frm_trans']['pay_entry'] = $entry;

		self::setup_form_after_payment_error( (int) $entry->form_id, (int) $entry->id, $errors );

		add_filter( 'frm_setup_new_fields_vars', 'FrmTransLiteActionsController::fill_entry_from_previous', 20, 2 );

		return $errors;
	}

	/**
	 * Reset a form after a payment fails.
	 *
	 * @since 6.5.1
	 *
	 * @param int                  $form_id
	 * @param int                  $entry_id
	 * @param array<string,string> $errors
	 * @return void
	 */
	private static function setup_form_after_payment_error( $form_id, $entry_id, $errors ) {
		$form       = FrmForm::getOne( $form_id );
		$save_draft = ! empty( $form->options['save_draft'] );

		global $frm_vars;
		$frm_vars['created_entries'][ $form_id ]['errors'] = $errors;

		// Set to true to get FrmProFieldsHelper::get_page_with_error() run.
		$_POST[ 'frm_page_order_' . $form_id ] = true;

		if ( ! $save_draft ) {
			// If draft saving is not on, delete the entry.
			self::$delete_pay_entry = true;
			return;
		}

		// If draft saving is on, load the draft entry.
		$frm_vars['created_entries'][ $form_id ]['entry_id'] = $entry_id;
		add_action(
			'frm_filter_final_form',
			/**
			 * Set the entry back to draft status after error.
			 *
			 * @param string $html
			 * @param int    $entry_id
			 * @return string
			 */
			function ( $html ) use ( $entry_id ) {
				global $wpdb;
				$wpdb->update( $wpdb->prefix . 'frm_items', array( 'is_draft' => 1 ), array( 'id' => $entry_id ) );
				return $html;
			}
		);
	}

	/**
	 * Maybe delete the previous pay entry when error occurs.
	 *
	 * @since 2.08
	 *
	 * @param array  $values Entry edit values.
	 * @param object $field  Field object.
	 * @return array
	 */
	public static function maybe_delete_pay_entry( $values, $field ) {
		if ( self::$delete_pay_entry ) {
			self::$delete_pay_entry = false;
			return FrmTransLiteActionsController::fill_entry_from_previous( $values, $field );
		}
		return $values;
	}
}
