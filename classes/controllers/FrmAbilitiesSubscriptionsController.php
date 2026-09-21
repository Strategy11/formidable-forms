<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Registers the Formidable subscription abilities for the WordPress Abilities API.
 *
 * Subscriptions are reviewed and managed here, not originated: list, get,
 * delete, and cancel cover the same review and cleanup workflow the Payments
 * admin screen's Subscriptions view already supports. Create and edit are
 * deliberately left out, since an ability is not a way to start a subscription.
 *
 * @since x.x
 */
class FrmAbilitiesSubscriptionsController {

	/**
	 * Register the subscription abilities.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function register_abilities() {
		self::register_list_subscriptions_ability();
		self::register_get_subscription_ability();
		self::register_delete_subscription_ability();
		self::register_cancel_subscription_ability();
	}

	/**
	 * Register the list subscriptions ability.
	 *
	 * @return void
	 */
	private static function register_list_subscriptions_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/list-subscriptions',
			array(
				'label'               => __( 'List Subscriptions', 'formidable' ),
				'description'         => __(
					'List subscriptions, optionally filtered by form and status. Use list-forms for the form_id.',
					'formidable'
				),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'form_id'   => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Only list subscriptions for entries on this form. Optional.', 'formidable' ),
						),
						'status'    => array(
							'type'        => 'string',
							'description' => __( 'Filter by subscription status, e.g. active, future_cancel, canceled, pending. Optional.', 'formidable' ),
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
					'description'          => __( 'Array of subscription objects keyed by subscription ID.', 'formidable' ),
					'additionalProperties' => array(
						'type'       => 'object',
						'properties' => self::subscription_properties(),
					),
				),
				'execute_callback'    => 'FrmAbilitiesSubscriptionsController::execute_list_subscriptions',
				'permission_callback' => 'FrmAbilitiesSubscriptionsController::can_list_subscriptions',
				'meta'                => FrmAbilitiesHelper::meta( true, false, true ),
			)
		);
	}

	/**
	 * Register the get subscription ability.
	 *
	 * @return void
	 */
	private static function register_get_subscription_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/get-subscription',
			array(
				'label'               => __( 'Get Subscription', 'formidable' ),
				'description'         => __( 'Retrieve a single subscription by ID.', 'formidable' ),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Subscription ID. Required.', 'formidable' ),
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'Subscription object.', 'formidable' ),
					'properties'  => self::subscription_properties(),
				),
				'execute_callback'    => 'FrmAbilitiesSubscriptionsController::execute_get_subscription',
				'permission_callback' => 'FrmAbilitiesSubscriptionsController::can_get_subscription',
				'meta'                => FrmAbilitiesHelper::meta( true, false, true ),
			)
		);
	}

	/**
	 * Register the delete subscription ability.
	 *
	 * @return void
	 */
	private static function register_delete_subscription_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/delete-subscription',
			array(
				'label'               => __( 'Delete Subscription', 'formidable' ),
				'description'         => __(
					'Delete a subscription record. This does not cancel it with the gateway. Use cancel-subscription to cancel.',
					'formidable'
				),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Subscription ID. Required.', 'formidable' ),
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'Deleted subscription object.', 'formidable' ),
					'properties'  => self::subscription_properties(),
				),
				'execute_callback'    => 'FrmAbilitiesSubscriptionsController::execute_delete_subscription',
				'permission_callback' => 'FrmAbilitiesSubscriptionsController::can_delete_subscription',
				'meta'                => FrmAbilitiesHelper::meta( false, true, false ),
			)
		);
	}

	/**
	 * Register the cancel subscription ability.
	 *
	 * @return void
	 */
	private static function register_cancel_subscription_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/cancel-subscription',
			array(
				'label'               => __( 'Cancel Subscription', 'formidable' ),
				'description'         => __(
					'Cancel a subscription through its original gateway (Stripe, Square, or PayPal). Fails if the gateway declines the cancelation.',
					'formidable'
				),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Subscription ID. Required.', 'formidable' ),
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'Canceled subscription object, with status set to future_cancel.', 'formidable' ),
					'properties'  => self::subscription_properties(),
				),
				'execute_callback'    => 'FrmAbilitiesSubscriptionsController::execute_cancel_subscription',
				'permission_callback' => 'FrmAbilitiesSubscriptionsController::can_cancel_subscription',
				'meta'                => FrmAbilitiesHelper::meta( false, true, false ),
			)
		);
	}

	/**
	 * List subscriptions, optionally filtered by form and status.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_list_subscriptions( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$rows = self::build_list_query( 'frm_subscriptions', $input );

		if ( is_wp_error( $rows ) ) {
			return $rows;
		}

		$data = array();

		foreach ( $rows as $row ) {
			$data[ $row->id ] = self::prepare_subscription_for_response( $row );
		}

		return $data;
	}

	/**
	 * Get one subscription.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_get_subscription( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$subscription = self::get_subscription( $input );

		return is_wp_error( $subscription ) ? $subscription : self::prepare_subscription_for_response( $subscription );
	}

	/**
	 * Delete a subscription.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_delete_subscription( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$subscription = self::get_subscription( $input );

		if ( is_wp_error( $subscription ) ) {
			return $subscription;
		}

		// Read the subscription before it is gone, so the caller gets back what it deleted.
		$data = self::prepare_subscription_for_response( $subscription );

		( new FrmTransLiteSubscription() )->destroy( $subscription->id );

		return $data;
	}

	/**
	 * Cancel a subscription through its original gateway.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_cancel_subscription( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$subscription = self::get_subscription( $input );

		if ( is_wp_error( $subscription ) ) {
			return $subscription;
		}

		$result = FrmTransLiteSubscriptionsController::cancel_subscription_for_gateway( $subscription );

		if ( ! $result['canceled'] ) {
			return new WP_Error(
				'frm_subscription_cancel_failed',
				$result['reason'] ? $result['reason'] : __( 'The payment gateway declined the cancelation.', 'formidable' ),
				array( 'status' => 500 )
			);
		}

		$subscription->status = 'future_cancel';

		return self::prepare_subscription_for_response( $subscription );
	}

	/**
	 * Load one subscription by the id in the input, validating it is present.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return object|WP_Error
	 */
	private static function get_subscription( $input ) {
		if ( empty( $input['id'] ) ) {
			return new WP_Error( 'frm_subscriptions_missing_id', __( 'A subscription ID is required.', 'formidable' ), array( 'status' => 400 ) );
		}

		$subscription = ( new FrmTransLiteSubscription() )->get_one( absint( $input['id'] ) );

		if ( ! $subscription ) {
			return new WP_Error( 'frm_subscription_not_found', __( 'No subscription was found with that ID.', 'formidable' ), array( 'status' => 404 ) );
		}

		return $subscription;
	}

	/**
	 * Query a page of rows from a payments/subscriptions style table.
	 *
	 * Shared shape with FrmAbilitiesPaymentsController::build_list_query(),
	 * kept separate per table since the join/filter columns differ per domain.
	 *
	 * @since x.x
	 *
	 * @param string $table Unprefixed table name, e.g. frm_subscriptions.
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
	 * Build the response shape for one subscription.
	 *
	 * @since x.x
	 *
	 * @param object $subscription The subscription row to describe.
	 *
	 * @return array
	 */
	private static function prepare_subscription_for_response( $subscription ) {
		return array(
			'id'             => (int) $subscription->id,
			'item_id'        => (int) $subscription->item_id,
			'action_id'      => (int) $subscription->action_id,
			'sub_id'         => (string) $subscription->sub_id,
			'amount'         => (float) $subscription->amount,
			'first_amount'   => (float) $subscription->first_amount,
			'interval_count' => (int) $subscription->interval_count,
			'time_interval'  => (string) $subscription->time_interval,
			'fail_count'     => (int) $subscription->fail_count,
			'end_count'      => (int) $subscription->end_count,
			'next_bill_date' => (string) $subscription->next_bill_date,
			'status'         => (string) $subscription->status,
			'paysys'         => (string) $subscription->paysys,
			'created_at'     => (string) $subscription->created_at,
			'test'           => null === $subscription->test ? null : (bool) $subscription->test,
		);
	}

	/**
	 * Output schema properties shared by every subscription ability.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private static function subscription_properties() {
		return array(
			'id'             => array(
				'type'        => array( 'string', 'integer' ),
				'description' => __( 'Numeric subscription ID', 'formidable' ),
			),
			'item_id'        => array(
				'type'        => array( 'string', 'integer' ),
				'description' => __( 'ID of the entry this subscription belongs to', 'formidable' ),
			),
			'action_id'      => array(
				'type'        => array( 'string', 'integer' ),
				'description' => __( 'ID of the form action (e.g. payment action) that started this subscription', 'formidable' ),
			),
			'sub_id'         => array(
				'type'        => 'string',
				'description' => __( 'Gateway subscription ID', 'formidable' ),
			),
			'amount'         => array(
				'type'        => 'number',
				'description' => __( 'Recurring billing amount', 'formidable' ),
			),
			'first_amount'   => array(
				'type'        => 'number',
				'description' => __( 'First payment amount, when different from the recurring amount', 'formidable' ),
			),
			'interval_count' => array(
				'type'        => 'integer',
				'description' => __( 'Number of time_interval units between billings, e.g. 1 with time_interval month means monthly', 'formidable' ),
			),
			'time_interval'  => array(
				'type'        => 'string',
				'description' => __( 'Billing interval unit, e.g. day, week, month, year', 'formidable' ),
			),
			'fail_count'     => array(
				'type'        => 'integer',
				'description' => __( 'Number of consecutive failed billing attempts', 'formidable' ),
			),
			'end_count'      => array(
				'type'        => 'integer',
				'description' => __( 'Number of billing cycles before the subscription ends. 9999 or greater means unlimited', 'formidable' ),
			),
			'next_bill_date' => array(
				'type'        => 'string',
				'description' => __( 'Next billing date, in MySQL date format', 'formidable' ),
			),
			'status'         => array(
				'type'        => 'string',
				'description' => __( 'Subscription status, e.g. active, future_cancel, canceled, pending', 'formidable' ),
			),
			'paysys'         => array(
				'type'        => 'string',
				'description' => __( 'Gateway that manages the subscription, e.g. stripe, square, paypal', 'formidable' ),
			),
			'created_at'     => array(
				'type'        => 'string',
				'description' => __( 'Subscription creation date in MySQL format', 'formidable' ),
			),
			'test'           => array(
				'type'        => array( 'boolean', 'null' ),
				'description' => __( 'Whether this subscription was created in test mode. Null when unknown.', 'formidable' ),
			),
		);
	}

	/**
	 * Permission callback for list subscriptions.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_list_subscriptions( $input ) {
		return current_user_can( 'frm_view_entries' ) || current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for get subscription.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_get_subscription( $input ) {
		return current_user_can( 'frm_view_entries' ) || current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for delete subscription.
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
	public static function can_delete_subscription( $input ) {
		return current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for cancel subscription.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_cancel_subscription( $input ) {
		return current_user_can( 'frm_edit_entries' ) || current_user_can( 'administrator' );
	}
}
