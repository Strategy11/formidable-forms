<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFormsListHelper extends FrmListHelper {
	public $status = '';

	public $total_items = 0;

	public function __construct( $args ) {
		$this->status = self::get_param( array( 'param' => 'form_type' ) );

		parent::__construct( $args );
	}

	public function prepare_items() {
		global $wpdb, $per_page, $mode;

		$page     = $this->get_pagenum();
		$per_page = $this->get_items_per_page( 'formidable_page_formidable_per_page' );

		$mode    = self::get_param(
			array(
				'param'   => 'mode',
				'default' => 'list',
			)
		);
		$orderby = self::get_param(
			array(
				'param'   => 'orderby',
				'default' => 'name',
			)
		);
		$order   = self::get_param(
			array(
				'param'   => 'order',
				'default' => 'ASC',
			)
		);
		$start   = self::get_param(
			array(
				'param'   => 'start',
				'default' => ( $page - 1 ) * $per_page,
			)
		);

		$s_query = array(
			array(
				'or'               => 1,
				'parent_form_id'   => null,
				'parent_form_id <' => 1,
			),
		);
		switch ( $this->status ) {
			case 'draft':
				$s_query['is_template'] = 0;
				$s_query['status']      = 'draft';
				break;
			case 'trash':
				$s_query['status'] = 'trash';
				break;
			default:
				$s_query['is_template'] = 0;
				$s_query['status !']    = 'trash';
				break;
		}

		$s = self::get_param(
			array(
				'param'    => 's',
				'sanitize' => 'sanitize_text_field',
			)
		);
		if ( $s != '' ) {
			preg_match_all( '/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $s, $matches );
			$search_terms = array_map( 'trim', $matches[0] );
			foreach ( (array) $search_terms as $term ) {
				$s_query[] = array(
					'or'               => true,
					'name LIKE'        => $term,
					'description LIKE' => $term,
					'created_at LIKE'  => $term,
					'form_key LIKE'    => $term,
					'id'               => $term,
				);
				unset( $term );
			}
		}

		$this->items = FrmForm::getAll( $s_query, $orderby . ' ' . $order, $start . ',' . $per_page );
		$total_items = FrmDb::get_count( 'frm_forms', $s_query );
		$this->total_items = $total_items;

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);
	}

	public function no_items() {
		if ( $this->status === 'trash' ) {
			echo '<p>';
			esc_html_e( 'No forms found in the trash.', 'formidable' );
			?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable' ) ); ?>">
				<?php esc_html_e( 'See all forms.', 'formidable' ); ?>
			</a>
			<?php
			echo '</p>';
		} else {
			$title = __( 'No Forms Found', 'formidable' );
			include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/_no_forms.php';
		}
	}

	public function get_bulk_actions() {
		$actions = array();

		if ( 'trash' == $this->status ) {
			if ( current_user_can( 'frm_edit_forms' ) ) {
				$actions['bulk_untrash'] = __( 'Restore', 'formidable' );
			}

			if ( current_user_can( 'frm_delete_forms' ) ) {
				$actions['bulk_delete'] = __( 'Delete Permanently', 'formidable' );
			}
		} elseif ( EMPTY_TRASH_DAYS && current_user_can( 'frm_delete_forms' ) ) {
			$actions['bulk_trash'] = __( 'Move to Trash', 'formidable' );
		} elseif ( current_user_can( 'frm_delete_forms' ) ) {
			$actions['bulk_delete'] = __( 'Delete', 'formidable' );
		}

		return $actions;
	}

	public function extra_tablenav( $which ) {
		if ( 'top' != $which ) {
			return;
		}

		if ( 'trash' == $this->status && current_user_can( 'frm_delete_forms' ) ) {
			?>
			<div class="alignleft actions frm_visible_overflow">
				<?php submit_button( __( 'Empty Trash', 'formidable' ), 'apply', 'delete_all', false ); ?>
			</div>
			<?php
		}
	}

	public function get_views() {

		$statuses = array(
			'published' => __( 'My Forms', 'formidable' ),
			'draft'     => __( 'Drafts', 'formidable' ),
			'trash'     => __( 'Trash', 'formidable' ),
		);

		$links     = array();
		$counts    = FrmForm::get_count();
		$form_type = self::get_param(
			array(
				'param'   => 'form_type',
				'default' => 'published',
			)
		);

		foreach ( $statuses as $status => $name ) {

			if ( $status == $form_type ) {
				$class = ' class="current"';
			} else {
				$class = '';
			}

			if ( $counts->{$status} || 'draft' !== $status ) {
				/* translators: %1$s: Status, %2$s: Number of items */
				$links[ $status ] = '<a href="' . esc_url( '?page=formidable&form_type=' . $status ) . '" ' . $class . '>' . sprintf( __( '%1$s <span class="count">(%2$s)</span>', 'formidable' ), $name, number_format_i18n( $counts->{$status} ) ) . '</a>';
			}

			unset( $status, $name );
		}

		return $links;
	}

	public function pagination( $which ) {
		global $mode;

		parent::pagination( $which );

		if ( 'top' === $which ) {
			$this->view_switcher( $mode );
		}
	}

	public function single_row( $item, $style = '' ) {
		global $frm_vars, $mode;

		// Set up the hover actions for this user
		$actions   = array();
		$edit_link = FrmForm::get_edit_link( $item->id );

		$this->get_actions( $actions, $item, $edit_link );

		$action_links = $this->row_actions( $actions );

		// Set up the checkbox ( because the user is editable, otherwise its empty )
		$checkbox = '<input type="checkbox" name="item-action[]" id="cb-item-action-' . absint( $item->id ) . '" value="' . esc_attr( $item->id ) . '" />';

		$r = '<tr id="item-action-' . absint( $item->id ) . '"' . $style . '>';

		list( $columns, $hidden ) = $this->get_column_info();

		$format = 'Y/m/d';
		if ( 'list' != $mode ) {
			$format .= ' \<\b\r \/\> g:i:s a';
		}

		foreach ( $columns as $column_name => $column_display_name ) {
			$class = $column_name . ' column-' . $column_name . ( 'name' == $column_name ? ' post-title page-title column-title' : '' );

			$style = '';
			if ( in_array( $column_name, $hidden ) ) {
				$class .= ' frm_hidden';
			}

			$class        = 'class="' . esc_attr( $class ) . '"';
			$data_colname = ' data-colname="' . esc_attr( $column_display_name ) . '"';
			$attributes   = $class . $style . $data_colname;

			switch ( $column_name ) {
				case 'cb':
					$r .= '<th scope="row" class="check-column">' . $checkbox . '</th>';
					break;
				case 'id':
				case 'form_key':
					$val = $item->{$column_name};
					break;
				case 'name':
					$val = $this->get_form_name( $item, $actions, $edit_link, $mode );
					$val .= $action_links;

					break;
				case 'created_at':
					$date = gmdate( $format, strtotime( $item->created_at ) );
					$val  = '<abbr title="' . esc_attr( gmdate( 'Y/m/d g:i:s A', strtotime( $item->created_at ) ) ) . '">' . $date . '</abbr>';
					break;
				case 'shortcode':
					$val = '<a href="#" class="frm-embed-form" role="button" aria-label="' . esc_html__( 'Embed Form', 'formidable' ) . '">' . FrmAppHelper::icon_by_class( 'frmfont frm_code_icon', array( 'echo' => false ) ) . '</a>';
					$val = apply_filters( 'frm_form_list_actions', $val, array( 'form' => $item ) );
					break;
				case 'entries':
					if ( isset( $item->options['no_save'] ) && $item->options['no_save'] ) {
						$val = FrmAppHelper::icon_by_class(
							'frmfont frm_forbid_icon frm_bstooltip',
							array(
								'title' => __( 'Saving entries is disabled for this form', 'formidable' ),
								'echo'  => false,
							)
						);
					} else {
						$text = FrmEntry::getRecordCount( $item->id );
						$val  = current_user_can( 'frm_view_entries' ) ? '<a href="' . esc_url( admin_url( 'admin.php?page=formidable-entries&form=' . $item->id ) ) . '">' . $text . '</a>' : $text;
						unset( $text );
					}
					break;
				default:
					if ( method_exists( $this, 'column_' . $column_name ) ) {
						$val = call_user_func( array( $this, 'column_' . $column_name ), $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					break;
			}

			if ( isset( $val ) ) {
				$r .= "<td $attributes>";
				$r .= $val;
				$r .= '</td>';
			}
			unset( $val );
		}
		$r .= '</tr>';

		return $r;
	}

	/**
	 * @param string $edit_link
	 */
	private function get_actions( &$actions, $item, $edit_link ) {
		$new_actions = FrmFormsHelper::get_action_links( $item->id, $item );
		foreach ( $new_actions as $link => $action ) {
			$new_actions[ $link ] = FrmFormsHelper::format_link_html( $action, 'short' );
		}

		if ( 'trash' == $this->status ) {
			$actions = $new_actions;

			return;
		}

		if ( current_user_can( 'frm_edit_forms' ) ) {
			$actions['frm_edit']     = '<a href="' . esc_url( $edit_link ) . '">' . __( 'Edit', 'formidable' ) . '</a>';
			$actions['frm_settings'] = '<a href="' . esc_url( '?page=formidable&frm_action=settings&id=' . $item->id ) . '">' . __( 'Settings', 'formidable' ) . '</a>';
		}

		$actions         = array_merge( $actions, $new_actions );
		$actions['view'] = '<a href="' . esc_url( FrmFormsHelper::get_direct_link( $item->form_key, $item ) ) . '" target="_blank">' . __( 'Preview', 'formidable' ) . '</a>';
	}

	/**
	 * @param string $edit_link
	 */
	private function get_form_name( $item, $actions, $edit_link, $mode = 'list' ) {
		$form_name = $item->name;
		if ( trim( $form_name ) == '' ) {
			$form_name = __( '(no title)', 'formidable' );
		}
		$form_name = FrmAppHelper::kses( $form_name );
		if ( 'excerpt' != $mode ) {
			$form_name = FrmAppHelper::truncate( $form_name, 50 );
		}

		$val = '<strong>';
		if ( 'trash' == $this->status ) {
			$val .= $form_name;
		} else {
			$val .= '<a href="' . esc_url( isset( $actions['frm_edit'] ) ? $edit_link : FrmFormsHelper::get_direct_link( $item->form_key, $item ) ) . '" class="row-title">' . FrmAppHelper::kses( $form_name ) . '</a> ';
		}

		$this->add_draft_label( $item, $val );
		$val .= '</strong>';

		$this->add_form_description( $item, $val );

		return $val;
	}

	/**
	 * @param string $val
	 */
	private function add_draft_label( $item, &$val ) {
		if ( 'draft' == $item->status && 'draft' != $this->status ) {
			$val .= ' - <span class="post-state">' . __( 'Draft', 'formidable' ) . '</span>';
		}
	}

	/**
	 * @param string $val
	 */
	private function add_form_description( $item, &$val ) {
		global $mode;
		if ( 'excerpt' == $mode ) {
			$val .= FrmAppHelper::truncate( strip_tags( $item->description ), 50 );
		}
	}

	/**
	 * @return string
	 */
	protected function confirm_bulk_delete() {
		return __( 'ALL selected forms and their entries will be permanently deleted. Want to proceed?', 'formidable' );
	}
}
