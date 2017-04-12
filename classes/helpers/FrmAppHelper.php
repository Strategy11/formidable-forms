<?php
if ( ! defined('ABSPATH') ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmAppHelper {
	public static $db_version = 43; //version of the database we are moving to
	public static $pro_db_version = 37; //deprecated

	/**
	 * @since 2.0
	 */
	public static $plug_version = '2.03.06';

    /**
     * @since 1.07.02
     *
     * @param none
     * @return string The version of this plugin
     */
    public static function plugin_version() {
        return self::$plug_version;
    }

    public static function plugin_folder() {
        return basename(self::plugin_path());
    }

    public static function plugin_path() {
        return dirname(dirname(dirname(__FILE__)));
    }

    public static function plugin_url() {
        //prevously FRM_URL constant
		return plugins_url( '', self::plugin_path() . '/formidable.php' );
    }

	public static function relative_plugin_url() {
		return str_replace( array( 'https:', 'http:' ), '', self::plugin_url() );
	}

    /**
     * @return string Site URL
     */
    public static function site_url() {
        return site_url();
    }

    /**
     * Get the name of this site
     * Used for [sitename] shortcode
     *
     * @since 2.0
     * @return string
     */
    public static function site_name() {
        return get_option('blogname');
    }

	public static function make_affiliate_url( $url ) {
		$affiliate_id = self::get_affiliate();
		if ( ! empty( $affiliate_id ) ) {
			$url = add_query_arg( 'aff', $affiliate_id, $url );
		}
		return $url;
	}

	public static function get_affiliate() {
		return '';
	}

    /**
     * Get the Formidable settings
     *
     * @since 2.0
     *
     * @param None
     * @return FrmSettings $frm_setings
     */
    public static function get_settings() {
        global $frm_settings;
        if ( empty($frm_settings) ) {
            $frm_settings = new FrmSettings();
        }
        return $frm_settings;
    }

	public static function get_menu_name() {
		$frm_settings = FrmAppHelper::get_settings();
		return $frm_settings->menu;
	}

    /**
     * Show a message in place of pro features
     *
     * @since 2.0
     */
	public static function update_message() {
		_deprecated_function( __FUNCTION__, '2.0.19' );
    }

    public static function pro_is_installed() {
        return apply_filters('frm_pro_installed', false);
    }

    /**
     * Check for certain page in Formidable settings
     *
     * @since 2.0
     *
     * @param string $page The name of the page to check
     * @return boolean
     */
	public static function is_admin_page( $page = 'formidable' ) {
        global $pagenow;
		$get_page = self::simple_get( 'page', 'sanitize_title' );
        if ( $pagenow ) {
			return $pagenow == 'admin.php' && $get_page == $page;
        }

		return is_admin() && $get_page == $page;
    }

    /**
     * Check for the form preview page
     *
     * @since 2.0
     *
     * @param None
     * @return boolean
     */
    public static function is_preview_page() {
        global $pagenow;
		$action = FrmAppHelper::simple_get( 'action', 'sanitize_title' );
		return $pagenow && $pagenow == 'admin-ajax.php' && $action == 'frm_forms_preview';
    }

    /**
     * Check for ajax except the form preview page
     *
     * @since 2.0
     *
     * @param None
     * @return boolean
     */
    public static function doing_ajax() {
        return defined('DOING_AJAX') && DOING_AJAX && ! self::is_preview_page();
    }

	/**
	 * @since 2.0.8
	 */
	public static function prevent_caching() {
		global $frm_vars;
		return isset( $frm_vars['prevent_caching'] ) && $frm_vars['prevent_caching'];
	}

    /**
     * Check if on an admin page
     *
     * @since 2.0
     *
     * @param None
     * @return boolean
     */
    public static function is_admin() {
        return is_admin() && ( ! defined('DOING_AJAX') || ! DOING_AJAX );
    }

    /**
     * Check if value contains blank value or empty array
     *
     * @since 2.0
     * @param mixed $value - value to check
	 * @param string
     * @return boolean
     */
    public static function is_empty_value( $value, $empty = '' ) {
        return ( is_array( $value ) && empty( $value ) ) || $value == $empty;
    }

    public static function is_not_empty_value( $value, $empty = '' ) {
        return ! self::is_empty_value( $value, $empty );
    }

    /**
     * Get any value from the $_SERVER
     *
     * @since 2.0
     * @param string $value
     * @return string
     */
	public static function get_server_value( $value ) {
        return isset( $_SERVER[ $value ] ) ? wp_strip_all_tags( $_SERVER[ $value ] ) : '';
    }

    /**
     * Check for the IP address in several places
     * Used by [ip] shortcode
     *
     * @return string The IP address of the current user
     */
    public static function get_ip_address() {
		$ip = '';
        foreach ( array(
            'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR',
        ) as $key ) {
            if ( ! isset( $_SERVER[ $key ] ) ) {
                continue;
            }

            foreach ( explode( ',', $_SERVER[ $key ] ) as $ip ) {
                $ip = trim($ip); // just to be safe

                if ( filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false ) {
                    return $ip;
                }
            }
        }

		return sanitize_text_field( $ip );
    }

    public static function get_param( $param, $default = '', $src = 'get', $sanitize = '' ) {
        if ( strpos($param, '[') ) {
            $params = explode('[', $param);
            $param = $params[0];
        }

		if ( $src == 'get' ) {
            $value = isset( $_POST[ $param ] ) ? stripslashes_deep( $_POST[ $param ] ) : ( isset( $_GET[ $param ] ) ? stripslashes_deep( $_GET[ $param ] ) : $default );
            if ( ! isset( $_POST[ $param ] ) && isset( $_GET[ $param ] ) && ! is_array( $value ) ) {
                $value = stripslashes_deep( htmlspecialchars_decode( $_GET[ $param ] ) );
            }
			self::sanitize_value( $sanitize, $value );
		} else {
            $value = self::get_simple_request( array( 'type' => $src, 'param' => $param, 'default' => $default, 'sanitize' => $sanitize ) );
        }

		if ( isset( $params ) && is_array( $value ) && ! empty( $value ) ) {
            foreach ( $params as $k => $p ) {
                if ( ! $k || ! is_array($value) ) {
                    continue;
                }

                $p = trim($p, ']');
                $value = isset( $value[ $p ] ) ? $value[ $p ] : $default;
            }
        }

        return $value;
    }

	public static function get_post_param( $param, $default = '', $sanitize = '' ) {
		return self::get_simple_request( array( 'type' => 'post', 'param' => $param, 'default' => $default, 'sanitize' => $sanitize ) );
	}

	/**
	 * @since 2.0
	 *
	 * @param string $param
	 * @param string $sanitize
	 * @param string $default
	 * @return string|array
	 */
	public static function simple_get( $param, $sanitize = 'sanitize_text_field', $default = '' ) {
		return self::get_simple_request( array( 'type' => 'get', 'param' => $param, 'default' => $default, 'sanitize' => $sanitize ) );
    }

	/**
	 * Get a GET/POST/REQUEST value and sanitize it
	 *
	 * @since 2.0.6
	 * @param array $args
	 * @return string|array
	 */
	public static function get_simple_request( $args ) {
		$defaults = array(
			'param' => '', 'default' => '',
			'type' => 'get', 'sanitize' => 'sanitize_text_field',
		);
		$args = wp_parse_args( $args, $defaults );

		$value = $args['default'];
		if ( $args['type'] == 'get' ) {
			if ( $_GET && isset( $_GET[ $args['param'] ] ) ) {
				$value = $_GET[ $args['param'] ];
			}
		} else if ( $args['type'] == 'post' ) {
			if ( isset( $_POST[ $args['param'] ] ) ) {
				$value = stripslashes_deep( maybe_unserialize( $_POST[ $args['param'] ] ) );
			}
		} else {
			if ( isset( $_REQUEST[ $args['param'] ] ) ) {
				$value = $_REQUEST[ $args['param'] ];
			}
		}

		self::sanitize_value( $args['sanitize'], $value );
		return $value;
	}

	/**
	* Preserve backslashes in a value, but make sure value doesn't get compounding slashes
	*
	* @since 2.0.8
	* @param string $value
	* @return string $value
	*/
	public static function preserve_backslashes( $value ) {
		// If backslashes have already been added, don't add them again
		if ( strpos( $value, '\\\\' ) === false ) {
			$value = addslashes( $value );
		}
		return $value;
	}

	public static function sanitize_value( $sanitize, &$value ) {
		if ( ! empty( $sanitize ) ) {
			if ( is_array( $value ) ) {
				$temp_values = $value;
				foreach ( $temp_values as $k => $v ) {
					FrmAppHelper::sanitize_value( $sanitize, $value[ $k ] );
				}
			} else {
				$value = call_user_func( $sanitize, $value );
			}
		}
	}

    public static function sanitize_request( $sanitize_method, &$values ) {
        $temp_values = $values;
        foreach ( $temp_values as $k => $val ) {
            if ( isset( $sanitize_method[ $k ] ) ) {
				$values[ $k ] = call_user_func( $sanitize_method[ $k ], $val );
            }
        }
    }

	public static function sanitize_array( &$values ) {
		$temp_values = $values;
		foreach ( $temp_values as $k => $val ) {
			$values[ $k ] = wp_kses_post( $val );
		}
	}

	/**
	 * Sanitize the value, and allow some HTML
	 * @since 2.0
	 * @param string $value
	 * @param array $allowed
	 * @return string
	 */
	public static function kses( $value, $allowed = array() ) {
		$html = array(
		    'a' => array(
				'href'  => array(),
				'title' => array(),
				'id'    => array(),
				'class' => array(),
		    ),
		);

		$allowed_html = array();
		foreach ( $allowed as $a ) {
			$allowed_html[ $a ] = isset( $html[ $a ] ) ? $html[ $a ] : array();
		}

		return wp_kses( $value, $allowed_html );
	}

    /**
     * Used when switching the action for a bulk action
     * @since 2.0
     */
    public static function remove_get_action() {
        if ( ! isset($_GET) ) {
            return;
        }

        $new_action = isset( $_GET['action'] ) ? sanitize_text_field( $_GET['action'] ) : ( isset( $_GET['action2'] ) ? sanitize_text_field( $_GET['action2'] ) : '' );
        if ( ! empty( $new_action ) ) {
			$_SERVER['REQUEST_URI'] = str_replace( '&action=' . $new_action, '', FrmAppHelper::get_server_value( 'REQUEST_URI' ) );
        }
    }

    /**
     * Check the WP query for a parameter
     *
     * @since 2.0
     * @return string|array
     */
    public static function get_query_var( $value, $param ) {
        if ( $value != '' ) {
            return $value;
        }

        global $wp_query;
        if ( isset( $wp_query->query_vars[ $param ] ) ) {
            $value = $wp_query->query_vars[ $param ];
        }

        return $value;
    }

    /**
     * @param string $type
     */
    public static function trigger_hook_load( $type, $object = null ) {
        // only load the form hooks once
		$hooks_loaded = apply_filters( 'frm_' . $type . '_hooks_loaded', false, $object );
        if ( ! $hooks_loaded ) {
			do_action( 'frm_load_' . $type . '_hooks' );
        }
    }

    /**
     * Check cache before fetching values and saving to cache
     *
     * @since 2.0
     *
     * @param string $cache_key The unique name for this cache
     * @param string $group The name of the cache group
     * @param string $query If blank, don't run a db call
     * @param string $type The wpdb function to use with this query
     * @return mixed $results The cache or query results
     */
    public static function check_cache( $cache_key, $group = '', $query = '', $type = 'get_var', $time = 300 ) {
        $results = wp_cache_get($cache_key, $group);
        if ( ! self::is_empty_value( $results, false ) || empty($query) ) {
            return $results;
        }

        if ( 'get_posts' == $type ) {
            $results = get_posts($query);
		} else if ( 'get_associative_results' == $type ) {
			global $wpdb;
			$results = $wpdb->get_results( $query, OBJECT_K );
        } else {
            global $wpdb;
            $results = $wpdb->{$type}($query);
        }

		self::set_cache( $cache_key, $results, $group, $time );

		return $results;
	}

	public static function set_cache( $cache_key, $results, $group = '', $time = 300 ) {
		if ( ! self::prevent_caching() ) {
			self::add_key_to_group_cache( $cache_key, $group );
			wp_cache_set( $cache_key, $results, $group, $time );
		}
	}

	/**
	 * Keep track of the keys cached in each group so they can be deleted
	 * in Redis and Memcache
	 */
	public static function add_key_to_group_cache( $key, $group ) {
		$cached = self::get_group_cached_keys( $group );
		$cached[ $key ] = $key;
		wp_cache_set( 'cached_keys', $cached, $group, 300 );
	}

	public static function get_group_cached_keys( $group ) {
		$cached = wp_cache_get( 'cached_keys', $group );
		if ( ! $cached || ! is_array( $cached ) ) {
			$cached = array();
		}

		return $cached;
	}

    /**
     * Data that should be stored for a long time can be stored in a transient.
     * First check the cache, then check the transient
     * @since 2.0
     * @return mixed The cached value or false
     */
	public static function check_cache_and_transient( $cache_key ) {
        // check caching layer first
        $results = self::check_cache( $cache_key );
        if ( $results ) {
            return $results;
        }

        // then check the transient
        $results = get_transient($cache_key);
        if ( $results ) {
            wp_cache_set($cache_key, $results);
        }

        return $results;
    }

    /**
     * @since 2.0
     * @param string $cache_key
     */
	public static function delete_cache_and_transient( $cache_key, $group = 'default' ) {
		delete_transient($cache_key);
		wp_cache_delete( $cache_key, $group );
	}

    /**
     * Delete all caching in a single group
     *
     * @since 2.0
     *
     * @param string $group The name of the cache group
     */
	public static function cache_delete_group( $group ) {
		$cached_keys = self::get_group_cached_keys( $group );

		if ( ! empty( $cached_keys ) ) {
			foreach ( $cached_keys as $key ) {
				wp_cache_delete( $key, $group );
			}

			wp_cache_delete( 'cached_keys', $group );
		}
	}

    /**
     * Check a value from a shortcode to see if true or false.
     * True when value is 1, true, 'true', 'yes'
     *
     * @since 1.07.10
     *
     * @param string $value The value to compare
     * @return boolean True or False
     */
	public static function is_true( $value ) {
        return ( true === $value || 1 == $value || 'true' == $value || 'yes' == $value );
    }

    /**
     * Used to filter shortcode in text widgets
     */
    public static function widget_text_filter_callback( $matches ) {
        return do_shortcode( $matches[0] );
    }

    public static function get_pages() {
		return get_posts( array( 'post_type' => 'page', 'post_status' => array( 'publish', 'private' ), 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
    }

    public static function wp_pages_dropdown( $field_name, $page_id, $truncate = false ) {
        $pages = self::get_pages();
		$selected = self::get_post_param( $field_name, $page_id, 'absint' );
    ?>
        <select name="<?php echo esc_attr($field_name); ?>" id="<?php echo esc_attr($field_name); ?>" class="frm-pages-dropdown">
            <option value=""> </option>
            <?php foreach ( $pages as $page ) { ?>
				<option value="<?php echo esc_attr($page->ID); ?>" <?php selected( $selected, $page->ID ) ?>>
					<?php echo esc_html( $truncate ? self::truncate( $page->post_title, $truncate ) : $page->post_title ); ?>
				</option>
            <?php } ?>
        </select>
    <?php
    }

	public static function post_edit_link( $post_id ) {
        $post = get_post($post_id);
        if ( $post ) {
			$post_url = admin_url( 'post.php?post=' . $post_id . '&action=edit' );
			return '<a href="' . esc_url( $post_url ) . '">' . self::truncate( $post->post_title, 50 ) . '</a>';
        }
        return '';
    }

	public static function wp_roles_dropdown( $field_name, $capability, $multiple = 'single' ) {
    ?>
        <select name="<?php echo esc_attr($field_name); ?>" id="<?php echo esc_attr($field_name); ?>" <?php
            echo ( 'multiple' == $multiple ) ? 'multiple="multiple"' : '';
            ?> class="frm_multiselect">
            <?php self::roles_options($capability); ?>
        </select>
    <?php
    }

	public static function roles_options( $capability ) {
        global $frm_vars;
        if ( isset($frm_vars['editable_roles']) ) {
            $editable_roles = $frm_vars['editable_roles'];
        } else {
            $editable_roles = get_editable_roles();
            $frm_vars['editable_roles'] = $editable_roles;
        }

        foreach ( $editable_roles as $role => $details ) {
            $name = translate_user_role($details['name'] ); ?>
        <option value="<?php echo esc_attr($role) ?>" <?php echo in_array($role, (array) $capability) ? ' selected="selected"' : ''; ?>><?php echo esc_attr($name) ?> </option>
<?php
            unset($role, $details);
        }
    }

	public static function frm_capabilities( $type = 'auto' ) {
        $cap = array(
            'frm_view_forms'        => __( 'View Forms and Templates', 'formidable' ),
            'frm_edit_forms'        => __( 'Add/Edit Forms and Templates', 'formidable' ),
            'frm_delete_forms'      => __( 'Delete Forms and Templates', 'formidable' ),
            'frm_change_settings'   => __( 'Access this Settings Page', 'formidable' ),
            'frm_view_entries'      => __( 'View Entries from Admin Area', 'formidable' ),
            'frm_delete_entries'    => __( 'Delete Entries from Admin Area', 'formidable' ),
        );

		if ( ! self::pro_is_installed() && 'pro' != $type ) {
            return $cap;
        }

        $cap['frm_create_entries'] = __( 'Add Entries from Admin Area', 'formidable' );
        $cap['frm_edit_entries'] = __( 'Edit Entries from Admin Area', 'formidable' );
        $cap['frm_view_reports'] = __( 'View Reports', 'formidable' );
        $cap['frm_edit_displays'] = __( 'Add/Edit Views', 'formidable' );

        return $cap;
    }

	public static function user_has_permission( $needed_role ) {
        if ( $needed_role == '-1' ) {
            return false;
		}

        // $needed_role will be equal to blank if "Logged-in users" is selected
        if ( ( $needed_role == '' && is_user_logged_in() ) || current_user_can( $needed_role ) ) {
            return true;
        }

        $roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );
        foreach ( $roles as $role ) {
			if ( current_user_can( $role ) ) {
        		return true;
			}
        	if ( $role == $needed_role ) {
        		break;
			}
        }
        return false;
    }

    /**
     * Make sure administrators can see Formidable menu
     *
     * @since 2.0
     */
    public static function maybe_add_permissions() {
		self::force_capability( 'frm_view_entries' );

        if ( ! current_user_can('administrator') || current_user_can('frm_view_forms') ) {
            return;
        }

		$user_id = get_current_user_id();
		$user = new WP_User( $user_id );
        $frm_roles = self::frm_capabilities();
        foreach ( $frm_roles as $frm_role => $frm_role_description ) {
			$user->add_cap( $frm_role );
            unset($frm_role, $frm_role_description);
        }
    }

	/**
	 * Make sure admins have permission to see the menu items
	 * @since 2.0.6
	 */
	public static function force_capability( $cap = 'frm_change_settings' ) {
		if ( current_user_can( 'administrator' ) && ! current_user_can( $cap ) ) {
			$role = get_role( 'administrator' );
			$frm_roles = self::frm_capabilities();
			foreach ( $frm_roles as $frm_role => $frm_role_description ) {
				$role->add_cap( $frm_role );
			}
		}
	}

    /**
     * Check if the user has permision for action.
     * Return permission message and stop the action if no permission
     * @since 2.0
     * @param string $permission
     */
	public static function permission_check( $permission, $show_message = 'show' ) {
        $permission_error = self::permission_nonce_error($permission);
        if ( $permission_error !== false ) {
            if ( 'hide' == $show_message ) {
                $permission_error = '';
            }
            wp_die($permission_error);
        }
    }

    /**
     * Check user permission and nonce
     * @since 2.0
     * @param string $permission
     * @return false|string The permission message or false if allowed
     */
	public static function permission_nonce_error( $permission, $nonce_name = '', $nonce = '' ) {
		if ( ! empty( $permission ) && ! current_user_can( $permission ) && ! current_user_can( 'administrator' ) ) {
			$frm_settings = self::get_settings();
			return $frm_settings->admin_permission;
		}

		$error = false;
        if ( empty($nonce_name) ) {
            return $error;
        }

        if ( $_REQUEST && ( ! isset( $_REQUEST[ $nonce_name ] ) || ! wp_verify_nonce( $_REQUEST[ $nonce_name ], $nonce ) ) ) {
            $frm_settings = self::get_settings();
            $error = $frm_settings->admin_permission;
        }

        return $error;
    }

    public static function checked( $values, $current ) {
		if ( self::check_selected( $values, $current ) ) {
            echo ' checked="checked"';
		}
    }

	public static function check_selected( $values, $current ) {
		$values = self::recursive_function_map( $values, 'trim' );
		$values = self::recursive_function_map( $values, 'htmlspecialchars_decode' );
		$current = htmlspecialchars_decode( trim( $current ) );

		return ( is_array( $values ) && in_array( $current, $values ) ) || ( ! is_array( $values ) && $values == $current );
	}

    /**
    * Check if current field option is an "other" option
    *
    * @since 2.0
    *
    * @param string $opt_key
    * @return boolean Returns true if current field option is an "Other" option
    */
    public static function is_other_opt( $opt_key ) {
        _deprecated_function( __FUNCTION__, '2.0.6', 'FrmFieldsHelper::is_other_opt' );
        return FrmFieldsHelper::is_other_opt( $opt_key );
    }

    /**
    * Get value that belongs in "Other" text box
    *
    * @since 2.0
    *
    * @param string $opt_key
    * @param array $field
    * @return string $other_val
    */
    public static function get_other_val( $opt_key, $field, $parent = false, $pointer = false ) {
		_deprecated_function( __FUNCTION__, '2.0.6', 'FrmFieldsHelper::get_other_val' );
		return FrmFieldsHelper::get_other_val( compact( 'opt_key', 'field', 'parent', 'pointer' ) );
    }

    /**
    * Check if there is a saved value for the "Other" text field. If so, set it as the $other_val.
    * Intended for front-end use
    *
    * @since 2.0
    *
    * @param array $field
    * @param boolean $other_opt
    * @param string $checked
    * @param array $args should include opt_key and field name
    * @return string $other_val
    */
    public static function prepare_other_input( $field, &$other_opt, &$checked, $args = array() ) {
		_deprecated_function( __FUNCTION__, '2.0.6', 'FrmFieldsHelper::prepare_other_input' );
		$args['field'] = $field;
		return FrmFieldsHelper::prepare_other_input( $args, $other_opt, $checked );
    }

	public static function recursive_function_map( $value, $function ) {
		if ( is_array( $value ) ) {
			$original_function = $function;
			if ( count( $value ) ) {
				$function = explode( ', ', self::prepare_array_values( $value, $function ) );
			} else {
				$function = array( $function );
			}
			if ( ! self::is_assoc( $value ) ) {
				$value = array_map( array( 'FrmAppHelper', 'recursive_function_map' ), $value, $function );
			} else {
				foreach ( $value as $k => $v ) {
					if ( ! is_array( $v ) ) {
						$value[ $k ] = call_user_func( $original_function, $v );
					}
				}
			}
		} else {
			$value = call_user_func( $function, $value );
		}

		return $value;
	}

	public static function is_assoc( $array ) {
		return (bool) count( array_filter( array_keys( $array ), 'is_string' ) );
	}

    /**
     * Flatten a multi-dimensional array
     */
	public static function array_flatten( $array, $keys = 'keep' ) {
        $return = array();
        foreach ( $array as $key => $value ) {
            if ( is_array($value) ) {
				$return = array_merge( $return, self::array_flatten( $value, $keys ) );
            } else {
				if ( $keys == 'keep' ) {
					$return[ $key ] = $value;
				} else {
					$return[] = $value;
				}
            }
        }
        return $return;
    }

	public static function esc_textarea( $text, $is_rich_text = false ) {
		$safe_text = str_replace( '&quot;', '"', $text );
		if ( ! $is_rich_text ) {
			$safe_text = htmlspecialchars( $safe_text, ENT_NOQUOTES );
		}
		$safe_text = str_replace( '&amp;', '&', $safe_text );
		return apply_filters( 'esc_textarea', $safe_text, $text );
	}

    /**
     * Add auto paragraphs to text areas
     * @since 2.0
     */
	public static function use_wpautop( $content ) {
        if ( apply_filters('frm_use_wpautop', true) ) {
            $content = wpautop(str_replace( '<br>', '<br />', $content));
        }
        return $content;
    }

	public static function replace_quotes( $val ) {
        //Replace double quotes
		$val = str_replace( array( '&#8220;', '&#8221;', '&#8243;' ), '"', $val );
        //Replace single quotes
        $val = str_replace( array( '&#8216;', '&#8217;', '&#8242;', '&prime;', '&rsquo;', '&lsquo;' ), "'", $val );
        return $val;
    }

    /**
     * @since 2.0
     * @return string The base Google APIS url for the current version of jQuery UI
     */
    public static function jquery_ui_base_url() {
		$url = 'http' . ( is_ssl() ? 's' : '' ) . '://ajax.googleapis.com/ajax/libs/jqueryui/' . self::script_version( 'jquery-ui-core', '1.11.4' );
        $url = apply_filters('frm_jquery_ui_base_url', $url);
        return $url;
    }

    /**
     * @param string $handle
     */
	public static function script_version( $handle, $default = 0 ) {
		global $wp_scripts;
		if ( ! $wp_scripts ) {
			return $default;
		}

		$ver = $default;
		if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
			return $ver;
		}

		$query = $wp_scripts->registered[ $handle ];
		if ( is_object( $query ) && ! empty( $query->ver ) ) {
			$ver = $query->ver;
		}

		return $ver;
	}

	public static function js_redirect( $url ) {
		return '<script type="text/javascript">window.location="' . esc_url_raw( $url ) . '"</script>';
    }

	public static function get_user_id_param( $user_id ) {
        if ( ! $user_id || empty($user_id) || is_numeric($user_id) ) {
            return $user_id;
        }

		if ( $user_id == 'current' ) {
			$user_id = get_current_user_id();
		} else {
            if ( is_email($user_id) ) {
                $user = get_user_by('email', $user_id);
            } else {
                $user = get_user_by('login', $user_id);
            }

            if ( $user ) {
                $user_id = $user->ID;
            }
            unset($user);
        }

        return $user_id;
    }

	public static function get_file_contents( $filename, $atts = array() ) {
        if ( ! is_file($filename) ) {
            return false;
        }

        extract($atts);
        ob_start();
        include($filename);
        $contents = ob_get_contents();
        ob_end_clean();
        return $contents;
    }

    /**
     * @param string $table_name
     * @param string $column
	 * @param int $id
	 * @param int $num_chars
     */
    public static function get_unique_key( $name = '', $table_name, $column, $id = 0, $num_chars = 5 ) {
        $key = '';

        if ( ! empty( $name ) ) {
            $key = sanitize_key($name);
        }

		if ( empty( $key ) ) {
            $max_slug_value = pow(36, $num_chars);
            $min_slug_value = 37; // we want to have at least 2 characters in the slug
            $key = base_convert( rand($min_slug_value, $max_slug_value), 10, 36 );
        }

		if ( is_numeric($key) || in_array( $key, array( 'id', 'key', 'created-at', 'detaillink', 'editlink', 'siteurl', 'evenodd' ) ) ) {
			$key = $key . 'a';
        }

		$key_check = FrmDb::get_var( $table_name, array( $column => $key, 'ID !' => $id ), $column );

        if ( $key_check || is_numeric($key_check) ) {
            $suffix = 2;
			do {
				$alt_post_name = substr( $key, 0, 200 - ( strlen( $suffix ) + 1 ) ) . $suffix;
				$key_check = FrmDb::get_var( $table_name, array( $column => $alt_post_name, 'ID !' => $id ), $column );
				$suffix++;
			} while ( $key_check || is_numeric( $key_check ) );
			$key = $alt_post_name;
        }
        return $key;
    }

    /**
     * Editing a Form or Entry
     * @param string $table
     * @return bool|array
     */
    public static function setup_edit_vars( $record, $table, $fields = '', $default = false, $post_values = array(), $args = array() ) {
        if ( ! $record ) {
            return false;
        }

        if ( empty($post_values) ) {
            $post_values = stripslashes_deep($_POST);
        }

		$values = array( 'id' => $record->id, 'fields' => array() );

		foreach ( array( 'name', 'description' ) as $var ) {
            $default_val = isset($record->{$var}) ? $record->{$var} : '';
			$values[ $var ] = self::get_param( $var, $default_val, 'get', 'wp_kses_post' );
            unset($var, $default_val);
        }

        $values['description'] = self::use_wpautop($values['description']);
        $frm_settings = self::get_settings();
        $is_form_builder = self::is_admin_page('formidable' );

        foreach ( (array) $fields as $field ) {
            // Make sure to filter default values (for placeholder text), but not on the form builder page
            if ( ! $is_form_builder ) {
                $field->default_value = apply_filters('frm_get_default_value', $field->default_value, $field, true );
            }
			$parent_form_id = isset( $args['parent_form_id'] ) ? $args['parent_form_id'] : $field->form_id;
			self::fill_field_defaults($field, $record, $values, compact('default', 'post_values', 'frm_settings', 'parent_form_id' ) );
        }

        self::fill_form_opts($record, $table, $post_values, $values);

        if ( $table == 'entries' ) {
            $values = FrmEntriesHelper::setup_edit_vars( $values, $record );
        } else if ( $table == 'forms' ) {
            $values = FrmFormsHelper::setup_edit_vars( $values, $record, $post_values );
        }

        return $values;
    }

	private static function fill_field_defaults( $field, $record, array &$values, $args ) {
        $post_values = $args['post_values'];

        if ( $args['default'] ) {
            $meta_value = $field->default_value;
        } else {
            if ( $record->post_id && self::pro_is_installed() && isset($field->field_options['post_field']) && $field->field_options['post_field'] ) {
                if ( ! isset($field->field_options['custom_field']) ) {
                    $field->field_options['custom_field'] = '';
                }
				$meta_value = FrmProEntryMetaHelper::get_post_value( $record->post_id, $field->field_options['post_field'], $field->field_options['custom_field'], array( 'truncate' => false, 'type' => $field->type, 'form_id' => $field->form_id, 'field' => $field ) );
            } else {
				$meta_value = FrmEntryMeta::get_meta_value( $record, $field->id );
            }
        }

		$field_type = isset( $post_values['field_options'][ 'type_' . $field->id ] ) ? $post_values['field_options'][ 'type_' . $field->id ] : $field->type;
        $new_value = isset( $post_values['item_meta'][ $field->id ] ) ? maybe_unserialize( $post_values['item_meta'][ $field->id ] ) : $meta_value;

        $field_array = array(
            'id'            => $field->id,
            'value'         => $new_value,
            'default_value' => $field->default_value,
            'name'          => $field->name,
            'description'   => $field->description,
            'type'          => apply_filters('frm_field_type', $field_type, $field, $new_value),
            'options'       => $field->options,
            'required'      => $field->required,
            'field_key'     => $field->field_key,
            'field_order'   => $field->field_order,
            'form_id'       => $field->form_id,
			'parent_form_id' => $args['parent_form_id'],
        );

        $args['field_type'] = $field_type;
        self::fill_field_opts($field, $field_array, $args);
		// Track the original field's type
		$field_array['original_type'] = isset( $field->field_options['original_type'] ) ? $field->field_options['original_type'] : $field->type;

        $field_array = apply_filters( 'frm_setup_edit_fields_vars', $field_array, $field, $values['id'], array() );

        if ( ! isset($field_array['unique']) || ! $field_array['unique'] ) {
            $field_array['unique_msg'] = '';
        }

        $field_array = array_merge( $field->field_options, $field_array );

        $values['fields'][ $field->id ] = $field_array;
    }

	private static function fill_field_opts( $field, array &$field_array, $args ) {
        $post_values = $args['post_values'];
        $opt_defaults = FrmFieldsHelper::get_default_field_opts($field_array['type'], $field, true);

        foreach ( $opt_defaults as $opt => $default_opt ) {
			$field_array[ $opt ] = ( $post_values && isset( $post_values['field_options'][ $opt . '_' . $field->id ] ) ) ? maybe_unserialize( $post_values['field_options'][ $opt . '_' . $field->id ] ) : ( isset( $field->field_options[ $opt ] ) ? $field->field_options[ $opt ] : $default_opt );
            if ( $opt == 'blank' && $field_array[ $opt ] == '' ) {
                $field_array[ $opt ] = $args['frm_settings']->blank_msg;
            } else if ( $opt == 'invalid' && $field_array[ $opt ] == '' ) {
                if ( $args['field_type'] == 'captcha' ) {
                    $field_array[ $opt ] = $args['frm_settings']->re_msg;
                } else {
                    $field_array[ $opt ] = sprintf( __( '%s is invalid', 'formidable' ), $field_array['name'] );
                }
            }
        }

        if ( $field_array['custom_html'] == '' ) {
            $field_array['custom_html'] = FrmFieldsHelper::get_default_html($args['field_type']);
        }
    }

    /**
     * @param string $table
     */
	private static function fill_form_opts( $record, $table, $post_values, array &$values ) {
        if ( $table == 'entries' ) {
            $form = $record->form_id;
			FrmForm::maybe_get_form( $form );
        } else {
            $form = $record;
        }

        if ( ! $form ) {
            return;
        }

        $values['form_name'] = isset($record->form_id) ? $form->name : '';
		$values['parent_form_id'] = isset( $record->form_id ) ? $form->parent_form_id : 0;

        if ( ! is_array($form->options) ) {
            return;
        }

        foreach ( $form->options as $opt => $value ) {
            $values[ $opt ] = isset( $post_values[ $opt ] ) ? maybe_unserialize( $post_values[ $opt ] ) : $value;
        }

        self::fill_form_defaults($post_values, $values);
    }

    /**
     * Set to POST value or default
     */
	private static function fill_form_defaults( $post_values, array &$values ) {
        $form_defaults = FrmFormsHelper::get_default_opts();

        foreach ( $form_defaults as $opt => $default ) {
            if ( ! isset( $values[ $opt ] ) || $values[ $opt ] == '' ) {
				$values[ $opt ] = ( $post_values && isset( $post_values['options'][ $opt ] ) ) ? $post_values['options'][ $opt ] : $default;
            }

            unset($opt, $defaut);
        }

		if ( ! isset( $values['custom_style'] ) ) {
			$values['custom_style'] = self::custom_style_value( $post_values );
		}

		foreach ( array( 'before', 'after', 'submit' ) as $h ) {
			if ( ! isset( $values[ $h . '_html' ] ) ) {
				$values[ $h . '_html' ] = ( isset( $post_values['options'][ $h . '_html' ] ) ? $post_values['options'][ $h . '_html' ] : FrmFormsHelper::get_default_html( $h ) );
            }
            unset($h);
        }
    }

	/**
	 * @since 2.2.10
	 * @param array $post_values
	 * @return boolean|int
	 */
	public static function custom_style_value( $post_values ) {
		if ( ! empty( $post_values ) && isset( $post_values['options']['custom_style'] ) ) {
			$custom_style = absint( $post_values['options']['custom_style'] );
		} else {
			$frm_settings = FrmAppHelper::get_settings();
			$custom_style = ( $frm_settings->load_style != 'none' );
		}
		return $custom_style;
	}

	public static function get_meta_value( $field_id, $entry ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmEntryMeta::get_meta_value' );
		return FrmEntryMeta::get_meta_value( $entry, $field_id );
	}

	public static function insert_opt_html( $args ) {
        $class = '';
        if ( in_array( $args['type'], array( 'email', 'user_id', 'hidden', 'select', 'radio', 'checkbox', 'phone', 'text' ) ) ) {
            $class .= 'show_frm_not_email_to';
        }
    ?>
<li>
    <a href="javascript:void(0)" class="frmids frm_insert_code alignright <?php echo esc_attr($class) ?>" data-code="<?php echo esc_attr($args['id']) ?>" >[<?php echo esc_attr( $args['id'] ) ?>]</a>
    <a href="javascript:void(0)" class="frmkeys frm_insert_code alignright <?php echo esc_attr($class) ?>" data-code="<?php echo esc_attr($args['key']) ?>" >[<?php echo esc_attr( self::truncate($args['key'], 10) ) ?>]</a>
    <a href="javascript:void(0)" class="frm_insert_code <?php echo esc_attr( $class ) ?>" data-code="<?php echo esc_attr($args['id']) ?>" ><?php echo esc_attr( self::truncate($args['name'], 60) ) ?></a>
</li>
    <?php
    }

	public static function truncate( $str, $length, $minword = 3, $continue = '...' ) {
        if ( is_array( $str ) ) {
            return '';
		}

        $length = (int) $length;
		$str = wp_strip_all_tags( $str );
		$original_len = self::mb_function( array( 'mb_strlen', 'strlen' ), array( $str ) );

		if ( $length == 0 ) {
            return '';
        } else if ( $length <= 10 ) {
			$sub = self::mb_function( array( 'mb_substr', 'substr' ), array( $str, 0, $length ) );
            return $sub . (($length < $original_len) ? $continue : '');
        }

        $sub = '';
        $len = 0;

		$words = self::mb_function( array( 'mb_split', 'explode' ), array( ' ', $str ) );

		foreach ( $words as $word ) {
            $part = (($sub != '') ? ' ' : '') . $word;
			$total_len = self::mb_function( array( 'mb_strlen', 'strlen' ), array( $sub . $part ) );
            if ( $total_len > $length && str_word_count($sub) ) {
                break;
            }

            $sub .= $part;
			$len += self::mb_function( array( 'mb_strlen', 'strlen' ), array( $part ) );

            if ( str_word_count($sub) > $minword && $total_len >= $length ) {
                break;
            }

            unset($total_len, $word);
        }

        return $sub . (($len < $original_len) ? $continue : '');
    }

	public static function mb_function( $function_names, $args ) {
		$mb_function_name = $function_names[0];
		$function_name = $function_names[1];
		if ( function_exists( $mb_function_name ) ) {
			$function_name = $mb_function_name;
		}
		return call_user_func_array( $function_name, $args );
	}

	public static function get_formatted_time( $date, $date_format = '', $time_format = '' ) {
        if ( empty($date) ) {
            return $date;
        }

        if ( empty($date_format) ) {
            $date_format = get_option('date_format');
        }

        if ( preg_match('/^\d{1-2}\/\d{1-2}\/\d{4}$/', $date) && self::pro_is_installed() ) {
            $frmpro_settings = new FrmProSettings();
            $date = FrmProAppHelper::convert_date($date, $frmpro_settings->date_format, 'Y-m-d');
        }

		$formatted = self::get_localized_date( $date_format, $date );

		$do_time = ( date( 'H:i:s', strtotime( $date ) ) != '00:00:00' );
		if ( $do_time ) {
			$formatted .= self::add_time_to_date( $time_format, $date );
		}

        return $formatted;
    }

	private static function add_time_to_date( $time_format, $date ) {
		if ( empty( $time_format ) ) {
			$time_format = get_option('time_format');
		}

		$trimmed_format = trim( $time_format );
		$time = '';
		if ( $time_format && ! empty( $trimmed_format ) ) {
			$time = ' ' . __( 'at', 'formidable' ) . ' ' . self::get_localized_date( $time_format, $date );
		}

		return $time;
	}

	/**
	 * @since 2.0.8
	 */
	public static function get_localized_date( $date_format, $date ) {
		$date = get_date_from_gmt( $date );
		return date_i18n( $date_format, strtotime( $date ) );
	}

	/**
	 * Gets the time ago in words
	 *
	 * @param int $from in seconds
	 * @param int|string $to in seconds
	 * @return string $time_ago
	 */
	public static function human_time_diff( $from, $to = '', $levels = 1 ) {
		if ( empty( $to ) ) {
			$now = new DateTime;
		} else {
			$now = new DateTime( '@' . $to );
		}
		$ago = new DateTime( '@' . $from );

		// Get the time difference
		$diff_object = $now->diff( $ago );
		$diff = get_object_vars( $diff_object );

		// Add week amount and update day amount
		$diff['w'] = floor( $diff['d'] / 7 );
		$diff['d'] -= $diff['w'] * 7;

		$time_strings = self::get_time_strings();

		foreach ( $time_strings as $k => $v ) {
			if ( $diff[ $k ] ) {
				$time_strings[ $k ] = $diff[ $k ] . ' ' . ( $diff[ $k ] > 1 ? $v[1] : $v[0] );
			} else {
				unset( $time_strings[ $k ] );
			}
		}

		$levels_deep = apply_filters( 'frm_time_ago_levels', $levels, compact( 'time_strings', 'from', 'to' ) );
		$time_strings = array_slice( $time_strings, 0, $levels_deep );
		$time_ago_string = $time_strings ? implode( ' ', $time_strings ) : '0 ' . __( 'seconds', 'formidable' );

		return $time_ago_string;
	}

	/**
	 * Get the translatable time strings
	 *
	 * @since 2.0.20
	 * @return array
	 */
	private static function get_time_strings() {
		return array(
			'y' => array( __( 'year', 'formidable' ), __( 'years', 'formidable' ) ),
			'm' => array( __( 'month', 'formidable' ), __( 'months', 'formidable' ) ),
			'w' => array( __( 'week', 'formidable' ), __( 'weeks', 'formidable' ) ),
			'd' => array( __( 'day', 'formidable' ), __( 'days', 'formidable' ) ),
			'h' => array( __( 'hour', 'formidable' ), __( 'hours', 'formidable' ) ),
			'i' => array( __( 'minute', 'formidable' ), __( 'minutes', 'formidable' ) ),
			's' => array( __( 'second', 'formidable' ), __( 'seconds', 'formidable' ) ),
		);
	}

    /**
     * Added for < WP 4.0 compatability
     *
     * @since 1.07.10
     *
     * @param string $term The value to escape
     * @return string The escaped value
     */
	public static function esc_like( $term ) {
        global $wpdb;
        if ( method_exists($wpdb, 'esc_like') ) {
			// WP 4.0
            $term = $wpdb->esc_like( $term );
        } else {
            $term = like_escape( $term );
        }

        return $term;
    }

    /**
     * @param string $order_query
     */
	public static function esc_order( $order_query ) {
        if ( empty($order_query) ) {
            return '';
        }

        // remove ORDER BY before santizing
        $order_query = strtolower($order_query);
        if ( strpos($order_query, 'order by') !== false ) {
            $order_query = str_replace('order by', '', $order_query);
        }

        $order_query = explode(' ', trim($order_query));

        $order_fields = array(
            'id', 'form_key', 'name', 'description',
            'parent_form_id', 'logged_in', 'is_template',
            'default_template', 'status', 'created_at',
        );

        $order = trim(trim(reset($order_query), ','));
        if ( ! in_array($order, $order_fields) ) {
            return '';
        }

        $order_by = '';
        if ( count($order_query) > 1 ) {
			$order_by = end( $order_query );
			self::esc_order_by( $order_by );
        }

		return ' ORDER BY ' . $order . ' ' . $order_by;
    }

	/**
	 * Make sure this is ordering by either ASC or DESC
	 */
	public static function esc_order_by( &$order_by ) {
		$sort_options = array( 'asc', 'desc' );
		if ( ! in_array( strtolower( $order_by ), $sort_options ) ) {
			$order_by = 'asc';
		}
	}

    /**
     * @param string $limit
     */
	public static function esc_limit( $limit ) {
        if ( empty($limit) ) {
            return '';
        }

        $limit = trim(str_replace(' limit', '', strtolower($limit)));
        if ( is_numeric($limit) ) {
			return ' LIMIT ' . $limit;
        }

        $limit = explode(',', trim($limit));
        foreach ( $limit as $k => $l ) {
            if ( is_numeric( $l ) ) {
                $limit[ $k ] = $l;
            }
        }

        $limit = implode(',', $limit);
		return ' LIMIT ' . $limit;
    }

    /**
     * Get an array of values ready to go through $wpdb->prepare
     * @since 2.0
     */
    public static function prepare_array_values( $array, $type = '%s' ) {
        $placeholders = array_fill(0, count($array), $type);
        return implode(', ', $placeholders);
    }

    public static function prepend_and_or_where( $starts_with = ' WHERE ', $where = '' ) {
        if ( empty($where) ) {
            return '';
        }

		if ( is_array( $where ) ) {
            global $wpdb;
            FrmDb::get_where_clause_and_values( $where, $starts_with );
			$where = $wpdb->prepare( $where['where'], $where['values'] );
		} else {
            $where = $starts_with . $where;
        }

        return $where;
    }

    // Pagination Methods

    /**
     * @param integer $current_p
     */
	public static function get_last_record_num( $r_count, $current_p, $p_size ) {
		return ( ( $r_count < ( $current_p * $p_size ) ) ? $r_count : ( $current_p * $p_size ) );
	}

    /**
     * @param integer $current_p
     */
    public static function get_first_record_num( $r_count, $current_p, $p_size ) {
        if ( $current_p == 1 ) {
            return 1;
        } else {
            return ( self::get_last_record_num( $r_count, ( $current_p - 1 ), $p_size ) + 1 );
        }
    }

	/**
	 * @return array
	 */
	public static function json_to_array( $json_vars ) {
        $vars = array();
        foreach ( $json_vars as $jv ) {
            $jv_name = explode('[', $jv['name']);
            $last = count($jv_name) - 1;
            foreach ( $jv_name as $p => $n ) {
                $name = trim($n, ']');
                if ( ! isset($l1) ) {
                    $l1 = $name;
                }

                if ( ! isset($l2) ) {
                    $l2 = $name;
                }

                if ( ! isset($l3) ) {
                    $l3 = $name;
                }

                $this_val = ( $p == $last ) ? $jv['value'] : array();

                switch ( $p ) {
                    case 0:
                        $l1 = $name;
                        self::add_value_to_array( $name, $l1, $this_val, $vars );
                    break;

                    case 1:
                        $l2 = $name;
                        self::add_value_to_array( $name, $l2, $this_val, $vars[ $l1 ] );
                    break;

                    case 2:
                        $l3 = $name;
                        self::add_value_to_array( $name, $l3, $this_val, $vars[ $l1 ][ $l2 ] );
                    break;

                    case 3:
                        $l4 = $name;
                        self::add_value_to_array( $name, $l4, $this_val, $vars[ $l1 ][ $l2 ][ $l3 ] );
                    break;
                }

                unset($this_val, $n);
            }

            unset($last, $jv);
        }

        return $vars;
    }

    /**
     * @param string $name
     * @param string $l1
     */
    public static function add_value_to_array( $name, $l1, $val, &$vars ) {
        if ( $name == '' ) {
            $vars[] = $val;
        } else if ( ! isset( $vars[ $l1 ] ) ) {
            $vars[ $l1 ] = $val;
        }
    }

	public static function maybe_add_tooltip( $name, $class = 'closed', $form_name = '' ) {
        $tooltips = array(
            'action_title'  => __( 'Give this action a label for easy reference.', 'formidable' ),
            'email_to'      => __( 'Add one or more recipient addresses separated by a ",".  FORMAT: Name <name@email.com> or name@email.com.  [admin_email] is the address set in WP General Settings.', 'formidable' ),
            'cc'            => __( 'Add CC addresses separated by a ",".  FORMAT: Name <name@email.com> or name@email.com.', 'formidable' ),
            'bcc'           => __( 'Add BCC addresses separated by a ",".  FORMAT: Name <name@email.com> or name@email.com.', 'formidable' ),
            'reply_to'      => __( 'If you would like a different reply to address than the "from" address, add a single address here.  FORMAT: Name <name@email.com> or name@email.com.', 'formidable' ),
            'from'          => __( 'Enter the name and/or email address of the sender. FORMAT: John Bates <john@example.com> or john@example.com.', 'formidable' ),
            'email_subject' => esc_attr( sprintf( __( 'If you leave the subject blank, the default will be used: %1$s Form submitted on %2$s', 'formidable' ), $form_name, self::site_name() ) ),
        );

        if ( ! isset( $tooltips[ $name ] ) ) {
            return;
        }

        if ( 'open' == $class ) {
            echo ' frm_help"';
        } else {
            echo ' class="frm_help"';
        }

		echo ' title="' . esc_attr( $tooltips[ $name ] );

        if ( 'open' != $class ) {
            echo '"';
        }
    }

	/**
	 * Add the current_page class to that page in the form nav
	 */
	public static function select_current_page( $page, $current_page, $action = array() ) {
		if ( $current_page != $page ) {
			return;
		}

		$frm_action = FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );
		if ( empty( $action ) || ( ! empty( $frm_action ) && in_array( $frm_action, $action ) ) ) {
			echo ' class="current_page"';
		}
	}

    /**
     * Prepare and json_encode post content
     *
     * @since 2.0
     *
     * @param array $post_content
     * @return string $post_content ( json encoded array )
     */
    public static function prepare_and_encode( $post_content ) {
        //Loop through array to strip slashes and add only the needed ones
		foreach ( $post_content as $key => $val ) {
			// Replace problematic characters (like &quot;)
			$val = str_replace( '&quot;', '"', $val );

			self::prepare_action_slashes( $val, $key, $post_content );
            unset( $key, $val );
        }

        // json_encode the array
        $post_content = json_encode( $post_content );

	    // add extra slashes for \r\n since WP strips them
		$post_content = str_replace( array( '\\r', '\\n', '\\u', '\\t' ), array( '\\\\r', '\\\\n', '\\\\u', '\\\\t' ), $post_content );

        // allow for &quot
	    $post_content = str_replace( '&quot;', '\\"', $post_content );

        return $post_content;
    }

	private static function prepare_action_slashes( $val, $key, &$post_content ) {
		if ( ! isset( $post_content[ $key ] ) ) {
			return;
		}

		if ( is_array( $val ) ) {
			foreach ( $val as $k1 => $v1 ) {
				self::prepare_action_slashes( $v1, $k1, $post_content[ $key ] );
				unset( $k1, $v1 );
			}
		} else {
			// Strip all slashes so everything is the same, no matter where the value is coming from
			$val = stripslashes( $val );

			// Add backslashes before double quotes and forward slashes only
			$post_content[ $key ] = addcslashes( $val, '"\\/' );
		}
	}

	/**
	 * Prepare and save settings in styles and actions
	 *
	 * @param array $settings
	 * @param string $group
	 *
	 * @since 2.0.6
	 */
	public static function save_settings( $settings, $group ) {
		$settings = (array) $settings;
		$settings['post_content'] = FrmAppHelper::prepare_and_encode( $settings['post_content'] );

		if ( empty( $settings['ID'] ) ) {
			unset( $settings['ID']);
		}

		// delete all caches for this group
		self::cache_delete_group( $group );

		return self::save_json_post( $settings );
	}

	/**
	 * Since actions are JSON encoded, we don't want any filters messing with it.
	 * Remove the filters and then add them back in case any posts or views are
	 * also being imported.
	 *
	 * Used when saving form actions and styles
	 *
	 * @since 2.0.4
	 */
	public static function save_json_post( $settings ) {
		global $wp_filter;
		$filters = $wp_filter['content_save_pre'];

		// Remove the balanceTags filter in case WordPress is trying to validate the XHTML
		remove_all_filters( 'content_save_pre' );

		$post = wp_insert_post( $settings );

		// add the content filters back for views or posts
		$wp_filter['content_save_pre'] = $filters;

		return $post;
	}

	public static function maybe_json_decode( $string ) {
        if ( is_array($string) ) {
            return $string;
        }

        $new_string = json_decode($string, true);
        if ( function_exists('json_last_error') ) {
			// php 5.3+
            if ( json_last_error() == JSON_ERROR_NONE ) {
                $string = $new_string;
            }
        } else if ( isset($new_string) ) {
			// php < 5.3 fallback
            $string = $new_string;
        }
        return $string;
    }

    /**
     * @since 1.07.10
     *
     * @param string $post_type The name of the post type that may need to be highlighted
     * echo The javascript to open and highlight the Formidable menu
     */
	public static function maybe_highlight_menu( $post_type ) {
        global $post;

        if ( isset($_REQUEST['post_type']) && $_REQUEST['post_type'] != $post_type ) {
            return;
        }

        if ( is_object($post) && $post->post_type != $post_type ) {
            return;
        }

        self::load_admin_wide_js();
        echo '<script type="text/javascript">jQuery(document).ready(function(){frmSelectSubnav();});</script>';
    }

    /**
     * Load the JS file on non-Formidable pages in the admin area
     * @since 2.0
     */
	public static function load_admin_wide_js( $load = true ) {
        $version = FrmAppHelper::plugin_version();
		wp_register_script( 'formidable_admin_global', FrmAppHelper::plugin_url() . '/js/formidable_admin_global.js', array( 'jquery' ), $version );

        wp_localize_script( 'formidable_admin_global', 'frmGlobal', array(
			'updating_msg' => __( 'Please wait while your site updates.', 'formidable' ),
            'deauthorize'  => __( 'Are you sure you want to deauthorize Formidable Forms on this site?', 'formidable' ),
			'url'          => FrmAppHelper::plugin_url(),
			'loading'      => __( 'Loading&hellip;' ),
			'nonce'        => wp_create_nonce( 'frm_ajax' ),
        ) );

		if ( $load ) {
			wp_enqueue_script( 'formidable_admin_global' );
		}
    }

	/**
	 * @since 2.0.9
	 */
	public static function load_font_style() {
		wp_enqueue_style( 'frm_fonts', self::plugin_url() . '/css/frm_fonts.css', array(), self::plugin_version() );
	}

    /**
     * @param string $location
     */
	public static function localize_script( $location ) {
		$ajax_url = admin_url( 'admin-ajax.php', is_ssl() ? 'admin' : 'http' );
		$ajax_url = apply_filters( 'frm_ajax_url', $ajax_url );

		wp_localize_script( 'formidable', 'frm_js', array(
			'ajax_url'  => $ajax_url,
			'images_url' => self::plugin_url() . '/images',
			'loading'   => __( 'Loading&hellip;' ),
			'remove'    => __( 'Remove', 'formidable' ),
			'offset'    => apply_filters( 'frm_scroll_offset', 4 ),
			'nonce'     => wp_create_nonce( 'frm_ajax' ),
			'id'        => __( 'ID', 'formidable' ),
			'no_results' => __( 'No results match', 'formidable' ),
		) );

		if ( $location == 'admin' ) {
			$frm_settings = self::get_settings();
			wp_localize_script( 'formidable_admin', 'frm_admin_js', array(
				'confirm_uninstall' => __( 'Are you sure you want to do this? Clicking OK will delete all forms, form data, and all other Formidable data. There is no Undo.', 'formidable' ),
				'desc'              => __( '(Click to add description)', 'formidable' ),
				'blank'             => __( '(Blank)', 'formidable' ),
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
				'css_invalid_size'  => __( 'In certain browsers (e.g. Firefox) text will not display correctly if the field height is too small relative to the field padding and text size. Please increase your field height or decrease your field padding.', 'formidable' ),
				'enter_password'    => __( 'Enter Password', 'formidable' ),
				'confirm_password'  => __( 'Confirm Password', 'formidable' ),
				'import_complete'   => __( 'Import Complete', 'formidable' ),
				'updating'          => __( 'Please wait while your site updates.', 'formidable' ),
				'no_save_warning'   => __( 'Warning: There is no way to retrieve unsaved entries.', 'formidable' ),
				'private'           => __( 'Private' ),
				'jquery_ui_url'     => self::jquery_ui_base_url(),
				'no_licenses'       => __( 'No new licenses were found', 'formidable' ),
			) );
		}
	}

    /**
	 * echo the message on the plugins listing page
     * @since 1.07.10
     *
     * @param float $min_version The version the add-on requires
     */
	public static function min_version_notice( $min_version ) {
        $frm_version = self::plugin_version();

        // check if Formidable meets minimum requirements
        if ( version_compare($frm_version, $min_version, '>=') ) {
            return;
        }

        $wp_list_table = _get_list_table('WP_Plugins_List_Table');
		echo '<tr class="plugin-update-tr active"><th colspan="' . absint( $wp_list_table->get_column_count() ) . '" class="check-column plugin-update colspanchange"><div class="update-message">' .
        __( 'You are running an outdated version of Formidable. This plugin may not work correctly if you do not update Formidable.', 'formidable' ) .
        '</div></td></tr>';
    }

    public static function locales( $type = 'date' ) {
        $locales = array(
            'en' => __( 'English', 'formidable' ),    '' => __( 'English/Western', 'formidable' ),
            'af' => __( 'Afrikaans', 'formidable' ),  'sq' => __( 'Albanian', 'formidable' ),
            'ar' => __( 'Arabic', 'formidable' ),     'hy' => __( 'Armenian', 'formidable' ),
            'az' => __( 'Azerbaijani', 'formidable' ), 'eu' => __( 'Basque', 'formidable' ),
            'bs' => __( 'Bosnian', 'formidable' ),    'bg' => __( 'Bulgarian', 'formidable' ),
            'ca' => __( 'Catalan', 'formidable' ),    'zh-HK' => __( 'Chinese Hong Kong', 'formidable' ),
            'zh-CN' => __( 'Chinese Simplified', 'formidable' ), 'zh-TW' => __( 'Chinese Traditional', 'formidable' ),
            'hr' => __( 'Croatian', 'formidable' ),   'cs' => __( 'Czech', 'formidable' ),
            'da' => __( 'Danish', 'formidable' ),     'nl' => __( 'Dutch', 'formidable' ),
            'en-GB' => __( 'English/UK', 'formidable' ), 'eo' => __( 'Esperanto', 'formidable' ),
            'et' => __( 'Estonian', 'formidable' ),   'fo' => __( 'Faroese', 'formidable' ),
            'fa' => __( 'Farsi/Persian', 'formidable' ), 'fil' => __( 'Filipino', 'formidable' ),
            'fi' => __( 'Finnish', 'formidable' ),    'fr' => __( 'French', 'formidable' ),
            'fr-CA' => __( 'French/Canadian', 'formidable' ), 'fr-CH' => __( 'French/Swiss', 'formidable' ),
            'de' => __( 'German', 'formidable' ),     'de-AT' => __( 'German/Austria', 'formidable' ),
            'de-CH' => __( 'German/Switzerland', 'formidable' ), 'el' => __( 'Greek', 'formidable' ),
            'he' => __( 'Hebrew', 'formidable' ),     'iw' => __( 'Hebrew', 'formidable' ),
            'hi' => __( 'Hindi', 'formidable' ),      'hu' => __( 'Hungarian', 'formidable' ),
            'is' => __( 'Icelandic', 'formidable' ),  'id' => __( 'Indonesian', 'formidable' ),
            'it' => __( 'Italian', 'formidable' ),    'ja' => __( 'Japanese', 'formidable' ),
            'ko' => __( 'Korean', 'formidable' ),     'lv' => __( 'Latvian', 'formidable' ),
            'lt' => __( 'Lithuanian', 'formidable' ), 'ms' => __( 'Malaysian', 'formidable' ),
            'no' => __( 'Norwegian', 'formidable' ),  'pl' => __( 'Polish', 'formidable' ),
            'pt' => __( 'Portuguese', 'formidable' ), 'pt-BR' => __( 'Portuguese/Brazilian', 'formidable' ),
            'pt-PT' => __( 'Portuguese/Portugal', 'formidable' ), 'ro' => __( 'Romanian', 'formidable' ),
            'ru' => __( 'Russian', 'formidable' ),    'sr' => __( 'Serbian', 'formidable' ),
            'sr-SR' => __( 'Serbian', 'formidable' ), 'sk' => __( 'Slovak', 'formidable' ),
            'sl' => __( 'Slovenian', 'formidable' ),  'es' => __( 'Spanish', 'formidable' ),
            'es-419' => __( 'Spanish/Latin America', 'formidable' ), 'sv' => __( 'Swedish', 'formidable' ),
            'ta' => __( 'Tamil', 'formidable' ),      'th' => __( 'Thai', 'formidable' ),
            'tu' => __( 'Turkish', 'formidable' ),    'tr' => __( 'Turkish', 'formidable' ),
            'uk' => __( 'Ukranian', 'formidable' ),   'vi' => __( 'Vietnamese', 'formidable' ),
        );

        if ( $type == 'captcha' ) {
            // remove the languages unavailable for the captcha
            $unset = array(
                '', 'af', 'sq', 'hy', 'az', 'eu', 'bs',
                'zh-HK', 'eo', 'et', 'fo', 'fr-CH',
                'he', 'is', 'ms', 'sr-SR', 'ta', 'tu',
            );
        } else {
            // remove the languages unavailable for the datepicker
            $unset = array(
                'en', 'fil', 'fr-CA', 'de-AT', 'de-AT',
                'de-CH', 'iw', 'hi', 'pt', 'pt-PT',
                'es-419', 'tr',
            );
        }

        $locales = array_diff_key($locales, array_flip($unset));
        $locales = apply_filters('frm_locales', $locales);

        return $locales;
    }
}
