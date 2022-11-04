<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmAppController {

	public static function menu() {
		FrmAppHelper::maybe_add_permissions();
		if ( ! current_user_can( 'frm_view_forms' ) ) {
			return;
		}

		$menu_name = FrmAppHelper::get_menu_name();
		add_menu_page( 'Formidable', $menu_name, 'frm_view_forms', 'formidable', 'FrmFormsController::route', self::menu_icon(), self::get_menu_position() );
	}

	/**
	 * @return int
	 */
	private static function get_menu_position() {
		return (int) apply_filters( 'frm_menu_position', 29 );
	}

	/**
	 * @since 3.05
	 */
	private static function menu_icon() {
		$icon = FrmAppHelper::svg_logo(
			array(
				'fill'   => '#a0a5aa',
				'orange' => '#a0a5aa',
			)
		);
		$icon = 'data:image/svg+xml;base64,' . base64_encode( $icon );

		return apply_filters( 'frm_icon', $icon );
	}

	/**
	 * @since 3.0
	 */
	public static function add_admin_class( $classes ) {
		if ( self::is_white_page() ) {
			$classes .= ' frm-white-body ';
			$classes .= self::get_os();

			$page = str_replace( 'formidable-', '', FrmAppHelper::simple_get( 'page', 'sanitize_title' ) );
			if ( empty( $page ) || $page === 'formidable' ) {
				$action = FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );
				if ( in_array( $action, array( 'settings', 'edit', 'list' ) ) ) {
					$page .= $action;
				} else {
					$page = $action;
				}
			}
			if ( ! empty( $page ) ) {
				$classes .= ' frm-admin-page-' . $page;
			}
		}

		if ( FrmAppHelper::is_full_screen() ) {
			$classes .= apply_filters( 'frm_admin_full_screen_class', ' frm-full-screen folded' );
		}

		if ( ! FrmAppHelper::pro_is_installed() ) {
			$classes .= ' frm-lite ';
		}

		return $classes;
	}

	/**
	 * @since 4.0
	 */
	private static function get_os() {
		$agent = strtolower( FrmAppHelper::get_server_value( 'HTTP_USER_AGENT' ) );
		$os    = '';
		if ( strpos( $agent, 'mac' ) !== false ) {
			$os = ' osx';
		} elseif ( strpos( $agent, 'linux' ) !== false ) {
			$os = ' linux';
		} elseif ( strpos( $agent, 'windows' ) !== false ) {
			$os = ' windows';
		}
		return $os;
	}

	/**
	 * @since 3.0
	 */
	private static function is_white_page() {
		$white_pages = array(
			'formidable',
			'formidable-entries',
			'formidable-views',
			'formidable-pro-upgrade',
			'formidable-addons',
			'formidable-import',
			'formidable-settings',
			'formidable-styles',
			'formidable-styles2',
			'formidable-inbox',
			'formidable-welcome',
			'formidable-applications',
		);

		$get_page      = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		$is_white_page = in_array( $get_page, $white_pages, true );

		if ( ! $is_white_page ) {
			$screen        = get_current_screen();
			$is_white_page = ( $screen && strpos( $screen->id, 'frm_display' ) !== false );
		}

		/**
		 * Allow another add on to style a page as a Formidable "white page", which adds a white background color.
		 *
		 * @since 5.3
		 *
		 * @param bool $is_white_page
		 */
		$is_white_page = apply_filters( 'frm_is_white_page', $is_white_page );

		return $is_white_page;
	}

	public static function load_wp_admin_style() {
		FrmAppHelper::load_font_style();
	}

	public static function get_form_nav( $form, $show_nav = false, $title = 'show' ) {
		$show_nav = FrmAppHelper::get_param( 'show_nav', $show_nav, 'get', 'absint' );
		if ( empty( $show_nav ) || ! $form ) {
			return;
		}

		FrmForm::maybe_get_form( $form );
		if ( ! is_object( $form ) ) {
			return;
		}

		$id           = $form->id;
		$current_page = self::get_current_page();
		$nav_items    = self::get_form_nav_items( $form );

		include( FrmAppHelper::plugin_path() . '/classes/views/shared/form-nav.php' );
	}

	private static function get_current_page() {
		$page         = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		$post_type    = FrmAppHelper::simple_get( 'post_type', 'sanitize_title', 'None' );
		$current_page = isset( $_GET['page'] ) ? $page : $post_type;

		if ( FrmAppHelper::is_view_builder_page() ) {
			$current_page = 'frm_display';
		}

		return $current_page;
	}

	private static function get_form_nav_items( $form ) {
		$id = $form->parent_form_id ? $form->parent_form_id : $form->id;

		$nav_items = array(
			array(
				'link'       => FrmForm::get_edit_link( $id ),
				'label'      => __( 'Build', 'formidable' ),
				'current'    => array( 'edit', 'new', 'duplicate' ),
				'page'       => 'formidable',
				'permission' => 'frm_edit_forms',
			),
			array(
				'link'       => admin_url( 'admin.php?page=formidable&frm_action=settings&id=' . absint( $id ) ),
				'label'      => __( 'Settings', 'formidable' ),
				'current'    => array( 'settings' ),
				'page'       => 'formidable',
				'permission' => 'frm_edit_forms',
			),
			array(
				'link'       => admin_url( 'admin.php?page=formidable-entries&frm_action=list&form=' . absint( $id ) ),
				'label'      => __( 'Entries', 'formidable' ),
				'current'    => array(),
				'page'       => 'formidable-entries',
				'permission' => 'frm_view_entries',
			),
		);

		$views_installed = is_callable( 'FrmProAppHelper::views_is_installed' ) ? FrmProAppHelper::views_is_installed() : FrmAppHelper::pro_is_installed();

		if ( ! $views_installed ) {
			$nav_items[] = array(
				'link'    => admin_url( 'admin.php?page=formidable-views&form=' . absint( $id ) ),
				'label'   => __( 'Views', 'formidable' ),
				'current' => array(),
				'page'    => 'formidable-views',
				'permission' => 'frm_view_entries',
				'atts'    => array(
					'class' => 'frm_noallow',
				),
			);
		}

		// Let people know reports and views exist.
		if ( ! FrmAppHelper::pro_is_installed() ) {
			$nav_items[] = array(
				'link'    => admin_url( 'admin.php?page=formidable&frm_action=lite-reports&form=' . absint( $id ) ),
				'label'   => __( 'Reports', 'formidable' ),
				'current' => array( 'reports' ),
				'page'    => 'formidable',
				'permission' => 'frm_view_entries',
				'atts'    => array(
					'class' => 'frm_noallow',
				),
			);
		}

		$nav_args = array(
			'form_id' => $id,
			'form'    => $form,
		);

		return apply_filters( 'frm_form_nav_list', $nav_items, $nav_args );
	}

	// Adds a settings link to the plugins page
	public static function settings_link( $links ) {
		$settings = array();

		if ( ! FrmAppHelper::pro_is_installed() ) {
			$settings[] = '<a href="' . esc_url( FrmAppHelper::admin_upgrade_link( 'plugin-row' ) ) . '" target="_blank" rel="noopener"><b>' . esc_html__( 'Upgrade to Pro', 'formidable' ) . '</b></a>';
		}

		$settings[] = '<a href="' . esc_url( admin_url( 'admin.php?page=formidable' ) ) . '">' . __( 'Build a Form', 'formidable' ) . '</a>';

		return array_merge( $settings, $links );
	}

	public static function pro_get_started_headline() {
		self::review_request();
		FrmAppHelper::min_pro_version_notice( '4.0' );
	}

	/**
	 * Add admin notices as needed for reviews
	 *
	 * @since 3.04.03
	 */
	private static function review_request() {
		$reviews = new FrmReviews();
		$reviews->review_request();
	}

	/**
	 * Save the request to hide the review
	 *
	 * @since 3.04.03
	 */
	public static function dismiss_review() {
		FrmAppHelper::permission_check( 'frm_change_settings' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$reviews = new FrmReviews();
		$reviews->dismiss_review();
	}

	/**
	 * @since 3.04.02
	 */
	public static function include_upgrade_overlay() {
		self::enqueue_dialog_assets();
		add_action( 'admin_footer', 'FrmAppController::upgrade_overlay_html' );
	}

	/**
	 * Enqueue scripts and styles required for modals.
	 *
	 * @since 5.3
	 *
	 * @return void
	 */
	public static function enqueue_dialog_assets() {
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_style( 'jquery-ui-dialog' );
	}

	/**
	 * @since 3.06.03
	 */
	public static function upgrade_overlay_html() {
		$is_pro       = FrmAppHelper::pro_is_installed();
		$upgrade_link = array(
			'medium'  => 'builder',
			'content' => 'upgrade',
		);
		$default_link = FrmAppHelper::admin_upgrade_link( $upgrade_link );
		$plugin_path  = FrmAppHelper::plugin_path();
		$shared_path  = $plugin_path . '/classes/views/shared/';

		include $shared_path . 'upgrade_overlay.php';
		include $shared_path . 'confirm-overlay.php';

		if ( FrmAppHelper::is_admin_page( 'formidable-welcome' ) || FrmAppHelper::on_form_listing_page() ) {
			self::new_form_overlay_html();
		}
	}

	private static function new_form_overlay_html() {
		FrmFormsController::before_list_templates();

		$plugin_path      = FrmAppHelper::plugin_path();
		$path             = $plugin_path . '/classes/views/frm-forms/';
		$expired          = FrmFormsController::expired();
		$expiring         = FrmAddonsController::is_license_expiring();
		$user             = wp_get_current_user(); // $user used in leave-email.php to determine a default value for field
		$view_path        = $path . 'new-form-overlay/';
		$modal_class      = '';
		$upgrade_link     = FrmAppHelper::admin_upgrade_link(
			array(
				'medium'  => 'new-template',
				'content' => 'upgrade',
			)
		);
		$renew_link       = FrmAppHelper::admin_upgrade_link(
			array(
				'medium'  => 'new-template',
				'content' => 'renew',
			)
		);
		$blocks_to_render = array();

		if ( ! FrmAppHelper::pro_is_installed() ) {
			// avoid rendering the email and code blocks for users who have upgraded or have a free license already
			$api = new FrmFormTemplateApi();
			if ( ! $api->has_free_access() ) {
				array_push( $blocks_to_render, 'email', 'code' );
			}
		}

		// avoid rendering the upgrade block for users with elite
		if ( 'elite' !== FrmAddonsController::license_type() ) {
			$blocks_to_render[] = 'upgrade';
		}

		// avoid rendering the renew block for users who are not currently expired
		if ( $expired ) {
			$blocks_to_render[] = 'renew';
			$modal_class        = 'frm-expired';
		} elseif ( $expiring ) {
			$modal_class = 'frm-expiring';
		}

		include $path . 'new-form-overlay.php';
	}

	public static function include_info_overlay() {
		self::enqueue_dialog_assets();
		add_action( 'admin_footer', 'FrmAppController::info_overlay_html' );
	}

	public static function info_overlay_html() {
		include FrmAppHelper::plugin_path() . '/classes/views/shared/info-overlay.php';
	}

	/**
	 * @since 3.04.02
	 */
	public static function remove_upsells() {
		remove_action( 'frm_before_settings', 'FrmSettingsController::license_box' );
		remove_action( 'frm_after_settings', 'FrmSettingsController::settings_cta' );
		remove_action( 'frm_add_form_style_tab_options', 'FrmFormsController::add_form_style_tab_options' );
		remove_action( 'frm_after_field_options', 'FrmFormsController::logic_tip' );
	}

	/**
	 * If there are CURL problems on this server, wp_remote_post won't work for installing
	 * Use a javascript fallback instead.
	 *
	 * @since 2.0.3
	 */
	public static function install_js_fallback() {
		FrmAppHelper::load_admin_wide_js();
		echo '<div id="hidden frm_install_message"></div><script type="text/javascript">jQuery(document).ready(function(){frm_install_now();});</script>';
	}

	/**
	 * Check if the database is outdated
	 *
	 * @since 2.0.1
	 * @return boolean
	 */
	public static function needs_update() {
		$needs_upgrade = self::compare_for_update(
			array(
				'option'             => 'frm_db_version',
				'new_db_version'     => FrmAppHelper::$db_version,
				'new_plugin_version' => FrmAppHelper::plugin_version(),
			)
		);

		if ( ! $needs_upgrade ) {
			$needs_upgrade = apply_filters( 'frm_db_needs_upgrade', $needs_upgrade );
		}

		return $needs_upgrade;
	}

	/**
	 * Check both version number and DB number for changes
	 *
	 * @since 3.0.04
	 */
	public static function compare_for_update( $atts ) {
		$db_version = get_option( $atts['option'] );

		if ( strpos( $db_version, '-' ) === false ) {
			$needs_upgrade = true;
		} else {
			$last_upgrade     = explode( '-', $db_version );
			$needs_db_upgrade = (int) $last_upgrade[1] < (int) $atts['new_db_version'];
			$new_version      = version_compare( $last_upgrade[0], $atts['new_plugin_version'], '<' );
			$needs_upgrade    = $needs_db_upgrade || $new_version;
		}

		return $needs_upgrade;
	}

	/**
	 * Check for database update and trigger js loading
	 *
	 * @since 2.0.1
	 */
	public static function admin_init() {
		if ( FrmAppHelper::is_admin_page( 'formidable' ) && 'duplicate' === FrmAppHelper::get_param( 'frm_action' ) ) {
			FrmFormsController::duplicate();
		}

		new FrmPersonalData(); // register personal data hooks

		if ( ! FrmAppHelper::doing_ajax() && self::needs_update() ) {
			self::network_upgrade_site();
		}

		if ( ! FrmAppHelper::doing_ajax() ) {
			// don't continue during ajax calls
			self::admin_js();
		}

		if ( FrmAppHelper::is_admin_page( 'formidable' ) ) {
			$action = FrmAppHelper::get_param( 'frm_action' );

			if ( in_array( $action, array( 'add_new', 'list_templates' ), true ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=formidable&triggerNewFormModal=1' ) );
				exit;
			}

			FrmInbox::maybe_disable_screen_options();
		}
	}

	/**
	 * @return void
	 */
	public static function admin_js() {
		$plugin_url = FrmAppHelper::plugin_url();
		$version    = FrmAppHelper::plugin_version();

		FrmAppHelper::load_admin_wide_js();

		wp_register_style( 'formidable_admin_global', $plugin_url . '/css/admin/frm_admin_global.css', array(), $version );
		wp_enqueue_style( 'formidable_admin_global' );

		wp_register_style( 'formidable-admin', $plugin_url . '/css/frm_admin.css', array(), $version );
		wp_register_style( 'formidable-grids', $plugin_url . '/css/frm_grids.css', array(), $version );

		wp_register_script( 'formidable_dom', $plugin_url . '/js/admin/dom.js', array( 'jquery', 'jquery-ui-dialog', 'wp-i18n' ), $version, true );
		wp_register_script( 'formidable_embed', $plugin_url . '/js/admin/embed.js', array( 'formidable_dom', 'jquery-ui-autocomplete' ), $version, true );
		self::register_popper1();
		wp_register_script( 'bootstrap_tooltip', $plugin_url . '/js/bootstrap.min.js', array( 'jquery', 'popper' ), '4.6.1', true );

		$page = FrmAppHelper::simple_get( 'page', 'sanitize_title' );

		if ( 'formidable-applications' === $page ) {
			FrmApplicationsController::load_assets();
			return;
		}

		$dependencies = array(
			'formidable_admin_global',
			'jquery',
			'jquery-ui-core',
			'jquery-ui-draggable',
			'jquery-ui-sortable',
			'bootstrap_tooltip',
			'bootstrap-multiselect',
			'wp-i18n',
			'wp-hooks', // Required in WP versions older than 5.7
			'formidable_dom',
			'formidable_embed',
		);

		if ( FrmAppHelper::is_style_editor_page() ) {
			$dependencies[] = 'wp-color-picker';
		}

		wp_register_script( 'formidable_admin', $plugin_url . '/js/formidable_admin.js', $dependencies, $version, true );

		if ( FrmAppHelper::on_form_listing_page() ) {
			// For the existing page dropdown in the Form embed modal.
			wp_enqueue_script( 'jquery-ui-autocomplete' );
		}

		wp_register_script( 'bootstrap-multiselect', $plugin_url . '/js/bootstrap-multiselect.js', array( 'jquery', 'bootstrap_tooltip', 'popper' ), '1.1.1', true );

		$post_type = FrmAppHelper::simple_get( 'post_type', 'sanitize_title' );

		global $pagenow;
		if ( strpos( $page, 'formidable' ) === 0 || ( $pagenow === 'edit.php' && $post_type === 'frm_display' ) ) {

			wp_enqueue_script( 'admin-widgets' );
			wp_enqueue_style( 'widgets' );
			self::maybe_deregister_popper2();
			wp_enqueue_script( 'formidable_admin' );
			wp_enqueue_script( 'formidable_embed' );
			FrmAppHelper::localize_script( 'admin' );

			wp_enqueue_style( 'formidable-admin' );
			if ( 'formidable-styles' !== $page && 'formidable-styles2' !== $page ) {
				wp_enqueue_style( 'formidable-grids' );
				wp_enqueue_style( 'formidable-dropzone' );
			} else {
				wp_enqueue_style( 'formidable-grids' );
			}

			if ( 'formidable-entries' === $page ) {
				// Load front end js for entries.
				wp_enqueue_script( 'formidable' );
			}

			do_action( 'frm_enqueue_builder_scripts' );
			self::include_upgrade_overlay();
			self::include_info_overlay();
		} elseif ( FrmAppHelper::is_view_builder_page() ) {
			if ( isset( $_REQUEST['post_type'] ) ) {
				$post_type = sanitize_title( wp_unslash( $_REQUEST['post_type'] ) );
			} elseif ( isset( $_REQUEST['post'] ) && absint( $_REQUEST['post'] ) ) {
				$post = get_post( absint( wp_unslash( $_REQUEST['post'] ) ) );
				if ( ! $post ) {
					return;
				}
				$post_type = $post->post_type;
			} else {
				return;
			}

			if ( $post_type === 'frm_display' ) {
				wp_enqueue_style( 'formidable-grids' );
				wp_enqueue_script( 'jquery-ui-draggable' );
				self::maybe_deregister_popper2();
				wp_enqueue_script( 'formidable_admin' );
				wp_enqueue_style( 'formidable-admin' );
				FrmAppHelper::localize_script( 'admin' );
				self::include_info_overlay();
			}
		}

		self::maybe_force_formidable_block_on_gutenberg_page();
	}

	/**
	 * Fix a Duplicator Pro conflict because it uses Popper 2. See issue #3459.
	 *
	 * @since 5.2.02.01
	 *
	 * @return void
	 */
	private static function maybe_deregister_popper2() {
		global $wp_scripts;

		if ( ! array_key_exists( 'popper', $wp_scripts->registered ) ) {
			return;
		}

		$popper = $wp_scripts->registered['popper'];
		if ( version_compare( $popper->ver, '2.0', '>=' ) ) {
			wp_deregister_script( 'popper' );
			self::register_popper1();
		}
	}

	/**
	 * Register Popper required for Bootstrap 4.
	 *
	 * @since 5.2.02.01
	 *
	 * @return void
	 */
	private static function register_popper1() {
		wp_register_script( 'popper', FrmAppHelper::plugin_url() . '/js/popper.min.js', array( 'jquery' ), '1.16.0', true );
	}

	/**
	 * Automatically insert a Formidable block when loading Gutenberg when $_GET['frmForm' is set.
	 *
	 * @since 5.2
	 *
	 * @return void
	 */
	private static function maybe_force_formidable_block_on_gutenberg_page() {
		global $pagenow;
		if ( 'post.php' !== $pagenow ) {
			return;
		}

		$form_id = FrmAppHelper::simple_get( 'frmForm', 'absint' );
		if ( ! $form_id ) {
			return;
		}

		self::add_js_to_inject_gutenberg_block( 'formidable/simple-form', 'formId', $form_id );
	}

	/**
	 * @since 5.3
	 *
	 * @return void
	 */
	public static function add_js_to_inject_gutenberg_block( $block_name, $object_key, $object_id ) {
		require FrmAppHelper::plugin_path() . '/classes/views/shared/edit-page-js.php';
	}

	public static function load_lang() {
		load_plugin_textdomain( 'formidable', false, FrmAppHelper::plugin_folder() . '/languages/' );
	}

	/**
	 * Check if the styles are updated when a form is loaded on the front-end
	 *
	 * @since 3.0.1
	 */
	public static function maybe_update_styles() {
		if ( self::needs_update() ) {
			self::network_upgrade_site();
		}
	}

	/**
	 * @since 3.0
	 */
	public static function create_rest_routes() {
		$args = array(
			'methods'  => 'GET',
			'callback' => 'FrmAppController::api_install',
			'permission_callback' => __CLASS__ . '::can_update_db',
		);

		register_rest_route( 'frm-admin/v1', '/install', $args );

		$args = array(
			'methods'  => 'GET',
			'callback' => 'FrmAddonsController::install_addon_api',
			'permission_callback' => 'FrmAddonsController::can_install_addon_api',
		);

		register_rest_route( 'frm-admin/v1', '/install-addon', $args );
	}

	/**
	 * Make sure the install is only being run when we tell it to.
	 * We don't want to run manually by people calling the API.
	 *
	 * @since 4.06.02
	 */
	public static function can_update_db() {
		return get_transient( 'frm_updating_api' );
	}

	/**
	 * Run silent upgrade on each site in the network during a network upgrade.
	 * Update database settings for all sites in a network during network upgrade process.
	 *
	 * @since 2.0.1
	 *
	 * @param int $blog_id Blog ID.
	 */
	public static function network_upgrade_site( $blog_id = 0 ) {
		// Flag to check if install is happening as intended.
		set_transient( 'frm_updating_api', true, MINUTE_IN_SECONDS );
		$request = new WP_REST_Request( 'GET', '/frm-admin/v1/install' );

		self::maybe_add_wp_site_health();

		if ( $blog_id ) {
			switch_to_blog( $blog_id );
			$response = rest_do_request( $request );
			restore_current_blog();
		} else {
			$response = rest_do_request( $request );
		}

		if ( $response->is_error() ) {
			// if the remove post fails, use javascript instead
			add_action( 'admin_notices', 'FrmAppController::install_js_fallback' );
		}
	}

	/**
	 * Make sure WP_Site_Health has been included because it is required when calling rest_do_request.
	 * Check first that the file exists because WP_Site_Health was only introduced in WordPress 5.2.
	 */
	private static function maybe_add_wp_site_health() {
		if ( ! class_exists( 'WP_Site_Health' ) ) {
			$wp_site_health_path = ABSPATH . 'wp-admin/includes/class-wp-site-health.php';
			if ( file_exists( $wp_site_health_path ) ) {
				require_once $wp_site_health_path;
			}
		}
	}

	/**
	 * @since 3.0
	 */
	public static function api_install() {
		delete_transient( 'frm_updating_api' );
		if ( self::needs_update() ) {
			$running = get_option( 'frm_install_running' );
			if ( false === $running || $running < strtotime( '-5 minutes' ) ) {
				update_option( 'frm_install_running', time(), 'no' );
				self::install();
				delete_option( 'frm_install_running' );
			}
		}

		return true;
	}

	/**
	 * Silent database upgrade (no redirect).
	 * Called via ajax request during network upgrade process.
	 *
	 * @since 2.0.1
	 */
	public static function ajax_install() {
		self::api_install();
		wp_die();
	}

	public static function install() {
		$frmdb = new FrmMigrate();
		$frmdb->upgrade();
	}

	public static function uninstall() {
		FrmAppHelper::permission_check( 'administrator' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$frmdb = new FrmMigrate();
		$frmdb->uninstall();

		//disable the plugin and redirect after uninstall so the tables don't get added right back
		$plugins = array( FrmAppHelper::plugin_folder() . '/formidable.php', 'formidable-pro/formidable-pro.php' );
		deactivate_plugins( $plugins, false, false );
		echo esc_url_raw( admin_url( 'plugins.php?deactivate=true' ) );

		wp_die();
	}

	public static function drop_tables( $tables ) {
		global $wpdb;
		$tables[] = $wpdb->prefix . 'frm_fields';
		$tables[] = $wpdb->prefix . 'frm_forms';
		$tables[] = $wpdb->prefix . 'frm_items';
		$tables[] = $wpdb->prefix . 'frm_item_metas';

		return $tables;
	}

	public static function deauthorize() {
		FrmAppHelper::permission_check( 'frm_change_settings' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		delete_option( 'frmpro-credentials' );
		delete_option( 'frmpro-authorized' );
		delete_site_option( 'frmpro-credentials' );
		delete_site_option( 'frmpro-authorized' );
		wp_die();
	}

	public static function set_footer_text( $text ) {
		if ( FrmAppHelper::is_formidable_admin() ) {
			$text = '';
		}

		return $text;
	}

	/**
	 * @deprecated 1.07.05
	 * @codeCoverageIgnore
	 */
	public static function get_form_shortcode( $atts ) {
		return FrmDeprecated::get_form_shortcode( $atts );
	}

	/**
	 * @deprecated 2.5.4
	 * @codeCoverageIgnore
	 */
	public static function widget_text_filter( $content ) {
		return FrmDeprecated::widget_text_filter( $content );
	}

	/**
	 * Deprecated in favor of wpmu_upgrade_site
	 *
	 * @deprecated 2.3
	 * @codeCoverageIgnore
	 */
	public static function front_head() {
		FrmDeprecated::front_head();
	}

	/**
	 * @deprecated 3.0.04
	 * @codeCoverageIgnore
	 */
	public static function activation_install() {
		FrmDeprecated::activation_install();
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function page_route( $content ) {
		return FrmDeprecated::page_route( $content );
	}

	/**
	 * Include icons on page for Embed Form modal.
	 *
	 * @since 5.2
	 *
	 * @return void
	 */
	public static function include_embed_form_icons() {
		_deprecated_function( __METHOD__, '5.3' );
	}
}
