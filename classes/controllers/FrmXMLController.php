<?php

class FrmXMLController {

	public static function menu() {
		add_submenu_page( 'formidable', 'Formidable | ' . __( 'Import/Export', 'formidable' ), __( 'Import/Export', 'formidable' ), 'frm_edit_forms', 'formidable-import', 'FrmXMLController::route' );
	}

	public static function add_default_templates() {
		if ( ! function_exists( 'libxml_disable_entity_loader' ) ) {
			// XML import is not enabled on your server
			return;
		}

		$set_err = libxml_use_internal_errors( true );
		$loader  = libxml_disable_entity_loader( true );

		$files = apply_filters( 'frm_default_templates_files', array() );

		foreach ( (array) $files as $file ) {
			FrmXMLHelper::import_xml( $file );
			unset( $file );
		}

		unset( $files );

		libxml_use_internal_errors( $set_err );
		libxml_disable_entity_loader( $loader );
	}

	/**
	 * Use the template link to install the XML template
	 *
	 * @since 3.06
	 */
	public static function install_template() {
		FrmAppHelper::permission_check( 'frm_create_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$url = FrmAppHelper::get_param( 'xml', '', 'post', 'esc_url_raw' );

		$response = wp_remote_get( $url );
		$body     = wp_remote_retrieve_body( $response );
		$xml      = simplexml_load_string( $body );

		if ( ! $xml ) {
			$response = array(
				'message' => __( 'There was an error reading the form template', 'formidable' ),
			);
			echo wp_json_encode( $response );
			wp_die();
		}

		self::set_new_form_name( $xml );

		$imported = FrmXMLHelper::import_xml_now( $xml );
		if ( isset( $imported['form_status'] ) && ! empty( $imported['form_status'] ) ) {
			// Get the last form id in case there are child forms.
			end( $imported['form_status'] );
			$form_id  = key( $imported['form_status'] );
			$response = array(
				'id'       => $form_id,
				'redirect' => FrmForm::get_edit_link( $form_id ),
				'success'  => 1,
			);
		} else {
			$response = array(
				'message' => __( 'There was an error importing form', 'formidable' ),
			);
		}

		echo wp_json_encode( $response );
		wp_die();
	}

	/**
	 * Change the name of the last form that is not a child.
	 * This will allow for lookup fields and embedded forms
	 * since we redirect to the last form.
	 *
	 * @since 3.06
	 *
	 * @param object $xml The values included in the XML.
	 */
	private static function set_new_form_name( &$xml ) {
		if ( ! isset( $xml->form ) ) {
			return;
		}

		// Get the main form ID.
		$set_name = 0;
		foreach ( $xml->form as $form ) {
			if ( ! isset( $form->parent_form_id ) || empty( $form->parent_form_id ) ) {
				$set_name = $form->id;
			}
		}

		foreach ( $xml->form as $form ) {
			// Maybe set the form name if this isn't a child form.
			if ( $set_name == $form->id ) {
				$form->name        = FrmAppHelper::get_param( 'name', '', 'post', 'sanitize_text_field' );
				$form->description = FrmAppHelper::get_param( 'desc', '', 'post', 'sanitize_textarea_field' );
			}

			// Use a unique key to prevent editing existing form.
			$name           = sanitize_title( $form->name );
			$form->form_key = FrmAppHelper::get_unique_key( $name, 'frm_forms', 'form_key' );
		}
	}

	public static function route() {
		$action = isset( $_REQUEST['frm_action'] ) ? 'frm_action' : 'action';
		$action = FrmAppHelper::get_param( $action, '', 'get', 'sanitize_title' );
		FrmAppHelper::include_svg();

		if ( 'import_xml' === $action ) {
			return self::import_xml();
		} elseif ( 'export_xml' === $action ) {
			return self::export_xml();
		} elseif ( apply_filters( 'frm_xml_route', true, $action ) ) {
			return self::form();
		}
	}

	public static function form( $errors = array(), $message = '' ) {
		$where = array(
			'status' => array( null, '', 'published' ),
		);
		$forms = FrmForm::getAll( $where, 'name' );

		$export_types = array(
			'forms' => __( 'Forms', 'formidable' ),
			'items' => __( 'Entries', 'formidable' ),
		);
		$export_types = apply_filters( 'frm_xml_export_types', $export_types );

		$export_format = array(
			'xml' => array(
				'name'    => 'XML',
				'support' => 'forms',
				'count'   => 'multiple',
			),
			'csv' => array(
				'name'    => 'CSV',
				'support' => 'items',
				'count'   => 'single',
			),
		);
		$export_format = apply_filters( 'frm_export_formats', $export_format );

		include( FrmAppHelper::plugin_path() . '/classes/views/xml/import_form.php' );
	}

	public static function import_xml() {
		$errors  = array();
		$message = '';

		$permission_error = FrmAppHelper::permission_nonce_error( 'frm_edit_forms', 'import-xml', 'import-xml-nonce' );
		if ( false !== $permission_error ) {
			$errors[] = $permission_error;
			self::form( $errors );

			return;
		}

		$has_file = isset( $_FILES ) && isset( $_FILES['frm_import_file'] ) && ! empty( $_FILES['frm_import_file']['name'] ) && ! empty( $_FILES['frm_import_file']['size'] ) && (int) $_FILES['frm_import_file']['size'] > 0;
		if ( ! $has_file ) {
			$errors[] = __( 'Oops, you didn\'t select a file.', 'formidable' );
			self::form( $errors );

			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$file = isset( $_FILES['frm_import_file']['tmp_name'] ) ? sanitize_option( 'upload_path', $_FILES['frm_import_file']['tmp_name'] ) : '';

		if ( ! is_uploaded_file( $file ) ) {
			unset( $file );
			$errors[] = __( 'The file does not exist, please try again.', 'formidable' );
			self::form( $errors );

			return;
		}

		//add_filter('upload_mimes', 'FrmXMLController::allow_mime');

		$export_format = array(
			'xml' => array(
				'name'    => 'XML',
				'support' => 'forms',
				'count'   => 'multiple',
			),
		);
		$export_format = apply_filters( 'frm_export_formats', $export_format );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$file_type = sanitize_option( 'upload_path', $_FILES['frm_import_file']['name'] );
		$file_type = strtolower( pathinfo( $file_type, PATHINFO_EXTENSION ) );
		if ( 'xml' !== $file_type && isset( $export_format[ $file_type ] ) ) {
			// allow other file types to be imported
			do_action( 'frm_before_import_' . $file_type );

			return;
		}
		unset( $file_type );

		if ( ! function_exists( 'libxml_disable_entity_loader' ) ) {
			$errors[] = __( 'XML import is not enabled on your server with the libxml_disable_entity_loader function.', 'formidable' );
			self::form( $errors );

			return;
		}

		$set_err = libxml_use_internal_errors( true );
		$loader  = libxml_disable_entity_loader( true );

		$result = FrmXMLHelper::import_xml( $file );
		FrmXMLHelper::parse_message( $result, $message, $errors );

		unset( $file );

		libxml_use_internal_errors( $set_err );
		libxml_disable_entity_loader( $loader );

		self::form( $errors, $message );
	}

	public static function export_xml() {
		$error = FrmAppHelper::permission_nonce_error( 'frm_edit_forms', 'export-xml', 'export-xml-nonce' );
		if ( ! empty( $error ) ) {
			wp_die( esc_html( $error ) );
		}

		$ids    = FrmAppHelper::get_post_param( 'frm_export_forms', array(), 'sanitize_text_field' );
		$type   = FrmAppHelper::get_post_param( 'type', array(), 'sanitize_text_field' );
		$format = FrmAppHelper::get_post_param( 'format', 'xml', 'sanitize_title' );

		if ( ! headers_sent() && ! $type ) {
			wp_redirect( esc_url_raw( admin_url( 'admin.php?page=formidable-import' ) ) );
			die();
		}

		if ( 'xml' === $format ) {
			self::generate_xml( $type, compact( 'ids' ) );
		} elseif ( 'csv' === $format ) {
			self::generate_csv( compact( 'ids' ) );
		} else {
			do_action( 'frm_export_format_' . $format, compact( 'ids' ) );
		}

		wp_die();
	}

	public static function generate_xml( $type, $args = array() ) {
		global $wpdb;

		self::prepare_types_array( $type );

		$tables = array(
			'items'   => $wpdb->prefix . 'frm_items',
			'forms'   => $wpdb->prefix . 'frm_forms',
			'posts'   => $wpdb->posts,
			'styles'  => $wpdb->posts,
			'actions' => $wpdb->posts,
		);

		$defaults = array(
			'ids' => false,
		);
		$args     = wp_parse_args( $args, $defaults );

		// Make sure ids are numeric.
		if ( is_array( $args['ids'] ) && ! empty( $args['ids'] ) ) {
			$args['ids'] = array_filter( $args['ids'], 'is_numeric' );
		}

		$records = array();

		foreach ( $type as $tb_type ) {
			$where = array();
			$join  = '';
			$table = $tables[ $tb_type ];

			$select     = $table . '.id';
			$query_vars = array();

			switch ( $tb_type ) {
				case 'forms':
					//add forms
					if ( $args['ids'] ) {
						$where[] = array(
							'or'                       => 1,
							$table . '.id'             => $args['ids'],
							$table . '.parent_form_id' => $args['ids'],
						);
					} else {
						$where[ $table . '.status !' ] = 'draft';
					}
					break;
				case 'actions':
					$select             = $table . '.ID';
					$where['post_type'] = FrmFormActionsController::$action_post_type;
					if ( ! empty( $args['ids'] ) ) {
						$where['menu_order'] = $args['ids'];
					}
					break;
				case 'items':
					// $join = "INNER JOIN {$wpdb->prefix}frm_item_metas im ON ($table.id = im.item_id)";
					if ( $args['ids'] ) {
						$where[ $table . '.form_id' ] = $args['ids'];
					}
					break;
				case 'styles':
					// Loop through all exported forms and get their selected style IDs.
					$frm_style     = new FrmStyle();
					$default_style = $frm_style->get_default_style();
					$form_ids      = $args['ids'];
					$style_ids     = array();
					foreach ( $form_ids as $form_id ) {
						$form_data = FrmForm::getOne( $form_id );
						// For forms that have not been updated while running 2.0, check if custom_style is set.
						if ( isset( $form_data->options['custom_style'] ) ) {
							if ( 1 === absint( $form_data->options['custom_style'] ) ) {
								$style_ids[] = $default_style->ID;
							} else {
								$style_ids[] = $form_data->options['custom_style'];
							}
						}
						unset( $form_id, $form_data );
					}
					$select             = $table . '.ID';
					$where['post_type'] = 'frm_styles';

					// Only export selected styles.
					if ( ! empty( $style_ids ) ) {
						$where['ID'] = $style_ids;
					}
					break;
				default:
					$select               = $table . '.ID';
					$join                 = ' INNER JOIN ' . $wpdb->postmeta . ' pm ON (pm.post_id=' . $table . '.ID)';
					$where['pm.meta_key'] = 'frm_form_id';

					if ( empty( $args['ids'] ) ) {
						$where['pm.meta_value >'] = 1;
					} else {
						$where['pm.meta_value'] = $args['ids'];
					}
			}

			$records[ $tb_type ] = FrmDb::get_col( $table . $join, $where, $select );
			unset( $tb_type );
		}

		$filename = self::get_file_name( $args, $type, $records );

		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );

		echo '<?xml version="1.0" encoding="' . esc_attr( get_bloginfo( 'charset' ) ) . "\" ?>\n";
		include( FrmAppHelper::plugin_path() . '/classes/views/xml/xml.php' );
	}

	private static function prepare_types_array( &$type ) {
		$type = (array) $type;
		if ( ! in_array( 'forms', $type ) && ( in_array( 'items', $type ) || in_array( 'posts', $type ) ) ) {
			// make sure the form is included if there are entries
			$type[] = 'forms';
		}

		if ( in_array( 'forms', $type ) ) {
			// include actions with forms
			$type[] = 'actions';
		}
	}

	/**
	 * Use a generic file name if multiple items are exported.
	 * Use the nme of the form if only one form is exported.
	 *
	 * @since 3.06
	 *
	 * @return string
	 */
	private static function get_file_name( $args, $type, $records ) {
		$has_one_form = isset( $records['forms'] ) && ! empty( $records['forms'] ) && count( $args['ids'] ) === 1;
		if ( $has_one_form ) {
			// one form is being exported
			$selected_form_id = reset( $args['ids'] );
			$filename         = 'form-' . $selected_form_id . '.xml';

			foreach ( $records['forms'] as $form_id ) {
				$filename = 'form-' . $form_id . '.xml';
				if ( $selected_form_id === $form_id ) {
					$form     = FrmForm::getOne( $form_id );
					$filename = sanitize_title( $form->name ) . '-form.xml';
					break;
				}
			}
		} else {
			$sitename = sanitize_key( get_bloginfo( 'name' ) );

			if ( ! empty( $sitename ) ) {
				$sitename .= '.';
			}
			$filename = $sitename . 'formidable.' . date( 'Y-m-d' ) . '.xml';
		}

		return $filename;
	}

	public static function generate_csv( $atts ) {
		$form_ids = $atts['ids'];
		if ( empty( $form_ids ) ) {
			wp_die( esc_html__( 'Please select a form', 'formidable' ) );
		}
		self::csv( reset( $form_ids ) );
	}

	/**
	 * Export to CSV
	 *
	 * @since 2.0.19
	 */
	public static function csv( $form_id = false, $search = '', $fid = '' ) {
		FrmAppHelper::permission_check( 'frm_view_entries' );

		if ( ! $form_id ) {
			$form_id = FrmAppHelper::get_param( 'form', '', 'get', 'sanitize_text_field' );
			$search  = FrmAppHelper::get_param( ( isset( $_REQUEST['s'] ) ? 's' : 'search' ), '', 'get', 'sanitize_text_field' );
			$fid     = FrmAppHelper::get_param( 'fid', '', 'get', 'sanitize_text_field' );
		}

		set_time_limit( 0 ); //Remove time limit to execute this function
		$mem_limit = str_replace( 'M', '', ini_get( 'memory_limit' ) );
		if ( (int) $mem_limit < 256 ) {
			wp_raise_memory_limit();
		}

		global $wpdb;

		$form    = FrmForm::getOne( $form_id );
		$form_id = $form->id;

		$form_cols = self::get_fields_for_csv_export( $form_id, $form );

		$item_id = FrmAppHelper::get_param( 'item_id', 0, 'get', 'sanitize_text_field' );
		if ( ! empty( $item_id ) ) {
			$item_id = explode( ',', $item_id );
		}

		$query = array(
			'form_id' => $form_id,
		);

		if ( $item_id ) {
			$query['id'] = $item_id;
		}

		/**
		 * Allows the query to be changed for fetching the entry ids to include in the export
		 *
		 * $query is the array of options to be filtered. It includes form_id, and maybe id (array of entry ids),
		 * and the search query. This should return an array, but it can be handled as a string as well.
		 */
		$query = apply_filters( 'frm_csv_where', $query, compact( 'form_id', 'search', 'fid', 'item_id' ) );

		$entry_ids = FrmDb::get_col( $wpdb->prefix . 'frm_items it', $query );
		unset( $query );

		if ( empty( $entry_ids ) ) {
			esc_html_e( 'There are no entries for that form.', 'formidable' );
		} else {
			FrmCSVExportHelper::generate_csv( compact( 'form', 'entry_ids', 'form_cols' ) );
		}

		wp_die();
	}

	/**
	 * Get the fields that should be included in the CSV export
	 *
	 * @since 2.0.19
	 *
	 * @param int $form_id
	 * @param object $form
	 *
	 * @return array $csv_fields
	 */
	private static function get_fields_for_csv_export( $form_id, $form ) {
		$csv_fields       = FrmField::get_all_for_form( $form_id, '', 'include', 'include' );
		$no_export_fields = FrmField::no_save_fields();
		foreach ( $csv_fields as $k => $f ) {
			if ( in_array( $f->type, $no_export_fields ) ) {
				unset( $csv_fields[ $k ] );
			}
		}

		return $csv_fields;
	}

	public static function allow_mime( $mimes ) {
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
