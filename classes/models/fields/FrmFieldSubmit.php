<?php
/**
 * Submit field class
 *
 * @since x.x
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 4.03
 */
class FrmFieldSubmit extends FrmFieldType {

	protected $type = 'submit';

	protected $has_for_label = false;

	protected $has_input = false;

	public function default_html() {
		$default_html = <<<DEFAULT_HTML
<div id="frm_field_[id]_container" class="frm_form_field form-field">
	[input]
</div>
DEFAULT_HTML;

		return $default_html;
	}

	protected function get_new_field_name() {
		$form = FrmForm::getOne( $this->field->form_id );
		if ( $form && isset( $form->options['submit_value'] ) ) {
			return $form->options['submit_value'];
		}
		return parent::get_new_field_name();
	}

	protected function field_settings_for_type() {
		$settings = array(
			'required'       => false,
			'visibility'     => false,
			'label_position' => false,
			'options'        => false,
			'default'        => false,
			'description'    => false,
		);

		return $settings;
	}

	/**
	 * @param array $args - Includes 'field', 'display', and 'values'
	 */
	public function show_extra_field_choices( $args ) {
		$field = $args['field'];
		// include( FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/summary-options.php' );

		parent::show_extra_field_choices( $args );
	}

	protected function extra_field_opts() {
		return array();
	}

	protected function include_form_builder_file() {
		return FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/field-submit.php';
	}

	protected function get_excluded_ids() {
		$ids = trim( FrmField::get_option( $this->field, 'exclude_fields' ) );
		if ( ! empty( $ids ) ) {
			$ids = explode( ',', $ids );
			// trim to avoid mismatch - due to empty space - when doing in_array.
			// array_filter to remove empty spaces caused by e.g. trailing comma.
			$ids = array_filter( array_map( 'trim', $ids ) );

			return $ids;
		} else {
			return array();
		}
	}

	public function show_label_on_form_builder() {
		// Do nothing.
	}

	public function include_front_form_file() {
		return $this->include_form_builder_file();
	}
}
