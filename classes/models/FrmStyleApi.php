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
		
		//{"form_align":"left","form_width":"100%","fieldset_bg_color":"","fieldset":"0px","fieldset_color":"000000","fieldset_padding":"0 0 15px 0","font":"\"DIN Web\",sans-serif","direction":"ltr","title_size":"25px","title_color":"444444","title_margin_top":"10px","title_margin_bottom":"10px","form_desc_size":"18px","form_desc_color":"666666","form_desc_margin_top":"10px","form_desc_margin_bottom":"25px","form_desc_padding":"0","label_color":"444444","weight":"500","font_size":"20px","position":"none","align":"left","width":"150px","label_padding":"0","required_color":"B94A48","required_weight":"300","description_color":"666666","description_weight":"300","description_style":"normal","description_font_size":"14px","description_align":"left","description_margin":"0","bg_color":"ffffff","text_color":"555555","border_color":"cccccc","field_border_width":"1px","field_border_style":"solid","remove_box_shadow":"1","bg_color_active":"ffffff","border_color_active":"00cccc","remove_box_shadow_active":"1","bg_color_error":"ffffff","text_color_error":"444444","border_color_error":"B94A48","border_width_error":"1px","border_style_error":"solid","bg_color_disabled":"ffffff","text_color_disabled":"A1A1A1","border_color_disabled":"E5E5E5","field_font_size":"18px","field_height":"46px","field_width":"100%","field_pad":"10px 10px 10px 18px","field_margin":"20px","border_radius":"0","field_weight":"200","radio_align":"block","check_align":"block","check_label_color":"444444","check_weight":"200","check_font_size":"16px","submit_font_size":"18px","submit_width":"200px","submit_height":"46px","submit_weight":"300","submit_border_radius":"0","submit_bg_color":"00cccc","submit_text_color":"ffffff","submit_border_color":"00cccc","submit_border_width":"1px","submit_shadow_color":"eeeeee","submit_bg_img":"","submit_margin":"10px","submit_padding":"6px 11px","submit_hover_bg_color":"009999","submit_hover_color":"ffffff","submit_hover_border_color":"009999","submit_active_bg_color":"009999","submit_active_color":"ffffff","submit_active_border_color":"009999","success_bg_color":"DFF0D8","success_border_color":"D6E9C6","success_text_color":"468847","success_font_size":"18px","error_bg":"F2DEDE","error_border":"EBCCD1","error_text":"B94A48","error_font_size":"18px","section_color":"444444","section_weight":"500","section_font_size":"22px","section_bg_color":"","section_pad":"12px 0 8px 0","section_mar_top":"15px","section_mar_bottom":"15px","section_border_color":"e8e8e8","section_border_width":"2px","section_border_style":"solid","section_border_loc":"-top","collapse_icon":"6","collapse_pos":"after","repeat_icon":"1","repeat_icon_color":"ffffff","theme_selector":"ui-lightness","theme_css":"ui-lightness","theme_name":"Smoothness","date_head_bg_color":"00cccc","date_head_color":"ffffff","date_band_color":"009a9a","toggle_on_color":"00cccc","toggle_off_color":"dddddd","toggle_font_size":"20px","slider_color":"00cccc","slider_bar_color":"00cccc","slider_font_size":"24px","progress_bg_color":"dddddd","progress_color":"ffffff","progress_active_bg_color":"00cccc","progress_active_color":"ffffff","progress_border_color":"dfdfdf","progress_border_size":"1px","progress_size":"46px","custom_css":"","center_form":"","line_height":"normal","auto_width":"","submit_style":"","important_style":""}
	}
}
