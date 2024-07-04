<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFormsController {

	/**
	 * Track the form that opened the redirect URL in a new tab. This is used to check if we should show the default
	 * message in the currect tab.
	 *
	 * @since 6.2
	 *
	 * @var array Keys are form IDs and values are 1.
	 */
	private static $redirected_in_new_tab = array();

	/**
	 * The HTML for the Formdiable TinyMCE button (That triggers a popup to insert shortcodes)
	 * is stored here and re-used as an optimization.
	 *
	 * @since 6.11
	 *
	 * @var string|null
	 */
	private static $formidable_tinymce_button;

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
		$data_message .= ' <img src="' . esc_url( $images_url ) . '/survey-logic.png" srcset="' . esc_url( $images_url ) . 'survey-logic@2x.png 2x" alt="' . esc_attr__( 'Conditional Logic options', 'formidable' ) . '"/>';
		echo '<a href="javascript:void(0)" class="frm_noallow frm_show_upgrade frm_add_logic_link frm-collapsed frm-flex-justify" data-upgrade="' . esc_attr__( 'Conditional Logic options', 'formidable' ) . '" data-message="' . esc_attr( $data_message ) . '" data-medium="builder" data-content="logic">';
		esc_html_e( 'Conditional Logic', 'formidable' );
		FrmAppHelper::icon_by_class( 'frmfont frm_arrowdown6_icon', array( 'aria-hidden' => 'true' ) );
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
	 * @param int|object $form
	 */
	private static function create_default_email_action( $form ) {
		FrmForm::maybe_get_form( $form );
		$create_email = apply_filters( 'frm_create_default_email_action', true, $form );

		if ( $create_email ) {
			$action_control = FrmFormActionsController::get_form_actions( 'email' );
			$action_control->create( $form->id );
		}
	}

	/**
	 * Create the default On submit action
	 *
	 * @since 6.0.0
	 *
	 * @param int|object $form Form object or ID.
	 */
	private static function create_default_on_submit_action( $form ) {
		FrmForm::maybe_get_form( $form );

		/**
		 * Enable or disable the default On Submit action.
		 *
		 * @since 6.0.0
		 *
		 * @param bool   $create Set to `false` if you want to disable.
		 * @param object $form   Form object.
		 */
		$create = apply_filters( 'frm_create_default_on_submit_action', true, $form );

		if ( $create ) {
			$action_control = FrmFormActionsController::get_form_actions( FrmOnSubmitAction::$slug );
			$action_control->create( $form->id );
		}
	}

	/**
	 * Creates submit button field.
	 *
	 * @since 6.9
	 *
	 * @param int|object $form Form ID or object.
	 */
	private static function create_submit_button_field( $form ) {
		FrmForm::maybe_get_form( $form );

		if ( FrmSubmitHelper::get_submit_field( $form->id ) ) {
			// Do not create submit button field if it exists.
			return;
		}

		FrmField::create(
			array(
				'type'          => FrmSubmitHelper::FIELD_TYPE,
				'name'          => __( 'Submit', 'formidable' ),
				'field_order'   => 9999,
				'form_id'       => $form->id,
				'field_options' => FrmFieldsHelper::get_default_field_options( FrmSubmitHelper::FIELD_TYPE ),
				'description'   => '',
				'default_value' => '',
				'options'       => array(),
			)
		);
	}

	/**
	 * @return void
	 */
	public static function edit( $values = false ) {
		FrmAppHelper::permission_check( 'frm_edit_forms' );

		$id = isset( $values['id'] ) ? absint( $values['id'] ) : FrmAppHelper::get_param( 'id', '', 'get', 'absint' );

		self::get_edit_vars( $id );
	}

	/**
	 * @param mixed  $id
	 * @param string $message
	 * @return void
	 */
	public static function settings( $id = false, $message = '' ) {
		FrmAppHelper::permission_check( 'frm_edit_forms' );

		if ( ! $id || ! is_numeric( $id ) ) {
			$id = FrmAppHelper::get_param( 'id', '', 'get', 'absint' );
		}

		FrmOnSubmitHelper::maybe_migrate_submit_settings_to_action( $id );

		self::get_settings_vars( $id, array(), $message );
	}

	public static function update_settings() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		$process_form = FrmAppHelper::get_post_param( 'process_form', '', 'sanitize_text_field' );

		if ( ! wp_verify_nonce( $process_form, 'process_form_nonce' ) ) {
			$frm_settings = FrmAppHelper::get_settings();
			$error_args   = array(
				'title'       => __( 'Verification failed', 'formidable' ),
				'body'        => $frm_settings->admin_permission,
				'cancel_text' => __( 'Cancel', 'formidable' ),
			);
			FrmAppController::show_error_modal( $error_args );
			return;
		}

		$id = FrmAppHelper::get_param( 'id', '', 'get', 'absint' );

		$errors   = FrmForm::validate( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$warnings = FrmFormsHelper::check_for_warnings( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( count( $errors ) > 0 ) {
			self::get_settings_vars( $id, $errors, compact( 'warnings' ) );
			return;
		}

		do_action( 'frm_before_update_form_settings', $id );

		$antispam_was_on = self::antispam_was_on( $id );

		FrmForm::update( $id, $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$antispam_is_on = ! empty( $_POST['options']['antispam'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( $antispam_is_on !== $antispam_was_on ) {
			FrmAntiSpam::clear_caches();
		}

		$message = __( 'Settings Successfully Updated', 'formidable' );

		self::get_settings_vars( $id, array(), compact( 'message', 'warnings' ) );
	}

	/**
	 * @param int $form_id
	 * @return bool
	 */
	private static function antispam_was_on( $form_id ) {
		$form = FrmForm::getOne( $form_id );
		return ! empty( $form->options['antispam'] );
	}

	/**
	 * @return void
	 */
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
			self::get_edit_vars( $id, $errors );
			return;
		}

		self::maybe_remove_draft_option_from_fields( $id );

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

		self::get_edit_vars( $id, array(), $message );
	}

	/**
	 * Remove the draft flag from any new fields from this current session.
	 *
	 * @since 6.8
	 *
	 * @param int $form_id
	 * @return void
	 */
	private static function maybe_remove_draft_option_from_fields( $form_id ) {
		$draft_field_ids_csv = FrmAppHelper::get_post_param( 'draft_fields', '', 'sanitize_text_field' );
		if ( ! $draft_field_ids_csv ) {
			// If the draft_fields input is empty there are no new fields in the session.
			return;
		}

		$draft_field_ids = array_filter( explode( ',', $draft_field_ids_csv ), 'is_numeric' );
		if ( ! $draft_field_ids ) {
			// Exit early if the draft fields input is invalid. It should be a CSV of integer values.
			return;
		}

		$draft_field_rows = FrmFieldsHelper::get_draft_field_results( $form_id, $draft_field_ids );
		foreach ( $draft_field_rows as $row ) {
			$row->field_options['draft'] = 0;
			FrmField::update( $row->id, array( 'field_options' => $row->field_options ) );
		}
	}

	/**
	 * Check if the value at the end of the form was included.
	 * If it's missing, it means other values at the end of the form
	 * were likely not saved either.
	 *
	 * @since 3.06.01
	 *
	 * @return bool
	 */
	private static function is_too_long( $values ) {
		return empty( $values['frm_end'] );
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
			$new_template = FrmAppHelper::simple_get( 'new_template' ) ? '&new_template=true' : '';
			$url          = admin_url( 'admin.php?page=formidable&frm_action=edit&id=' . absint( $form ) . $new_template );
			$message      = 'form_duplicated';
		}

		$url .= '&message=' . $message;

		wp_safe_redirect( $url );
		exit();
	}

	/**
	 * @return string|null
	 */
	public static function page_preview() {
		$params = FrmForm::list_page_params();
		if ( ! $params['form'] ) {
			return null;
		}

		$form = FrmForm::getOne( $params['form'] );
		if ( ! $form ) {
			return null;
		}

		return self::show_form( $form->id, '', 'auto', 'auto' );
	}

	/**
	 * @since 3.0
	 *
	 * @return void
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
		if ( ! is_a( $random_page, 'WP_Post' ) ) {
			// The return type can also be int.
			return;
		}

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
	 * @param WP_Post $page The page object.
	 * @return void
	 */
	private static function set_post_global( $page ) {
		global $post;
		$post = $page; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	}

	/**
	 * @since 3.0
	 *
	 * @return void
	 */
	private static function load_theme_preview() {
		add_filter( 'wp_title', 'FrmFormsController::preview_title', 9999 );
		add_filter( 'the_title', 'FrmFormsController::preview_page_title', 9999 );
		add_filter( 'the_content', 'FrmFormsController::preview_content', 9999 );
		add_action( 'loop_no_results', 'FrmFormsController::show_page_preview' );
		add_filter( 'is_active_sidebar', '__return_false' );
		FrmStylesController::enqueue_css( 'enqueue', true );

		if ( false === get_template_part( 'page' ) ) {
			if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
				add_filter( 'body_class', 'FrmFormsController::preview_block_theme_body_classnames' );
			}
			self::fallback_when_page_template_part_is_not_supported_by_theme();
		}
	}

	/**
	 * Add padding to the body for block themes.
	 *
	 * @since 6.5.2
	 *
	 * @param array $classes The body classes list.
	 * @return array
	 */
	public static function preview_block_theme_body_classnames( $classes ) {
		$classes[] = 'has-global-padding';
		return $classes;
	}

	/**
	 * Not every theme supports get_template_part( 'page' ).
	 * When this is not supported, false is returned, and we can handle a fallback.
	 *
	 * @return void
	 */
	private static function fallback_when_page_template_part_is_not_supported_by_theme() {
		if ( have_posts() ) {
			the_post();
			self::get_template( 'header' );

			// add some generic class names to the container to add some natural padding to the content.
			// .entry-content catches the WordPress TwentyTwenty theme.
			// .container catches Customizr content.
			echo '<div class="container entry-content">';
			the_content();
			echo '</div>';

			self::get_template( 'footer' );
		}
	}

	/**
	 * Calls core function to get a template part if it doesn't cause deprecation warnings. Otherwise skips the deprecation function call
	 * and renders required html fragements calling required functions.
	 *
	 * @since 6.7.1
	 * @param string $template
	 * @return void
	 */
	private static function get_template( $template ) {
		if ( self::should_try_getting_template( $template ) ) {
			call_user_func( 'get_' . $template );
			return;
		}

		if ( 'header' === $template ) {
			include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/preview/header.php';
			return;
		}

		if ( 'footer' === $template ) {
			include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/preview/footer.php';
		}
	}

	/**
	 * Returns true if calling a template function doesn't trigger deprecation warnings.
	 *
	 * @since 6.7.1
	 * @param string $template
	 * @return bool
	 */
	private static function should_try_getting_template( $template ) {
		$stylesheet_path = get_stylesheet_directory();
		$template_path   = get_template_directory();
		$is_child_theme  = $stylesheet_path !== $template_path;
		$template_name   = $template . '.php';

		return file_exists( $stylesheet_path . '/' . $template_name ) || $is_child_theme && file_exists( $template_path . '/' . $template_name );
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
	 * Set the page title for the theme preview page.
	 *
	 * @since 3.0
	 *
	 * @param string $title
	 * @return string
	 */
	public static function preview_title( $title ) {
		return __( 'Form Preview', 'formidable' );
	}

	/**
	 * Set the page content for the theme preview page.
	 *
	 * @since 3.0
	 *
	 * @param string $content
	 * @return string
	 */
	public static function preview_content( $content ) {
		if ( in_the_loop() ) {
			self::show_page_preview();
			// Clear the content for the page we're using.
			$content = '';
		}

		return $content;
	}

	/**
	 * @since 3.0
	 */
	private static function load_direct_preview() {
		header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );

		// print_emoji_styles is deprecated.
		remove_action( 'wp_print_styles', 'print_emoji_styles' );

		$key = FrmAppHelper::simple_get( 'form', 'sanitize_title' );
		if ( $key == '' ) {
			$key = FrmAppHelper::get_post_param( 'form', '', 'sanitize_title' );
		}

		$form = FrmForm::getAll( array( 'form_key' => $key ), '', 1 );
		if ( empty( $form ) ) {
			$form = FrmForm::getAll( array(), '', 1 );
		}

		self::fix_deprecated_null_param_warning();

		require FrmAppHelper::plugin_path() . '/classes/views/frm-entries/direct.php';
	}

	/**
	 * Some themes have a null $src value.
	 * This function adds a filter to ensure that $src is not null.
	 * WP will call str_starts_with with the null value triggering a deprecated message otherwise.
	 *
	 * @since 6.10
	 *
	 * @return void
	 */
	private static function fix_deprecated_null_param_warning() {
		add_filter(
			'script_loader_src',
			/**
			 * @param string|null $src
			 * @return string
			 */
			function ( $src ) {
				if ( is_null( $src ) ) {
					$src = '';
				}
				return $src;
			}
		);
	}

	public static function untrash() {
		self::change_form_status( 'untrash' );
	}

	/**
	 * @param array $ids
	 * @return string
	 */
	public static function bulk_untrash( $ids ) {
		FrmAppHelper::permission_check( 'frm_edit_forms' );

		$count = FrmForm::set_status( $ids, 'published' );

		if ( ! $count ) {
			// Don't show "0 forms restored" on refresh.
			return '';
		}

		/* translators: %1$s: Number of forms */
		$message = sprintf( _n( '%1$s form restored from the Trash.', '%1$s forms restored from the Trash.', $count, 'formidable' ), $count );

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

		// Check nonce url.
		check_admin_referer( $status . '_form_' . $params['id'] );

		$count = 0;
		if ( FrmForm::set_status( $params['id'], $available_status[ $status ]['new_status'] ) ) {
			++$count;
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
		$available_status['trash']['message'] = sprintf( _n( '%1$s form moved to the Trash. %2$sUndo%3$s', '%1$s forms moved to the Trash. %2$sUndo%3$s', $count, 'formidable' ), $count, '<a href="' . esc_url( wp_nonce_url( '?page=formidable&frm_action=untrash&form_type=' . $form_type . '&id=' . $params['id'], 'untrash_form_' . $params['id'] ) ) . '">', '</a>' );

		$message = $available_status[ $status ]['message'];

		self::display_forms_list( $params, $message );
	}

	/**
	 * @param array $ids
	 * @return string
	 */
	public static function bulk_trash( $ids ) {
		FrmAppHelper::permission_check( 'frm_delete_forms' );

		$count = 0;
		foreach ( $ids as $id ) {
			if ( FrmForm::trash( $id ) ) {
				++$count;
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
			++$count;
		}

		/* translators: %1$s: Number of forms */
		$message = sprintf( _n( '%1$s Form Permanently Deleted', '%1$s Forms Permanently Deleted', $count, 'formidable' ), $count );

		self::display_forms_list( $params, $message );
	}

	/**
	 * @param array $ids
	 * @return string
	 */
	public static function bulk_destroy( $ids ) {
		FrmAppHelper::permission_check( 'frm_delete_forms' );

		$count = 0;
		foreach ( $ids as $id ) {
			$d = FrmForm::destroy( $id );
			if ( $d ) {
				++$count;
			}
		}

		if ( $count ) {
			/* translators: %1$s: Number of forms */
			return sprintf( _n( '%1$s form permanently deleted.', '%1$s forms permanently deleted.', $count, 'formidable' ), $count );
		}

		return '';
	}

	/**
	 * @since 6.7.1 Function went from private to public.
	 */
	public static function delete_all() {
		// Check nonce url.
		$permission_error = FrmAppHelper::permission_nonce_error( 'frm_delete_forms', '_wpnonce', 'bulk-toplevel_page_formidable' );
		if ( $permission_error !== false ) {
			self::display_forms_list( array(), '', array( $permission_error ) );

			return;
		}

		$count = FrmForm::scheduled_delete( time() );
		$url   = remove_query_arg( array( 'delete_all' ) );

		$url .= '&message=forms_permanently_deleted&forms_deleted=' . $count;

		wp_safe_redirect( $url );
		die();
	}

	/**
	 * Create a new form from the modal.
	 *
	 * @since 4.0
	 */
	public static function build_new_form() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$new_values             = self::get_modal_values();
		$new_values['form_key'] = $new_values['name'];
		$new_values['options']  = array(
			'antispam'           => 1,
			'on_submit_migrated' => 1,
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

		self::create_submit_button_field( $form_id );
		self::create_default_on_submit_action( $form_id );
		self::create_default_email_action( $form_id );

		$response = array(
			'redirect' => FrmForm::get_edit_link( $form_id ) . '&new_template=true',
		);

		echo wp_json_encode( $response );
		wp_die();
	}

	/**
	 * Before creating a new form, get the name and description from the modal.
	 *
	 * @since 4.0
	 *
	 * @return array<string,string>
	 */
	public static function get_modal_values() {
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
	 *
	 * @return void
	 */
	public static function insert_form_button() {
		if ( current_user_can( 'frm_view_forms' ) ) {
			// Store the result in memory and re-use it when this function is called multiple times.
			// This helps speed up the form builder when there are a lot of HTML fields, where this
			// button is inserted once per HTML field.
			// In a form with 66 HTML fields, this saves 0.5 seconds on page load time, tested locally.
			if ( ! isset( self::$formidable_tinymce_button ) ) {
				FrmAppHelper::load_admin_wide_js();
				$menu_name                       = FrmAppHelper::get_menu_name();
				$icon                            = apply_filters( 'frm_media_icon', FrmAppHelper::svg_logo() );
				self::$formidable_tinymce_button = '<a href="#TB_inline?width=50&height=50&inlineId=frm_insert_form" class="thickbox button add_media frm_insert_form" title="' . esc_attr__( 'Add forms and content', 'formidable' ) . '">' . FrmAppHelper::kses( $icon, 'all' ) . ' ' . esc_html( $menu_name ) . '</a>';
			}
			echo self::$formidable_tinymce_button; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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

		include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/shortcode_opts.php';

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
		$columns['cb']         = '<input type="checkbox" />';
		$columns['name']       = esc_html__( 'Form Title', 'formidable' );
		$columns['entries']    = esc_html__( 'Entries', 'formidable' );
		$columns['id']         = 'ID';
		$columns['form_key']   = esc_html__( 'Key', 'formidable' );
		$columns['shortcode']  = esc_html__( 'Actions', 'formidable' );
		$columns['created_at'] = esc_html__( 'Date', 'formidable' );

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

	/**
	 * @return array<string,string>
	 */
	public static function get_sortable_columns() {
		return array(
			'id'          => 'id',
			'name'        => 'name',
			'description' => 'description',
			'form_key'    => 'form_key',
			'created_at'  => 'created_at',
		);
	}

	/**
	 * @param mixed $hidden_columns
	 * @return array
	 */
	public static function hidden_columns( $hidden_columns ) {
		if ( ! is_array( $hidden_columns ) ) {
			$hidden_columns = array();
		}

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
		if ( $option === 'formidable_page_formidable_per_page' ) {
			$save = (int) $value;
		}

		return $save;
	}

	/**
	 * @param int|string $id
	 * @param array      $errors
	 * @param string     $message
	 * @param bool       $create_link
	 * @return void
	 */
	private static function get_edit_vars( $id, $errors = array(), $message = '', $create_link = false ) {
		global $frm_vars;

		$form       = FrmForm::getOne( $id );
		$error_args = array(
			'title'        => __( 'You can\'t edit the form', 'formidable' ),
			'body'         => __( 'You are trying to edit a form that does not exist', 'formidable' ),
			'cancel_url'   => admin_url( 'admin.php?page=formidable' ),
			'continue_url' => add_query_arg(
				array(
					'page' => 'formidable',
				)
			),
		);
		if ( ! $form ) {
			FrmAppController::show_error_modal( $error_args );
			return;
		}

		if ( 'trash' === $form->status ) {
			$error_args['body']          = __( 'The form you\'re trying to edit is in trash. You must restore it first before you can make changes', 'formidable' );
			$error_args['continue_url']  = add_query_arg(
				array(
					'page'       => 'formidable',
					'_wpnonce'   => wp_create_nonce( 'untrash_form_' . $id ),
					'form_type'  => 'trash',
					'frm_action' => 'untrash',
					'id'         => $id,
				)
			);
			$error_args['continue_text'] = __( 'Restore form', 'formidable' );
			FrmAppController::show_error_modal( $error_args );
			return;
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
		FrmSubmitHelper::maybe_create_submit_field( $form, $fields, $reset_fields );

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
		$has_fields    = ! empty( $values['fields'] ) && ! FrmSubmitHelper::only_contains_submit_field( $values['fields'] );

		if ( defined( 'DOING_AJAX' ) ) {
			wp_die();
		}

		require FrmAppHelper::plugin_path() . '/classes/views/frm-forms/edit.php';
	}

	/**
	 * @param array $fields
	 * @return array
	 */
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

	/**
	 * @param int|string   $id
	 * @param array        $errors
	 * @param array|string $args
	 * @return void
	 */
	public static function get_settings_vars( $id, $errors = array(), $args = array() ) {
		FrmAppHelper::permission_check( 'frm_edit_forms' );

		global $frm_vars;

		self::maybe_print_media_templates();

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

		require FrmAppHelper::plugin_path() . '/classes/views/frm-forms/settings.php';
	}

	/**
	 * Print WordPress media templates email actions does not trigger a "Uncaught Error: Template not found: #tmpl-media-selection" error when the media button is clicked.
	 *
	 * @since 6.10
	 *
	 * @return void
	 */
	private static function maybe_print_media_templates() {
		if ( FrmAppHelper::pro_is_included() ) {
			// This issue does not exist when Pro is active so exit early.
			return;
		}

		add_action(
			'wp_enqueue_editor',
			function () {
				wp_print_media_templates();
			}
		);
	}

	/**
	 * @since 4.0
	 */
	public static function form_publish_button( $atts ) {
		$values = $atts['values'];
		include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/_publish_box.php';
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
				'icon'       => 'frm_icon_font frm_lock_closed_icon',
				'html_class' => 'frm_show_upgrade_tab frm_noallow',
				'data'       => array(
					'medium'     => 'permissions',
					'upgrade'    => __( 'Form Permissions', 'formidable' ),
					'message'    => __( 'Allow editing, protect forms and files, limit entries, and save drafts. Upgrade to get form and entry permissions.', 'formidable' ),
					'screenshot' => 'permissions.png',
				),
			),
			'scheduling'  => array(
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
				'name'     => __( 'Buttons', 'formidable' ),
				'class'    => __CLASS__,
				'function' => 'buttons_settings',
				'icon'     => 'frm_icon_font frm_button_icon',
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
			'abandonment' => array(
				'name'       => __( 'Form Abandonment', 'formidable' ),
				'icon'       => 'frm_icon_font frm_abandoned_icon',
				'html_class' => 'frm_show_upgrade_tab frm_noallow',
				'data'       => FrmAppHelper::get_upgrade_data_params(
					'abandonment',
					array(
						'upgrade'    => __( 'Form abandonment settings', 'formidable' ),
						'message'    => __( 'Unlock the power of data capture to boost lead generation and master the art of form optimization.', 'formidable' ),
						'screenshot' => 'abandonment.png',
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

		foreach ( array( 'landing', 'chat', 'abandonment' ) as $feature ) {
			if ( ! FrmAppHelper::show_new_feature( $feature ) ) {
				unset( $sections[ $feature ] );
			}
		}

		$sections = apply_filters( 'frm_add_form_settings_section', $sections, $values );

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
		}//end foreach

		return $sections;
	}

	/**
	 * @since 4.0
	 *
	 * @param array $values
	 * @return void
	 */
	public static function advanced_settings( $values ) {
		$first_h3 = 'frm_first_h3';

		include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/settings-advanced.php';
	}

	/**
	 * @param array $values
	 * @return void
	 */
	public static function render_spam_settings( $values ) {
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
	 * @return void
	 */
	public static function buttons_settings( $values ) {
		include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/settings-buttons.php';
	}

	/**
	 * @since 4.0
	 *
	 * @param array $values
	 * @return void
	 */
	public static function html_settings( $values ) {
		include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/settings-html.php';
	}

	/**
	 * Replace old Submit Button href with new href to avoid errors in Chrome
	 *
	 * @since 2.03.08
	 *
	 * @param array|bool $values
	 * @return void
	 */
	private static function clean_submit_html( &$values ) {
		if ( is_array( $values ) && isset( $values['submit_html'] ) ) {
			$values['submit_html'] = str_replace( 'javascript:void(0)', '#', $values['submit_html'] );
		}
	}

	/**
	 * Updates classes used in submit and prev buttons to avoid conflict with twenty twenty-one theme.
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	public static function update_button_classes( $classes ) {
		if ( function_exists( 'twenty_twenty_one_setup' ) ) {
			$classes[] = 'has-text-color has-background';
		}

		return $classes;
	}

	/**
	 * @param int|string $form_id
	 * @param string     $class
	 * @return void
	 */
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
		$settings_tab = FrmAppHelper::is_admin_page( 'formidable' );

		$cond_shortcodes  = apply_filters( 'frm_conditional_shortcodes', array() );
		$entry_shortcodes = self::get_shortcode_helpers( $settings_tab );

		$advanced_helpers = self::advanced_helpers( compact( 'fields', 'form_id' ) );

		include FrmAppHelper::plugin_path() . '/classes/views/shared/mb_adv_info.php';
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
		if ( $user_fields ) {
			$user_helpers = array();
			foreach ( $user_fields as $uk => $uf ) {
				$user_helpers[ '|user_id| show="' . $uk . '"' ] = $uf;
				unset( $uk, $uf );
			}

			$advanced_helpers['user_id'] = array(
				'codes' => $user_helpers,
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
	 * Insert the form class setting into the form.
	 *
	 * @param stdClass $form
	 * @return void
	 */
	public static function form_classes( $form ) {
		if ( isset( $form->options['form_class'] ) ) {
			echo esc_attr( sanitize_text_field( $form->options['form_class'] ) );
		}

		if ( ! empty( $form->options['js_validate'] ) ) {
			echo ' frm_js_validate ';
			self::add_js_validate_form_to_global_vars( $form );
		}

		if ( ! FrmFormsHelper::should_use_pro_for_ajax_submit() && FrmForm::is_ajax_on( $form ) ) {
			echo ' frm_ajax_submit ';
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
				'form_id'       => FrmAppHelper::get_post_param( 'form_id', '', 'absint' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'default_email' => true,
				'plain_text'    => FrmAppHelper::get_post_param( 'plain_text', '', 'absint' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			)
		);
		wp_die();
	}

	/**
	 * @param string                    $content
	 * @param int|stdClass|string       $form
	 * @param false|int|stdClass|string $entry
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

		/*
		 * Repeater actions adds `parent_entry` to `$entry` store the parent entry data. If `parent_entry` is not empty,
		 * use the parent form ID instead of repeater form ID to fix the parent form field shortcodes doesn't work.
		 */
		if ( ! empty( $entry->parent_entry ) ) {
			$form = $entry->parent_entry->form_id;
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
	 * @param array|string        $string
	 * @param int|stdClass|string $form
	 * @return array|string
	 */
	public static function replace_form_name_shortcodes( $string, $form ) {
		if ( ! is_string( $string ) ) {
			return $string;
		}

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
	 * @param false|int|stdClass|string $entry
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

	/**
	 * @param array $errors
	 * @return array
	 */
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

		if ( ! empty( $message ) ) {
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
				if ( 'edit' === $action ) {
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
		}//end if

		add_action( 'frm_load_form_hooks', 'FrmHooksController::trigger_load_form_hooks' );
		FrmAppHelper::trigger_hook_load( 'form' );

		switch ( $action ) {
			case 'create':
			case 'edit':
			case 'update':
			case 'trash':
			case 'untrash':
			case 'destroy':
			case 'settings':
			case 'update_settings':
				return self::$action( $vars );
			case 'lite-reports':
				self::no_reports( $vars );
				return;
			case 'views':
				self::no_views( $vars );
				return;
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

				if ( 'forms_permanently_deleted' === $message ) {
					$count = FrmAppHelper::get_param( 'forms_deleted', 0, 'get', 'absint' );
					/* translators: %1$s: Number of forms */
					$message = sprintf( _n( '%1$s form permanently deleted.', '%1$s forms permanently deleted.', $count, 'formidable' ), $count );
					self::display_forms_list( array(), $message, '' );
					return;
				}

				self::display_forms_list();

				return;
		}//end switch
	}

	/**
	 * Rename a form.
	 *
	 * Handles the AJAX request for renaming a form.
	 *
	 * @since 6.7
	 *
	 * @return void
	 */
	public static function rename_form() {
		// Check permission and nonce
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		// Get posted data
		$form_id = FrmAppHelper::get_post_param( 'form_id', '', 'absint' );
		$name    = FrmAppHelper::get_post_param( 'form_name', '', 'sanitize_text_field' );

		// Update the form name and form key.
		$form_key = FrmAppHelper::get_unique_key( sanitize_title( $name ), 'frm_forms', 'form_key' );
		FrmForm::update( $form_id, compact( 'name', 'form_key' ) );

		wp_send_json_success( compact( 'form_key' ) );
	}

	public static function json_error( $errors ) {
		$errors['json'] = __( 'Abnormal HTML characters prevented your form from saving correctly', 'formidable' );

		return $errors;
	}

	/**
	 * Education for premium features.
	 *
	 * @since 4.05
	 * @return void
	 */
	public static function add_form_style_tab_options() {
		include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/add_form_style_options.php';
	}

	/**
	 * Add education about views.
	 *
	 * @since 4.07
	 *
	 * @return void
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
	 *
	 * @return void
	 */
	public static function no_reports( $values = array() ) {
		$id   = FrmAppHelper::get_param( 'form', '', 'get', 'absint' );
		$form = $id ? FrmForm::getOne( $id ) : false;

		include FrmAppHelper::plugin_path() . '/classes/views/shared/reports-info.php';
	}

	/**
	 * FRONT-END FORMS.
	 */
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
		if ( ! empty( $frm_vars['skip_shortcode'] ) ) {
			$sc  = '[formidable';
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
	 * @param false|int|string $id
	 * @param false|string     $key
	 * @return false|stdClass
	 */
	private static function maybe_get_form_by_id_or_key( $id, $key ) {
		if ( ! $id ) {
			$id = $key;
		}
		return self::maybe_get_form_to_show( $id );
	}

	/**
	 * @param false|int|string $id
	 * @param false|string     $key
	 * @param bool|int|string  $title may be 'auto', true, false, 'true', 'false', 'yes', '1', 1, '0', 0.
	 * @param bool|int|string  $description may be 'auto', true, false, 'true', 'false', 'yes', '1', 1, '0', 0.
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
	 * @param false|int|string $id
	 * @return false|stdClass
	 */
	private static function maybe_get_form_to_show( $id ) {
		$form = false;

		if ( ! empty( $id ) ) {
			// Form id or key is set.
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

		$pass_args['action'] = $params['action'];
		$handle_process_here = $params['action'] === 'create' && $params['posted_form_id'] == $form->id && $_POST; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( ! $handle_process_here ) {
			FrmFormState::set_initial_value( 'title', $title );
			FrmFormState::set_initial_value( 'description', $description );

			do_action( 'frm_display_form_action', $params, $fields, $form, $title, $description );
			if ( apply_filters( 'frm_continue_to_new', true, $form->id, $params['action'] ) ) {
				self::show_form_after_submit( $pass_args );
			}
		} elseif ( ! empty( $errors ) ) {
			self::show_form_after_submit( $pass_args );

		} else {

			do_action( 'frm_validate_form_creation', $params, $fields, $form, $title, $description );

			if ( apply_filters( 'frm_continue_to_create', true, $form->id ) ) {
				$entry_id              = self::just_created_entry( $form->id );
				$pass_args['entry_id'] = $entry_id;
				$pass_args['reset']    = true;

				self::run_on_submit_actions( $pass_args );

				do_action(
					'frm_after_entry_processed',
					array(
						'entry_id' => $entry_id,
						'form'     => $form,
					)
				);
			}
		}//end if
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

		return isset( $frm_vars['created_entries'] ) && isset( $frm_vars['created_entries'][ $form_id ] ) && isset( $frm_vars['created_entries'][ $form_id ]['entry_id'] ) ? $frm_vars['created_entries'][ $form_id ]['entry_id'] : 0;
	}

	/**
	 * Gets confirmation method.
	 *
	 * @since 3.0
	 * @since 6.0 This method can return an array of met On Submit actions.
	 *
	 * @param array $atts {
	 *     Atts.
	 *
	 *     @type object $form     Form object.
	 *     @type int    $entry_id Entry ID.
	 * }
	 * @return array|string
	 */
	private static function get_confirmation_method( $atts ) {
		$action = FrmOnSubmitHelper::current_event( $atts );
		$opt    = 'update' === $action ? 'edit_action' : 'success_action';
		$method = ! empty( $atts['form']->options[ $opt ] ) ? $atts['form']->options[ $opt ] : 'message';

		if ( ! empty( $atts['entry_id'] ) ) {
			$met_actions = self::get_met_on_submit_actions( $atts, $action );
			if ( $met_actions ) {
				$method = $met_actions;
			}
		}

		$method = apply_filters( 'frm_success_filter', $method, $atts['form'], $action );

		if ( $method !== 'message' && ( ! $atts['entry_id'] || ! is_numeric( $atts['entry_id'] ) ) ) {
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
				'action'   => FrmOnSubmitHelper::current_event( $params ),
			)
		);

		self::maybe_trigger_redirect_with_action( $conf_method, $form, $params, $args );
	}

	/**
	 * Maybe trigger redirect with the new Confirmation action.
	 *
	 * @since 6.1.1
	 *
	 * @param array|string $conf_method Array of confirmation actions or the action type string.
	 * @param object       $form        Form object.
	 * @param array        $params      See {@see FrmFormsController::maybe_trigger_redirect()}.
	 * @param array        $args        See {@see FrmFormsController::maybe_trigger_redirect()}.
	 */
	public static function maybe_trigger_redirect_with_action( $conf_method, $form, $params, $args ) {
		if ( is_array( $conf_method ) && 1 === count( $conf_method ) ) {
			if ( 'redirect' === FrmOnSubmitHelper::get_action_type( $conf_method[0] ) ) {
				$event = FrmOnSubmitHelper::current_event( $params );
				FrmOnSubmitHelper::populate_on_submit_data( $form->options, $conf_method[0], $event );
				$conf_method = 'redirect';
			}
		}

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
	 * @since 6.0 `$args['force_delay_redirect']` is added.
	 *
	 * @param array $args {
	 *     The args.
	 *
	 *     @type string $conf_method          The method.
	 *     @type object $form                 Form object.
	 *     @type int    $entry_id             Entry ID.
	 *     @type string $action               The action event. Accepts `create` or `update`.
	 *     @type array  $fields               The array of fields.
	 *     @type int    $force_delay_redirect Force to show the message before redirecting in case redirect method runs.
	 * }
	 */
	public static function run_success_action( $args ) {
		global $frm_vars;
		$extra_args = $args;
		unset( $extra_args['form'] );

		do_action( 'frm_success_action', $args['conf_method'], $args['form'], $args['form']->options, $args['entry_id'], $extra_args );

		$opt = ! isset( $args['action'] ) || $args['action'] === 'create' ? 'success' : 'edit';

		$args['success_opt'] = $opt;
		$args['ajax']        = ! empty( $frm_vars['ajax'] );

		if ( $args['conf_method'] === 'page' && is_numeric( $args['form']->options[ $opt . '_page_id' ] ) ) {
			self::load_page_after_submit( $args );
		} elseif ( $args['conf_method'] === 'redirect' ) {
			self::redirect_after_submit( $args );
		} else {
			self::show_message_after_save( $args );
		}
	}

	/**
	 * Gets met On Submit actions.
	 *
	 * @since 6.0
	 *
	 * @param array  $args  See {@see FrmFormsController::run_success_action()}.
	 * @param string $event Form event. Default is `create`.
	 * @return array Array of actions that meet the conditional logics.
	 */
	public static function get_met_on_submit_actions( $args, $event = 'create' ) {
		if ( ! FrmOnSubmitHelper::form_has_migrated( $args['form'] ) ) {
			return array();
		}

		// If a redirect action has already opened the URL in a new tab, we show the default message in the currect tab.
		if ( ! empty( self::$redirected_in_new_tab[ $args['form']->id ] ) ) {
			return array( FrmOnSubmitHelper::get_fallback_action_after_open_in_new_tab( $event ) );
		}

		$entry        = FrmEntry::getOne( $args['entry_id'], true );
		$actions      = FrmOnSubmitHelper::get_actions( $args['form']->id );
		$met_actions  = array();
		$has_redirect = false;

		foreach ( $actions as $action ) {
			if ( ! in_array( $event, $action->post_content['event'], true ) ) {
				continue;
			}

			if ( FrmFormAction::action_conditions_met( $action, $entry ) ) {
				continue;
			}

			$action_type = FrmOnSubmitHelper::get_action_type( $action );

			if ( 'redirect' === $action_type ) {
				if ( $has_redirect ) {
					// Do not process because we run the first redirect action only.
					continue;
				}
			}

			if ( ! self::is_valid_on_submit_action( $action, $args, $event ) ) {
				continue;
			}

			if ( 'redirect' === $action_type ) {
				$has_redirect = true;
			}

			$met_actions[] = $action;
			unset( $action );
		}//end foreach

		$args['event'] = $event;

		/**
		 * Filters the On Submit actions that meet the conditional logics.
		 *
		 * @since 6.0
		 *
		 * @param array $met_actions Actions that meet the conditional logics.
		 * @param array $args        See {@see FrmFormsController::run_success_action()}. `$args['event']` is also added.
		 */
		$met_actions = apply_filters( 'frm_get_met_on_submit_actions', $met_actions, $args );

		if ( empty( $met_actions ) ) {
			$met_actions = array( FrmOnSubmitHelper::get_fallback_action( $event ) );
		}

		return $met_actions;
	}

	/**
	 * Checks if a Confirmation action has the valid data.
	 *
	 * @since 6.1.2
	 *
	 * @param object $action Form action object.
	 * @param array  $args   See {@see FrmFormsController::run_success_action()}.
	 * @param string $event  Form event. Default is `create`.
	 * @return bool
	 */
	private static function is_valid_on_submit_action( $action, $args, $event = 'create' ) {
		$action_type = FrmOnSubmitHelper::get_action_type( $action );

		if ( 'redirect' === $action_type ) {
			// Run through frm_redirect_url filter. This is used for the valid action check.
			$action->post_content['success_url'] = apply_filters(
				'frm_redirect_url',
				$action->post_content['success_url'],
				$args['form'],
				$args + array( 'action' => $event )
			);

			return ! empty( $action->post_content['success_url'] );
		}

		if ( 'page' === $action_type ) {
			if ( empty( $action->post_content['success_page_id'] ) ) {
				return false;
			}

			$page = get_post( $action->post_content['success_page_id'] );
			if ( ! $page || 'trash' === $page->post_status ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Runs On Submit actions.
	 *
	 * @since 6.0
	 *
	 * @param array $args See inside {@see FrmFormsController::get_form_contents()} method.
	 */
	public static function run_on_submit_actions( $args ) {
		$args['conf_method'] = self::get_confirmation_method(
			array(
				'form'     => $args['form'],
				'entry_id' => $args['entry_id'],
				'action'   => FrmOnSubmitHelper::current_event( $args ),
			)
		);
		if ( ! is_array( $args['conf_method'] ) ) {
			self::run_success_action( $args );
			return;
		}

		// If conf_method is an array, run On Submit actions.
		if ( ! $args['conf_method'] ) {
			// Use default message.
			FrmOnSubmitHelper::populate_on_submit_data( $args['form']->options );
			self::run_success_action( $args );
		} elseif ( 1 === count( $args['conf_method'] ) ) {
			FrmOnSubmitHelper::populate_on_submit_data( $args['form']->options, reset( $args['conf_method'] ) );
			$args['conf_method'] = $args['form']->options['success_action'];
			self::run_success_action( $args );
		} else {
			self::run_multi_on_submit_actions( $args );
		}
	}

	/**
	 * Runs multiple success actions.
	 *
	 * @since 6.0
	 *
	 * @param array $args See {@see FrmFormsController::run_success_action()}.
	 */
	public static function run_multi_on_submit_actions( $args ) {
		$redirect_action = null;
		foreach ( $args['conf_method'] as $action ) {
			if ( 'redirect' === FrmOnSubmitHelper::get_action_type( $action ) ) {
				// We catch the redirect action to run it last.
				$redirect_action = $action;
				continue;
			}

			self::run_single_on_submit_action( $args, $action );

			unset( $action );
		}

		if ( $redirect_action ) {
			// Show script to delay the redirection.
			$args['force_delay_redirect'] = true;
			self::run_single_on_submit_action( $args, $redirect_action );
		}
	}

	/**
	 * Runs single On Submit action.
	 *
	 * @since 6.0
	 *
	 * @param array  $args   See {@see FrmFormsController::run_success_action()}.
	 * @param object $action On Submit action object.
	 */
	public static function run_single_on_submit_action( $args, $action ) {
		$new_args = self::get_run_success_action_args( $args, $action );
		self::run_success_action( $new_args );
	}

	/**
	 * Gets run_success_action() args from the On Submit action.
	 *
	 * @since 6.0
	 *
	 * @param array  $args   See {@see FrmFormsController::run_success_action()}.
	 * @param object $action On Submit action object.
	 * @return array
	 */
	private static function get_run_success_action_args( $args, $action ) {
		$new_args = $args;

		FrmOnSubmitHelper::populate_on_submit_data( $new_args['form']->options, $action, $args['action'] );

		$opt = 'update' === $args['action'] ? 'edit_' : 'success_';

		$new_args['conf_method'] = $new_args['form']->options[ $opt . 'action' ];

		/**
		 * Filters the run success action args.
		 *
		 * @since 6.0
		 *
		 * @param array  $new_args The new args.
		 * @param array  $args     The old args. See {@see FrmFormsController::run_success_action()}.
		 * @param object $action   On Submit action object.
		 */
		return apply_filters( 'frm_get_run_success_action_args', $new_args, $args, $action );
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

			// Fix the On Submit page content doesn't show when previewing In theme.
			$has_preview_filter = has_filter( 'the_content', 'FrmFormsController::preview_content' );

			if ( $has_preview_filter ) {
				remove_filter( 'the_content', 'FrmFormsController::preview_content', 9999 );
			}

			echo apply_filters( 'the_content', $content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			if ( $has_preview_filter ) {
				add_filter( 'the_content', 'FrmFormsController::preview_content', 9999 );
			}

			$post = $old_post;
		}//end if
	}

	/**
	 * @since 3.0
	 * @param array $args See {@see FrmFormsController::run_success_action()}.
	 */
	private static function redirect_after_submit( $args ) {
		add_filter( 'frm_use_wpautop', '__return_false' );

		$opt         = $args['success_opt'];
		$success_url = trim( $args['form']->options[ $opt . '_url' ] );
		$success_url = apply_filters( 'frm_content', $success_url, $args['form'], $args['entry_id'] );
		$success_url = do_shortcode( $success_url );

		$args['id'] = $args['entry_id'];
		FrmEntriesController::delete_entry_before_redirect( $success_url, $args['form'], $args );

		add_filter( 'frm_redirect_url', 'FrmEntriesController::prepare_redirect_url' );
		$success_url = apply_filters( 'frm_redirect_url', $success_url, $args['form'], $args );

		$doing_ajax = FrmAppHelper::doing_ajax();

		if ( ! empty( $args['ajax'] ) && $doing_ajax && empty( $args['force_delay_redirect'] ) ) {
			// Is AJAX submit and there is just one Redirect action runs.
			echo json_encode( self::get_ajax_redirect_response_data( $args + compact( 'success_url' ) ) );
			wp_die();
		}

		if ( ! headers_sent() && empty( $args['force_delay_redirect'] ) ) {
			// Not AJAX submit, no headers sent, and there is just one Redirect action runs.
			if ( ! empty( $args['form']->options['open_in_new_tab'] ) ) {
				self::print_open_in_new_tab_js_with_fallback_handler( $success_url, $args );
				self::$redirected_in_new_tab[ $args['form']->id ] = 1;
				return;
			}

			wp_redirect( esc_url_raw( $success_url ) );
			// Do not use wp_die or redirect fails.
			die();
		}

		// Redirect with a delay.
		self::redirect_after_submit_using_js( $args + compact( 'success_url', 'doing_ajax' ) );
	}

	/**
	 * Prints open in new tab js with fallback handler.
	 *
	 * @since 6.3.1
	 *
	 * @param string $success_url Success URL.
	 * @param array  $args        See {@see FrmFormsController::redirect_after_submit()}.
	 */
	private static function print_open_in_new_tab_js_with_fallback_handler( $success_url, $args ) {
		echo '<script>var newTab = window.open("' . esc_url_raw( $success_url ) . '", "_blank");';
		echo 'if ( ! newTab ) {';

		$data = array(
			'formId'  => intval( $args['form']->id ),
			'message' => self::get_redirect_fallback_message( $success_url, $args ),
		);
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo 'frmShowNewTabFallback = ' . FrmAppHelper::maybe_json_encode( $data ) . ';';
		echo '}</script>';
	}

	/**
	 * Gets response data for redirect action when AJAX submitting.
	 *
	 * @since 6.3.1
	 *
	 * @param array $args See {@see FrmFormsController::run_success_action()}.
	 * @return array
	 */
	private static function get_ajax_redirect_response_data( $args ) {
		$response_data = array( 'redirect' => $args['success_url'] );

		if ( ! empty( $args['form']->options['open_in_new_tab'] ) ) {
			$response_data['openInNewTab'] = 1;

			$args['message'] = FrmOnSubmitHelper::get_default_new_tab_msg();

			$args['form']->options['success_msg'] = $args['message'];
			$args['form']->options['edit_msg']    = $args['message'];
			if ( ! isset( $args['fields'] ) ) {
				$args['fields'] = FrmField::get_all_for_form( $args['form']->id );
			}

			$args['message'] = self::prepare_submit_message( $args['form'], $args['entry_id'], $args );

			ob_start();
			self::show_lone_success_messsage( $args );
			$response_data['content'] = ob_get_clean();

			$response_data['fallbackMsg'] = self::get_redirect_fallback_message( $args['success_url'], $args );
		}

		return $response_data;
	}

	/**
	 * Redirects after submitting using JS. This is used when showing message before redirecting.
	 *
	 * @since 6.0
	 *
	 * @param array $args See {@see FrmFormsController::run_success_action()}.
	 */
	private static function redirect_after_submit_using_js( $args ) {
		$success_msg  = FrmOnSubmitHelper::get_default_redirect_msg();
		$redirect_msg = self::get_redirect_message( $args['success_url'], $success_msg, $args );
		$success_url  = esc_url_raw( $args['success_url'] );

		/**
		 * Filters the delay time before redirecting when On Submit Redirect action is delayed.
		 *
		 * @since 6.0
		 *
		 * @param int $delay_time Delay time in miliseconds.
		 */
		$delay_time = apply_filters( 'frm_redirect_delay_time', 8000 );

		if ( ! empty( $args['form']->options['open_in_new_tab'] ) ) {
			$redirect_js = 'window.open("' . $success_url . '", "_blank")';
		} else {
			$redirect_js = 'window.location="' . $success_url . '";';
		}

		add_filter( 'frm_use_wpautop', '__return_true' );

		echo FrmAppHelper::maybe_kses( $redirect_msg ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<script>';
		if ( empty( $args['doing_ajax'] ) ) {
			// Not AJAX submit, delay JS until window.load.
			echo 'window.onload=function(){';
		}
		echo 'setTimeout(function(){' . $redirect_js . '}, ' . intval( $delay_time ) . ');'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( empty( $args['doing_ajax'] ) ) {
			echo '};';
		}
		echo '</script>';
	}

	/**
	 * @since 3.0
	 *
	 * @param string $success_url
	 * @param string $success_msg
	 * @param array  $args
	 */
	private static function get_redirect_message( $success_url, $success_msg, $args ) {
		$redirect_msg = '<div class="' . esc_attr( FrmFormsHelper::get_form_style_class( $args['form'] ) ) . '"><div class="frm-redirect-msg" role="status">' . $success_msg . '<br/>' .
			self::get_redirect_fallback_message( $success_url, $args ) .
			'</div></div>';

		$redirect_args = array(
			'entry_id' => $args['entry_id'],
			'form_id'  => $args['form']->id,
			'form'     => $args['form'],
		);

		return apply_filters( 'frm_redirect_msg', $redirect_msg, $redirect_args );
	}

	/**
	 * Gets fallback message when redirecting failed.
	 *
	 * @since 6.3.1
	 *
	 * @param string $success_url Redirect URL.
	 * @param array  $args        Contains `form` object.
	 * @return string
	 */
	private static function get_redirect_fallback_message( $success_url, $args ) {
		$target = '';
		if ( ! empty( $args['form']->options['open_in_new_tab'] ) ) {
			$target = ' target="_blank"';
		}

		return sprintf(
			/* translators: %1$s: Start link HTML, %2$s: End link HTML */
			__( '%1$sClick here%2$s if you are not automatically redirected.', 'formidable' ),
			'<a href="' . esc_url( $success_url ) . '"' . $target . '>',
			'</a>'
		);
	}

	/**
	 * Prepare to show the success message and empty form after submit
	 *
	 * @since 2.05
	 */
	public static function show_message_after_save( $atts ) {
		$atts['message'] = self::prepare_submit_message( $atts['form'], $atts['entry_id'], $atts );

		if ( ! isset( $atts['form']->options['show_form'] ) || $atts['form']->options['show_form'] ) {
			if ( isset( $atts['action'] ) && 'update' === $atts['action'] && is_callable( array( 'FrmProEntriesController', 'show_front_end_form_with_entry' ) ) ) {
				$entry = FrmEntry::getOne( $atts['entry_id'] );
				if ( $entry ) {
					// This is copied from the Pro plugin.
					$atts['conf_message'] = FrmProEntriesController::confirmation( 'message', $atts['form'], $atts['form']->options, $entry->id, $atts );
					$atts['show_form']    = FrmProEntriesController::is_form_displayed_after_edit( $atts['form'] );
					FrmProEntriesController::show_front_end_form_with_entry( $entry, $atts );
				}
			} else {
				self::show_form_after_submit( $atts );
			}
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
	 * @since 4.05.02
	 * @return string - 'before', 'after', or 'submit'
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
		 * @since 4.05.02
		 * @return string - 'before' or 'after'
		 */
		return apply_filters( 'frm_message_placement', $place, compact( 'form', 'message' ) );
	}

	/**
	 * Get all the values needed on the new.php entry page
	 *
	 * @since 2.05
	 */
	private static function fill_atts_for_form_display( &$args ) {
		if ( ! isset( $args['title'] ) && isset( $args['show_title'] ) ) {
			$args['title'] = $args['show_title'];
		}

		if ( ! isset( $args['description'] ) && isset( $args['show_description'] ) ) {
			$args['description'] = $args['show_description'];
		}

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

		include FrmAppHelper::plugin_path() . '/classes/views/frm-entries/errors.php';
	}

	/**
	 * Prepare the success message before it's shown
	 *
	 * @since 2.05
	 * @since 6.0.x Added the third parameter.
	 *
	 * @param object $form     Form object.
	 * @param int    $entry_id Entry ID.
	 * @param array  $args     See {@see FrmFormsController::run_success_action()}.
	 * @return string
	 */
	private static function prepare_submit_message( $form, $entry_id, $args = array() ) {
		$frm_settings = FrmAppHelper::get_settings( array( 'current_form' => $form->id ) );
		$opt          = isset( $args['success_opt'] ) ? $args['success_opt'] : 'success';

		if ( $entry_id && is_numeric( $entry_id ) ) {
			$message = isset( $form->options[ $opt . '_msg' ] ) ? $form->options[ $opt . '_msg' ] : $frm_settings->success_msg;
			$class   = 'frm_message';
		} else {
			$message = $frm_settings->failed_msg;
			$class   = FrmFormsHelper::form_error_class();
		}

		$message = FrmFormsHelper::get_success_message( compact( 'message', 'form', 'entry_id', 'class' ) );

		return apply_filters( 'frm_main_feedback', $message, $form, $entry_id );
	}

	/**
	 * @return void
	 */
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
		if ( 'captcha-api' == $handle && ! strpos( $tag, 'defer' ) ) {
			$tag = str_replace( ' src', ' defer="defer" async="async" src', $tag );
		}

		return $tag;
	}

	public static function footer_js( $location = 'footer' ) {
		global $frm_vars;

		FrmStylesController::enqueue_css();

		if ( ! FrmAppHelper::is_admin() && $location !== 'header' && ! empty( $frm_vars['forms_loaded'] ) ) {
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
	 * @return bool
	 */
	private static function is_minification_on( $atts ) {
		return ! empty( $atts['minimize'] );
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
	 * @return void
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
	 * @param int $form_id
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
	 * @return void
	 */
	public static function get_page_dropdown() {
		if ( ! current_user_can( 'publish_posts' ) ) {
			die( 0 );
		}

		check_ajax_referer( 'frm_ajax', 'nonce' );

		$html             = FrmAppHelper::clip(
			function () {
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
	public static function create( $values = array() ) {
		_deprecated_function( __METHOD__, '4.0', 'FrmFormsController::update' );
		self::update( $values );
	}

	/**
	 * @deprecated 6.7
	 *
	 * @return bool
	 */
	public static function expired() {
		_deprecated_function( __METHOD__, '6.7' );
		return FrmAddonsController::is_license_expired();
	}
}
