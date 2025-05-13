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
	 *
	 * @return void
	 */
	public function send_snapshot() {
		if ( ! $this->tracking_allowed() ) {
			return;
		}

		$ep = 'aHR0cHM6Ly91c2FnZTIuZm9ybWlkYWJsZWZvcm1zLmNvbS9zbmFwc2hvdA==';
		// $ep = base64_encode( 'http://localhost:4567/snapshot' ); // Uncomment for testing
		$body = json_encode( $this->snapshot() );

		// Setup variable for wp_remote_request.
		$post = array(
			'method'  => 'POST',
			'headers' => array(
				'Accept'         => 'application/json',
				'Content-Type'   => 'application/json',
				'Content-Length' => strlen( $body ),
			),
			'body'    => $body,
			// Without this, Debug Log catches the `http_request_failed` error.
			'timeout' => 45,
		);

		// Remove time limit to execute this function.
		if ( function_exists( 'set_time_limit' ) ) {
			set_time_limit( 0 );
		}

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
			'uuid'              => $this->uuid(),
			// Let's keep it anonymous.
			'admin_email'       => '',
			'wp_version'        => $wp_version,
			'php_version'       => phpversion(),
			'mysql_version'     => $wpdb->db_version(),
			'os'                => FrmAppHelper::get_server_os(),
			'locale'            => get_locale(),

			'active_license'    => FrmAppHelper::pro_is_installed(),
			'form_count'        => $form_counts->published,
			'entry_count'       => FrmEntry::getRecordCount(),
			'timestamp'         => gmdate( 'c' ),

			'theme_name'        => is_object( $theme_data ) ? $theme_data->Name : '', // phpcs:ignore WordPress.NamingConventions
			'plugins'           => $this->plugins(),
			'settings'          => array(
				$this->settings(),
			),
			'forms'             => $this->forms(),
			'fields'            => $this->fields(),
			'actions'           => $this->actions(),

			'onboarding-wizard' => $this->onboarding_wizard(),
			'flows'             => FrmUsageController::get_flows_data(),
			'payments'          => $this->payments(),
			'subscriptions'     => $this->payments( 'frm_subscriptions' ),
		);

		if ( method_exists( 'FrmProAddonsController', 'get_readable_license_type' ) ) {
			$snap['active_license'] = FrmProAddonsController::get_readable_license_type();
		}

		return apply_filters( 'frm_usage_snapshot', $snap );
	}

	/**
	 * Gets onboarding wizard data.
	 *
	 * @since 6.16.1
	 *
	 * @return array
	 */
	private function onboarding_wizard() {
		$data         = FrmOnboardingWizardController::get_usage_data();
		$skipped_keys = array(
			'default_email',
			'is_subscribed',
			'allows_tracking',
			'summary_emails',
			'installed_addons',
		);

		foreach ( $skipped_keys as $skipped_key ) {
			unset( $data[ $skipped_key ] );
		}

		return $data;
	}

	/**
	 * Gets payments data.
	 *
	 * @since 6.16.1
	 *
	 * @param string $table Database table name.
	 * @return array
	 */
	private function payments( $table = 'frm_payments' ) {
		$allowed_tables = array( 'frm_payments', 'frm_subscriptions' );
		if ( ! in_array( $table, $allowed_tables, true ) ) {
			return array();
		}

		if ( ! FrmTransLiteAppHelper::payments_table_exists() ) {
			return array();
		}

		global $wpdb;
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT amount, status, paysys, created_at FROM %1$s',
				$wpdb->prefix . $table
			)
		);

		$payments = array();
		foreach ( $rows as $row ) {
			$payments[] = array(
				'amount'     => (float) $row->amount,
				'status'     => $row->status,
				'gateway'    => $row->paysys,
				'created_at' => $row->created_at,
			);
		}

		return $payments;
	}

	/**
	 * @since 3.06.04
	 * @return array
	 */
	private function plugins() {
		$plugin_list = FrmAppHelper::get_plugins();

		$plugins = array();
		foreach ( $plugin_list as $slug => $info ) {
			$plugins[] = array(
				'name'    => $info['Name'],
				'slug'    => $slug,
				'version' => $info['Version'],
				'active'  => is_plugin_active( $slug ),
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
		$settings_list = FrmAppHelper::get_settings();
		$settings      = array(
			'messages'    => $this->messages( $settings_list ),
			'permissions' => $this->permissions( $settings_list ),
		);
		$pass_settings = array(
			'load_style',
			'fade_form',
			're_type',
			're_lang',
			're_multi',
			'menu',
			'mu_menu',
			'no_ips',
			'custom_header_ip',
			'enable_gdpr',
			'no_gdpr_cookies',
			'btsp_css',
			'btsp_version',
			'admin_bar',
			'summary_emails',
			'active_captcha',
			'honeypot',
			'wp_spam_check',
			'denylist_check',
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
	 *
	 * @param FrmSettings $settings_list
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

		$default = $settings_list->default_options();

		$message_settings = array();
		foreach ( $messages as $message ) {
			$message_settings[ 'changed-' . $message ] = $settings_list->$message === $default[ $message ] ? 0 : 1;
		}

		return $message_settings;
	}

	/**
	 * Include the permissions settings for each capability.
	 *
	 * @since 3.06.04
	 *
	 * @param FrmSettings $settings_list
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
				'or'               => 1,
				'parent_form_id'   => null,
				'parent_form_id <' => 1,
			),
		);

		$saved_forms = FrmForm::getAll( $s_query );
		$forms       = array();
		$settings    = array(
			'form_class',
			'akismet',
			'antispam',
			'stopforumspam',
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

		$style = new FrmStyle();
		foreach ( $saved_forms as $form ) {
			$new_form = array(
				'form_id'           => $form->id,
				'description'       => $form->description,
				'logged_in'         => $form->logged_in,
				'editable'          => $form->editable,
				'is_template'       => $form->is_template,
				'entry_count'       => FrmEntry::getRecordCount( $form->id ),
				'field_count'       => $this->form_field_count( $form->id ),
				'form_action_count' => $this->form_action_count( $form->id ),
			);

			foreach ( $settings as $setting ) {
				if ( isset( $form->options[ $setting ] ) ) {
					if ( 'custom_style' === $setting ) {
						$style->id = $form->options[ $setting ];

						if ( ! $style->id ) {
							$style_name = 0;
						} elseif ( 1 === intval( $style->id ) ) {
							$style_name = 'formidable-style';
						} else {
							$style_post = $style->get_one();
							$style_name = $style_post ? $style_post->post_name : 'formidable-style';
						}

						$new_form[ $setting ] = $style_name;
					} else {
						$new_form[ $setting ] = $this->maybe_json( $form->options[ $setting ] );
					}
				}
			}

			$forms[] = apply_filters( 'frm_usage_form', $new_form, compact( 'form' ) );
		}//end foreach

		// If the array uses numeric keys, reset them.
		return $forms;
	}

	/**
	 * @since 3.06.04
	 * @return int
	 */
	private function form_field_count( $form_id ) {
		global $wpdb;

		$join = $wpdb->prefix . 'frm_fields fi JOIN ' . $wpdb->prefix . 'frm_forms fo ON (fi.form_id=fo.id)';

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
		$args = array(
			'limit'    => 50,
			'order_by' => 'id DESC',
		);

		$fields = FrmDb::get_results( 'frm_fields', array(), 'id, form_id, name, type, field_options', $args );
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
		return FrmUsageController::tracking_allowed();
	}

	/**
	 * @since 3.06.04
	 * @return string
	 */
	private function maybe_json( $value ) {
		return is_array( $value ) ? json_encode( $value ) : $value;
	}
}
