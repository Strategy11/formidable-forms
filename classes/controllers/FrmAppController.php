<?php

class FrmAppController {

	public static function menu() {
		add_filter( 'plugin_action_links_' . FrmAppHelper::plugin_folder() . '/formidable.php', 'FrmAppController::settings_link' );
		add_filter( 'admin_body_class', 'FrmAppController::wp_admin_body_class' );

        FrmAppHelper::maybe_add_permissions();

        if ( ! current_user_can( 'frm_view_forms' ) ) {
            return;
        }

        $count = count( get_post_types( array( 'show_ui' => true, '_builtin' => false, 'show_in_menu' => true ) ) );
        $pos = ( (int) $count > 0 ) ? '22.7' : '29.3';
        $pos = apply_filters( 'frm_menu_position', $pos );

        $frm_settings = FrmAppHelper::get_settings();
        add_menu_page( 'Formidable', $frm_settings->menu, 'frm_view_forms', 'formidable', 'FrmFormsController::route', FrmAppHelper::plugin_url() . '/images/form_16.png', $pos );
    }

    public static function load_wp_admin_style() {
        wp_enqueue_style( 'frm_fonts', FrmAppHelper::plugin_url() . '/css/frm_fonts.css', array(), FrmAppHelper::plugin_version() );
    }

    public static function get_form_nav( $form, $show_nav = '', $title = 'show' ) {
        global $pagenow, $frm_vars;

		$show_nav = FrmAppHelper::get_param( 'show_nav', $show_nav, 'get', 'absint' );
        if ( empty( $show_nav ) ) {
            return;
        }

		$current_page = isset( $_GET['page'] ) ? FrmAppHelper::simple_get( 'page', 'sanitize_title' ) : ( isset( $_GET['post_type'] ) ? FrmAppHelper::simple_get( 'post_type', 'sanitize_title' ) : 'None' );

        if ( $form ) {
            FrmFormsHelper::maybe_get_form( $form );

            if ( is_object( $form ) ) {
                $id = $form->id;
            }
        }

        if ( ! isset( $id ) ) {
            $form = $id = false;
        }

        include( FrmAppHelper::plugin_path() . '/classes/views/shared/form-nav.php' );
    }

    // Adds a settings link to the plugins page
    public static function settings_link( $links ) {
		$settings = '<a href="' . esc_url( admin_url( 'admin.php?page=formidable-settings' ) ) . '">' . __( 'Settings', 'formidable' ) . '</a>';
        array_unshift( $links, $settings );

        return $links;
    }

