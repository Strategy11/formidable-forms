<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFormApi {

	/**
	 * @var string
	 */
	protected $license = '';

	/**
	 * @var string
	 */
	protected $cache_key = '';

	/**
	 * @var string
	 */
	protected $cache_timeout = '+6 hours';

	/**
	 * The number of days an add-on is new.
	 *
	 * @var int
	 */
	protected $new_days = 90;

	/**
	 * @since 3.06
	 */
	public function __construct( $license = null ) {
		$this->set_license( $license );
		$this->set_cache_key();
	}

	/**
	 * @since 3.06
	 *
	 * @return void
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
	 *
	 * @return void
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

		if ( $this->is_running() ) {
			// If there's no saved cache, we'll need to wait for the current request to finish.
			return array();
		}

		$this->set_running();

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

		$addons['response_code'] = wp_remote_retrieve_response_code( $response );

		foreach ( $addons as $k => $addon ) {
			if ( ! is_array( $addon ) ) {
				continue;
			}

			if ( isset( $addon['categories'] ) ) {
				$cats = array_intersect( $this->skip_categories(), $addon['categories'] );
				if ( ! empty( $cats ) ) {
					unset( $addons[ $k ] );
					continue;
				}
			}

			if ( ! array_key_exists( 'is_new', $addon ) && array_key_exists( 'released', $addon ) ) {
				$addons[ $k ]['is_new'] = $this->is_new( $addon );
			}
		}

		$this->set_cached( $addons );
		$this->done_running();

		return $addons;
	}

	/**
	 * Prevent multiple requests from running at the same time.
	 *
	 * @since 6.8.3
	 *
	 * @return bool
	 */
	protected function is_running() {
		if ( $this->run_as_multisite() ) {
			return get_site_transient( $this->transient_key() );
		}
		return get_transient( $this->transient_key() );
	}

	/**
	 * @since 6.8.3
	 *
	 * @return void
	 */
	protected function set_running() {
		$expires = 2 * MINUTE_IN_SECONDS;
		if ( $this->run_as_multisite() ) {
			set_site_transient( $this->transient_key(), true, $expires );
			return;
		}

		set_transient( $this->transient_key(), true, $expires );
	}

	/**
	 * @since 6.8.3
	 *
	 * @return void
	 */
	protected function done_running() {
		if ( $this->run_as_multisite() ) {
			delete_site_transient( $this->transient_key() );
		}
		delete_transient( $this->transient_key() );
	}

	/**
	 * Only allow one site in the network to make the api request at a time.
	 * If there is a license for the request, run individually.
	 *
	 * @since 6.8.3
	 *
	 * @return bool
	 */
	protected function run_as_multisite() {
		return is_multisite() && empty( $this->license );
	}

	/**
	 * @since 6.8.3
	 *
	 * @return string
	 */
	protected function transient_key() {
		return strtolower( __CLASS__ ) . '_request_lock';
	}

	/**
	 * @since 3.06
	 *
	 * @return string
	 */
	protected function api_url() {
		if ( empty( $this->license ) ) {
			// Direct traffic to Cloudflare worker when there is no license.
			return 'https://plapi.formidableforms.com/list/';
		}
		return 'https://formidableforms.com/wp-json/s11edd/v1/updates/';
	}

	/**
	 * @since 3.06
	 *
	 * @return string[]
	 */
	protected function skip_categories() {
		return array( 'WordPress Form Templates', 'WordPress Form Style Templates' );
	}

	/**
	 * @since 3.06
	 *
	 * @param object $license_plugin The FrmAddon object.
	 * @param array  $addons
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
				if ( is_array( $addon ) && ! empty( $addon['title'] ) && strtolower( $license_plugin->plugin_name ) === strtolower( $addon['title'] ) ) {
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
	 *
	 * @return array|bool
	 */
	protected function get_cached() {
		$cache = $this->get_cached_option();
		if ( empty( $cache ) ) {
			return false;
		}

		// If the api call is running, we can use the expired cache.
		if ( ! $this->is_running() ) {
			if ( empty( $cache['timeout'] ) || time() > $cache['timeout'] ) {
				// Cache is expired.
				return false;
			}

			$version     = FrmAppHelper::plugin_version();
			$for_current = isset( $cache['version'] ) && $cache['version'] == $version;
			if ( ! $for_current ) {
				// Force a new check.
				return false;
			}
		}

		$values = json_decode( $cache['value'], true );

		return $values;
	}

	/**
	 * Get the cache for the network if multisite.
	 *
	 * @since 6.8.3
	 *
	 * @return mixed
	 */
	protected function get_cached_option() {
		if ( is_multisite() ) {
			$cached = get_site_option( $this->cache_key );
			if ( $cached ) {
				return $cached;
			}
		}

		return get_option( $this->cache_key );
	}

	/**
	 * @since 3.06
	 *
	 * @param array $addons
	 *
	 * @return void
	 */
	protected function set_cached( $addons ) {
		$data = array(
			'timeout' => strtotime( $this->get_cache_timeout( $addons ), time() ),
			'value'   => wp_json_encode( $addons ),
			'version' => FrmAppHelper::plugin_version(),
		);

		if ( is_multisite() ) {
			update_site_option( $this->cache_key, $data );
		} else {
			update_option( $this->cache_key, $data, 'no' );
		}
	}

	/**
	 * If the last check was a a rate limit, we'll need to check again sooner.
	 *
	 * @since 6.8.3
	 *
	 * @param array $addons
	 *
	 * @return string
	 */
	protected function get_cache_timeout( $addons ) {
		$timeout = $this->cache_timeout;
		if ( isset( $addons['response_code'] ) && 429 === $addons['response_code'] ) {
			$timeout = '+5 minutes';
		}
		return $timeout;
	}

	/**
	 * @since 3.06
	 *
	 * @return void
	 */
	public function reset_cached() {
		if ( is_multisite() ) {
			delete_site_option( $this->cache_key );
		} else {
			delete_option( $this->cache_key );
		}
		$this->done_running();
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
			if ( is_string( $addons['error'] ) ) {
				$errors[] = $addons['error'];
			} elseif ( ! empty( $addons['error']['message'] ) ) {
				$errors[] = $addons['error']['message'];
			}

			do_action( 'frm_license_error', $addons['error'] );
		}

		return $errors;
	}

	/**
	 * Check if a template is new.
	 *
	 * @since 6.0
	 *
	 * @param array $addon
	 * @return bool
	 */
	protected function is_new( $addon ) {
		return strtotime( $addon['released'] ) > strtotime( '-' . $this->new_days . ' days' );
	}
}
