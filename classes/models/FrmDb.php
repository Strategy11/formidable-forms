<?php

class FrmDb {
    var $fields;
    var $forms;
    var $entries;
    var $entry_metas;

    public function __construct() {
        if ( ! defined('ABSPATH') ) {
            die('You are not allowed to call this page directly.');
        }

        global $wpdb;
        $this->fields         = $wpdb->prefix . 'frm_fields';
        $this->forms          = $wpdb->prefix . 'frm_forms';
        $this->entries        = $wpdb->prefix . 'frm_items';
        $this->entry_metas    = $wpdb->prefix . 'frm_item_metas';
    }

    public function upgrade( $old_db_version = false ) {
	    do_action( 'frm_before_install' );

        global $wpdb;
        //$frm_db_version is the version of the database we're moving to
        $frm_db_version = FrmAppHelper::$db_version;
        $old_db_version = (float) $old_db_version;
        if ( ! $old_db_version ) {
            $old_db_version = get_option('frm_db_version');
        }

        if ( $frm_db_version != $old_db_version ) {
			// update rewrite rules for views and other custom post types
			flush_rewrite_rules();

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            $this->create_tables();
            $this->migrate_data($frm_db_version, $old_db_version);

            /***** SAVE DB VERSION *****/
            update_option('frm_db_version', $frm_db_version);

            /**** ADD/UPDATE DEFAULT TEMPLATES ****/
            FrmXMLController::add_default_templates();

			if ( ! $old_db_version ) {
				$this->maybe_create_contact_form();
			}
        }

        do_action('frm_after_install');

        /**** update the styling settings ****/
		if ( is_admin() && function_exists( 'get_filesystem_method' ) ) {
			$frm_style = new FrmStyle();
			$frm_style->update( 'default' );
		}
    }

    public function collation() {
        global $wpdb;
        if ( ! $wpdb->has_cap( 'collation' ) ) {
            return '';
        }

        $charset_collate = '';
		if ( ! empty( $wpdb->charset ) ) {
			$charset_collate .= ' DEFAULT CHARACTER SET ' . $wpdb->charset;
		}

		if ( ! empty( $wpdb->collate ) ) {
			$charset_collate .= ' COLLATE ' . $wpdb->collate;
		}

        return $charset_collate;
    }

    private function create_tables() {
        $charset_collate = $this->collation();
        $sql = array();

        /* Create/Upgrade Fields Table */
		$sql[] = 'CREATE TABLE ' . $this->fields . ' (
				id BIGINT(20) NOT NULL auto_increment,
				field_key varchar(100) default NULL,
                name text default NULL,
                description longtext default NULL,
                type text default NULL,
                default_value longtext default NULL,
                options longtext default NULL,
                field_order int(11) default 0,
                required int(1) default NULL,
                field_options longtext default NULL,
                form_id int(11) default NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY form_id (form_id),
                UNIQUE KEY field_key (field_key)
        )';

