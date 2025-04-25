<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFormTemplateApi extends FrmFormApi {

	protected static $code_option_name = 'frm_free_license_code';

	private static $base_api_url = 'https://formidableforms.com/wp-json/form-templates/v1/';

	protected static $free_license;

	/**
	 * @var int
	 */
	protected $new_days = 30;

	/**
	 * @var string
	 */
	protected $cache_timeout = '+12 hours';

	/**
	 * @since 3.06
	 *
	 * @return void
	 */
	protected function set_cache_key() {
		$this->cache_key = 'frm_form_templates_l';

		if ( ! empty( $this->license ) ) {
			$this->cache_key .= md5( $this->license );
		} else {
			$this->cache_key .= '01fd72122f4a90f15684915c729d4368';
		}
	}

	/**
	 * @since 3.06
	 *
	 * @return string
	 */
	protected function api_url() {
		$url = self::$base_api_url . 'list';

		if ( empty( $this->license ) ) {
			$url .= '?l=RlJFRVRFTVBMQVRFUyEhIQ%3D%3D&v=' . FrmAppHelper::plugin_version();
		}

		return $url;
	}

	/**
	 * @since 3.06
	 *
	 * @return string[]
	 */
	protected function skip_categories() {
		return array();
	}

	/**
	 * AJAX Hook for signing free users up for a template API key
	 *
	 * @return void
	 */
	public static function signup() {
		_deprecated_function( __METHOD__, '6.20' );
	}

	/**
	 * @return string
	 */
	public function get_free_license() {
		_deprecated_function( __METHOD__, '6.20' );
		return '';
	}

	/**
	 * Check to make sure the free code is being used.
	 *
	 * @since 4.09.02
	 *
	 * @return bool
	 */
	public function has_free_access() {
		_deprecated_function( __METHOD__, '6.20' );
		return true;
	}
}
