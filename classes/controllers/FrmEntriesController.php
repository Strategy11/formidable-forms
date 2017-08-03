<?php

class FrmEntriesController {

    public static function menu() {
		FrmAppHelper::force_capability( 'frm_view_entries' );

        add_submenu_page('formidable', 'Formidable | ' . __( 'Entries', 'formidable' ), __( 'Entries', 'formidable' ), 'frm_view_entries', 'formidable-entries', 'FrmEntriesController::route' );

		if ( ! in_array( FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' ), array( 'edit', 'show' ) ) ) {
			$menu_name = FrmAppHelper::get_menu_name();
			add_filter( 'manage_' . sanitize_title( $menu_name ) . '_page_formidable-entries_columns', 'FrmEntriesController::manage_columns' );
			add_filter( 'get_user_option_manage' . sanitize_title( $menu_name ) . '_page_formidable-entriescolumnshidden', 'FrmEntriesController::hidden_columns' );
			add_filter( 'manage_' . sanitize_title( $menu_name ) . '_page_formidable-entries_sortable_columns', 'FrmEntriesController::sortable_columns' );
        }
    }

    /* Display in Back End */
    public static function route() {
		$action = FrmAppHelper::get_param( 'frm_action', '', 'get', 'sanitize_title' );

        switch ( $action ) {
            case 'show':
            case 'destroy':
            case 'destroy_all':
                return self::$action();

            default:
                do_action( 'frm_entry_action_route', $action );
                if ( apply_filters( 'frm_entry_stop_action_route', false, $action ) ) {
                    return;
                }

                return self::display_list();
        }
    }

	public static function contextual_help( $help, $screen_id, $screen ) {
        // Only add to certain screens. add_help_tab was introduced in WordPress 3.3
        if ( ! method_exists( $screen, 'add_help_tab' ) ) {
            return $help;
        }

		$action = FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );
		$page = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		if ( $page != 'formidable-entries' || ( ! empty( $action ) && $action != 'list' ) ) {
            return $help;
        }

		unset( $action, $page );

        $screen->add_help_tab( array(
            'id'      => 'formidable-entries-tab',
            'title'   => __( 'Overview', 'formidable' ),
			'content' => '<p>' . esc_html__( 'This screen provides access to all of your entries. You can customize the display of this screen to suit your workflow.', 'formidable' ) . '</p> <p>' . esc_html__( 'Hovering over a row in the entries list will display action links that allow you to manage your entry.', 'formidable' ) . '</p>',
        ));

        $screen->set_help_sidebar(
			'<p><strong>' . esc_html__( 'For more information:', 'formidable' ) . '</strong></p>' .
			'<p><a href="' . esc_url( FrmAppHelper::make_affiliate_url( 'https://formidableforms.com/knowledgebase/manage-entries-from-the-back-end/' ) ) . '" target="_blank">' . esc_html__( 'Documentation on Entries', 'formidable' ) . '</a></p>' .
			'<p><a href="' . esc_url( FrmAppHelper::make_affiliate_url( 'https://formidableforms.com/help-desk/' ) ) . '" target="_blank">' . esc_html__( 'Support', 'formidable' ) . '</a></p>'
    	);

        return $help;
    }

	public static function manage_columns( $columns ) {
        global $frm_vars;
		$form_id = FrmForm::get_current_form_id();

		$columns[ $form_id . '_id' ] = 'ID';
		$columns[ $form_id . '_item_key' ] = esc_html__( 'Entry Key', 'formidable' );

		if ( $form_id ) {
			self::get_columns_for_form( $form_id, $columns );
		} else {
			$columns[ $form_id . '_form_id' ] = __( 'Form', 'formidable' );
			$columns[ $form_id . '_name' ] = __( 'Entry Name', 'formidable' );
			$columns[ $form_id . '_user_id' ] = __( 'Created By', 'formidable' );
		}

		$columns[ $form_id . '_created_at' ] = __( 'Entry creation date', 'formidable' );
		$columns[ $form_id . '_updated_at' ] = __( 'Entry update date', 'formidable' );
		$columns[ $form_id . '_ip' ] = 'IP';

        $frm_vars['cols'] = $columns;

		$action = FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );
		if ( FrmAppHelper::is_admin_page( 'formidable-entries' ) && in_array( $action, array( '', 'list', 'destroy' ) ) ) {
			add_screen_option( 'per_page', array( 'label' => __( 'Entries', 'formidable' ), 'default' => 20, 'option' => 'formidable_page_formidable_entries_per_page' ) );
        }

