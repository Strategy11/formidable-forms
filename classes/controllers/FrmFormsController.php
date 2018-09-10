<?php

class FrmFormsController {

    public static function menu() {
		$menu_label = __( 'Forms', 'formidable' );
		if ( ! FrmAppHelper::pro_is_installed() ) {
			$menu_label .= ' (Lite)';
		}
		add_submenu_page( 'formidable', 'Formidable | ' . $menu_label, $menu_label, 'frm_view_forms', 'formidable', 'FrmFormsController::route' );

		self::maybe_load_listing_hooks();
    }

	public static function maybe_load_listing_hooks() {
		$action = FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );
		if ( ! empty( $action ) && ! in_array( $action, array( 'list', 'trash', 'untrash', 'destroy' ) ) ) {
			return;
		}

		add_filter( 'get_user_option_managetoplevel_page_formidablecolumnshidden', 'FrmFormsController::hidden_columns' );

		add_filter( 'manage_toplevel_page_formidable_columns', 'FrmFormsController::get_columns', 0 );
		add_filter( 'manage_toplevel_page_formidable_sortable_columns', 'FrmFormsController::get_sortable_columns' );
	}

    public static function head() {
		wp_enqueue_script( 'formidable-editinplace' );

        if ( wp_is_mobile() ) {
    		wp_enqueue_script( 'jquery-touch-punch' );
    	}
    }

	public static function register_widgets() {
		require_once( FrmAppHelper::plugin_path() . '/classes/widgets/FrmShowForm.php' );
		register_widget( 'FrmShowForm' );
	}

	/**
	 * By default, Divi processes form shortcodes on the edit post page.
	 * Now that won't do.
	 *
	 * @since 3.01
	 */
	public static function prevent_divi_conflict( $shortcodes ) {
		$shortcodes[] = 'formidable';
		return $shortcodes;
	}

	public static function list_form() {
		FrmAppHelper::permission_check( 'frm_view_forms' );

		$params = FrmForm::list_page_params();
		$errors = self::process_bulk_form_actions( array() );
		$errors = apply_filters( 'frm_admin_list_form_action', $errors );

		return self::display_forms_list( $params, '', $errors );
	}

	public static function new_form( $values = array() ) {
		FrmAppHelper::permission_check( 'frm_edit_forms' );

        global $frm_vars;

		$action = isset( $_REQUEST['frm_action'] ) ? 'frm_action' : 'action';
		$action = empty( $values ) ? FrmAppHelper::get_param( $action, '', 'get', 'sanitize_title' ) : $values[ $action ];

		if ( $action == 'create' ) {
			self::create( $values );
			return;
		} else if ( $action == 'new' ) {
			$frm_field_selection = FrmField::field_selection();
			$values = FrmFormsHelper::setup_new_vars( $values );
            $id = FrmForm::create( $values );
			$form = FrmForm::getOne( $id );

			self::create_default_email_action( $form );

			$all_templates = FrmForm::getAll( array( 'is_template' => 1 ), 'name' );

            $values['id'] = $id;
			require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/new.php' );
        }
    }

	/**
	 * Create the default email action
	 *
	 * @since 2.02.11
	 *
	 * @param object $form
	 */
    private static function create_default_email_action( $form ) {
    	$create_email = apply_filters( 'frm_create_default_email_action', true, $form );

	    if ( $create_email ) {
		    $action_control = FrmFormActionsController::get_form_actions( 'email' );
		    $action_control->create( $form->id );
	    }
    }

	public static function create( $values = array() ) {
		FrmAppHelper::permission_check( 'frm_edit_forms' );

        global $frm_vars;
        if ( empty( $values ) ) {
            $values = $_POST;
        }

        //Set radio button and checkbox meta equal to "other" value
        if ( FrmAppHelper::pro_is_installed() ) {
            $values = FrmProEntry::mod_other_vals( $values, 'back' );
        }

		$id = isset( $values['id'] ) ? absint( $values['id'] ) : FrmAppHelper::get_param( 'id', '', 'get', 'absint' );

        if ( ! current_user_can( 'frm_edit_forms' ) || ( $_POST && ( ! isset( $values['frm_save_form'] ) || ! wp_verify_nonce( $values['frm_save_form'], 'frm_save_form_nonce' ) ) ) ) {
            $frm_settings = FrmAppHelper::get_settings();
            $errors = array( 'form' => $frm_settings->admin_permission );
        } else {
			$errors = FrmForm::validate( $values );
        }

		if ( count( $errors ) > 0 ) {
            $hide_preview = true;
			$frm_field_selection = FrmField::field_selection();
            $form = FrmForm::getOne( $id );
			$fields = FrmField::get_all_for_form( $id );

			$values = FrmAppHelper::setup_edit_vars( $form, 'forms', '', true );
			$values['fields'] = $fields;
			$all_templates = FrmForm::getAll( array( 'is_template' => 1 ), 'name' );

			require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/new.php' );
        } else {
            FrmForm::update( $id, $values, true );
			$url = admin_url( 'admin.php?page=formidable&frm_action=settings&id=' . $id );
			die( FrmAppHelper::js_redirect( $url ) ); // WPCS: XSS ok.
        }
    }

    public static function edit( $values = false ) {
		FrmAppHelper::permission_check( 'frm_edit_forms' );

		$id = isset( $values['id'] ) ? absint( $values['id'] ) : FrmAppHelper::get_param( 'id', '', 'get', 'absint' );
		return self::get_edit_vars( $id );
    }

    public static function settings( $id = false, $message = '' ) {
		FrmAppHelper::permission_check( 'frm_edit_forms' );

		if ( ! $id || ! is_numeric( $id ) ) {
			$id = FrmAppHelper::get_param( 'id', '', 'get', 'absint' );
        }
		return self::get_settings_vars( $id, array(), $message );
    }

    public static function update_settings() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );

		$id = FrmAppHelper::get_param( 'id', '', 'get', 'absint' );

		$errors = FrmForm::validate( $_POST );
		if ( count( $errors ) > 0 ) {
			return self::get_settings_vars( $id, $errors );
		}

		do_action( 'frm_before_update_form_settings', $id );

		FrmForm::update( $id, $_POST );

        $message = __( 'Settings Successfully Updated', 'formidable' );
		return self::get_settings_vars( $id, array(), $message );
    }

	public static function update( $values = array() ) {
		if ( empty( $values ) ) {
            $values = $_POST;
        }

        //Set radio button and checkbox meta equal to "other" value
        if ( FrmAppHelper::pro_is_installed() ) {
            $values = FrmProEntry::mod_other_vals( $values, 'back' );
        }

        $errors = FrmForm::validate( $values );
        $permission_error = FrmAppHelper::permission_nonce_error( 'frm_edit_forms', 'frm_save_form', 'frm_save_form_nonce' );
        if ( $permission_error !== false ) {
            $errors['form'] = $permission_error;
        }

		$id = isset( $values['id'] ) ? absint( $values['id'] ) : FrmAppHelper::get_param( 'id', '', 'get', 'absint' );

		if ( count( $errors ) > 0 ) {
            return self::get_edit_vars( $id, $errors );
		} else {
            FrmForm::update( $id, $values );
            $message = __( 'Form was Successfully Updated', 'formidable' );
            if ( defined( 'DOING_AJAX' ) ) {
				wp_die( esc_html( $message ) );
            }
			return self::get_edit_vars( $id, array(), $message );
        }
    }

	/**
	 * Redirect to the url for creating from a template
	 * Also delete the current form
	 * @since 2.0
	 */
	public static function _create_from_template() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$current_form = FrmAppHelper::get_param( 'this_form', '', 'get', 'absint' );
		$template_id = FrmAppHelper::get_param( 'id', '', 'get', 'absint' );

		if ( $current_form ) {
			FrmForm::destroy( $current_form );
		}

		echo esc_url_raw( admin_url( 'admin.php?page=formidable&frm_action=duplicate&id=' . absint( $template_id ) ) );
		wp_die();
	}

    public static function duplicate() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );

		$params = FrmForm::list_page_params();
        $form = FrmForm::duplicate( $params['id'], $params['template'], true );
        $message = $params['template'] ? __( 'Form template was Successfully Created', 'formidable' ) : __( 'Form was Successfully Copied', 'formidable' );
        if ( $form ) {
			return self::get_edit_vars( $form, array(), $message, true );
        } else {
			return self::display_forms_list( $params, __( 'There was a problem creating the new template.', 'formidable' ) );
        }
    }

    public static function page_preview() {
		$params = FrmForm::list_page_params();
        if ( ! $params['form'] ) {
            return;
        }

        $form = FrmForm::getOne( $params['form'] );
		if ( $form ) {
			return self::show_form( $form->id, '', true, true );
		}
    }

	/**
	 * @since 3.0
	 */
	public static function show_page_preview() {
		echo self::page_preview(); // WPCS: XSS ok.
	}

    public static function preview() {
        do_action( 'frm_wp' );

        global $frm_vars;
        $frm_vars['preview'] = true;

		self::load_wp();

		$include_theme = FrmAppHelper::get_param( 'theme', '', 'get', 'absint' );
		if ( $include_theme ) {
			self::set_preview_query();
			self::load_theme_preview();
		} else {
			self::load_direct_preview();
		}

		wp_die();
	}

	/**
	 * @since 3.0
	 */
	private static function load_wp() {
		if ( ! defined( 'ABSPATH' ) && ! defined( 'XMLRPC_REQUEST' ) ) {
			global $wp;
			$root = dirname( dirname( dirname( dirname( __FILE__ ) ) ) );
			include_once( $root . '/wp-config.php' );
			$wp->init();
			$wp->register_globals();
		}
	}

	private static function set_preview_query() {
		$random_page = get_posts(
			array(
				'numberposts' => 1,
				'orderby'     => 'date',
				'order'       => 'ASC',
				'post_type'   => 'page',
			)
		);

		if ( ! empty( $random_page ) ) {
			$random_page = reset( $random_page );
			query_posts(
				array(
					'post_type' => 'page',
					'page_id'   => $random_page->ID,
				)
			);
		}
	}

	/**
	 * @since 3.0
	 */
	private static function load_theme_preview() {
		add_filter( 'wp_title', 'FrmFormsController::preview_title', 9999 );
		add_filter( 'the_title', 'FrmFormsController::preview_page_title', 9999 );
		add_filter( 'the_content', 'FrmFormsController::preview_content', 9999 );
		add_action( 'loop_no_results', 'FrmFormsController::show_page_preview' );
		add_filter( 'is_active_sidebar', '__return_false' );
		get_template_part( 'page' );
	}


	/**
	 * Set the page title for the theme preview page
	 *
	 * @since 3.0
	 */
	public static function preview_page_title( $title ) {
		if ( in_the_loop() ) {
			$title = self::preview_title( $title );
		}
		return $title;
	}

	/**
	 * Set the page title for the theme preview page
	 *
	 * @since 3.0
	 */
	public static function preview_title( $title ) {
		return __( 'Form Preview', 'formidable' );
	}

	/**
	 * Set the page content for the theme preview page
	 *
	 * @since 3.0
	 */
	public static function preview_content( $content ) {
		if ( in_the_loop() ) {
			$content = FrmFormsController::show_page_preview();
		}
		return $content;
	}

	/**
	 * @since 3.0
	 */
	private static function load_direct_preview() {
		header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );

		$key = FrmAppHelper::simple_get( 'form', 'sanitize_title' );
		if ( $key == '' ) {
			$key = FrmAppHelper::get_post_param( 'form', '', 'sanitize_title' );
		}

		$form = FrmForm::getAll( array( 'form_key' => $key ), '', 1 );
		if ( empty( $form ) ) {
			$form = FrmForm::getAll( array(), '', 1 );
		}

		require( FrmAppHelper::plugin_path() . '/classes/views/frm-entries/direct.php' );
	}

    public static function untrash() {
		self::change_form_status( 'untrash' );
    }

	public static function bulk_untrash( $ids ) {
		FrmAppHelper::permission_check( 'frm_edit_forms' );

        $count = FrmForm::set_status( $ids, 'published' );

		$message = sprintf( _n( '%1$s form restored from the Trash.', '%1$s forms restored from the Trash.', $count, 'formidable' ), 1 );
        return $message;
    }

    public static function trash() {
		self::change_form_status( 'trash' );
    }

	/**
	 * @param string $status
	 *
	 * @return int The number of forms changed
	 */
	public static function change_form_status( $status ) {
		$available_status = array(
			'untrash' => array(
				'permission' => 'frm_edit_forms',
				'new_status' => 'published',
			),
			'trash'   => array(
				'permission' => 'frm_delete_forms',
				'new_status' => 'trash',
			),
		);

		if ( ! isset( $available_status[ $status ] ) ) {
			return;
		}

		FrmAppHelper::permission_check( $available_status[ $status ]['permission'] );

		$params = FrmForm::list_page_params();

		//check nonce url
		check_admin_referer( $status . '_form_' . $params['id'] );

		$count = 0;
		if ( FrmForm::set_status( $params['id'], $available_status[ $status ]['new_status'] ) ) {
			$count++;
		}

		$form_type = FrmAppHelper::get_simple_request(
			array(
				'param' => 'form_type',
				'type' => 'request',
			)
		);

		$available_status['untrash']['message'] = sprintf( _n( '%1$s form restored from the Trash.', '%1$s forms restored from the Trash.', $count, 'formidable' ), $count );
		$available_status['trash']['message'] = sprintf( _n( '%1$s form moved to the Trash. %2$sUndo%3$s', '%1$s forms moved to the Trash. %2$sUndo%3$s', $count, 'formidable' ), $count, '<a href="' . esc_url( wp_nonce_url( '?page=formidable&frm_action=untrash&form_type=' . $form_type . '&id=' . $params['id'], 'untrash_form_' . $params['id'] ) ) . '">', '</a>' );

		$message = $available_status[ $status ]['message'];

		self::display_forms_list( $params, $message );
	}

	public static function bulk_trash( $ids ) {
		FrmAppHelper::permission_check( 'frm_delete_forms' );

        $count = 0;
        foreach ( $ids as $id ) {
            if ( FrmForm::trash( $id ) ) {
                $count++;
            }
        }

		$current_page = FrmAppHelper::get_simple_request(
			array(
				'param' => 'form_type',
				'type' => 'request',
			)
		);
		$message = sprintf(
			_n( '%1$s form moved to the Trash. %2$sUndo%3$s', '%1$s forms moved to the Trash. %2$sUndo%3$s', $count, 'formidable' ),
			$count,
			'<a href="' . esc_url( wp_nonce_url( '?page=formidable&frm_action=list&action=bulk_untrash&form_type=' . $current_page . '&item-action=' . implode( ',', $ids ), 'bulk-toplevel_page_formidable' ) ) . '">',
			'</a>'
		);

        return $message;
    }

    public static function destroy() {
		FrmAppHelper::permission_check( 'frm_delete_forms' );

		$params = FrmForm::list_page_params();

        //check nonce url
		check_admin_referer( 'destroy_form_' . $params['id'] );

        $count = 0;
        if ( FrmForm::destroy( $params['id'] ) ) {
            $count++;
        }

		$message = sprintf( _n( '%1$s form permanently deleted.', '%1$s forms permanently deleted.', $count, 'formidable' ), $count );

		self::display_forms_list( $params, $message );
    }

	public static function bulk_destroy( $ids ) {
		FrmAppHelper::permission_check( 'frm_delete_forms' );

        $count = 0;
        foreach ( $ids as $id ) {
            $d = FrmForm::destroy( $id );
            if ( $d ) {
                $count++;
            }
        }

		$message = sprintf( _n( '%1$s form permanently deleted.', '%1$s forms permanently deleted.', $count, 'formidable' ), $count );

        return $message;
    }

    private static function delete_all() {
        //check nonce url
		$permission_error = FrmAppHelper::permission_nonce_error( 'frm_delete_forms', '_wpnonce', 'bulk-toplevel_page_formidable' );
        if ( $permission_error !== false ) {
			self::display_forms_list( array(), '', array( $permission_error ) );
            return;
        }

		$count = FrmForm::scheduled_delete( time() );
		$message = sprintf( _n( '%1$s form permanently deleted.', '%1$s forms permanently deleted.', $count, 'formidable' ), $count );

		self::display_forms_list( array(), $message );
    }

	/**
	* Inserts Formidable button
	* Hook exists since 2.5.0
	*
	* @since 2.0.15
	*/
	public static function insert_form_button() {
		if ( current_user_can( 'frm_view_forms' ) ) {
			$menu_name = FrmAppHelper::get_menu_name();
			$content = '<a href="#TB_inline?width=50&height=50&inlineId=frm_insert_form" class="thickbox button add_media frm_insert_form" title="' . esc_attr__( 'Add forms and content', 'formidable' ) . '">
				<span class="frm-buttons-icon wp-media-buttons-icon"></span> ' .
				$menu_name . '</a>';
			echo wp_kses_post( $content );
		}
	}

    public static function insert_form_popup() {
		$page = basename( FrmAppHelper::get_server_value( 'PHP_SELF' ) );
		if ( ! in_array( $page, array( 'post.php', 'page.php', 'page-new.php', 'post-new.php' ) ) ) {
            return;
        }

        FrmAppHelper::load_admin_wide_js();

        $shortcodes = array(
			'formidable' => array(
				'name'  => __( 'Form', 'formidable' ),
				'label' => __( 'Insert a Form', 'formidable' ),
			),
        );

		$shortcodes = apply_filters( 'frm_popup_shortcodes', $shortcodes );

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/insert_form_popup.php' );
    }

    public static function get_shortcode_opts() {
		FrmAppHelper::permission_check( 'frm_view_forms' );
        check_ajax_referer( 'frm_ajax', 'nonce' );

		$shortcode = FrmAppHelper::get_post_param( 'shortcode', '', 'sanitize_text_field' );
		if ( empty( $shortcode ) ) {
            wp_die();
        }

		echo '<div id="sc-opts-' . esc_attr( $shortcode ) . '" class="frm_shortcode_option">';
		echo '<input type="radio" name="frmsc" value="' . esc_attr( $shortcode ) . '" id="sc-' . esc_attr( $shortcode ) . '" class="frm_hidden" />';

        $form_id = '';
        $opts = array();
		switch ( $shortcode ) {
            case 'formidable':
                $opts = array(
					'form_id'       => 'id',
                    //'key' => ',
					'title'         => array(
						'val'   => 1,
						'label' => __( 'Display form title', 'formidable' ),
					),
					'description'   => array(
						'val'   => 1,
						'label' => __( 'Display form description', 'formidable' ),
					),
					'minimize'      => array(
						'val'   => 1,
						'label' => __( 'Minimize form HTML', 'formidable' ),
					),
                );
        }
		$opts = apply_filters( 'frm_sc_popup_opts', $opts, $shortcode );

		if ( isset( $opts['form_id'] ) && is_string( $opts['form_id'] ) ) {
			// allow other shortcodes to use the required form id option
			$form_id = $opts['form_id'];
			unset( $opts['form_id'] );
		}

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/shortcode_opts.php' );

        echo '</div>';

        wp_die();
    }

	public static function display_forms_list( $params = array(), $message = '', $errors = array() ) {
        FrmAppHelper::permission_check( 'frm_view_forms' );

        global $wpdb, $frm_vars;

		if ( empty( $params ) ) {
			$params = FrmForm::list_page_params();
        }

        $wp_list_table = new FrmFormsListHelper( compact( 'params' ) );

        $pagenum = $wp_list_table->get_pagenum();

        $wp_list_table->prepare_items();

        $total_pages = $wp_list_table->get_pagination_arg( 'total_pages' );
        if ( $pagenum > $total_pages && $total_pages > 0 ) {
			wp_redirect( esc_url_raw( add_query_arg( 'paged', $total_pages ) ) );
            die();
        }

		require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/list.php' );
    }

	public static function get_columns( $columns ) {
	    $columns['cb'] = '<input type="checkbox" />';
	    $columns['id'] = 'ID';

		$type = FrmAppHelper::get_simple_request(
			array(
				'param'   => 'form_type',
				'type'    => 'request',
				'default' => 'published',
			)
		);

        if ( 'template' == $type ) {
            $columns['name']        = __( 'Template Name', 'formidable' );
            $columns['type']        = __( 'Type', 'formidable' );
            $columns['form_key']    = __( 'Key', 'formidable' );
        } else {
            $columns['name']        = __( 'Form Title', 'formidable' );
            $columns['entries']     = __( 'Entries', 'formidable' );
            $columns['form_key']    = __( 'Key', 'formidable' );
            $columns['shortcode']   = __( 'Shortcodes', 'formidable' );
        }

        $columns['created_at'] = __( 'Date', 'formidable' );

		add_screen_option(
			'per_page',
			array(
				'label'   => __( 'Forms', 'formidable' ),
				'default' => 20,
				'option'  => 'formidable_page_formidable_per_page',
			)
		);

        return $columns;
	}

	public static function get_sortable_columns() {
		return array(
			'id'            => 'id',
			'name'          => 'name',
			'description'   => 'description',
			'form_key'      => 'form_key',
			'created_at'    => 'created_at',
		);
	}

	public static function hidden_columns( $hidden_columns ) {
		$type = FrmAppHelper::get_simple_request(
			array(
				'param' => 'form_type',
				'type'  => 'request',
			)
		);

		if ( $type === 'template' ) {
			$hidden_columns[] = 'id';
			$hidden_columns[] = 'form_key';
		}

		return $hidden_columns;
	}

	public static function save_per_page( $save, $option, $value ) {
        if ( $option == 'formidable_page_formidable_per_page' ) {
            $save = (int) $value;
        }
        return $save;
    }

	private static function get_edit_vars( $id, $errors = array(), $message = '', $create_link = false ) {
        global $frm_vars;

        $form = FrmForm::getOne( $id );
        if ( ! $form ) {
            wp_die( esc_html__( 'You are trying to edit a form that does not exist.', 'formidable' ) );
        }

        if ( $form->parent_form_id ) {
			wp_die( sprintf( esc_html__( 'You are trying to edit a child form. Please edit from %1$shere%2$s', 'formidable' ), '<a href="' . esc_url( admin_url( 'admin.php?page=formidable&frm_action=edit&id=' . $form->parent_form_id ) ) . '">', '</a>' ) );
        }

		$frm_field_selection = FrmField::field_selection();
		$fields = FrmField::get_all_for_form( $form->id );

        // Automatically add end section fields if they don't exist (2.0 migration)
        $reset_fields = false;
        FrmFormsHelper::auto_add_end_section_fields( $form, $fields, $reset_fields );

        if ( $reset_fields ) {
            $fields = FrmField::get_all_for_form( $form->id, '', 'exclude' );
        }

		unset( $end_section_values, $last_order, $open, $reset_fields );

		$args = array( 'parent_form_id' => $form->id );
		$values = FrmAppHelper::setup_edit_vars( $form, 'forms', '', true, array(), $args );
		$values['fields'] = $fields;

        $edit_message = __( 'Form was Successfully Updated', 'formidable' );
        if ( $form->is_template && $message == $edit_message ) {
            $message = __( 'Template was Successfully Updated', 'formidable' );
        }

		$all_templates = FrmForm::getAll( array( 'is_template' => 1 ), 'name' );

        if ( $form->default_template ) {
			wp_die( esc_html__( 'That template cannot be edited', 'formidable' ) );
		} elseif ( defined( 'DOING_AJAX' ) ) {
            wp_die();
        } else if ( $create_link ) {
			require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/new.php' );
        } else {
			require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/edit.php' );
        }
    }

	public static function get_settings_vars( $id, $errors = array(), $message = '' ) {
		FrmAppHelper::permission_check( 'frm_edit_forms' );

        global $frm_vars;

        $form = FrmForm::getOne( $id );

		$fields = FrmField::get_all_for_form( $id );
		$values = FrmAppHelper::setup_edit_vars( $form, 'forms', $fields, true );

		if ( isset( $values['default_template'] ) && $values['default_template'] ) {
			wp_die( esc_html__( 'That template cannot be edited', 'formidable' ) );
		}

		self::clean_submit_html( $values );

        $action_controls = FrmFormActionsController::get_form_actions();

		$sections = apply_filters( 'frm_add_form_settings_section', array(), $values );
        $pro_feature = FrmAppHelper::pro_is_installed() ? '' : ' class="pro_feature"';

		$styles = apply_filters( 'frm_get_style_opts', array() );

		$first_h3 = 'frm_first_h3';

		require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/settings.php' );
    }

	/**
	 * Replace old Submit Button href with new href to avoid errors in Chrome
	 *
	 * @since 2.03.08
	 *
	 * @param array|boolean $values
	 */
	private static function clean_submit_html( &$values ) {
		if ( is_array( $values ) && isset( $values['submit_html'] ) ) {
			$values['submit_html'] = str_replace( 'javascript:void(0)', '#', $values['submit_html'] );
		}
	}

    public static function mb_tags_box( $form_id, $class = '' ) {
		$fields = FrmField::get_all_for_form( $form_id, '', 'include' );
        $linked_forms = array();
        $col = 'one';
		$settings_tab = FrmAppHelper::is_admin_page( 'formidable' ) ? true : false;

		$cond_shortcodes = apply_filters( 'frm_conditional_shortcodes', array() );
		$adv_shortcodes = self::get_advanced_shortcodes();
		$user_fields = apply_filters( 'frm_user_shortcodes', array() );
		$entry_shortcodes = self::get_shortcode_helpers( $settings_tab );

		include( FrmAppHelper::plugin_path() . '/classes/views/shared/mb_adv_info.php' );
    }

	/**
	 * Get an array of the options to display in the advanced tab
	 * of the customization panel
	 * @since 2.0.6
	 */
	private static function get_advanced_shortcodes() {
		$adv_shortcodes = array(
			'sep=", "'       => array(
				'label' => __( 'Separator', 'formidable' ),
				'title' => __( 'Use a different separator for checkbox fields', 'formidable' ),
			),
			'format="d-m-Y"' => __( 'Date Format', 'formidable' ),
			'show="field_label"' => __( 'Field Label', 'formidable' ),
			'wpautop=0'      => array(
				'label' => __( 'No Auto P', 'formidable' ),
				'title' => __( 'Do not automatically add any paragraphs or line breaks', 'formidable' ),
			),
		);
		$adv_shortcodes = apply_filters( 'frm_advanced_shortcodes', $adv_shortcodes );
		// __( 'Leave blank instead of defaulting to User Login', 'formidable' ) : blank=1

		return $adv_shortcodes;
	}

	/**
	 * Get an array of the helper shortcodes to display in the customization panel
	 * @since 2.0.6
	 */
	private static function get_shortcode_helpers( $settings_tab ) {
		$entry_shortcodes = array(
			'id'        => __( 'Entry ID', 'formidable' ),
			'key'       => __( 'Entry Key', 'formidable' ),
			'post_id'   => __( 'Post ID', 'formidable' ),
			'ip'        => __( 'User IP', 'formidable' ),
			'created-at' => __( 'Entry created', 'formidable' ),
			'updated-at' => __( 'Entry updated', 'formidable' ),
			''          => '',
			'siteurl'   => __( 'Site URL', 'formidable' ),
			'sitename'  => __( 'Site Name', 'formidable' ),
        );

		if ( ! FrmAppHelper::pro_is_installed() ) {
			unset( $entry_shortcodes['post_id'] );
		}

		if ( $settings_tab ) {
			$entry_shortcodes['default-message'] = __( 'Default Msg', 'formidable' );
			$entry_shortcodes['default-html'] = __( 'Default HTML', 'formidable' );
			$entry_shortcodes['default-plain'] = __( 'Default Plain', 'formidable' );
		}

		/**
		 * Use this hook to add or remove buttons in the helpers section
		 * in the customization panel
		 * @since 2.0.6
		 */
		$entry_shortcodes = apply_filters( 'frm_helper_shortcodes', $entry_shortcodes, $settings_tab );

		return $entry_shortcodes;
	}

	/**
	 * Insert the form class setting into the form
	 */
	public static function form_classes( $form ) {
		if ( isset( $form->options['form_class'] ) ) {
			echo esc_attr( sanitize_text_field( $form->options['form_class'] ) );
		}

		if ( isset( $form->options['js_validate'] ) && $form->options['js_validate'] ) {
			echo ' frm_js_validate ';
		}
	}

	public static function get_email_html() {
		FrmAppHelper::permission_check( 'frm_view_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		echo FrmEntriesController::show_entry_shortcode( // WPCS: XSS ok.
			array(
				'form_id'       => FrmAppHelper::get_post_param( 'form_id', '', 'absint' ),
				'default_email' => true,
				'plain_text'    => FrmAppHelper::get_post_param( 'plain_text', '', 'absint' ),
			)
		);
		wp_die();
	}

    public static function filter_content( $content, $form, $entry = false ) {
		self::get_entry_by_param( $entry );
        if ( ! $entry ) {
            return $content;
        }

        if ( is_object( $form ) ) {
            $form = $form->id;
        }

        $shortcodes = FrmFieldsHelper::get_shortcodes( $content, $form );
        $content = apply_filters( 'frm_replace_content_shortcodes', $content, $entry, $shortcodes );

        return $content;
    }

	private static function get_entry_by_param( &$entry ) {
		if ( ! $entry || ! is_object( $entry ) ) {
			if ( ! $entry || ! is_numeric( $entry ) ) {
				$entry = FrmAppHelper::get_post_param( 'id', false, 'sanitize_title' );
			}

			FrmEntry::maybe_get_entry( $entry );
		}
	}

    public static function replace_content_shortcodes( $content, $entry, $shortcodes ) {
        return FrmFieldsHelper::replace_content_shortcodes( $content, $entry, $shortcodes );
    }

    public static function process_bulk_form_actions( $errors ) {
        if ( ! $_REQUEST ) {
            return $errors;
        }

		$bulkaction = FrmAppHelper::get_param( 'action', '', 'get', 'sanitize_text_field' );
        if ( $bulkaction == -1 ) {
			$bulkaction = FrmAppHelper::get_param( 'action2', '', 'get', 'sanitize_title' );
        }

        if ( ! empty( $bulkaction ) && strpos( $bulkaction, 'bulk_' ) === 0 ) {
            FrmAppHelper::remove_get_action();

            $bulkaction = str_replace( 'bulk_', '', $bulkaction );
        }

		$ids = FrmAppHelper::get_param( 'item-action', '', 'get', 'sanitize_text_field' );
        if ( empty( $ids ) ) {
            $errors[] = __( 'No forms were specified', 'formidable' );
            return $errors;
        }

        $permission_error = FrmAppHelper::permission_nonce_error( '', '_wpnonce', 'bulk-toplevel_page_formidable' );
        if ( $permission_error !== false ) {
            $errors[] = $permission_error;
            return $errors;
        }

        if ( ! is_array( $ids ) ) {
            $ids = explode( ',', $ids );
        }

        switch ( $bulkaction ) {
            case 'delete':
                $message = self::bulk_destroy( $ids );
				break;
            case 'trash':
                $message = self::bulk_trash( $ids );
				break;
            case 'untrash':
                $message = self::bulk_untrash( $ids );
        }

        if ( isset( $message ) && ! empty( $message ) ) {
			echo '<div id="message" class="updated frm_updated_message">' . FrmAppHelper::kses( $message, array( 'a' ) ) . '</div>'; // WPCS: XSS ok.
        }

        return $errors;
    }

	/**
	 * @deprecated 1.07.05
	 * @codeCoverageIgnore
	 */
    public static function add_default_templates( $path, $default = true, $template = true ) {
        _deprecated_function( __FUNCTION__, '1.07.05', 'FrmXMLController::add_default_templates()' );

		$path = untrailingslashit( trim( $path ) );
		$templates = glob( $path . '/*.php' );

		for ( $i = count( $templates ) - 1; $i >= 0; $i-- ) {
			$filename = str_replace( '.php', '', str_replace( $path . '/', '', $templates[ $i ] ) );
			$template_query = array( 'form_key' => $filename );
            if ( $template ) {
                $template_query['is_template'] = 1;
            }
            if ( $default ) {
                $template_query['default_template'] = 1;
            }
			$form = FrmForm::getAll( $template_query, '', 1 );

            $values = FrmFormsHelper::setup_new_vars();
            $values['form_key'] = $filename;
            $values['is_template'] = $template;
            $values['status'] = 'published';
            if ( $default ) {
                $values['default_template'] = 1;
            }

            include( $templates[ $i ] );

            //get updated form
			if ( isset( $form ) && ! empty( $form ) ) {
                $old_id = $form->id;
				$form = FrmForm::getOne( $form->id );
            } else {
                $old_id = false;
				$form = FrmForm::getAll( $template_query, '', 1 );
            }

            if ( $form ) {
				do_action( 'frm_after_duplicate_form', $form->id, (array) $form, array( 'old_id' => $old_id ) );
            }
        }
    }

    public static function route() {
		$action = isset( $_REQUEST['frm_action'] ) ? 'frm_action' : 'action';
        $vars = array();
		if ( isset( $_POST['frm_compact_fields'] ) ) {
			FrmAppHelper::permission_check( 'frm_edit_forms' );

			$json_vars = htmlspecialchars_decode( nl2br( stripslashes( str_replace( '&quot;', '\\\"', $_POST['frm_compact_fields'] ) ) ) );
			$json_vars = json_decode( $json_vars, true );
			if ( empty( $json_vars ) ) {
                // json decoding failed so we should return an error message
				$action = FrmAppHelper::get_param( $action, '', 'get', 'sanitize_title' );
                if ( 'edit' == $action ) {
                    $action = 'update';
                }

				add_filter( 'frm_validate_form', 'FrmFormsController::json_error' );
            } else {
				$vars = FrmAppHelper::json_to_array( $json_vars );
                $action = $vars[ $action ];
				unset( $_REQUEST['frm_compact_fields'], $_POST['frm_compact_fields'] );
				$_REQUEST = array_merge( $_REQUEST, $vars );
				$_POST = array_merge( $_POST, $_REQUEST );
            }
        } else {
			$action = FrmAppHelper::get_param( $action, '', 'get', 'sanitize_title' );
    		if ( isset( $_REQUEST['delete_all'] ) ) {
                // override the action for this page
    			$action = 'delete_all';
            }
        }

		add_action( 'frm_load_form_hooks', 'FrmHooksController::trigger_load_form_hooks' );
        FrmAppHelper::trigger_hook_load( 'form' );

        switch ( $action ) {
            case 'new':
				return self::new_form( $vars );
            case 'create':
            case 'edit':
            case 'update':
            case 'duplicate':
            case 'trash':
            case 'untrash':
            case 'destroy':
            case 'delete_all':
            case 'settings':
            case 'update_settings':
				return self::$action( $vars );
            default:
				do_action( 'frm_form_action_' . $action );
				if ( apply_filters( 'frm_form_stop_action_' . $action, false ) ) {
                    return;
                }

				$action = FrmAppHelper::get_param( 'action', '', 'get', 'sanitize_text_field' );
                if ( $action == -1 ) {
					$action = FrmAppHelper::get_param( 'action2', '', 'get', 'sanitize_title' );
                }

				if ( strpos( $action, 'bulk_' ) === 0 ) {
                    FrmAppHelper::remove_get_action();
                    return self::list_form();
                }

                return self::display_forms_list();
        }
    }

    public static function json_error( $errors ) {
        $errors['json'] = __( 'Abnormal HTML characters prevented your form from saving correctly', 'formidable' );
        return $errors;
    }


    /* FRONT-END FORMS */
    public static function admin_bar_css() {
		if ( is_admin() || ! current_user_can( 'frm_edit_forms' ) ) {
            return;
        }

		add_action( 'wp_before_admin_bar_render', 'FrmFormsController::admin_bar_configure' );
		FrmAppHelper::load_font_style();
	}

	public static function admin_bar_configure() {
        global $frm_vars;
		if ( empty( $frm_vars['forms_loaded'] ) ) {
            return;
        }

        $actions = array();
		foreach ( $frm_vars['forms_loaded'] as $form ) {
			if ( is_object( $form ) ) {
				$actions[ $form->id ] = $form->name;
			}
			unset( $form );
		}

		if ( empty( $actions ) ) {
			return;
		}

		self::add_menu_to_admin_bar();
		self::add_forms_to_admin_bar( $actions );
	}

	/**
	 * @since 2.05.07
	 */
	public static function add_menu_to_admin_bar() {
		global $wp_admin_bar;

		$wp_admin_bar->add_node(
			array(
				'id'    => 'frm-forms',
				'title' => '<span class="ab-icon"></span><span class="ab-label">' . FrmAppHelper::get_menu_name() . '</span>',
				'href'  => admin_url( 'admin.php?page=formidable' ),
				'meta'  => array(
					'title' => FrmAppHelper::get_menu_name(),
				),
			)
		);
	}

	/**
	 * @since 2.05.07
	 */
	private static function add_forms_to_admin_bar( $actions ) {
		global $wp_admin_bar;

		asort( $actions );

		foreach ( $actions as $form_id => $name ) {

			$wp_admin_bar->add_node(
				array(
					'parent'    => 'frm-forms',
					'id'        => 'edit_form_' . $form_id,
					'title'     => empty( $name ) ? __( '(no title)' ) : $name,
					'href'      => admin_url( 'admin.php?page=formidable&frm_action=edit&id=' . $form_id ),
				)
			);
		}
	}

    //formidable shortcode
	public static function get_form_shortcode( $atts ) {
        global $frm_vars;
		if ( isset( $frm_vars['skip_shortcode'] ) && $frm_vars['skip_shortcode'] ) {
            $sc = '[formidable';
			if ( ! empty( $atts ) ) {
				foreach ( $atts as $k => $v ) {
					$sc .= ' ' . $k . '="' . esc_attr( $v ) . '"';
				}
			}
			return $sc . ']';
        }

		$shortcode_atts = shortcode_atts(
			array(
				'id'          => '',
				'key'         => '',
				'title'       => false,
				'description' => false,
				'readonly'    => false,
				'entry_id'    => false,
				'fields'      => array(),
				'exclude_fields' => array(),
				'minimize'    => false,
			),
			$atts
		);
		do_action( 'formidable_shortcode_atts', $shortcode_atts, $atts );

        return self::show_form( $shortcode_atts['id'], $shortcode_atts['key'], $shortcode_atts['title'], $shortcode_atts['description'], $atts );
    }

    public static function show_form( $id = '', $key = '', $title = false, $description = false, $atts = array() ) {
        if ( empty( $id ) ) {
            $id = $key;
        }

        $form = self::maybe_get_form_to_show( $id );
        if ( ! $form ) {
            return __( 'Please select a valid form', 'formidable' );
        }

		FrmAppController::maybe_update_styles();

		add_action( 'frm_load_form_hooks', 'FrmHooksController::trigger_load_form_hooks' );
        FrmAppHelper::trigger_hook_load( 'form', $form );

        $form = apply_filters( 'frm_pre_display_form', $form );

        $frm_settings = FrmAppHelper::get_settings();

		if ( self::is_viewable_draft_form( $form ) ) {
			// don't show a draft form on a page
			$form = __( 'Please select a valid form', 'formidable' );
		} else if ( self::user_should_login( $form ) ) {
			$form = do_shortcode( $frm_settings->login_msg );
		} else if ( self::user_has_permission_to_view( $form ) ) {
			$form = do_shortcode( $frm_settings->login_msg );
		} else {
			do_action( 'frm_pre_get_form', $form );
			$form = self::get_form( $form, $title, $description, $atts );

			/**
			 * Use this shortcode to check for external shortcodes that may span
			 * across multiple fields in the customizable HTML
			 * @since 2.0.8
			 */
			$form = apply_filters( 'frm_filter_final_form', $form );
		}

		return $form;
    }

	private static function maybe_get_form_to_show( $id ) {
		$form = false;

		if ( ! empty( $id ) ) { // no form id or key set
			$form = FrmForm::getOne( $id );
			if ( ! $form || $form->parent_form_id || $form->status == 'trash' ) {
				$form = false;
			}
		}

		return $form;
	}

	private static function is_viewable_draft_form( $form ) {
		global $post;
		$frm_settings = FrmAppHelper::get_settings();
		return $form->status == 'draft' && current_user_can( 'frm_edit_forms' ) && ! FrmAppHelper::is_preview_page();
	}

	private static function user_should_login( $form ) {
		return $form->logged_in && ! is_user_logged_in();
	}

	private static function user_has_permission_to_view( $form ) {
		return $form->logged_in && get_current_user_id() && isset( $form->options['logged_in_role'] ) && $form->options['logged_in_role'] != '' && ! FrmAppHelper::user_has_permission( $form->options['logged_in_role'] );
	}

    public static function get_form( $form, $title, $description, $atts = array() ) {
		ob_start();

		do_action( 'frm_before_get_form', $atts );

        self::get_form_contents( $form, $title, $description, $atts );
		self::enqueue_scripts( FrmForm::get_params( $form ) );

        $contents = ob_get_contents();
        ob_end_clean();

		self::maybe_minimize_form( $atts, $contents );

        return $contents;
    }

	public static function enqueue_scripts( $params ) {
		do_action( 'frm_enqueue_form_scripts', $params );
	}

	public static function get_form_contents( $form, $title, $description, $atts ) {
		$params = FrmForm::get_params( $form );
		$errors = self::get_saved_errors( $form, $params );
		$fields = FrmFieldsHelper::get_form_fields( $form->id, $errors );
		$reset = false;
		$pass_args = compact( 'form', 'fields', 'errors', 'title', 'description', 'reset' );

		$handle_process_here = $params['action'] == 'create' && $params['posted_form_id'] == $form->id && $_POST;

		if ( ! $handle_process_here ) {
			do_action( 'frm_display_form_action', $params, $fields, $form, $title, $description );
			if ( apply_filters( 'frm_continue_to_new', true, $form->id, $params['action'] ) ) {
				self::show_form_after_submit( $pass_args );
			}
		} elseif ( ! empty( $errors ) ) {
			self::show_form_after_submit( $pass_args );

		} else {

			do_action( 'frm_validate_form_creation', $params, $fields, $form, $title, $description );

			if ( apply_filters( 'frm_continue_to_create', true, $form->id ) ) {
				$entry_id = self::just_created_entry( $form->id );
				$pass_args['entry_id'] = $entry_id;
				$pass_args['reset'] = true;
				$pass_args['conf_method'] = self::get_confirmation_method( compact( 'form', 'entry_id' ) );

				self::run_success_action( $pass_args );

				do_action(
					'frm_after_entry_processed',
					array(
						'entry_id' => $entry_id,
						'form'     => $form,
					)
				);
			}
		}
	}

	/**
	 * If the form was processed earlier (init), get the generated errors
	 * @since 2.05
	 */
	private static function get_saved_errors( $form, $params ) {
		global $frm_vars;

		if ( $params['posted_form_id'] == $form->id && $_POST && isset( $frm_vars['created_entries'][ $form->id ] ) ) {
			$errors = $frm_vars['created_entries'][ $form->id ]['errors'];
		} else {
			$errors = array();
		}
		return $errors;
	}

	/**
	 * @since 2.2.7
	 */
	public static function just_created_entry( $form_id ) {
		global $frm_vars;
		return ( isset( $frm_vars['created_entries'] ) && isset( $frm_vars['created_entries'][ $form_id ] ) && isset( $frm_vars['created_entries'][ $form_id ]['entry_id'] ) ) ? $frm_vars['created_entries'][ $form_id ]['entry_id'] : 0;
	}

	/**
	 * @since 3.0
	 */
	private static function get_confirmation_method( $atts ) {
		$opt = 'success_action';
		$method = ( isset( $atts['form']->options[ $opt ] ) && ! empty( $atts['form']->options[ $opt ] ) ) ? $atts['form']->options[ $opt ] : 'message';
		$method = apply_filters( 'frm_success_filter', $method, $atts['form'], 'create' );

		if ( $method != 'message' && ( ! $atts['entry_id'] || ! is_numeric( $atts['entry_id'] ) ) ) {
			$method = 'message';
		}

		return $method;
	}

	public static function maybe_trigger_redirect( $form, $params, $args ) {
		if ( ! isset( $params['id'] ) ) {
			global $frm_vars;
			$params['id'] = $frm_vars['created_entries'][ $form->id ]['entry_id'];
		}

		$conf_method = self::get_confirmation_method(
			array(
				'form'     => $form,
				'entry_id' => $params['id'],
			)
		);

		if ( 'redirect' === $conf_method ) {
			self::trigger_redirect( $form, $params, $args );
		}
	}

	public static function trigger_redirect( $form, $params, $args ) {
		$success_args = array(
			'action'      => $params['action'],
			'conf_method' => 'redirect',
			'form'        => $form,
			'entry_id'    => $params['id'],
		);

		if ( isset( $args['ajax'] ) ) {
			$success_args['ajax'] = $args['ajax'];
		}

		self::run_success_action( $success_args );
	}

	/**
	 * Used when the success action is not 'message'
	 * @since 2.05
	 */
	public static function run_success_action( $args ) {
		$extra_args = $args;
		unset( $extra_args['form'] );

		do_action( 'frm_success_action', $args['conf_method'], $args['form'], $args['form']->options, $args['entry_id'], $extra_args );

		$opt = ( ! isset( $args['action'] ) || $args['action'] == 'create' ) ? 'success' : 'edit';
		$args['success_opt'] = $opt;
		if ( $args['conf_method'] == 'page' && is_numeric( $args['form']->options[ $opt . '_page_id' ] ) ) {
			self::load_page_after_submit( $args );
		} elseif ( $args['conf_method'] == 'redirect' ) {
			self::redirect_after_submit( $args );
		} else {
			self::show_message_after_save( $args );
		}
	}

	/**
	 * @since 3.0
	 */
	private static function load_page_after_submit( $args ) {
		global $post;
		$opt = $args['success_opt'];
		if ( ! $post || $args['form']->options[ $opt . '_page_id' ] != $post->ID ) {
			$page = get_post( $args['form']->options[ $opt . '_page_id' ] );
			$old_post = $post;
			$post = $page;
			$content = apply_filters( 'frm_content', $page->post_content, $args['form'], $args['entry_id'] );
			echo apply_filters( 'the_content', $content ); // WPCS: XSS ok.
			$post = $old_post;
		}
	}

	/**
	 * @since 3.0
	 */
	private static function redirect_after_submit( $args ) {
		global $frm_vars;

		add_filter( 'frm_use_wpautop', '__return_false' );

		$opt = $args['success_opt'];
		$success_url = trim( $args['form']->options[ $opt . '_url' ] );
		$success_url = apply_filters( 'frm_content', $success_url, $args['form'], $args['entry_id'] );
		$success_url = do_shortcode( $success_url );

		$success_msg = isset( $args['form']->options[ $opt . '_msg' ] ) ? $args['form']->options[ $opt . '_msg' ] : __( 'Please wait while you are redirected.', 'formidable' );

		$redirect_msg = self::get_redirect_message( $success_url, $success_msg, $args );

		$args['id'] = $args['entry_id'];
		FrmEntriesController::delete_entry_before_redirect( $success_url, $args['form'], $args );

		add_filter( 'frm_redirect_url', 'FrmEntriesController::prepare_redirect_url' );
		$success_url = apply_filters( 'frm_redirect_url', $success_url, $args['form'], $args );

		$doing_ajax = FrmAppHelper::doing_ajax();

		if ( isset( $args['ajax'] ) && $args['ajax'] && $doing_ajax ) {
			echo json_encode( array( 'redirect' => $success_url ) );
			wp_die();
		} elseif ( ! headers_sent() ) {
			wp_redirect( esc_url_raw( $success_url ) );
			die(); // do not use wp_die or redirect fails
		} else {
			add_filter( 'frm_use_wpautop', '__return_true' );

			echo $redirect_msg; // WPCS: XSS ok.
			echo "<script type='text/javascript'>window.onload = function(){setTimeout(window.location='" . esc_url_raw( $success_url ) . "', 8000);}</script>";
		}
	}

	/**
	 * @since 3.0
	 * @param string $success_url
	 * @param string $success_msg
	 * @param array $args
	 */
	private static function get_redirect_message( $success_url, $success_msg, $args ) {
		$redirect_msg = '<div class="' . esc_attr( FrmFormsHelper::get_form_style_class( $args['form'] ) ) . '"><div class="frm-redirect-msg frm_message">' . $success_msg . '<br/>' .
			sprintf( __( '%1$sClick here%2$s if you are not automatically redirected.', 'formidable' ), '<a href="' . esc_url( $success_url ) . '">', '</a>' ) .
			'</div></div>';

		$redirect_args = array(
			'entry_id' => $args['entry_id'],
			'form_id'  => $args['form']->id,
			'form'     => $args['form'],
		);
		return apply_filters( 'frm_redirect_msg', $redirect_msg, $redirect_args );
	}

	/**
	 * Prepare to show the success message and empty form after submit
	 * @since 2.05
	 */
	public static function show_message_after_save( $atts ) {
		$atts['message'] = self::prepare_submit_message( $atts['form'], $atts['entry_id'] );

		if ( ! isset( $atts['form']->options['show_form'] ) || $atts['form']->options['show_form'] ) {
			self::show_form_after_submit( $atts );
		} else {
			self::show_lone_success_messsage( $atts );
		}
	}

	/**
	 * Show an empty form
	 * @since 2.05
	 */
	private static function show_form_after_submit( $args ) {
		self::fill_atts_for_form_display( $args );

		$errors = $args['errors'];
		$message = $args['message'];
		$form = $args['form'];
		$title = $args['title'];
		$description = $args['description'];

		if ( empty( $args['fields'] ) ) {
			$values = array();
		} else {
			$values = FrmEntriesHelper::setup_new_vars( $args['fields'], $form, $args['reset'] );
		}
		unset( $args );

		$include_form_tag = apply_filters( 'frm_include_form_tag', true, $form );

		$frm_settings = FrmAppHelper::get_settings();
		$submit = isset( $form->options['submit_value'] ) ? $form->options['submit_value'] : $frm_settings->submit_value;

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-entries/new.php' );
	}

	/**
	 * Get all the values needed on the new.php entry page
	 * @since 2.05
	 */
	private static function fill_atts_for_form_display( &$args ) {
		$defaults = array(
			'errors'  => array(),
			'message' => '',
			'fields'  => array(),
			'form'    => array(),
			'title'   => true,
			'description' => false,
			'reset'   => false,
		);
		$args = wp_parse_args( $args, $defaults );
	}

	/**
	 * Show the success message without the form
	 * @since 2.05
	 */
	private static function show_lone_success_messsage( $atts ) {
		global $frm_vars;
		$values = FrmEntriesHelper::setup_new_vars( $atts['fields'], $atts['form'], true );
		self::maybe_load_css( $atts['form'], $values['custom_style'], $frm_vars['load_css'] );

		$include_extra_container = 'frm_forms' . FrmFormsHelper::get_form_style_class( $values );
		$errors = array();
		$form = $atts['form'];
		$message = $atts['message'];

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-entries/errors.php' );
	}

	/**
	 * Prepare the success message before it's shown
	 * @since 2.05
	 */
	private static function prepare_submit_message( $form, $entry_id ) {
		$frm_settings = FrmAppHelper::get_settings();

		if ( $entry_id && is_numeric( $entry_id ) ) {
			$message = isset( $form->options['success_msg'] ) ? $form->options['success_msg'] : $frm_settings->success_msg;
			$class = 'frm_message';
		} else {
			$message = $frm_settings->failed_msg;
			$class = FrmFormsHelper::form_error_class();
		}

		$message = FrmFormsHelper::get_success_message( compact( 'message', 'form', 'entry_id', 'class' ) );
		return apply_filters( 'frm_main_feedback', $message, $form, $entry_id );
	}

	public static function front_head() {
		$version = FrmAppHelper::plugin_version();
		$suffix = FrmAppHelper::js_suffix();

		if ( ! empty( $suffix ) && self::has_combo_js_file() ) {
			wp_register_script( 'formidable', FrmAppHelper::plugin_url() . '/js/frm.min.js', array( 'jquery' ), $version, true );
		} else {
			wp_register_script( 'formidable', FrmAppHelper::plugin_url() . "/js/formidable{$suffix}.js", array( 'jquery' ), $version, true );
		}

		add_filter( 'script_loader_tag', 'FrmFormsController::defer_script_loading', 10, 2 );

		if ( FrmAppHelper::is_admin() ) {
			// don't load this in back-end
			return;
		}

		FrmAppHelper::localize_script( 'front' );
		FrmStylesController::enqueue_css( 'register' );
	}

	/**
	 * @since 3.0
	 */
	public static function has_combo_js_file() {
		return is_readable( FrmAppHelper::plugin_path() . '/js/frm.min.js' );
	}

	public static function maybe_load_css( $form, $this_load, $global_load ) {
		$load_css = FrmForm::is_form_loaded( $form, $this_load, $global_load );

		if ( $load_css ) {
			global $frm_vars;
			self::footer_js( 'header' );
			$frm_vars['css_loaded'] = true;
		}
	}

	public static function defer_script_loading( $tag, $handle ) {
	    if ( 'recaptcha-api' == $handle && ! strpos( $tag, 'defer' ) ) {
	        $tag = str_replace( ' src', ' defer="defer" async="async" src', $tag );
		}
	    return $tag;
	}

	public static function footer_js( $location = 'footer' ) {
		global $frm_vars;

		FrmStylesController::enqueue_css();

		if ( ! FrmAppHelper::is_admin() && $location != 'header' && ! empty( $frm_vars['forms_loaded'] ) ) {
			//load formidable js
			wp_enqueue_script( 'formidable' );
		}
	}

	/**
	 * @since 2.0.8
	 */
	private static function maybe_minimize_form( $atts, &$content ) {
		// check if minimizing is turned on
		if ( self::is_minification_on( $atts ) ) {
			$content = str_replace( array( "\r\n", "\r", "\n", "\t", '    ' ), '', $content );
		}
	}

	/**
	 * @since 2.0.8
	 * @return boolean
	 */
	private static function is_minification_on( $atts ) {
		return isset( $atts['minimize'] ) && ! empty( $atts['minimize'] );
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function bulk_create_template( $ids ) {
		_deprecated_function( __METHOD__, '3.0', 'FrmForm::duplicate( $id, true, true )' );
		FrmAppHelper::permission_check( 'frm_edit_forms' );

		foreach ( $ids as $id ) {
			FrmForm::duplicate( $id, true, true );
		}

		return __( 'Form template was Successfully Created', 'formidable' );
	}

	/**
	 * @deprecated 2.03
	 * @codeCoverageIgnore
	 */
	public static function register_pro_scripts() {
		_deprecated_function( __FUNCTION__, '2.03', 'FrmProEntriesController::register_scripts' );
		if ( FrmAppHelper::pro_is_installed() ) {
			FrmProEntriesController::register_scripts();
		}
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function edit_key() {
		_deprecated_function( __METHOD__, '3.0' );
		$values = self::edit_in_place_value( 'form_key' );
		echo wp_kses( stripslashes( FrmForm::get_key_by_id( $values['form_id'] ) ), array() );
		wp_die();
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function edit_description() {
		_deprecated_function( __METHOD__, '3.0' );
		$values = self::edit_in_place_value( 'description' );
		echo wp_kses_post( FrmAppHelper::use_wpautop( stripslashes( $values['description'] ) ) );
		wp_die();
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	private static function edit_in_place_value( $field ) {
		_deprecated_function( __METHOD__, '3.0' );
		check_ajax_referer( 'frm_ajax', 'nonce' );
		FrmAppHelper::permission_check( 'frm_edit_forms', 'hide' );

		$form_id = FrmAppHelper::get_post_param( 'form_id', '', 'absint' );
		$value = FrmAppHelper::get_post_param( 'update_value', '', 'wp_filter_post_kses' );

		$values = array( $field => trim( $value ) );
		FrmForm::update( $form_id, $values );
		$values['form_id'] = $form_id;

		return $values;
	}
}
