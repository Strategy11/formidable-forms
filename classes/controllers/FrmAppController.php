<?php

class FrmAppController {

	public static function menu() {
        FrmAppHelper::maybe_add_permissions();
        if ( ! current_user_can( 'frm_view_forms' ) ) {
            return;
        }

		$menu_name = FrmAppHelper::get_menu_name();
		add_menu_page( 'Formidable', $menu_name, 'frm_view_forms', 'formidable', 'FrmFormsController::route', '', self::get_menu_position() );
    }

	private static function get_menu_position() {
		return apply_filters( 'frm_menu_position', '29.3' );
	}

	/**
	 * @since 3.0
	 */
	public static function add_admin_class( $classes ) {
		if ( self::is_white_page() ) {
			$classes .= ' frm-white-body ';
		}
		return $classes;
	}

	/**
	 * @since 3.0
	 */
	private static function is_white_page() {
		$is_white_page = ( FrmAppHelper::is_admin_page( 'formidable' ) || FrmAppHelper::is_admin_page( 'formidable-entries' ) || FrmAppHelper::is_admin_page( 'formidable-pro-upgrade' ) );
		if ( ! $is_white_page ) {
			$screen = get_current_screen();
			$is_white_page = ( $screen && $screen->id === 'edit-frm_display' );
		}

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

		$id = $form->id;
		$current_page = self::get_current_page();
		$nav_items = self::get_form_nav_items( $form );

		include( FrmAppHelper::plugin_path() . '/classes/views/shared/form-nav.php' );
	}

	private static function get_current_page() {
		global $pagenow;

		$page = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		$post_type = FrmAppHelper::simple_get( 'post_type', 'sanitize_title', 'None' );
		$current_page = isset( $_GET['page'] ) ? $page : $post_type;
		if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) {
			$current_page = 'frm_display';
		}

