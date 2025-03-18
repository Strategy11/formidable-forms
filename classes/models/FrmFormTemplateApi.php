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
	 * @return string
	 */
	public function get_free_license() {
		if ( ! isset( self::$free_license ) ) {
			self::$free_license = get_option( self::$code_option_name );
		}

		return self::$free_license;
	}

	/**
	 * Check to make sure the free code is being used.
	 *
	 * @since 4.09.02
	 *
	 * @return bool
	 */
	public function has_free_access() {
		$free_access = $this->get_free_license();
		if ( ! $free_access ) {
			return false;
		}

		$templates    = $this->get_api_info();
		$contact_form = 20872734;
		return isset( $templates[ $contact_form ] ) && ! empty( $templates[ $contact_form ]['url'] );
	}

	/**
	 * AJAX Hook for signing free users up for a template API key
	 *
	 * @return void
	 */
	public static function signup() {
		_deprecated_function( __METHOD__, 'x.x' );
	}
}
