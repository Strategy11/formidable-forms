<?php

class FrmXMLController {

    public static function menu() {
        add_submenu_page('formidable', 'Formidable | '. __( 'Import/Export', 'formidable' ), __( 'Import/Export', 'formidable' ), 'frm_edit_forms', 'formidable-import', 'FrmXMLController::route');
    }

    public static function add_default_templates() {
		if ( ! function_exists( 'libxml_disable_entity_loader' ) ) {
    		// XML import is not enabled on your server
    		return;
    	}

        $set_err = libxml_use_internal_errors(true);
        $loader = libxml_disable_entity_loader( true );

		$files = apply_filters( 'frm_default_templates_files', array( FrmAppHelper::plugin_path() . '/classes/views/xml/default-templates.xml' ) );

        foreach ( (array) $files as $file ) {
            FrmXMLHelper::import_xml($file);
            unset($file);
        }
        /*
        if(is_wp_error($result))
            $errors[] = $result->get_error_message();
        else if($result)
            $message = $result;
        */

        unset( $files );

        libxml_use_internal_errors( $set_err );
    	libxml_disable_entity_loader( $loader );
    }

    public static function route() {
        $action = isset( $_REQUEST['frm_action'] ) ? 'frm_action' : 'action';
		$action = FrmAppHelper::get_param( $action, '', 'get', 'sanitize_title' );
		if ( $action == 'import_xml' ) {
            return self::import_xml();
		} else if ( $action == 'export_xml' ) {
            return self::export_xml();
        } else {
            if ( apply_filters( 'frm_xml_route', true, $action ) ) {
                return self::form();
            }
        }
    }

    public static function form( $errors = array(), $message = '' ) {
        $forms = FrmForm::getAll( array( 'status' => array( null, '', 'published' ) ), 'name' );

        $export_types = apply_filters( 'frm_xml_export_types',
            array( 'forms' => __( 'Forms', 'formidable' ) )
        );

        $export_format = apply_filters( 'frm_export_formats', array(
            'xml' => array( 'name' => 'XML', 'support' => 'forms', 'count' => 'multiple' ),
        ) );

        if ( FrmAppHelper::pro_is_installed() ) {
            $frmpro_settings = new FrmProSettings();
            $csv_format = $frmpro_settings->csv_format;
        } else {
            $csv_format = 'UTF-8';
        }

        include(FrmAppHelper::plugin_path() .'/classes/views/xml/import_form.php');
    }

    public static function import_xml() {
        $errors = array();
        $message = '';

        $permission_error = FrmAppHelper::permission_nonce_error('frm_edit_forms', 'import-xml', 'import-xml-nonce');
        if ( $permission_error !== false ) {
            $errors[] = $permission_error;
            self::form($errors);
            return;
        }

        if ( ! isset($_FILES) || ! isset($_FILES['frm_import_file']) || empty($_FILES['frm_import_file']['name']) || (int) $_FILES['frm_import_file']['size'] < 1 ) {
            $errors[] = __( 'Oops, you didn\'t select a file.', 'formidable' );
            self::form($errors);
            return;
        }

        $file = $_FILES['frm_import_file']['tmp_name'];

        if ( ! is_uploaded_file( $file ) ) {
            unset($file);
            $errors[] = __( 'The file does not exist, please try again.', 'formidable' );
            self::form($errors);
            return;
        }

        //add_filter('upload_mimes', 'FrmXMLController::allow_mime');

        $export_format = apply_filters('frm_export_formats', array(
			'xml' => array( 'name' => 'XML', 'support' => 'forms', 'count' => 'multiple' ),
		) );

        $file_type = strtolower(pathinfo($_FILES['frm_import_file']['name'], PATHINFO_EXTENSION));
        if ( $file_type != 'xml' && isset( $export_format[ $file_type ] ) ) {
            // allow other file types to be imported
            do_action('frm_before_import_'. $file_type );
            return;
        }
        unset($file_type);

        //$media_id = FrmProAppHelper::upload_file('frm_import_file');

		if ( ! function_exists( 'libxml_disable_entity_loader' ) ) {
			$errors[] = __( 'XML import is not enabled on your server.', 'formidable' );
			self::form( $errors );
			return;
		}

		$set_err = libxml_use_internal_errors( true );
		$loader = libxml_disable_entity_loader( true );

		$result = FrmXMLHelper::import_xml( $file );
		FrmXMLHelper::parse_message( $result, $message, $errors );

		unset( $file );

		libxml_use_internal_errors( $set_err );
		libxml_disable_entity_loader( $loader );

        self::form($errors, $message);
    }

