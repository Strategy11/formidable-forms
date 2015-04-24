<?php

class FrmEntriesListHelper extends FrmListHelper {

	public function prepare_items() {
        global $wpdb, $per_page;

        $per_page = $this->get_items_per_page( 'formidable_page_formidable_entries_per_page');

        $form_id = $this->params['form'];
        if ( ! $form_id ) {
            $this->items = array();
    		$this->set_pagination_args( array(
    			'total_items' => 0,
				'per_page' => $per_page,
    		) );
            return;
        }

		$default_orderby = 'id';
		$default_order = 'DESC';

	    $s_query = array( 'it.form_id' => $form_id );

		$s = isset( $_REQUEST['s'] ) ? stripslashes($_REQUEST['s']) : '';

	    if ( $s != '' && FrmAppHelper::pro_is_installed() ) {
	        $fid = isset( $_REQUEST['fid'] ) ? absint( $_REQUEST['fid'] ) : '';
	        $s_query = FrmProEntriesHelper::get_search_str( $s_query, $s, $form_id, $fid);
	    }

        $orderby = isset( $_REQUEST['orderby'] ) ? sanitize_title( $_REQUEST['orderby'] ) : $default_orderby;
        if ( strpos($orderby, 'meta') !== false ) {
            $order_field_type = FrmField::get_type( str_replace( 'meta_', '', $orderby ) );
            $orderby .= in_array( $order_field_type, array( 'number', 'scale') ) ? ' +0 ' : '';
        }

		$order = isset( $_REQUEST['order'] ) ? sanitize_title( $_REQUEST['order'] ) : $default_order;
		$order = ' ORDER BY ' . $orderby . ' ' . $order;

        $page = $this->get_pagenum();
		$start = (int) isset( $_REQUEST['start'] ) ? absint( $_REQUEST['start'] ) : ( ( $page - 1 ) * $per_page );

        $this->items = FrmEntry::getAll($s_query, $order, ' LIMIT '. $start .','. $per_page, true, false);
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

        $form_id = $form = $this->params['form'];
        if ( $form_id ) {
            $form = FrmForm::getOne($form_id);
        }
        $colspan = $this->get_column_count();

        include(FrmAppHelper::plugin_path() .'/classes/views/frm-entries/no_entries.php');
	}

	public function search_box( $text, $input_id ) {
    	if ( ! $this->has_items() && ! isset( $_REQUEST['s'] ) ) {
    		return;
    	}

        if ( isset($this->params['form']) ) {
            $form = FrmForm::getOne($this->params['form']);
        } else {
			$form = FrmForm::get_published_forms( array(), 1 );
        }

        if ( $form ) {
            $field_list = FrmField::getAll( array( 'fi.form_id' => $form->id, 'fi.type not' => FrmFieldsHelper::no_save_fields() ), 'field_order');
        }

        $fid = isset($_REQUEST['fid']) ? esc_attr( stripslashes( $_REQUEST['fid'] ) ) : '';
    	$input_id = $input_id . '-search-input';
        $search_str = isset($_REQUEST['s']) ? esc_attr( stripslashes( $_REQUEST['s'] ) ) : '';

        foreach ( array( 'orderby', 'order') as $get_var ) {
        	if ( ! empty( $_REQUEST[ $get_var ] ) ) {
        		echo '<input type="hidden" name="'. esc_attr( $get_var ) .'" value="' . esc_attr( $_REQUEST[ $get_var ] ) . '" />';
        	}
        }

?>
<div class="search-box frm_sidebar">
    <label class="screen-reader-text" for="<?php echo esc_attr( $input_id ) ?>"><?php echo esc_attr( $text ); ?>:</label>
    <input type="text" id="<?php echo esc_attr( $input_id ) ?>" name="s" value="<?php echo esc_attr( $search_str ); ?>" />
    <?php
	if ( isset( $field_list ) && ! empty( $field_list ) ) { ?>
    <select name="fid" class="hide-if-js">
        <option value="">&mdash; <?php _e( 'All Fields', 'formidable' ) ?> &mdash;</option>
        <option value="created_at" <?php selected($fid, 'created_at') ?>><?php _e( 'Entry creation date', 'formidable' ) ?></option>
        <option value="id" <?php selected($fid, 'id') ?>><?php _e( 'Entry ID', 'formidable' ) ?></option>
        <?php foreach ( $field_list as $f ) { ?>
        <option value="<?php echo ($f->type == 'user_id') ? 'user_id' : $f->id ?>" <?php selected($fid, $f->id) ?>><?php echo FrmAppHelper::truncate($f->name, 30);  ?></option>
        <?php } ?>
    </select>

    <div class="button dropdown hide-if-no-js">
        <a href="#" id="frm-fid-search" class="frm-dropdown-toggle" data-toggle="dropdown"><?php _e( 'Search', 'formidable' ) ?> <b class="caret"></b></a>
        <ul class="frm-dropdown-menu pull-right" id="frm-fid-search-menu" role="menu" aria-labelledby="frm-fid-search">
            <li><a href="#" id="fid-">&mdash; <?php _e( 'All Fields', 'formidable' ) ?> &mdash;</a></li>
            <li><a href="#" id="fid-created_at"><?php _e( 'Entry creation date', 'formidable' ) ?></a></li>
            <li><a href="#" id="fid-id"><?php _e( 'Entry ID', 'formidable' ) ?></a></li>
    	    <?php
			foreach ( $field_list as $f ) { ?>
            <li><a href="#" id="fid-<?php echo ($f->type == 'user_id') ? 'user_id' : $f->id ?>"><?php echo FrmAppHelper::truncate($f->name, 30); ?></a></li>
    	    <?php
    	        unset($f);
    	    } ?>
        </ul>
    </div>
    <?php submit_button( $text, 'button hide-if-js', false, false, array( 'id' => 'search-submit') );
    } else {
        submit_button( $text, 'button', false, false, array( 'id' => 'search-submit') );
		if ( ! empty( $search_str ) ) { ?>
	<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-entries&frm_action=list&form=' . $form->id ) ) ?>"><?php _e( 'Reset', 'formidable' ) ?></a>
    <?php
		}
    } ?>

</div>
<?php
	}

