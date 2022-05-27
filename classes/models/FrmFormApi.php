<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFormApi {

	protected $license = '';
	protected $cache_key = '';
	protected $cache_timeout = '+6 hours';

	/**
	 * @since 3.06
	 */
	public function __construct( $license = null ) {
		$this->set_license( $license );
		$this->set_cache_key();
	}

	/**
	 * @since 3.06
	 */
	private function set_license( $license ) {
		if ( $license === null ) {
			$edd_update = $this->get_pro_updater();
			if ( ! empty( $edd_update ) ) {
				$license = $edd_update->license;
			}
		}
		$this->license = $license;
	}

	/**
	 * @since 3.06
	 * @return string
	 */
	public function get_license() {
		return $this->license;
	}

	/**
	 * @since 3.06
	 */
	protected function set_cache_key() {
		$this->cache_key = 'frm_addons_l' . ( empty( $this->license ) ? '' : md5( $this->license ) );
	}

	/**
	 * @since 3.06
	 * @return string
	 */
	public function get_cache_key() {
		return $this->cache_key;
	}

	/**
	 * @since 3.06
	 * @return array
	 */
	public function get_api_info() {
		$url = $this->api_url();
		if ( ! empty( $this->license ) ) {
			$url .= '?l=' . urlencode( base64_encode( $this->license ) );
		}

		$addons = $this->get_cached();
		if ( is_array( $addons ) ) {
			return $addons;
		}

		// We need to know the version number to allow different downloads.
		$agent = 'formidable/' . FrmAppHelper::plugin_version();
		if ( class_exists( 'FrmProDb' ) ) {
			$agent = 'formidable-pro/' . FrmProDb::$plug_version;
		}

		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 25,
				'user-agent' => $agent . '; ' . get_bloginfo( 'url' ),
			)
		);

		if ( is_array( $response ) && ! is_wp_error( $response ) ) {
			$addons = $response['body'] ? json_decode( $response['body'], true ) : array();
		}

		if ( ! is_array( $addons ) ) {
			$addons = array();
		}

		foreach ( $addons as $k => $addon ) {
			if ( ! isset( $addon['categories'] ) ) {
				continue;
			}
			$cats = array_intersect( $this->skip_categories(), $addon['categories'] );
			if ( ! empty( $cats ) ) {
				unset( $addons[ $k ] );
			}
		}

		$this->set_cached( $addons );

		return $addons;
	}

	/**
	 * @since 3.06
	 */
	protected function api_url() {
		return 'https://formidableforms.com/wp-json/s11edd/v1/updates/';
	}

	/**
	 * @since 3.06
	 */
	protected function skip_categories() {
		return array( 'WordPress Form Templates', 'WordPress Form Style Templates' );
	}

	/**
	 * @since 3.06
	 *
	 * @param object $license_plugin The FrmAddon object
	 *
	 * @return array
	 */
	public function get_addon_for_license( $license_plugin, $addons = array() ) {
		if ( empty( $addons ) ) {
			$addons = $this->get_api_info();
		}
		$download_id = $license_plugin->download_id;
		$plugin      = array();
		if ( empty( $download_id ) && ! empty( $addons ) ) {
			foreach ( $addons as $addon ) {
				if ( strtolower( $license_plugin->plugin_name ) == strtolower( $addon['title'] ) ) {
					return $addon;
				}
			}
		} elseif ( isset( $addons[ $download_id ] ) ) {
			$plugin = $addons[ $download_id ];
		}

		return $plugin;
	}

	/**
	 * @since 3.06
	 */
	public function get_pro_updater() {
		if ( FrmAppHelper::pro_is_installed() && is_callable( 'FrmProAppHelper::get_updater' ) ) {
			$updater = FrmProAppHelper::get_updater();
			$this->set_license( $updater->license );

			return $updater;
		}

		return false;
	}

	/**
	 * @since 3.06
	 * @return array
	 */
	protected function get_cached() {
		$cache = get_option( $this->cache_key );

		FrmAppHelper::filter_gmt_offset();

		if ( empty( $cache ) || empty( $cache['timeout'] ) || current_time( 'timestamp' ) > $cache['timeout'] ) {
			return false; // Cache is expired
		}

		$version     = FrmAppHelper::plugin_version();
		$for_current = isset( $cache['version'] ) && $cache['version'] == $version;
		if ( ! $for_current ) {
			// Force a new check.
			return false;
		}

		return json_decode( $cache['value'], true );
	}

	/**
	 * @since 3.06
	 */
	protected function set_cached( $addons ) {
		FrmAppHelper::filter_gmt_offset();

		$data = array(
			'timeout' => strtotime( $this->cache_timeout, current_time( 'timestamp' ) ),
			'value'   => json_encode( $addons ),
			'version' => FrmAppHelper::plugin_version(),
		);

		update_option( $this->cache_key, $data, 'no' );
	}

	/**
	 * @since 3.06
	 */
	public function reset_cached() {
		delete_option( $this->cache_key );
	}

	/**
	 * @since 3.06
	 * @return array
	 */
	public function error_for_license() {
		$errors = array();
		if ( ! empty( $this->license ) ) {
			$errors = $this->get_error_from_response();
		}

		return $errors;
	}

	/**
	 * @since 3.06
	 * @return array
	 */
	public function get_error_from_response( $addons = array() ) {
		if ( empty( $addons ) ) {
			$addons = $this->get_api_info();
		}
		$errors = array();
		if ( isset( $addons['error'] ) ) {
			$errors[] = $addons['error']['message'];
			do_action( 'frm_license_error', $addons['error'] );
		}

		return $errors;
	}
}
