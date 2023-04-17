<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmStylesController {

	/**
	 * @var string $post_type
	 */
	public static $post_type = 'frm_styles';

	/**
	 * @var string $screen
	 */
	public static $screen = 'formidable_page_formidable-styles';

	/**
	 * @var string|null $message
	 */
	private static $message;

	public static function load_pro_hooks() {
		if ( FrmAppHelper::pro_is_installed() ) {
			FrmProStylesController::load_pro_hooks();
		}
	}

	public static function register_post_types() {
		register_post_type(
			self::$post_type,
			array(
				'label'           => __( 'Styles', 'formidable' ),
				'public'          => false,
				'show_ui'         => false,
				'capability_type' => 'page',
				'capabilities'    => array(
					'edit_post'          => 'frm_change_settings',
					'edit_posts'         => 'frm_change_settings',
					'edit_others_posts'  => 'frm_change_settings',
					'publish_posts'      => 'frm_change_settings',
					'delete_post'        => 'frm_change_settings',
					'delete_posts'       => 'frm_change_settings',
					'read_private_posts' => 'read_private_posts',
				),
				'supports'        => array(
					'title',
				),
				'has_archive'     => false,
				'labels'          => array(
					'name'          => __( 'Styles', 'formidable' ),
					'singular_name' => __( 'Style', 'formidable' ),
					'menu_name'     => __( 'Style', 'formidable' ),
					'edit'          => __( 'Edit', 'formidable' ),
					'add_new_item'  => __( 'Create a New Style', 'formidable' ),
					'edit_item'     => __( 'Edit Style', 'formidable' ),
				),
			)
		);
	}

	/**
	 * Add two links for the visual styler.
	 * There's a "Styles" submenu in the Formidable menu.
	 * There's a second alternative "Forms" submenu in the Appearance menu.
	 * This submenu links to a page to edit a form with the default style.
	 *
	 * @return void
	 */
	public static function menu() {
		add_submenu_page( 'formidable', 'Formidable | ' . __( 'Styles', 'formidable' ), __( 'Styles', 'formidable' ), 'frm_change_settings', 'formidable-styles', 'FrmStylesController::route' );
		add_submenu_page( 'themes.php', 'Formidable | ' . __( 'Styles', 'formidable' ), __( 'Forms', 'formidable' ), 'frm_change_settings', 'formidable-styles2', 'FrmStylesController::route' );
	}

	/**
	 * Remove filters for the visual styler preview.
	 * This triggers earlier than self::admin_init which is called too late.
	 *
	 * @return void
	 */
	public static function plugins_loaded() {
		if ( ! FrmAppHelper::is_style_editor_page() ) {
			return;
		}

		self::disable_form_css();
		self::prevent_form_scripts_from_loading();
	}

	/**
	 * Avoid loading CSS the normal way with the preview in the styler.
	 * It gets loaded instead with the frmpro_css action set to the #frm-custom-theme-css element.
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private static function disable_form_css() {
		add_filter( 'get_frm_stylesheet', '__return_false' );
	}

	/**
	 * Removing this action prevents front end JavaScript from loading.
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private static function prevent_form_scripts_from_loading() {
		remove_action( 'init', 'FrmFormsController::front_head' );
	}

	/**
	 * @return void
	 */
	public static function admin_init() {
		self::maybe_hook_into_global_settings_save();

		if ( ! FrmAppHelper::is_style_editor_page() ) {
			return;
		}

		self::load_pro_hooks();

		$version = FrmAppHelper::plugin_version();
		wp_enqueue_script( 'jquery-ui-datepicker' );

		if ( FrmAppHelper::is_style_editor_page( 'edit' ) ) {
			wp_enqueue_style( 'wp-color-picker' );
		}

		wp_enqueue_style( 'frm-custom-theme', admin_url( 'admin-ajax.php?action=frmpro_css' ), array(), $version );

		$style = apply_filters( 'frm_style_head', false );
		if ( $style ) {
			wp_enqueue_style( 'frm-single-custom-theme', admin_url( 'admin-ajax.php?action=frmpro_load_css&flat=1' ) . '&' . http_build_query( $style->post_content ), array(), $version );
		}
	}

	/**
	 * @since 6.0
	 *
	 * @return void
	 */
	private static function maybe_hook_into_global_settings_save() {
		if ( empty( $_POST ) || ! isset( $_POST['style'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Avoid changing any style data if the style array is not sent in the request.
			return;
		}

		add_action(
			'frm_update_settings',
			/**
			 * Update the form data on the "Manage Styles" tab after global settings are saved.
			 */
			function() {
				self::manage_styles();
			}
		);
	}

	/**
	 * @param string $register Either 'enqueue' or 'register'.
	 * @param bool   $force True to enqueue/register the style if a form has not been loaded.
	 * @return void
	 */
	public static function enqueue_css( $register = 'enqueue', $force = false ) {
		global $frm_vars;

		$register_css = $register === 'register';
		$should_load  = $force || ( ( $frm_vars['load_css'] || $register_css ) && ! FrmAppHelper::is_admin() );

		if ( ! $should_load ) {
			return;
		}

		$frm_settings = FrmAppHelper::get_settings();
		if ( $frm_settings->load_style === 'none' ) {
			return;
		}

		$css = apply_filters( 'get_frm_stylesheet', self::custom_stylesheet() );

		if ( $css ) {
			$css     = (array) $css;
			$version = FrmAppHelper::plugin_version();

			foreach ( $css as $css_key => $file ) {
				if ( $register_css ) {
					$this_version = self::get_css_version( $css_key, $version );
					wp_register_style( $css_key, $file, array(), $this_version );
				}

				$load_on_all = ! FrmAppHelper::is_admin() && 'all' === $frm_settings->load_style;
				if ( $load_on_all || $register != 'register' ) {
					wp_enqueue_style( $css_key );
				}
				unset( $css_key, $file );
			}

			if ( $frm_settings->load_style === 'all' ) {
				$frm_vars['css_loaded'] = true;
			}
		}
		unset( $css );

		add_filter( 'style_loader_tag', 'FrmStylesController::add_tags_to_css', 10, 2 );
	}

	/**
	 * @return array<string,string>
	 */
	public static function custom_stylesheet() {
		global $frm_vars;
		$stylesheet_urls = array();

		if ( empty( $frm_vars['css_loaded'] ) ) {
			// Include css in head.
			self::get_url_to_custom_style( $stylesheet_urls );
		}

		return $stylesheet_urls;
	}

	/**
	 * @param array $stylesheet_urls
	 * @return void
	 */
	private static function get_url_to_custom_style( &$stylesheet_urls ) {
		$file_name = '/css/' . self::get_file_name();
		if ( is_readable( FrmAppHelper::plugin_path() . $file_name ) ) {
			$url = FrmAppHelper::plugin_url() . $file_name;
		} else {
			$url = admin_url( 'admin-ajax.php?action=frmpro_css' );
		}
		$stylesheet_urls['formidable'] = $url;
	}

	/**
	 * Use a different stylesheet per site in a multisite install
	 *
	 * @since 3.0.03
	 */
	public static function get_file_name() {
		if ( is_multisite() ) {
			$blog_id = get_current_blog_id();
			$name    = 'formidableforms' . absint( $blog_id ) . '.css';
		} else {
			$name = 'formidableforms.css';
		}

		return $name;
	}

	private static function get_css_version( $css_key, $version ) {
		if ( 'formidable' == $css_key ) {
			$this_version = get_option( 'frm_last_style_update' );
			if ( ! $this_version ) {
				$this_version = $version;
			}
		} else {
			$this_version = $version;
		}

		return $this_version;
	}

	public static function add_tags_to_css( $tag, $handle ) {
		if ( ( 'formidable' == $handle || 'jquery-theme' == $handle ) && strpos( $tag, ' property=' ) === false ) {
			$frm_settings = FrmAppHelper::get_settings();
			if ( $frm_settings->use_html ) {
				$tag = str_replace( ' type="', ' property="stylesheet" type="', $tag );
			}
		}

		return $tag;
	}

	/**
	 * Route the edit route to the style function.
	 *
	 * @since 6.0 this function no longer has any parameter values.
	 *
	 * @return void
	 */
	public static function edit() {
		self::load_styler();
	}

	/**
	 * When a new style route is hit, show a new style with the defaults. There is no ID yet, it is created after the first save event.
	 *
	 * @return void
	 */
	public static function new_style() {
		self::load_styler();
	}

	/**
	 * When the duplicate route is hit, it just shows the visual styler with a copy of the target style. There is no ID yet, it is created after the first save event.
	 *
	 * @return void
	 */
	public static function duplicate() {
		self::load_styler();
	}

	/**
	 * Render the style page for a form for assigning a style to a form, and for updating a target style.
	 *
	 * @since 6.0 this function no longer takes parameters.
	 *
	 * @return void
	 */
	public static function load_styler() {
		if ( 'assign_style' === FrmAppHelper::get_post_param( 'frm_action' ) ) {
			self::save_form_style();
		}

		self::setup_styles_and_scripts_for_styler();

		$style_id = self::get_style_id_for_styler();
		if ( ! $style_id ) {
			wp_die( esc_html__( 'Invalid route', 'formidable' ), esc_html__( 'Invalid route', 'formidable' ), 400 );
		}

		$form_id = FrmAppHelper::simple_get( 'form', 'absint', 0 );
		if ( ! $form_id ) {
			$form_id = self::get_form_id_for_style( $style_id );
		}

		$form = FrmForm::getOne( $form_id );
		if ( ! is_object( $form ) ) {
			wp_die( esc_html__( 'Invalid route', 'formidable' ), esc_html__( 'Invalid route', 'formidable' ), 400 );
		}

		$frm_style     = new FrmStyle( $style_id );
		$active_style  = $frm_style->get_one();
		$default_style = self::get_default_style();

		self::disable_admin_page_styling_on_submit_buttons();

		/**
		 * @since 6.0
		 *
		 * @param array {
		 *     @type stdClass $form
		 * }
		 */
		do_action( 'frm_before_render_style_page', compact( 'form' ) );

		self::render_style_page( $active_style, $form, $default_style );
	}

	/**
	 * @since 6.0
	 *
	 * @return int
	 */
	private static function get_style_id_for_styler() {
		$action = FrmAppHelper::simple_get( 'frm_action' );
		if ( 'duplicate' === $action ) {
			// The duplicate action uses style_id instead of id for better backward compatibility.
			return FrmAppHelper::simple_get( 'style_id', 'absint', 0 );
		}

		$style_id = FrmAppHelper::simple_get( 'id', 'absint', 0 );
		if ( $style_id ) {
			// Always use the style ID from the URL if one is specified.
			return $style_id;
		}

		$request_form_id = FrmAppHelper::simple_get( 'form', 'absint', 0 );
		if ( $request_form_id && is_callable( 'FrmProStylesController::get_active_style_for_form' ) ) {
			return FrmProStylesController::get_active_style_for_form( $request_form_id )->ID;
		}

		return self::get_default_style()->ID;
	}

	/**
	 * If a form ID is not being passed in the URL, try to get the best match.
	 *
	 * @since 6.0
	 *
	 * @param int $style_id
	 * @return int
	 */
	private static function get_form_id_for_style( $style_id ) {
		$check   = serialize( array( 'custom_style' => (string) $style_id ) );
		$check   = substr( $check, 5, -1 );
		$form_id = FrmDb::get_var(
			'frm_forms',
			array(
				'options LIKE' => $check,
				'status'       => 'published',
			)
		);

		if ( ! $form_id ) {
			// TODO: Show a message why a random form is being shown (because no form is assigned to the style).
			// Fallback to any form.
			$where = array(
				'status'         => 'published',
				'parent_form_id' => array( null, 0 ), // Make sure it's not a repeater.
			);
			$form_id = FrmDb::get_var( 'frm_forms', $where, 'id' );
		}

		return $form_id;
	}

	/**
	 * Add a frm_no_style_button class to all buttons to avoid some style rules like border-radius: 30px.
	 *
	 * @return void
	 */
	private static function disable_admin_page_styling_on_submit_buttons() {
		add_filter(
			'frm_submit_button_class',
			function( $classes ) {
				$classes[] = 'frm_no_style_button';
				return $classes;
			}
		);
	}

	/**
	 * @since 6.0
	 *
	 * @return WP_Post
	 */
	private static function get_default_style() {
		$frm_style     = new FrmStyle( 'default' );
		$default_style = $frm_style->get_one();
		return $default_style;
	}

	/**
	 * Save style for form (from Styler list page) via a POST action.
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private static function save_form_style() {
		$permission_error = FrmAppHelper::permission_nonce_error( 'frm_edit_forms', 'frm_save_form_style', 'frm_save_form_style_nonce' );
		if ( $permission_error !== false ) {
			wp_die( 'Unable to save form', '', 403 );
		}

		$style_id = FrmAppHelper::get_post_param( 'style_id', 0, 'absint' );
		if ( $style_id && ! self::confirm_style_exists_before_setting( $style_id ) ) {
			wp_die( esc_html__( 'Invalid target style', 'formidable' ), esc_html__( 'Invalid target style', 'formidable' ), 400 );
			return;
		}

		/**
		 * Hook into the saved style ID so Pro can import a style template by its key and return a new style ID.
		 *
		 * @since 6.0
		 *
		 * @param int $style_id
		 */
		$style_id = apply_filters( 'frm_saved_form_style_id', $style_id );

		if ( ! $style_id && '0' !== FrmAppHelper::get_post_param( 'style_id', 'sanitize_text_field', '' ) ) {
			// "0" is a special value used for the enable/disable toggle.
			wp_die( esc_html__( 'Invalid style value', 'formidable' ), esc_html__( 'Invalid style value', 'formidable' ), 400 );
			return;
		}

		$form_id = FrmAppHelper::get_post_param( 'form_id', 'absint', 0 );
		if ( ! $form_id ) {
			wp_die( esc_html__( 'No form specified', 'formidable' ), esc_html__( 'No form specified', 'formidable' ), 400 );
			return;
		}

		$form = FrmForm::getOne( $form_id );
		if ( ! $form ) {
			wp_die( esc_html__( 'Form does not exist', 'formidable' ), esc_html__( 'Form does not exist', 'formidable' ), 400 );
			return;
		}

		// If the default style is selected, use the "Always use default" legacy option instead of the default style.
		// There's also a check here for conversational forms.
		// Without the check it isn't possible to select "Default" because "Always use default" will convert to "Lines" dynamically.
		$default_style = self::get_default_style();
		if ( $style_id === $default_style->ID && empty( $form->options['chat'] ) ) {
			$style_id = 1;
		}

		$form->options['custom_style'] = (string) $style_id; // We want to save a string for consistency. FrmStylesHelper::get_form_count_for_style expects the custom style ID is a string.

		global $wpdb;
		$wpdb->update( $wpdb->prefix . 'frm_forms', array( 'options' => maybe_serialize( $form->options ) ), array( 'id' => $form->id ) );

		FrmForm::clear_form_cache();

		self::$message = __( 'Successfully updated style.', 'formidable' );
	}

	/**
	 * Validate that we're assigning a form to a style that actually exists before assigning it to a form.
	 *
	 * @param int $style_id
	 * @return bool True if the style actually exists.
	 */
	private static function confirm_style_exists_before_setting( $style_id ) {
		global $wpdb;
		$post_type = FrmDb::get_var( $wpdb->posts, array( 'ID' => $style_id ), 'post_type' );
		return self::$post_type === $post_type;
	}

	/**
	 * Register and enqueue styles and scripts for the style tab page.
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	private static function setup_styles_and_scripts_for_styler() {
		$plugin_url      = FrmAppHelper::plugin_url();
		$version         = FrmAppHelper::plugin_version();
		$js_dependencies = array( 'wp-i18n', 'wp-hooks', 'formidable_dom' );

		if ( FrmAppHelper::pro_is_installed() ) {
			$js_dependencies[] = 'jquery-ui-datepicker';
		}

		wp_register_script( 'formidable_style', $plugin_url . '/js/admin/style.js', $js_dependencies, $version );
		wp_register_style( 'formidable_style', $plugin_url . '/css/admin/style.css', array(), $version );
		wp_print_styles( 'formidable_style' );

		wp_print_styles( 'formidable' );
		wp_enqueue_script( 'formidable_style' );
	}

	/**
	 * Render the style page (with a more limited and typed scope than calling it from self::style directly).
	 *
	 * @since 6.0
	 *
	 * @param stdClass|WP_Post $active_style
	 * @param array<WP_Post>   $styles
	 * @param stdClass         $form
	 * @param WP_Post          $default_style
	 * @return void
	 */
	private static function render_style_page( $active_style, $form, $default_style ) {
		$style_views_path = self::get_views_path();
		$view             = FrmAppHelper::simple_get( 'frm_action', 'sanitize_text_field', 'list' ); // edit, list (default), new_style.
		$frm_style        = new FrmStyle( $active_style->ID );

		if ( 'new_style' !== $view && ! FrmAppHelper::simple_get( 'form' ) && ! FrmAppHelper::simple_get( 'style_id' ) ) {
			$view = 'edit'; // Have the Appearance > Forms link fallback to the edit view. Otherwise we want to use 'list' as the default.
		}

		if ( in_array( $view, array( 'edit', 'new_style', 'duplicate' ), true ) ) {
			self::add_meta_boxes();
		}

		switch ( $view ) {
			case 'edit':
				$style = $active_style;
				break;

			case 'duplicate':
				$style            = clone $active_style;
				$new_style        = $frm_style->get_new();
				$style->ID        = $new_style->ID;
				$style->post_name = $new_style->post_name;
				unset( $new_style );
				break;

			case 'new_style':
				$style = $frm_style->get_new();
				break;
		}

		if ( in_array( $view, array( 'duplicate', 'new_style' ), true ) ) {
			$style->post_title = FrmAppHelper::simple_get( 'style_name' );
			$style->menu_order = 0;
		}

		if ( ! isset( $style ) ) {
			$style = $active_style;
		}

		self::force_form_style( $style );

		if ( isset( self::$message ) ) {
			$message = self::$message;
		}

		$preview_helper = new FrmStylesPreviewHelper( $form->id );
		$preview_helper->adjust_form_for_preview();

		// Get form HTML before displaying warnings and notes so we can check global $frm_vars data without adding extra database calls.
		$target_form_preview_html = $preview_helper->get_html_for_form_preview();
		$warnings                 = $preview_helper->get_warnings_for_styler_preview( $style, $default_style, $view );
		$notes                    = $preview_helper->get_notes_for_styler_preview();

		include $style_views_path . 'show.php';
	}

	/**
	 * @since 6.0
	 *
	 * @return string
	 */
	private static function get_views_path() {
		return FrmAppHelper::plugin_path() . '/classes/views/styles/';
	}

	/**
	 * Filter form classes so the form uses the preview style, not the form's active style.
	 *
	 * @since 6.0
	 *
	 * @param WP_Post|stdClass $style A new style is not a WP_Post object.
	 * @return void
	 */
	private static function force_form_style( $style ) {
		add_filter(
			'frm_add_form_style_class',
			function( $class ) use ( $style ) {
				$split = array_filter(
					explode( ' ', $class ),
					/**
					 * @param string $class
					 */
					function( $class ) {
						return $class && 0 !== strpos( $class, 'frm_style_' );
					}
				);
				$split[] = 'frm_style_' . $style->post_name;
				return implode( ' ', $split );
			}
		);
	}

	/**
	 * Save style post object via a POST request submitted from the Visual styler "edit" page.
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public static function save_style() {
		$frm_style   = new FrmStyle();
		$message     = '';
		$post_id     = FrmAppHelper::get_post_param( 'ID', false, 'sanitize_title' );
		$style_nonce = FrmAppHelper::get_post_param( 'frm_style', '', 'sanitize_text_field' );

		if ( $post_id === false || ! wp_verify_nonce( $style_nonce, 'frm_style_nonce' ) ) {
			// Exit early if the request isn't valid.
			// Since we're not dying, it should just reload the visual styler without any message.
			return;
		}

		$id = $frm_style->update( $post_id );
		if ( ! $post_id && $id ) {
			self::maybe_redirect_after_save( $id );
			$post_id = reset( $id ); // Set the post id to the new style so it will be loaded for editing.
		}

		self::$message = __( 'Your styling settings have been saved.', 'formidable' );
	}

	/**
	 * Show the edit view after saving.
	 * The save event is triggered earlier, on admin init where self::save_style is called.
	 * This happens earlier because there is a possible redirect (to adjust the URL for a new or duplicated style).
	 *
	 * @return void
	 */
	public static function save() {
		self::edit();
	}

	/**
	 * Force a redirect after duplicating or creating a new style to avoid an old stale URL that could result in more styles than intended.
	 *
	 * @since 6.0
	 *
	 * @param array $ids
	 * @return void
	 */
	private static function maybe_redirect_after_save( $ids ) {
		$referer = FrmAppHelper::get_server_value( 'HTTP_REFERER' );
		$parsed  = parse_url( $referer );
		$query   = $parsed['query'];

		$current_action      = false;
		$actions_to_redirect = array( 'duplicate', 'new_style' );
		foreach ( $actions_to_redirect as $action ) {
			if ( false !== strpos( $query, 'frm_action=' . $action ) ) {
				$current_action = $action;
				break;
			}
		}

		if ( false === $current_action ) {
			// Do not redirect as the referer URL did not match $actions_to_redirect.
			return;
		}

		parse_str( $query, $parsed_query );
		$form_id      = ! empty( $parsed_query['form'] ) ? absint( $parsed_query['form'] ) : 0;

		$style     = new stdClass();
		$style->ID = end( $ids );
		wp_safe_redirect( esc_url_raw( FrmStylesHelper::get_edit_url( $style, $form_id ) ) );
		die();
	}

	/**
	 * @since 6.0
	 *
	 * @param string       $message
	 * @param array|object $forms
	 * @return void
	 */
	public static function manage( $message = '', $forms = array() ) {
		$frm_style     = new FrmStyle();
		$styles        = $frm_style->get_all();
		$default_style = $frm_style->get_default_style( $styles );

		if ( ! $forms ) {
			$forms = FrmForm::get_published_forms();
		}

		include FrmAppHelper::plugin_path() . '/classes/views/styles/manage.php';
	}

	/**
	 * Handle saving for the page rendered in self::manage which is included in Global Settings in the "Manage Styles" tab.
	 * This gets called from the frm_update_settings hook which is called after saving Global settings.
	 *
	 * @return void
	 */
	private static function manage_styles() {
		global $wpdb;

		$forms = FrmForm::get_published_forms();
		foreach ( $forms as $form ) {
			$new_style      = ( isset( $_POST['style'] ) && isset( $_POST['style'][ $form->id ] ) ) ? sanitize_text_field( wp_unslash( $_POST['style'][ $form->id ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$previous_style = ( isset( $_POST['prev_style'] ) && isset( $_POST['prev_style'][ $form->id ] ) ) ? sanitize_text_field( wp_unslash( $_POST['prev_style'][ $form->id ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( $new_style == $previous_style ) {
				continue;
			}

			$form->options['custom_style'] = $new_style;
			$wpdb->update( $wpdb->prefix . 'frm_forms', array( 'options' => maybe_serialize( $form->options ) ), array( 'id' => $form->id ) );
			unset( $form );
		}
	}

	/**
	 * Echo content for the Custom CSS page.
	 *
	 * @param string $message
	 * @return void
	 */
	public static function custom_css( $message = '' ) {
		$settings   = self::enqueue_codemirror();
		$id         = $settings ? 'frm_codemirror_box' : 'frm_custom_css_box';
		$custom_css = self::get_custom_css();

		include FrmAppHelper::plugin_path() . '/classes/views/styles/custom_css.php';
	}

	/**
	 * Get custom CSS code entered in the Custom CSS page.
	 *
	 * @since 6.0
	 *
	 * @return string
	 */
	public static function get_custom_css() {
		$settings = FrmAppHelper::get_settings();
		if ( is_string( $settings->custom_css ) ) {
			return $settings->custom_css;
		}

		// If it does not exist, check the default style as a fallback.
		$frm_style  = new FrmStyle();
		$style      = $frm_style->get_default_style();
		$custom_css = $style->post_content['custom_css'];

		return $custom_css;
	}

	/**
	 * Enqueue assets for codemirror, built into WordPress since 4.9.
	 * The Custom CSS page uses codemirror.
	 *
	 * @since 6.0 Previously this code was embedded in self::custom_css.
	 *
	 * @return array|false
	 */
	private static function enqueue_codemirror() {
		if ( ! function_exists( 'wp_enqueue_code_editor' ) ) {
			// The WordPress version is likely older than 4.9.
			return false;
		}

		$settings = wp_enqueue_code_editor(
			array(
				'type'       => 'text/css',
				'codemirror' => array(
					'indentUnit'  => 2,
					'tabSize'     => 2,
					// As the codemirror box only appears once you click into the Custom CSS tab, we need to auto-refresh.
					// Otherwise the line numbers all end up with a 1px width causing overlap issues with the text in the content.
					'autoRefresh' => true,
				),
			)
		);

		if ( $settings ) {
			wp_add_inline_script(
				'code-editor',
				sprintf(
					'jQuery( function() { wp.codeEditor.initialize( \'frm_codemirror_box\', %s ); } );',
					wp_json_encode( $settings )
				)
			);
		}

		return $settings;
	}

	/**
	 * Handling routing for the visual styler.
	 *
	 * @return void
	 */
	public static function route() {
		$action = FrmAppHelper::get_param( 'frm_action', '', 'get', 'sanitize_title' );
		FrmAppHelper::include_svg();

		switch ( $action ) {
			case 'edit':
			case 'save':
				self::$action();
				return;
			default:
				do_action( 'frm_style_action_route', $action );

				if ( apply_filters( 'frm_style_stop_action_route', false, $action ) ) {
					return;
				}

				if ( in_array( $action, array( 'new_style', 'duplicate' ), true ) ) {
					self::$action();
					return;
				}

				self::edit();
				return;
		}
	}

	/**
	 * Handle AJAX routing for frm_settings_reset for resetting styles to the default settings.
	 * From the edit view, it will return default styles and not actually update the style.
	 * On the list view, it does update the style immediately, and returns the default card style attributes so the style card can be reset as well.
	 *
	 * @since 6.0 When a style_id is passed to this action, the style will actually be reset.
	 *
	 * @return void
	 */
	public static function reset_styling() {
		FrmAppHelper::permission_check( 'frm_change_settings' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$style_id = FrmAppHelper::get_post_param( 'style_id', '', 'absint' );
		if ( ! $style_id ) {
			// A style ID is not sent when resetting on the edit page.
			// Instead of resetting the style, send the defaults back so the inputs can be updated with JavaScript.
			$frm_style = new FrmStyle();
			$defaults  = $frm_style->get_defaults();
			echo json_encode( $defaults );
			wp_die();
		}

		$frm_style            = new FrmStyle();
		$defaults             = $frm_style->get_defaults();
		$default_post_content = FrmAppHelper::prepare_and_encode( $defaults );
		$where                = array(
			'ID'        => $style_id,
			'post_type' => self::$post_type,
		);
		global $wpdb;
		$wpdb->update( $wpdb->posts, array( 'post_content' => $default_post_content ), $where );

		$frm_style->save_settings(); // Save the settings after resetting to default or the old style will still appear.

		$data = array(
			'style' => FrmStylesCardHelper::get_style_param_for_card( $frm_style->get_new() ),
		);
		wp_send_json_success( $data );
		wp_die();
	}

	/**
	 * Handle routing for the frm_change_styling AJAX action.
	 * This doesn't actually change styling. It just handles the events when someone changes a style.
	 * It responds with the new CSS required for the updated styler preview in the edit page.
	 *
	 * @return void
	 */
	public static function change_styling() {
		FrmAppHelper::permission_check( 'frm_change_settings' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$frm_style = new FrmStyle();
		$defaults  = false; // Intentionally avoid defaults here so nothing gets removed from our style.
		$style     = '';

		echo '<style type="text/css">';
		include FrmAppHelper::plugin_path() . '/css/_single_theme.css.php';
		echo '</style>';
		wp_die();
	}

	/**
	 * @return void
	 */
	public static function add_meta_boxes() {
		// setup meta boxes
		$meta_boxes = array(
			'general'                => __( 'General', 'formidable' ),
			'form-title'             => __( 'Form Title', 'formidable' ),
			'form-description'       => __( 'Form Description', 'formidable' ),
			'field-labels'           => __( 'Field Labels', 'formidable' ),
			'field-description'      => __( 'Field Description', 'formidable' ),
			'field-colors'           => __( 'Field Colors', 'formidable' ),
			'field-sizes'            => __( 'Field Settings', 'formidable' ),
			'check-box-radio-fields' => __( 'Check Box & Radio Fields', 'formidable' ),
			'buttons'                => __( 'Buttons', 'formidable' ),
			'form-messages'          => __( 'Form Messages', 'formidable' ),
		);

		/**
		 * Add custom boxes to the styling settings
		 *
		 * @since 2.3
		 *
		 * @param array $meta_boxes
		 */
		$meta_boxes = apply_filters( 'frm_style_boxes', $meta_boxes );

		foreach ( $meta_boxes as $nicename => $name ) {
			add_meta_box( $nicename . '-style', $name, 'FrmStylesController::include_style_section', self::$screen, 'side', 'default', $nicename );
			unset( $nicename, $name );
		}
	}

	/**
	 * @param array $atts
	 * @param array $sec
	 * @return void
	 */
	public static function include_style_section( $atts, $sec ) {
		extract( $atts ); // phpcs:ignore WordPress.PHP.DontExtract
		$style = $atts['style'];
		FrmStylesHelper::prepare_color_output( $style->post_content, false );

		$current_tab = FrmAppHelper::simple_get( 'page-tab', 'sanitize_title', 'default' );
		$file_name   = FrmAppHelper::plugin_path() . '/classes/views/styles/_' . $sec['args'] . '.php';

		/**
		 * Set the location of custom styling settings right before
		 * loading onto the page. If your style box was named "progress",
		 * this hook name will be frm_style_settings_progress.
		 *
		 * @since 2.3
		 */
		$file_name = apply_filters( 'frm_style_settings_' . $sec['args'], $file_name );

		echo '<div class="frm_grid_container">';
		include $file_name;
		echo '</div>';
	}

	public static function load_css() {
		header( 'Content-type: text/css' );

		$frm_style = new FrmStyle();
		$defaults  = $frm_style->get_defaults();
		$style     = '';

		include FrmAppHelper::plugin_path() . '/css/_single_theme.css.php';
		wp_die();
	}

	/**
	 * @return void
	 */
	public static function load_saved_css() {
		$css = get_transient( 'frmpro_css' );

		ob_start();
		include FrmAppHelper::plugin_path() . '/css/custom_theme.css.php';
		$output = ob_get_clean();
		$output = self::replace_relative_url( $output );

		/**
		 * The API needs to load font icons through a custom URL.
		 *
		 * @since 5.2
		 *
		 * @param string $output
		 */
		$output = apply_filters( 'frm_saved_css', $output );

		echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		self::maybe_hide_sample_form_error_message();

		wp_die();
	}

	/**
	 * Add an extra style rule to hide a broken style warning.
	 * To avoid cluttering the front end with any unecessary styles this is only added when the referer URL matches the styler.
	 *
	 * @since 6.2.3
	 *
	 * @return void
	 */
	public static function maybe_hide_sample_form_error_message() {
		$referer = FrmAppHelper::get_server_value( 'HTTP_REFERER' );
		if ( false !== strpos( $referer, 'admin.php?page=formidable-styles' ) ) {
			echo '#frm_broken_styles_warning { display: none; }';
		}
	}

	/**
	 * Replaces relative URL with absolute URL.
	 *
	 * @since 4.11.03
	 *
	 * @param string $css CSS content.
	 * @return string
	 */
	public static function replace_relative_url( $css ) {
		$plugin_url = trailingslashit( FrmAppHelper::plugin_url() );
		return str_replace(
			array(
				'url(../',
				"url('../",
				'url("../',
			),
			array(
				'url(' . $plugin_url,
				"url('" . $plugin_url,
				'url("' . $plugin_url,
			),
			$css
		);
	}

	/**
	 * Check if the Formidable styling should be loaded,
	 * then enqueue it for the footer
	 *
	 * @since 2.0
	 */
	public static function enqueue_style() {
		global $frm_vars;

		if ( isset( $frm_vars['css_loaded'] ) && $frm_vars['css_loaded'] ) {
			// The CSS has already been loaded.
			return;
		}

		$frm_settings = FrmAppHelper::get_settings();
		if ( $frm_settings->load_style != 'none' ) {
			wp_enqueue_style( 'formidable' );
			$frm_vars['css_loaded'] = true;
		}
	}

	/**
	 * Get the stylesheets for the form settings page
	 *
	 * @return array<WP_Post>
	 */
	public static function get_style_opts() {
		$frm_style = new FrmStyle();
		$styles    = $frm_style->get_all();

		return $styles;
	}

	/**
	 * Get the style post object for a target form.
	 *
	 * @param object|string|boolean $form
	 * @return WP_Post|null
	 */
	public static function get_form_style( $form = 'default' ) {
		$style = FrmFormsHelper::get_form_style( $form );

		if ( empty( $style ) || 1 == $style ) {
			$style = 'default';
		}

		$frm_style = new FrmStyle( $style );
		return $frm_style->get_one();
	}

	/**
	 * @param string $class
	 * @param string $style
	 */
	public static function get_form_style_class( $class, $style ) {
		if ( 1 == $style ) {
			$style = 'default';
		}

		$frm_style = new FrmStyle( $style );
		$style     = $frm_style->get_one();

		if ( $style ) {
			$class .= ' frm_style_' . $style->post_name;
			self::maybe_add_rtl_class( $style, $class );
		}

		return $class;
	}

	/**
	 * @param object $style
	 * @param string $class
	 *
	 * @since 3.0
	 */
	private static function maybe_add_rtl_class( $style, &$class ) {
		$is_rtl = isset( $style->post_content['direction'] ) && 'rtl' === $style->post_content['direction'];
		if ( $is_rtl ) {
			$class .= ' frm_rtl';
		}
	}

	/**
	 * @param string $val
	 */
	public static function get_style_val( $val, $form = 'default' ) {
		$style = self::get_form_style( $form );
		if ( $style && isset( $style->post_content[ $val ] ) ) {
			return $style->post_content[ $val ];
		}
	}

	public static function show_entry_styles( $default_styles ) {
		$frm_style = new FrmStyle( 'default' );
		$style     = $frm_style->get_one();

		if ( ! $style ) {
			return $default_styles;
		}

		foreach ( $default_styles as $name => $val ) {
			$setting = $name;
			if ( 'border_width' == $name ) {
				$setting = 'field_border_width';
			} elseif ( 'alt_bg_color' == $name ) {
				$setting = 'bg_color_active';
			}
			$default_styles[ $name ] = $style->post_content[ $setting ];
			unset( $name, $val );
		}

		return $default_styles;
	}

	public static function &important_style( $important, $field ) {
		$important = self::get_style_val( 'important_style', $field['form_id'] );

		return $important;
	}

	public static function do_accordion_sections( $screen, $context, $object ) {
		return do_accordion_sections( $screen, $context, $object );
	}

	/**
	 * Rename a style via an AJAX action.
	 *
	 * @since 6.0
	 *
	 * @return void
	 */
	public static function rename_style() {
		$permission_error = FrmAppHelper::permission_nonce_error( 'frm_edit_forms', 'nonce', 'frm_ajax' );
		if ( $permission_error !== false ) {
			$data = array(
				'message' => __( 'Unable to rename style', 'formidable' ),
			);
			wp_send_json_error( $data, 403 );
			die();
		}

		$style_id   = FrmAppHelper::get_post_param( 'style_id', 0, 'absint' );
		$style_name = FrmAppHelper::get_post_param( 'style_name', '', 'sanitize_text_field' );

		if ( ! $style_id || ! $style_name ) {
			$data = array(
				'message' => __( 'Invalid route', 'formidable' ),
			);
			wp_send_json_error( $data, 400 );
			die();
		}

		$post = get_post( $style_id );
		if ( ! $post || $post->post_type !== self::$post_type ) {
			$data = array(
				'message' => __( 'The style you are renaming either does not exist or it is not a style', 'formidable' ),
			);
			wp_send_json_error( $data, 404 );
			die();
		}

		global $wpdb;
		$wpdb->update( $wpdb->posts, array( 'post_title' => $style_name ), array( 'ID' => $post->ID ) );

		$data = array();
		wp_send_json_success( $data );
	}

	/**
	 * Prevent the WordPress edit.css file from loading on the visual styler page.
	 * This way .form-field elements do not have border styles applied to them.
	 *
	 * @since 6.0
	 *
	 * @param WP_Styles $styles
	 * @return void
	 */
	public static function disable_conflicting_wp_admin_css( $styles ) {
		if ( ! FrmAppHelper::is_style_editor_page() ) {
			return;
		}

		FrmStylesPreviewHelper::disable_conflicting_wp_admin_css( $styles );
	}

	/**
	 * @deprecated 6.1 Saving custom CSS has been moved into Global Settings.
	 *
	 * @return void
	 */
	public static function save_css() {
		_deprecated_function( __METHOD__, '6.1' );
	}
}
