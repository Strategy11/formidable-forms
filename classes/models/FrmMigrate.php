<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmMigrate {
	public $fields;
	public $forms;
	public $entries;
	public $entry_metas;

	public function __construct() {
		global $wpdb;
		$this->fields      = $wpdb->prefix . 'frm_fields';
		$this->forms       = $wpdb->prefix . 'frm_forms';
		$this->entries     = $wpdb->prefix . 'frm_items';
		$this->entry_metas = $wpdb->prefix . 'frm_item_metas';
	}

	public function upgrade() {
		do_action( 'frm_before_install' );

		global $wpdb, $frm_vars;

		$frm_vars['doing_upgrade'] = true;

		$needs_upgrade = FrmAppController::compare_for_update(
			array(
				'option'             => 'frm_db_version',
				'new_db_version'     => FrmAppHelper::$db_version,
				'new_plugin_version' => FrmAppHelper::plugin_version(),
			)
		);

		if ( $needs_upgrade ) {
			// update rewrite rules for views and other custom post types
			flush_rewrite_rules();

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$old_db_version = get_option( 'frm_db_version' );

			$this->create_tables();
			$this->migrate_data( $old_db_version );

			// SAVE DB VERSION.
			update_option( 'frm_db_version', FrmAppHelper::plugin_version() . '-' . FrmAppHelper::$db_version );

			if ( ! $old_db_version ) {
				$this->maybe_create_contact_form();
			}
		}

		do_action( 'frm_after_install' );

		$frm_vars['doing_upgrade'] = false;

		FrmAppHelper::save_combined_js();

		// update the styling settings
		if ( function_exists( 'get_filesystem_method' ) ) {
			$frm_style = new FrmStyle();
			$frm_style->update( 'default' );
		}
	}

	public function collation() {
		global $wpdb;
		if ( ! $wpdb->has_cap( 'collation' ) ) {
			return '';
		}

		return $wpdb->get_charset_collate();
	}

	private function create_tables() {
		$charset_collate = $this->collation();
		$sql             = array();

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
				$wpdb->query( $q . $charset_collate ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			}
			unset( $q );
		}

		$this->add_composite_indexes_for_entries();
	}

	/**
	 * These indexes help optimize database queries for entries.
	 *
	 * @since 6.6
	 *
	 * @return void
	 */
	private function add_composite_indexes_for_entries() {
		global $wpdb;

		$table_name = "{$wpdb->prefix}frm_items";
		$index_name = 'idx_is_draft_created_at';

		if ( ! self::index_exists( $table_name, $index_name ) ) {
			$wpdb->query( "CREATE INDEX idx_is_draft_created_at ON `{$wpdb->prefix}frm_items` (is_draft, created_at)" );
		}

		$table_name = "{$wpdb->prefix}frm_item_metas";
		$index_name = 'idx_field_id_item_id';

		if ( ! self::index_exists( $table_name, $index_name ) ) {
			$wpdb->query( "CREATE INDEX idx_field_id_item_id ON `{$wpdb->prefix}frm_item_metas` (field_id, item_id)" );
		}
	}

	/**
	 * Check that an index exists in a database table before trying to add it (which results in an error).
	 *
	 * @since 6.6
	 *
	 * @param string $table_name
	 * @param string $index_name
	 * @return bool
	 */
	private static function index_exists( $table_name, $index_name ) {
		global $wpdb;
		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT 1 FROM information_schema.statistics
					WHERE table_schema = database()
						AND table_name = %s
						AND index_name = %s
					LIMIT 1',
				array( $table_name, $index_name )
			)
		);
		return (bool) $row;
	}

	private function maybe_create_contact_form() {
		$form_exists = FrmForm::get_id_by_key( 'contact-form' );
		if ( ! $form_exists ) {
			$this->add_default_template();
		}
	}

	/**
	 * Create the default contact form
	 *
	 * @since 3.06
	 */
	private function add_default_template() {
		if ( FrmXMLHelper::check_if_libxml_disable_entity_loader_exists() ) {
			// XML import is not enabled on your server.
			return;
		}

		$set_err = libxml_use_internal_errors( true );
		$loader  = FrmXMLHelper::maybe_libxml_disable_entity_loader( true );

		$file = FrmAppHelper::plugin_path() . '/classes/views/xml/default-templates.xml';
		FrmXMLHelper::import_xml( $file );

		libxml_use_internal_errors( $set_err );
		FrmXMLHelper::maybe_libxml_disable_entity_loader( $loader );
	}

	/**
	 * @param int|string $old_db_version
	 */
	private function migrate_data( $old_db_version ) {
		if ( ! $old_db_version ) {
			$old_db_version = get_option( 'frm_db_version' );
		}
		if ( strpos( $old_db_version, '-' ) ) {
			$last_upgrade   = explode( '-', $old_db_version );
			$old_db_version = (int) $last_upgrade[1];
		}

		if ( ! is_numeric( $old_db_version ) ) {
			// bail if we don't know the previous version
			return;
		}

		$migrations = array( 16, 11, 16, 17, 23, 25, 86, 90, 97, 98, 101 );
		foreach ( $migrations as $migration ) {
			if ( FrmAppHelper::$db_version >= $migration && $old_db_version < $migration ) {
				$function_name = 'migrate_to_' . $migration;
				$this->$function_name();
			}
		}
	}

	public function uninstall() {
		if ( ! current_user_can( 'administrator' ) ) {
			$frm_settings = FrmAppHelper::get_settings();
			wp_die( esc_html( $frm_settings->admin_permission ) );
		}

		global $wpdb, $wp_roles;

		$wpdb->query( 'DROP TABLE IF EXISTS ' . $this->fields ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $this->forms ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $this->entries ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $this->entry_metas ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		delete_option( 'frm_options' );
		delete_option( 'frm_db_version' );
		delete_option( 'frm_install_running' );
		delete_option( 'frm_lite_settings_upgrade' );
		delete_option( 'frm-usage-uuid' );
		delete_option( 'frm_inbox' );
		delete_option( 'frmpro_css' );
		delete_option( FrmOnboardingWizardController::REDIRECT_STATUS_OPTION );
		delete_option( FrmEmailSummaryHelper::$option_name );

		// Delete roles.
		$frm_roles = FrmAppHelper::frm_capabilities();
		$roles     = get_editable_roles();
		foreach ( $frm_roles as $frm_role => $frm_role_description ) {
			foreach ( $roles as $role => $details ) {
				$wp_roles->remove_cap( $role, $frm_role );
				unset( $role, $details );
			}
			unset( $frm_role, $frm_role_description );
		}
		unset( $roles, $frm_roles );

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
		delete_transient( FrmOnboardingWizardController::TRANSIENT_NAME );

		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE %s OR option_name LIKE %s', '_transient_timeout_frm_form_fields_%', '_transient_frm_form_fields_%' ) );

		do_action( 'frm_after_uninstall' );

		return true;
	}

	/**
	 * Disables summary email for multisite (not the main site) if recipient setting isn't changed.
	 *
	 * @since 6.8
	 */
	private function migrate_to_101() {
		if ( ! is_multisite() || get_main_site_id() === get_current_blog_id() ) {
			return;
		}

		$frm_settings = FrmAppHelper::get_settings();
		if ( empty( $frm_settings->summary_emails ) || '[admin_email]' !== $frm_settings->summary_emails_recipients ) {
			// User changed it.
			return;
		}

		$frm_settings->summary_emails = 0;
		$frm_settings->store();
	}

	/**
	 * Clear frmpro_css transient.
	 *
	 * @since 4.10.02
	 */
	private function migrate_to_98() {
		delete_transient( 'frmpro_css' );
	}

	/**
	 * Move default_blank and clear_on_focus to placeholder.
	 *
	 * @since 4.0
	 */
	private function migrate_to_97() {
		$this->migrate_to_placeholder( 'clear_on_focus' );
		$this->migrate_to_placeholder( 'default_blank' );
	}

	/**
	 * Move clear_on_focus or default_blank to placeholder.
	 *
	 * @since 4.0
	 */
	private function migrate_to_placeholder( $type = 'clear_on_focus' ) {
		$query = array(
			'field_options like' => '"' . $type . '";s:1:"1";',
		);

		$fields = FrmDb::get_results( $this->fields, $query, 'id, default_value, field_options, options' );

		foreach ( $fields as $field ) {
			FrmAppHelper::unserialize_or_decode( $field->field_options );
			FrmAppHelper::unserialize_or_decode( $field->options );
			$update_values = FrmXMLHelper::migrate_field_placeholder( $field, $type );
			if ( empty( $update_values ) ) {
				continue;
			}

			FrmField::update( $field->id, $update_values );
			unset( $field );
		}
	}

	/**
	 * Delete uneeded default templates
	 *
	 * @since 3.06
	 */
	private function migrate_to_90() {
		$form = FrmForm::getOne( 'contact' );
		if ( $form && $form->default_template == 1 ) {
			FrmForm::destroy( 'contact' );
		}
	}

	/**
	 * Reverse migration 17 -- Divide by 9
	 *
	 * @since 3.0.05
	 */
	private function migrate_to_86() {

		$fields = $this->get_fields_with_size();

		foreach ( (array) $fields as $f ) {
			FrmAppHelper::unserialize_or_decode( $f->field_options );
			$size = $f->field_options['size'];
			$this->maybe_convert_migrated_size( $size );

			if ( $size === $f->field_options['size'] ) {
				continue;
			}

			$f->field_options['size'] = $size;
			FrmField::update( $f->id, array( 'field_options' => $f->field_options ) );
			unset( $f );
		}

		// reverse the extra size changes in widgets
		$widgets = get_option( 'widget_frm_show_form' );
		if ( empty( $widgets ) ) {
			return;
		}

		$this->revert_widget_field_size();
	}

	private function get_fields_with_size() {
		$field_types = array(
			'textarea',
			'text',
			'number',
			'email',
			'url',
			'rte',
			'date',
			'phone',
			'password',
			'image',
			'tag',
			'file',
		);

		$query = array(
			'type'                   => $field_types,
			'field_options like'     => 's:4:"size";',
			'field_options not like' => 's:4:"size";s:0:',
		);

		return FrmDb::get_results( $this->fields, $query, 'id, field_options' );
	}

	/**
	 * Reverse the extra size changes in widgets
	 *
	 * @since 3.0.05
	 */
	private function revert_widget_field_size() {
		$widgets = get_option( 'widget_frm_show_form' );
		if ( empty( $widgets ) ) {
			return;
		}

		FrmAppHelper::unserialize_or_decode( $widgets );
		foreach ( $widgets as $k => $widget ) {
			if ( ! is_array( $widget ) || ! isset( $widget['size'] ) ) {
				continue;
			}

			$this->maybe_convert_migrated_size( $widgets[ $k ]['size'] );
		}
		update_option( 'widget_frm_show_form', $widgets );
	}

	/**
	 * Divide by 9 to reverse the multiplication
	 *
	 * @since 3.0.05
	 */
	private function maybe_convert_migrated_size( &$size ) {
		$has_px_size = ! empty( $size ) && strpos( $size, 'px' );
		if ( ! $has_px_size ) {
			return;
		}

		$int_size = str_replace( 'px', '', $size );
		if ( ! is_numeric( $int_size ) || (int) $int_size < 900 ) {
			return;
		}

		$pixel_conversion = 9;

		$size = round( (int) $int_size / $pixel_conversion );
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
		$styles    = $frm_style->get_all( 'post_date', 'ASC', 1 );
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
		$exists = $wpdb->get_row( 'SHOW COLUMNS FROM ' . $this->forms . ' LIKE "parent_form_id"' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		if ( empty( $exists ) ) {
			$wpdb->query( 'ALTER TABLE ' . $this->forms . ' ADD parent_form_id int(11) default 0' ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}
	}

	/**
	 * Change field size from character to pixel -- Multiply by 9
	 */
	private function migrate_to_17() {
		$fields = $this->get_fields_with_size();

		foreach ( $fields as $f ) {
			FrmAppHelper::unserialize_or_decode( $f->field_options );
			if ( empty( $f->field_options['size'] ) || ! is_numeric( $f->field_options['size'] ) ) {
				continue;
			}

			$this->convert_character_to_px( $f->field_options['size'] );

			FrmField::update( $f->id, array( 'field_options' => $f->field_options ) );
			unset( $f );
		}

		$this->adjust_widget_size();
	}

	/**
	 * Change the characters in widgets to pixels
	 */
	private function adjust_widget_size() {
		$widgets = get_option( 'widget_frm_show_form' );
		if ( empty( $widgets ) ) {
			return;
		}

		FrmAppHelper::unserialize_or_decode( $widgets );
		foreach ( $widgets as $k => $widget ) {
			if ( ! is_array( $widget ) || ! isset( $widget['size'] ) ) {
				continue;
			}
			$this->convert_character_to_px( $widgets[ $k ]['size'] );
		}
		update_option( 'widget_frm_show_form', $widgets );
	}

	private function convert_character_to_px( &$size ) {
		$pixel_conversion = 9;

		$size  = round( $pixel_conversion * (int) $size );
		$size .= 'px';
	}

	/**
	 * Migrate post and email notification settings into actions
	 */
	private function migrate_to_16() {
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
		 */
		foreach ( $forms as $form ) {
			if ( $form->is_template && $form->default_template ) {
				// don't migrate the default templates since the email will be added anyway
				continue;
			}

			// Format form options
			$form_options = $form->options;
			FrmAppHelper::unserialize_or_decode( $form_options );

			// Migrate settings to actions
			FrmXMLHelper::migrate_form_settings_to_actions( $form_options, $form->id );
		}
	}

	private function migrate_to_11() {
		global $wpdb;

		$forms = FrmDb::get_results( $this->forms, array(), 'id, options' );

		$sending          = __( 'Sending', 'formidable' );
		$img              = FrmAppHelper::plugin_url() . '/images/ajax_loader.gif';
		$old_default_html = <<<DEFAULT_HTML
<div class="frm_submit">
[if back_button]<input type="submit" value="[back_label]" name="frm_prev_page" formnovalidate="formnovalidate" [back_hook] />[/if back_button]
<input type="submit" value="[button_label]" [button_action] />
<img class="frm_ajax_loading" src="$img" alt="$sending" style="visibility:hidden;" />
</div>
DEFAULT_HTML;
		unset( $sending, $img );

		$new_default_html = FrmFormsHelper::get_default_html( 'submit' );
		$draft_link       = FrmFormsHelper::get_draft_link();
		foreach ( $forms as $form ) {
			FrmAppHelper::unserialize_or_decode( $form->options );
			if ( empty( $form->options['submit_html'] ) ) {
				continue;
			}

			if ( $form->options['submit_html'] != $new_default_html && $form->options['submit_html'] == $old_default_html ) {
				$form->options['submit_html'] = $new_default_html;
				$wpdb->update( $this->forms, array( 'options' => serialize( $form->options ) ), array( 'id' => $form->id ) );
			} elseif ( ! strpos( $form->options['submit_html'], 'save_draft' ) ) {
				$form->options['submit_html'] = preg_replace( '~\<\/div\>(?!.*\<\/div\>)~', $draft_link . "\r\n</div>", $form->options['submit_html'] );
				$wpdb->update( $this->forms, array( 'options' => serialize( $form->options ) ), array( 'id' => $form->id ) );
			}
			unset( $form );
		}
	}
}
