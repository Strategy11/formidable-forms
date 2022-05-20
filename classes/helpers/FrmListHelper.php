<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmListHelper {
	/**
	 * The current list of items
	 *
	 * @since 2.0.18
	 * @var array
	 * @access public
	 */
	public $items;

	/**
	 * @since 4.07
	 */
	public $total_items = false;

	/**
	 * Various information about the current table
	 *
	 * @since 2.0.18
	 * @var array
	 * @access protected
	 */
	protected $_args;

	/**
	 * Various information needed for displaying the pagination
	 *
	 * @since 2.0.18
	 * @var array
	 */
	protected $_pagination_args = array();

	/**
	 * The current screen
	 *
	 * @since 2.0.18
	 * @var object
	 * @access protected
	 */
	protected $screen;

	/**
	 * Cached bulk actions
	 *
	 * @since 2.0.18
	 * @var array
	 * @access private
	 */
	private $_actions;

	/**
	 * Cached pagination output
	 *
	 * @since 2.0.18
	 * @var string
	 * @access private
	 */
	private $_pagination;

	/**
	 * The view switcher modes.
	 *
	 * @since 2.0.18
	 * @var array
	 * @access protected
	 */
	protected $modes = array();

	/**
	 *
	 * @var array
	 */
	protected $params;

	/**
	 * Stores the value returned by ->get_column_info()
	 *
	 * @var array
	 */
	protected $_column_headers;

	protected $compat_fields = array( '_args', '_pagination_args', 'screen', '_actions', '_pagination' );

	protected $compat_methods = array(
		'set_pagination_args',
		'get_views',
		'get_bulk_actions',
		'bulk_actions',
		'row_actions',
		'view_switcher',
		'get_items_per_page',
		'pagination',
		'get_sortable_columns',
		'get_column_info',
		'get_table_classes',
		'display_tablenav',
		'extra_tablenav',
		'single_row_columns',
	);

	/**
	 * Construct the table object
	 */
	public function __construct( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'params'   => array(),
				'plural'   => '',
				'singular' => '',
				'ajax'     => false,
				'screen'   => null,
			)
		);

		$this->params = $args['params'];

		$this->screen = convert_to_screen( $args['screen'] );

		add_filter( "manage_{$this->screen->id}_columns", array( $this, 'get_columns' ), 0 );

		if ( ! $args['plural'] ) {
			$args['plural'] = $this->screen->base;
		}

		$args['plural']   = sanitize_key( $args['plural'] );
		$args['singular'] = sanitize_key( $args['singular'] );

		$this->_args = $args;

		if ( $args['ajax'] ) {
			// wp_enqueue_script( 'list-table' );
			add_action( 'admin_footer', array( $this, '_js_vars' ) );
		}

		if ( empty( $this->modes ) ) {
			$this->modes = array(
				'list'    => __( 'List View', 'formidable' ),
				'excerpt' => __( 'Excerpt View', 'formidable' ),
			);
		}
	}

	public function ajax_user_can() {
		return current_user_can( 'administrator' );
	}

	public function get_columns() {
		return array();
	}

	public function display_rows() {
		foreach ( $this->items as $item ) {
			echo "\n\t", $this->single_row( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @uses FrmListHelper::set_pagination_args()
	 *
	 * @since 2.0.18
	 * @access public
	 * @abstract
	 */
	public function prepare_items() {
		die( 'function FrmListHelper::prepare_items() must be over-ridden in a sub-class.' );
	}

	/**
	 * @since 3.0
	 */
	protected function get_param( $args ) {
		return FrmAppHelper::get_simple_request(
			array(
				'param'    => $args['param'],
				'default'  => isset( $args['default'] ) ? $args['default'] : '',
				'sanitize' => isset( $args['sanitize'] ) ? $args['sanitize'] : 'sanitize_title',
				'type'     => 'request',
			)
		);
	}

	/**
	 * An internal method that sets all the necessary pagination arguments
	 *
	 * @param array $args An associative array with information about the pagination
	 *
	 * @access protected
	 *
	 * @param array|string $args
	 */
	protected function set_pagination_args( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'total_items' => 0,
				'total_pages' => 0,
				'per_page'    => 0,
			)
		);

		if ( ! $args['total_pages'] && $args['per_page'] > 0 ) {
			$args['total_pages'] = ceil( $args['total_items'] / $args['per_page'] );
		}

		// Redirect if page number is invalid and headers are not already sent.
		if ( ! headers_sent() && ! wp_doing_ajax() && $args['total_pages'] > 0 && $this->get_pagenum() > $args['total_pages'] ) {
			wp_redirect( add_query_arg( 'paged', $args['total_pages'] ) );
			exit;
		}

		$this->_pagination_args = $args;
	}

	/**
	 * Access the pagination args.
	 *
	 * @since 2.0.18
	 * @access public
	 *
	 * @param string $key Pagination argument to retrieve. Common values include 'total_items',
	 *                    'total_pages', 'per_page', or 'infinite_scroll'.
	 *
	 * @return int Number of items that correspond to the given pagination argument.
	 */
	public function get_pagination_arg( $key ) {
		if ( 'page' == $key ) {
			return $this->get_pagenum();
		}

		if ( isset( $this->_pagination_args[ $key ] ) ) {
			return $this->_pagination_args[ $key ];
		}
	}

	/**
	 * Whether the table has items to display or not
	 *
	 * @since 2.0.18
	 * @access public
	 *
	 * @return bool
	 */
	public function has_items() {
		return ! empty( $this->items );
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 2.0.18
	 * @access public
	 */
	public function no_items() {
		esc_html_e( 'No items found.', 'formidable' );
	}

	/**
	 * Display the search box.
	 *
	 * @since 2.0.18
	 * @access public
	 *
	 * @param string $text The search button text
	 * @param string $input_id The search input id
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		foreach ( array( 'orderby', 'order' ) as $search_params ) {
			$this->hidden_search_inputs( $search_params );
		}

		FrmAppHelper::show_search_box( compact( 'text', 'input_id' ) );
	}

	private function hidden_search_inputs( $param_name ) {
		if ( ! empty( $_REQUEST[ $param_name ] ) ) {
			$value = sanitize_text_field( wp_unslash( $_REQUEST[ $param_name ] ) );
			echo '<input type="hidden" name="' . esc_attr( $param_name ) . '" value="' . esc_attr( $value ) . '" />';
		}
	}

	/**
	 * Get an associative array ( id => link ) with the list
	 * of views available on this table.
	 *
	 * @since 2.0.18
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_views() {
		return array();
	}

	/**
	 * Display the list of views available on this table.
	 *
	 * @since 2.0.18
	 * @access public
	 */
	public function views() {
		$views = $this->get_views();
		/**
		 * Filter the list of available list table views.
		 *
		 * The dynamic portion of the hook name, `$this->screen->id`, refers
		 * to the ID of the current screen, usually a string.
		 *
		 * @since 3.5.0
		 *
		 * @param array $views An array of available list table views.
		 */
		$views = apply_filters( 'views_' . $this->screen->id, $views );

		if ( empty( $views ) ) {
			return;
		}

		echo "<ul class='subsubsub'>\n";
		foreach ( $views as $class => $view ) {
			$views[ $class ] = "\t" . '<li class="' . esc_attr( $class ) . '">' . $view;
		}
		echo implode( " |</li>\n", $views ) . "</li>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</ul>';
	}

	/**
	 * Get an associative array ( option_name => option_title ) with the list
	 * of bulk actions available on this table.
	 *
	 * @since 2.0.18
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		return array();
	}

	/**
	 * Display the bulk actions dropdown.
	 *
	 * @since 2.0.18
	 * @access protected
	 *
	 * @param string $which The location of the bulk actions: 'top' or 'bottom'.
	 *                      This is designated as optional for backwards-compatibility.
	 */
	protected function bulk_actions( $which = '' ) {
		if ( is_null( $this->_actions ) ) {
			$no_new_actions = $this->get_bulk_actions();
			$this->_actions = $no_new_actions;

			/**
			 * Filter the list table Bulk Actions drop-down.
			 *
			 * The dynamic portion of the hook name, `$this->screen->id`, refers
			 * to the ID of the current screen, usually a string.
			 *
			 * This filter can currently only be used to remove bulk actions.
			 *
			 * @since 3.5.0
			 *
			 * @param array $actions An array of the available bulk actions.
			 */
			$this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions );
			$this->_actions = array_intersect_assoc( $this->_actions, $no_new_actions );

			$two = '';
		} else {
			$two = '2';
		}

		if ( empty( $this->_actions ) ) {
			return;
		}

		echo "<label for='bulk-action-selector-" . esc_attr( $which ) . "' class='screen-reader-text'>" . esc_attr__( 'Select bulk action', 'formidable' ) . '</label>';
		echo "<select name='action" . esc_attr( $two ) . "' id='bulk-action-selector-" . esc_attr( $which ) . "'>\n";
		echo "<option value='-1' selected='selected'>" . esc_attr__( 'Bulk Actions', 'formidable' ) . "</option>\n";

		foreach ( $this->_actions as $name => $title ) {
			$params = array(
				'value' => $name,
			);
			if ( 'edit' === $name ) {
				$params['class'] = 'hide-if-no-js';
			}

			echo "\t<option ";
			FrmAppHelper::array_to_html_params( $params, true );
			echo '>' . esc_html( $title ) . '</option>' . "\n";
		}

		echo "</select>\n";

		if ( isset( $this->_actions['bulk_delete'] ) ) {
			$verify = $this->confirm_bulk_delete();

			if ( $verify ) {
				echo "<a id='confirm-bulk-delete-" . esc_attr( $which ) . "' class='frm-hidden' href='confirm-bulk-delete' data-frmcaution='" . esc_html__( 'Heads up', 'formidable' ) . "' data-frmverify='" . esc_attr( $verify ) . "'></a>";
			}
		}

		submit_button( __( 'Apply', 'formidable' ), 'action', '', false, array( 'id' => "doaction$two" ) );
		echo "\n";
	}

	/**
	 * @return string if empty there will be no confirmation pop up
	 */
	protected function confirm_bulk_delete() {
		return '';
	}

	/**
	 * Get the current action selected from the bulk actions dropdown.
	 *
	 * @since 2.0.18
	 * @access public
	 *
	 * @return string|false The action name or False if no action was selected
	 */
	public function current_action() {
		if ( isset( $_REQUEST['filter_action'] ) && ! empty( $_REQUEST['filter_action'] ) ) {
			return false;
		}

		$action = $this->get_bulk_action( 'action' );
		if ( $action === false ) {
			$action = $this->get_bulk_action( 'action2' );
		}

		return $action;
	}

	private function get_bulk_action( $action_name ) {
		$action       = false;
		$action_param = $this->get_param(
			array(
				'param'    => $action_name,
				'sanitize' => 'sanitize_text_field',
			)
		);
		if ( $action_param && - 1 != $action_param ) {
			$action = $action_param;
		}

		return $action;
	}

	/**
	 * Generate row actions div
	 *
	 * @since 2.0.18
	 * @access protected
	 *
	 * @param array $actions The list of actions
	 * @param bool $always_visible Whether the actions should be always visible
	 *
	 * @return string
	 */
	protected function row_actions( $actions, $always_visible = false ) {
		$action_count = count( $actions );

		$i = 0;

		if ( ! $action_count ) {
			return '';
		}

		$out = '<div class="' . ( $always_visible ? 'row-actions visible' : 'row-actions' ) . '">';
		foreach ( $actions as $action => $link ) {
			++ $i;
			( $i == $action_count ) ? $sep = '' : $sep = ' | ';
			$out .= "<span class='$action'>$link$sep</span>";
		}
		$out .= '</div>';

		$out .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __( 'Show more details', 'formidable' ) . '</span></button>';

		return $out;
	}

	/**
	 * Display a view switcher
	 *
	 * @since 2.0.18
	 * @access protected
	 *
	 * @param string $current_mode
	 */
	protected function view_switcher( $current_mode ) {
		?>
		<input type="hidden" name="mode" value="<?php echo esc_attr( $current_mode ); ?>"/>
		<div class="view-switch">
			<?php
			foreach ( $this->modes as $mode => $title ) {
				$classes = array( 'view-' . $mode );
				if ( $current_mode == $mode ) {
					$classes[] = 'current';
				}

				printf(
					'<a href="%s" class="%s" id="view-switch-' . esc_attr( $mode ) . '"><span class="screen-reader-text">%s</span></a>' . "\n",
					esc_url( add_query_arg( 'mode', $mode ) ),
					esc_attr( implode( ' ', $classes ) ),
					esc_html( $title )
				);
			}
			?>
		</div>
		<?php
	}

	/**
	 * Get the current page number
	 *
	 * @since 2.0.18
	 * @access public
	 *
	 * @return int
	 */
	public function get_pagenum() {
		$pagenum = isset( $_REQUEST['paged'] ) ? absint( $_REQUEST['paged'] ) : 0;

		if ( isset( $this->_pagination_args['total_pages'] ) && $pagenum > $this->_pagination_args['total_pages'] ) {
			$pagenum = $this->_pagination_args['total_pages'];
		}

		return max( 1, $pagenum );
	}

	/**
	 * Get number of items to display on a single page
	 *
	 * @since 2.0.18
	 * @access protected
	 *
	 * @param string $option
	 * @param int $default
	 *
	 * @return int
	 */
	protected function get_items_per_page( $option, $default = 20 ) {
		$per_page = (int) get_user_option( $option );
		if ( empty( $per_page ) || $per_page < 1 ) {
			$per_page = $default;
		}

		/**
		 * Filter the number of items to be displayed on each page of the list table.
		 *
		 * The dynamic hook name, $option, refers to the `per_page` option depending
		 * on the type of list table in use. Possible values include: 'edit_comments_per_page',
		 * 'sites_network_per_page', 'site_themes_network_per_page', 'themes_network_per_page',
		 * 'users_network_per_page', 'edit_post_per_page', 'edit_page_per_page',
		 * 'edit_{$post_type}_per_page', etc.
		 *
		 * @since 2.9.0
		 *
		 * @param int $per_page Number of items to be displayed. Default 20.
		 */
		return (int) apply_filters( $option, $per_page );
	}

	/**
	 * Display the pagination.
	 *
	 * @since 2.0.18
	 * @access protected
	 *
	 * @param string $which
	 */
	protected function pagination( $which ) {
		if ( empty( $this->_pagination_args ) ) {
			return;
		}

		$total_items     = $this->_pagination_args['total_items'];
		$total_pages     = $this->_pagination_args['total_pages'];
		$infinite_scroll = false;
		if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
			$infinite_scroll = $this->_pagination_args['infinite_scroll'];
		}

		/* translators: %s: Number of items */
		$output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items, 'formidable' ), number_format_i18n( $total_items ) ) . '</span>';

		$current = $this->get_pagenum();

		$page_links = array();

		$total_pages_before = '<span class="paging-input">';
		$total_pages_after  = '</span>';

		$disable = $this->disabled_pages( $total_pages );

		$page_links[] = $this->add_page_link(
			array(
				'page'     => 'first',
				'arrow'    => '&laquo;',
				'number'   => '',
				'disabled' => $disable['first'],
			)
		);

		$page_links[] = $this->add_page_link(
			array(
				'page'     => 'prev',
				'arrow'    => '&lsaquo;',
				'number'   => max( 1, $current - 1 ),
				'disabled' => $disable['prev'],
			)
		);

		if ( 'bottom' == $which ) {
			$html_current_page  = $current;
			$total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page', 'formidable' ) . '</span><span id="table-paging" class="paging-input">';
		} else {
			$html_current_page = sprintf(
				"%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' />",
				'<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page', 'formidable' ) . '</label>',
				$current,
				strlen( $total_pages )
			);
		}
		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );

		/* translators: %1$s: Current page number, %2$s: Total pages */
		$page_links[] = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging', 'formidable' ), $html_current_page, $html_total_pages ) . $total_pages_after;

		$page_links[] = $this->add_page_link(
			array(
				'page'     => 'next',
				'arrow'    => '&rsaquo;',
				'number'   => min( $total_pages, $current + 1 ),
				'disabled' => $disable['next'],
			)
		);

		$page_links[] = $this->add_page_link(
			array(
				'page'     => 'last',
				'arrow'    => '&raquo;',
				'number'   => $total_pages,
				'disabled' => $disable['last'],
			)
		);

		$pagination_links_class = 'pagination-links';
		if ( ! empty( $infinite_scroll ) ) {
			$pagination_links_class = ' hide-if-js';
		}
		$output .= "\n" . '<span class="' . esc_attr( $pagination_links_class ) . '">' . join( "\n", $page_links ) . '</span>';

		if ( $total_pages ) {
			$page_class = $total_pages < 2 ? ' one-page' : '';
		} else {
			$page_class = ' no-pages';
		}
		$this->_pagination = "<div class='tablenav-pages" . esc_attr( $page_class ) . "'>$output</div>";

		echo $this->_pagination; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	private function disabled_pages( $total_pages ) {
		$current = $this->get_pagenum();
		$disable = array(
			'first' => false,
			'last'  => false,
			'prev'  => false,
			'next'  => false,
		);

		if ( $current == 1 ) {
			$disable['first'] = true;
			$disable['prev']  = true;
		} elseif ( $current == 2 ) {
			$disable['first'] = true;
		}

		if ( $current == $total_pages ) {
			$disable['last'] = true;
			$disable['next'] = true;
		} elseif ( $current == $total_pages - 1 ) {
			$disable['last'] = true;
		}

		return $disable;
	}

	private function link_label( $link ) {
		$labels = array(
			'first' => __( 'First page', 'formidable' ),
			'last'  => __( 'Last page', 'formidable' ),
			'prev'  => __( 'Previous page', 'formidable' ),
			'next'  => __( 'Next page', 'formidable' ),
		);

		return $labels[ $link ];
	}

	private function current_url() {
		$current_url = set_url_scheme( 'http://' . FrmAppHelper::get_server_value( 'HTTP_HOST' ) . FrmAppHelper::get_server_value( 'REQUEST_URI' ) );

		return remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );
	}

	private function add_page_link( $atts ) {
		if ( $atts['disabled'] ) {
			$link = $this->add_disabled_link( $atts['arrow'] );
		} else {
			$link = $this->add_active_link( $atts );
		}

		return $link;
	}

	private function add_disabled_link( $label ) {
		return '<span class="tablenav-pages-navspan button disabled" aria-hidden="true">' . $label . '</span>';
	}

	private function add_active_link( $atts ) {
		$url   = esc_url( add_query_arg( 'paged', $atts['number'], $this->current_url() ) );
		$label = $this->link_label( $atts['page'] );

		return sprintf(
			"<a class='button %s-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
			$atts['page'],
			$url,
			$label,
			$atts['arrow']
		);
	}

	/**
	 * Get a list of sortable columns. The format is:
	 * 'internal-name' => 'orderby'
	 * or
	 * 'internal-name' => array( 'orderby', true )
	 *
	 * The second format will make the initial sorting order be descending
	 *
	 * @since 2.0.18
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array();
	}

	/**
	 * Gets the name of the default primary column.
	 *
	 * @since 4.3.0
	 * @access protected
	 *
	 * @return string Name of the default primary column, in this case, an empty string.
	 */
	protected function get_default_primary_column_name() {
		$columns = $this->get_columns();
		$column  = '';

		// We need a primary defined so responsive views show something,
		// so let's fall back to the first non-checkbox column.
		foreach ( $columns as $col => $column_name ) {
			if ( 'cb' === $col ) {
				continue;
			}

			$column = $col;
			break;
		}

		return $column;
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 4.3.0
	 * @access protected
	 *
	 * @return string The name of the primary column.
	 */
	protected function get_primary_column_name() {
		$columns = $this->get_columns();
		$default = $this->get_default_primary_column_name();

		// If the primary column doesn't exist fall back to the
		// first non-checkbox column.
		if ( ! isset( $columns[ $default ] ) ) {
			$default = self::get_default_primary_column_name();
		}

		/**
		 * Filter the name of the primary column for the current list table.
		 *
		 * @since 4.3.0
		 *
		 * @param string $default Column name default for the specific list table, e.g. 'name'.
		 * @param string $context Screen ID for specific list table, e.g. 'plugins'.
		 */
		$column = apply_filters( 'list_table_primary_column', $default, $this->screen->id );

		if ( empty( $column ) || ! isset( $columns[ $column ] ) ) {
			$column = $default;
		}

		return $column;
	}

	/**
	 * Get a list of all, hidden and sortable columns, with filter applied
	 *
	 * @since 2.0.18
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_column_info() {
		// $_column_headers is already set / cached
		if ( isset( $this->_column_headers ) && is_array( $this->_column_headers ) ) {
			// Back-compat for list tables that have been manually setting $_column_headers for horse reasons.
			// In 4.3, we added a fourth argument for primary column.
			$column_headers = array( array(), array(), array(), $this->get_primary_column_name() );
			foreach ( $this->_column_headers as $key => $value ) {
				$column_headers[ $key ] = $value;
			}

			return $column_headers;
		}

		$columns = get_column_headers( $this->screen );
		$hidden  = get_hidden_columns( $this->screen );

		$sortable_columns = $this->get_sortable_columns();
		/**
		 * Filter the list table sortable columns for a specific screen.
		 *
		 * The dynamic portion of the hook name, `$this->screen->id`, refers
		 * to the ID of the current screen, usually a string.
		 *
		 * @since 3.5.0
		 *
		 * @param array $sortable_columns An array of sortable columns.
		 */
		$_sortable = apply_filters( "manage_{$this->screen->id}_sortable_columns", $sortable_columns );

		$sortable = array();
		foreach ( $_sortable as $id => $data ) {
			if ( empty( $data ) ) {
				continue;
			}

			$data = (array) $data;
			if ( ! isset( $data[1] ) ) {
				$data[1] = false;
			}

			$sortable[ $id ] = $data;
		}

		$primary = $this->get_primary_column_name();

		$this->_column_headers = array( $columns, $hidden, $sortable, $primary );

		return $this->_column_headers;
	}

	/**
	 * Return number of visible columns
	 *
	 * @since 2.0.18
	 * @access public
	 *
	 * @return int
	 */
	public function get_column_count() {
		list ( $columns, $hidden ) = $this->get_column_info();
		$hidden = array_intersect( array_keys( $columns ), array_filter( $hidden ) );

		return count( $columns ) - count( $hidden );
	}

	/**
	 * Print column headers, accounting for hidden and sortable columns.
	 *
	 * @since 2.0.18
	 * @access public
	 *
	 * @staticvar int $cb_counter
	 *
	 * @param bool $with_id Whether to set the id attribute or not
	 */
	public function print_column_headers( $with_id = true ) {
		list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

		$current_url = set_url_scheme( 'http://' . FrmAppHelper::get_server_value( 'HTTP_HOST' ) . FrmAppHelper::get_server_value( 'REQUEST_URI' ) );
		$current_url = remove_query_arg( 'paged', $current_url );

		if ( isset( $_GET['orderby'] ) ) {
			$current_orderby = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
		} else {
			$current_orderby = '';
		}

		if ( isset( $_GET['order'] ) && 'desc' == $_GET['order'] ) {
			$current_order = 'desc';
		} else {
			$current_order = 'asc';
		}

		if ( ! empty( $columns['cb'] ) ) {
			static $cb_counter = 1;
			$columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All', 'formidable' ) . '</label>';
			$columns['cb'] .= '<input id="cb-select-all-' . esc_attr( $cb_counter ) . '" type="checkbox" />';
			$cb_counter ++;
		}

		foreach ( $columns as $column_key => $column_display_name ) {
			$class = array( 'manage-column', "column-$column_key" );

			if ( in_array( $column_key, $hidden ) ) {
				$class[] = 'hidden';
			}

			if ( 'cb' == $column_key ) {
				$class[] = 'check-column';
			} elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) ) {
				$class[] = 'num';
			}

			if ( $column_key === $primary ) {
				$class[] = 'column-primary';
			}

			if ( isset( $sortable[ $column_key ] ) ) {
				list( $orderby, $desc_first ) = $sortable[ $column_key ];

				if ( $current_orderby == $orderby ) {
					$order   = 'asc' == $current_order ? 'desc' : 'asc';
					$class[] = 'sorted';
					$class[] = $current_order;
				} else {
					$order   = $desc_first ? 'desc' : 'asc';
					$class[] = 'sortable';
					$class[] = $desc_first ? 'asc' : 'desc';
				}

				$column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . esc_html( $column_display_name ) . '</span><span class="sorting-indicator"></span></a>';
			}

			$tag   = ( 'cb' === $column_key ) ? 'td' : 'th';
			$scope = ( 'th' === $tag ) ? 'scope="col"' : '';
			$id    = $with_id ? "id='" . esc_attr( $column_key ) . "'" : '';

			if ( ! empty( $class ) ) {
				$class = "class='" . esc_attr( join( ' ', $class ) ) . "'";
			}

			if ( ! $this->has_min_items() && ! $with_id ) {
				// Hide the labels but show the border.
				$column_display_name = '';
			}
			echo "<$tag $scope $id $class>$column_display_name</$tag>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Display the table
	 *
	 * @since 2.0.18
	 * @access public
	 */
	public function display() {
		$singular     = $this->_args['singular'];
		$tbody_params = array();
		if ( $singular ) {
			$tbody_params['data-wp-lists'] = 'list:' . $singular;
		}

		$this->display_tablenav( 'top' );
		?>
		<table class="wp-list-table <?php echo esc_attr( implode( ' ', $this->get_table_classes() ) ); ?>">
			<?php if ( $this->has_min_items( 1 ) ) { ?>
			<thead>
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
			</thead>
			<?php } ?>

			<tbody id="the-list"<?php FrmAppHelper::array_to_html_params( $tbody_params, true ); ?>>
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>

			<?php if ( $this->has_min_items( 1 ) ) { ?>
			<tfoot>
				<tr>
					<?php $this->print_column_headers( false ); ?>
				</tr>
			</tfoot>
			<?php } ?>
		</table>
		<?php
		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Get a list of CSS classes for the list table table tag.
	 *
	 * @since 2.0.18
	 * @access protected
	 *
	 * @return array List of CSS classes for the table tag.
	 */
	protected function get_table_classes() {
		return array( 'widefat', 'fixed', 'striped', $this->_args['plural'] );
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * @since 2.0.18
	 * @access protected
	 *
	 * @param string $which
	 */
	protected function display_tablenav( $which ) {
		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'], '_wpnonce', false );
			if ( ! $this->has_min_items( 1 ) ) {
				// Don't show bulk actions if no items.
				return;
			}
		} elseif ( ! $this->has_min_items() ) {
			// don't show the bulk actions when there aren't many rows.
			return;
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<div class="alignleft actions bulkactions">
				<?php $this->bulk_actions( $which ); ?>
			</div>
			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>

			<br class="clear"/>
		</div>
		<?php
	}

	/**
	 * Use this to exclude the footer labels and bulk items.
	 * When close together, it feels like duplicates.
	 *
	 * @since 4.07
	 */
	protected function has_min_items( $limit = 5 ) {
		return $this->has_items() && ( $this->total_items === false || $this->total_items >= $limit );
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * @since 2.0.18
	 * @access protected
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
	}

	/**
	 * Generate the tbody element for the list table.
	 *
	 * @since 2.0.18
	 * @access public
	 */
	public function display_rows_or_placeholder() {
		if ( $this->has_items() ) {
			$this->display_rows();
		} else {
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . esc_attr( $this->get_column_count() ) . '">';
			$this->no_items();
			echo '</td></tr>';
		}
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @since 2.0.18
	 * @access public
	 *
	 * @param object $item The current item
	 */
	public function single_row( $item ) {
		echo '<tr>';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Generates the columns for a single row of the table
	 *
	 * @since 2.0.18
	 * @access protected
	 *
	 * @param object $item The current item
	 */
	protected function single_row_columns( $item ) {
		list( $columns, $hidden,, $primary ) = $this->get_column_info();

		foreach ( $columns as $column_name => $column_display_name ) {
			$classes = "$column_name column-$column_name";
			if ( $primary === $column_name ) {
				$classes .= ' has-row-actions column-primary';
			}

			if ( in_array( $column_name, $hidden ) ) {
				$classes .= ' hidden';
			}

			$params = array(
				'class'        => $classes,
				// Comments column uses HTML in the display name with screen reader text.
				// Instead of using esc_attr(), we strip tags to get closer to a user-friendly string.
				'data-colname' => $column_display_name,
			);

			if ( 'cb' === $column_name ) {
				echo '<th scope="row" class="check-column"></th>';
			} else {
				echo '<td ';
				FrmAppHelper::array_to_html_params( $params, true );
				echo '>';

				if ( method_exists( $this, 'column_' . $column_name ) ) {
					echo call_user_func( array( $this, 'column_' . $column_name ), $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}

				echo $this->handle_row_actions( $item, $column_name, $primary ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo '</td>';
			}
		}
	}

	/**
	 * Generates and display row actions links for the list table.
	 *
	 * @since 4.3.0
	 * @access protected
	 *
	 * @param object $item The item being acted upon.
	 * @param string $column_name Current column name.
	 * @param string $primary Primary column name.
	 *
	 * @return string The row actions output. In this case, an empty string.
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		return $column_name == $primary ? '<button type="button" class="toggle-row"><span class="screen-reader-text">' . esc_html__( 'Show more details', 'formidable' ) . '</span></button>' : '';
	}

	/**
	 * Handle an incoming ajax request (called from admin-ajax.php)
	 *
	 * @since 2.0.18
	 * @access public
	 */
	public function ajax_response() {
		$this->prepare_items();

		ob_start();
		if ( ! empty( $_REQUEST['no_placeholder'] ) ) {
			$this->display_rows();
		} else {
			$this->display_rows_or_placeholder();
		}

		$rows = ob_get_clean();

		$response = array( 'rows' => $rows );

		if ( isset( $this->_pagination_args['total_items'] ) ) {
			$response['total_items_i18n'] = sprintf(
				/* translators: %s: Number of items */
				_n( '%s item', '%s items', $this->_pagination_args['total_items'], 'formidable' ),
				number_format_i18n( $this->_pagination_args['total_items'] )
			);
		}
		if ( isset( $this->_pagination_args['total_pages'] ) ) {
			$response['total_pages']      = $this->_pagination_args['total_pages'];
			$response['total_pages_i18n'] = number_format_i18n( $this->_pagination_args['total_pages'] );
		}

		die( wp_json_encode( $response ) );
	}

	/**
	 * Send required variables to JavaScript land
	 *
	 * @access public
	 */
	public function _js_vars() {
		$args = array(
			'class'  => get_class( $this ),
			'screen' => array(
				'id'   => $this->screen->id,
				'base' => $this->screen->base,
			),
		);

		printf( "<script type='text/javascript'>list_args = %s;</script>\n", wp_json_encode( $args ) );
	}
}