    public static function pro_get_started_headline() {
        // Don't display this error as we're upgrading the thing, or if the user shouldn't see the message
        if ( 'upgrade-plugin' == FrmAppHelper::simple_get( 'action', 'sanitize_title' ) || ! current_user_can( 'update_plugins' ) ) {
            return;
        }

		if ( get_site_option( 'frmpro-authorized' ) && ! file_exists( FrmAppHelper::plugin_path() . '/pro/formidable-pro.php' ) ) {
            FrmAppHelper::load_admin_wide_js();

            // user is authorized, but running free version
            $inst_install_url = 'https://formidablepro.com/knowledgebase/install-formidable-forms/';
        ?>
<div class="error" class="frm_previous_install">
		<?php
		echo wp_kses_post( apply_filters( 'frm_pro_update_msg',
			sprintf(
				__( 'This site has been previously authorized to run Formidable Forms.<br/>%1$sInstall the pro version%2$s or %3$sdeauthorize%4$s this site to continue running the free version and remove this message.', 'formidable' ),
				'<a href="' . esc_url( $inst_install_url ) . '" target="_blank">', '</a>',
				'<a href="#" class="frm_deauthorize_link">', '</a>'
			), esc_url( $inst_install_url )
		) ); ?>
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
		$pro_db_version = FrmAppHelper::pro_is_installed() ? get_option( 'frmpro_db_version' ) : false;
		return ( ( $db_version < FrmAppHelper::$db_version ) || ( FrmAppHelper::pro_is_installed() && (int) $pro_db_version < FrmAppHelper::$pro_db_version ) );
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
			self::admin_js();
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
            self::localize_script( 'admin' );

            wp_enqueue_style( 'formidable-admin' );
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
                self::localize_script( 'admin' );
            }
        } else if ( $pagenow == 'widgets.php' ) {
            FrmAppHelper::load_admin_wide_js();
        }
    }

    public static function wp_admin_body_class( $classes ) {
        global $wp_version;
        //we need this class everywhere in the admin for the menu
        if ( version_compare( $wp_version, '3.7.2', '>' ) ) {
            $classes .= ' frm_38_trigger';
        }

        return $classes;
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

    public static function widget_text_filter_callback( $matches ) {
        _deprecated_function( __FUNCTION__, '2.0', 'FrmAppHelper::widget_text_filter_callback' );
        return FrmAppHelper::widget_text_filter_callback( $matches );
    }

    public static function front_head() {
        if ( is_multisite() ) {
            $old_db_version = get_option( 'frm_db_version' );
            $pro_db_version = FrmAppHelper::pro_is_installed() ? get_option( 'frmpro_db_version' ) : false;
            if ( ( (int) $old_db_version < (int) FrmAppHelper::$db_version ) ||
                ( FrmAppHelper::pro_is_installed() && (int) $pro_db_version < (int) FrmAppHelper::$pro_db_version ) ) {
                self::install( $old_db_version );
            }
        }

        $version = FrmAppHelper::plugin_version();
        wp_register_script( 'formidable', FrmAppHelper::plugin_url() . '/js/formidable.min.js', array( 'jquery' ), $version, true );
        wp_register_script( 'jquery-placeholder', FrmAppHelper::plugin_url() . '/js/jquery/jquery.placeholder.js', array( 'jquery' ), '2.0.7', true );

        if ( FrmAppHelper::is_admin() ) {
            // don't load this in back-end
            return;
        }

        self::localize_script( 'front' );

        $frm_settings = FrmAppHelper::get_settings();

        $style = apply_filters( 'get_frm_stylesheet', self::custom_stylesheet() );
        if ( $style ) {
            foreach ( (array) $style as $k => $file ) {
                wp_register_style( $k, $file, array(), $version );
                if ( 'all' == $frm_settings->load_style ) {
                    wp_enqueue_style( $k );
                }
                unset( $k, $file );
            }
        }
        unset( $style );

        if ( $frm_settings->load_style == 'all' ) {
            global $frm_vars;
            $frm_vars['css_loaded'] = true;
        }
    }

    /**
     * @param string $location
     */
    public static function localize_script( $location ) {
        wp_localize_script( 'formidable', 'frm_js', array(
            'ajax_url'  => admin_url( 'admin-ajax.php' ),
            'images_url' => FrmAppHelper::plugin_url() . '/images',
            'loading'   => __( 'Loading&hellip;' ),
            'remove'    => __( 'Remove', 'formidable' ),
            'offset'    => apply_filters( 'frm_scroll_offset', 4 ),
            'nonce'     => wp_create_nonce( 'frm_ajax' ),
            'id'        => __( 'ID', 'formidable' ),
        ) );

        if ( $location == 'admin' ) {
            $frm_settings = FrmAppHelper::get_settings();
            wp_localize_script( 'formidable_admin', 'frm_admin_js', array(
                'confirm_uninstall' => __( 'Are you sure you want to do this? Clicking OK will delete all forms, form data, and all other Formidable data. There is no Undo.', 'formidable' ),
                'desc'              => __( '(Click to add description)', 'formidable' ),
                'blank'             => __( '(blank)', 'formidable' ),
                'no_label'          => __( '(no label)', 'formidable' ),
                'saving'            => esc_attr( __( 'Saving', 'formidable' ) ),
                'saved'             => esc_attr( __( 'Saved', 'formidable' ) ),
                'ok'                => __( 'OK' ),
                'cancel'            => __( 'Cancel', 'formidable' ),
                'default'           => __( 'Default', 'formidable' ),
                'clear_default'     => __( 'Clear default value when typing', 'formidable' ),
                'no_clear_default'  => __( 'Do not clear default value when typing', 'formidable' ),
                'valid_default'     => __( 'Default value will pass form validation', 'formidable' ),
                'no_valid_default'  => __( 'Default value will NOT pass form validation', 'formidable' ),
                'confirm'           => __( 'Are you sure?', 'formidable' ),
                'conf_delete'       => __( 'Are you sure you want to delete this field and all data associated with it?', 'formidable' ),
                'conf_delete_sec'   => __( 'WARNING: This will delete all fields inside of the section as well.', 'formidable' ),
				'conf_no_repeat'    => __( 'Warning: If you have entries with multiple rows, all but the first row will be lost.', 'formidable' ),
                'default_unique'    => $frm_settings->unique_msg,
                'default_conf'      => __( 'The entered values do not match', 'formidable' ),
                'enter_email'       => __( 'Enter Email', 'formidable' ),
                'confirm_email'     => __( 'Confirm Email', 'formidable' ),
                'enter_password'    => __( 'Enter Password', 'formidable' ),
                'confirm_password'  => __( 'Confirm Password', 'formidable' ),
                'import_complete'   => __( 'Import Complete', 'formidable' ),
                'updating'          => __( 'Please wait while your site updates.', 'formidable' ),
                'no_save_warning'   => __( 'Warning: There is no way to retrieve unsaved entries.', 'formidable' ),
                'jquery_ui_url'     => FrmAppHelper::jquery_ui_base_url(),
            ) );
        }
    }

    public static function custom_stylesheet() {
        global $frm_vars;
		$stylesheet_urls = array();
		self::maybe_enqueue_jquery_css();

        if ( ! isset( $frm_vars['css_loaded'] ) || ! $frm_vars['css_loaded'] ) {
            //include css in head
			self::get_url_to_custom_style( $stylesheet_urls );
        }

		return $stylesheet_urls;
    }

	private static function get_url_to_custom_style( &$stylesheet_urls ) {
		$uploads = FrmStylesHelper::get_upload_base();
		$saved_css_path = '/formidable/css/formidablepro.css';
		if ( is_readable( $uploads['basedir'] . $saved_css_path ) ) {
			$url = $uploads['baseurl'] . $saved_css_path;
		} else {
			$url = admin_url( 'admin-ajax.php' ) . '?action=frmpro_css';
		}
		$stylesheet_urls['formidable'] = $url;
	}

	private static function maybe_enqueue_jquery_css() {
		global $frm_vars;
		if ( isset( $frm_vars['datepicker_loaded'] ) && ! empty( $frm_vars['datepicker_loaded'] ) ) {
			FrmStylesHelper::enqueue_jquery_css();
		}
	}

    public static function load_css() {
        $css = get_transient( 'frmpro_css' );

        include( FrmAppHelper::plugin_path() . '/css/custom_theme.css.php' );
        wp_die();
    }

    public static function footer_js( $location = 'footer' ) {
        global $frm_vars;

        $frm_settings = FrmAppHelper::get_settings();
        if ( $frm_vars['load_css'] && ! FrmAppHelper::is_admin() && $frm_settings->load_style != 'none' ) {
            $css = apply_filters( 'get_frm_stylesheet', self::custom_stylesheet() );

            if ( ! empty( $css ) ) {
                foreach ( (array) $css as $css_key => $file ) {
                    wp_enqueue_style( $css_key );
                    unset( $css_key, $file );
                }
            }
            unset( $css );
        }

        if ( ! FrmAppHelper::is_admin() && $location != 'header' && ! empty( $frm_vars['forms_loaded'] ) ) {
            //load formidable js
            wp_enqueue_script( 'formidable' );
        }
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
		if ( is_wp_error( $r ) ) {
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
        check_ajax_referer( 'frm_ajax', 'nonce' );

        if ( current_user_can( 'administrator' ) ) {
            $frmdb = new FrmDb();
            $frmdb->uninstall();

			//disable the plugin and redirect after uninstall so the tables don't get added right back
			deactivate_plugins( FrmAppHelper::plugin_folder() . '/formidable.php', false, false );
			echo esc_url_raw( admin_url( 'plugins.php?deactivate=true' ) );
        } else {
            $frm_settings = FrmAppHelper::get_settings();
            wp_die( $frm_settings->admin_permission );
        }
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

    public static function update_message( $features ) {
        _deprecated_function( __FUNCTION__, '2.0', 'FrmAppHelper::update_message' );
        return FrmAppHelper::update_message( $features );
    }

    public static function deauthorize() {
        check_ajax_referer( 'frm_ajax', 'nonce' );

        delete_option( 'frmpro-credentials' );
        delete_option( 'frmpro-authorized' );
        delete_site_option( 'frmpro-credentials' );
        delete_site_option( 'frmpro-authorized' );
        wp_die();
    }

    //formidable shortcode
    public static function get_form_shortcode( $atts ) {
        _deprecated_function( __FUNCTION__, '1.07.05', 'FrmFormsController::get_form_shortcode()' );
        return FrmFormsController::get_form_shortcode( $atts );
    }

    public static function get_postbox_class() {
        _deprecated_function( __FUNCTION__, '2.0' );
        return 'postbox-container';
    }
}