	public function single_row( $item, $style = '' ) {
		// Set up the hover actions for this user
		$actions = array();
		$view_link = '?page=formidable-entries&frm_action=show&id='. $item->id;

		$this->get_actions( $actions, $item, $view_link );

        $action_links = $this->row_actions( $actions );

		// Set up the checkbox ( because the user is editable, otherwise its empty )
		$checkbox = "<input type='checkbox' name='item-action[]' id='cb-item-action-{$item->id}' value='{$item->id}' />";

		$r = "<tr id='item-action-{$item->id}'$style>";

		list( $columns, $hidden ) = $this->get_column_info();
        $action_col = false;

		foreach ( $columns as $column_name => $column_display_name ) {
			$class = $column_name .' column-'. $column_name;

			if ( in_array( $column_name, $hidden ) ) {
				$class .= ' frm_hidden';
			} else if ( ! $action_col && ! in_array($column_name, array( 'cb', 'id', 'form_id', 'post_id')) ) {
			    $action_col = $column_name;
            }

			$attributes = 'class="' . esc_attr( $class ) . '"';
            unset($class);

            $col_name = preg_replace('/^('. $this->params['form'] .'_)/', '', $column_name);

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
    				if ( strpos($col_name, 'frmsep_') === 0 ) {
    				    $sep_val = true;
    				    $col_name = str_replace('frmsep_', '', $col_name);
    				} else {
    				    $sep_val = false;
    				}

    				if ( strpos($col_name, '-_-') ) {
    				    list($col_name, $embedded_field_id) = explode('-_-', $col_name);
    				}

    				$col = FrmField::getOne($col_name);

                    $atts = array(
                        'type' => $col->type, 'truncate' => true,
                        'post_id' => $item->post_id, 'entry_id' => $item->id,
                        'embedded_field_id' => 0,
                    );

                    if ( $sep_val ) {
                        $atts['saved_value'] = true;
                    }

    				if ( isset($embedded_field_id) ) {
                        $atts['embedded_field_id'] = $embedded_field_id;
    				    unset($embedded_field_id);
    				}

                    $val = FrmEntriesHelper::prepare_display_value($item, $col, $atts);

				break;
			}

			if ( isset( $val ) ) {
			    $r .= "<td $attributes>";
				if ( $column_name == $action_col ) {
					$edit_link = '?page=formidable-entries&frm_action=edit&id='. $item->id;
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
		$actions['view'] = '<a href="' . esc_url( $view_link ) . '">'. __( 'View', 'formidable' ) .'</a>';

        if ( current_user_can('frm_delete_entries') ) {
            $delete_link = '?page=formidable-entries&frm_action=destroy&id='. $item->id .'&form='. $this->params['form'];
			$actions['delete'] = '<a href="' . esc_url( wp_nonce_url( $delete_link ) ) . '" class="submitdelete" onclick="return confirm(\'' . esc_attr( __( 'Are you sure you want to delete that?', 'formidable' ) ) . '\')">' . __( 'Delete' ) . '</a>';
	    }

        $actions = apply_filters('frm_row_actions', $actions, $item);
    }

}
