<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmEntriesListHelper extends FrmListHelper {
	protected $column_name;
	protected $item;
	protected $field;

	/**
	 * @since 4.07
	 */
	public $total_items = 0;

	/**
	 * @return void
	 */
	public function prepare_items() {
		global $per_page;

		$per_page = $this->get_items_per_page( 'formidable_page_formidable_entries_per_page' );
		$form_id  = $this->params['form'];

		$s_query = array();

		if ( $form_id ) {
			$s_query['it.form_id'] = $form_id;
			$join_form_in_query    = false;
		} else {
			$s_query[]          = array(
				'or'               => 1,
				'parent_form_id'   => null,
				'parent_form_id <' => 1,
			);
			$join_form_in_query = true;
		}

		$s = self::get_param(
			array(
				'param'    => 's',
				'sanitize' => 'sanitize_text_field',
			)
		);

		if ( $s != '' && FrmAppHelper::pro_is_installed() ) {
			$fid     = self::get_param( array( 'param' => 'fid' ) );
			$s_query = FrmProEntriesHelper::get_search_str( $s_query, $s, $form_id, $fid );
		}

		$s_query = apply_filters( 'frm_entries_list_query', $s_query, compact( 'form_id' ) );

		$orderby = self::get_param(
			array(
				'param'   => 'orderby',
				'default' => 'id',
			)
		);

		if ( strpos( $orderby, 'meta' ) !== false ) {
			$order_field_type = FrmField::get_type( str_replace( 'meta_', '', $orderby ) );
			$orderby          .= in_array( $order_field_type, array( 'number', 'scale', 'star' ) ) ? '+0' : '';
		}

		$order = self::get_param(
			array(
				'param'   => 'order',
				'default' => 'DESC',
			)
		);
		$order = FrmDb::esc_order( $orderby . ' ' . $order );

		$page  = $this->get_pagenum();
		$start = (int) self::get_param(
			array(
				'param'   => 'start',
				'default' => ( $page - 1 ) * $per_page,
			)
		);

		$limit       = FrmDb::esc_limit( $start . ',' . $per_page );
		$this->items = FrmEntry::getAll( $s_query, $order, $limit, true, $join_form_in_query );
		$total_items = FrmEntry::getRecordCount( $s_query );
		$this->total_items = $total_items;

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * @return void
	 */
	public function no_items() {
		$s = self::get_param(
			array(
				'param'    => 's',
				'sanitize' => 'sanitize_text_field',
			)
		);
		if ( ! empty( $s ) ) {
			esc_html_e( 'No Entries Found', 'formidable' );

			return;
		}

		$form_id = $this->params['form'];
		$form    = $this->params['form'];

		if ( $form_id ) {
			$form = FrmForm::getOne( $form_id );
		}
		$has_form = ! empty( $form );

		if ( ! $has_form ) {
			$has_form = FrmForm::getAll( array(), '', 1 );
			$has_form = ! empty( $has_form );
		}

		$colspan = $this->get_column_count();

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-entries/no_entries.php' );
	}

	/**
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		// Searching is a pro feature
	}

	/**
	 * @return void
	 */
	protected function display_tablenav( $which ) {
		$is_footer = ( $which !== 'top' );
		if ( $is_footer && ! empty( $this->items ) ) {
			?>
			<p>
				<?php esc_html_e( 'Getting spam form submissions?', 'formidable' ); ?>
				<a href="https://formidableforms.com/knowledgebase/add-spam-protection/" target="_blank">
					<?php esc_html_e( 'Learn how to prevent them.', 'formidable' ); ?>
				</a>
			</p>
			<?php
		}
		parent::display_tablenav( $which );
	}

	/**
	 * @return void
	 */
	protected function extra_tablenav( $which ) {
		$form_id = FrmAppHelper::simple_get( 'form', 'absint' );
		if ( $which === 'top' && ! $form_id ) {
			echo '<div class="alignleft actions">';

			// Override the referrer to prevent it from being used for the screen options.
			echo '<input type="hidden" name="_wp_http_referer" value="" />';

			FrmFormsHelper::forms_dropdown( 'form', $form_id, array( 'blank' => __( 'View all forms', 'formidable' ) ) );
			submit_button( __( 'Filter', 'formidable' ), 'filter_action action', '', false, array( 'id' => 'post-query-submit' ) );
			echo '</div>';
		}
	}

	/**
	 * Gets the name of the primary column in the Entries screen
	 *
	 * @since 2.0.14
	 *
	 * @return string $primary_column
	 */
	protected function get_primary_column_name() {
		$columns = get_column_headers( $this->screen );
		$hidden  = get_hidden_columns( $this->screen );

		$primary_column = '';

		foreach ( $columns as $column_key => $column_display_name ) {
			if ( 'cb' != $column_key && ! in_array( $column_key, $hidden ) ) {
				$primary_column = $column_key;
				break;
			}
		}

		return $primary_column;
	}

	/**
	 * @return string
	 */
	public function single_row( $item, $style = '' ) {
		// Set up the hover actions for this user
		$actions   = array();
		$view_link = '?page=formidable-entries&frm_action=show&id=' . $item->id;

		$this->get_actions( $actions, $item, $view_link );

		$action_links = $this->row_actions( $actions );

		// Set up the checkbox ( because the user is editable, otherwise its empty )
		$checkbox = "<input type='checkbox' name='item-action[]' id='cb-item-action-{$item->id}' value='{$item->id}' />";

		$r = "<tr id='item-action-{$item->id}'$style>";

		list( $columns, $hidden, , $primary ) = $this->get_column_info();
		$action_col                           = false;
		$action_columns                       = $this->get_action_columns();

		foreach ( $columns as $column_name => $column_display_name ) {
			$class = $column_name . ' column-' . $column_name;

			if ( $column_name === $primary ) {
				$class .= ' column-primary';
			}

			if ( in_array( $column_name, $hidden, true ) ) {
				$class .= ' frm_hidden';
			} elseif ( ! $action_col && ! in_array( $column_name, $action_columns, true ) ) {
				$action_col = $column_name;
			}

			$attributes = 'class="' . esc_attr( $class ) . '"';
			unset( $class );
			$attributes .= ' data-colname="' . $column_display_name . '"';

			$form_id           = $this->params['form'] ? $this->params['form'] : 0;
			$this->column_name = preg_replace( '/^(' . $form_id . '_)/', '', $column_name );

			if ( $this->column_name == 'cb' ) {
				$r .= "<th scope='row' class='check-column'>$checkbox</th>";
			} else {
				if ( in_array( $column_name, $hidden, true ) ) {
					$val = '';
				} else {
					$val = $this->column_value( $item );
				}

				$r .= "<td $attributes>";
				if ( $column_name == $action_col ) {
					$edit_link = admin_url( 'admin.php?page=formidable-entries&frm_action=edit&id=' . $item->id );
					$r         .= '<a href="' . esc_url( isset( $actions['edit'] ) ? $edit_link : $view_link ) . '" class="row-title" >' . $val . '</a> ';
					$r         .= $action_links;
				} else {
					$r .= $val;
				}
				$r .= '</td>';
			}
			unset( $val );
		}
		$r .= '</tr>';

		return $r;
	}

	/**
	 * Get the column names that the logged in user can action on
	 *
	 * @return string[]
	 */
	private function get_action_columns() {
		return array( 'cb', 'form_id', 'id', 'post_id' );
	}

	/**
	 * @param object $item
	 */
	private function column_value( $item ) {
		$col_name = $this->column_name;

		switch ( $col_name ) {
			case 'ip':
			case 'id':
			case 'item_key':
				$val = $item->{$col_name};
				break;
			case 'name':
			case 'description':
				$val = FrmAppHelper::truncate( strip_tags( $item->{$col_name} ), 100 );
				break;
			case 'created_at':
			case 'updated_at':
				$date = FrmAppHelper::get_formatted_time( $item->{$col_name} );
				$val  = '<abbr title="' . esc_attr( FrmAppHelper::get_formatted_time( $item->{$col_name}, '', 'g:i:s A' ) ) . '">' . $date . '</abbr>';
				break;
			case 'is_draft':
				$val = empty( $item->is_draft ) ? esc_html__( 'No', 'formidable' ) : esc_html__( 'Yes', 'formidable' );
				break;
			case 'form_id':
				$form_id             = $item->form_id;
				$user_can_edit_forms = false === FrmAppHelper::permission_nonce_error( 'frm_edit_forms' );
				if ( $user_can_edit_forms ) {
					$val = FrmFormsHelper::edit_form_link( $form_id );
				} else {
					$val = FrmFormsHelper::edit_form_link_label( $form_id );
				}
				break;
			case 'post_id':
				$val = FrmAppHelper::post_edit_link( $item->post_id );
				break;
			case 'user_id':
				$user = get_userdata( $item->user_id );
				$val  = $user ? $user->user_login : '';
				break;
			case 'parent_item_id':
				$val = $item->parent_item_id;
				break;
			default:
				$val = apply_filters( 'frm_entries_' . $col_name . '_column', false, compact( 'item' ) );
				if ( $val === false ) {
					$this->get_column_value( $item, $val );
				}

				/**
				 * Allows changing entries list column value.
				 *
				 * @since 5.1
				 *
				 * @param mixed $val Column value.
				 * @param array $args Contains `item` and `col_name`.
				 */
				$val = apply_filters( 'frm_entries_column_value', $val, compact( 'item', 'col_name' ) );
		}

		return $val;
	}

	/**
	 * @param string $view_link
	 * @param array $actions
	 * @param object $item
	 *
	 * @return void
	 */
	private function get_actions( &$actions, $item, $view_link ) {
		$actions['view'] = '<a href="' . esc_url( $view_link ) . '">' . __( 'View', 'formidable' ) . '</a>';

		if ( current_user_can( 'frm_delete_entries' ) ) {
			$delete_link       = '?page=formidable-entries&frm_action=destroy&id=' . $item->id . '&form=' . $this->params['form'];
			$actions['delete'] = '<a href="' . esc_url( wp_nonce_url( $delete_link ) ) . '" class="submitdelete" data-frmverify="' . esc_attr__( 'Permanently delete this entry?', 'formidable' ) . '" data-frmverify-btn="frm-button-red">' . __( 'Delete', 'formidable' ) . '</a>';
		}

		$actions = apply_filters( 'frm_row_actions', $actions, $item );
	}

	/**
	 * @param false $val
	 *
	 * @return void
	 */
	private function get_column_value( $item, &$val ) {
		$col_name = $this->column_name;

		if ( strpos( $col_name, 'frmsep_' ) === 0 ) {
			$sep_val  = true;
			$col_name = str_replace( 'frmsep_', '', $col_name );
		} else {
			$sep_val = false;
		}

		if ( strpos( $col_name, '-_-' ) ) {
			list( $col_name, $embedded_field_id ) = explode( '-_-', $col_name );
		}

		$field = FrmField::getOne( $col_name );
		if ( ! $field ) {
			return;
		}

		$atts = array(
			'type'              => $field->type,
			'truncate'          => true,
			'post_id'           => $item->post_id,
			'entry_id'          => $item->id,
			'embedded_field_id' => 0,
		);

		if ( $sep_val ) {
			$atts['saved_value'] = true;
		}

		if ( isset( $embedded_field_id ) ) {
			$atts['embedded_field_id'] = $embedded_field_id;
			unset( $embedded_field_id );
		}

		$val = FrmEntriesHelper::prepare_display_value( $item, $field, $atts );
	}

	/**
	 * @return string
	 */
	protected function confirm_bulk_delete() {
		return __( 'ALL selected entries in this form will be permanently deleted. Want to proceed?', 'formidable' );
	}
}
