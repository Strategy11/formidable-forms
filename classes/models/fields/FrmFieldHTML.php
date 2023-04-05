<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

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

	/**
	 * @since 4.0
	 *
	 * @param array $args - Includes 'field', 'display', and 'values'
	 *
	 * @return void
	 */
	public function show_primary_options( $args ) {
		$field = $args['field'];
		include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/html-content.php';

		parent::show_primary_options( $args );
	}

	/**
	 * @return string
	 */
	public function default_html() {
		return '<div id="frm_field_[id]_container" class="frm_form_field form-field">[description]</div>';
	}

	/**
	 * @since 3.0
	 */
	protected function after_replace_html_shortcodes( $args, $html ) {
		FrmFieldsHelper::run_wpautop( array( 'wpautop' => true ), $html );
		$pre_filter = $html;
		$html       = apply_filters( 'frm_get_default_value', $html, (object) $this->field, false );
		if ( $pre_filter === $html ) {
			$html = do_shortcode( $html );
		}

		return $html;
	}

	/**
	 * @return string
	 */
	public function get_container_class() {
		return ' frm_html_container';
	}

	/**
	 * @return string
	 */
	protected function include_form_builder_file() {
		return FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/field-html.php';
	}
}
