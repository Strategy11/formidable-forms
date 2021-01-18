<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 3.06.04
 */
class FrmUsage {

	/**
	 * @since 3.06.04
	 */
	public function send_snapshot() {
		if ( ! $this->tracking_allowed() ) {
			return;
		}

		$ep   = 'aHR0cHM6Ly9mcm0tdXNlci10cmFja2luZy5oZXJva3VhcHAuY29tL3NuYXBzaG90';
		// $ep = base64_encode( 'http://localhost:4567/snapshot' ); // Uncomment for testing
		$body = json_encode( $this->snapshot() );

		// Setup variable for wp_remote_request.
		$post = array(
			'method'    => 'POST',
			'headers'   => array(
				'Accept'         => 'application/json',
				'Content-Type'   => 'application/json',
				'Content-Length' => strlen( $body ),
			),
			'body'      => $body,
		);

		wp_remote_request( base64_decode( $ep ), $post );
	}

	/**
	 * @since 3.06.04
	 * @return string
	 */
	public function uuid( $regenerate = false ) {
		$uuid_key = 'frm-usage-uuid';
		$uuid     = get_option( $uuid_key );

		if ( $regenerate || empty( $uuid ) ) {
			// Definitely not cryptographically secure but
			// close enough to provide an unique id
			$uuid = md5( uniqid() . site_url() );
			update_option( $uuid_key, $uuid, 'no' );
		}

		return $uuid;
	}

	/**
	 * @since 3.06.04
	 * @return array
	 */
	public function snapshot() {
		global $wpdb, $wp_version;

		$theme_data  = wp_get_theme();
		$form_counts = FrmForm::get_count();

		$snap = array(
			'uuid'           => $this->uuid(),
			'admin_email'    => '', // Let's keep it anonymous.
			'wp_version'     => $wp_version,
			'php_version'    => phpversion(),
			'mysql_version'  => $wpdb->db_version(),
			'os'             => php_uname( 's' ),
			'locale'         => get_locale(),

			'active_license' => FrmAppHelper::pro_is_installed(),
			'form_count'     => $form_counts->published,
			'entry_count'    => FrmEntry::getRecordCount(),
			'timestamp'      => gmdate( 'c' ),

			'theme_name'     => is_object( $theme_data ) ? $theme_data->Name : '', // phpcs:ignore WordPress.NamingConventions
			'plugins'        => $this->plugins(),
			'settings'       => array(
				$this->settings(),
			),
			'forms'          => $this->forms(),
			'fields'         => $this->fields(),
			'actions'        => $this->actions(),
		);

		return apply_filters( 'frm_usage_snapshot', $snap );
	}

	/**
	 * @since 3.06.04
	 * @return array
	 */
	private function plugins() {
		$plugin_list = get_plugins();

		$plugins = array();
		foreach ( $plugin_list as $slug => $info ) {
			$plugins[] = array(
				'name'        => $info['Name'],
				'slug'        => $slug,
				'version'     => $info['Version'],
				'active'      => is_plugin_active( $slug ),
			);
		}

		return $plugins;
	}

	/**
	 * Add global settings to tracking data.
	 *
	 * @since 3.06.04
	 * @return array
	 */
	private function settings() {
		$settings_list  = FrmAppHelper::get_settings();
		$settings       = array(
			'messages'    => $this->messages( $settings_list ),
			'permissions' => $this->permissions( $settings_list ),
		);
		$pass_settings = array(
			'load_style',
			'use_html',
			'old_css',
			'fade_form',
			'jquery_css',
			're_type',
			're_lang',
			're_multi',
			'menu',
			'mu_menu',
			'no_ips',
			'btsp_css',
			'btsp_errors',
			'admin_bar',
		);

		foreach ( $pass_settings as $setting ) {
			if ( isset( $settings_list->$setting ) ) {
				$settings[ $setting ] = $this->maybe_json( $settings_list->$setting );
			}
		}

		$settings = apply_filters( 'frm_usage_settings', $settings );

		$settings['messages']    = $this->maybe_json( $settings['messages'] );
		$settings['permissions'] = $this->maybe_json( $settings['permissions'] );

		return $settings;
	}

	/**
	 * Include the permissions settings for each capability.
	 *
	 * @since 3.06.04
	 * @return array
	 */
	private function messages( $settings_list ) {
		$messages = array(
			'success_msg',
			'blank_msg',
			'unique_msg',
			'invalid_msg',
			'failed_msg',
			'submit_value',
			'login_msg',
			'admin_permission',
		);

		$message_settings = array();
		foreach ( $messages as $message ) {
			$message_settings[ $message ] = $settings_list->$message;
		}

		return $message_settings;
	}

