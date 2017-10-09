<?php

/**
 * @since 3.0
 */
class FrmFieldHTML extends FrmFieldType {

	/**
	 * @var string
	 * @since 3.0
	 */
	protected $type = 'html';

	/**
	 * @var bool
	 * @since 3.0
	 */
	protected $has_input = false;

	public function default_html() {
		return '<div id="frm_field_[id]_container" class="frm_form_field form-field">[description]</div>';
	}

	/**
	 * @since 3.0
	 */
	private function after_replace_html_shortcodes( $args, $html ) {
		FrmFieldsHelper::run_wpautop( array( 'wpautop' => true ), $html );

		$html = apply_filters( 'frm_get_default_value', $html, (object) $this->field, false );
		return do_shortcode( $html );
	}

	protected function include_form_builder_file() {
		return FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/field-html.php';
	}
}
