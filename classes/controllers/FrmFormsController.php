<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

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
		if ( ! FrmAppHelper::on_form_listing_page() ) {
			return;
		}

		add_filter( 'get_user_option_managetoplevel_page_formidablecolumnshidden', 'FrmFormsController::hidden_columns' );

		add_filter( 'manage_toplevel_page_formidable_columns', 'FrmFormsController::get_columns', 0 );
		add_filter( 'manage_toplevel_page_formidable_sortable_columns', 'FrmFormsController::get_sortable_columns' );
	}

	public static function head() {
		if ( wp_is_mobile() ) {
			wp_enqueue_script( 'jquery-touch-punch' );
		}
	}

	public static function register_widgets() {
		require_once FrmAppHelper::plugin_path() . '/classes/widgets/FrmShowForm.php';
		register_widget( 'FrmShowForm' );
	}

	/**
	 * Show a message about conditional logic
	 *
	 * @since 4.06.03
	 */
	public static function logic_tip() {
		$images_url    = FrmAppHelper::plugin_url() . '/images/';
		$data_message  = __( 'Only show the fields you need and create branching forms. Upgrade to get conditional logic and question branching.', 'formidable' );
		$data_message .= ' <img src="' . esc_attr( $images_url ) . '/survey-logic.png" srcset="' . esc_attr( $images_url ) . 'survey-logic@2x.png 2x" alt="' . esc_attr__( 'Conditional Logic options', 'formidable' ) . '"/>';
		echo '<a href="javascript:void(0)" class="frm_noallow frm_show_upgrade frm_add_logic_link" data-upgrade="' . esc_attr__( 'Conditional Logic options', 'formidable' ) . '" data-message="' . esc_attr( $data_message ) . '" data-medium="builder" data-content="logic">';
		FrmAppHelper::icon_by_class( 'frmfont frm_swap_icon' );
		esc_html_e( 'Add Conditional Logic', 'formidable' );
		echo '</a>';
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

	/**
	 * @return void
	 */
	public static function list_form() {
		FrmAppHelper::permission_check( 'frm_view_forms' );

		$message = '';
		$params  = FrmForm::list_page_params();
		$errors  = self::process_bulk_form_actions( array() );
		if ( isset( $errors['message'] ) ) {
			$message = $errors['message'];
			unset( $errors['message'] );
		}
		$errors = apply_filters( 'frm_admin_list_form_action', $errors );

		self::display_forms_list( $params, $message, $errors );
	}

	/**
	 * Create the default email action
	 *
	 * @since 2.02.11
	 *
	 * @param object|int $form
	 */
	private static function create_default_email_action( $form ) {
		FrmForm::maybe_get_form( $form );
		$create_email = apply_filters( 'frm_create_default_email_action', true, $form );

		if ( $create_email ) {
			$action_control = FrmFormActionsController::get_form_actions( 'email' );
			$action_control->create( $form->id );
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

		$errors = FrmForm::validate( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$warnings = FrmFormsHelper::check_for_warnings( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( count( $errors ) > 0 ) {
			return self::get_settings_vars( $id, $errors, compact( 'warnings' ) );
		}

		do_action( 'frm_before_update_form_settings', $id );

		$antispam_was_on = self::antispam_was_on( $id );

		FrmForm::update( $id, $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$antispam_is_on = ! empty( $_POST['options']['antispam'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( $antispam_is_on !== $antispam_was_on ) {
			FrmAntiSpam::clear_caches();
		}

		$message = __( 'Settings Successfully Updated', 'formidable' );

		return self::get_settings_vars( $id, array(), compact( 'message', 'warnings' ) );
	}

	/**
	 * @param int $form_id
	 * @return bool
	 */
	private static function antispam_was_on( $form_id ) {
		$form = FrmForm::getOne( $form_id );
		return ! empty( $form->options['antispam'] );
	}

	public static function update( $values = array() ) {
		if ( empty( $values ) ) {
			$values = $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		// Set radio button and checkbox meta equal to "other" value.
		if ( FrmAppHelper::pro_is_installed() ) {
			$values = FrmProEntry::mod_other_vals( $values, 'back' );
		}

		$errors           = FrmForm::validate( $values );
		$permission_error = FrmAppHelper::permission_nonce_error( 'frm_edit_forms', 'frm_save_form', 'frm_save_form_nonce' );
		if ( $permission_error !== false ) {
			$errors['form'] = $permission_error;
		}

		$id = isset( $values['id'] ) ? absint( $values['id'] ) : FrmAppHelper::get_param( 'id', '', 'get', 'absint' );

		if ( count( $errors ) > 0 ) {
			return self::get_edit_vars( $id, $errors );
		} else {
			FrmForm::update( $id, $values );
			$message = __( 'Form was successfully updated.', 'formidable' );

			if ( self::is_too_long( $values ) ) {
				$message .= '<br/> ' . sprintf(
					/* translators: %1$s: Start link HTML, %2$s: end link HTML */
					__( 'However, your form is very long and may be %1$sreaching server limits%2$s.', 'formidable' ),
					'<a href="https://formidableforms.com/knowledgebase/i-have-a-long-form-why-did-the-options-at-the-end-of-the-form-stop-saving/?utm_source=WordPress&utm_medium=builder&utm_campaign=liteplugin" target="_blank" rel="noopener">',
					'</a>'
				);
			}

			if ( defined( 'DOING_AJAX' ) ) {
				wp_die( FrmAppHelper::kses( $message, array( 'a' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			return self::get_edit_vars( $id, array(), $message );
		}
	}

	/**
	 * Check if the value at the end of the form was included.
	 * If it's missing, it means other values at the end of the form
	 * were likely not saved either.
	 *
	 * @since 3.06.01
	 */
	private static function is_too_long( $values ) {
		return ( ! isset( $values['frm_end'] ) ) || empty( $values['frm_end'] );
	}

	/**
	 * Redirect to the url for creating from a template
	 * Also delete the current form
	 *
	 * @since 2.0
	 * @deprecated 3.06
	 */
	public static function _create_from_template() {
		_deprecated_function( __FUNCTION__, '3.06' );

		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$current_form = FrmAppHelper::get_param( 'this_form', '', 'get', 'absint' );
		$template_id  = FrmAppHelper::get_param( 'id', '', 'get', 'absint' );

		if ( $current_form ) {
			FrmForm::destroy( $current_form );
		}

		echo esc_url_raw( admin_url( 'admin.php?page=formidable&frm_action=duplicate&id=' . absint( $template_id ) ) );
		wp_die();
	}

	public static function duplicate() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		$nonce = FrmAppHelper::simple_get( '_wpnonce' );

		if ( ! wp_verify_nonce( $nonce ) ) {
			$frm_settings = FrmAppHelper::get_settings();
			wp_die( esc_html( $frm_settings->admin_permission ) );
		}

		$params  = FrmForm::list_page_params();
		$form    = FrmForm::duplicate( $params['id'], $params['template'], true );
		$url     = admin_url( 'admin.php?page=formidable' );
		$message = 'form_duplicate_error';

		if ( $form ) {
			$url = admin_url( 'admin.php?page=formidable&frm_action=edit&id=' . absint( $form ) );
			$message = 'form_duplicated';
		}

		$url .= '&message=' . $message;

		wp_safe_redirect( $url );
		exit();
	}

	/**
	 * @return string
	 */
	public static function page_preview() {
		$params = FrmForm::list_page_params();
		if ( ! $params['form'] ) {
			return;
		}

		$form = FrmForm::getOne( $params['form'] );
		if ( $form ) {
			return self::show_form( $form->id, '', 'auto', 'auto' );
		}
	}

	/**
	 * @since 3.0
	 */
	public static function show_page_preview() {
		echo self::page_preview(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * @return void
	 */
	public static function preview() {
		do_action( 'frm_wp' );
		FrmAppHelper::set_current_screen_and_hook_suffix();

		global $frm_vars;
		$frm_vars['preview'] = true;

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
	 * Set the page global to any available page (except for the Blog Page).
	 *
	 * @return void
	 */
	private static function set_preview_query() {
		$page_query = array(
			'numberposts' => 1,
			'orderby'     => 'date',
			'order'       => 'ASC',
			'post_type'   => 'page',
		);

		$page_for_posts = get_option( 'page_for_posts' );
		if ( is_numeric( $page_for_posts ) ) {
			// Avoid querying for the "Posts Page" or "Blog Page" so we don't display 10 forms.
			$page_query['post__not_in'] = array( $page_for_posts );
		}

		$random_page = get_posts( $page_query );
		if ( ! $random_page ) {
			return;
		}

		$random_page = reset( $random_page );
		query_posts(
			array(
				'post_type' => 'page',
				'page_id'   => $random_page->ID,
			)
		);

		// Fixes Pro issue #3004. Prevent an undefined $post object.
		// Otherwise WordPress themes will trigger a warning "Attempt to read property "comment_count" on null".
		self::set_post_global( $random_page );
	}

	/**
	 * Set the WP $post global object. Used for in-theme preview when defining a page.
	 *
	 * @since 5.5.2
	 *
	 * @param WP_Post $post
	 * @return void
	 */
	private static function set_post_global( $page ) {
		global $post;
		$post = $page; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
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
		FrmStylesController::enqueue_css( 'enqueue', true );

		if ( false === get_template_part( 'page' ) ) {
			self::fallback_when_page_template_part_is_not_supported_by_theme();
		}
	}

	/**
	 * Not every theme supports get_template_part( 'page' ).
	 * When this is not supported, false is returned, and we can handle a fallback.
	 */
	private static function fallback_when_page_template_part_is_not_supported_by_theme() {
		if ( have_posts() ) {
			the_post();
			get_header( '' );
			// add some generic class names to the container to add some natural padding to the content.
			// .entry-content catches the WordPress TwentyTwenty theme.
			// .container catches Customizr content.
			echo '<div class="container entry-content">';
			the_content();
			echo '</div>';
			get_footer();
		}
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
			$content = self::show_page_preview();
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

		/* translators: %1$s: Number of forms */
		$message = sprintf( _n( '%1$s form restored from the Trash.', '%1$s forms restored from the Trash.', $count, 'formidable' ), 1 );

		return $message;
	}

	/**
	 * @since 3.06
	 */
	public static function ajax_trash() {
		FrmAppHelper::permission_check( 'frm_delete_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );
		$form_id = FrmAppHelper::get_param( 'id', '', 'post', 'absint' );
		FrmForm::set_status( $form_id, 'trash' );
		wp_die();
	}

	public static function trash() {
		self::change_form_status( 'trash' );
	}

	/**
	 * @param string $status
	 *
	 * @return void
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
			$count ++;
		}

		$form_type = FrmAppHelper::get_simple_request(
			array(
				'param' => 'form_type',
				'type'  => 'request',
			)
		);

		/* translators: %1$s: Number of forms */
		$available_status['untrash']['message'] = sprintf( _n( '%1$s form restored from the Trash.', '%1$s forms restored from the Trash.', $count, 'formidable' ), $count );

		/* translators: %1$s: Number of forms, %2$s: Start link HTML, %3$s: End link HTML */
		$available_status['trash']['message']   = sprintf( _n( '%1$s form moved to the Trash. %2$sUndo%3$s', '%1$s forms moved to the Trash. %2$sUndo%3$s', $count, 'formidable' ), $count, '<a href="' . esc_url( wp_nonce_url( '?page=formidable&frm_action=untrash&form_type=' . $form_type . '&id=' . $params['id'], 'untrash_form_' . $params['id'] ) ) . '">', '</a>' );

		$message = $available_status[ $status ]['message'];

		self::display_forms_list( $params, $message );
	}

	public static function bulk_trash( $ids ) {
		FrmAppHelper::permission_check( 'frm_delete_forms' );

		$count = 0;
		foreach ( $ids as $id ) {
			if ( FrmForm::trash( $id ) ) {
				$count ++;
			}
		}

		$current_page = FrmAppHelper::get_simple_request(
			array(
				'param' => 'form_type',
				'type'  => 'request',
			)
		);
		$message      = sprintf(
			/* translators: %1$s: Number of forms, %2$s: Start link HTML, %3$s: End link HTML */
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

		// Check nonce url.
		check_admin_referer( 'destroy_form_' . $params['id'] );

		$count = 0;
		if ( FrmForm::destroy( $params['id'] ) ) {
			$count ++;
		}

		/* translators: %1$s: Number of forms */
		$message = sprintf( _n( '%1$s Form Permanently Deleted', '%1$s Forms Permanently Deleted', $count, 'formidable' ), $count );

		self::display_forms_list( $params, $message );
	}

	public static function bulk_destroy( $ids ) {
		FrmAppHelper::permission_check( 'frm_delete_forms' );

		$count = 0;
		foreach ( $ids as $id ) {
			$d = FrmForm::destroy( $id );
			if ( $d ) {
				$count ++;
			}
		}

		/* translators: %1$s: Number of forms */
		$message = sprintf( _n( '%1$s form permanently deleted.', '%1$s forms permanently deleted.', $count, 'formidable' ), $count );

		return $message;
	}

	private static function delete_all() {
		// Check nonce url.
		$permission_error = FrmAppHelper::permission_nonce_error( 'frm_delete_forms', '_wpnonce', 'bulk-toplevel_page_formidable' );
		if ( $permission_error !== false ) {
			self::display_forms_list( array(), '', array( $permission_error ) );

			return;
		}

		$count   = FrmForm::scheduled_delete( time() );

		/* translators: %1$s: Number of forms */
		$message = sprintf( _n( '%1$s form permanently deleted.', '%1$s forms permanently deleted.', $count, 'formidable' ), $count );

		self::display_forms_list( array(), $message );
	}

	/**
	 * Create a new form from the modal.
	 *
	 * @since 4.0
	 */
	public static function build_new_form() {
		global $wpdb;

		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$new_values             = self::get_modal_values();
		$new_values['form_key'] = $new_values['name'];
		$new_values['options']  = array(
			'antispam' => 1,
		);

		/**
		 * Allows changing form values before creating from the modal.
		 *
		 * @since 5.4
		 *
		 * @param array $values Form values.
		 */
		$new_values = apply_filters( 'frm_new_form_values', $new_values );

		$form_id = FrmForm::create( $new_values );
		/**
		 * @since 5.3
		 *
		 * @param int $form_id
		 */
		do_action( 'frm_build_new_form', $form_id );

		self::create_default_email_action( $form_id );

		$response = array(
			'redirect' => FrmForm::get_edit_link( $form_id ),
		);

		echo wp_json_encode( $response );
		wp_die();
	}

	/**
	 * Create a custom template from a form
	 *
	 * @since 3.06
	 */
	public static function build_template() {
		global $wpdb;

		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$form_id     = FrmAppHelper::get_param( 'xml', '', 'post', 'absint' );
		$new_form_id = FrmForm::duplicate( $form_id, 1, true );
		if ( ! $new_form_id ) {
			$response = array(
				'message' => __( 'There was an error creating a template.', 'formidable' ),
			);
		} else {
			$new_values    = self::get_modal_values();
			$query_results = $wpdb->update( $wpdb->prefix . 'frm_forms', $new_values, array( 'id' => $new_form_id ) );
			if ( $query_results ) {
				FrmForm::clear_form_cache();
			}

			$response = array(
				'redirect' => admin_url( 'admin.php?page=formidable&frm_action=duplicate&id=' . $new_form_id ) . '&_wpnonce=' . wp_create_nonce(),
			);
		}

		echo wp_json_encode( $response );
		wp_die();
	}

	/**
	 * Before creating a new form, get the name and description from the modal.
	 *
	 * @since 4.0
	 */
	private static function get_modal_values() {
		$name = FrmAppHelper::get_param( 'name', '', 'post', 'sanitize_text_field' );
		$desc = FrmAppHelper::get_param( 'desc', '', 'post', 'sanitize_textarea_field' );

		return array(
			'name'        => $name,
			'description' => $desc,
		);
	}

	/**
	 * Inserts Formidable button
	 * Hook exists since 2.5.0
	 *
	 * @since 2.0.15
	 */
	public static function insert_form_button() {
		if ( current_user_can( 'frm_view_forms' ) ) {
			FrmAppHelper::load_admin_wide_js();
			$menu_name = FrmAppHelper::get_menu_name();
			$icon      = apply_filters( 'frm_media_icon', FrmAppHelper::svg_logo() );
			echo '<a href="#TB_inline?width=50&height=50&inlineId=frm_insert_form" class="thickbox button add_media frm_insert_form" title="' . esc_attr__( 'Add forms and content', 'formidable' ) . '">' .
				FrmAppHelper::kses( $icon, 'all' ) . // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				' ' . esc_html( $menu_name ) . '</a>';
		}
	}

	/**
	 * @return void
	 */
	public static function insert_form_popup() {
		if ( ! self::should_insert_form_popup() ) {
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

		include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/insert_form_popup.php';

		if ( FrmAppHelper::is_form_builder_page() && ! class_exists( '_WP_Editors', false ) ) {
			// initialize a wysiwyg so we have usable settings defined in tinyMCEPreInit.mceInit
			require ABSPATH . WPINC . '/class-wp-editor.php';
			?>
			<div class="frm_hidden">
				<?php wp_editor( '', 'frm_description_placeholder', array() ); ?>
			</div>
			<?php
		}
	}

	/**
	 * Check the page being loaded, determine if this is a page that should include the form popup.
	 *
	 * @since 5.0.14
	 *
	 * @return bool
	 */
	private static function should_insert_form_popup() {
		if ( FrmAppHelper::is_form_builder_page() ) {
			return true;
		}
		$page = basename( FrmAppHelper::get_server_value( 'PHP_SELF' ) );
		return in_array( $page, array( 'post.php', 'page.php', 'page-new.php', 'post-new.php' ), true );
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
		$opts    = array();
		switch ( $shortcode ) {
			case 'formidable':
				$opts = array(
					'form_id'     => 'id',
					'title'       => array(
						'val'   => 1,
						'label' => __( 'Display form title', 'formidable' ),
					),
					'description' => array(
						'val'   => 1,
						'label' => __( 'Display form description', 'formidable' ),
					),
					'minimize'    => array(
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

	/**
	 * Display list of forms in a table.
	 *
	 * @param array  $params
	 * @param string $message
	 * @param array  $errors
	 * @return void
	 */
	public static function display_forms_list( $params = array(), $message = '', $errors = array() ) {
		FrmAppHelper::permission_check( 'frm_view_forms' );

		global $wpdb, $frm_vars;

		if ( ! $params ) {
			$params = FrmForm::list_page_params();
		}

		/**
		 * @since 5.3.1
		 *
		 * @param string $table_class Class name for List Helper.
		 */
		$table_class   = apply_filters( 'frm_forms_list_class', 'FrmFormsListHelper' );
		$wp_list_table = new $table_class( compact( 'params' ) );

		$pagenum = $wp_list_table->get_pagenum();

		$wp_list_table->prepare_items();

		$total_pages = $wp_list_table->get_pagination_arg( 'total_pages' );
		if ( $pagenum > $total_pages && $total_pages > 0 ) {
			wp_redirect( esc_url_raw( add_query_arg( 'paged', $total_pages ) ) );
			die();
		}

		require FrmAppHelper::plugin_path() . '/classes/views/frm-forms/list.php';
	}

	/**
	 * @param array<string,string> $columns
	 * @return array<string,string>
	 */
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

		if ( 'template' === $type ) {
			$columns['name']     = __( 'Template Name', 'formidable' );
			$columns['type']     = __( 'Type', 'formidable' );
			$columns['form_key'] = __( 'Key', 'formidable' );
		} else {
			$columns['name']      = __( 'Form Title', 'formidable' );
			$columns['entries']   = __( 'Entries', 'formidable' );
			$columns['form_key']  = __( 'Key', 'formidable' );
			$columns['shortcode'] = __( 'Actions', 'formidable' );
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
			'id'          => 'id',
			'name'        => 'name',
			'description' => 'description',
			'form_key'    => 'form_key',
			'created_at'  => 'created_at',
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

	/**
	 * @return bool
	 */
	public static function expired() {
		global $frm_expired;
		return $frm_expired;
	}

	/**
	 * Get data from api before rendering it so that we can flag the modal as expired
	 *
	 * @return void
	 */
	public static function before_list_templates() {
		global $frm_templates;
		global $frm_expired;
		global $frm_license_type;

		$api           = new FrmFormTemplateApi();
		$frm_templates = $api->get_api_info();
		$expired       = false;
		$license_type  = '';
		if ( isset( $frm_templates['error'] ) ) {
			$error        = $frm_templates['error']['message'];
			$error        = str_replace( 'utm_medium=addons', 'utm_medium=form-templates', $error );
			$expired      = 'expired' === $frm_templates['error']['code'];
			$license_type = isset( $frm_templates['error']['type'] ) ? $frm_templates['error']['type'] : '';
			unset( $frm_templates['error'] );
		}

		$frm_expired      = $expired;
		$frm_license_type = $license_type;
	}

	/**
	 * @return void
	 */
	public static function list_templates() {
		global $frm_templates;
		global $frm_license_type;

		$templates             = $frm_templates;
		$custom_templates      = array();
		$templates_by_category = array();

		self::add_user_templates( $custom_templates );

		foreach ( $templates as $template ) {
			if ( ! isset( $template['categories'] ) ) {
				continue;
			}

			foreach ( $template['categories'] as $category ) {
				if ( ! isset( $templates_by_category[ $category ] ) ) {
					$templates_by_category[ $category ] = array();
				}

				$templates_by_category[ $category ][] = $template;
			}
		}
		unset( $template );

		// Subcategories that are included elsewhere.
		$redundant_cats = array( 'PayPal', 'Stripe', 'Twilio' );

		$categories = array_keys( $templates_by_category );
		$categories = array_diff( $categories, FrmFormsHelper::ignore_template_categories() );
		$categories = array_diff( $categories, $redundant_cats );
		sort( $categories );

		array_walk(
			$custom_templates,
			function( &$template ) {
				$template['custom'] = true;
			}
		);

		$my_templates_translation = __( 'My Templates', 'formidable' );
		$categories               = array_merge( array( $my_templates_translation ), $categories );
		$pricing                  = FrmAppHelper::admin_upgrade_link( 'form-templates' );
		$license_type             = $frm_license_type;
		$args                     = compact( 'pricing', 'license_type' );
		$where                    = apply_filters( 'frm_forms_dropdown', array(), '' );
		$forms                    = FrmForm::get_published_forms( $where );
		$view_path                = FrmAppHelper::plugin_path() . '/classes/views/frm-forms/';

		$templates_by_category[ $my_templates_translation ] = $custom_templates;

		unset( $pricing, $license_type, $where );
		wp_enqueue_script( 'accordion' ); // register accordion for template groups
		require $view_path . 'list-templates.php';
	}

	/**
	 * @since 4.03.01
	 */
	private static function get_template_categories( $templates ) {
		$categories = array();
		foreach ( $templates as $template ) {
			if ( isset( $template['categories'] ) ) {
				$categories = array_merge( $categories, $template['categories'] );
			}
		}
		$exclude_cats = FrmFormsHelper::ignore_template_categories();
		$categories = array_unique( $categories );
		$categories = array_diff( $categories, $exclude_cats );
		sort( $categories );
		return $categories;
	}

	private static function add_user_templates( &$templates ) {
		$user_templates = array(
			'is_template'      => 1,
			'default_template' => 0,
		);
		$user_templates = FrmForm::getAll( $user_templates, 'name' );
		foreach ( $user_templates as $template ) {
			$template = array(
				'id'          => $template->id,
				'name'        => $template->name,
				'key'         => $template->form_key,
				'description' => $template->description,
				'url'         => wp_nonce_url( admin_url( 'admin.php?page=formidable&frm_action=duplicate&id=' . absint( $template->id ) ) ),
				'released'    => $template->created_at,
				'installed'   => 1,
			);
			array_unshift( $templates, $template );
			unset( $template );
		}
	}

	private static function get_edit_vars( $id, $errors = array(), $message = '', $create_link = false ) {
		global $frm_vars;

		$form = FrmForm::getOne( $id );
		if ( ! $form ) {
			wp_die( esc_html__( 'You are trying to edit a form that does not exist.', 'formidable' ) );
		}

		if ( $form->parent_form_id ) {
			/* translators: %1$s: Start link HTML, %2$s: End link HTML */
			wp_die( sprintf( esc_html__( 'You are trying to edit a child form. Please edit from %1$shere%2$s', 'formidable' ), '<a href="' . esc_url( FrmForm::get_edit_link( $form->parent_form_id ) ) . '">', '</a>' ) );
		}

		$frm_field_selection = FrmField::field_selection();

		$fields = FrmField::get_all_for_form( $form->id );

		// Automatically add end section fields if they don't exist (2.0 migration).
		$reset_fields = false;
		FrmFormsHelper::auto_add_end_section_fields( $form, $fields, $reset_fields );

		if ( $reset_fields ) {
			$fields = FrmField::get_all_for_form( $form->id, '', 'exclude' );
		}

		unset( $reset_fields );

		$args   = array( 'parent_form_id' => $form->id );
		$values = FrmAppHelper::setup_edit_vars( $form, 'forms', '', true, array(), $args );

		/**
		 * Allows modifying the list of fields in the form builder.
		 *
		 * @since 5.0.04
		 *
		 * @param object[] $fields Array of fields.
		 * @param array    $args   The arguments. Contains `form`.
		 */
		$values['fields'] = apply_filters( 'frm_fields_in_form_builder', $fields, compact( 'form' ) );

		$edit_message = __( 'Form was successfully updated.', 'formidable' );
		if ( $form->is_template && $message == $edit_message ) {
			$message = __( 'Template was successfully updated.', 'formidable' );
		}

		self::maybe_update_form_builder_message( $message );

		$all_templates = FrmForm::getAll( array( 'is_template' => 1 ), 'name' );
		$has_fields    = isset( $values['fields'] ) && ! empty( $values['fields'] );

		if ( defined( 'DOING_AJAX' ) ) {
			wp_die();
		} else {
			require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/edit.php' );
		}
	}

	public static function update_form_builder_fields( $fields, $form ) {
		foreach ( $fields as $field ) {
			$field->do_not_include_icons = true;
		}
		return $fields;
	}

	public static function maybe_update_form_builder_message( &$message ) {
		if ( 'form_duplicated' === FrmAppHelper::simple_get( 'message' ) ) {
			$message = __( 'Form was Successfully Copied', 'formidable' );
		}
	}

	public static function get_settings_vars( $id, $errors = array(), $args = array() ) {
		FrmAppHelper::permission_check( 'frm_edit_forms' );

		global $frm_vars;

		if ( ! is_array( $args ) ) {
			// For reverse compatibility.
			$args = array(
				'message' => $args,
			);
		}

		$defaults = array(
			'message'  => '',
			'warnings' => array(),
		);
		$args     = array_merge( $defaults, $args );
		$message  = $args['message'];
		$warnings = $args['warnings'];

		$form   = FrmForm::getOne( $id );
		$fields = FrmField::get_all_for_form( $id );
		$values = FrmAppHelper::setup_edit_vars( $form, 'forms', $fields, true );

		/**
		 * Allows changing fields in the form settings.
		 *
		 * @since 5.0.04
		 *
		 * @param array $fields Array of fields.
		 * @param array $args   The arguments. Contains `form`.
		 */
		$values['fields'] = apply_filters( 'frm_fields_in_settings', $values['fields'], compact( 'form' ) );

		self::clean_submit_html( $values );

		$sections = self::get_settings_tabs( $values );
		$current  = FrmAppHelper::simple_get( 't', 'sanitize_title', 'advanced_settings' );

		require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/settings.php' );
	}

	/**
	 * @since 4.0
	 */
	public static function form_publish_button( $atts ) {
		$values = $atts['values'];
		include( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/_publish_box.php' );
	}

	/**
	 * Get a list of all the settings tabs for the form settings page.
	 *
	 * @since 4.0
	 *
	 * @param array $values
	 * @return array
	 */
	private static function get_settings_tabs( $values ) {
		$sections = array(
			'advanced'    => array(
				'name'     => __( 'General', 'formidable' ),
				'title'    => __( 'General Form Settings', 'formidable' ),
				'function' => array( __CLASS__, 'advanced_settings' ),
				'icon'     => 'frm_icon_font frm_settings_icon',
			),
			'email'       => array(
				'name'     => __( 'Actions & Notifications', 'formidable' ),
				'function' => array( 'FrmFormActionsController', 'email_settings' ),
				'id'       => 'frm_notification_settings',
				'icon'     => 'frm_icon_font frm_mail_bulk_icon',
			),
			'permissions' => array(
				'name'       => __( 'Form Permissions', 'formidable' ),
				'icon'       => 'frm_icon_font frm_lock_icon',
				'html_class' => 'frm_show_upgrade_tab frm_noallow',
				'data'       => array(
					'medium'     => 'permissions',
					'upgrade'    => __( 'Form Permissions', 'formidable' ),
					'message'    => __( 'Allow editing, protect forms and files, limit entries, and save drafts. Upgrade to get form and entry permissions.', 'formidable' ),
					'screenshot' => 'permissions.png',
				),
			),
			'scheduling' => array(
				'name'       => __( 'Form Scheduling', 'formidable' ),
				'icon'       => 'frm_icon_font frm_calendar_icon',
				'html_class' => 'frm_show_upgrade_tab frm_noallow',
				'data'       => array(
					'medium'     => 'scheduling',
					'upgrade'    => __( 'Form scheduling settings', 'formidable' ),
					'screenshot' => 'scheduling.png',
				),
			),
			'buttons'     => array(
				'name'     => __( 'Styling & Buttons', 'formidable' ),
				'class'    => __CLASS__,
				'function' => 'buttons_settings',
				'icon'     => 'frm_icon_font frm_pallet_icon',
			),
			'landing'     => array(
				'name'       => __( 'Form Landing Page', 'formidable' ),
				'icon'       => 'frm_icon_font frm_file_text_icon',
				'html_class' => 'frm_show_upgrade_tab frm_noallow',
				'data'       => FrmAppHelper::get_landing_page_upgrade_data_params(),
			),
			'chat'        => array(
				'name'       => __( 'Conversational Forms', 'formidable' ),
				'icon'       => 'frm_icon_font frm_chat_forms_icon',
				'html_class' => 'frm_show_upgrade_tab frm_noallow',
				'data'       => FrmAppHelper::get_upgrade_data_params(
					'chat',
					array(
						'upgrade'    => __( 'Conversational Forms', 'formidable' ),
						'message'    => __( 'Ask one question at a time for automated conversations.', 'formidable' ),
						'screenshot' => 'chat.png',
					)
				),
			),
			'html'        => array(
				'name'     => __( 'Customize HTML', 'formidable' ),
				'class'    => __CLASS__,
				'function' => 'html_settings',
				'icon'     => 'frm_icon_font frm_code_icon',
			),
		);

		foreach ( array( 'landing', 'chat' ) as $feature ) {
			if ( ! FrmAppHelper::show_new_feature( $feature ) ) {
				unset( $sections[ $feature ] );
			}
		}

		$sections = apply_filters( 'frm_add_form_settings_section', $sections, $values );

		if ( FrmAppHelper::pro_is_installed() && ! FrmAppHelper::meets_min_pro_version( '4.0' ) ) {
			// Prevent settings from showing in 2 spots.
			unset( $sections['permissions'], $sections['scheduling'] );
		}

		foreach ( $sections as $key => $section ) {
			$defaults = array(
				'html_class' => '',
				'name'       => ucfirst( $key ),
				'icon'       => 'frm_icon_font frm_settings_icon',
			);

			$section = array_merge( $defaults, $section );

			if ( ! isset( $section['anchor'] ) ) {
				$section['anchor'] = $key;
			}
			$section['anchor'] .= '_settings';

			if ( ! isset( $section['title'] ) ) {
				$section['title'] = $section['name'];
			}

			if ( ! isset( $section['id'] ) ) {
				$section['id'] = $section['anchor'];
			}

			$sections[ $key ] = $section;
		}

		return $sections;
	}

	/**
	 * @since 4.0
	 *
	 * @param array $values
	 */
	public static function advanced_settings( $values ) {
		$first_h3 = 'frm_first_h3';

		include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/settings-advanced.php';
	}

	/**
	 * @param array $values
	 */
	private static function render_spam_settings( $values ) {
		if ( function_exists( 'akismet_http_post' ) ) {
			include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/spam-settings/akismet.php';
		}
		include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/spam-settings/honeypot.php';
		include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/spam-settings/antispam.php';
	}

	/**
	 * @since 4.0
	 *
	 * @param array $values
	 */
	public static function buttons_settings( $values ) {
		$styles = apply_filters( 'frm_get_style_opts', array() );

		$frm_settings    = FrmAppHelper::get_settings();
		$no_global_style = $frm_settings->load_style === 'none';

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/settings-buttons.php' );
	}

	/**
	 * @since 4.0
	 *
	 * @param array $values
	 */
	public static function html_settings( $values ) {
		include( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/settings-html.php' );
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

		/**
		 * Allows modifying the list of fields in the tags box.
		 *
		 * @since 5.0.04
		 *
		 * @param array $fields The list of fields.
		 * @param array $args   The arguments. Contains `form_id`.
		 */
		$fields       = apply_filters( 'frm_fields_in_tags_box', $fields, compact( 'form_id' ) );
		$linked_forms = array();
		$col          = 'one';
		$settings_tab = FrmAppHelper::is_admin_page( 'formidable' ) ? true : false;

		$cond_shortcodes  = apply_filters( 'frm_conditional_shortcodes', array() );
		$entry_shortcodes = self::get_shortcode_helpers( $settings_tab );

		$advanced_helpers = self::advanced_helpers( compact( 'fields', 'form_id' ) );

		include( FrmAppHelper::plugin_path() . '/classes/views/shared/mb_adv_info.php' );
	}

	/**
	 * @since 3.04.01
	 */
	private static function advanced_helpers( $atts ) {
		$advanced_helpers = array(
			'default' => array(
				'heading' => __( 'Customize field values with the following parameters.', 'formidable' ),
				'codes'   => self::get_advanced_shortcodes(),
			),
		);

		$user_fields = self::user_shortcodes();
		if ( ! empty( $user_fields ) ) {
			$user_helpers = array();
			foreach ( $user_fields as $uk => $uf ) {
				$user_helpers[ '|user_id| show="' . $uk . '"' ] = $uf;
				unset( $uk, $uf );
			}

			$advanced_helpers['user_id'] = array(
				'codes'   => $user_helpers,
			);
		}

		/**
		 * Add extra helper shortcodes on the Advanced tab in form settings and views
		 *
		 * @since 3.04.01
		 *
		 * @param array $advanced_helpers
		 * @param array $atts - Includes fields and form_id
		 */
		return apply_filters( 'frm_advanced_helpers', $advanced_helpers, $atts );
	}

	/**
	 * Get an array of the options to display in the advanced tab
	 * of the customization panel
	 *
	 * @since 2.0.6
	 */
	private static function get_advanced_shortcodes() {
		$adv_shortcodes = array(
			'x sep=", "'           => array(
				'label' => __( 'Separator', 'formidable' ),
				'title' => __( 'Use a different separator for checkbox fields', 'formidable' ),
			),
			'x format="d-m-Y"'     => array(
				'label' => __( 'Date Format', 'formidable' ),
			),
			'x show="field_label"' => array(
				'label' => __( 'Field Label', 'formidable' ),
			),
			'x wpautop=0'          => array(
				'label' => __( 'No Auto P', 'formidable' ),
				'title' => __( 'Do not automatically add any paragraphs or line breaks', 'formidable' ),
			),
		);
		$adv_shortcodes = apply_filters( 'frm_advanced_shortcodes', $adv_shortcodes );

		// __( 'Leave blank instead of defaulting to User Login', 'formidable' ) : blank=1

		return $adv_shortcodes;
	}

	/**
	 * @since 3.04.01
	 */
	private static function user_shortcodes() {
		$options = array(
			'ID'           => __( 'User ID', 'formidable' ),
			'first_name'   => __( 'First Name', 'formidable' ),
			'last_name'    => __( 'Last Name', 'formidable' ),
			'display_name' => __( 'Display Name', 'formidable' ),
			'user_login'   => __( 'User Login', 'formidable' ),
			'user_email'   => __( 'Email', 'formidable' ),
			'avatar'       => __( 'Avatar', 'formidable' ),
			'author_link'  => __( 'Author Link', 'formidable' ),
		);

		return apply_filters( 'frm_user_shortcodes', $options );
	}

	/**
	 * Get an array of the helper shortcodes to display in the customization panel
	 *
	 * @since 2.0.6
	 */
	private static function get_shortcode_helpers( $settings_tab ) {
		$entry_shortcodes = array(
			'id'         => __( 'Entry ID', 'formidable' ),
			'key'        => __( 'Entry Key', 'formidable' ),
			'post_id'    => __( 'Post ID', 'formidable' ),
			'ip'         => __( 'User IP', 'formidable' ),
			'created-at' => __( 'Entry created', 'formidable' ),
			'updated-at' => __( 'Entry updated', 'formidable' ),
			''           => '',
			'siteurl'    => __( 'Site URL', 'formidable' ),
			'sitename'   => __( 'Site Name', 'formidable' ),
		);

		if ( ! FrmAppHelper::pro_is_installed() ) {
			unset( $entry_shortcodes['post_id'] );
		}

		if ( $settings_tab ) {
			$entry_shortcodes['default-message'] = __( 'Default Msg', 'formidable' );
			$entry_shortcodes['default-html']    = __( 'Default HTML', 'formidable' );
			$entry_shortcodes['default-plain']   = __( 'Default Plain', 'formidable' );
			$entry_shortcodes['form_name']       = __( 'Form Name', 'formidable' );
		}

		/**
		 * Use this hook to add or remove buttons in the helpers section
		 * in the customization panel
		 *
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

		if ( ! empty( $form->options['js_validate'] ) ) {
			echo ' frm_js_validate ';
			self::add_js_validate_form_to_global_vars( $form );
		}
	}

	/**
	 * @since 5.0.03
	 *
	 * @param stdClass $form
	 */
	public static function add_js_validate_form_to_global_vars( $form ) {
		global $frm_vars;
		if ( ! isset( $frm_vars['js_validate_forms'] ) ) {
			$frm_vars['js_validate_forms'] = array();
		}
		$frm_vars['js_validate_forms'][ $form->id ] = $form;
	}

	public static function get_email_html() {
		FrmAppHelper::permission_check( 'frm_view_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		echo FrmEntriesController::show_entry_shortcode( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			array(
				'form_id'       => FrmAppHelper::get_post_param( 'form_id', '', 'absint' ),
				'default_email' => true,
				'plain_text'    => FrmAppHelper::get_post_param( 'plain_text', '', 'absint' ),
			)
		);
		wp_die();
	}

	/**
	 * @param string                    $content
	 * @param stdClass|string|int       $form
	 * @param stdClass|string|int|false $entry
	 * @return string
	 */
	public static function filter_content( $content, $form, $entry = false ) {
		$content = self::replace_form_name_shortcodes( $content, $form );

		self::get_entry_by_param( $entry );
		if ( ! $entry ) {
			return $content;
		}

		if ( is_object( $form ) ) {
			$form = $form->id;
		}

		$shortcodes = FrmFieldsHelper::get_shortcodes( $content, $form );
		$content    = apply_filters( 'frm_replace_content_shortcodes', $content, $entry, $shortcodes );

		return $content;
	}

	/**
	 * Replace any [form_name] shortcodes in a string.
	 *
	 * @since 5.5
	 *
	 * @param string              $string
	 * @param stdClass|string|int $form
	 * @return string
	 */
	public static function replace_form_name_shortcodes( $string, $form ) {
		if ( false === strpos( $string, '[form_name]' ) ) {
			return $string;
		}

		if ( ! is_object( $form ) ) {
			$form = FrmForm::getOne( $form );
		}

		$form_name = is_object( $form ) ? $form->name : '';
		return str_replace( '[form_name]', $form_name, $string );
	}

	/**
	 * @param stdClass|string|int|false $entry
	 * @return void
	 */
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
		if ( $bulkaction == - 1 ) {
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
			$errors['message'] = $message;
		}

		return $errors;
	}

	public static function route() {
		$action = isset( $_REQUEST['frm_action'] ) ? 'frm_action' : 'action';
		$vars   = array();
		FrmAppHelper::include_svg();

		if ( isset( $_POST['frm_compact_fields'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			FrmAppHelper::permission_check( 'frm_edit_forms' );

			// Javascript needs to be allowed in some field settings.
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
			$json_vars = htmlspecialchars_decode( nl2br( str_replace( '&quot;', '"', wp_unslash( $_POST['frm_compact_fields'] ) ) ) );
			$json_vars = json_decode( $json_vars, true );
			if ( empty( $json_vars ) ) {
				// json decoding failed so we should return an error message.
				$action = FrmAppHelper::get_param( $action, '', 'get', 'sanitize_title' );
				if ( 'edit' == $action ) {
					$action = 'update';
				}

				add_filter( 'frm_validate_form', 'FrmFormsController::json_error' );
			} else {
				$vars   = FrmAppHelper::json_to_array( $json_vars );
				$action = $vars[ $action ];
				unset( $_REQUEST['frm_compact_fields'], $_POST['frm_compact_fields'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$_REQUEST = array_merge( $_REQUEST, $vars ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$_POST    = array_merge( $_POST, $_REQUEST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			}
		} else {
			$action = FrmAppHelper::get_param( $action, '', 'get', 'sanitize_title' );
			if ( isset( $_REQUEST['delete_all'] ) ) {
				// Override the action for this page.
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
			case 'trash':
			case 'untrash':
			case 'destroy':
			case 'delete_all':
			case 'settings':
			case 'update_settings':
				return self::$action( $vars );
			case 'lite-reports':
				return self::no_reports( $vars );
			case 'views':
				return self::no_views( $vars );
			default:
				do_action( 'frm_form_action_' . $action );
				if ( apply_filters( 'frm_form_stop_action_' . $action, false ) ) {
					return;
				}

				$action = FrmAppHelper::get_param( 'action', '', 'get', 'sanitize_text_field' );
				if ( $action == - 1 ) {
					$action = FrmAppHelper::get_param( 'action2', '', 'get', 'sanitize_title' );
				}

				if ( strpos( $action, 'bulk_' ) === 0 ) {
					FrmAppHelper::remove_get_action();

					self::list_form();
					return;
				}

				$message = FrmAppHelper::get_param( 'message' );
				if ( 'form_duplicate_error' === $message ) {
					self::display_forms_list( array(), '', array( __( 'There was a problem duplicating the form', 'formidable' ) ) );
					return;
				}

				self::display_forms_list();

				return;
		}
	}

	public static function json_error( $errors ) {
		$errors['json'] = __( 'Abnormal HTML characters prevented your form from saving correctly', 'formidable' );

		return $errors;
	}

	/**
	 * Education for premium features.
	 *
	 * @since 4.05
	 */
	public static function add_form_style_tab_options() {
		include( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/add_form_style_options.php' );
	}

	/**
	 * Add education about views.
	 *
	 * @since 4.07
	 */
	public static function no_views( $values = array() ) {
		FrmAppHelper::include_svg();
		$id   = FrmAppHelper::get_param( 'form', '', 'get', 'absint' );
		$form = $id ? FrmForm::getOne( $id ) : false;

		include FrmAppHelper::plugin_path() . '/classes/views/shared/views-info.php';
	}

	/**
	 * Add education about reports.
	 *
	 * @since 4.07
	 */
	public static function no_reports( $values = array() ) {
		$id   = FrmAppHelper::get_param( 'form', '', 'get', 'absint' );
		$form = $id ? FrmForm::getOne( $id ) : false;

		include FrmAppHelper::plugin_path() . '/classes/views/shared/reports-info.php';
	}

	/* FRONT-END FORMS */
	public static function admin_bar_css() {
		if ( is_admin() || ! current_user_can( 'frm_edit_forms' ) ) {
			return;
		}

		self::move_menu_to_footer();

		add_action( 'wp_before_admin_bar_render', 'FrmFormsController::admin_bar_configure' );
		FrmAppHelper::load_font_style();
	}

	/**
	 * @since 4.05.02
	 */
	private static function move_menu_to_footer() {
		$settings = FrmAppHelper::get_settings();
		if ( empty( $settings->admin_bar ) ) {
			remove_action( 'wp_body_open', 'wp_admin_bar_render', 0 );
		}
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
					'parent' => 'frm-forms',
					'id'     => 'edit_form_' . $form_id,
					'title'  => empty( $name ) ? __( '(no title)', 'formidable' ) : $name,
					'href'   => FrmForm::get_edit_link( $form_id ),
				)
			);
		}
	}

	/**
	 * The formidable shortcode
	 *
	 * @param array $atts The params from the shortcode.
	 * @return string
	 */
	public static function get_form_shortcode( $atts ) {
		global $frm_vars;
		if ( isset( $frm_vars['skip_shortcode'] ) && $frm_vars['skip_shortcode'] ) {
			$sc = '[formidable';
			$sc .= FrmAppHelper::array_to_html_params( $atts );
			return $sc . ']';
		}

		$shortcode_atts = shortcode_atts(
			array(
				'id'             => '',
				'key'            => '',
				'title'          => 'auto',
				'description'    => 'auto',
				'readonly'       => false,
				'entry_id'       => false,
				'fields'         => array(),
				'exclude_fields' => array(),
				'minimize'       => false,
			),
			$atts
		);
		do_action( 'formidable_shortcode_atts', $shortcode_atts, $atts );

		return self::show_form( $shortcode_atts['id'], $shortcode_atts['key'], $shortcode_atts['title'], $shortcode_atts['description'], $atts );
	}

	/**
	 * @since 5.2.01
	 *
	 * @param string|int|false $id
	 * @param string|false     $key
	 * @return stdClass|false
	 */
	private static function maybe_get_form_by_id_or_key( $id, $key ) {
		if ( ! $id ) {
			$id = $key;
		}
		return self::maybe_get_form_to_show( $id );
	}

	/**
	 * @param string|int|false $id
	 * @param string|false     $key
	 * @param string|int|bool  $title may be 'auto', true, false, 'true', 'false', 'yes', '1', 1, '0', 0.
	 * @param string|int|bool  $description may be 'auto', true, false, 'true', 'false', 'yes', '1', 1, '0', 0.
	 * @param array            $atts
	 * @return string
	 */
	public static function show_form( $id = '', $key = '', $title = false, $description = false, $atts = array() ) {
		$form = self::maybe_get_form_by_id_or_key( $id, $key );

		if ( ! $form ) {
			return __( 'Please select a valid form', 'formidable' );
		}

		if ( 'auto' === $title ) {
			$title = ! empty( $form->options['show_title'] );
		}

		if ( 'auto' === $description ) {
			$description = ! empty( $form->options['show_description'] );
		}

		FrmAppController::maybe_update_styles();

		add_action( 'frm_load_form_hooks', 'FrmHooksController::trigger_load_form_hooks' );
		FrmAppHelper::trigger_hook_load( 'form', $form );

		$form = apply_filters( 'frm_pre_display_form', $form );

		$frm_settings = FrmAppHelper::get_settings( array( 'current_form' => $form->id ) );

		if ( self::is_viewable_draft_form( $form ) ) {
			// don't show a draft form on a page
			$form = __( 'Please select a valid form', 'formidable' );
		} elseif ( ! FrmForm::is_visible_to_user( $form ) ) {
			$form = do_shortcode( $frm_settings->login_msg );
		} else {
			do_action( 'frm_pre_get_form', $form );
			$form = self::get_form( $form, $title, $description, $atts );

			/**
			 * Use this shortcode to check for external shortcodes that may span
			 * across multiple fields in the customizable HTML
			 *
			 * @since 2.0.8
			 */
			$form = apply_filters( 'frm_filter_final_form', $form );
		}

		return $form;
	}

	/**
	 * @param string|int|false $id
	 * @return stdClass|false
	 */
	private static function maybe_get_form_to_show( $id ) {
		$form = false;

		if ( ! empty( $id ) ) { // form id or key is set
			$form = FrmForm::getOne( $id );
			if ( ! $form || $form->parent_form_id || $form->status === 'trash' ) {
				$form = false;
			}
		}

		return $form;
	}

	private static function is_viewable_draft_form( $form ) {
		return $form->status === 'draft' && current_user_can( 'frm_edit_forms' ) && ! FrmAppHelper::is_preview_page();
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
		$params    = FrmForm::get_params( $form );
		$errors    = self::get_saved_errors( $form, $params );
		$fields    = FrmFieldsHelper::get_form_fields( $form->id, $errors );
		$reset     = false;
		$pass_args = compact( 'form', 'fields', 'errors', 'title', 'description', 'reset' );

		$handle_process_here = $params['action'] === 'create' && $params['posted_form_id'] == $form->id && $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing

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
				$entry_id                 = self::just_created_entry( $form->id );
				$pass_args['entry_id']    = $entry_id;
				$pass_args['reset']       = true;
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
	 *
	 * @since 2.05
	 */
	private static function get_saved_errors( $form, $params ) {
		global $frm_vars;

		if ( $params['posted_form_id'] == $form->id && $_POST && isset( $frm_vars['created_entries'][ $form->id ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$errors = $frm_vars['created_entries'][ $form->id ]['errors'];
		} else {
			$errors = array();
		}

		/**
		 * Allows modifying the generated errors if the form was processed earlier.
		 *
		 * @since 5.2.03
		 *
		 * @param array $errors Errors data. Is empty array if no errors found.
		 * @param array $params Form params. See {@see FrmForm::get_params()}.
		 */
		return apply_filters( 'frm_saved_errors', $errors, $params );
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
		$opt    = 'success_action';
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
	 *
	 * @since 2.05
	 */
	public static function run_success_action( $args ) {
		$extra_args = $args;
		unset( $extra_args['form'] );

		do_action( 'frm_success_action', $args['conf_method'], $args['form'], $args['form']->options, $args['entry_id'], $extra_args );

		$opt = ( ! isset( $args['action'] ) || $args['action'] === 'create' ) ? 'success' : 'edit';

		$args['success_opt'] = $opt;
		if ( $args['conf_method'] === 'page' && is_numeric( $args['form']->options[ $opt . '_page_id' ] ) ) {
			self::load_page_after_submit( $args );
		} elseif ( $args['conf_method'] === 'redirect' ) {
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
			$page     = get_post( $args['form']->options[ $opt . '_page_id' ] );
			$old_post = $post;
			$post     = $page;
			$content  = apply_filters( 'frm_content', $page->post_content, $args['form'], $args['entry_id'] );
			echo apply_filters( 'the_content', $content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$post = $old_post;
		}
	}

	/**
	 * @since 3.0
	 */
	private static function redirect_after_submit( $args ) {
		global $frm_vars;

		add_filter( 'frm_use_wpautop', '__return_false' );

		$opt         = $args['success_opt'];
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
			echo FrmAppHelper::maybe_kses( $redirect_msg ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo "<script type='text/javascript'>window.onload = function(){setTimeout(window.location='" . esc_url_raw( $success_url ) . "', 8000);}</script>";
		}
	}

	/**
	 * @since 3.0
	 *
	 * @param string $success_url
	 * @param string $success_msg
	 * @param array $args
	 */
	private static function get_redirect_message( $success_url, $success_msg, $args ) {
		$redirect_msg = '<div class="' . esc_attr( FrmFormsHelper::get_form_style_class( $args['form'] ) ) . '"><div class="frm-redirect-msg frm_message" role="status">' . $success_msg . '<br/>' .
			/* translators: %1$s: Start link HTML, %2$s: End link HTML */
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
	 *
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
	 *
	 * @since 2.05
	 */
	private static function show_form_after_submit( $args ) {
		self::fill_atts_for_form_display( $args );

		$errors      = $args['errors'];
		$message     = $args['message'];
		$form        = $args['form'];
		$title       = $args['title'];
		$description = $args['description'];

		if ( empty( $args['fields'] ) ) {
			$values = array(
				'custom_style' => FrmAppHelper::custom_style_value( array() ),
			);
		} else {
			$values = FrmEntriesHelper::setup_new_vars( $args['fields'], $form, $args['reset'] );
		}
		unset( $args );

		$include_form_tag = apply_filters( 'frm_include_form_tag', true, $form );

		$frm_settings = FrmAppHelper::get_settings();
		$submit       = isset( $form->options['submit_value'] ) ? $form->options['submit_value'] : $frm_settings->submit_value;

		global $frm_vars;
		self::maybe_load_css( $form, $values['custom_style'], $frm_vars['load_css'] );

		$message_placement = self::message_placement( $form, $message );

		include FrmAppHelper::plugin_path() . '/classes/views/frm-entries/new.php';
	}

	/**
	 * @return string - 'before', 'after', or 'submit'
	 *
	 * @since 4.05.02
	 */
	private static function message_placement( $form, $message ) {
		$place = 'before';

		if ( $message && isset( $form->options['form_class'] ) ) {
			if ( strpos( $form->options['form_class'], 'frm_below_success' ) !== false ) {
				$place = 'after';
			} elseif ( strpos( $form->options['form_class'], 'frm_inline_success' ) !== false ) {
				$place = 'submit';
			}
		}

		/**
		 * @return string - 'before' or 'after'
		 *
		 * @since 4.05.02
		 */
		return apply_filters( 'frm_message_placement', $place, compact( 'form', 'message' ) );
	}

	/**
	 * Get all the values needed on the new.php entry page
	 *
	 * @since 2.05
	 */
	private static function fill_atts_for_form_display( &$args ) {
		$defaults = array(
			'errors'      => array(),
			'message'     => '',
			'fields'      => array(),
			'form'        => array(),
			'title'       => true,
			'description' => false,
			'reset'       => false,
		);
		$args     = wp_parse_args( $args, $defaults );
	}

	/**
	 * Show the success message without the form
	 *
	 * @since 2.05
	 */
	private static function show_lone_success_messsage( $atts ) {
		global $frm_vars;
		$values = FrmEntriesHelper::setup_new_vars( $atts['fields'], $atts['form'], true );
		self::maybe_load_css( $atts['form'], $values['custom_style'], $frm_vars['load_css'] );

		$include_extra_container = 'frm_forms' . FrmFormsHelper::get_form_style_class( $values );

		$errors  = array();
		$form    = $atts['form'];
		$message = $atts['message'];

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-entries/errors.php' );
	}

	/**
	 * Prepare the success message before it's shown
	 *
	 * @since 2.05
	 */
	private static function prepare_submit_message( $form, $entry_id ) {
		$frm_settings = FrmAppHelper::get_settings( array( 'current_form' => $form->id ) );

		if ( $entry_id && is_numeric( $entry_id ) ) {
			$message = isset( $form->options['success_msg'] ) ? $form->options['success_msg'] : $frm_settings->success_msg;
			$class   = 'frm_message';
		} else {
			$message = $frm_settings->failed_msg;
			$class   = FrmFormsHelper::form_error_class();
		}

		$message = FrmFormsHelper::get_success_message( compact( 'message', 'form', 'entry_id', 'class' ) );

		return apply_filters( 'frm_main_feedback', $message, $form, $entry_id );
	}

	public static function front_head() {
		$version = FrmAppHelper::plugin_version();
		$suffix  = FrmAppHelper::js_suffix();

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

		if ( ! $load_css ) {
			return;
		}

		global $frm_vars;
		self::footer_js( 'header' );
		$frm_vars['css_loaded'] = true;

		self::load_late_css();
	}

	/**
	 * If css is loaded only on applicable pages, include it before the form loads
	 * to prevent a flash of unstyled form.
	 *
	 * @since 4.01
	 *
	 * @return void
	 */
	private static function load_late_css() {
		$frm_settings = FrmAppHelper::get_settings();
		$late_css     = $frm_settings->load_style === 'dynamic';

		if ( ! $late_css || ! self::should_load_late() ) {
			return;
		}

		global $wp_styles;
		if ( is_array( $wp_styles->queue ) && in_array( 'formidable', $wp_styles->queue, true ) ) {
			wp_print_styles( 'formidable' );
		}
	}

	/**
	 * Avoid late load if All in One SEO is active because it prevents CSS from loading entirely.
	 *
	 * @since 5.2.03
	 *
	 * @return bool
	 */
	private static function should_load_late() {
		return ! function_exists( 'aioseo' );
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
			// load formidable js
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
	 * @since 5.0.16
	 *
	 * @return void
	 */
	public static function landing_page_preview_option() {
		$dir = apply_filters( 'frm_landing_page_preview_option', false );
		if ( false === $dir || ! file_exists( $dir . 'landing-page-preview-option.php' ) ) {
			$dir = self::get_form_views_path();
		}
		include $dir . 'landing-page-preview-option.php';
	}

	/**
	 * @since 5.0.16
	 *
	 * @return string
	 */
	private static function get_form_views_path() {
		return FrmAppHelper::plugin_path() . '/classes/views/frm-forms/';
	}

	/**
	 * Create a page with an embedded formidable Gutenberg block.
	 *
	 * @since 5.2
	 *
	 * @return never
	 */
	public static function create_page_with_shortcode() {
		if ( ! current_user_can( 'publish_posts' ) ) {
			die( 0 );
		}

		check_ajax_referer( 'frm_ajax', 'nonce' );

		$type = FrmAppHelper::get_post_param( 'type', '', 'sanitize_text_field' );
		if ( ! $type || ! in_array( $type, array( 'form', 'view' ), true ) ) {
			die( 0 );
		}

		$object_id = FrmAppHelper::get_post_param( 'object_id', '', 'absint' );
		if ( ! $object_id ) {
			die( 0 );
		}

		$postarr = array( 'post_type' => 'page' );

		if ( 'form' === $type ) {
			$postarr['post_content'] = self::get_page_shortcode_content_for_form( $object_id );
		} else {
			$postarr['post_content'] = apply_filters( 'frm_create_page_with_' . $type . '_shortcode_content', '', $object_id );
		}

		$name = FrmAppHelper::get_post_param( 'name', '', 'sanitize_text_field' );
		if ( $name ) {
			$postarr['post_title'] = $name;
		}

		$success = wp_insert_post( $postarr );
		if ( ! is_numeric( $success ) || ! $success ) {
			die( 0 );
		}

		wp_send_json(
			array(
				'redirect' => get_edit_post_link( $success, 'redirect' ),
			)
		);
	}

	/**
	 * @since 5.3
	 *
	 * @param string $content
	 * @param int    $form_id
	 * @return string
	 */
	private static function get_page_shortcode_content_for_form( $form_id ) {
		$shortcode          = '[formidable id="' . $form_id . '"]';
		$html_comment_start = '<!-- wp:formidable/simple-form {"formId":"' . $form_id . '"} -->';
		$html_comment_end   = '<!-- /wp:formidable/simple-form -->';
		return $html_comment_start . '<div>' . $shortcode . '</div>' . $html_comment_end;
	}

	/**
	 * Get page dropdown for AJAX request for embedding form in an existing page.
	 *
	 * @return never
	 */
	public static function get_page_dropdown() {
		if ( ! current_user_can( 'publish_posts' ) ) {
			die( 0 );
		}

		check_ajax_referer( 'frm_ajax', 'nonce' );

		$html             = FrmAppHelper::clip(
			function() {
				FrmAppHelper::maybe_autocomplete_pages_options(
					array(
						'field_name'  => 'frm_page_dropdown',
						'page_id'     => '',
						'placeholder' => __( 'Select a Page', 'formidable' ),
					)
				);
			}
		);
		$post_type_object = get_post_type_object( 'page' );
		wp_send_json(
			array(
				'html'          => $html,
				'edit_page_url' => admin_url( sprintf( $post_type_object->_edit_link . '&action=edit', 0 ) ),
			)
		);
	}

	/**
	 * @deprecated 4.0
	 */
	public static function new_form( $values = array() ) {
		FrmDeprecated::new_form( $values );
	}

	/**
	 * @deprecated 4.0
	 */
	public static function create( $values = array() ) {
		_deprecated_function( __METHOD__, '4.0', 'FrmFormsController::update' );
		self::update( $values );
	}

	/**
	 * @deprecated 1.07.05
	 * @codeCoverageIgnore
	 */
	public static function add_default_templates( $path, $default = true, $template = true ) {
		FrmDeprecated::add_default_templates( $path, $default, $template );
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function bulk_create_template( $ids ) {
		return FrmDeprecated::bulk_create_template( $ids );
	}

	/**
	 * @deprecated 2.03
	 * @codeCoverageIgnore
	 */
	public static function register_pro_scripts() {
		FrmDeprecated::register_pro_scripts();
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function edit_key() {
		FrmDeprecated::edit_key();
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function edit_description() {
		FrmDeprecated::edit_description();
	}

	/**
	 * @deprecated 4.08
	 * @since 3.06
	 */
	public static function add_new() {
		_deprecated_function( __FUNCTION__, '4.08' );
	}
}