        return $columns;
    }

	private static function get_columns_for_form( $form_id, &$columns ) {
		$form_cols = FrmField::get_all_for_form( $form_id, '', 'include' );

		foreach ( $form_cols as $form_col ) {
			if ( FrmField::is_no_save_field( $form_col->type ) ) {
				continue;
			}

			if ( $form_col->type == 'form' && isset( $form_col->field_options['form_select'] ) && ! empty( $form_col->field_options['form_select'] ) ) {
				$sub_form_cols = FrmField::get_all_for_form( $form_col->field_options['form_select'] );

				if ( $sub_form_cols ) {
					foreach ( $sub_form_cols as $k => $sub_form_col ) {
						if ( FrmField::is_no_save_field( $sub_form_col->type ) ) {
							unset( $sub_form_cols[ $k ] );
							continue;
						}
						$columns[ $form_id . '_' . $sub_form_col->field_key . '-_-' . $form_col->id ] = FrmAppHelper::truncate( $sub_form_col->name, 35 );
						unset($sub_form_col);
					}
				}
				unset($sub_form_cols);
			} else {
				$col_id = $form_col->field_key;
				if ( $form_col->form_id != $form_id ) {
					$col_id .= '-_-form' . $form_col->form_id;
				}
				
				$has_separate_value = ! FrmField::is_option_empty( $form_col, 'separate_value' );
				$is_post_status     = FrmField::is_option_true( $form_col, 'post_field' ) && $form_col->field_options['post_field'] == 'post_status';
				if ( $has_separate_value && ! $is_post_status ) {
					$columns[ $form_id . '_frmsep_' . $col_id ] = FrmAppHelper::truncate( $form_col->name, 35 );
				}
				$columns[ $form_id . '_' . $col_id ] = FrmAppHelper::truncate( $form_col->name, 35 );
			}
		}
	}

	public static function check_hidden_cols( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		$menu_name = FrmAppHelper::get_menu_name();
		$this_page_name = 'manage' . sanitize_title( $menu_name ) . '_page_formidable-entriescolumnshidden';
		if ( $meta_key != $this_page_name || $meta_value == $prev_value ) {
            return $check;
        }

		if ( empty( $prev_value ) ) {
			$prev_value = get_metadata( 'user', $object_id, $meta_key, true );
		}

        global $frm_vars;
        //add a check so we don't create a loop
        $frm_vars['prev_hidden_cols'] = ( isset($frm_vars['prev_hidden_cols']) && $frm_vars['prev_hidden_cols'] ) ? false : $prev_value;

        return $check;
    }

    //add hidden columns back from other forms
	public static function update_hidden_cols( $meta_id, $object_id, $meta_key, $meta_value ) {
		$menu_name = FrmAppHelper::get_menu_name();
		$sanitized = sanitize_title( $menu_name );
		$this_page_name = 'manage' . $sanitized . '_page_formidable-entriescolumnshidden';
		if ( $meta_key != $this_page_name ) {
            return;
        }

        global $frm_vars;
        if ( ! isset($frm_vars['prev_hidden_cols']) || ! $frm_vars['prev_hidden_cols'] ) {
            return; //don't continue if there's no previous value
        }

        foreach ( $meta_value as $mk => $mv ) {
            //remove blank values
            if ( empty( $mv ) ) {
                unset( $meta_value[ $mk ] );
            }
        }

        $cur_form_prefix = reset($meta_value);
        $cur_form_prefix = explode('_', $cur_form_prefix);
        $cur_form_prefix = $cur_form_prefix[0];
        $save = false;

        foreach ( (array) $frm_vars['prev_hidden_cols'] as $prev_hidden ) {
			if ( empty( $prev_hidden ) || in_array( $prev_hidden, $meta_value ) ) {
                //don't add blank cols or process included cols
                continue;
            }

			$form_prefix = explode( '_', $prev_hidden );
            $form_prefix = $form_prefix[0];
            if ( $form_prefix == $cur_form_prefix ) {
                //don't add back columns that are meant to be hidden
                continue;
            }

            $meta_value[] = $prev_hidden;
            $save = true;
            unset($form_prefix);
        }

		if ( $save ) {
            $user = wp_get_current_user();
			update_user_option( $user->ID, $this_page_name, $meta_value, true );
        }
    }

	public static function save_per_page( $save, $option, $value ) {
        if ( $option == 'formidable_page_formidable_entries_per_page' ) {
            $save = (int) $value;
        }
        return $save;
    }

	public static function sortable_columns() {
		$form_id = FrmForm::get_current_form_id();
		$fields = FrmField::get_all_for_form( $form_id );

		$columns = array(
			$form_id . '_id'         => 'id',
			$form_id . '_created_at' => 'created_at',
			$form_id . '_updated_at' => 'updated_at',
			$form_id . '_ip'         => 'ip',
			$form_id . '_item_key'   => 'item_key',
			$form_id . '_is_draft'   => 'is_draft',
		);

		foreach ( $fields as $field ) {
			if ( $field->type != 'checkbox' && ( ! isset( $field->field_options['post_field'] ) || $field->field_options['post_field'] == '' ) ) {
				// Can't sort on checkboxes because they are stored serialized, or post fields
				$columns[ $form_id . '_' . $field->field_key ] = 'meta_' . $field->id;
			}
		}

		return $columns;
	}

	public static function hidden_columns( $result ) {
        global $frm_vars;

		$form_id = FrmForm::get_current_form_id();

        $return = false;
        foreach ( (array) $result as $r ) {
            if ( ! empty( $r ) ) {
                $form_prefix = explode( '_', $r );
                $form_prefix = $form_prefix[0];

                if ( (int) $form_prefix == (int) $form_id ) {
                    $return = true;
                    break;
                }

                unset($form_prefix);
            }
        }

        if ( $return ) {
			return $result;
		}

        $i = isset($frm_vars['cols']) ? count($frm_vars['cols']) : 0;
        $max_columns = 8;
        if ( $i <= $max_columns ) {
			return $result;
		}

        global $frm_vars;
        if ( isset($frm_vars['current_form']) && $frm_vars['current_form'] ) {
            $frm_vars['current_form']->options = maybe_unserialize($frm_vars['current_form']->options);
        }

		$has_custom_hidden_columns = ( isset( $frm_vars['current_form'] ) && $frm_vars['current_form'] && isset( $frm_vars['current_form']->options['hidden_cols'] ) && ! empty( $frm_vars['current_form']->options['hidden_cols'] ) );
		if ( $has_custom_hidden_columns ) {
            $result = $frm_vars['current_form']->options['hidden_cols'];
        } else {
            $cols = $frm_vars['cols'];
            $cols = array_reverse($cols, true);

			if ( $form_id ) {
				$result[] = $form_id . '_id';
				$i--;
			}

			$result[] = $form_id . '_item_key';
            $i--;

			foreach ( $cols as $col_key => $col ) {
                if ( $i > $max_columns ) {
					$result[] = $col_key;
				}
                //remove some columns by default
                $i--;
                unset($col_key, $col);
            }
        }

        return $result;
    }

	public static function display_list( $message = '', $errors = array() ) {
        global $wpdb, $frm_vars;

		$form = FrmForm::maybe_get_current_form();
		$params = FrmForm::get_admin_params( $form );

        if ( $form ) {
            $params['form'] = $form->id;
            $frm_vars['current_form'] = $form;

			self::get_delete_form_time( $form, $errors );
		}

        $table_class = apply_filters( 'frm_entries_list_class', 'FrmEntriesListHelper' );

        $wp_list_table = new $table_class( array( 'params' => $params ) );

        $pagenum = $wp_list_table->get_pagenum();

        $wp_list_table->prepare_items();

        $total_pages = $wp_list_table->get_pagination_arg( 'total_pages' );
        if ( $pagenum > $total_pages && $total_pages > 0 ) {
			$url = add_query_arg( 'paged', $total_pages );
            if ( headers_sent() ) {
                echo FrmAppHelper::js_redirect($url);
            } else {
                wp_redirect( esc_url_raw( $url ) );
            }
            die();
        }

        if ( empty($message) && isset($_GET['import-message']) ) {
            $message = __( 'Your import is complete', 'formidable' );
        }

		require( FrmAppHelper::plugin_path() . '/classes/views/frm-entries/list.php' );
    }

	private static function get_delete_form_time( $form, &$errors ) {
		if ( 'trash' == $form->status ) {
			$delete_timestamp = time() - ( DAY_IN_SECONDS * EMPTY_TRASH_DAYS );
			$time_to_delete = FrmAppHelper::human_time_diff( $delete_timestamp, ( isset( $form->options['trash_time'] ) ? ( $form->options['trash_time'] ) : time() ) );
			$errors['trash'] = sprintf( __( 'This form is in the trash and is scheduled to be deleted permanently in %s along with any entries.', 'formidable' ), $time_to_delete );
		}
	}

    /* Back End CRUD */
	public static function show( $id = 0 ) {
        FrmAppHelper::permission_check('frm_view_entries');

        if ( ! $id ) {
			$id = FrmAppHelper::get_param( 'id', 0, 'get', 'absint' );

            if ( ! $id ) {
				$id = FrmAppHelper::get_param( 'item_id', 0, 'get', 'absint' );
            }
        }

        $entry = FrmEntry::getOne($id, true);
		if ( ! $entry ) {
			echo '<div id="form_show_entry_page" class="wrap">' .
				__( 'You are trying to view an entry that does not exist.', 'formidable' ) .
				'</div>';
			return;
		}

        $data = maybe_unserialize($entry->description);
		if ( ! is_array( $data ) || ! isset( $data['referrer'] ) ) {
			$data = array( 'referrer' => $data );
		}

		$fields = FrmField::get_all_for_form( $entry->form_id, '', 'include' );
        $to_emails = array();

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-entries/show.php' );
    }

    public static function destroy() {
        FrmAppHelper::permission_check('frm_delete_entries');

		$params = FrmForm::get_admin_params();

        if ( isset($params['keep_post']) && $params['keep_post'] ) {
            //unlink entry from post
            global $wpdb;
			$wpdb->update( $wpdb->prefix . 'frm_items', array( 'post_id' => '' ), array( 'id' => $params['id'] ) );
        }

        $message = '';
        if ( FrmEntry::destroy( $params['id'] ) ) {
            $message = __( 'Entry was Successfully Destroyed', 'formidable' );
        }

        self::display_list( $message );
    }

    public static function destroy_all() {
        if ( ! current_user_can( 'frm_delete_entries' ) ) {
            $frm_settings = FrmAppHelper::get_settings();
            wp_die( $frm_settings->admin_permission );
        }

        global $wpdb;
		$params = FrmForm::get_admin_params();
        $message = '';
        $errors = array();
        $form_id = (int) $params['form'];

        if ( $form_id ) {
            $entry_ids = FrmDb::get_col( 'frm_items', array( 'form_id' => $form_id ) );
			$action = FrmFormAction::get_action_for_form( $form_id, 'wppost', 1 );

            if ( $action ) {
                // this action takes a while, so only trigger it if there are posts to delete
                foreach ( $entry_ids as $entry_id ) {
                    do_action( 'frm_before_destroy_entry', $entry_id );
                    unset( $entry_id );
                }
            }

            $wpdb->query( $wpdb->prepare( "DELETE em.* FROM {$wpdb->prefix}frm_item_metas as em INNER JOIN {$wpdb->prefix}frm_items as e on (em.item_id=e.id) and form_id=%d", $form_id ) );
            $results = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}frm_items WHERE form_id=%d", $form_id ) );
            if ( $results ) {
				FrmEntry::clear_cache();
                $message = __( 'Entries were Successfully Destroyed', 'formidable' );
            }
        } else {
            $errors = __( 'No entries were specified', 'formidable' );
        }

        self::display_list( $message, $errors );
    }

    public static function show_form( $id = '', $key = '', $title = false, $description = false ) {
        _deprecated_function( __FUNCTION__, '1.07.05', 'FrmFormsController::show_form()' );
        return FrmFormsController::show_form( $id, $key, $title, $description );
    }

    public static function get_form( $filename, $form, $title, $description ) {
        _deprecated_function( __FUNCTION__, '1.07.05', 'FrmFormsController::get_form()' );
        return FrmFormsController::get_form( $form, $title, $description );
    }

    public static function process_entry( $errors = '', $ajax = false ) {
		$form_id = FrmAppHelper::get_post_param( 'form_id', '', 'absint' );
		if ( FrmAppHelper::is_admin() || empty( $_POST ) || empty( $form_id ) || ! isset( $_POST['item_key'] ) ) {
            return;
        }

        global $frm_vars;

		$form = FrmForm::getOne( $form_id );
        if ( ! $form ) {
            return;
        }

		$params = FrmForm::get_params( $form );

        if ( ! isset( $frm_vars['form_params'] ) ) {
            $frm_vars['form_params'] = array();
        }
		$frm_vars['form_params'][ $form->id ] = $params;

		if ( isset( $frm_vars['created_entries'][ $form_id ] ) ) {
            return;
        }

        if ( $errors == '' && ! $ajax ) {
			$errors = FrmEntryValidate::validate( $_POST );
        }

		/**
		 * Use this filter to add trigger actions and add errors after
		 * all other errors have been processed
		 * @since 2.0.6
		 */
		$errors = apply_filters( 'frm_entries_before_create', $errors, $form );

		$frm_vars['created_entries'][ $form_id ] = array( 'errors' => $errors );

        if ( empty( $errors ) ) {
			$_POST['frm_skip_cookie'] = 1;
            if ( $params['action'] == 'create' ) {
				if ( apply_filters( 'frm_continue_to_create', true, $form_id ) && ! isset( $frm_vars['created_entries'][ $form_id ]['entry_id'] ) ) {
					$frm_vars['created_entries'][ $form_id ]['entry_id'] = FrmEntry::create( $_POST );
                }
            }

            do_action( 'frm_process_entry', $params, $errors, $form, array( 'ajax' => $ajax ) );
			unset( $_POST['frm_skip_cookie'] );
        }
    }

    public static function delete_entry_before_redirect( $url, $form, $atts ) {
        self::_delete_entry( $atts['id'], $form );
        return $url;
    }

    //Delete entry if not redirected
    public static function delete_entry_after_save( $atts ) {
        self::_delete_entry( $atts['entry_id'], $atts['form'] );
    }

    private static function _delete_entry( $entry_id, $form ) {
        if ( ! $form ) {
            return;
        }

        $form->options = maybe_unserialize( $form->options );
        if ( isset( $form->options['no_save'] ) && $form->options['no_save'] ) {
            FrmEntry::destroy( $entry_id );
        }
    }

	/**
	 * @param $atts
	 *
	 * @return array|string
	 */
	public static function show_entry_shortcode( $atts ) {
		$defaults = array(
			'id'             => false,
			'entry'          => false,
			'fields'         => false,
			'plain_text'     => false,
			'user_info'      => false,
			'include_blank'  => false,
			'default_email'  => false,
			'form_id'        => false,
			'format'         => 'text',
			'direction'      => 'ltr',
			'font_size'      => '',
			'text_color'     => '',
			'border_width'   => '',
			'border_color'   => '',
			'bg_color'       => '',
			'alt_bg_color'   => '',
			'clickable'      => false,
			'exclude_fields' => '',
			'include_fields' => '',
			'include_extras' => '',
			'inline_style'   => 1,
		);

		$atts = shortcode_atts( $defaults, $atts );

		if ( $atts['default_email'] ) {

			$entry_shortcode_formatter = FrmEntryFactory::entry_shortcode_formatter_instance( $atts['form_id'], $atts['format'] );
			$formatted_entry = $entry_shortcode_formatter->content();

		} else {

			$entry_formatter = FrmEntryFactory::entry_formatter_instance( $atts );
			$formatted_entry = $entry_formatter->get_formatted_entry_values();

		}

		return $formatted_entry;
	}

	public static function get_params( $form = null ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmForm::get_params' );
		return FrmForm::get_params( $form );
	}

	public static function entry_sidebar( $entry ) {
        $data = maybe_unserialize($entry->description);
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
		if ( isset( $data['browser'] ) ) {
			$browser = FrmEntriesHelper::get_browser( $data['browser'] );
		}

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-entries/sidebar-shared.php' );
    }

	/***********************************************************************
	 * Deprecated Functions
	 ************************************************************************/

	/**
	 * @deprecated 2.02.14
	 *
	 * @return mixed
	 */
	public static function filter_email_value( $value ) {
		_deprecated_function( __FUNCTION__, '2.02.14', 'FrmProEntriesController::filter_value_in_single_entry_table' );
		return $value;
	}

	/**
	 * @deprecated 2.02.14
	 *
	 * @return mixed
	 */
	public static function filter_display_value( $value ) {
		_deprecated_function( __FUNCTION__, '2.02.14', 'FrmProEntriesController::filter_display_value' );
		return $value;
	}

	/**
	 * @deprecated 2.03.04
	 *
	 * @return mixed
	 */
	public static function filter_shortcode_value( $value ) {
		_deprecated_function( __FUNCTION__, '2.03.04', 'custom code' );
		return $value;
	}
}
