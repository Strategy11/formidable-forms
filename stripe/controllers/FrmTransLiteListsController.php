<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLiteListsController {

	/**
	 * @return void
	 */
	public static function add_list_hooks() {
		if ( FrmTransLiteAppHelper::should_fallback_to_paypal() ) {
			return;
		}

		$hook_name = 'manage_' . sanitize_title( FrmAppHelper::get_menu_name() ) . '_page_formidable-payments_columns';
		add_filter( $hook_name, __CLASS__ . '::payment_columns' );
		add_filter( 'screen_options_show_screen', __CLASS__ . '::remove_screen_options', 10, 2 );
	}

	/**
	 * @param array $columns
	 * @return array
	 */
	public static function payment_columns( $columns = array() ) {
		add_screen_option(
			'per_page',
			array(
				'label'   => esc_html__( 'Payments', 'formidable' ),
				'default' => 20,
				'option'  => 'formidable_page_formidable_payments_per_page',
			)
		);

		$type = FrmAppHelper::get_simple_request(
			array(
				'param'   => 'trans_type',
				'type'    => 'request',
				'default' => 'payments',
			)
		);

		$columns['cb']      = '<input type="checkbox" />';
		$columns['user_id'] = esc_html__( 'Customer', 'formidable' );

		if ( 'subscriptions' === $type ) {
			$add_columns = array(
				'sub_id'         => esc_html__( 'Profile ID', 'formidable' ),
				'item_id'        => esc_html__( 'Entry', 'formidable' ),
				'form_id'        => esc_html__( 'Form', 'formidable' ),
				'amount'         => esc_html__( 'Billing Cycle', 'formidable' ),
				'end_count'      => esc_html__( 'Payments Made', 'formidable' ),
				'next_bill_date' => esc_html__( 'Next Bill Date', 'formidable' ),
			);
		} else {
			$add_columns = array(
				'receipt_id'  => esc_html__( 'Receipt ID', 'formidable' ),
				'item_id'     => esc_html__( 'Entry', 'formidable' ),
				'form_id'     => esc_html__( 'Form', 'formidable' ),
				'amount'      => esc_html__( 'Amount', 'formidable' ),
				'sub_id'      => esc_html__( 'Subscription', 'formidable' ),
				'begin_date'  => esc_html__( 'Begin Date', 'formidable' ),
				'expire_date' => esc_html__( 'Expire Date', 'formidable' ),
			);
		}

		$columns = $columns + $add_columns;

		$columns['status']     = esc_html__( 'Status', 'formidable' );
		$columns['created_at'] = esc_html__( 'Date', 'formidable' );

		$paypal_is_active = class_exists( 'FrmPaymentsController', false );
		if ( $paypal_is_active ) {
			$columns['paysys'] = esc_html__( 'Processor', 'formidable' );
		}

		$columns['mode'] = esc_html__( 'Mode', 'formidable' );

		return $columns;
	}

	/**
	 * Prevent the "screen options" tab from showing when
	 * viewing a payment or subscription
	 *
	 * @since 6.5
	 */
	public static function remove_screen_options( $show_screen, $screen ) {
		if ( ! in_array( FrmAppHelper::simple_get( 'action', 'sanitize_title' ), array( 'edit', 'show', 'new', 'duplicate' ), true ) ) {
			return $show_screen;
		}

		$menu_name = sanitize_title( FrmAppHelper::get_menu_name() );
		if ( $screen->id === $menu_name . '_page_formidable-payments' ) {
			$show_screen = false;
		}

		return $show_screen;
	}

	/**
	 * Handle payment/subscription list routing.
	 *
	 * @param string $action
	 * @return void
	 */
	public static function route( $action ) {
		self::display_list();
	}

	/**
	 * @return array
	 */
	public static function list_page_params() {
		$values = array();
		foreach ( array(
			'id'     => '',
			'paged'  => 1,
			'form'   => '',
			'search' => '',
			'sort'   => '',
			'sdir'   => '',
		) as $var => $default ) {
			$values[ $var ] = FrmAppHelper::get_param( $var, $default );
		}

		return $values;
	}

	/**
	 * Display a list.
	 *
	 * @param array $response
	 * @return void
	 */
	public static function display_list( $response = array() ) {
		FrmAppHelper::include_svg();

		$defaults = array(
			'errors'  => array(),
			'message' => '',
		);
		$response = array_merge( $defaults, $response );
		$errors   = $response['errors'];
		$message  = $response['message'];

		$wp_list_table = new FrmTransLiteListHelper( self::list_page_params() );

		$pagenum = $wp_list_table->get_pagenum();

		$wp_list_table->prepare_items();

		$total_pages = $wp_list_table->get_pagination_arg( 'total_pages' );
		if ( $pagenum > $total_pages && $total_pages > 0 ) {
			// if the current page is higher than the total pages,
			// reset it and prepare again to get the right entries.
			$_GET['paged']     = $total_pages;
			$_REQUEST['paged'] = $total_pages;
			$pagenum           = $wp_list_table->get_pagenum();
			$wp_list_table->prepare_items();
		}

		include FrmTransLiteAppHelper::plugin_path() . '/views/lists/list.php';
	}

	/**
	 * @param mixed  $save
	 * @param string $option
	 * @param int    $value
	 */
	public static function save_per_page( $save, $option, $value ) {
		if ( $option === 'formidable_page_formidable_payments_per_page' ) {
			$save = absint( $value );
		}
		return $save;
	}
}
