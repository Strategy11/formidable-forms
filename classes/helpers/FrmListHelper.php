<?php

if ( !defined('ABSPATH') ) die('You are not allowed to call this page directly.');

if(class_exists('FrmListHelper'))
    return;

class FrmListHelper extends WP_List_Table {
    
	function __construct($args) {
	    global $frm_settings;
	    
	    $args = wp_parse_args( $args, array(
			'ajax' => false,
			'table_name' => '',
			'page_name' => '',
			'params' => array()
		) );
		$this->table_name = $args['table_name'];
		$this->page_name = $args['page_name'];
		$this->params = $args['params'];
		
	    $screen = get_current_screen();

		parent::__construct( $args );
	}

	function ajax_user_can() {
		return current_user_can( 'administrator' );
	}

	function prepare_items() {
	    global $wpdb, $per_page, $frm_settings;
		$paged = $this->get_pagenum();
		$default_orderby = 'name';
		$default_order = 'ASC';
		
        $orderby = ( isset( $_REQUEST['orderby'] ) ) ? $_REQUEST['orderby'] : $default_orderby;
		$order = ( isset( $_REQUEST['order'] ) ) ? $_REQUEST['order'] : $default_order;
		
		$page = $this->get_pagenum();
		$default_count = empty($this->page_name) ? 20 : 10;
		$per_page = $this->get_items_per_page( 'formidable_page_formidable'. str_replace('-', '_', $this->page_name) .'_per_page', $default_count);

		$start = ( isset( $_REQUEST['start'] ) ) ? $_REQUEST['start'] : (( $page - 1 ) * $per_page);
		$s = isset( $_REQUEST['s'] ) ? stripslashes($_REQUEST['s']) : '';
		$fid = isset( $_REQUEST['fid'] ) ? $_REQUEST['fid'] : '';
		if($s != ''){
		    preg_match_all('/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $s, $matches);
		    $search_terms = array_map('trim', $matches[0]);
		}
		
		$s_query =  " (status is NULL OR status = '' OR status = 'published') AND default_template=0 AND is_template = ". (int)$this->params['template'];

	    if($s != ''){
	        foreach ( (array) $search_terms as $term ) {
	            if ( !empty($s_query) ) {
                    $s_query .= " AND";
                }
                
	            $term = FrmAppHelper::esc_like($term);
	            
	            $s_query .= $wpdb->prepare(" (name like %s OR description like %s OR created_at like %s)", '%'. $term .'%', '%'. $term .'%', '%'. $term .'%');
	            
	            unset($term);
            }
	    }
	    
	    $frm_form = new FrmForm();
        $this->items = $frm_form->getAll($s_query, " ORDER BY $orderby $order", " LIMIT $start, $per_page", true, false);
        $total_items = FrmAppHelper::getRecordCount($s_query, $this->table_name);
		

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page' => $per_page
		) );
	}
	
	function no_items() {
	    if ($this->params['template']){
            _e('No Templates Found', 'formidable') ?>. 
            <br/><br/><?php _e('To add a new template','formidable') ?>:
            <ol><li><?php printf(__('Create a new %1$sform%2$s.', 'formidable'), '<a href="?page=formidable&amp;frm_action=new-selection">', '</a>') ?></li>
                <li><?php printf(__('After your form is created, go to Formidable -> %1$sForms%2$s.', 'formidable'), '<a href="?page=formidable">', '</a>') ?></li>
                <li><?php _e('Place your mouse over the name of the form you just created, and click the "Create Template" link.', 'formidable') ?></li>
            </ol>
<?php   }else{ 
            _e('No Forms Found', 'formidable') ?>. 
            <a href="?page=formidable&amp;frm_action=new-selection"><?php _e('Add New', 'formidable'); ?></a>
<?php   }
	}
	
	function get_bulk_actions(){
	    $actions = array();
	    if ( current_user_can('frm_delete_forms') ) {
            $actions['bulk_delete'] = __('Delete');
        }
            
        return $actions;
    }

	function display_rows() {
		$style = '';
		foreach ( $this->items as $item ) {
			$style = ( ' class="alternate"' == $style ) ? '' : ' class="alternate"';
			echo "\n\t", $this->single_row( $item, $style );
		}
	}
	
	function single_row( $item, $style='') {
	    global $frm_vars, $frm_entry;
		$checkbox = '';
		
		// Set up the hover actions for this user
		$actions = array();
		$title = esc_attr(strip_tags($item->name));
		
		if ( current_user_can('frm_edit_forms') ) {
		    $edit_link = "?page=formidable&frm_action=edit&id={$item->id}";
		    $duplicate_link = "?page=formidable&frm_action=duplicate&id={$item->id}";
		    
		    $actions['frm_edit'] = "<a href='" . esc_url( $edit_link ) . "'>". __('Edit') ."</a>";
		    
		    if ( $this->params['template'] ) {
		        $actions['frm_duplicate'] = "<a href='" . wp_nonce_url( $duplicate_link ) . "'>". __('Create Form from Template', 'formidable') ."</a>";
            } else {
    		    $actions['frm_settings'] = "<a href='" . wp_nonce_url( "?page=formidable&frm_action=settings&id={$item->id}" ) . "'>". __('Settings', 'formidable') ."</a>";
    		    
    		    if ( $frm_vars['pro_is_installed'] ) {
        	        $actions['duplicate'] = '<a href="' . wp_nonce_url( $duplicate_link ) . '">'. __('Duplicate', 'formidable') .'</a>';
        	    }
        	}
        }
        
        $delete_link = "?page=formidable&frm_action=destroy&id={$item->id}";
        if(current_user_can('frm_delete_forms'))
		    $actions['trash'] = '<a class="submitdelete" href="' . wp_nonce_url( $delete_link ) .'" onclick="return confirm(\''. __('Are you sure you want to delete that?', 'formidable') .'\')">' . __( 'Delete' ) . '</a>';
		
		$actions['view'] = '<a href="'. FrmFormsHelper::get_direct_link($item->form_key, $item) .'" target="_blank">'. __('Preview') .'</a>';  
        
        $action_links = $this->row_actions( $actions );
        
		// Set up the checkbox ( because the user is editable, otherwise its empty )
		$checkbox = '<input type="checkbox" name="item-action[]" id="cb-item-action-'. $item->id .'" value="'. $item->id .'" />';

		$r = '<tr id="item-action-'. $item->id .'"'. $style .'>';

		list( $columns, $hidden ) = $this->get_column_info();
        $action_col = false;

		foreach ( $columns as $column_name => $column_display_name ) {
			$class = 'class="'. $column_name .' column-'. $column_name .'"';

			$style = '';
			if ( in_array( $column_name, $hidden ) )
				$style = ' style="display:none;"';
			else if(!$action_col and !in_array($column_name, array('cb', 'id')))
			    $action_col = $column_name;

			$attributes = "$class$style";

			switch ( $column_name ) {
				case 'cb':
					$r .= '<th scope="row" class="check-column">'. $checkbox .'</th>';
					break;
				case 'id':
				case 'form_key':
				    $val = $item->{$column_name};
				    break;
				case 'name':
				    if(trim($item->{$column_name}) == '')
				        $val = __('(no title)');
				    else
				        $val = FrmAppHelper::truncate(strip_tags($item->{$column_name}), 50);
				    break;
				case 'description':
				    $val = FrmAppHelper::truncate(strip_tags($item->{$column_name}), 50);
				    break;
				case 'created_at':
				    $format = 'Y/m/d'; //get_option('date_format');
				    $date = date($format, strtotime($item->{$column_name}));
					$val = "<abbr title='". date($format .' g:i:s A', strtotime($item->{$column_name})) ."'>". $date ."</abbr>";
					break;
				case 'shortcode':
				    $val = '<input type="text" readonly="true" class="frm_select_box" value="'. esc_attr("[formidable id={$item->id}]") .'" /><br/>';
				    $val .= '<input type="text" readonly="true" class="frm_select_box" value="'. esc_attr("[formidable key={$item->form_key}]") .'" />';
			        break;
			    case 'entries':
			        $text = $frm_entry->getRecordCount($item->id);
                    //$text = sprintf(_n( '%1$s Entry', '%1$s Entries', $text, 'formidable' ), $text);
                    $val = (current_user_can('frm_view_entries')) ? '<a href="'. esc_url(admin_url('admin.php') .'?page=formidable-entries&form='. $item->id ) .'">'. $text .'</a>' : $text;
                    unset($text);
			        break;
			    case 'link':
			        $links = array();
                    if($frm_vars['pro_is_installed'] and current_user_can('frm_create_entries'))
                		$links[] = '<a href="'. wp_nonce_url( "?page=formidable-entries&frm_action=new&form={$item->id}" ) .'" class="frm_add_entry_icon frm_icon_font frm_bstooltip" title="'. __('Add Entry', 'formidable'). '" data-toggle="tooltip"> </a>';
                	
                	if ( current_user_can('frm_edit_forms') ){
                	    $links[] = '<a href="' . wp_nonce_url( "?page=formidable&frm_action=duplicate&id={$item->id}&template=1" ) .'" class="frm_icon_font frm_new_template_icon frm_bstooltip" title="'. __('Create template from form', 'formidable') .'" data-toggle="tooltip"> </a>';
                	}
                	
                    $val = implode(' ', $links);
                    break;
				default:
				    $val = $column_name;
				break;
			}
			
			if(isset($val)){
			    $r .= "<td $attributes>";
			    if($column_name == $action_col){                              
			        $r .= '<a class="row-title" href="'. ( isset($actions['frm_edit']) ? $edit_link : FrmFormsHelper::get_direct_link($item->form_key, $item) ) .'">'. $val .'</a> ';
			        $r .= $action_links;
			    }else{
			        $r .= $val;
			    }
			    $r .= '</td>';
			}
			unset($val);
		}
		$r .= '</tr>';

		return $r;
	}
	
}
