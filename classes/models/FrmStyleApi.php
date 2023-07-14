<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.0
 */
class FrmStyleApi extends FrmFormApi {

	/**
	 * @var string $base_api_url
	 */
	private static $base_api_url = 'https://formidableforms.com/wp-json/style-templates/v1/list';

	/**
	 * @var int $new_days
	 */
	protected $new_days = 30;

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
		$this->cache_key = 'frm_style_templates_l' . ( empty( $this->license ) ? '' : md5( $this->license ) );
	}

	public function get_api_info() {
		$api_info = parent::get_api_info();
		$api_info = $this->fill_missing_style_settings( $api_info );
		return $api_info;
	}

	/**
	 * @param array $api_info
	 * @return array
	 */
	private function fill_missing_style_settings( $api_info ) {
		// Remove 'Styling Template' from titles.
		foreach ( $api_info as $id => $template ) {
			if ( isset( $template['name'] ) ) {
				$api_info[ $id ]['name'] = preg_replace( '/(\sStyle|Styling)?(\sTemplate)?$/', '', $template['name'] );
			}
		}

		return $api_info;
	}
}
