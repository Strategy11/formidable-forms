<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Registers the Formidable payment abilities for the WordPress Abilities API.
 *
 * Payments are reviewed and managed here, not originated: list, get, delete,
 * and refund cover the same review and cleanup workflow the Payments admin
 * screen already supports. Create and edit are deliberately left out, since an
 * ability is not a way to originate a charge.
 *
 * @since x.x
 */
class FrmAbilitiesPaymentsController {

	/**
	 * Register the payment abilities.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function register_abilities() {
		self::register_list_payments_ability();
		self::register_get_payment_ability();
		self::register_delete_payment_ability();
		self::register_refund_payment_ability();
	}

	/**
	 * Register the list payments ability.
	 *
	 * @return void
	 */
	private static function register_list_payments_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/list-payments',
			array(
				'label'               => __( 'List Payments', 'formidable' ),
				'description'         => __(
					'List payments, optionally filtered by form and status. Use list-forms for the form_id.',
					'formidable'
				),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'form_id'   => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Only list payments for entries on this form. Optional.', 'formidable' ),
						),
						'status'    => array(
							'type'        => 'string',
							'description' => __( 'Filter by payment status, e.g. complete, pending, refunded, failed. Optional.', 'formidable' ),
						),
						'page'      => array(
							'type'        => 'integer',
							'description' => __( 'Page number. Default 1.', 'formidable' ),
						),
						'page_size' => array(
							'type'        => 'integer',
							'description' => __( 'Results per page, capped at 200. Default 50.', 'formidable' ),
						),
						'order_by'  => array(
							'type'        => 'string',
							'description' => __( 'Column to order by, e.g. created_at, amount, status. Default created_at.', 'formidable' ),
						),
						'order'     => array(
							'type'        => 'string',
							'description' => __( 'Sort direction. Default DESC.', 'formidable' ),
							'enum'        => array( 'ASC', 'DESC', 'asc', 'desc' ),
						),
					),
				),
				'output_schema'       => array(
					'type'                 => 'object',
					'description'          => __( 'Array of payment objects keyed by payment ID.', 'formidable' ),
					'additionalProperties' => array(
						'type'       => 'object',
						'properties' => self::payment_properties(),
					),
				),
				'execute_callback'    => 'FrmAbilitiesPaymentsController::execute_list_payments',
				'permission_callback' => 'FrmAbilitiesPaymentsController::can_list_payments',
				'meta'                => FrmAbilitiesHelper::meta( true, false, true ),
			)
		);
	}

	/**
	 * Register the get payment ability.
	 *
	 * @return void
	 */
	private static function register_get_payment_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/get-payment',
			array(
				'label'               => __( 'Get Payment', 'formidable' ),
				'description'         => __( 'Retrieve a single payment by ID.', 'formidable' ),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Payment ID. Required.', 'formidable' ),
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'Payment object.', 'formidable' ),
					'properties'  => self::payment_properties(),
				),
				'execute_callback'    => 'FrmAbilitiesPaymentsController::execute_get_payment',
				'permission_callback' => 'FrmAbilitiesPaymentsController::can_get_payment',
				'meta'                => FrmAbilitiesHelper::meta( true, false, true ),
			)
		);
	}

	/**
	 * Register the delete payment ability.
	 *
	 * @return void
	 */
	private static function register_delete_payment_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/delete-payment',
			array(
				'label'               => __( 'Delete Payment', 'formidable' ),
				'description'         => __(
					'Delete a payment record. This removes Formidable\'s record of the payment; it does not refund it. Use refund-payment to refund.',
					'formidable'
				),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Payment ID. Required.', 'formidable' ),
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'Deleted payment object.', 'formidable' ),
					'properties'  => self::payment_properties(),
				),
				'execute_callback'    => 'FrmAbilitiesPaymentsController::execute_delete_payment',
				'permission_callback' => 'FrmAbilitiesPaymentsController::can_delete_payment',
				'meta'                => FrmAbilitiesHelper::meta( false, true, false ),
			)
		);
	}

	/**
	 * Register the refund payment ability.
	 *
	 * @return void
	 */
	private static function register_refund_payment_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/refund-payment',
			array(
				'label'               => __( 'Refund Payment', 'formidable' ),
				'description'         => __(
					'Refund a payment through its original gateway (Stripe, Square, or PayPal). Fails if the gateway declines the refund.',
					'formidable'
				),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Payment ID. Required.', 'formidable' ),
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'Refunded payment object, with status set to refunded.', 'formidable' ),
					'properties'  => self::payment_properties(),
				),
				'execute_callback'    => 'FrmAbilitiesPaymentsController::execute_refund_payment',
				'permission_callback' => 'FrmAbilitiesPaymentsController::can_refund_payment',
				'meta'                => FrmAbilitiesHelper::meta( false, true, false ),
			)
		);
	}

	/**
	 * List payments, optionally filtered by form and status.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_list_payments( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$rows = self::build_list_query( 'frm_payments', $input );

		if ( is_wp_error( $rows ) ) {
			return $rows;
		}

		$data = array();

		foreach ( $rows as $row ) {
			$data[ $row->id ] = self::prepare_payment_for_response( $row );
		}

		return $data;
	}

	/**
	 * Get one payment.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_get_payment( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$payment = self::get_payment( $input );

		return is_wp_error( $payment ) ? $payment : self::prepare_payment_for_response( $payment );
	}

	/**
	 * Delete a payment.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_delete_payment( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$payment = self::get_payment( $input );

		if ( is_wp_error( $payment ) ) {
			return $payment;
		}

		// Read the payment before it is gone, so the caller gets back what it deleted.
		$data = self::prepare_payment_for_response( $payment );

		( new FrmTransLitePayment() )->destroy( $payment->id );

		return $data;
	}

	/**
	 * Refund a payment through its original gateway.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_refund_payment( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$payment = self::get_payment( $input );

		if ( is_wp_error( $payment ) ) {
			return $payment;
		}

		$result = FrmTransLitePaymentsController::refund_payment_for_gateway( $payment );

		if ( ! $result['refunded'] ) {
			return new WP_Error(
				'frm_payment_refund_failed',
				$result['reason'] ? $result['reason'] : __( 'The payment gateway declined the refund.', 'formidable' ),
				array( 'status' => 500 )
			);
		}

		$payment->status = 'refunded';

		return self::prepare_payment_for_response( $payment );
	}

	/**
	 * Load one payment by the id in the input, validating it is present.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return object|WP_Error
	 */
	private static function get_payment( $input ) {
		if ( empty( $input['id'] ) ) {
			return new WP_Error( 'frm_payments_missing_id', __( 'A payment ID is required.', 'formidable' ), array( 'status' => 400 ) );
		}

		$payment = ( new FrmTransLitePayment() )->get_one( absint( $input['id'] ) );

		if ( ! $payment ) {
			return new WP_Error( 'frm_payment_not_found', __( 'No payment was found with that ID.', 'formidable' ), array( 'status' => 404 ) );
		}

		return $payment;
	}

	/**
	 * Query a page of rows from a payments/subscriptions style table.
	 *
	 * Shared shape with FrmAbilitiesSubscriptionsController::build_list_query(),
	 * kept separate per table since the join/filter columns differ per domain.
	 *
	 * @since x.x
	 *
	 * @param string $table Unprefixed table name, e.g. frm_payments.
	 * @param array  $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	private static function build_list_query( $table, $input ) {
		global $wpdb;

		list( $order_clause, $limit_clause ) = FrmAbilitiesHelper::prepare_order_and_limit( $input );

		$table_name = $wpdb->prefix . $table;
		$status     = ! empty( $input['status'] ) && is_string( $input['status'] ) ? sanitize_text_field( $input['status'] ) : '';

		if ( ! empty( $input['form_id'] ) ) {
			$form = FrmAbilitiesHelper::get_form( $input['form_id'] );

			if ( is_wp_error( $form ) ) {
				return $form;
			}

			$sql    = 'SELECT p.* FROM %i p JOIN %i i ON p.item_id = i.id WHERE i.form_id = %d';
			$params = array( $table_name, $wpdb->prefix . 'frm_items', $form->id );

			if ( $status ) {
				$sql     .= ' AND p.status = %s';
				$params[] = $status;
			}
		} else {
			$sql    = 'SELECT p.* FROM %i p';
			$params = array( $table_name );

			if ( $status ) {
				$sql     .= ' WHERE p.status = %s';
				$params[] = $status;
			}
		}//end if

		$sql .= $order_clause . $limit_clause;

		// @codingStandardsIgnoreStart
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
		// @codingStandardsIgnoreEnd

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Build the response shape for one payment.
	 *
	 * @since x.x
	 *
	 * @param object $payment The payment row to describe.
	 *
	 * @return array
	 */
	private static function prepare_payment_for_response( $payment ) {
		return array(
			'id'          => (int) $payment->id,
			'item_id'     => (int) $payment->item_id,
			'action_id'   => (int) $payment->action_id,
			'receipt_id'  => (string) $payment->receipt_id,
			'invoice_id'  => (string) $payment->invoice_id,
			'sub_id'      => (string) $payment->sub_id,
			'amount'      => (float) $payment->amount,
			'status'      => (string) $payment->status,
			'paysys'      => (string) $payment->paysys,
			'begin_date'  => (string) $payment->begin_date,
			'expire_date' => (string) $payment->expire_date,
			'created_at'  => (string) $payment->created_at,
			'test'        => null === $payment->test ? null : (bool) $payment->test,
		);
	}

	/**
	 * Output schema properties shared by every payment ability.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private static function payment_properties() {
		return array(
			'id'          => array(
				'type'        => array( 'string', 'integer' ),
				'description' => __( 'Numeric payment ID', 'formidable' ),
			),
			'item_id'     => array(
				'type'        => array( 'string', 'integer' ),
				'description' => __( 'ID of the entry this payment belongs to', 'formidable' ),
			),
			'action_id'   => array(
				'type'        => array( 'string', 'integer' ),
				'description' => __( 'ID of the form action (e.g. payment action) that recorded this payment', 'formidable' ),
			),
			'receipt_id'  => array(
				'type'        => 'string',
				'description' => __( 'Gateway receipt or charge ID', 'formidable' ),
			),
			'invoice_id'  => array(
				'type'        => 'string',
				'description' => __( 'Gateway invoice ID, when applicable', 'formidable' ),
			),
			'sub_id'      => array(
				'type'        => 'string',
				'description' => __( 'ID of the subscription this payment belongs to, when applicable', 'formidable' ),
			),
			'amount'      => array(
				'type'        => 'number',
				'description' => __( 'Payment amount', 'formidable' ),
			),
			'status'      => array(
				'type'        => 'string',
				'description' => __( 'Payment status, e.g. complete, pending, refunded, failed', 'formidable' ),
			),
			'paysys'      => array(
				'type'        => 'string',
				'description' => __( 'Gateway that processed the payment, e.g. stripe, square, paypal', 'formidable' ),
			),
			'begin_date'  => array(
				'type'        => 'string',
				'description' => __( 'Date the payment began, in MySQL date format', 'formidable' ),
			),
			'expire_date' => array(
				'type'        => 'string',
				'description' => __( 'Date the payment expires, in MySQL date format', 'formidable' ),
			),
			'created_at'  => array(
				'type'        => 'string',
				'description' => __( 'Payment creation date in MySQL format', 'formidable' ),
			),
			'test'        => array(
				'type'        => array( 'boolean', 'null' ),
				'description' => __( 'Whether this payment was made in test mode. Null when unknown.', 'formidable' ),
			),
		);
	}

	/**
	 * Permission callback for list payments.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_list_payments( $input ) {
		return current_user_can( 'frm_view_entries' ) || current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for get payment.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_get_payment( $input ) {
		return current_user_can( 'frm_view_entries' ) || current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for delete payment.
	 *
	 * FrmTransLiteDb::destroy() itself hard-requires the administrator
	 * capability, so this matches rather than allowing a role that would only
	 * hit that internal check and fail.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_delete_payment( $input ) {
		return current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for refund payment.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_refund_payment( $input ) {
		return current_user_can( 'frm_edit_entries' ) || current_user_can( 'administrator' );
	}
}
