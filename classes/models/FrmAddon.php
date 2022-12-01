<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/** @phpstan-consistent-constructor */
class FrmAddon {
	public $store_url = 'https://formidableforms.com';
	public $download_id;
	public $plugin_file;
	public $plugin_folder;
	public $plugin_name;
	public $plugin_slug;
	public $option_name;
	public $version;
	public $author = 'Strategy11';
	public $is_parent_licence = false;
	public $needs_license = true;
	private $is_expired_addon = false;
	public $license;
	protected $get_beta = false;

	public function __construct() {

		if ( empty( $this->plugin_slug ) ) {
			$this->plugin_slug = preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->plugin_name ) ) );
		}
		if ( empty( $this->option_name ) ) {
			$this->option_name = 'edd_' . $this->plugin_slug . '_license_';
		}

		$this->plugin_folder = plugin_basename( $this->plugin_file );
		$this->license       = $this->get_license();

		add_filter( 'frm_installed_addons', array( &$this, 'insert_installed_addon' ) );
		$this->edd_plugin_updater();
	}

	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		new static();
	}

	public function insert_installed_addon( $plugins ) {
		$plugins[ $this->plugin_slug ] = $this;

		return $plugins;
	}

	public static function get_addon( $plugin_slug ) {
		$plugins = apply_filters( 'frm_installed_addons', array() );
		$plugin  = false;
		if ( isset( $plugins[ $plugin_slug ] ) ) {
			$plugin = $plugins[ $plugin_slug ];
		}

		return $plugin;
	}

	public function edd_plugin_updater() {

		$this->is_license_revoked();
		$license = $this->license;

		add_action( 'after_plugin_row_' . plugin_basename( $this->plugin_file ), array( $this, 'maybe_show_license_message' ), 10, 2 );

		if ( ! empty( $license ) ) {

			if ( 'formidable/formidable.php' !== $this->plugin_folder ) {
				add_filter( 'plugins_api', array( &$this, 'plugins_api_filter' ), 10, 3 );
			}

			add_filter( 'site_transient_update_plugins', array( &$this, 'clear_expired_download' ) );
		}
	}

	/**
	 * Updates information on the "View version x.x details" page with custom data.
	 *
	 * @uses api_request()
	 *
	 * @param mixed $_data
	 * @param string $_action
	 * @param object $_args
	 *
	 * @return object $_data
	 */
	public function plugins_api_filter( $_data, $_action = '', $_args = null ) {

		if ( $_action != 'plugin_information' ) {
			return $_data;
		}

		$slug  = basename( $this->plugin_file, '.php' );
		$slug2 = str_replace( '/' . $slug . '.php', '', $this->plugin_folder );
		if ( empty( $_args->slug ) || ( $_args->slug != $slug && $_args->slug !== $slug2 ) ) {
			return $_data;
		}

		$item_id = $this->download_id;
		if ( empty( $item_id ) ) {
			$_data = array(
				'name'      => $this->plugin_name,
				'excerpt'   => '',
				'changelog' => 'See the full changelog at <a href="' . esc_url( $this->store_url . '/changelog/' ) . '"></a>',
				'banners'   => array(
					'high' => '',
					'low'  => 'https://ps.w.org/formidable/assets/banner-1544x500.png',
				),
			);
		} else {
			$api     = new FrmFormApi( $this->license );
			$plugins = $api->get_api_info();
			$_data   = $plugins[ $item_id ];
		}

		$_data['sections'] = array(
			'description' => $_data['excerpt'],
			'changelog'   => $_data['changelog'],
		);
		$_data['author']   = '<a href="' . esc_url( $this->store_url ) . '">' . esc_html( $this->author ) . '</a>';
		$_data['homepage'] = $this->store_url;

		return (object) $_data;
	}

	public function get_license() {
		$license = $this->maybe_get_pro_license();
		if ( ! empty( $license ) ) {
			return $license;
		}

		$license = trim( get_option( $this->option_name . 'key' ) );
		if ( empty( $license ) ) {
			$license = $this->activate_defined_license();
		}

		return $license;
	}

	/**
	 * @since 3.04.03
	 */
	protected function maybe_get_pro_license() {
		// prevent a loop if $this is the pro plugin
		$get_license = FrmAppHelper::pro_is_installed() && is_callable( 'FrmProAppHelper::get_updater' ) && $this->plugin_name != 'Formidable Pro';

		if ( ! $get_license ) {
			return false;
		}

		$api = new FrmFormApi();
		$api->get_pro_updater();
		$license = $api->get_license();
		if ( empty( $license ) ) {
			return false;
		}

		$this->get_api_info( $license );
		if ( ! $this->is_parent_licence ) {
			$license = false;
		}

		return $license;
	}

	/**
	 * Activate the license in wp-config.php
	 *
	 * @since 2.04
	 */
	public function activate_defined_license() {
		$license = $this->get_defined_license();
		if ( ! empty( $license ) && ! $this->is_active() && $this->is_time_to_auto_activate() ) {
			$response = $this->activate_license( $license );
			$this->set_auto_activate_time();
			if ( ! $response['success'] ) {
				$license = '';
			}
		}

		return $license;
	}

	/**
	 * Check the wp-config.php for the license key
	 *
	 * @since 2.04
	 */
	public function get_defined_license() {
		$consant_name = 'FRM_' . strtoupper( $this->plugin_slug ) . '_LICENSE';

		return defined( $consant_name ) ? constant( $consant_name ) : false;
	}

	public function set_license( $license ) {
		update_option( $this->option_name . 'key', $license );
	}

	/**
	 * If the license is in the config, limit the frequency of checks.
	 * The license may be entered incorrectly, so we don't want to check on every page load.
	 *
	 * @since 2.04
	 */
	private function is_time_to_auto_activate() {
		$last_try = get_option( $this->option_name . 'last_activate' );

		return ( ! $last_try || $last_try < strtotime( '-1 day' ) );
	}

	private function set_auto_activate_time() {
		update_option( $this->option_name . 'last_activate', time() );
	}

	public function is_active() {
		return get_option( $this->option_name . 'active' );
	}

	/**
	 * @since 3.04.03
	 *
	 * @param array error
	 */
	public function maybe_clear_license( $error ) {
		if ( $error['code'] === 'disabled' && $error['license'] === $this->license ) {
			$this->clear_license();
		}
	}

	public function clear_license() {
		delete_option( $this->option_name . 'active' );
		delete_option( $this->option_name . 'key' );
		delete_site_option( $this->transient_key() );
		delete_option( $this->transient_key() );
		$this->delete_cache();
	}

	public function set_active( $is_active ) {
		update_option( $this->option_name . 'active', $is_active );
		$this->delete_cache();
		FrmAppHelper::save_combined_js();
		$this->update_pro_capabilities();
	}

	/**
	 * Updates roles capabilities after pro license is active.
	 *
	 * @since 5.0
	 */
	protected function update_pro_capabilities() {
		global $wp_roles;

		if ( ! function_exists( 'get_editable_roles' ) ) {
			require_once ABSPATH . 'wp-admin/includes/user.php';
		}

		$caps     = FrmAppHelper::frm_capabilities( 'pro_only' );
		$roles    = get_editable_roles();
		$settings = new FrmSettings();
		foreach ( $caps as $cap => $cap_desc ) {
			$cap_roles = (array) ( isset( $settings->$cap ) ? $settings->$cap : 'administrator' );

			// Make sure administrators always have permissions.
			if ( ! in_array( 'administrator', $cap_roles ) ) {
				array_push( $cap_roles, 'administrator' );
			}

			foreach ( $roles as $role => $details ) {
				if ( in_array( $role, $cap_roles ) ) {
					$wp_roles->add_cap( $role, $cap );
				} else {
					$wp_roles->remove_cap( $role, $cap );
				}
			}
		}
	}

	/**
	 * @since 3.04.03
	 */
	protected function delete_cache() {
		delete_transient( 'frm_api_licence' );

		$api = new FrmFormApi( $this->license );
		$api->reset_cached();

		$api = new FrmFormTemplateApi( $this->license );
		$api->reset_cached();

		$api = new FrmApplicationApi( $this->license );
		$api->reset_cached();
	}

	/**
	 * The Pro version includes the show_license_message function.
	 * We need an extra check before we allow it to show a message.
	 *
	 * @since 3.04.03
	 */
	public function maybe_show_license_message( $file, $plugin ) {
		if ( $this->is_expired_addon || isset( $plugin['package'] ) ) {
			// let's not show a ton of duplicate messages
			return;
		}

		$this->show_license_message( $file, $plugin );
	}

	public function show_license_message( $file, $plugin ) {
		$message = '';
		if ( empty( $this->license ) ) {
			/* translators: %1$s: Plugin name, %2$s: Start link HTML, %3$s: end link HTML */
			$message = sprintf( esc_html__( 'Your %1$s license key is missing. Please add it on the %2$slicenses page%3$s.', 'formidable' ), esc_html( $this->plugin_name ), '<a href="' . esc_url( admin_url( 'admin.php?page=formidable-settings' ) ) . '">', '</a>' );
		} else {
			$api    = new FrmFormApi( $this->license );
			$errors = $api->error_for_license();
			if ( ! empty( $errors ) ) {
				$message = reset( $errors );
			}
		}

		if ( empty( $message ) ) {
			return;
		}

		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
		$id            = sanitize_title( $plugin['Name'] ) . '-next';

		echo '<tr class="plugin-update-tr active" id="' . esc_attr( $id ) . '"><td colspan="' . esc_attr( $wp_list_table->get_column_count() ) . '" class="plugin-update colspanchange"><div class="update-message notice error inline notice-error notice-alt"><p>';
		echo FrmAppHelper::kses( $message, 'a' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<script type="text/javascript">var d = document.getElementById("' . esc_attr( $id ) . '").previousSibling;if ( d !== null ){ d.className = d.className + " update"; }</script>';
		echo '</p></div></td></tr>';
	}

	public function clear_expired_download( $transient ) {
		if ( ! is_object( $transient ) ) {
			return $transient;
		}

		if ( $this->is_current_version( $transient ) ) {
			//make sure it doesn't show there is an update if plugin is up-to-date
			if ( isset( $transient->response[ $this->plugin_folder ] ) ) {
				unset( $transient->response[ $this->plugin_folder ] );
			}
		} elseif ( isset( $transient->response ) && isset( $transient->response[ $this->plugin_folder ] ) ) {
			$this->prepare_update_details( $transient->response[ $this->plugin_folder ] );

			// if the transient has expired, clear the update and trigger it again
			if ( $transient->response[ $this->plugin_folder ] === false ) {
				if ( ! $this->has_been_cleared() ) {
					$this->cleared_plugins();
					$this->manually_queue_update();
				}
				unset( $transient->response[ $this->plugin_folder ] );
			}
		}

		return $transient;
	}

	/**
	 * Check if the plugin information is correct to allow an update
	 *
	 * @since 3.04.03
	 *
	 * @param object $transient - the current plugin info saved for update
	 */
	private function prepare_update_details( &$transient ) {
		$version_info = $transient;
		$has_beta_url = isset( $version_info->beta ) && ! empty( $version_info->beta );
		if ( $this->get_beta && ! $has_beta_url ) {
			$version_info = (object) $this->get_api_info( $this->license );
		}

		if ( isset( $version_info->new_version ) && ! empty( $version_info->new_version ) ) {
			$this->clear_old_plugin_version( $version_info );
			if ( $version_info === false ) { // was cleared with timeout
				$transient = false;
			} else {
				$this->maybe_use_beta_url( $version_info );

				if ( version_compare( $version_info->new_version, $this->version, '>' ) ) {
					$transient = $version_info;
				}
			}
		}
	}

	/**
	 * Get the API info for this plugin
	 *
	 * @since 3.04.03
	 */
	protected function get_api_info( $license ) {
		$api   = new FrmFormApi( $license );
		$addon = $api->get_addon_for_license( $this );

		// if there is no download url, this license does not apply to the addon
		if ( isset( $addon['package'] ) ) {
			$this->is_parent_licence = true;
		} elseif ( isset( $addon['error'] ) ) {
			// if the license is expired, we must assume all add-ons were packaged
			$this->is_parent_licence = true;
			$this->is_expired_addon  = true;
		}

		return $addon;
	}

	/**
	 * Make sure transients don't stick around on sites that
	 * don't save the transient expiration
	 *
	 * @since 2.05.05
	 */
	private function clear_old_plugin_version( &$version_info ) {
		$timeout = ( isset( $version_info->timeout ) && ! empty( $version_info->timeout ) ) ? $version_info->timeout : 0;
		if ( ! empty( $timeout ) && time() > $timeout ) {
			$version_info = false; // Cache is expired
			$api          = new FrmFormApi( $this->license );
			$api->reset_cached();
		}
	}

	/**
	 * The beta url is always included if the download has a beta.
	 * Check if the beta should be downloaded.
	 *
	 * @since 3.04.03
	 */
	private function maybe_use_beta_url( &$version_info ) {
		if ( $this->get_beta && isset( $version_info->beta ) && ! empty( $version_info->beta ) ) {
			$version_info->new_version = $version_info->beta['version'];
			$version_info->package     = $version_info->beta['package'];
			if ( isset( $version_info->plugin ) && ! empty( $version_info->plugin ) ) {
				$version_info->plugin = $version_info->beta['plugin'];
			}
		}
	}

	private function is_current_version( $transient ) {
		if ( empty( $transient->checked ) || ! isset( $transient->checked[ $this->plugin_folder ] ) ) {
			return false;
		}

		$response = ! isset( $transient->response ) || empty( $transient->response );
		if ( $response ) {
			return true;
		}

		return isset( $transient->response ) && isset( $transient->response[ $this->plugin_folder ] ) && $transient->checked[ $this->plugin_folder ] === $transient->response[ $this->plugin_folder ]->new_version;
	}

	private function has_been_cleared() {
		$last_cleared = get_option( 'frm_last_cleared' );

		return ( $last_cleared && $last_cleared > gmdate( 'Y-m-d H:i:s', strtotime( '-5 minutes' ) ) );
	}

	private function cleared_plugins() {
		update_option( 'frm_last_cleared', gmdate( 'Y-m-d H:i:s' ) );
	}

	private function is_license_revoked() {
		if ( empty( $this->license ) || empty( $this->plugin_slug ) || isset( $_POST['license'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return;
		}

		if ( is_multisite() ) {
			$last_checked = get_site_option( $this->transient_key() );
		} else {
			$last_checked = get_option( $this->transient_key() );
		}

		$seven_days_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) );

		if ( ! $last_checked || $last_checked < $seven_days_ago ) {
			// check weekly
			if ( is_multisite() ) {
				update_site_option( $this->transient_key(), gmdate( 'Y-m-d H:i:s' ) );
			} else {
				update_option( $this->transient_key(), gmdate( 'Y-m-d H:i:s' ) );
			}

			$response = $this->get_license_status();
			if ( 'revoked' === $response['status'] || 'blocked' === $response['status'] || 'disabled' === $response['status'] || 'missing' === $response['status'] ) {
				$this->clear_license();
			}
		}
	}

	/**
	 * Use a new cache after the license is changed, or Formidable is updated.
	 */
	private function transient_key() {
		return 'frm_' . md5( sanitize_key( $this->license . '_' . $this->plugin_slug ) );
	}

	public static function activate() {
		FrmAppHelper::permission_check( 'frm_change_settings' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$license = stripslashes( FrmAppHelper::get_param( 'license', '', 'post', 'sanitize_text_field' ) );
		if ( empty( $license ) ) {
			wp_die(
				json_encode(
					array(
						'message' => __( 'Oops! You forgot to enter your license number.', 'formidable' ),
						'success' => false,
					)
				)
			);
		}

		$plugin_slug = FrmAppHelper::get_param( 'plugin', '', 'post', 'sanitize_text_field' );
		$response    = self::activate_license_for_plugin( $license, $plugin_slug );

		echo json_encode( $response );
		wp_die();
	}

	/**
	 * @since 4.08
	 */
	public static function activate_license_for_plugin( $license, $plugin_slug ) {
		$this_plugin = self::get_addon( $plugin_slug );
		return $this_plugin->activate_license( $license );
	}

	private function activate_license( $license ) {
		$this->set_license( $license );
		$this->license = $license;

		$response            = $this->get_license_status();
		$response['message'] = '';
		$response['success'] = false;

		if ( $response['error'] ) {
			$response['message'] = $response['status'];
		} else {
			$messages = $this->get_messages();
			if ( is_string( $response['status'] ) && isset( $messages[ $response['status'] ] ) ) {
				$response['message'] = $messages[ $response['status'] ];
			} else {
				$response['message'] = FrmAppHelper::kses( $response['status'], array( 'a' ) );
			}

			$is_valid = false;
			if ( 'valid' === $response['status'] ) {
				$is_valid            = 'valid';
				$response['success'] = true;
			}
			$this->set_active( $is_valid );
		}

		return $response;
	}

	private function get_license_status() {
		$response = array(
			'status' => 'missing',
			'error'  => true,
		);
		if ( empty( $this->license ) ) {
			$response['error'] = false;

			return $response;
		}

		try {
			$response['error'] = false;
			$license_data      = $this->send_mothership_request( 'activate_license' );

			// $license_data->license will be either "valid" or "invalid"
			if ( is_array( $license_data ) ) {
				if ( in_array( $license_data['license'], array( 'valid', 'invalid' ), true ) ) {
					$response['status'] = $license_data['license'];
				}
			} else {
				$response['status'] = $license_data;
			}
		} catch ( Exception $e ) {
			$response['status'] = $e->getMessage();
		}

		return $response;
	}

	private function get_messages() {
		return array(
			'valid'               => __( 'Your license has been activated. Enjoy!', 'formidable' ),
			'invalid'             => __( 'That license key is invalid', 'formidable' ),
			'expired'             => __( 'That license is expired', 'formidable' ),
			'revoked'             => __( 'That license has been refunded', 'formidable' ),
			'no_activations_left' => __( 'That license has been used on too many sites', 'formidable' ),
			'invalid_item_id'     => __( 'Oops! That is the wrong license key for this plugin.', 'formidable' ),
			'missing'             => __( 'That license key is invalid', 'formidable' ),
		);
	}

	/**
	 * @since 4.03
	 */
	public static function reset_cache() {
		FrmAppHelper::permission_check( 'frm_change_settings' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$this_plugin = self::set_license_from_post();
		$this_plugin->delete_cache();

		$response = array(
			'success' => true,
			'message' => __( 'Cache cleared', 'formidable' ),
		);

		echo json_encode( $response );
		wp_die();
	}

	public static function deactivate() {
		FrmAppHelper::permission_check( 'frm_change_settings' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$this_plugin = self::set_license_from_post();

		$response = array(
			'success' => false,
			'message' => '',
		);
		try {
			// $license_data->license will be either "deactivated" or "failed"
			$license_data = $this_plugin->send_mothership_request( 'deactivate_license' );
			if ( is_array( $license_data ) && 'deactivated' === $license_data['license'] ) {
				$response['success'] = true;
				$response['message'] = __( 'That license was removed successfully', 'formidable' );
			} else {
				$response['message'] = __( 'There was an error deactivating your license.', 'formidable' );
			}
		} catch ( Exception $e ) {
			$response['message'] = $e->getMessage();
		}

		$this_plugin->clear_license();

		echo json_encode( $response );
		wp_die();
	}

	/**
	 * @since 4.03
	 */
	private static function set_license_from_post() {
		$plugin_slug          = FrmAppHelper::get_param( 'plugin', '', 'post', 'sanitize_text_field' );
		$this_plugin          = self::get_addon( $plugin_slug );
		$license              = $this_plugin->get_license();
		$this_plugin->license = $license;
		return $this_plugin;
	}

	public function send_mothership_request( $action ) {
		$api_params = array(
			'edd_action' => $action,
			'license'    => $this->license,
			'url'        => home_url(),
		);
		if ( is_numeric( $this->download_id ) ) {
			$api_params['item_id'] = absint( $this->download_id );
		} else {
			$api_params['item_name'] = rawurlencode( $this->plugin_name );
		}

		$arg_array = array(
			'body'       => $api_params,
			'timeout'    => 25,
			'user-agent' => $this->plugin_slug . '/' . $this->version . '; ' . get_bloginfo( 'url' ),
		);

		$resp = wp_remote_post( $this->store_url, $arg_array );
		$body = wp_remote_retrieve_body( $resp );

		$message = __( 'Your License Key was invalid', 'formidable' );
		if ( is_wp_error( $resp ) ) {
			$link = FrmAppHelper::admin_upgrade_link( 'api', 'knowledgebase/why-cant-i-activate-formidable-pro/' );
			/* translators: %1$s: Start link HTML, %2$s: End link HTML */
			$message = sprintf( __( 'You had an error communicating with the Formidable API. %1$sClick here%2$s for more information.', 'formidable' ), '<a href="' . esc_url( $link ) . '" target="_blank">', '</a>' );
			$message .= ' ' . $resp->get_error_message();
		} elseif ( 'error' === $body || is_wp_error( $body ) ) {
			$message = __( 'You had an HTTP error connecting to the Formidable API', 'formidable' );
		} else {
			$json_res = json_decode( $body, true );
			if ( null !== $json_res ) {
				if ( is_array( $json_res ) && isset( $json_res['error'] ) ) {
					$message = $json_res['error'];
				} else {
					$message = $json_res;
				}
			} elseif ( isset( $resp['response'] ) && isset( $resp['response']['code'] ) ) {
				/* translators: %1$s: Error code, %2$s: Error message */
				$message = sprintf( __( 'There was a %1$s error: %2$s', 'formidable' ), $resp['response']['code'], $resp['response']['message'] . ' ' . $resp['body'] );
			}
		}

		return $message;
	}

	public function manually_queue_update() {
		$updates               = new stdClass();
		$updates->last_checked = 0;
		$updates->response     = array();
		$updates->translations = array();
		$updates->no_update    = array();
		$updates->checked      = array();
		set_site_transient( 'update_plugins', $updates );
	}
}
