<?php

class FrmStylesController {
    public static $post_type = 'frm_styles';
    public static $screen = 'formidable_page_formidable-styles';

    public static function load_pro_hooks() {
        if ( FrmAppHelper::pro_is_installed() ) {
            FrmProStylesController::load_pro_hooks();
        }
    }

    public static function register_post_types() {
        register_post_type( self::$post_type, array(
            'label' => __( 'Styles', 'formidable' ),
            'public' => false,
            'show_ui' => false,
            'capability_type' => 'page',
			'capabilities' => array(
				'edit_post'		=> 'frm_change_settings',
				'edit_posts'	=> 'frm_change_settings',
				'edit_others_posts' => 'frm_change_settings',
				'publish_posts' => 'frm_change_settings',
				'delete_post'	=> 'frm_change_settings',
				'delete_posts'	=> 'frm_change_settings',
				'read_private_posts' => 'read_private_posts',
			),
            'supports' => array(
				'title',
            ),
            'has_archive' => false,
            'labels' => array(
				'name' => __( 'Styles', 'formidable' ),
				'singular_name' => __( 'Style', 'formidable' ),
				'menu_name' => __( 'Style', 'formidable' ),
				'edit' => __( 'Edit' ),
				'add_new_item' => __( 'Create a New Style', 'formidable' ),
				'edit_item'    => __( 'Edit Style', 'formidable' ),
			),
        ) );
    }

    public static function menu() {
		add_submenu_page('formidable', 'Formidable | ' . __( 'Styles', 'formidable' ), __( 'Styles', 'formidable' ), 'frm_change_settings', 'formidable-styles', 'FrmStylesController::route' );
    }

    public static function admin_init() {
        if ( ! FrmAppHelper::is_admin_page('formidable-styles') ) {
            return;
        }

        self::load_pro_hooks();

		$style_tab = FrmAppHelper::get_param( 'frm_action', '', 'get', 'sanitize_title' );
		if ( $style_tab == 'manage' || $style_tab == 'custom_css' ) {
			// we only need to load these styles/scripts on the styler page
			return;
		}

        wp_enqueue_script('jquery-ui-datepicker');

        $version = FrmAppHelper::plugin_version();
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_enqueue_script( 'jquery-frm-themepicker', FrmAppHelper::plugin_url() . '/js/jquery/jquery-ui-themepicker' . $suffix . '.js', array( 'jquery' ), $version );

		wp_enqueue_style( 'frm-custom-theme', admin_url('admin-ajax.php?action=frmpro_css' ) );

        $style = apply_filters('frm_style_head', false);
        if ( $style ) {
			wp_enqueue_style( 'frm-single-custom-theme', admin_url( 'admin-ajax.php?action=frmpro_load_css&flat=1' ) . '&' . http_build_query( $style->post_content ) );
        }
    }

