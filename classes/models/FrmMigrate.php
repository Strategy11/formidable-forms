<?php

class FrmMigrate {
	public $fields;
	public $forms;
	public $entries;
	public $entry_metas;

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
		$query = array(
			'type' => $field_types,
			'field_options like' => 's:4:"size";',
			'field_options not like' => 's:4:"size";s:0:',
		);

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
			$html = FrmField::get_option( $field, 'custom_html' );
			if ( $html == $default_html || $html == $old_default_html ) {
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
