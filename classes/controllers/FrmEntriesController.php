<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmEntriesController {

	public static function menu() {
		FrmAppHelper::force_capability( 'frm_view_entries' );

		add_submenu_page( 'formidable', 'Formidable | ' . __( 'Entries', 'formidable' ), __( 'Entries', 'formidable' ), 'frm_view_entries', 'formidable-entries', 'FrmEntriesController::route' );

		$views_installed = is_callable( 'FrmProAppHelper::views_is_installed' ) ? FrmProAppHelper::views_is_installed() : FrmAppHelper::pro_is_installed();
		if ( ! $views_installed ) {
			add_submenu_page( 'formidable', 'Formidable | ' . __( 'Views', 'formidable' ), __( 'Views', 'formidable' ), 'frm_view_entries', 'formidable-views', 'FrmFormsController::no_views' );
		}

		if ( FrmAppHelper::is_admin_page( 'formidable-entries' ) ) {
			self::load_manage_entries_hooks();
		}
	}

	/**
	 * @since 2.05.07
	 */
	private static function load_manage_entries_hooks() {
		if ( ! in_array( FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' ), array( 'edit', 'show', 'new', 'duplicate' ), true ) ) {
			$menu_name = FrmAppHelper::get_menu_name();
			$base      = self::base_column_key( $menu_name );

			add_filter( 'manage_' . $base . '_columns', 'FrmEntriesController::manage_columns' );
			add_filter( 'get_user_option_' . self::hidden_column_key( $menu_name ), 'FrmEntriesController::hidden_columns' );
			add_filter( 'manage_' . $base . '_sortable_columns', 'FrmEntriesController::sortable_columns' );
		} else {
			add_filter( 'screen_options_show_screen', __CLASS__ . '::remove_screen_options', 10, 2 );
		}
	}

	/**
	 * Display in Back End.
	 */
	public static function route() {
		$action = FrmAppHelper::get_param( 'frm_action', '', 'get', 'sanitize_title' );
		FrmAppHelper::include_svg();

		switch ( $action ) {
			case 'show':
			case 'destroy':
				return self::$action();

			default:
				do_action( 'frm_entry_action_route', $action );
				if ( apply_filters( 'frm_entry_stop_action_route', false, $action ) ) {
					return;
				}

				return self::display_list();
		}
	}

	/**
	 * Prevent the "screen options" tab from showing when
	 * editing or creating an entry
	 *
	 * @since 3.0
	 */
	public static function remove_screen_options( $show_screen, $screen ) {
		$menu_name = sanitize_title( FrmAppHelper::get_menu_name() );
		if ( $screen->id == $menu_name . '_page_formidable-entries' ) {
			$show_screen = false;
		}

		return $show_screen;
	}

	public static function manage_columns( $columns ) {
		global $frm_vars;
		$form_id = FrmForm::get_current_form_id();

		$columns[ $form_id . '_id' ]       = 'ID';
		$columns[ $form_id . '_item_key' ] = esc_html__( 'Entry Key', 'formidable' );

		if ( $form_id ) {
			self::get_columns_for_form( $form_id, $columns );
		} else {
			$columns[ $form_id . '_form_id' ] = esc_html__( 'Form', 'formidable' );
			$columns[ $form_id . '_name' ]    = esc_html__( 'Entry Name', 'formidable' );
			$columns[ $form_id . '_user_id' ] = esc_html__( 'Created By', 'formidable' );
		}

		$columns[ $form_id . '_is_draft' ]   = esc_html__( 'Entry Status', 'formidable' );
		$columns[ $form_id . '_created_at' ] = __( 'Entry creation date', 'formidable' );
		$columns[ $form_id . '_updated_at' ] = __( 'Entry update date', 'formidable' );
		self::maybe_add_ip_col( $form_id, $columns );

		$frm_vars['cols'] = $columns;

		$action = FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );
		if ( FrmAppHelper::is_admin_page( 'formidable-entries' ) && in_array( $action, array( '', 'list', 'destroy' ) ) ) {
			add_screen_option(
				'per_page',
				array(
					'label'   => esc_html__( 'Entries', 'formidable' ),
					'default' => 20,
					'option'  => 'formidable_page_formidable_entries_per_page',
				)
			);
		}

		return $columns;
	}

	private static function get_columns_for_form( $form_id, &$columns ) {
		$form_cols = FrmField::get_all_for_form( $form_id, '', 'include' );

		/**
		 * Allows changing fields in the Entries list table heading.
		 *
		 * @since 5.0.04
		 *
		 * @param array $fields  Array of fields.
		 * @param array $args    The arguments. Contains `form_id`.
		 */
		$form_cols = apply_filters( 'frm_fields_in_entries_list_table', $form_cols, compact( 'form_id' ) );

		foreach ( $form_cols as $form_col ) {
			if ( FrmField::is_no_save_field( $form_col->type ) ) {
				continue;
			}

			$has_child_fields = $form_col->type === 'form' && ! empty( $form_col->field_options['form_select'] );
			if ( $has_child_fields ) {
				self::add_subform_cols( $form_col, $form_id, $columns );
			} else {
				self::add_field_cols( $form_col, $form_id, $columns );
			}
		}
	}

	/**
	 * @since 3.01
	 */
	private static function add_subform_cols( $field, $form_id, &$columns ) {
		$sub_form_cols = FrmField::get_all_for_form( $field->field_options['form_select'] );
		if ( empty( $sub_form_cols ) ) {
			return;
		}

		foreach ( $sub_form_cols as $k => $sub_form_col ) {
			if ( FrmField::is_no_save_field( $sub_form_col->type ) ) {
				unset( $sub_form_cols[ $k ] );
				continue;
			}
			$columns[ $form_id . '_' . $sub_form_col->field_key . '-_-' . $field->id ] = FrmAppHelper::truncate( $sub_form_col->name, 35 );
			unset( $sub_form_col );
		}
	}

	/**
	 * @since 3.01
	 */
	private static function add_field_cols( $field, $form_id, &$columns ) {
		$col_id = $field->field_key;
		if ( $field->form_id != $form_id ) {
			$col_id .= '-_-form' . $field->form_id;
		}

		$has_separate_value = ! FrmField::is_option_empty( $field, 'separate_value' );
		$is_post_status     = FrmField::is_option_true( $field, 'post_field' ) && $field->field_options['post_field'] === 'post_status';
		if ( $has_separate_value && ! $is_post_status ) {
			$columns[ $form_id . '_frmsep_' . $col_id ] = FrmAppHelper::truncate( $field->name, 35 );
		}

		$columns[ $form_id . '_' . $col_id ] = FrmAppHelper::truncate( $field->name, 35 );
	}

	private static function maybe_add_ip_col( $form_id, &$columns ) {
		if ( FrmAppHelper::ips_saved() ) {
			$columns[ $form_id . '_ip' ] = 'IP';
		}
	}

	public static function check_hidden_cols( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		$this_page_name = self::hidden_column_key();
		if ( $meta_key != $this_page_name || $meta_value == $prev_value ) {
			return $check;
		}

		if ( empty( $prev_value ) ) {
			$prev_value = get_metadata( 'user', $object_id, $meta_key, true );
		}

		global $frm_vars;
		// Add a check so we don't create a loop.
		$frm_vars['prev_hidden_cols'] = ! empty( $frm_vars['prev_hidden_cols'] ) ? false : $prev_value;

		return $check;
	}

	/**
	 * Add hidden columns back from other forms
	 */
	public static function update_hidden_cols( $meta_id, $object_id, $meta_key, $meta_value ) {
		$this_page_name = self::hidden_column_key();
		if ( $meta_key != $this_page_name ) {
			return;
		}

		global $frm_vars;
		if ( ! isset( $frm_vars['prev_hidden_cols'] ) || ! $frm_vars['prev_hidden_cols'] ) {
			// Don't continue if there's no previous value.
			return;
		}

		foreach ( $meta_value as $mk => $mv ) {
			// Remove blank values.
			if ( empty( $mv ) ) {
				unset( $meta_value[ $mk ] );
			}
		}

		$cur_form_prefix = reset( $meta_value );
		$cur_form_prefix = explode( '_', $cur_form_prefix );
		$cur_form_prefix = $cur_form_prefix[0];
		$save            = false;

		foreach ( (array) $frm_vars['prev_hidden_cols'] as $prev_hidden ) {
			if ( empty( $prev_hidden ) || in_array( $prev_hidden, $meta_value ) ) {
				// Don't add blank cols or process included cols.
				continue;
			}

			$form_prefix = explode( '_', $prev_hidden );
			$form_prefix = $form_prefix[0];
			if ( $form_prefix == $cur_form_prefix ) {
				// Don't add back columns that are meant to be hidden.
				continue;
			}

			$meta_value[] = $prev_hidden;
			$save         = true;
			unset( $form_prefix );
		}

		if ( $save ) {
			$user_id = get_current_user_id();
			update_user_option( $user_id, $this_page_name, $meta_value, true );
		}
	}

	/**
	 * @since 2.05.07
	 */
	private static function hidden_column_key( $menu_name = '' ) {
		$base = self::base_column_key( $menu_name );

		return 'manage' . $base . 'columnshidden';
	}

	/**
	 * @since 2.05.07
	 */
	private static function base_column_key( $menu_name = '' ) {
		if ( empty( $menu_name ) ) {
			$menu_name = FrmAppHelper::get_menu_name();
		}

		return sanitize_title( $menu_name ) . '_page_formidable-entries';
	}

	public static function save_per_page( $save, $option, $value ) {
		if ( $option === 'formidable_page_formidable_entries_per_page' ) {
			$save = (int) $value;
		}

		return $save;
	}

	public static function sortable_columns() {
		$form_id = FrmForm::get_current_form_id();
		$fields  = FrmField::get_all_for_form( $form_id );

		$columns = array(
			$form_id . '_id'         => 'id',
			$form_id . '_created_at' => 'created_at',
			$form_id . '_updated_at' => 'updated_at',
			$form_id . '_ip'         => 'ip',
			$form_id . '_item_key'   => 'item_key',
			$form_id . '_is_draft'   => 'is_draft',
		);

		if ( ! $form_id ) {
			$columns[ $form_id . '_user_id' ] = 'user_id';
			$columns[ $form_id . '_name' ]    = 'name';
			$columns[ $form_id . '_form_id' ] = 'form_id';
		}

		foreach ( $fields as $field ) {
			if ( self::field_supports_sorting( $field ) ) {
				$columns[ $form_id . '_' . $field->field_key ] = 'meta_' . $field->id;
			}
		}

		return $columns;
	}

	/**
	 * Can't sort on checkboxes because they are sorted serialized.
	 * Some post content can be sorted but not everything.
	 *
	 * @param stdClass $field
	 * @return bool
	 */
	private static function field_supports_sorting( $field ) {
		$is_sortable = 'checkbox' !== $field->type && empty( $field->field_options['post_field'] );
		return apply_filters( 'frm_field_column_is_sortable', $is_sortable, $field );
	}

	/**
	 * @param mixed $result Option value from database for hidden columns in entries table.
	 * @return array
	 */
	public static function hidden_columns( $result ) {
		if ( ! is_array( $result ) ) {
			// Force an unexpected value to be an array.
			// Since $result is a filtered option and gets saved to the database, it's possible it could be a string.
			// Since this code expects an array it would break with a "Uncaught Error: [] operator not supported for strings" error.
			$result = array();
		}

		$form_id = FrmForm::get_current_form_id();

		$hidden = self::user_hidden_columns_for_form( $form_id, $result );

		global $frm_vars;
		$i = isset( $frm_vars['cols'] ) ? count( $frm_vars['cols'] ) : 0;

		if ( ! empty( $hidden ) ) {
			$result      = $hidden;
			$i           = $i - count( $result );
			$max_columns = 11;
		} else {
			$max_columns = 8;
		}

		if ( $i <= $max_columns ) {
			return $result;
		}

		self::remove_excess_cols( compact( 'i', 'max_columns', 'form_id' ), $result );

		return $result;
	}

	/**
	 * @since 2.05.07
	 */
	private static function user_hidden_columns_for_form( $form_id, $result ) {
		$hidden = array();
		foreach ( (array) $result as $r ) {
			if ( ! empty( $r ) ) {
				list( $form_prefix, $field_key ) = explode( '_', $r );

				if ( (int) $form_prefix === (int) $form_id ) {
					$hidden[] = $r;
				}

				unset( $form_prefix );
			}
		}

		return $hidden;
	}

	/**
	 * Remove some columns by default when there are too many
	 *
	 * @since 2.05.07
	 */
	private static function remove_excess_cols( $atts, &$result ) {
		global $frm_vars;

		$remove_first = array(
			$atts['form_id'] . '_item_key' => '',
			$atts['form_id'] . '_id'       => '',
		);
		$cols         = $remove_first + array_reverse( $frm_vars['cols'], true );

		$i = $atts['i'];

		foreach ( $cols as $col_key => $col ) {
			if ( $i <= $atts['max_columns'] ) {
				break;
			}

			if ( empty( $result ) || ! in_array( $col_key, $result, true ) ) {
				$result[] = $col_key;
				--$i;
			}

			unset( $col_key, $col );
		}
	}

	public static function display_list( $message = '', $errors = array() ) {
		global $wpdb, $frm_vars;

		$form   = FrmForm::maybe_get_current_form();
		$params = FrmForm::get_admin_params( $form );

		if ( $form ) {
			$params['form']           = $form->id;
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
				FrmAppHelper::js_redirect( $url, true );
			} else {
				wp_redirect( esc_url_raw( $url ) );
			}
			die();
		}

		if ( empty( $message ) && isset( $_GET['import-message'] ) ) {
			$message = __( 'Your import is complete', 'formidable' );
		}

		require FrmAppHelper::plugin_path() . '/classes/views/frm-entries/list.php';
	}

	private static function get_delete_form_time( $form, &$errors ) {
		if ( 'trash' === $form->status ) {
			$delete_timestamp = time() - ( DAY_IN_SECONDS * EMPTY_TRASH_DAYS );
			$time_to_delete   = FrmAppHelper::human_time_diff( $delete_timestamp, ( isset( $form->options['trash_time'] ) ? $form->options['trash_time'] : time() ) );

			/* translators: %1$s: Time string */
			$errors['trash'] = sprintf( __( 'This form is in the trash and is scheduled to be deleted permanently in %s along with any entries.', 'formidable' ), $time_to_delete );
		}
	}

	/**
	 * Back End CRUD.
	 */
	public static function show( $id = 0 ) {
		FrmAppHelper::permission_check( 'frm_view_entries' );

		if ( ! $id ) {
			$id = FrmAppHelper::get_param( 'id', 0, 'get', 'absint' );

			if ( ! $id ) {
				$id = FrmAppHelper::get_param( 'item_id', 0, 'get', 'absint' );
			}
		}

		$entry = FrmEntry::getOne( $id, true );
		if ( ! $entry ) {
			echo '<div id="form_show_entry_page" class="wrap">' .
				esc_html__( 'You are trying to view an entry that does not exist.', 'formidable' ) .
				'</div>';

			return;
		}

		$data = $entry->description;
		if ( ! is_array( $data ) || ! isset( $data['referrer'] ) ) {
			$data = array( 'referrer' => $data );
		}

		$fields = FrmField::get_all_for_form( $entry->form_id, '', 'include' );
		$form   = FrmForm::getOne( $entry->form_id );

		include FrmAppHelper::plugin_path() . '/classes/views/frm-entries/show.php';
	}

	/**
	 * Destroy an entry from the admin page.
	 * This is triggered from the entries list from the "Delete" row action, and also from the "Delete Entry" trigger in the view/edit entry sidebar.
	 *
	 * @return void
	 */
	public static function destroy() {
		$permission_error = FrmAppHelper::permission_nonce_error( 'frm_delete_entries', '_wpnonce', -1 );
		if ( false !== $permission_error ) {
			$error_args = array(
				'title'      => __( 'Verification failed', 'formidable' ),
				'body'       => $permission_error,
				'cancel_url' => admin_url( 'admin.php?page=formidable-entries' ),
			);
			FrmAppController::show_error_modal( $error_args );
			return;
		}

		$params = FrmForm::get_admin_params();

		if ( isset( $params['keep_post'] ) && $params['keep_post'] ) {
			self::unlink_post( $params['id'] );
		}

		$message = '';
		if ( FrmEntry::destroy( $params['id'] ) ) {
			$message = __( 'Entry was successfully deleted', 'formidable' );
		}

		self::display_list( $message );
	}

	public static function process_entry( $errors = '', $ajax = false ) {
		$form_id = FrmAppHelper::get_post_param( 'form_id', '', 'absint' );
		if ( FrmAppHelper::is_admin() || empty( $_POST ) || empty( $form_id ) || ! isset( $_POST['item_key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
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
			$errors = FrmEntryValidate::validate( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		/**
		 * Use this filter to add trigger actions and add errors after
		 * all other errors have been processed
		 *
		 * @since 2.0.6
		 */
		$errors = apply_filters( 'frm_entries_before_create', $errors, $form );

		$frm_vars['created_entries'][ $form_id ] = array( 'errors' => $errors );

		if ( empty( $errors ) ) {
			$_POST['frm_skip_cookie'] = 1;
			$do_success               = false;
			if ( $params['action'] === 'create' ) {
				if ( apply_filters( 'frm_continue_to_create', true, $form_id ) && ! isset( $frm_vars['created_entries'][ $form_id ]['entry_id'] ) ) {
					$frm_vars['created_entries'][ $form_id ]['entry_id'] = FrmEntry::create( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

					$params['id'] = $frm_vars['created_entries'][ $form_id ]['entry_id'];
					$do_success   = true;
				}
			}

			do_action( 'frm_process_entry', $params, $errors, $form, array( 'ajax' => $ajax ) );
			if ( $do_success ) {
				FrmFormsController::maybe_trigger_redirect( $form, $params, array( 'ajax' => $ajax ) );
			}
			unset( $_POST['frm_skip_cookie'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}
	}

	/**
	 * Escape url entities before redirect
	 *
	 * @since 3.0
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	public static function prepare_redirect_url( $url ) {
		return str_replace( array( ' ', '[', ']', '|', '@' ), array( '%20', '%5B', '%5D', '%7C', '%40' ), $url );
	}

	public static function delete_entry_before_redirect( $url, $form, $atts ) {
		self::_delete_entry( $atts['id'], $form );

		return $url;
	}

	/**
	 * Delete entry if not redirected.
	 *
	 * @param array $atts
	 */
	public static function delete_entry_after_save( $atts ) {
		self::_delete_entry( $atts['entry_id'], $atts['form'] );
	}

	private static function _delete_entry( $entry_id, $form ) {
		if ( ! $form ) {
			return;
		}

		FrmAppHelper::unserialize_or_decode( $form->options );
		if ( isset( $form->options['no_save'] ) && $form->options['no_save'] ) {
			self::unlink_post( $entry_id );
			FrmEntry::destroy( $entry_id );
		}
	}

	/**
	 * Unlink entry from post
	 */
	private static function unlink_post( $entry_id ) {
		global $wpdb;
		$wpdb->update( $wpdb->prefix . 'frm_items', array( 'post_id' => '' ), array( 'id' => $entry_id ) );
		FrmEntry::clear_cache();
	}

	/**
	 * @param array $atts
	 *
	 * @return array|string
	 */
	public static function show_entry_shortcode( $atts ) {
		$defaults = array(
			'id'              => false,
			'entry'           => false,
			'fields'          => false,
			'plain_text'      => false,
			'user_info'       => false,
			'include_blank'   => false,
			'default_email'   => false,
			'form_id'         => false,
			'format'          => 'text',
			'array_key'       => 'key',
			'direction'       => 'ltr',
			'font_size'       => '',
			'text_color'      => '',
			'border_width'    => '',
			'border_color'    => '',
			'bg_color'        => '',
			'alt_bg_color'    => '',
			'class'           => '',
			'clickable'       => false,
			'exclude_fields'  => '',
			'include_fields'  => '',
			'include_extras'  => '',
			'inline_style'    => 1,
			// Return embedded fields as nested array.
			'child_array'     => false,
			'line_breaks'     => true,
			'array_separator' => ', ',
		);
		$defaults = apply_filters( 'frm_show_entry_defaults', $defaults );

		$atts = shortcode_atts( $defaults, $atts );

		if ( $atts['default_email'] ) {
			$shortcode_atts = array(
				'format'     => $atts['format'],
				'plain_text' => $atts['plain_text'],
			);

			$entry_formatter = FrmEntryFactory::entry_shortcode_formatter_instance( $atts['form_id'], $shortcode_atts );
			$formatted_entry = $entry_formatter->content();

		} else {

			$entry_formatter = FrmEntryFactory::entry_formatter_instance( $atts );
			$formatted_entry = $entry_formatter->get_formatted_entry_values();

		}

		return $formatted_entry;
	}

	public static function entry_sidebar( $entry = false ) {
		$data = array();
		$id   = 0;

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		if ( $entry ) {
			$id   = $entry->id;
			$data = $entry->description;
			if ( isset( $data['browser'] ) ) {
				$browser = FrmEntriesHelper::get_browser( $data['browser'] );
			}
			/**
			 * Add or remove information in the entry sidebar.
			 *
			 * @since 5.5.2
			 * @param array $data
			 */
			$data = apply_filters( 'frm_sidebar_data', $data, compact( 'entry' ) );
		}

		include FrmAppHelper::plugin_path() . '/classes/views/frm-entries/sidebar-shared.php';
	}
}