	public static function enqueue_css( $register = 'enqueue' ) {
		global $frm_vars;

		$register_css = ( $register == 'register' );

		if ( ( $frm_vars['load_css'] || $register_css ) && ! FrmAppHelper::is_admin() ) {
			$frm_settings = FrmAppHelper::get_settings();
			if ( $frm_settings->load_style == 'none' ) {
				return;
			}

			$css = apply_filters( 'get_frm_stylesheet', self::custom_stylesheet() );

			if ( ! empty( $css ) ) {
				$version = FrmAppHelper::plugin_version();

				foreach ( (array) $css as $css_key => $file ) {
					if ( $register_css ) {
						$this_version = self::get_css_version( $css_key, $version );
						wp_register_style( $css_key, $file, array(), $this_version );
					}

					if ( 'all' == $frm_settings->load_style || $register != 'register' ) {
						wp_enqueue_style( $css_key );
					}
					unset( $css_key, $file );
				}

				if ( $frm_settings->load_style == 'all' ) {
					$frm_vars['css_loaded'] = true;
				}
			}
			unset( $css );

			add_filter( 'style_loader_tag', 'FrmStylesController::add_tags_to_css', 10, 2 );
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
			$url = admin_url( 'admin-ajax.php?action=frmpro_css' );
		}
		$stylesheet_urls['formidable'] = $url;
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

	private static function maybe_enqueue_jquery_css() {
		global $frm_vars;
		if ( isset( $frm_vars['datepicker_loaded'] ) && ! empty( $frm_vars['datepicker_loaded'] ) ) {
			FrmStylesHelper::enqueue_jquery_css();
		}
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

	public static function new_style( $return = '' ) {
        self::load_styler('default');
    }

	public static function duplicate() {
		self::load_styler('default');
	}

	public static function edit( $style_id = false, $message = '' ) {
        if ( ! $style_id ) {
			$style_id = FrmAppHelper::get_param( 'id', '', 'get', 'absint' );
            if ( empty($style_id) ) {
                $style_id = 'default';
            }
        }

        if ( 'default' == $style_id ) {
            $style = 'default';
        } else {
            $frm_style = new FrmStyle($style_id);
            $style = $frm_style->get_one();
            $style = $style->ID;
        }

        self::load_styler($style, $message);
    }

    public static function save() {
        $frm_style = new FrmStyle();
        $message = '';
		$post_id = FrmAppHelper::get_post_param( 'ID', false, 'sanitize_title' );
		$style_nonce = FrmAppHelper::get_post_param( 'frm_style', '', 'sanitize_text_field' );

		if ( $post_id !== false && wp_verify_nonce( $style_nonce, 'frm_style_nonce' ) ) {
            $id = $frm_style->update($post_id);
            if ( empty($post_id) && ! empty($id) ) {
                // set the post id to the new style so it will be loaded for editing
                $post_id = reset($id);
            }
            // include the CSS that includes this style
			echo '<link href="' . esc_url( admin_url( 'admin-ajax.php?action=frmpro_css' ) ) . '" type="text/css" rel="Stylesheet" class="frm-custom-theme" />';
            $message = __( 'Your styling settings have been saved.', 'formidable' );
        }

        return self::edit($post_id, $message);
    }

	public static function load_styler( $style, $message = '' ) {
        global $frm_settings;

        $frm_style = new FrmStyle();
        $styles = $frm_style->get_all();

        if ( is_numeric($style) ) {
            $style = $styles[ $style ];
        } else if ( 'default' == $style ) {
            $style = $frm_style->get_default_style($styles);
        }

        self::add_meta_boxes();

		include( FrmAppHelper::plugin_path() . '/classes/views/styles/show.php' );
    }

	/**
	 * @param string $message
	 * @param array|object $forms
	 */
	private static function manage( $message = '', $forms = array() ) {
        $frm_style = new FrmStyle();
        $styles = $frm_style->get_all();
        $default_style = $frm_style->get_default_style($styles);

        if ( empty($forms) ) {
            $forms = FrmForm::get_published_forms();
        }

		include( FrmAppHelper::plugin_path() . '/classes/views/styles/manage.php' );
    }

    private static function manage_styles() {
		$style_nonce = FrmAppHelper::get_post_param( 'frm_manage_style', '', 'sanitize_text_field' );
		if ( ! $_POST || ! isset( $_POST['style'] ) || ! wp_verify_nonce( $style_nonce, 'frm_manage_style_nonce' ) ) {
            return self::manage();
        }

        global $wpdb;

		$forms = FrmForm::get_published_forms();
        foreach ( $forms as $form ) {
            if ( $_POST['style'][ $form->id ] == $_POST['prev_style'][ $form->id ] ) {
                continue;
            }

            $form->options['custom_style'] = $_POST['style'][ $form->id ];

			$wpdb->update( $wpdb->prefix . 'frm_forms', array( 'options' => maybe_serialize( $form->options ) ), array( 'id' => $form->id ) );
            unset($form);
        }

        $message = __( 'Your form styles have been saved.', 'formidable' );
        return self::manage($message, $forms);
    }

    public static function custom_css( $message = '', $style = null ) {
        wp_enqueue_style('codemirror', FrmAppHelper::plugin_url() . '/css/codemirror.css');
        wp_enqueue_script('codemirror', FrmAppHelper::plugin_url() . '/js/codemirror/codemirror.js', array(), '4.7');
        wp_enqueue_script( 'codemirror-css', FrmAppHelper::plugin_url() . '/js/codemirror/css.js', array( 'codemirror' ), '4.7' );

        if ( ! isset($style) ) {
            $frm_style = new FrmStyle();
            $style = $frm_style->get_default_style();
        }

		include( FrmAppHelper::plugin_path() . '/classes/views/styles/custom_css.php' );
    }

    public static function save_css() {
        $frm_style = new FrmStyle();

        $message = '';
		$post_id = FrmAppHelper::get_post_param( 'ID', false, 'sanitize_text_field' );
		$nonce = FrmAppHelper::get_post_param( 'frm_custom_css', '', 'sanitize_text_field' );
		if ( wp_verify_nonce( $nonce, 'frm_custom_css_nonce' ) ) {
            $frm_style->update($post_id);
            $message = __( 'Your styling settings have been saved.', 'formidable' );
        }

        return self::custom_css($message);
    }

    public static function route() {
		$action = FrmAppHelper::get_param( 'frm_action', '', 'get', 'sanitize_title' );

        switch ( $action ) {
            case 'edit':
            case 'save':
            case 'manage':
            case 'manage_styles':
            case 'custom_css':
            case 'save_css':
				return self::$action();
            default:
            	do_action( 'frm_style_action_route', $action );
            	if ( apply_filters( 'frm_style_stop_action_route', false, $action ) ) {
                	return;
            	}

                if ( 'new_style' == $action || 'duplicate' == $action ) {
                    return self::$action();
                }

                return self::edit();
        }
    }

    public static function reset_styling() {
		FrmAppHelper::permission_check('frm_change_settings');
        check_ajax_referer( 'frm_ajax', 'nonce' );

        $frm_style = new FrmStyle();
        $defaults = $frm_style->get_defaults();

        echo json_encode( $defaults );
        wp_die();
    }

    public static function change_styling() {
        check_ajax_referer( 'frm_ajax', 'nonce' );

        $frm_style = new FrmStyle();
        $defaults = $frm_style->get_defaults();
		$style = '';

        // remove the # from the colors
        foreach ( $_GET['frm_style_setting']['post_content'] as $k => $v ) {
            if ( ! is_array($v) && strpos($v, '#') === 0 ) {
                $_GET['frm_style_setting']['post_content'][ $k ] = str_replace( '#', '', $v );
            }
        }

        echo '<style type="text/css">';
		include( FrmAppHelper::plugin_path() . '/css/_single_theme.css.php' );
        echo '</style>';
        wp_die();
    }

    private static function add_meta_boxes() {

        // setup meta boxes
        $meta_boxes = array(
            'general'           => __( 'General', 'formidable' ),
            'form-title'        => __( 'Form Title', 'formidable' ),
            'form-description'  => __( 'Form Description', 'formidable' ),
            'field-labels'      => __( 'Field Labels', 'formidable' ),
            'field-description' => __( 'Field Description', 'formidable' ),
            'field-colors'      => __( 'Field Colors', 'formidable' ),
            'field-sizes'       => __( 'Field Settings', 'formidable' ),
            'check-box-radio-fields' => __( 'Check Box & Radio Fields', 'formidable' ),
            'buttons'           => __( 'Buttons', 'formidable' ),
            'form-messages'     => __( 'Form Messages', 'formidable' ),
        );

		/**
		 * Add custom boxes to the styling settings
		 * @since 2.3
		 */
		$meta_boxes = apply_filters( 'frm_style_boxes', $meta_boxes );

        foreach ( $meta_boxes as $nicename => $name ) {
			add_meta_box( $nicename . '-style', $name, 'FrmStylesController::include_style_section', self::$screen, 'side', 'default', $nicename );
            unset($nicename, $name);
        }
    }

	public static function include_style_section( $atts, $sec ) {
        extract($atts);
		$current_tab = FrmAppHelper::simple_get( 'page-tab', 'sanitize_title', 'default' );
		$file_name = FrmAppHelper::plugin_path() . '/classes/views/styles/_' . $sec['args'] . '.php';

		/**
		 * Set the location of custom styling settings right before
		 * loading onto the page. If your style box was named "progress",
		 * this hook name will be frm_style_settings_progress.
		 *
		 * @since 2.3
		 */
		$file_name = apply_filters( 'frm_style_settings_' . $sec['args'], $file_name );

		include( $file_name );
    }

    public static function load_css() {
        header('Content-type: text/css');

        $frm_style = new FrmStyle();
        $defaults = $frm_style->get_defaults();
		$style = '';

		include( FrmAppHelper::plugin_path() . '/css/_single_theme.css.php' );
        wp_die();
    }

	public static function load_saved_css() {
		$css = get_transient( 'frmpro_css' );

		include( FrmAppHelper::plugin_path() . '/css/custom_theme.css.php' );
		wp_die();
	}

    /**
     * Check if the Formidable styling should be loaded,
     * then enqueue it for the footer
     * @since 2.0
     */
    public static function enqueue_style() {
        global $frm_vars;

        if ( isset( $frm_vars['css_loaded'] ) && $frm_vars['css_loaded'] ) {
            // the CSS has already been loaded
            return;
        }

        $frm_settings = FrmAppHelper::get_settings();
        if ( $frm_settings->load_style != 'none' ) {
            wp_enqueue_style( 'formidable' );
            $frm_vars['css_loaded'] = true;
        }
    }

    // Get the stylesheets for the form settings page
    public static function get_style_opts() {
        $frm_style = new FrmStyle();
        $styles = $frm_style->get_all();

        return $styles;
    }

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

        $frm_style = new FrmStyle($style);
        $style = $frm_style->get_one();

        if ( $style ) {
			$class .= ' frm_style_' . $style->post_name;
        }

        return $class;
    }

    /**
     * @param string $val
     */
	public static function get_style_val( $val, $form = 'default' ) {
        $style = self::get_form_style($form);
        if ( $style && isset( $style->post_content[ $val ] ) ) {
            return $style->post_content[ $val ];
        }
    }

	public static function show_entry_styles( $default_styles ) {
        $frm_style = new FrmStyle('default');
        $style = $frm_style->get_one();

        if ( ! $style ) {
            return $default_styles;
        }

        foreach ( $default_styles as $name => $val ) {
            $setting = $name;
            if ( 'border_width' == $name ) {
                $setting = 'field_border_width';
            } else if ( 'alt_bg_color' == $name ) {
                $setting = 'bg_color_active';
            }
            $default_styles[ $name ] = $style->post_content[ $setting ];
            unset($name, $val);
        }

        return $default_styles;
    }

	public static function &important_style( $important, $field ) {
        $important = self::get_style_val('important_style', $field['form_id']);
        return $important;
    }

    /**
     * Fallback for WP < 3.6
     */
    public static function do_accordion_sections( $screen, $context, $object ) {
        if ( function_exists( 'do_accordion_sections' ) ) {
            return do_accordion_sections( $screen, $context, $object );
        }

    	global $wp_meta_boxes;

        $screen = 'formidable_page_formidable-styles';
        $screen = convert_to_screen( $screen );

    	$page = $screen->id;

    	$hidden = get_hidden_meta_boxes( $screen );
    	?>
    	<div id="side-sortables" class="accordion-container">
    	<?php
    	$i = 0;
    	$first_open = false;
    	do {
			if ( ! isset( $wp_meta_boxes ) || ! isset( $wp_meta_boxes[ $page ] ) || ! isset( $wp_meta_boxes[ $page ][ $context ] ) ) {
				break;
			}

    		foreach ( array( 'high', 'core', 'default', 'low' ) as $priority ) {
    			if ( isset( $wp_meta_boxes[ $page ][ $context ][ $priority ] ) ) {
    				foreach ( $wp_meta_boxes[ $page ][ $context ][ $priority ] as $box ) {
    					if ( false == $box || ! $box['title'] ) {
    						continue;
						}

    					$i++;
    					$hidden_class = in_array( $box['id'], $hidden ) ? 'hide-if-js' : '';

    					if ( ! $first_open && empty( $hidden_class ) ) {
    						$first_open = true;
    					}

    					?>
						<div class="postbox <?php echo esc_attr( $box['id'] ); ?>">
						<div class="handlediv" title="<?php esc_attr_e( 'Click to toggle', 'formidable' ) ?>"><br/></div>
                        <h3 class='hndle'><span><?php echo esc_html( $box['title'] ); ?></span></h3>
                            <div class="accordion-section-content <?php postbox_classes( $box['id'], $page ); ?>">
                                <div class="inside">
    								<?php call_user_func( $box['callback'], $object, $box ); ?>
    							</div><!-- .inside -->
    						</div><!-- .accordion-section-content -->
    					</div><!-- .postbox -->
    					<?php
    				}
    			}
    		}
    	} while ( 0 );
    	?>
    	</div><!-- .accordion-container -->
    	<?php
    	return $i;
    }
}
