<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmAddon {
	public $store_url = 'https://formidablepro.com';
	public $plugin_file;
	public $plugin_name;
	public $plugin_slug;
	public $option_name;
	public $version;
	public $author = 'Strategy11';

	public function __construct() {

		if ( empty( $this->plugin_slug ) ) {
			$this->plugin_slug = preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $this->plugin_name ) ) );
		}
		if ( empty( $this->option_name ) ) {
			$this->option_name = 'edd_' . $this->plugin_slug . '_license_';
		}

		add_filter( 'frm_installed_addons', array( &$this, 'insert_installed_addon' ) );
		$this->edd_plugin_updater();
	}

	public static function load_hooks() {
		add_filter( 'frm_include_addon_page', '__return_true' );
		//new static();
	}

	public function insert_installed_addon( $plugins ) {
		$plugins[ $this->plugin_slug ] = $this;
		return $plugins;
	}

	public static function get_addon( $plugin_slug ) {
		$plugins = apply_filters( 'frm_installed_addons', array() );
		$plugin = false;
		if ( isset( $plugins[ $plugin_slug ] ) ) {
			$plugin = $plugins[ $plugin_slug ];
		}
		return $plugin;
	}

	public function edd_plugin_updater() {

		// retrieve our license key from the DB
		$license = trim( get_option( $this->option_name . 'key' ) );

		if ( empty( $license ) ) {
			add_action( 'after_plugin_row_' . plugin_basename( $this->plugin_file ), array( $this, 'show_license_message' ), 10, 2 );
		} else {
			if ( ! class_exists('EDD_SL_Plugin_Updater') ) {
				include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
			}

			// setup the updater
			new EDD_SL_Plugin_Updater( $this->store_url, $this->plugin_file, array(
				'version' 	=> $this->version,
				'license' 	=> $license,
				'author' 	=> $this->author,
			) );
		}
	}

	public function show_license_message( $file, $plugin ) {
		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
		echo '<tr class="plugin-update-tr active"><td colspan="' . esc_attr( $wp_list_table->get_column_count() ) . '" class="plugin-update colspanchange"><div class="update-message">';
		echo sprintf( __( 'Your %1$s license key is missing. Please add it on the %2$slicenses page%3$s.', 'formidable' ), $this->plugin_name, '<a href="' . esc_url( admin_url('admin.php?page=formidable-settings&t=licenses_settings' ) ) . '">', '</a>' );
		$id = sanitize_title( $plugin['Name'] );
		echo '<script type="text/javascript">var d = document.getElementById("' . esc_attr( $id ) . '");if ( d !== null ){ d.className = d.className + " update"; }</script>';
		echo '</div></td></tr>';
	}

	public static function activate() {
	 	check_ajax_referer( 'frm_ajax', 'nonce' );

		if ( ! isset( $_POST['license'] ) || empty( $_POST['license'] ) ) {
			wp_die( __( 'Oops! You forgot to enter your license number.', 'formidable' ) );
		}

		$license = stripslashes( sanitize_text_field( $_POST['license'] ) );
		$plugin_slug = sanitize_text_field( $_POST['plugin'] );
		$this_plugin = self::get_addon( $plugin_slug );
		update_option( $this_plugin->option_name . 'key', $license );

		$response = array( 'success' => false, 'message' => '' );
		try {
			$license_data = $this_plugin->send_mothership_request( 'activate_license', $license );

			// $license_data->license will be either "valid" or "invalid"
			$is_valid = 'invalid';
			if ( is_array( $license_data ) && $license_data['license'] == 'valid' ) {
				$is_valid = $license_data['license'];
				$response['success'] = __( 'Enjoy!', 'formidable' );
			} else {
				$response['message'] = __( 'That license is invalid', 'formidable' );
			}

			update_option( $this_plugin->option_name . 'active', $is_valid );
		} catch ( Exception $e ) {
			$response['message'] = $e->getMessage();
		}

		echo json_encode( $response );
		wp_die();
	}

	public static function deactivate() {
		check_ajax_referer( 'frm_ajax', 'nonce' );

		$license = stripslashes( sanitize_text_field( $_POST['license'] ) );
		$plugin_slug = sanitize_text_field( $_POST['plugin'] );
		$this_plugin = self::get_addon( $plugin_slug );

		$response = array( 'success' => false, 'message' => '' );
		try {
			// $license_data->license will be either "deactivated" or "failed"
			$license_data = $this_plugin->send_mothership_request( 'deactivate_license', $license );
			if ( is_array( $license_data ) && $license_data['license'] == 'deactivated' ) {
				$response['success'] = true;
				$response['message'] = __( 'That license was removed successfully', 'helpdesk' );
			} else {
				$response['message'] = __( 'There was an error deactivating your license.', 'formidable' );
			}
		} catch ( Exception $e ) {
			$response['message'] = $e->getMessage();
		}

		delete_option( $this_plugin->option_name . 'active' );
		delete_option( $this_plugin->option_name . 'key' );

		echo json_encode( $response );
		wp_die();
	}

	public function send_mothership_request( $action, $license ) {
		$api_params = array(
			'edd_action' => $action,
			'license'    => $license,
			'item_name'  => urlencode( $this->plugin_name ),
			'url'        => home_url(),
		);

		$arg_array = array(
			'body'      => $api_params,
			'timeout'   => 15,
			'sslverify' => false,
			'user-agent' => $this->plugin_slug . '/' . $this->version . '; ' . get_bloginfo( 'url' ),
		);

		$resp = wp_remote_post( $this->store_url, $arg_array );
		$body = wp_remote_retrieve_body( $resp );

		if ( is_wp_error( $resp ) ) {
			$message = sprintf( __( 'You had an error communicating with Formidable Pro\'s API. %1$sClick here%2$s for more information.', 'formidable' ), '<a href="http://formidablepro.com/knowledgebase/why-cant-i-activate-formidable-pro/" target="_blank">', '</a>');
			if ( is_wp_error( $resp ) ) {
				$message .= ' '. $resp->get_error_message();
			}
			return $message;
		} else if ( $body == 'error' || is_wp_error( $body ) ) {
			return __( 'You had an HTTP error connecting to Formidable Pro\'s API', 'formidable' );
		} else {
			$json_res = json_decode( $body, true );
			if ( null !== $json_res ) {
				if ( is_array( $json_res ) && isset( $json_res['error'] ) ) {
					return $json_res['error'];
				} else {
					return $json_res;
				}
			} else if ( isset( $resp['response'] ) && isset( $resp['response']['code'] ) ) {
				return sprintf( __( 'There was a %1$s error: %2$s', 'formidable' ), $resp['response']['code'], $resp['response']['message'] .' '. $resp['body'] );
			}
		}

		return __( 'Your License Key was invalid', 'formidable' );
	}
}