	/**
	 * Include the permissions settings for each capability.
	 *
	 * @since 3.06.04
	 * @return array
	 */
	private function permissions( $settings_list ) {
		$permissions = array();
		$frm_roles   = FrmAppHelper::frm_capabilities();

		foreach ( $frm_roles as $frm_role => $frm_role_description ) {
			if ( isset( $settings_list->$frm_role ) ) {
				$permissions[ $frm_role ] = $settings_list->$frm_role;
			}
		}

		return $permissions;
	}

	/**
	 * @since 3.06.04
	 * @return array
	 */
	private function forms() {
		$s_query = array(
			array(
				'or' => 1,
				'parent_form_id' => null,
				'parent_form_id <' => 1,
			),
		);

		$saved_forms = FrmForm::getAll( $s_query );
		$forms       = array();
		$settings    = array(
			'form_class',
			'akismet',
			'custom_style',
			'success_action',
			'show_form',
			'no_save',
			'ajax_load',
			'ajax_submit',
			'js_validate',
			'logged_in_role',
			'single_entry',
			'single_entry_type',
			'editable_role',
			'open_editable_role',
			'edit_action',
			'edit_value',
			'edit_msg',
			'save_draft',
			'draft_msg',
			'submit_align',
			'protect_files',
			'protect_files_role',
			'max_entries',
			'open_status',
			'closed_msg',
			'open_date',
			'close_date',
			'copy',
			'prev_value',
			'submit_conditions',
		);

		foreach ( $saved_forms as $form ) {
			$new_form = array(
				'form_id'     => $form->id,
				'description' => $form->description,
				'logged_in'   => $form->logged_in,
				'editable'    => $form->editable,
				'is_template' => $form->is_template,
				'entry_count' => FrmEntry::getRecordCount( $form->id ),
				'field_count' => $this->form_field_count( $form->id ),
				'form_action_count' => $this->form_action_count( $form->id ),
			);

			foreach ( $settings as $setting ) {
				if ( isset( $form->options[ $setting ] ) ) {
					$new_form[ $setting ] = $this->maybe_json( $form->options[ $setting ] );
				}
			}

			$forms[] = apply_filters( 'frm_usage_form', $new_form, compact( 'form' ) );
		}

		// If the array uses numeric keys, reset them.
		return $forms;
	}

	/**
	 * @since 3.06.04
	 * @return int
	 */
	private function form_field_count( $form_id ) {
		global $wpdb;

		$join = $wpdb->prefix . 'frm_fields fi LEFT OUTER JOIN ' . $wpdb->prefix . 'frm_forms fo ON (fi.form_id=fo.id)';

		$field_query = array(
			'or'             => 1,
			'fi.form_id'     => $form_id,
			'parent_form_id' => $form_id,
		);

		return FrmDb::get_count( $join, $field_query );
	}

	/**
	 * @since 3.06.04
	 * @return int
	 */
	private function form_action_count( $form_id ) {
		$args = array(
			'post_type'  => FrmFormActionsController::$action_post_type,
			'menu_order' => $form_id,
			'fields'     => 'ids',
		);

		$actions = FrmDb::check_cache( json_encode( $args ), 'frm_actions', $args, 'get_posts' );
		return count( $actions );
	}

	/**
	 * Get the last 100 fields created.
	 *
	 * @since 3.06.04
	 * @return array
	 */
	private function fields() {
		$args   = array(
			'limit'    => 50,
			'order_by' => 'id DESC',
		);

		$fields = FrmDb::get_results( 'frm_fields', array(), 'form_id, name, type, field_options', $args );
		foreach ( $fields as $k => $field ) {
			FrmAppHelper::unserialize_or_decode( $field->field_options );
			$fields[ $k ]->field_options = json_encode( $field->field_options );
		}
		return $fields;
	}

	/**
	 * @since 3.06.04
	 * @return array
	 */
	private function actions() {
		$args = array(
			'post_type'   => FrmFormActionsController::$action_post_type,
			'numberposts' => 100,
		);

		$actions = array();

		$saved_actions = FrmDb::check_cache( json_encode( $args ), 'frm_actions', $args, 'get_posts' );
		foreach ( $saved_actions as $action ) {
			$actions[] = array(
				'form_id'  => $action->menu_order,
				'type'     => $action->post_excerpt,
				'status'   => $action->post_status,
				'settings' => $action->post_content,
			);
		}

		return $actions;
	}

	/**
	 * @since 3.06.04
	 * @return bool
	 */
	private function tracking_allowed() {
		$settings = FrmAppHelper::get_settings();
		return $settings->tracking;
	}

	/**
	 * @since 3.06.04
	 * @return string
	 */
	private function maybe_json( $value ) {
		return is_array( $value ) ? json_encode( $value ) : $value;
	}
}

