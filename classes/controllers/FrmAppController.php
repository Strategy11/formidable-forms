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
		$count = count( get_post_types( array( 'show_ui' => true, '_builtin' => false, 'show_in_menu' => true ) ) );
		$pos = $count ? '22.7' : '29.3';
		$pos = apply_filters( 'frm_menu_position', $pos );
		return $pos;
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

		$nav_items = apply_filters( 'frm_form_nav_list', $nav_items, array( 'form_id' => $id, 'form' => $form ) );
		return $nav_items;
	}

    // Adds a settings link to the plugins page
    public static function settings_link( $links ) {
		$settings = '<a href="' . esc_url( admin_url( 'admin.php?page=formidable-settings' ) ) . '">' . __( 'Settings', 'formidable' ) . '</a>';
        array_unshift( $links, $settings );

        return $links;
    }

    public static function pro_get_started_headline() {
		self::maybe_show_upgrade_bar();

        // Don't display this error as we're upgrading the thing, or if the user shouldn't see the message
        if ( 'upgrade-plugin' == FrmAppHelper::simple_get( 'action', 'sanitize_title' ) || ! current_user_can( 'update_plugins' ) ) {
            return;
        }

		if ( get_site_option( 'frmpro-authorized' ) && ! file_exists( FrmAppHelper::plugin_path() . '/pro/formidable-pro.php' ) ) {
            FrmAppHelper::load_admin_wide_js();

            // user is authorized, but running free version
            $inst_install_url = 'https://formidableforms.com/knowledgebase/install-formidable-forms/';
        ?>
<div class="error" class="frm_previous_install">
		<?php
		echo wp_kses_post( apply_filters( 'frm_pro_update_msg',
			sprintf(
				__( 'This site has been previously authorized to run Formidable Forms.<br/>%1$sInstall Formidable Pro%2$s or %3$sdeauthorize%4$s this site to continue running the free version and remove this message.', 'formidable' ),
				'<a href="' . esc_url( $inst_install_url ) . '" target="_blank">', '</a>',
				'<a href="#" class="frm_deauthorize_link">', '</a>'
			), esc_url( $inst_install_url )
		) ); ?>
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
?>
<div class="update-nag frm-update-to-pro">
	<?php echo FrmAppHelper::kses( $tip['tip'] ) ?>
	<span><?php echo FrmAppHelper::kses( $tip['call'] ) ?></span>
	<a href="<?php echo esc_url( FrmAppHelper::make_affiliate_url('https://formidableforms.com?banner=1&tip=' . absint( $tip['num'] ) ) ) ?>" class="button">Upgrade to Pro</a>
</div>
<?php
		}
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
		$db_version = (int) get_option( 'frm_db_version' );
		$needs_upgrade = ( (int) $db_version < (int) FrmAppHelper::$db_version );
		if ( ! $needs_upgrade ) {
			$needs_upgrade = apply_filters( 'frm_db_needs_upgrade', $needs_upgrade );
		}
		return $needs_upgrade;
	}

	/**
	 * Check for database update and trigger js loading
	 *
	 * @since 2.0.1
	 */
	public static function admin_init() {
		if ( ! FrmAppHelper::doing_ajax() && self::needs_update() ) {
			self::network_upgrade_site();
		}

		$action = FrmAppHelper::simple_get( 'action', 'sanitize_title' );
		if ( ! FrmAppHelper::doing_ajax() || $action == 'frm_import_choices' ) {
			// don't continue during ajax calls
			self::load_tour();
			self::admin_js();
		}
	}

	/**
	 * See if we should start our tour.
	 * @since 2.0.20
	 */
	private static function load_tour() {
		$restart_tour = filter_input( INPUT_GET, 'frm_restart_tour' );
		if ( $restart_tour ) {
			delete_user_meta( get_current_user_id(), 'frm_ignore_tour' );
		}
		self::ignore_tour();

		if ( ! self::has_ignored_tour() ) {
			add_action( 'admin_enqueue_scripts', array( 'FrmPointers', 'get_instance' ) );
		}
	}

	/**
	 * Returns the value of the ignore tour.
	 *
	 * @return bool
	 */
	private static function has_ignored_tour() {
		$user_meta = get_user_meta( get_current_user_id(), 'frm_ignore_tour' );

		return ! empty( $user_meta );
	}

	/**
	 * Listener for the ignore tour GET value. If this one is set, just set the user meta to true.
	 */
	private static function ignore_tour() {
		if ( filter_input( INPUT_GET, 'frm_ignore_tour' ) && wp_verify_nonce( filter_input( INPUT_GET, 'nonce' ), 'frm-ignore-tour' ) ) {
			update_user_meta( get_current_user_id(), 'frm_ignore_tour', true );
		}
	}

    public static function admin_js() {
		$version = FrmAppHelper::plugin_version();
		FrmAppHelper::load_admin_wide_js( false );

		wp_register_script( 'formidable_admin', FrmAppHelper::plugin_url() . '/js/formidable_admin.js', array(
			'formidable_admin_global', 'formidable', 'jquery',
			'jquery-ui-core', 'jquery-ui-draggable',
			'jquery-ui-sortable',
			'bootstrap_tooltip', 'bootstrap-multiselect',
		), $version, true );
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
			wp_enqueue_style( 'formidable-grids' );
			wp_enqueue_style( 'formidable-dropzone' );
            add_thickbox();

            wp_register_script( 'formidable-editinplace', FrmAppHelper::plugin_url() . '/js/jquery/jquery.editinplace.packed.js', array( 'jquery' ), '2.3.0' );

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
     */
    public static function widget_text_filter( $content ) {
    	$regex = '/\[\s*(formidable|display-frm-data|frm-stats|frm-graph|frm-entry-links|formresults|frm-search)\s+.*\]/';
    	return preg_replace_callback( $regex, 'FrmAppHelper::widget_text_filter_callback', $content );
    }

	/**
	 * Deprecated in favor of wpmu_upgrade_site
	 */
	public static function front_head() {
		_deprecated_function( __FUNCTION__, '2.3' );
		if ( is_multisite() && self::needs_update() ) {
			self::install();
		}
	}

	public static function localize_script( $location ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmAppHelper::localize_script' );
		return FrmAppHelper::localize_script( $location );
	}

	public static function custom_stylesheet() {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmStylesController::custom_stylesheet' );
		return FrmStylesController::custom_stylesheet();
	}

	public static function load_css() {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmStylesController::load_saved_css' );
		return FrmStylesController::load_saved_css();
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
		if ( $blog_id ) {
			switch_to_blog( $blog_id );
			$upgrade_url = admin_url( 'admin-ajax.php' );
			restore_current_blog();
		} else {
			$upgrade_url = admin_url( 'admin-ajax.php' );
		}

		$upgrade_url = add_query_arg( array( 'action' => 'frm_silent_upgrade' ), $upgrade_url );
		$r = wp_remote_get( esc_url_raw( $upgrade_url ) );
		if ( is_wp_error( $r ) || ! is_array( $r ) || ! empty( $r['body'] ) ) {
			// if the remove post fails, use javascript instead
			add_action( 'admin_notices', 'FrmAppController::install_js_fallback' );
		}
	}

	/**
	 * Silent database upgrade (no redirect).
	 * Called via ajax request during network upgrade process.
	 *
	 * @since 2.0.1
	 */
	public static function ajax_install() {
		if ( self::needs_update() ) {
			self::install();
		}
		wp_die();
	}

    public static function activation_install() {
        FrmAppHelper::delete_cache_and_transient( 'frm_plugin_version' );
        FrmFormActionsController::actions_init();
        self::install();
    }

    public static function install( $old_db_version = false ) {
        $frmdb = new FrmDb();
        $frmdb->upgrade( $old_db_version );
    }

    public static function uninstall() {
		FrmAppHelper::permission_check('administrator');
        check_ajax_referer( 'frm_ajax', 'nonce' );

		$frmdb = new FrmDb();
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

    // Routes for wordpress pages -- we're just replacing content here folks.
    public static function page_route( $content ) {
        global $post;

        $frm_settings = FrmAppHelper::get_settings();
        if ( $post && $post->ID == $frm_settings->preview_page_id && isset( $_GET['form'] ) ) {
            $content = FrmFormsController::page_preview();
        }

        return $content;
    }

    public static function deauthorize() {
		FrmAppHelper::permission_check('frm_change_settings');
        check_ajax_referer( 'frm_ajax', 'nonce' );

        delete_option( 'frmpro-credentials' );
        delete_option( 'frmpro-authorized' );
        delete_site_option( 'frmpro-credentials' );
        delete_site_option( 'frmpro-authorized' );
        wp_die();
    }

    public static function get_form_shortcode( $atts ) {
        _deprecated_function( __FUNCTION__, '1.07.05', 'FrmFormsController::get_form_shortcode()' );
        return FrmFormsController::get_form_shortcode( $atts );
    }
}
