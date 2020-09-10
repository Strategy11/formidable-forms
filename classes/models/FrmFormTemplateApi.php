<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFormTemplateApi extends FrmFormApi {

	/**
	 * @since 3.06
	 */
	protected function set_cache_key() {
		$this->cache_key = 'frm_form_templates_l' . ( empty( $this->license ) ? '' : md5( $this->license ) );
	}

	/**
	 * @since 3.06
	 */
	protected function api_url() {
		return 'https://formidableforms.com/wp-json/form-templates/v1/list';
	}

	/**
	 * @since 3.06
	 */
	protected function skip_categories() {
		return array();
	}
}
