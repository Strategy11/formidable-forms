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
<div id="frm_field_[id]_container" class="frm_form_field form-field [required_class][error_class]">
	[input]
</div>
DEFAULT_HTML;

		return $default_html;
	}

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
	 * @param array $args - Includes 'field', 'display', and 'values'.
	 */
	public function show_primary_options( $args ) {
		$field = $args['field'];

		// Fallback for deprecated hook.
		if ( has_action( 'frm_add_form_button_options' ) ) {
			_deprecated_hook( 'frm_add_form_button_options', esc_html( FrmAppHelper::$plug_version ) );

			$values = FrmAppHelper::setup_edit_vars( FrmForm::getOne( $field['form_id'] ), 'forms' );
			echo '<table>';
			do_action( 'frm_add_form_button_options', $values );
			echo '</table>';
		}

		parent::show_primary_options( $args );
	}

	protected function extra_field_opts() {
		return array();
	}

	protected function include_form_builder_file() {
		return FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/field-submit.php';
	}

	public function show_label_on_form_builder() {
		// Do nothing.
	}

	public function front_field_input( $args, $shortcode_atts ) {
		$form        = FrmForm::getOne( $this->field['form_id'] );
		$submit      = $this->field['name'];
		$form_action = FrmSubmitHelper::get_current_action_from_global_var( $form->id );
		$values      = FrmAppHelper::setup_edit_vars( $form, 'forms' );

		ob_start();
		FrmFormsHelper::get_custom_submit( $values['submit_html'], $form, $submit, $form_action, $values );
		return ob_get_clean();
	}
}
