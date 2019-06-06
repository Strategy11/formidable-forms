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

	/**
	 * @since 4.0
	 * @param array $args - Includes 'field', 'display', and 'values'
	 */
	public function show_primary_options( $args ) {
		$field = $args['field'];
		include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/html-content.php' );

		parent::show_primary_options( $args );
	}

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

	public function get_container_class() {
		return ' frm_html_container';
	}

	protected function include_form_builder_file() {
		return FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/field-html.php';
	}

	/**
	 * @since 4.0
	 */
	public function sanitize_value( &$value ) {
		FrmAppHelper::sanitize_value( 'wp_kses_post', $value );

		// do wp_specialchars_decode to get back '&' that wp_kses_post might have turned to '&amp;'
		// this MUST be done, else we'll be back to the '& entity' problem.
		$value = wp_specialchars_decode( $value );
	}
}
