<?php

class FrmEntriesListHelper extends FrmListHelper {
	protected $column_name;
	protected $item;
	protected $field;

	public function prepare_items() {
        global $per_page;

		$per_page = $this->get_items_per_page( 'formidable_page_formidable_entries_per_page' );
        $form_id = $this->params['form'];

		$default_orderby = 'id';
		$default_order = 'DESC';
		$s_query = array();

		if ( $form_id ) {
			$s_query['it.form_id'] = $form_id;
		}

		$s = isset( $_REQUEST['s'] ) ? stripslashes($_REQUEST['s']) : '';

	    if ( $s != '' && FrmAppHelper::pro_is_installed() ) {
	        $fid = isset( $_REQUEST['fid'] ) ? sanitize_title( $_REQUEST['fid'] ) : '';
	        $s_query = FrmProEntriesHelper::get_search_str( $s_query, $s, $form_id, $fid );
	    }

        $orderby = isset( $_REQUEST['orderby'] ) ? sanitize_title( $_REQUEST['orderby'] ) : $default_orderby;
        if ( strpos($orderby, 'meta') !== false ) {
            $order_field_type = FrmField::get_type( str_replace( 'meta_', '', $orderby ) );
			$orderby .= in_array( $order_field_type, array( 'number', 'scale' ) ) ? ' +0 ' : '';
        }

		$order = isset( $_REQUEST['order'] ) ? sanitize_title( $_REQUEST['order'] ) : $default_order;
		$order = ' ORDER BY ' . $orderby . ' ' . $order;

        $page = $this->get_pagenum();
		$start = (int) isset( $_REQUEST['start'] ) ? absint( $_REQUEST['start'] ) : ( ( $page - 1 ) * $per_page );

		$this->items = FrmEntry::getAll( $s_query, $order, ' LIMIT ' . $start . ',' . $per_page, true, false );
        $total_items = FrmEntry::getRecordCount($s_query);

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page' => $per_page,
		) );
	}

	public function no_items() {
        $s = isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : '';
	    if ( ! empty($s) ) {
            _e( 'No Entries Found', 'formidable' );
            return;
        }

		$form_id = $this->params['form'];
		$form = $this->params['form'];

        if ( $form_id ) {
            $form = FrmForm::getOne($form_id);
        }
        $colspan = $this->get_column_count();

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-entries/no_entries.php' );
	}

	public function search_box( $text, $input_id ) {
		// Searching is a pro feature
	}

	protected function extra_tablenav( $which ) {
		$form_id = FrmAppHelper::simple_get( 'form', 'absint' );
		if ( $which == 'top' && empty( $form_id ) ) {
			echo '<div class="alignleft actions">';
			echo FrmFormsHelper::forms_dropdown( 'form', $form_id, array( 'blank' => __( 'View all forms', 'formidable' ) ) );
			submit_button( __( 'Filter' ), 'filter_action', '', false, array( 'id' => "post-query-submit" ) );
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
		$hidden = get_hidden_columns( $this->screen );

		$primary_column = '';

		foreach ( $columns as $column_key => $column_display_name ) {
			if ( 'cb' != $column_key && ! in_array( $column_key, $hidden ) ) {
				$primary_column = $column_key;
				break;
			}
		}

		return $primary_column;
	}

	public function single_row( $item, $style = '' ) {
		// Set up the hover actions for this user
		$actions = array();
		$view_link = '?page=formidable-entries&frm_action=show&id=' . $item->id;

		$this->get_actions( $actions, $item, $view_link );

        $action_links = $this->row_actions( $actions );

		// Set up the checkbox ( because the user is editable, otherwise its empty )
		$checkbox = "<input type='checkbox' name='item-action[]' id='cb-item-action-{$item->id}' value='{$item->id}' />";

		$r = "<tr id='item-action-{$item->id}'$style>";

		list( $columns, $hidden, , $primary ) = $this->get_column_info();
        $action_col = false;

		foreach ( $columns as $column_name => $column_display_name ) {
			$class = $column_name . ' column-' . $column_name;

			if ( $column_name === $primary ) {
				$class .= ' column-primary';
			}

			if ( in_array( $column_name, $hidden ) ) {
				$class .= ' frm_hidden';
			} else if ( ! $action_col && ! in_array( $column_name, array( 'cb', 'id', 'form_id', 'post_id' ) ) ) {
			    $action_col = $column_name;
            }

			$attributes = 'class="' . esc_attr( $class ) . '"';
			unset($class);
			$attributes .= ' data-colname="' . $column_display_name . '"';

			$form_id = $this->params['form'] ? $this->params['form'] : 0;
			$col_name = preg_replace( '/^(' . $form_id . '_)/', '', $column_name );
			$this->column_name = $col_name;

			switch ( $col_name ) {
				case 'cb':
					$r .= "<th scope='row' class='check-column'>$checkbox</th>";
					break;
				case 'ip':
				case 'id':
				case 'item_key':
				    $val = $item->{$col_name};
				    break;
				case 'name':
				case 'description':
				    $val = FrmAppHelper::truncate(strip_tags($item->{$col_name}), 100);
				    break;
				case 'created_at':
				case 'updated_at':
				    $date = FrmAppHelper::get_formatted_time($item->{$col_name});
					$val = '<abbr title="' . esc_attr( FrmAppHelper::get_formatted_time( $item->{$col_name}, '', 'g:i:s A' ) ) . '">' . $date . '</abbr>';
					break;
				case 'is_draft':
				    $val = empty($item->is_draft) ? __( 'No') : __( 'Yes');
			        break;
				case 'form_id':
				    $val = FrmFormsHelper::edit_form_link($item->form_id);
    				break;
				case 'post_id':
				    $val = FrmAppHelper::post_edit_link($item->post_id);
				    break;
				case 'user_id':
				    $user = get_userdata($item->user_id);
				    $val = $user->user_login;
				    break;
				default:
					$val = apply_filters( 'frm_entries_' . $col_name . '_column', false, compact( 'item' ) );
					if ( $val === false ) {
						$this->get_column_value( $item, $val );
					}
				break;
			}

			if ( isset( $val ) ) {
			    $r .= "<td $attributes>";
				if ( $column_name == $action_col ) {
					$edit_link = '?page=formidable-entries&frm_action=edit&id=' . $item->id;
					$r .= '<a href="' . esc_url( isset( $actions['edit'] ) ? $edit_link : $view_link ) . '" class="row-title" >' . $val . '</a> ';
			        $r .= $action_links;
				} else {
			        $r .= $val;
			    }
			    $r .= '</td>';
			}
			unset($val);
		}
		$r .= '</tr>';

		return $r;
	}

    /**
     * @param string $view_link
     */
    private function get_actions( &$actions, $item, $view_link ) {
		$actions['view'] = '<a href="' . esc_url( $view_link ) . '">' . __( 'View', 'formidable' ) . '</a>';

        if ( current_user_can('frm_delete_entries') ) {
			$delete_link = '?page=formidable-entries&frm_action=destroy&id=' . $item->id . '&form=' . $this->params['form'];
			$actions['delete'] = '<a href="' . esc_url( wp_nonce_url( $delete_link ) ) . '" class="submitdelete" onclick="return confirm(\'' . esc_attr( __( 'Are you sure you want to delete that?', 'formidable' ) ) . '\')">' . __( 'Delete' ) . '</a>';
	    }

        $actions = apply_filters('frm_row_actions', $actions, $item);
    }

	private function get_column_value( $item, &$val ) {
		$col_name = $this->column_name;

		if ( strpos( $col_name, 'frmsep_' ) === 0 ) {
			$sep_val = true;
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
			'type' => $field->type, 'truncate' => true,
			'post_id' => $item->post_id, 'entry_id' => $item->id,
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
}