		return $current_page;
	}

	private static function get_form_nav_items( $form ) {
		$id = $form->parent_form_id ? $form->parent_form_id : $form->id;

		$nav_items = array(
			array(
				'link'    => admin_url( 'admin.php?page=formidable&frm_action=edit&id=' . absint( $id ) ),
				'label'   => __( 'Build', 'formidable' ),
				'current' => array( 'edit', 'new', 'duplicate' ),
				'page'    => 'formidable',
				'permission' => 'frm_edit_forms',
			),
			array(
				'link'    => admin_url( 'admin.php?page=formidable&frm_action=settings&id=' . absint( $id ) ),
				'label'   => __( 'Settings', 'formidable' ),
				'current' => array( 'settings' ),
				'page'    => 'formidable',
				'permission' => 'frm_edit_forms',
			),
			array(
				'link'    => admin_url( 'admin.php?page=formidable-entries&frm_action=list&form=' . absint( $id ) ),
				'label'   => __( 'Entries', 'formidable' ),
				'current' => array(),
				'page'    => 'formidable-entries',
				'permission' => 'frm_view_entries',
			),
		);

		$nav_args = array(
			'form_id' => $id,
			'form'    => $form,
		);
		return apply_filters( 'frm_form_nav_list', $nav_items, $nav_args );
	}

    // Adds a settings link to the plugins page
    public static function settings_link( $links ) {
		$settings = '<a href="' . esc_url( admin_url( 'admin.php?page=formidable' ) ) . '">' . __( 'Build a Form', 'formidable' ) . '</a>';
        array_unshift( $links, $settings );

        return $links;
    }

    public static function pro_get_started_headline() {
		self::maybe_show_upgrade_bar();

        // Don't display this error as we're upgrading the thing, or if the user shouldn't see the message
        if ( 'upgrade-plugin' == FrmAppHelper::simple_get( 'action', 'sanitize_title' ) || ! current_user_can( 'update_plugins' ) ) {
            return;
        }

		$pro_installed = is_dir( WP_PLUGIN_DIR . '/formidable-pro' );

		if ( get_site_option( 'frmpro-authorized' ) && ! is_callable( 'load_formidable_pro' ) ) {
			FrmAppHelper::load_admin_wide_js();

			// user is authorized, but running free version

			if ( $pro_installed ) {
				// if pro version is installed, include link to activate it
				$inst_install_url = wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=formidable-pro/formidable-pro.php' ), 'activate-plugin_formidable-pro/formidable-pro.php' );
			} else {
				$inst_install_url = 'https://formidableforms.com/knowledgebase/install-formidable-forms/?utm_source=WordPress&utm_medium=get-started&utm_campaign=liteplugin';
			}
        ?>
<div class="error" class="frm_previous_install">
		<?php
		echo apply_filters( // WPCS: XSS ok.
			'frm_pro_update_msg',
			sprintf(
				esc_html__( 'This site has been previously authorized to run Formidable Forms. %1$sInstall Formidable Pro%2$s or %3$sdeauthorize%4$s this site to continue running the free version and remove this message.', 'formidable' ),
				'<br/><a href="' . esc_url( $inst_install_url ) . '" target="_blank">',
				'</a>',
				'<a href="#" class="frm_deauthorize_link">',
				'</a>'
			),
			esc_url( $inst_install_url )
		);
		?>
</div>
<?php
        }
    }

	private static function maybe_show_upgrade_bar() {
		$page = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		if ( strpos( $page, 'formidable' ) !== 0 ) {
			return;
		}

		if ( FrmAppHelper::pro_is_installed() ) {
			return;
		}

		$affiliate = FrmAppHelper::get_affiliate();
		if ( ! empty( $affiliate ) ) {
			$tip = FrmTipsHelper::get_banner_tip();
			$link = FrmAppHelper::admin_upgrade_link( 'banner' );
?>
<div class="update-nag frm-update-to-pro">
	<?php echo FrmAppHelper::kses( $tip['tip'] ); // WPCS: XSS ok. ?>
	<span><?php echo FrmAppHelper::kses( $tip['call'] ); // WPCS: XSS ok. ?></span>
	<a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url( $link ) ); ?>" class="button">Upgrade to Pro</a>
</div>
<?php
		}
	}

	/**
	 * @since 3.04.02
	 */
	public static function include_upgrade_overlay() {
		$is_pro = FrmAppHelper::pro_is_installed();
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_style( 'jquery-ui-dialog' );
		include( FrmAppHelper::plugin_path() . '/classes/views/shared/upgrade_overlay.php' );
	}

	/**
	 * @since 3.04.02
	 */
	public static function remove_upsells() {
		remove_action( 'frm_before_settings', 'FrmSettingsController::license_box' );
		remove_action( 'frm_after_settings', 'FrmSettingsController::settings_cta' );
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
			$last_upgrade = explode( '-', $db_version );
			$needs_db_upgrade = (int) $last_upgrade[1] < (int) $atts['new_db_version'];
			$new_version = version_compare( $last_upgrade[0], $atts['new_plugin_version'], '<' );
			$needs_upgrade = $needs_db_upgrade || $new_version;
		}

		return $needs_upgrade;
	}

	/**
	 * Check for database update and trigger js loading
	 *
	 * @since 2.0.1
	 */
	public static function admin_init() {
		new FrmPersonalData(); // register personal data hooks

		if ( ! FrmAppHelper::doing_ajax() && self::needs_update() ) {
			self::network_upgrade_site();
		}

		$action = FrmAppHelper::simple_get( 'action', 'sanitize_title' );
		if ( ! FrmAppHelper::doing_ajax() || $action == 'frm_import_choices' ) {
			// don't continue during ajax calls
			self::admin_js();
		}
	}

    public static function admin_js() {
		$version = FrmAppHelper::plugin_version();
		FrmAppHelper::load_admin_wide_js( false );

		$dependecies = array(
			'formidable_admin_global',
			'formidable',
			'jquery',
			'jquery-ui-core',
			'jquery-ui-draggable',
			'jquery-ui-sortable',
			'bootstrap_tooltip',
			'bootstrap-multiselect',
		);

		if ( FrmAppHelper::is_admin_page( 'formidable-styles' ) ) {
			$dependecies[] = 'wp-color-picker';
		}

		wp_register_script( 'formidable_admin', FrmAppHelper::plugin_url() . '/js/formidable_admin.js', $dependecies, $version, true );
		wp_register_style( 'formidable-admin', FrmAppHelper::plugin_url() . '/css/frm_admin.css', array(), $version );
        wp_register_script( 'bootstrap_tooltip', FrmAppHelper::plugin_url() . '/js/bootstrap.min.js', array( 'jquery' ), '3.3.4' );
		wp_register_style( 'formidable-grids', FrmAppHelper::plugin_url() . '/css/frm_grids.css', array(), $version );

		// load multselect js
		wp_register_script( 'bootstrap-multiselect', FrmAppHelper::plugin_url() . '/js/bootstrap-multiselect.js', array( 'jquery', 'bootstrap_tooltip' ), '0.9.8', true );

		$page = FrmAppHelper::simple_get( 'page', 'sanitize_title' );
		$post_type = FrmAppHelper::simple_get( 'post_type', 'sanitize_title' );

		global $pagenow;
		if ( strpos( $page, 'formidable' ) === 0 || ( $pagenow == 'edit.php' && $post_type == 'frm_display' ) ) {

            wp_enqueue_script( 'admin-widgets' );
            wp_enqueue_style( 'widgets' );
            wp_enqueue_script( 'formidable' );
            wp_enqueue_script( 'formidable_admin' );
			FrmAppHelper::localize_script( 'admin' );

			wp_enqueue_style( 'formidable-admin' );
			if ( 'formidable-styles' !== $page ) {
				wp_enqueue_style( 'formidable-grids' );
				wp_enqueue_style( 'formidable-dropzone' );
				add_thickbox();
			} else {
				$settings = FrmAppHelper::get_settings();
				if ( empty( $settings->old_css ) ) {
					wp_enqueue_style( 'formidable-grids' );
				}
			}

            wp_register_script( 'formidable-editinplace', FrmAppHelper::plugin_url() . '/js/jquery/jquery.editinplace.packed.js', array( 'jquery' ), '2.3.0' );

			do_action( 'frm_enqueue_builder_scripts' );
        } else if ( $pagenow == 'post.php' || ( $pagenow == 'post-new.php' && $post_type == 'frm_display' ) ) {
            if ( isset( $_REQUEST['post_type'] ) ) {
                $post_type = sanitize_title( $_REQUEST['post_type'] );
			} else if ( isset( $_REQUEST['post'] ) && absint( $_REQUEST['post'] ) ) {
				$post = get_post( absint( $_REQUEST['post'] ) );
                if ( ! $post ) {
                    return;
                }
                $post_type = $post->post_type;
            } else {
                return;
            }

            if ( $post_type == 'frm_display' ) {
                wp_enqueue_script( 'jquery-ui-draggable' );
                wp_enqueue_script( 'formidable_admin' );
                wp_enqueue_style( 'formidable-admin' );
				FrmAppHelper::localize_script( 'admin' );
            }
        } else if ( $pagenow == 'widgets.php' ) {
            FrmAppHelper::load_admin_wide_js();
        }
    }

    public static function load_lang() {
        load_plugin_textdomain( 'formidable', false, FrmAppHelper::plugin_folder() . '/languages/' );
    }

	/**
	 * Filter shortcodes in text widgets
	 *
	 * @deprecated 2.5.4
	 * @codeCoverageIgnore
	 */
	public static function widget_text_filter( $content ) {
		_deprecated_function( __METHOD__, '2.5.4' );
		$regex = '/\[\s*(formidable|display-frm-data|frm-stats|frm-graph|frm-entry-links|formresults|frm-search)\s+.*\]/';
		return preg_replace_callback( $regex, 'FrmAppHelper::widget_text_filter_callback', $content );
	}

	/**
	 * Deprecated in favor of wpmu_upgrade_site
	 *
	 * @deprecated 2.3
	 * @codeCoverageIgnore
	 */
	public static function front_head() {
		_deprecated_function( __FUNCTION__, '2.3' );
		if ( is_multisite() && self::needs_update() ) {
			self::install();
		}
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
		);
		register_rest_route( 'frm-admin/v1', '/install', $args );
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

		$request = new WP_REST_Request( 'GET', '/frm-admin/v1/install' );

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
	 * @since 3.0
	 */
	public static function api_install() {
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

	/**
	 * @deprecated 3.0.04
	 * @codeCoverageIgnore
	 */
    public static function activation_install() {
		_deprecated_function( __METHOD__, '3.0.04', 'FrmAppController::install' );
        FrmDb::delete_cache_and_transient( 'frm_plugin_version' );
        FrmFormActionsController::actions_init();
        self::install();
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
		deactivate_plugins( FrmAppHelper::plugin_folder() . '/formidable.php', false, false );
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
	 * Routes for wordpress pages -- we're just replacing content
	 *
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function page_route( $content ) {
		_deprecated_function( __METHOD__, '3.0' );
		global $post;

		if ( $post && isset( $_GET['form'] ) ) {
			$content = FrmFormsController::page_preview();
		}

		return $content;
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
			$link = FrmAppHelper::admin_upgrade_link( 'footer' );
			$text = sprintf(
				__( 'Help us spread the %1$sFormidable Forms%2$s love with %3$s %5$s on WordPress.org%4$s. Thank you heaps!', 'formidable' ),
				'<a href="' . esc_url( FrmAppHelper::make_affiliate_url( $link ) ) . '" target="_blank">',
				'</a>',
				'<a href="https://wordpress.org/support/plugin/formidable/reviews/?filter=5#new-post" target="_blank">',
				'</a>',
				'&#9733;&#9733;&#9733;&#9733;&#9733;'
			);
			$text = '<span id="footer-thankyou">' . $text . '</span>';
		}
		return $text;
	}

	/**
	 * @deprecated 1.07.05
	 * @codeCoverageIgnore
	 */
    public static function get_form_shortcode( $atts ) {
        _deprecated_function( __FUNCTION__, '1.07.05', 'FrmFormsController::get_form_shortcode()' );
        return FrmFormsController::get_form_shortcode( $atts );
    }
}
