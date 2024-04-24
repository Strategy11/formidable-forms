<?php
/**
 * Submit field class
 *
 * @since 6.9
 * @package Formidable
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.9
 */
class FrmFieldSubmit extends FrmFieldType {

	/**
	 * Field type.
	 *
	 * @var string
	 */
	protected $type = FrmSubmitHelper::FIELD_TYPE;

	/**
	 * Has for label or not?
	 *
	 * @var bool
	 */
	protected $has_for_label = false;

	/**
	 * Has input or not?
	 *
	 * @var bool
	 */
	protected $has_input = false;

	/**
	 * Default HTML.
	 *
	 * @return string
	 */
	public function default_html() {
		$default_html = <<<DEFAULT_HTML
<div id="frm_field_[id]_container" class="frm_form_field form-field [required_class][error_class]">
	[input]
</div>
DEFAULT_HTML;

		return $default_html;
	}

	/**
	 * Sets settings for this field type.
	 *
	 * @return array
	 */
	protected function field_settings_for_type() {
		$settings = array(
			'required'       => false,
			'visibility'     => false,
			'label_position' => false,
			'options'        => false,
			'default'        => false,
			'description'    => false,
			'logic'          => true,
		);

		return $settings;
	}

	/**
	 * Includes form builder file.
	 *
	 * @return string
	 */
	protected function include_form_builder_file() {
		return FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/field-submit.php';
	}

	/**
	 * Shows label on form builder.
	 */
	public function show_label_on_form_builder() {
		// Do nothing.
	}

	/**
	 * Gets frontend field input.
	 *
	 * @param array $args           Args.
	 * @param array $shortcode_atts Shortcode atts.
	 * @return string
	 */
	public function front_field_input( $args, $shortcode_atts ) {
		if ( ! FrmForm::show_submit( $args['form'] ) ) {
			return '';
		}

		$form_action = FrmSubmitHelper::get_current_action_from_global_var( $args['form']->id );
		$values      = FrmAppHelper::setup_edit_vars( $args['form'], 'forms' );
		$filter_args = array(
			'action' => $form_action,
			'values' => $values,
			'form'   => $args['form'],
		);

		$submit_label = apply_filters( 'frm_submit_label', $this->field['name'], $filter_args );

		ob_start();
		FrmFormsHelper::get_custom_submit( $values['submit_html'], $args['form'], $submit_label, $form_action, $values );
		return ob_get_clean();
	}
}