        /* Create/Upgrade Forms Table */
		$sql[] = 'CREATE TABLE ' . $this->forms . ' (
                id int(11) NOT NULL auto_increment,
				form_key varchar(100) default NULL,
                name varchar(255) default NULL,
                description text default NULL,
                parent_form_id int(11) default 0,
                logged_in tinyint(1) default NULL,
                editable tinyint(1) default NULL,
                is_template tinyint(1) default 0,
                default_template tinyint(1) default 0,
                status varchar(255) default NULL,
                options longtext default NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY form_key (form_key)
        )';

        /* Create/Upgrade Items Table */
		$sql[] = 'CREATE TABLE ' . $this->entries . ' (
				id BIGINT(20) NOT NULL auto_increment,
				item_key varchar(100) default NULL,
                name varchar(255) default NULL,
                description text default NULL,
                ip text default NULL,
				form_id BIGINT(20) default NULL,
				post_id BIGINT(20) default NULL,
				user_id BIGINT(20) default NULL,
				parent_item_id BIGINT(20) default 0,
				is_draft tinyint(1) default 0,
				updated_by BIGINT(20) default NULL,
                created_at datetime NOT NULL,
                updated_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY form_id (form_id),
                KEY post_id (post_id),
                KEY user_id (user_id),
                KEY parent_item_id (parent_item_id),
                UNIQUE KEY item_key (item_key)
        )';

        /* Create/Upgrade Meta Table */
		$sql[] = 'CREATE TABLE ' . $this->entry_metas . ' (
				id BIGINT(20) NOT NULL auto_increment,
				meta_value longtext default NULL,
				field_id BIGINT(20) NOT NULL,
				item_id BIGINT(20) NOT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY field_id (field_id),
                KEY item_id (item_id)
        )';

        foreach ( $sql as $q ) {
			if ( function_exists( 'dbDelta' ) ) {
				dbDelta( $q . $charset_collate . ';' );
			} else {
				global $wpdb;
				$wpdb->query( $q . $charset_collate );
			}
            unset($q);
        }
    }

	private function maybe_create_contact_form() {
		$template_id = FrmForm::getIdByKey( 'contact' );
		if ( $template_id ) {
			$form_id = FrmForm::duplicate( $template_id, false, true );
			if ( $form_id ) {
				$values = array(
					'status'   => 'published',
					'form_key' => 'contact-form',
				);
				FrmForm::update( $form_id, $values );
			}
		}
	}

    /**
     * @param integer $frm_db_version
	 * @param int $old_db_version
     */
	private function migrate_data( $frm_db_version, $old_db_version ) {
		$migrations = array( 4, 6, 11, 16, 17, 23, 25 );
        foreach ( $migrations as $migration ) {
            if ( $frm_db_version >= $migration && $old_db_version < $migration ) {
				$function_name = 'migrate_to_' . $migration;
                $this->$function_name();
            }
        }
    }

    /**
     * Change array into format $wpdb->prepare can use
	 *
	 * @param array $args
	 * @param string $starts_with
     */
    public static function get_where_clause_and_values( &$args, $starts_with = ' WHERE ' ) {
        if ( empty($args) ) {
			// add an arg to prevent prepare from failing
			$args = array( 'where' => $starts_with . '1=%d', 'values' => array( 1 ) );
			return;
        }

		$where = '';
		$values = array();

		if ( is_array( $args ) ) {
			$base_where = $starts_with;
			self::parse_where_from_array( $args, $base_where, $where, $values );
		}

		$args = compact( 'where', 'values' );
    }

    /**
	 * @param array $args
     * @param string $base_where
     * @param string $where
	 * @param array $values
     */
    public static function parse_where_from_array( $args, $base_where, &$where, &$values ) {
        $condition = ' AND';
        if ( isset( $args['or'] ) ) {
            $condition = ' OR';
            unset( $args['or'] );
        }

        foreach ( $args as $key => $value ) {
            $where .= empty( $where ) ? $base_where : $condition;
            $array_inc_null = ( ! is_numeric( $key ) && is_array( $value ) && in_array( null, $value ) );
            if ( is_numeric( $key ) || $array_inc_null ) {
                $where .= ' ( ';
                $nested_where = '';
                if ( $array_inc_null ) {
                    foreach ( $value as $val ) {
                        self::parse_where_from_array( array( $key => $val, 'or' => 1 ), '', $nested_where, $values );
                    }
                } else {
                    self::parse_where_from_array( $value, '', $nested_where, $values );
                }
                $where .= $nested_where;
                $where .= ' ) ';
            } else {
                self::interpret_array_to_sql( $key, $value, $where, $values );
            }
        }
    }

    /**
     * @param string $key
	 * @param string|array $value
     * @param string $where
	 * @param array $values
     */
    private static function interpret_array_to_sql( $key, $value, &$where, &$values ) {
		$key = trim( $key );

		if ( strpos( $key, 'created_at' ) !== false || strpos( $key, 'updated_at' ) !== false ) {
            $k = explode(' ', $key);
            $where .= ' DATE_FORMAT(' . reset( $k ) . ', %s) ' . str_replace( reset( $k ), '', $key );
            $values[] = '%Y-%m-%d %H:%i:%s';
        } else {
			$where .= ' ' . $key;
        }

		$lowercase_key = explode( ' ', strtolower( $key ) );
		$lowercase_key = end( $lowercase_key );

        if ( is_array( $value ) ) {
            // translate array of values to "in"
			if ( strpos( $lowercase_key, 'like' ) !== false ) {
				$where = preg_replace('/' . $key . '$/', '', $where);
				$where .= '(';
				$start = true;
				foreach ( $value as $v ) {
					if ( ! $start ) {
						$where .= ' OR ';
					}
					$start = false;
					$where .= $key . ' %s';
					$values[] = '%' . FrmAppHelper::esc_like( $v ) . '%';
				}
				$where .= ')';
			} else if ( ! empty( $value ) ) {
				$where .= ' in (' . FrmAppHelper::prepare_array_values( $value, '%s' ) . ')';
				$values = array_merge( $values, $value );
			}
        } else if ( strpos( $lowercase_key, 'like' ) !== false ) {
			/**
			 * Allow string to start or end with the value
			 * If the key is like% then skip the first % for starts with
			 * If the key is %like then skip the last % for ends with
			 */
			$start = '%';
			$end = '%';
			if ( $lowercase_key == 'like%' ) {
				$start = '';
				$where = rtrim( $where, '%' );
			} else if ( $lowercase_key == '%like' ) {
				$end = '';
				$where = rtrim( rtrim( $where, '%like' ), '%LIKE' );
				$where .= 'like';
			}

			$where .= ' %s';
			$values[] = $start . FrmAppHelper::esc_like( $value ) . $end;

        } else if ( $value === null ) {
            $where .= ' IS NULL';
        } else {
			// allow a - to prevent = from being added
			if ( substr( $key, -1 ) == '-' ) {
				$where = rtrim( $where, '-' );
			} else {
				$where .= '=';
			}

			self::add_query_placeholder( $key, $value, $where );

            $values[] = $value;
        }
    }

	/**
	 * Add %d, or %s to query
	 *
	 * @since 2.02.05
	 * @param string $key
	 * @param int|string $value
	 * @param string $where
	 */
    private static function add_query_placeholder( $key, $value, &$where ) {
		if ( is_numeric( $value ) && strpos( $key, 'meta_value' ) === false ) {
			$where .= '%d';
		} else {
			$where .= '%s';
		}
	}

    /**
     * @param string $table
	 * @param array $where
	 * @param array $args
	 * @return int
     */
    public static function get_count( $table, $where = array(), $args = array() ) {
        $count = self::get_var( $table, $where, 'COUNT(*)', $args );
        return $count;
    }

	/**
	 * @param string $table
	 * @param array $where
	 * @param string $field
	 * @param array $args
	 * @param string $limit
	 * @param string $type
	 * @return array|null|string|object
	 */
    public static function get_var( $table, $where = array(), $field = 'id', $args = array(), $limit = '', $type = 'var' ) {
        $group = '';
        self::get_group_and_table_name( $table, $group );
		self::convert_options_to_array( $args, '', $limit );

		$query = self::generate_query_string_from_pieces( $field, $table, $where, $args );

		$cache_key = self::generate_cache_key( $where, $args, $field, $type );
		$results = FrmAppHelper::check_cache( $cache_key, $group, $query, 'get_' . $type );
        return $results;
    }

	/**
	 * Generate a cache key from the where query, field, type, and other arguments
	 *
	 * @since 2.03.07
	 *
	 * @param array $where
	 * @param array $args
	 * @param string $field
	 * @param string $type
	 *
	 * @return string
	 */
	private static function generate_cache_key( $where, $args, $field, $type ) {
		$cache_key = '';
		$where = FrmAppHelper::array_flatten( $where );
		foreach ( $where as $key => $value ) {
			$cache_key .= $key . '_' . $value;
		}
		$cache_key .= implode( '_', $args ) . $field . '_' . $type;
		$cache_key = str_replace( array( ' ', ',' ), '_', $cache_key );

		return $cache_key;
	}

    /**
     * @param string $table
     * @param array $where
	 * @param string $field
	 * @param array $args
	 * @param string $limit
	 * @return mixed
     */
    public static function get_col( $table, $where = array(), $field = 'id', $args = array(), $limit = '' ) {
        return self::get_var( $table, $where, $field, $args, $limit, 'col' );
    }

    /**
     * @since 2.0
     * @param string $table
	 * @param array $where
	 * @param string $fields
	 * @param array $args
	 * @return mixed
     */
    public static function get_row( $table, $where = array(), $fields = '*', $args = array() ) {
        $args['limit'] = 1;
        return self::get_var( $table, $where, $fields, $args, '', 'row' );
    }

    /**
     * Prepare a key/value array before DB call
	 *
     * @since 2.0
     * @param string $table
	 * @param array $where
	 * @param string $fields
	 * @param array $args
	 * @return mixed
     */
    public static function get_results( $table, $where = array(), $fields = '*', $args = array() ) {
        return self::get_var( $table, $where, $fields, $args, '', 'results' );
    }

	/**
	 * Check for like, not like, in, not in, =, !=, >, <, <=, >=
	 * Return a value to append to the where array key
	 *
	 * @param string $where_is
	 * @return string
	 */
	public static function append_where_is( $where_is ) {
		$switch_to = array(
			'='		=> '',
			'!=' 	=> '!',
			'<='	=> '<',
			'>='	=> '>',
			'like'	=> 'like',
			'not like' => 'not like',
			'in'	=> '',
			'not in' => 'not',
			'like%'	=> 'like%',
			'%like'	=> '%like',
		);

		$where_is = strtolower( $where_is );
		if ( isset( $switch_to[ $where_is ] ) ) {
			return ' ' . $switch_to[ $where_is ];
		}

		// > and < need a little more work since we don't want them switched to >= and <=
		if ( $where_is == '>' || $where_is == '<' ) {
			return ' ' . $where_is . '-'; // the - indicates that the = should not be added later
		}

		// fallback to = if the query is none of these
		return '';
	}

    /**
     * Get 'frm_forms' from wp_frm_forms or a longer table param that includes a join
     * Also add the wpdb->prefix to the table if it's missing
     *
     * @param string $table
     * @param string $group
     */
    private static function get_group_and_table_name( &$table, &$group ) {
		global $wpdb, $wpmuBaseTablePrefix;

        $table_parts = explode(' ', $table);
        $group = reset($table_parts);
        $group = str_replace( $wpdb->prefix, '', $group );

		$prefix = $wpmuBaseTablePrefix ? $wpmuBaseTablePrefix : $wpdb->base_prefix;
		$group = str_replace( $prefix, '', $group );

        if ( $group == $table ) {
            $table = $wpdb->prefix . $table;
        }

		// switch to singular group name
		$group = rtrim( $group, 's' );
    }

    private static function convert_options_to_array( &$args, $order_by = '', $limit = '' ) {
        if ( ! is_array($args) ) {
			$args = array( 'order_by' => $args );
        }

        if ( ! empty( $order_by ) ) {
            $args['order_by'] = $order_by;
        }

        if ( ! empty( $limit ) ) {
            $args['limit'] = $limit;
        }

        $temp_args = $args;
        foreach ( $temp_args as $k => $v ) {
            if ( $v == '' ) {
				unset( $args[ $k ] );
                continue;
            }

            if ( $k == 'limit' ) {
				$args[ $k ] = FrmAppHelper::esc_limit( $v );
            }
            $db_name = strtoupper( str_replace( '_', ' ', $k ) );
            if ( strpos( $v, $db_name ) === false ) {
				$args[ $k ] = $db_name . ' ' . $v;
            }
        }

		// Make sure LIMIT is the last argument
		if ( isset( $args['order_by'] ) && isset( $args['limit'] ) ) {
			$temp_limit = $args['limit'];
			unset( $args['limit'] );
			$args['limit'] = $temp_limit;
		}
    }

	/**
	 * Get the associative array results for the given columns, table, and where query
	 *
	 * @since 2.02.05
	 * @param string $columns
	 * @param string $table
	 * @param array $where
	 * @return mixed
	 */
	public static function get_associative_array_results( $columns, $table, $where ) {
		$group = '';
		self::get_group_and_table_name( $table, $group );

		$query = self::generate_query_string_from_pieces( $columns, $table, $where );

		$cache_key = str_replace( array( ' ', ',' ), '_', trim( implode( '_', FrmAppHelper::array_flatten( $where ) ) . $columns . '_results_ARRAY_A' , ' WHERE' ) );
		$results = FrmAppHelper::check_cache( $cache_key, $group, $query, 'get_associative_results' );

		return $results;
	}

	/**
	 * Combine the pieces of a query to form a full, prepared query
	 *
	 * @since 2.02.05
	 *
	 * @param string $columns
	 * @param string $table
	 * @param mixed $where
	 * @param array $args
	 * @return string
	 */
	private static function generate_query_string_from_pieces( $columns, $table, $where, $args = array() ) {
		$query = 'SELECT ' . $columns . ' FROM ' . $table;

		if ( is_array( $where ) || empty( $where ) ) {
			self::get_where_clause_and_values( $where );
			global $wpdb;
			$query = $wpdb->prepare( $query . $where['where'] . ' ' . implode( ' ', $args ), $where['values'] );
		} else {
			/**
			 * Allow the $where to be prepared before we recieve it here.
			 * This is a fallback for reverse compatability, but is not recommended
			 */
			_deprecated_argument( 'where', '2.0', __( 'Use the query in an array format so it can be properly prepared.', 'formidable' ) );
			$query .= $where . ' ' . implode( ' ', $args );
		}

		return $query;
	}

    public function uninstall() {
		if ( ! current_user_can( 'administrator' ) ) {
            $frm_settings = FrmAppHelper::get_settings();
            wp_die($frm_settings->admin_permission);
        }

        global $wpdb, $wp_roles;

		$wpdb->query( 'DROP TABLE IF EXISTS ' . $this->fields );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $this->forms );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $this->entries );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $this->entry_metas );

        delete_option('frm_options');
        delete_option('frm_db_version');

        //delete roles
        $frm_roles = FrmAppHelper::frm_capabilities();
        $roles = get_editable_roles();
        foreach ( $frm_roles as $frm_role => $frm_role_description ) {
            foreach ( $roles as $role => $details ) {
                $wp_roles->remove_cap( $role, $frm_role );
                unset($role, $details);
    		}
    		unset($frm_role, $frm_role_description);
		}
		unset($roles, $frm_roles);

		// delete actions, views, and styles

		// prevent the post deletion from triggering entries to be deleted
		remove_action( 'before_delete_post', 'FrmProDisplaysController::before_delete_post' );
		remove_action( 'deleted_post', 'FrmProEntriesController::delete_entry' );

		$post_ids = $wpdb->get_col( $wpdb->prepare( 'SELECT ID FROM ' . $wpdb->posts . ' WHERE post_type in (%s, %s, %s)', FrmFormActionsController::$action_post_type, FrmStylesController::$post_type, 'frm_display' ) );
		foreach ( $post_ids as $post_id ) {
			// Delete's each post.
			wp_delete_post( $post_id, true );
		}
		unset( $post_ids );

		// delete transients
		delete_transient( 'frmpro_css' );
		delete_transient( 'frm_options' );
		delete_transient( 'frmpro_options' );

		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE %s OR option_name LIKE %s', '_transient_timeout_frm_form_fields_%', '_transient_frm_form_fields_%' ) );

        do_action('frm_after_uninstall');
        return true;
    }

	/**
	 * Migrate old styling settings. If sites are using the old
	 * default 400px field width, switch it to 100%
	 *
	 * @since 2.0.4
	 */
	private function migrate_to_25() {
		// get the style that was created with the style migration
		$frm_style = new FrmStyle();
		$styles = $frm_style->get_all( 'post_date', 'ASC', 1 );
		if ( empty( $styles ) ) {
			return;
		}

		foreach ( $styles as $style ) {
			if ( $style->post_content['field_width'] == '400px' ) {
				$style->post_content['field_width'] = '100%';
				$frm_style->save( (array) $style );
				return;
			}
		}
	}

	/**
	 * Check if the parent_form_id columns exists.
	 * If not, try and add it again
	 *
	 * @since 2.0.2
	 */
	private function migrate_to_23() {
		global $wpdb;
		$exists = $wpdb->get_row( 'SHOW COLUMNS FROM ' . $this->forms . ' LIKE "parent_form_id"' );
		if ( empty( $exists ) ) {
			$wpdb->query( 'ALTER TABLE ' . $this->forms . ' ADD parent_form_id int(11) default 0' );
		}
	}

    /**
     * Change field size from character to pixel -- Multiply by 9
     */
    private function migrate_to_17() {
        global $wpdb;
		$pixel_conversion = 9;

        // Get query arguments
		$field_types = array( 'textarea', 'text', 'number', 'email', 'url', 'rte', 'date', 'phone', 'password', 'image', 'tag', 'file' );
		$query = array( 'type' => $field_types, 'field_options like' => 's:4:"size";', 'field_options not like' => 's:4:"size";s:0:' );

        // Get results
		$fields = FrmDb::get_results( $this->fields, $query, 'id, field_options' );

        $updated = 0;
        foreach ( $fields as $f ) {
            $f->field_options = maybe_unserialize($f->field_options);
            if ( empty($f->field_options['size']) || ! is_numeric($f->field_options['size']) ) {
                continue;
            }

			$f->field_options['size'] = round( $pixel_conversion * (int) $f->field_options['size'] );
            $f->field_options['size'] .= 'px';
            $u = FrmField::update( $f->id, array( 'field_options' => $f->field_options ) );
            if ( $u ) {
                $updated++;
            }
            unset($f);
        }

        // Change the characters in widgets to pixels
        $widgets = get_option('widget_frm_show_form');
        if ( empty($widgets) ) {
            return;
        }

        $widgets = maybe_unserialize($widgets);
        foreach ( $widgets as $k => $widget ) {
            if ( ! is_array($widget) || ! isset($widget['size']) ) {
                continue;
            }
			$size = round( $pixel_conversion * (int) $widget['size'] );
            $size .= 'px';
			$widgets[ $k ]['size'] = $size;
        }
        update_option('widget_frm_show_form', $widgets);
    }

    /**
     * Migrate post and email notification settings into actions
     */
    private function migrate_to_16() {
        global $wpdb;

        $forms = FrmDb::get_results( $this->forms, array(), 'id, options, is_template, default_template' );

        /**
        * Old email settings format:
        * email_to: Email or field id
        * also_email_to: array of fields ids
        * reply_to: Email, field id, 'custom'
        * cust_reply_to: string
        * reply_to_name: field id, 'custom'
        * cust_reply_to_name: string
        * plain_text: 0|1
        * email_message: string or ''
        * email_subject: string or ''
        * inc_user_info: 0|1
        * update_email: 0, 1, 2
        *
        * Old autoresponder settings format:
        * auto_responder: 0|1
        * ar_email_message: string or ''
        * ar_email_to: field id
        * ar_plain_text: 0|1
        * ar_reply_to_name: string
        * ar_reply_to: string
        * ar_email_subject: string
        * ar_update_email: 0, 1, 2
        *
        * New email settings:
        * post_content: json settings
        * post_title: form id
        * post_excerpt: message
        *
        */

        foreach ( $forms as $form ) {
			if ( $form->is_template && $form->default_template ) {
				// don't migrate the default templates since the email will be added anyway
				continue;
			}

            // Format form options
            $form_options = maybe_unserialize($form->options);

            // Migrate settings to actions
            FrmXMLHelper::migrate_form_settings_to_actions( $form_options, $form->id );
        }
    }

    private function migrate_to_11() {
        global $wpdb;

        $forms = FrmDb::get_results( $this->forms, array(), 'id, options');

        $sending = __( 'Sending', 'formidable' );
		$img = FrmAppHelper::plugin_url() . '/images/ajax_loader.gif';
        $old_default_html = <<<DEFAULT_HTML
<div class="frm_submit">
[if back_button]<input type="submit" value="[back_label]" name="frm_prev_page" formnovalidate="formnovalidate" [back_hook] />[/if back_button]
<input type="submit" value="[button_label]" [button_action] />
<img class="frm_ajax_loading" src="$img" alt="$sending" style="visibility:hidden;" />
</div>
DEFAULT_HTML;
        unset($sending, $img);

        $new_default_html = FrmFormsHelper::get_default_html('submit');
        $draft_link = FrmFormsHelper::get_draft_link();
		foreach ( $forms as $form ) {
            $form->options = maybe_unserialize($form->options);
            if ( ! isset($form->options['submit_html']) || empty($form->options['submit_html']) ) {
                continue;
            }

            if ( $form->options['submit_html'] != $new_default_html && $form->options['submit_html'] == $old_default_html ) {
                $form->options['submit_html'] = $new_default_html;
				$wpdb->update( $this->forms, array( 'options' => serialize( $form->options ) ), array( 'id' => $form->id ) );
			} else if ( ! strpos( $form->options['submit_html'], 'save_draft' ) ) {
				$form->options['submit_html'] = preg_replace( '~\<\/div\>(?!.*\<\/div\>)~', $draft_link . "\r\n</div>", $form->options['submit_html'] );
				$wpdb->update( $this->forms, array( 'options' => serialize( $form->options ) ), array( 'id' => $form->id ) );
            }
            unset($form);
        }
        unset($forms);
    }

    private function migrate_to_6() {
        global $wpdb;

		$no_save = array_merge( FrmField::no_save_fields(), array( 'form', 'hidden', 'user_id' ) );
		$fields = FrmDb::get_results( $this->fields, array( 'type NOT' => $no_save ), 'id, field_options' );

        $default_html = <<<DEFAULT_HTML
<div id="frm_field_[id]_container" class="form-field [required_class] [error_class]">
    <label class="frm_pos_[label_position]">[field_name]
        <span class="frm_required">[required_label]</span>
    </label>
    [input]
    [if description]<div class="frm_description">[description]</div>[/if description]
</div>
DEFAULT_HTML;

        $old_default_html = <<<DEFAULT_HTML
<div id="frm_field_[id]_container" class="form-field [required_class] [error_class]">
    <label class="frm_pos_[label_position]">[field_name]
        <span class="frm_required">[required_label]</span>
    </label>
    [input]
    [if description]<p class="frm_description">[description]</p>[/if description]
</div>
DEFAULT_HTML;

        $new_default_html = FrmFieldsHelper::get_default_html('text');
        foreach ( $fields as $field ) {
            $field->field_options = maybe_unserialize($field->field_options);
			if ( ! FrmField::is_option_empty( $field, 'custom_html' ) || $field->field_options['custom_html'] == $default_html || $field->field_options['custom_html'] == $old_default_html ) {
                $field->field_options['custom_html'] = $new_default_html;
				$wpdb->update( $this->fields, array( 'field_options' => maybe_serialize( $field->field_options ) ), array( 'id' => $field->id ) );
            }
            unset($field);
        }
        unset($default_html, $old_default_html, $fields);
    }

    private function migrate_to_4() {
        global $wpdb;
		$user_ids = FrmEntryMeta::getAll( array( 'fi.type' => 'user_id' ) );
        foreach ( $user_ids as $user_id ) {
			$wpdb->update( $this->entries, array( 'user_id' => $user_id->meta_value ), array( 'id' => $user_id->item_id ) );
        }
    }
}
