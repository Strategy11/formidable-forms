<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmAppController {

	/**
	 * @return void
	 */
	public static function menu() {
		FrmAppHelper::maybe_add_permissions();
		if ( ! current_user_can( 'frm_view_forms' ) ) {
			return;
		}

		$menu_name = FrmAppHelper::get_menu_name();

		if ( in_array( $menu_name, array( 'Formidable', 'Forms' ), true ) ) {
			$menu_name .= wp_kses_post( FrmInboxController::get_notice_count() );
		}

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

		if ( self::is_grey_page() ) {
			$classes .= ' frm-grey-body ';
		}

		if ( FrmAppHelper::is_full_screen() ) {
			$full_screen_on = self::get_full_screen_setting();
			$add_class      = '';
			if ( $full_screen_on ) {
				$add_class = ' frm-full-screen is-fullscreen-mode';

				// Load the CSS for .is-fullscreen-mode.
				wp_enqueue_style( 'wp-edit-post' );
			}
			$classes .= apply_filters( 'frm_admin_full_screen_class', $add_class );
		}

		if ( ! FrmAppHelper::pro_is_installed() ) {
			$classes .= ' frm-lite ';
		}

		if ( get_user_setting( 'unfold' ) && 'f' !== get_user_setting( 'mfold' ) ) {
			$classes .= ' frm-unfold ';
		}

		return $classes;
	}

	/**
	 * Get the full screen mode setting from the block editor.
	 *
	 * @since 6.1
	 *
	 * @return bool
	 */
	private static function get_full_screen_setting() {
		global $wpdb;
		$meta_key = $wpdb->get_blog_prefix() . 'persisted_preferences';

		$prefs = get_user_meta( get_current_user_id(), $meta_key, true );
		if ( $prefs && isset( $prefs['core/edit-post']['fullscreenMode'] ) ) {
			return $prefs['core/edit-post']['fullscreenMode'];
		}

		return true;
	}

	/**
	 * @since 4.0
	 *
	 * @return string
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
			'formidable-views-editor',
			'formidable-addons',
			'formidable-import',
			'formidable-settings',
			'formidable-styles',
			'formidable-styles2',
			'formidable-inbox',
			FrmFormTemplatesController::PAGE_SLUG,
			FrmOnboardingWizardController::PAGE_SLUG,
		);

		if ( ! class_exists( 'FrmTransHooksController', false ) && ! FrmTransLiteAppHelper::should_fallback_to_paypal() ) {
			// Only consider the payments page as a "white page" when the Payments submodule is off.
			// Otherwise this causes a lot of styling issues when the Stripe add-on (or Authorize.Net) is active.

			// Add an extra check to avoid white page styling on the PayPal "edit" action.
			// We fallback to the PayPal add on for the "edit" action since Stripe Lite does not have an edit view.
			if ( ! in_array( FrmAppHelper::simple_get( 'action' ), array( 'edit', 'new' ), true ) || ! is_callable( 'FrmPaymentsController::route' ) ) {
				$white_pages[] = 'formidable-payments';
			}
		}

		$is_white_page = self::is_page_in_list( $white_pages ) || self::is_grey_page() || FrmAppHelper::is_view_builder_page();

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

	/**
	 * Add a grey bg instead of white.
	 *
	 * @since 6.8
	 *
	 * @return bool
	 */
	private static function is_grey_page() {
		$grey_pages = array(
			'formidable-applications',
			'formidable-dashboard',
			'formidable-views',
		);

		$is_grey_page = self::is_page_in_list( $grey_pages );

		/**
		 * Filter to change FF wrapper background to grey.
		 *
		 * @since 6.8
		 *
		 * @param bool $is_grey_page
		 * @return bool
		 */
		return apply_filters( 'frm_is_grey_page', $is_grey_page );
	}

	/**
	 * @since 6.8
	 *
	 * @param array $pages A list of page names to check.
	 * @return bool
	 */
	private static function is_page_in_list( $pages ) {
		$get_page = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		return in_array( $get_page, $pages, true );
	}

	/**
	 * @return void
	 */
	public static function load_wp_admin_style() {
		FrmAppHelper::load_font_style();
	}

	/**
	 * @param int|object $form
	 * @param bool       $show_nav
	 * @param string     $title
	 *
	 * @psalm-param 'hide'|'show' $title
	 *
	 * @return void
	 */
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

		include FrmAppHelper::plugin_path() . '/classes/views/shared/form-nav.php';
	}

	/**
	 * @return string
	 */
	private static function get_current_page() {
		$page         = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		$post_type    = FrmAppHelper::simple_get( 'post_type', 'sanitize_title', 'None' );
		$current_page = isset( $_GET['page'] ) ? $page : $post_type;

		if ( FrmAppHelper::is_view_builder_page() ) {
			$current_page = 'frm_display';
		}

		return $current_page;
	}

	/**
	 * @param object $form
	 * @return array
	 */
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
				'link'       => FrmStylesHelper::get_list_url( $id ),
				'label'      => __( 'Style', 'formidable' ),
				'current'    => array(),
				'page'       => 'formidable-styles',
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

		$views_installed = is_callable( 'FrmProAppHelper::views_is_installed' ) && FrmProAppHelper::views_is_installed();

		if ( ! $views_installed ) {
			$nav_items[] = array(
				'link'       => admin_url( 'admin.php?page=formidable-views&form=' . absint( $id ) ),
				'label'      => __( 'Views', 'formidable' ),
				'current'    => array(),
				'page'       => 'formidable-views',
				'permission' => 'frm_view_entries',
				'atts'       => array(
					'class' => 'frm_noallow',
				),
			);
		}

		// Let people know reports and views exist.
		if ( ! FrmAppHelper::pro_is_installed() ) {
			$nav_items[] = array(
				'link'       => admin_url( 'admin.php?page=formidable&frm_action=lite-reports&form=' . absint( $id ) ),
				'label'      => __( 'Reports', 'formidable' ),
				'current'    => array( 'reports' ),
				'page'       => 'formidable',
				'permission' => 'frm_view_entries',
				'atts'       => array(
					'class' => 'frm_noallow',
				),
			);
		}

		$nav_args = array(
			'form_id' => $id,
			'form'    => $form,
		);

		return (array) apply_filters( 'frm_form_nav_list', $nav_items, $nav_args );
	}

	/**
	 * Adds a settings link to the plugins page
	 *
	 * @param array $links
	 * @return array
	 */
	public static function settings_link( $links ) {
		$settings = array();

		if ( ! FrmAppHelper::pro_is_installed() ) {
			if ( FrmAddonsController::is_license_expired() ) {
				$label = __( 'Renew', 'formidable' );
			} else {
				$label = FrmSalesApi::get_best_sale_value( 'plugin_page_cta_text' );
				if ( ! $label ) {
					$label = __( 'Upgrade to Pro', 'formidable' );
				}
			}

			$upgrade_link = FrmSalesApi::get_best_sale_value( 'plugin_page_cta_link' );
			if ( $upgrade_link ) {
				$upgrade_link = FrmAppHelper::maybe_add_missing_utm( $upgrade_link, array( 'medium' => 'plugin-row' ) );
			} else {
				$upgrade_link = FrmAppHelper::admin_upgrade_link( 'plugin-row' );
			}

			$settings[] = '<a href="' . esc_url( $upgrade_link ) . '" target="_blank" rel="noopener"><b style="color:#1da867;font-weight:700;">' . esc_html( $label ) . '</b></a>';
		}

		$settings[] = '<a href="' . esc_url( admin_url( 'admin.php?page=formidable' ) ) . '">' . __( 'Build a Form', 'formidable' ) . '</a>';

		return array_merge( $settings, $links );
	}

	/**
	 * @return void
	 */
	public static function pro_get_started_headline() {
		self::review_request();
		FrmAppHelper::min_pro_version_notice( '6.0' );
	}

	/**
	 * Add admin notices as needed for reviews
	 *
	 * @since 3.04.03
	 *
	 * @return void
	 */
	private static function review_request() {
		$reviews = new FrmReviews();
		$reviews->review_request();
	}

	/**
	 * Save the request to hide the review
	 *
	 * @since 3.04.03
	 *
	 * @return void
	 */
	public static function dismiss_review() {
		FrmAppHelper::permission_check( 'frm_change_settings' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$reviews = new FrmReviews();
		$reviews->dismiss_review();
	}

	/**
	 * @since 3.04.02
	 *
	 * @return void
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
	 *
	 * @return void
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
	}

	/**
	 * Create a basic form with an email field.
	 *
	 * @param string $form_key
	 * @param string $title
	 * @param string $description
	 * @return void
	 */
	public static function api_email_form( $form_key, $title = '', $description = '' ) {
		$user = wp_get_current_user();
		$args = array(
			'api_url'     => 'https://sandbox.formidableforms.com/api/wp-json/frm/v2/forms/' . $form_key . '?return=html&exclude_script=jquery&exclude_style=formidable-css',
			'title'       => $title,
			'description' => $description,
		);
		require FrmAppHelper::plugin_path() . '/classes/views/form-templates/modals/leave-email-modal.php';
	}

	/**
	 * @return void
	 */
	public static function include_info_overlay() {
		self::enqueue_dialog_assets();
		add_action( 'admin_footer', 'FrmAppController::info_overlay_html' );
	}

	/**
	 * @return void
	 */
	public static function info_overlay_html() {
		include FrmAppHelper::plugin_path() . '/classes/views/shared/info-overlay.php';
	}

	/**
	 * @since 3.04.02
	 *
	 * @return void
	 */
	public static function remove_upsells() {
		remove_action( 'frm_before_settings', 'FrmSettingsController::license_box' );
		remove_action( 'frm_after_settings_tabs', 'FrmSettingsController::settings_cta' );
		remove_action( 'frm_after_field_options', 'FrmFormsController::logic_tip' );
	}

	/**
	 * If there are CURL problems on this server, wp_remote_post won't work for installing
	 * Use a javascript fallback instead.
	 *
	 * @since 2.0.3
	 *
	 * @return void
	 */
	public static function install_js_fallback() {
		FrmAppHelper::load_admin_wide_js();
		?>
			<div id="frm_install_message"></div>
			<script>jQuery(document).ready( frm_install_now );</script>
		<?php
	}

	/**
	 * Check if the database is outdated
	 *
	 * @since 2.0.1
	 * @return bool
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
	 *
	 * @param array $atts
	 * @return bool
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
	 *
	 * @return void
	 */
	public static function admin_init() {
		if ( FrmAppHelper::get_param( 'delete_all' ) && FrmAppHelper::is_admin_page( 'formidable' ) && 'trash' === FrmAppHelper::get_param( 'form_type' ) ) {
			FrmFormsController::delete_all();
		}

		if ( FrmAppHelper::is_admin_page( 'formidable' ) && 'duplicate' === FrmAppHelper::get_param( 'frm_action' ) ) {
			FrmFormsController::duplicate();
		}

		if ( FrmAppHelper::is_admin_page( 'formidable' ) && FrmAppHelper::simple_get( 'frm_add_tables' ) ) {
			self::add_missing_tables();
		}

		if ( FrmAppHelper::is_style_editor_page() && 'save' === FrmAppHelper::get_param( 'frm_action' ) ) {
			// Hook in earlier than FrmStylesController::route so we can redirect before the headers have been sent.
			FrmStylesController::save_style();
		}

		if ( 'formidable-pro-upgrade' === FrmAppHelper::get_param( 'page' ) && ! FrmAppHelper::pro_is_installed() && current_user_can( 'frm_view_forms' ) ) {
			$redirect = FrmSalesApi::get_best_sale_value( 'menu_cta_link' );
			$utm      = array(
				'medium'  => 'upgrade',
				'content' => 'submenu-upgrade',
			);

			if ( $redirect ) {
				$redirect = FrmAppHelper::maybe_add_missing_utm( $redirect, $utm );
			} else {
				$redirect = FrmAppHelper::admin_upgrade_link( $utm );
			}

			wp_redirect( $redirect );
			die();
		}

		// Register personal data hooks.
		new FrmPersonalData();

		if ( ! FrmAppHelper::doing_ajax() && self::needs_update() ) {
			self::network_upgrade_site();
		}

		if ( ! FrmAppHelper::doing_ajax() ) {
			// don't continue during ajax calls
			self::admin_js();
		}

		self::trigger_page_load_hooks();

		if ( FrmAppHelper::is_admin_page( 'formidable' ) ) {
			// Redirect to the "Form Templates" page if the 'frm_action' parameter matches specific actions.
			// This provides backward compatibility for old addons that use legacy modal templates.
			$action             = FrmAppHelper::get_param( 'frm_action' );
			$trigger_name_modal = FrmAppHelper::get_param( 'triggerNewFormModal' );
			if ( $trigger_name_modal || in_array( $action, array( 'add_new', 'list_templates' ), true ) ) {
				$application_id = FrmAppHelper::simple_get( 'applicationId', 'absint' );
				$url_param      = $application_id ? '&applicationId=' . $application_id : '';

				wp_safe_redirect( admin_url( 'admin.php?page=' . FrmFormTemplatesController::PAGE_SLUG . $url_param ) );
				exit;
			}

			FrmInbox::maybe_disable_screen_options();
		}
	}

	/**
	 * Get the current page and check for a possible function to trigger.
	 * If a class name matches the page name, and the class has a load_page() method, trigger it.
	 *
	 * @since 6.8
	 *
	 * @return void
	 */
	private static function trigger_page_load_hooks() {
		$page = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		if ( strpos( $page, 'formidable-' ) !== 0 ) {
			// Only trigger hooks on Formidable pages.
			return;
		}

		$page  = str_replace( array( 'formidable-', '-' ), array( '', ' ' ), $page );
		$page  = str_replace( ' ', '', ucwords( $page ) );
		$class = 'Frm' . $page . 'Controller';
		if ( class_exists( $class ) && method_exists( $class, 'load_page' ) ) {
			call_user_func( array( $class, 'load_page' ) );
		}
	}

	/**
	 * @return void
	 */
	public static function admin_js() {
		$plugin_url = FrmAppHelper::plugin_url();
		$version    = FrmAppHelper::plugin_version();

		FrmAppHelper::load_admin_wide_js();

		// Register component assets early to ensure they can be enqueued later in controllers.
		wp_register_style( 'formidable-animations', $plugin_url . '/css/admin/animations.css', array(), $version );

		if ( class_exists( 'FrmOverlayController' ) ) {
			// This should always exist.
			// But it may not have loaded properly when updating the plugin.
			FrmOverlayController::register_assets();
		}

		wp_register_style( 'formidable_admin_global', $plugin_url . '/css/admin/frm_admin_global.css', array(), $version );
		wp_enqueue_style( 'formidable_admin_global' );

		wp_register_style( 'formidable-admin', $plugin_url . '/css/frm_admin.css', array(), $version );
		wp_register_style( 'formidable-grids', $plugin_url . '/css/frm_grids.css', array(), $version );

		wp_register_script( 'formidable_dom', $plugin_url . '/js/admin/dom.js', array( 'jquery', 'jquery-ui-dialog', 'wp-i18n' ), $version, true );
		wp_register_script( 'formidable_embed', $plugin_url . '/js/admin/embed.js', array( 'formidable_dom', 'jquery-ui-autocomplete' ), $version, true );
		self::register_popper1();
		wp_register_script( 'bootstrap_tooltip', $plugin_url . '/js/bootstrap.min.js', array( 'jquery', 'popper' ), '4.6.1', true );

		$settings_js_vars = array(
			'currencies' => FrmCurrencyHelper::get_currencies(),
		);
		wp_register_script( 'formidable_settings', $plugin_url . '/js/admin/settings.js', array(), $version, true );
		wp_localize_script( 'formidable_settings', 'frmSettings', $settings_js_vars );

		if ( self::should_show_floating_links() ) {
			self::enqueue_floating_links( $plugin_url, $version );
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
			// Required in WP versions older than 5.7
			'wp-hooks',
			'formidable_dom',
			'formidable_embed',
		);

		if ( FrmAppHelper::is_style_editor_page( 'edit' ) ) {
			// We only need to load the color picker when editing styles.
			$dependencies[] = 'wp-color-picker';
		}

		wp_register_script( 'formidable_admin', $plugin_url . '/js/formidable_admin.js', $dependencies, $version, true );

		if ( FrmAppHelper::on_form_listing_page() ) {
			// For the existing page dropdown in the Form embed modal.
			wp_enqueue_script( 'jquery-ui-autocomplete' );
		}

		wp_register_script( 'bootstrap-multiselect', $plugin_url . '/js/bootstrap-multiselect.js', array( 'jquery', 'bootstrap_tooltip', 'popper' ), '1.1.1', true );

		if ( ! class_exists( 'FrmTransHooksController', false ) ) {
			/**
			 * Gateway fields are included for add-on compatibility but we do not want it to be visible.
			 * They do however need to be visible when the payments submodule is active.
			 */
			wp_add_inline_style(
				'formidable-admin',
				'#frm_builder_page li[data-ftype="gateway"] { display: none; }'
			);
		}

		$page      = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		$post_type = FrmAppHelper::simple_get( 'post_type', 'sanitize_title' );

		global $pagenow;
		if ( strpos( $page, 'formidable' ) === 0 || ( $pagenow === 'edit.php' && $post_type === 'frm_display' ) ) {
			wp_enqueue_script( 'admin-widgets' );
			wp_enqueue_style( 'widgets' );
			self::maybe_deregister_popper2();
			wp_enqueue_script( 'formidable_admin' );
			wp_set_script_translations( 'formidable_admin', 'formidable' );
			wp_enqueue_script( 'formidable_embed' );
			wp_set_script_translations( 'formidable_embed', 'formidable' );
			FrmAppHelper::localize_script( 'admin' );

			wp_enqueue_style( 'formidable-animations' );
			wp_enqueue_style( 'formidable-admin' );
			if ( 'formidable-styles' !== $page && 'formidable-styles2' !== $page ) {
				wp_enqueue_style( 'formidable-grids' );
				self::maybe_enqueue_dropzone_css( $page );
			} else {
				wp_enqueue_style( 'formidable-grids' );
			}

			if ( 'formidable-entries' === $page ) {
				// Load front end js for entries.
				wp_enqueue_script( 'formidable' );

				// Registers and enqueues the entries page scripts.
				wp_register_script( 'formidable_entries', $plugin_url . '/js/admin/entries.js', array( 'formidable_admin', 'wp-dom-ready' ), $version, true );
				wp_enqueue_script( 'formidable_entries' );
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
				self::enqueue_legacy_views_assets();
			}
		}//end if

		if ( 'formidable-addons' === $page ) {
			wp_register_script( 'formidable_addons', $plugin_url . '/js/admin/addons.js', array( 'formidable_admin', 'wp-dom-ready' ), $version, true );
			wp_enqueue_script( 'formidable_addons' );
		}
	}

	/**
	 * Avoid loading dropzone CSS on the form list page. It isn't required there.
	 *
	 * @since 6.11
	 *
	 * @param string $page
	 * @return void
	 */
	private static function maybe_enqueue_dropzone_css( $page ) {
		if ( ! FrmAppHelper::pro_is_installed() ) {
			return;
		}

		$should_avoid_loading_dropzone = 'formidable' === $page && ! FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );
		if ( ! $should_avoid_loading_dropzone ) {
			wp_enqueue_style( 'formidable-dropzone' );
		}
	}

	/**
	 * The floating links are not shown on every page.
	 * They are also not shown if white labeling is being used.
	 *
	 * @since 6.8.4
	 *
	 * @return bool
	 */
	private static function should_show_floating_links() {
		if ( ! FrmAppHelper::is_formidable_branding() ) {
			return false;
		}

		return FrmAppHelper::is_formidable_admin() &&
			! FrmAppHelper::is_style_editor_page() &&
			! FrmAppHelper::is_admin_page( 'formidable-views-editor' ) &&
			! FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );
	}

	/**
	 * @since 6.3.2
	 *
	 * @return void
	 */
	public static function admin_enqueue_scripts() {
		self::load_wp_admin_style();
		self::maybe_force_formidable_block_on_gutenberg_page();

		if ( FrmAppHelper::is_admin_page( 'formidable-settings' ) ) {
			wp_enqueue_style( FrmDashboardController::PAGE_SLUG, FrmAppHelper::plugin_url() . '/css/admin/dashboard.css', array(), FrmAppHelper::plugin_version() );
		}

		FrmUsageController::load_scripts();
	}

	/**
	 * Enqueue required assets for the Legacy Views editor (Views v4.x).
	 *
	 * @since 6.1.2
	 *
	 * @return void
	 */
	private static function enqueue_legacy_views_assets() {
		wp_enqueue_style( 'formidable-grids' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		self::maybe_deregister_popper2();
		wp_enqueue_script( 'formidable_admin' );
		wp_add_inline_style(
			'formidable-admin',
			'
			.frm-white-body.post-type-frm_display .columns-2 {
				display: block;
				overflow: visible;
			}

			.frm-white-body.post-type-frm_display .columns-2 > div {
				overflow-y: hidden;
			}

			.frm-white-body.post-type-frm_display #titlediv #title-prompt-text {
				padding: 3px 0 0 10px;
			}

			.post-type-frm_display #field-search-input,
			.post-type-frm_display #advanced-search-input {
				flex: 1;
			}
			'
		);
		wp_enqueue_style( 'formidable-admin' );
		wp_enqueue_script( 'formidable_legacy_views', FrmAppHelper::plugin_url() . '/js/admin/legacy-views.js', array( 'jquery', 'formidable_admin' ), FrmAppHelper::plugin_version() );
		FrmAppHelper::localize_script( 'admin' );
		self::include_info_overlay();
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
		if ( ! self::should_register_popper() ) {
			return;
		}
		wp_register_script( 'popper', FrmAppHelper::plugin_url() . '/js/popper.min.js', array( 'jquery' ), '1.16.0', true );
	}

	/**
	 * Only register popper on Formidable pages.
	 * This helps to avoid popper conflicts on other plugin pages, including the WP Bakery page editor.
	 *
	 * @since 6.11.1
	 *
	 * @return bool
	 */
	private static function should_register_popper() {
		global $pagenow;

		$post_id = FrmAppHelper::simple_get( 'post', 'absint' );
		if ( 'post.php' === $pagenow && $post_id && 'frm_display' === get_post_type( $post_id ) ) {
			return true;
		}

		$post_type          = FrmAppHelper::simple_get( 'post_type', 'sanitize_title' );
		$is_views_post_type = 'frm_display' === $post_type;
		if ( in_array( $pagenow, array( 'post-new.php', 'edit.php' ), true ) && $is_views_post_type ) {
			return true;
		}

		$page = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		if ( strpos( $page, 'formidable' ) === 0 ) {
			return true;
		}

		return in_array( $pagenow, array( 'term.php', 'edit-tags.php' ), true ) && FrmAppHelper::simple_get( 'taxonomy' ) === 'frm_application';
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
	 * @param string       $block_name
	 * @param string       $object_key
	 * @param array|string $object_id
	 *
	 * @return void
	 */
	public static function add_js_to_inject_gutenberg_block( $block_name, $object_key, $object_id ) {
		require FrmAppHelper::plugin_path() . '/classes/views/shared/edit-page-js.php';
	}

	/**
	 * @return void
	 */
	public static function load_lang() {
		load_plugin_textdomain( 'formidable', false, FrmAppHelper::plugin_folder() . '/languages/' );
	}

	/**
	 * Check if the styles are updated when a form is loaded on the front-end
	 *
	 * @since 3.0.1
	 *
	 * @return void
	 */
	public static function maybe_update_styles() {
		if ( self::needs_update() ) {
			self::network_upgrade_site();
		}
	}

	/**
	 * @since 3.0
	 *
	 * @return void
	 */
	public static function create_rest_routes() {
		$args = array(
			'methods'             => 'GET',
			'callback'            => 'FrmAppController::api_install',
			'permission_callback' => __CLASS__ . '::can_update_db',
		);

		register_rest_route( 'frm-admin/v1', '/install', $args );

		$args = array(
			'methods'             => 'GET',
			'callback'            => 'FrmAddonsController::install_addon_api',
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
	 *
	 * @return void
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
	 *
	 * @return void
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
	 *
	 * @return true
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
	 *
	 * @return void
	 */
	public static function ajax_install() {
		FrmAppHelper::permission_check( 'frm_change_settings' );
		check_ajax_referer( 'frm_ajax', 'nonce' );
		self::api_install();
		wp_die();
	}

	/**
	 * @return void
	 */
	public static function install() {
		$frmdb = new FrmMigrate();
		$frmdb->upgrade();
	}

	/**
	 * @return void
	 */
	public static function uninstall() {
		FrmAppHelper::permission_check( 'administrator' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$frmdb = new FrmMigrate();
		$frmdb->uninstall();

		// Disable the plugin and redirect after uninstall so the tables don't get added right back.
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

	/**
	 * @return void
	 */
	public static function deauthorize() {
		FrmAppHelper::permission_check( 'frm_change_settings' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		delete_option( 'frmpro-credentials' );
		delete_option( 'frmpro-authorized' );
		delete_site_option( 'frmpro-credentials' );
		delete_site_option( 'frmpro-authorized' );
		wp_die();
	}

	/**
	 * This is triggered when Formidable is activated.
	 *
	 * @return void
	 */
	public static function handle_activation() {
		self::maybe_activate_payment_cron();
	}

	/**
	 * The payment cron is unscheduled when Formidable is deactivated.
	 * We need to add it back again on activation if Stripe is configured.
	 *
	 * @since 6.5
	 *
	 * @return void
	 */
	private static function maybe_activate_payment_cron() {
		if ( ! FrmStrpLiteConnectHelper::stripe_connect_is_setup() ) {
			return;
		}

		FrmTransLiteAppController::maybe_schedule_cron();
	}

	public static function set_footer_text( $text ) {
		if ( FrmAppHelper::is_formidable_admin() ) {
			$text = '';
		}

		return $text;
	}

	/**
	 * Add admin footer links.
	 *
	 * @since 6.4.1
	 *
	 * @return void
	 */
	public static function add_admin_footer_links() {
		FrmFormsController::include_device_too_small_message();
		if ( self::should_show_footer_links() ) {
			include FrmAppHelper::plugin_path() . '/classes/views/shared/admin-footer-links.php';
		}
	}

	/**
	 * Check if the footer links should be shown.
	 *
	 * @since 6.7
	 *
	 * @return bool
	 */
	private static function should_show_footer_links() {
		$post_type          = FrmAppHelper::simple_get( 'post_type', 'sanitize_title' );
		$is_formidable_page = FrmAppHelper::is_formidable_admin() || 'frm_logs' === $post_type;
		$show_footer_links  = $is_formidable_page;
		if ( FrmAppHelper::is_full_screen() || ! FrmAppHelper::is_formidable_branding() ) {
			$show_footer_links = false;
		}

		/**
		 * Filter whether to show the Formidable footer links.
		 *
		 * @since 6.7
		 *
		 * @param bool $show_footer_links
		 * @return bool
		 */
		return apply_filters( 'frm_show_footer_links', $show_footer_links );
	}

	/**
	 * Show an error modal and terminate the script execution.
	 *
	 * @since 6.7
	 *
	 * @param array $error_args Arguments that control the behavior of the error modal.
	 *
	 * @return void
	 */
	public static function show_error_modal( $error_args ) {
		add_filter( 'frm_show_footer_links', '__return_false' );

		$defaults = array(
			'title'            => '',
			'body'             => '',
			'cancel_url'       => '',
			'cancel_classes'   => '',
			'continue_url'     => '',
			'continue_classes' => '',
			'icon'             => 'frm_lock_simple',
		);

		$error_args = wp_parse_args( $error_args, $defaults );
		if ( ! isset( $error_args['cancel_text'] ) && ! empty( $error_args['cancel_url'] ) ) {
			$error_args['cancel_text'] = __( 'Cancel', 'formidable' );
		}

		if ( ! isset( $error_args['continue_text'] ) && ! empty( $error_args['continue_url'] ) ) {
			$error_args['continue_text'] = __( 'Continue', 'formidable' );
		}

		include FrmAppHelper::plugin_path() . '/classes/views/frm-forms/error-modal.php';
	}

	/**
	 * Handles Floating Links' scripts and styles enqueueing.
	 *
	 * @since 6.4
	 *
	 * @param string $plugin_url URL of the plugin.
	 * @param string $version Current version of the plugin.
	 * @return void
	 */
	private static function enqueue_floating_links( $plugin_url, $version ) {
		if ( ! $plugin_url || ! $version ) {
			// If any required parameters are missing, exit early.
			return;
		}

		// Enqueue the Floating Links styles.
		wp_enqueue_style( 's11-floating-links', $plugin_url . '/css/packages/s11-floating-links.css', array(), $version );

		// Enqueue the Floating Links script.
		wp_enqueue_script( 's11-floating-links', $plugin_url . '/js/packages/floating-links/s11-floating-links.js', array( 'formidable_admin' ), $version, true );

		// Enqueue the config script.
		wp_enqueue_script( 's11-floating-links-config', $plugin_url . '/js/packages/floating-links/config.js', array( 'wp-i18n' ), $version, true );

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 's11-floating-links-config', 's11-' );
		}

		$floating_links_data = array(
			'proIsInstalled' => FrmAppHelper::pro_is_installed(),
		);
		wp_localize_script( 's11-floating-links-config', 's11FloatingLinksData', $floating_links_data );

		/**
		 * Prompt Pro to load additional floating links scripts.
		 * This is used to include images in the Inbox SlideIn when Pro is active.
		 *
		 * @since 6.8.4
		 */
		do_action( 'frm_enqueue_floating_links' );
	}

	/**
	 * Check if we are in our admin pages.
	 *
	 * @return bool
	 */
	private static function in_our_pages() {
		global $current_screen, $pagenow;
		if ( FrmAppHelper::is_formidable_admin() ) {
			return true;
		}

		if ( ! empty( $current_screen->post_type ) && 'frm_logs' === $current_screen->post_type ) {
			return true;
		}

		if ( in_array( $pagenow, array( 'term.php', 'edit-tags.php' ), true ) && 'frm_application' === FrmAppHelper::simple_get( 'taxonomy' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Handles actions related to the current screen.
	 *
	 * @since 6.19
	 *
	 * @return void
	 */
	public static function handle_current_screen() {
		if ( ! self::in_our_pages() ) {
			return;
		}

		self::filter_admin_notices();
		self::remember_custom_sort();
	}

	/**
	 * Hide all third-parties admin notices only in our admin pages.
	 *
	 * @return void
	 */
	public static function filter_admin_notices() {
		$actions = array(
			'admin_notices',
			'network_admin_notices',
			'user_admin_notices',
			'all_admin_notices',
		);

		global $wp_filter;

		foreach ( $actions as $action ) {
			if ( empty( $wp_filter[ $action ]->callbacks ) ) {
				continue;
			}
			foreach ( $wp_filter[ $action ]->callbacks as $priority => $callbacks ) {
				foreach ( $callbacks as $callback_name => $callback ) {
					if ( self::is_our_callback_string( $callback_name ) || self::is_our_callback_array( $callback ) ) {
						continue;
					}
					unset( $wp_filter[ $action ]->callbacks[ $priority ][ $callback_name ] );
				}
			}
		}
	}

	/**
	 * Remembers and applies user-specific sorting preferences.
	 *
	 * @return void
	 */
	private static function remember_custom_sort() {
		$screen  = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		if ( ! FrmAppHelper::is_admin_list_page() && ! FrmAppHelper::is_admin_list_page( 'formidable-entries' ) ) {
			return;
		}

		$orderby = FrmAppHelper::get_param( 'orderby' );

		if ( ! $orderby ) {
			return;
		}

		$user_id  = get_current_user_id();
		$meta_key = 'frm_preferred_list_sort_' . $screen->id;
		$order    = FrmAppHelper::get_param( 'order' );

		$new_sort = array(
			'orderby' => $orderby,
			'order'   => $order,
		);

		$current_sort = get_user_meta( $user_id, $meta_key, true );

		if ( $new_sort !== $current_sort ) {
			update_user_meta(
				$user_id,
				$meta_key,
				array(
					'orderby' => $orderby,
					'order'   => $order,
				)
			);
		}
	}

	/**
	 * Retrieve and apply any saved sorting preferences for the current screen.
	 *
	 * @since 6.19
	 *
	 * @param string &$orderby Reference to the current 'orderby' parameter.
	 * @param string &$order   Reference to the current 'order' parameter.
	 * @return void
	 */
	public static function apply_saved_sort_preference( &$orderby, &$order ) {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		$user_id             = get_current_user_id();
		$preferred_list_sort = get_user_meta( $user_id, 'frm_preferred_list_sort_' . $screen->id, true );

		if ( is_array( $preferred_list_sort ) && ! empty( $preferred_list_sort['orderby'] ) ) {
			$orderby = $preferred_list_sort['orderby'];

			if ( ! empty( $preferred_list_sort['order'] ) ) {
				$order = $preferred_list_sort['order'];
			}
		}
	}

	/**
	 * Validate that the callback name is ours not from third-party.
	 *
	 * @param string $callback_name WordPress callback name.
	 *
	 * @return bool
	 */
	private static function is_our_callback_string( $callback_name ) {
		return 0 === stripos( $callback_name, 'frm' );
	}

	/**
	 * Validate that the callback array is ours not from third-party.
	 *
	 * @param array $callback WordPress callback array.
	 *
	 * @return bool
	 */
	private static function is_our_callback_array( $callback ) {
		return ! empty( $callback['function'] ) &&
			is_array( $callback['function'] ) &&
			! empty( $callback['function'][0] ) &&
			self::is_our_callback_string( is_object( $callback['function'][0] ) ? get_class( $callback['function'][0] ) : $callback['function'][0] );
	}

	/**
	 * In some cases, the DB tables may fail to install.
	 * This function tries to add them again when the user clicks the link to try again
	 * from the given inbox notice.
	 *
	 * @since 6.19
	 */
	private static function add_missing_tables() {
		FrmAppHelper::permission_check( 'frm_view_forms' );

		$inbox = new FrmInbox();
		$error = $inbox->check_for_error();

		if ( ! $error || 'failed-to-create-tables' !== $error['key'] ) {
			// Confirm the inbox item with this CTA exists.
			wp_safe_redirect( admin_url( 'admin.php?page=formidable' ) );
			exit;
		}

		global $wpdb;
		$exists = $wpdb->get_results( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'frm_forms' ) );

		if ( $exists ) {
			// Exit early if the table already exists.
			wp_safe_redirect( admin_url( 'admin.php?page=formidable' ) );
			exit;
		}

		delete_option( 'frm_db_version' );
		wp_safe_redirect( admin_url( 'admin.php?page=formidable' ) );
		exit;
	}

	/**
	 * Handles the small screen proceed action.
	 *
	 * @since 6.21
	 *
	 * @return void
	 */
	public static function small_screen_proceed() {
		FrmAppHelper::permission_check( 'frm_view_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );
		update_user_option( get_current_user_id(), 'frm_ignore_small_screen_warning', true );
		wp_send_json_success();
	}
}
