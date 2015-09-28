<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFormsListHelper extends FrmListHelper {
    var $status = '';

	public function __construct( $args ) {
		$this->status = isset( $_REQUEST['form_type'] ) ? $_REQUEST['form_type'] : '';

		parent::__construct( $args );
	}

	public function prepare_items() {
	    global $wpdb, $per_page, $mode;

	    $mode = empty( $_REQUEST['mode'] ) ? 'list' : $_REQUEST['mode'];

		$default_orderby = 'name';
		$default_order = 'ASC';

        $orderby = ( isset( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : $default_orderby;
		$order = ( isset( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : $default_order;

		$page = $this->get_pagenum();
		$per_page = $this->get_items_per_page( 'formidable_page_formidable_per_page' );

		$start = ( isset( $_REQUEST['start'] ) ) ? $_REQUEST['start'] : (( $page - 1 ) * $per_page);

        $s_query = array();
        $s_query[] = array( 'or' => 1, 'parent_form_id' => null, 'parent_form_id <' => 1 );
		switch ( $this->status ) {
		    case 'template':
                $s_query['is_template'] = 1;
                $s_query['status !'] = 'trash';
		        break;
		    case 'draft':
                $s_query['is_template'] = 0;
                $s_query['status'] = 'draft';
		        break;
		    case 'trash':
                $s_query['status'] = 'trash';
		        break;
		    default:
                $s_query['is_template'] = 0;
                $s_query['status !'] = 'trash';
		        break;
		}

        $s = isset( $_REQUEST['s'] ) ? stripslashes($_REQUEST['s']) : '';
	    if ( $s != '' ) {
	        preg_match_all('/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $s, $matches);
		    $search_terms = array_map('trim', $matches[0]);
	        foreach ( (array) $search_terms as $term ) {
                $s_query[] = array(
                    'or'    => true, 'name LIKE' => $term, 'description LIKE' => $term, 'created_at LIKE' => $term,
                );
	            unset($term);
            }
	    }

        $this->items = FrmForm::getAll($s_query, $orderby .' '. $order, $start .','. $per_page);
        $total_items = FrmDb::get_count( 'frm_forms', $s_query );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page' => $per_page,
		) );
	}

	public function no_items() {
	    if ( 'template' == $this->status ) {
            _e( 'No Templates Found.', 'formidable' ) ?>
            <br/><br/><?php _e( 'To add a new template:', 'formidable' ) ?>
            <ol><li><?php printf( __( 'Create a new %1$sform%2$s.', 'formidable' ), '<a href="'. esc_url( admin_url( 'admin?page=formidable&frm_action=new' ) ) . '"', '</a>' ) ?></li>
                <li><?php printf(__( 'After your form is created, go to Formidable -> %1$sForms%2$s.', 'formidable' ), '<a href="?page=formidable">', '</a>') ?></li>
                <li><?php _e( 'Place your mouse over the name of the form you just created, and click the "Create Template" link.', 'formidable' ) ?></li>
            </ol>
<?php   } else {
            _e( 'No Forms Found.', 'formidable' ) ?>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable&frm_action=new' ) ) ?>"><?php _e( 'Add New', 'formidable' ); ?></a>
<?php   }
	}

	public function get_bulk_actions() {
	    $actions = array();

		if ( in_array( $this->status, array( '', 'published' ) ) ) {
	        $actions['bulk_create_template'] = __( 'Create Template', 'formidable' );
	    }

	    if ( 'trash' == $this->status ) {
	        if ( current_user_can('frm_edit_forms') ) {
	            $actions['bulk_untrash'] = __( 'Restore', 'formidable' );
	        }

	        if ( current_user_can('frm_delete_forms') ) {
	            $actions['bulk_delete'] = __( 'Delete Permanently', 'formidable' );
	        }
	    } else if ( EMPTY_TRASH_DAYS && current_user_can('frm_delete_forms') ) {
	        $actions['bulk_trash'] = __( 'Move to Trash', 'formidable' );
	    } else if ( current_user_can('frm_delete_forms') ) {
	        $actions['bulk_delete'] = __( 'Delete');
	    }

        return $actions;
    }

	public function extra_tablenav( $which ) {
        if ( 'top' != $which ) {
            return;
        }

        if ( 'trash' == $this->status && current_user_can('frm_delete_forms') ) {
?>
            <div class="alignleft actions frm_visible_overflow">
			<?php submit_button( __( 'Empty Trash' ), 'apply', 'delete_all', false ); ?>
            </div>
<?php
            return;
        }

        if ( 'template' != $this->status ) {
            return;
        }

		$where = apply_filters( 'frm_forms_dropdown', array(), '' );
		$forms = FrmForm::get_published_forms( $where );

        $base = admin_url('admin.php?page=formidable&form_type=template');
        $args = array(
            'frm_action'    => 'duplicate',
            'template'      => true,
        );

?>
    <div class="alignleft actions frm_visible_overflow">
    <div class="dropdown frm_tiny_top_margin">
        <a href="#" id="frm-templateDrop" class="frm-dropdown-toggle button" data-toggle="dropdown"><?php _e( 'Create New Template', 'formidable' ) ?> <b class="caret"></b></a>
		<ul class="frm-dropdown-menu" role="menu" aria-labelledby="frm-templateDrop">
		<?php
        if ( empty( $forms ) ) { ?>
            <li class="frm_dropdown_li"><?php _e( 'You have not created any forms yet. <br/>You must create a form before you can make a template.', 'formidable' ) ?></li>
        <?php
        } else {
            foreach ( $forms as $form ) {
		        $args['id'] = $form->id; ?>
			<li><a href="<?php echo esc_url( add_query_arg( $args, $base ) ); ?>" tabindex="-1"><?php echo esc_html( empty( $form->name ) ? __( '(no title)' ) : FrmAppHelper::truncate( $form->name, 33 ) ); ?></a></li>
			<?php
			    unset($form);
			}
        }
        ?>
		</ul>
	</div>
	</div>
<?php
	}

	public function get_views() {

		$statuses = array(
		    'published' => __( 'My Forms', 'formidable' ),
		    'template'  => __( 'Templates', 'formidable' ),
		    'draft'     => __( 'Drafts', 'formidable' ),
		    'trash'     => __( 'Trash', 'formidable' ),
		);

	    $links = array();
	    $counts = FrmForm::get_count();
        $form_type = isset( $_REQUEST['form_type'] ) ? sanitize_text_field( $_REQUEST['form_type'] ) : 'published';

	    foreach ( $statuses as $status => $name ) {

	        if ( $status == $form_type ) {
    			$class = ' class="current"';
    		} else {
    		    $class = '';
    		}

    		if ( $counts->{$status} || 'published' == $status ) {
				$links[ $status ] = '<a href="' . esc_url( '?page=formidable&form_type=' . $status ) . '" ' . $class . '>' . sprintf( __( '%1$s <span class="count">(%2$s)</span>', 'formidable' ), $name, number_format_i18n( $counts->{$status} ) ) . '</a>';
		    }

		    unset($status, $name);
	    }

		return $links;
	}

	public function pagination( $which ) {
		global $mode;

		parent::pagination( $which );

		if ( 'top' == $which ) {
			$this->view_switcher( $mode );
		}
	}

	public function single_row( $item, $style = '' ) {
	    global $frm_vars, $mode;

		// Set up the hover actions for this user
		$actions = array();
        $edit_link = '?page=formidable&frm_action=edit&id='. $item->id;
	    $duplicate_link = '?page=formidable&frm_action=duplicate&id='. $item->id;

        $this->get_actions($actions, $item, $edit_link, $duplicate_link);

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
            $class = $column_name .' column-'. $column_name . ( ('name' == $column_name) ? ' post-title page-title column-title' : '' );

			$style = '';
			if ( in_array( $column_name, $hidden ) ) {
                $class .= ' frm_hidden';
			}

			$class = 'class="' . esc_attr( $class ) . '"';
			$data_colname = ' data-colname="' . esc_attr( $column_display_name ) . '"';
			$attributes = $class . $style . $data_colname;

			switch ( $column_name ) {
				case 'cb':
					$r .= '<th scope="row" class="check-column">'. $checkbox .'</th>';
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
				    $date = date($format, strtotime($item->created_at));
					$val = '<abbr title="' . esc_attr( date( 'Y/m/d g:i:s A', strtotime( $item->created_at ) ) ) . '">' . $date . '</abbr>';
					break;
				case 'shortcode':
					$val = '<input type="text" readonly="readonly" class="frm_select_box" value="' . esc_attr( '[formidable id=' . $item->id .']' ) . '" /><br/>';
				    if ( 'excerpt' == $mode ) {
						$val .= '<input type="text" readonly="readonly" class="frm_select_box" value="' . esc_attr( '[formidable key=' . $item->form_key . ']' ) . '" />';
				    }
			        break;
			    case 'entries':
					if ( isset( $item->options['no_save'] ) && $item->options['no_save'] ) {
			            $val = '<i class="frm_icon_font frm_forbid_icon frm_bstooltip" title="'. esc_attr('Entries are not being saved', 'formidable' ) .'"></i>';
			        } else {
			            $text = FrmEntry::getRecordCount($item->id);
                        $val = (current_user_can('frm_view_entries')) ? '<a href="'. esc_url(admin_url('admin.php') .'?page=formidable-entries&form='. $item->id ) .'">'. $text .'</a>' : $text;
                        unset($text);
                    }
			        break;
                case 'type':
                    $val = ( $item->is_template && $item->default_template ) ? __( 'Default', 'formidable' ) : __( 'Custom', 'formidable' );
                    break;
			}

			if ( isset($val) ) {
			    $r .= "<td $attributes>";
			    $r .= $val;
			    $r .= '</td>';
			}
			unset($val);
		}
		$r .= '</tr>';

		return $r;
	}

    /**
     * @param string $edit_link
     * @param string $duplicate_link
     */
    private function get_actions( &$actions, $item, $edit_link, $duplicate_link ) {
		if ( 'trash' == $this->status ) {
			if ( current_user_can('frm_edit_forms') ) {
				$actions['restore'] = FrmFormsHelper::delete_trash_link( $item->id, $item->status, 'short' );
			}

		    if ( current_user_can('frm_delete_forms') ) {
    		    $actions['trash'] = '<a href="' . esc_url(wp_nonce_url( '?page=formidable&form_status=trash&frm_action=destroy&id='. $item->id, 'destroy_form_'. $item->id )) .'" class="submitdelete"  onclick="return confirm(\''. __( 'Are you sure you want to permanently delete that?', 'formidable' ) .'\')">' . __( 'Delete Permanently' ) . '</a>';
    		}
            return;
		}

		if ( current_user_can('frm_edit_forms') ) {
            if ( ! $item->is_template || ! $item->default_template ) {
		        $actions['frm_edit'] = '<a href="'. esc_url( $edit_link ) . '">'. __( 'Edit') .'</a>';
            }

		    if ( $item->is_template ) {
				$actions['frm_duplicate'] = '<a href="'. esc_url( wp_nonce_url( $duplicate_link ) ) . '">' . __( 'Create Form from Template', 'formidable' ) . '</a>';
            } else {
    		    $actions['frm_settings'] = '<a href="'. esc_url('?page=formidable&frm_action=settings&id='. $item->id ) . '">'. __( 'Settings', 'formidable' ) .'</a>';

    		    if ( FrmAppHelper::pro_is_installed() ) {
					$actions['duplicate'] = '<a href="' . esc_url( wp_nonce_url( $duplicate_link ) ) . '">' . __( 'Duplicate', 'formidable' ) . '</a>';
        	    }
        	}
        }

		$actions['trash'] = FrmFormsHelper::delete_trash_link( $item->id, $item->status, 'short' );
		if ( empty( $actions['trash'] ) ) {
			// the user doesn't have permission
			unset( $actions['trash'] );
		}

		$actions['view'] = '<a href="' . esc_url( FrmFormsHelper::get_direct_link( $item->form_key, $item ) ) . '" target="_blank">' . __( 'Preview') . '</a>';
    }

    /**
     * @param string $edit_link
     */
	private function get_form_name( $item, $actions, $edit_link, $mode = 'list' ) {
        $form_name = $item->name;
        if ( trim($form_name) == '' ) {
            $form_name = __( '(no title)');
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
            $val .= ' - <span class="post-state">'. __( 'Draft', 'formidable' ) .'</span>';
        }
    }

    /**
     * @param string $val
     */
    private function add_form_description( $item, &$val ) {
        global $mode;
        if ( 'excerpt' == $mode ) {
            $val .= FrmAppHelper::truncate(strip_tags($item->description), 50);
        }
    }
}
