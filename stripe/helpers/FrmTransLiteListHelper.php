<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmTransLiteListHelper extends FrmListHelper {

	/**
	 * @var string
	 */
	private $table = '';

	/**
	 * An array of all valid entry ids.
	 * This is retrieved all at once with a single database query.
	 * This is used to determine if a specific entry is deleted.
	 * When an entry is deleted, there is no link to the deleted entry.
	 *
	 * @var int[]
	 */
	private $valid_entry_ids = array();

	public function __construct( $args ) {
		$this->table = FrmAppHelper::get_simple_request(
			array(
				'param' => 'trans_type',
				'type'  => 'request',
			)
		);
		parent::__construct( $args );
	}

	/**
	 * @return void
	 */
	public function prepare_items() {
		global $wpdb;

		$orderby = FrmAppHelper::get_param( 'orderby', 'id', 'get', 'sanitize_title' );
		$order   = FrmAppHelper::get_param( 'order', 'DESC', 'get', 'sanitize_text_field' );
		if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
			$order = 'DESC';
		}

		$page        = $this->get_pagenum();
		$per_page    = $this->get_items_per_page( 'formidable_page_formidable_payments_per_page' );
		$start       = ( $page - 1 ) * $per_page;
		$start       = FrmAppHelper::get_param( 'start', $start, 'get', 'absint' );
		$query       = $this->get_table_query();
		$order_query = FrmDb::esc_order( "ORDER BY p.{$orderby} $order" );

		// @codingStandardsIgnoreStart
		$this->items = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * ' . $query . $order_query . ' LIMIT %d, %d',
				$start,
				$per_page
			)
		);
		$total_items = $wpdb->get_var( 'SELECT COUNT(*) ' . $query );
		// @codingStandardsIgnoreEnd

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * @return string
	 */
	private function get_table_query() {
		global $wpdb;

		$table_name = $this->table === 'subscriptions' ? 'frm_subscriptions' : 'frm_payments';
		$form_id    = FrmAppHelper::get_param( 'form', 0, 'get', 'absint' );

		if ( $form_id ) {
			// @codingStandardsIgnoreStart
			$query = $wpdb->prepare(
				"FROM `{$wpdb->prefix}{$table_name}` p
				LEFT JOIN `{$wpdb->prefix}frm_items` i ON p.item_id = i.id
				WHERE i.form_id = %d",
				$form_id
			);
			// @codingStandardsIgnoreEnd
		} else {
			$query = "FROM `{$wpdb->prefix}{$table_name}` p";
		}

		return $query;
	}

	/**
	 * @return void
	 */
	public function no_items() {
		esc_html_e( 'No payments found.', 'formidable' );
	}

	/**
	 * @return array
	 */
	public function get_views() {
		$statuses = array(
			'payments'      => __( 'Payments', 'formidable' ),
			'subscriptions' => __( 'Subscriptions', 'formidable' ),
		);

		$links = array();

		$frm_payment = new FrmTransLitePayment();
		$frm_sub     = new FrmTransLiteSubscription();
		$counts      = array(
			'payments'      => $frm_payment->get_count(),
			'subscriptions' => $frm_sub->get_count(),
		);
		$type        = FrmAppHelper::get_simple_request(
			array(
				'param'   => 'trans_type',
				'type'    => 'request',
				'default' => 'payments',
			)
		);

		foreach ( $statuses as $status => $name ) {
			if ( $status === $type ) {
				$class = ' class="current"';
			} else {
				$class = '';
			}

			if ( $counts[ $status ] || 'published' === $status ) {
				$links[ $status ] = '<a href="' . esc_url( '?page=formidable-payments&trans_type=' . $status ) . '" ' . $class . '>'
					// translators: %1$s: Transaction type (Payments or Subscriptions), %2$s: Span start tag, %3$s: Count, %3$s: Span close tag.
					. sprintf( esc_html__( '%1$s %2$s(%3$s)%4$s', 'formidable' ), esc_html( $name ), '<span class="count">', number_format_i18n( $counts[ $status ] ), '</span>' )
					. '</a>';
			}

			unset( $status, $name );
		}

		return $links;
	}

	/**
	 * @return array
	 */
	public function get_columns() {
		return FrmTransLiteListsController::payment_columns();
	}

	/**
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'item_id'        => 'item_id',
			'amount'         => 'amount',
			'created_at'     => 'created_at',
			'receipt_id'     => 'receipt_id',
			'sub_id'         => 'sub_id',
			'begin_date'     => 'begin_date',
			'expire_date'    => 'expire_date',
			'status'         => 'status',
			'next_bill_date' => 'next_bill_date',
		);
	}

	/**
	 * @param string $which
	 * @return void
	 */
	public function extra_tablenav( $which ) {
		$footer = $which !== 'top';
		if ( $footer ) {
			return;
		}

		$form_id = FrmAppHelper::simple_get( 'form', 'absint', 0 );
		if ( $form_id ) {
			// Don't show the switcher if it's already in the header.
			return;
		}

		FrmFormsHelper::forms_dropdown(
			'form',
			$form_id,
			array( 'blank' => __( 'View all forms', 'formidable' ) )
		);

		echo '<input type="hidden" name="trans_type" value="' . esc_attr( FrmAppHelper::get_param( 'trans_type', 'payments', 'get', 'sanitize_text_field' ) ) . '">';
		echo '<input id="post-query-submit" class="button" type="submit" value="Filter" name="filter_action" />';
	}

	/**
	 * @return void
	 */
	public function display_rows() {
		$date_format = FrmTransLiteAppHelper::get_date_format();

		$alt = 0;

		$form_ids = $this->get_form_ids();
		$args     = compact( 'form_ids', 'date_format' );
		// $form_ids is indexed by entry ID.
		$this->valid_entry_ids = array_keys( $form_ids );

		foreach ( $this->items as $item ) {
			echo '<tr id="payment-' . esc_attr( $item->id ) . '" ';

			$is_alternate = 0 === $alt % 2;
			++$alt;

			if ( $is_alternate ) {
				echo 'class="alternate"';
			}

			echo '>';
			$this->display_columns( $item, $args );
			echo '</tr>';

			unset( $item );
		}
	}

	/**
	 * @param object $item
	 * @param array  $args
	 *
	 * @return void
	 */
	private function display_columns( $item, $args ) {
		list( $columns, $hidden ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$attributes          = self::get_row_classes( compact( 'column_name', 'hidden' ) );
			$args['column_name'] = $column_name;
			$val                 = $this->get_column_value( $item, $args );

			echo '<td ' . $attributes . '>' . $val . '</td>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			unset( $val );
		}
	}

	protected function get_column_info() {
		$column_info = parent::get_column_info();
		// Remove the checkbox column.
		unset( $column_info[0]['cb'] );
		return $column_info;
	}

	/**
	 * @param object $item
	 * @param array  $args
	 */
	private function get_column_value( $item, $args ) {
		$column_name   = $args['column_name'];
		$function_name = 'get_' . $column_name . '_column';

		if ( method_exists( $this, $function_name ) ) {
			$val = $this->$function_name( $item, $args );
		} else {
			$val = $item->$column_name ? $item->$column_name : '';

			if ( strpos( $column_name, '_date' ) !== false ) {
				if ( ! empty( $item->$column_name ) && $item->$column_name !== '0000-00-00' ) {
					$val = FrmTransLiteAppHelper::format_the_date( $item->$column_name, $args['date_format'] );
				} else {
					$val = '';
				}
			}
		}

		return $val;
	}

	/**
	 * @return array
	 */
	private function get_form_ids() {
		$entry_ids = array();
		foreach ( $this->items as $item ) {
			$entry_ids[] = absint( $item->item_id );
			unset( $item );
		}

		global $wpdb;
		// @codingStandardsIgnoreStart
		$forms = $wpdb->get_results(
			"SELECT
				fo.id as form_id,
				fo.name,
				e.id
			FROM {$wpdb->prefix}frm_items e
			LEFT JOIN {$wpdb->prefix}frm_forms fo ON e.form_id = fo.id
			WHERE e.id in (" . implode( ',', $entry_ids ) . ')'
		);
		// @codingStandardsIgnoreEnd
		unset( $entry_ids );

		$form_ids = array();
		foreach ( $forms as $form ) {
			$form_ids[ $form->id ] = $form;
			unset( $form );
		}

		return $form_ids;
	}

	/**
	 * @param array $atts
	 *
	 * @return string
	 */
	private function get_row_classes( $atts ) {
		$class = 'column-' . $atts['column_name'];

		if ( in_array( $atts['column_name'], $atts['hidden'] ) ) {
			$class .= ' frm_hidden';
		}

		return 'class="' . esc_attr( $class ) . '"';
	}

	/**
	 * @param object $item
	 *
	 * @return string
	 */
	private function get_receipt_id_column( $item ) {
		return $this->get_action_column( $item, 'receipt_id' );
	}

	/**
	 * @param object $item
	 * @param string $field
	 *
	 * @return string
	 */
	private function get_action_column( $item, $field ) {
		$link = add_query_arg(
			array(
				'action' => 'show',
				'id'     => $item->id,
				'type'   => $this->table,
			)
		);

		$link_params = array(
			'class' => 'rot-title',
			'href'  => esc_url( $link ),
			'title' => __( 'View', 'formidable' ),
		);
		$link        = '<a ' . FrmAppHelper::array_to_html_params( $link_params ) . '>'
			. $item->{$field}
			. '</a>';

		return '<strong>' . $link . '</strong>'
			. '<br />'
			. $this->row_actions( $this->get_row_actions( $item ) );
	}

	/**
	 * @param object $item
	 * @return array
	 */
	private function get_row_actions( $item ) {
		$base_link   = '?page=formidable-payments&action=';
		$view_link   = $base_link . 'show&id=' . $item->id . '&type=' . $this->table;
		$delete_link = $base_link . 'destroy&id=' . $item->id . '&type=' . $this->table;

		$actions         = array();
		$actions['view'] = '<a href="' . esc_url( $view_link ) . '">' . esc_html__( 'View', 'formidable' ) . '</a>';

		if ( $this->table !== 'subscriptions' && 'stripe' !== $item->paysys && class_exists( 'FrmPaymentsController', false ) ) {
			$edit_link       = $base_link . 'edit&id=' . $item->id;
			$actions['edit'] = '<a href="' . esc_url( $edit_link ) . '">' . esc_html__( 'Edit', 'formidable' ) . '</a>';
		}

		$actions['delete'] = '<a href="' . esc_url( wp_nonce_url( $delete_link ) ) . '" data-frmverify="' . esc_attr__( 'Permanently delete this payment?', 'formidable' ) . '" data-frmverify-btn="frm-button-red">' . esc_html__( 'Delete', 'formidable' ) . '</a>';

		return $actions;
	}

	/**
	 * Get the column value for displaying an entry ID.
	 *
	 * @param object $item A payment or subscription object.
	 * @return string
	 */
	private function get_item_id_column( $item ) {
		$entry_id         = (int) $item->item_id;
		$entry_is_deleted = ! in_array( $entry_id, $this->valid_entry_ids );

		if ( $entry_is_deleted ) {
			// translators: %d: ID of the deleted entry.
			return sprintf( __( '%d (Deleted)', 'formidable' ), $entry_id );
		}

		return '<a href="' . esc_url( '?page=formidable-entries&frm_action=show&action=show&id=' . $entry_id ) . '">' . absint( $entry_id ) . '</a>';
	}

	/**
	 * @param object $item
	 * @param array  $atts
	 * @return mixed
	 */
	private function get_form_id_column( $item, $atts ) {
		if ( isset( $atts['form_ids'][ $item->item_id ] ) ) {
			$form_link = FrmFormsHelper::edit_form_link( $atts['form_ids'][ $item->item_id ]->form_id );
			return $form_link;
		}

		return '';
	}

	/**
	 * @param object $item
	 * @return string
	 */
	private function get_user_id_column( $item ) {
		global $wpdb;
		$val = FrmDb::get_var( 'frm_items', array( 'id' => $item->item_id ), 'user_id' );
		return FrmTransLiteAppHelper::get_user_link( $val );
	}

	/**
	 * @param object $item
	 * @param array  $atts
	 * @return string
	 */
	private function get_created_at_column( $item, $atts ) {
		if ( empty( $item->created_at ) || $item->created_at === '0000-00-00 00:00:00' ) {
			return '';
		}

		$date       = FrmAppHelper::get_localized_date( $atts['date_format'], $item->created_at );
		$date_title = FrmAppHelper::get_localized_date( $atts['date_format'] . ' g:i:s A', $item->created_at );
		$val        = '<abbr title="' . esc_attr( $date_title ) . '">' . $date . '</abbr>';
		return $val;
	}

	/**
	 * @return string
	 */
	private function get_amount_column( $item ) {
		if ( $this->table === 'subscriptions' ) {
			return FrmTransLiteAppHelper::format_billing_cycle( $item );
		}
		return FrmTransLiteAppHelper::formatted_amount( $item );
	}

	/**
	 * @param object $item
	 *
	 * @return string
	 */
	private function get_end_count_column( $item ) {
		$limit              = $item->end_count >= 9999 ? __( 'unlimited', 'formidable' ) : $item->end_count;
		$frm_payment        = new FrmTransLitePayment();
		$completed_payments = $frm_payment->get_all_by( $item->id, 'sub_id' );
		$count              = 0;

		foreach ( $completed_payments as $completed_payment ) {
			if ( $completed_payment->status === 'complete' ) {
				++$count;
			}
		}

		// translators: %1$s: Count, %2$s: Limit.
		return sprintf( __( '%1$s of %2$s', 'formidable' ), $count, $limit );
	}

	private function get_status_column( $item ) {
		$status = esc_html( FrmTransLiteAppHelper::show_status( FrmTransLiteAppHelper::get_payment_status( $item ) ) );

		if ( 'processing' === $item->status ) {
			$status .= $this->get_processing_tooltip();
		}

		return $status;
	}

	/**
	 * @since 6.5, introduced in v2.04 of the Payments submodule.
	 *
	 * @return string
	 */
	private function get_processing_tooltip() {
		return FrmAppHelper::clip(
			function () {
				$params = array(
					'class' => 'frm_help frm_icon_font frm_tooltip_icon',
					'title' => __( 'This payment method may take between 4-5 business days to process.', 'formidable' ),
				);
				?>
				<span <?php FrmAppHelper::array_to_html_params( $params, true ); ?>></span>
				<?php
			}
		);
	}

	/**
	 * @param object $item
	 * @return string
	 */
	private function get_sub_id_column( $item ) {
		if ( empty( $item->sub_id ) ) {
			return '';
		}

		if ( $this->table === 'subscriptions' ) {
			return $this->get_action_column( $item, 'sub_id' );
		}

		return '<a href="' . esc_url( '?page=formidable-payments&action=show&type=subscriptions&id=' . $item->sub_id ) . '">' . $item->sub_id . '</a>';
	}

	/**
	 * Get the string for the "Processor" column.
	 * Convert the supported pay systems to their proper case.
	 *
	 * @param object $item
	 * @param array  $atts
	 * @return string
	 */
	private function get_paysys_column( $item, $atts ) {
		switch ( $item->paysys ) {
			case 'stripe':
				return 'Stripe';
			case 'paypal':
				return 'PayPal';
		}
		return $item->paysys;
	}

	/**
	 * Display 'Test' or 'Live' in a mode column if the value is known.
	 * Old payment entries will have a NULL 'test' column value.
	 *
	 * @since 6.6
	 *
	 * @param stdClass $item Payment or Subscription object.
	 * @return string
	 */
	private function get_mode_column( $item ) {
		return esc_html( FrmTransLiteAppHelper::get_test_mode_display_string( $item ) );
	}
}
