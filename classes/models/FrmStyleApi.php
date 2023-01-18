<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmStyleApi extends FrmFormApi {

	/**
	 * @var string $base_api_url
	 */
	private static $base_api_url = 'https://formidableforms.com/wp-json/style-templates/v1/list';

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
		$divi  = $api_info[28067337];
		$sleek = $api_info[28067363];
		$lines = $api_info[28067373];
		$big   = $api_info[28067379];
		$dark  = $api_info[28067312];

		$lines['settings'] = $this->get_placeholder_lines_style();
		$big['settings']   = $this->get_placeholder_big_style();

		$api_info[28067337] = $divi;
		$api_info[28067363] = $sleek;
		$api_info[28067373] = $lines;
		$api_info[28067379] = $big;
		$api_info[28067312] = $dark;

		return $api_info;
	}

	/**
	 * @return array
	 */
	private function get_placeholder_lines_style() {
		return array(
			'fieldset_bg_color'    => '',
			'field_border_width'   => '0px 0px 1px 0px',
			'field_border_style'   => 'solid',
			'border_color'         => 'rgba(96,100,105,0.3)',
			'submit_text_color'    => 'rgba(52,64,73,1)',
			'submit_bg_color'      => 'rgba(179,192,201,1)',
			'submit_border_color'  => '',
			'submit_border_width'  => 0,
			'submit_border_radius' => '4px',
			'submit_weight'        => 600,
			'submit_text_color'    => 'rgba(52,64,73,1)',
			'submit_width'         => 'auto',
			'label_color'          => 'rgba(133,148,158,1)',
			'text_color'           => 'rgba(96,100,105,1)',
			'bg_color'             => 'ffffff',
		);
	}

	/**
	 * @return array
	 */
	private function get_placeholder_big_style() {
		return array(
			'fieldset_bg_color'    => '',
			'field_border_width'   => '1px',
			'field_border_style'   => 'solid',
			'border_color'         => 'cccccc',
			'submit_text_color'    => 'ffffff',
			'submit_bg_color'      => '00cccc',
			'submit_border_color'  => '00cccc',
			'submit_border_width'  => '1px',
			'submit_border_radius' => 0,
			'submit_weight'        => 300,
			'submit_text_color'    => 'ffffff',
			'submit_width'         => '200px',
			'label_color'          => '444444',
			'text_color'           => '555555',
			'bg_color'             => 'ffffff',
		);
	}
}
