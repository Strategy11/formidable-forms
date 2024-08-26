<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 5.3
 */
class FrmApplicationApi extends FrmFormApi {

	/**
	 * @var string
	 */
	private static $base_api_url = 'https://formidableforms.com/wp-json/view-templates/v1/list';

	/**
	 * @var int
	 */
	protected $new_days = 60;

	/**
	 * @var string
	 */
	protected $cache_timeout = '+12 hours';

	/**
	 * @return string
	 */
	protected function api_url() {
		return self::$base_api_url;
	}

	/**
	 * @return void
	 */
	protected function set_cache_key() {
		$this->cache_key = 'frm_applications_l' . ( empty( $this->license ) ? '' : md5( $this->license ) );
	}
}