    public static function export_xml() {
        $error = FrmAppHelper::permission_nonce_error( 'frm_edit_forms', 'export-xml', 'export-xml-nonce' );
        if ( ! empty($error) ) {
            wp_die( $error );
        }

		$ids = FrmAppHelper::get_post_param( 'frm_export_forms', array() );
		$type = FrmAppHelper::get_post_param( 'type', array() );
		$format = FrmAppHelper::get_post_param( 'format', 'xml', 'sanitize_title' );

        if ( ! headers_sent() && ! $type ) {
            wp_redirect( esc_url_raw( admin_url( 'admin.php?page=formidable-import' ) ) );
            die();
        }

        if ( $format == 'xml' ) {
            self::generate_xml($type, compact('ids'));
        } else {
            do_action('frm_export_format_'. $format, compact('ids'));
        }

        wp_die();
    }

    public static function generate_xml($type, $args = array() ) {
    	global $wpdb;

	    $type = (array) $type;
        if ( in_array( 'items', $type) && ! in_array( 'forms', $type) ) {
            // make sure the form is included if there are entries
            $type[] = 'forms';
        }

	    if ( in_array( 'forms', $type) ) {
            // include actions with forms
	        $type[] = 'actions';
	    }

	    $tables = array(
	        'items'     => $wpdb->prefix .'frm_items',
	        'forms'     => $wpdb->prefix .'frm_forms',
	        'posts'     => $wpdb->posts,
	        'styles'    => $wpdb->posts,
	        'actions'   => $wpdb->posts,
	    );

		$defaults = array( 'ids' => false );
	    $args = wp_parse_args( $args, $defaults );

        $sitename = sanitize_key( get_bloginfo( 'name' ) );

    	if ( ! empty( $sitename ) ) {
			$sitename .= '.';
		}
    	$filename = $sitename . 'formidable.' . date( 'Y-m-d' ) . '.xml';

    	header( 'Content-Description: File Transfer' );
    	header( 'Content-Disposition: attachment; filename=' . $filename );
    	header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );

        //make sure ids are numeric
    	if ( is_array( $args['ids'] ) && ! empty( $args['ids'] ) ) {
	        $args['ids'] = array_filter( $args['ids'], 'is_numeric' );
	    }

	    $records = array();

		foreach ( $type as $tb_type ) {
            $where = array();
			$join = '';
            $table = $tables[ $tb_type ];

            $select = $table .'.id';
            $query_vars = array();

            switch ( $tb_type ) {
                case 'forms':
                    //add forms
                    if ( $args['ids'] ) {
						$where[] = array( 'or' => 1, $table . '.id' => $args['ids'], $table .'.parent_form_id' => $args['ids'] );
                	} else {
						$where[ $table . '.status !' ] = 'draft';
                	}
                break;
                case 'actions':
                    $select = $table .'.ID';
					$where['post_type'] = FrmFormActionsController::$action_post_type;
                    if ( ! empty($args['ids']) ) {
						$where['menu_order'] = $args['ids'];
                    }
                break;
                case 'items':
                    //$join = "INNER JOIN {$wpdb->prefix}frm_item_metas im ON ($table.id = im.item_id)";
                    if ( $args['ids'] ) {
						$where[ $table . '.form_id' ] = $args['ids'];
                    }
                break;
                case 'styles':
                    // Loop through all exported forms and get their selected style IDs
                    $form_ids = $args['ids'];
                    $style_ids = array();
                    foreach ( $form_ids as $form_id ) {
                        $form_data = FrmForm::getOne( $form_id );
                        // For forms that have not been updated while running 2.0, check if custom_style is set
                        if ( isset( $form_data->options['custom_style'] ) ) {
                            $style_ids[] = $form_data->options['custom_style'];
                        }
                        unset( $form_id, $form_data );
                    }
                    $select = $table .'.ID';
                    $where['post_type'] = 'frm_styles';

                    // Only export selected styles
                    if ( ! empty( $style_ids ) ) {
                        $where['ID'] = $style_ids;
                    }
                break;
                default:
                    $select = $table .'.ID';
                    $join = ' INNER JOIN ' . $wpdb->postmeta . ' pm ON (pm.post_id=' . $table . '.ID)';
                    $where['pm.meta_key'] = 'frm_form_id';

                    if ( empty($args['ids']) ) {
                        $where['pm.meta_value >'] = 1;
                    } else {
                        $where['pm.meta_value'] = $args['ids'];
                    }
                break;
            }

			$records[ $tb_type ] = FrmDb::get_col( $table . $join, $where, $select );
            unset($tb_type);
        }

		echo '<?xml version="1.0" encoding="' . esc_attr( get_bloginfo('charset') ) . "\" ?>\n";
        include(FrmAppHelper::plugin_path() .'/classes/views/xml/xml.php');
    }

    public static function allow_mime($mimes) {
        if ( ! isset( $mimes['csv'] ) ) {
            // allow csv files
            $mimes['csv'] = 'text/csv';
        }

        if ( ! isset( $mimes['xml'] ) ) {
            // allow xml
            $mimes['xml'] = 'text/xml';
        }

        return $mimes;
    }
}
