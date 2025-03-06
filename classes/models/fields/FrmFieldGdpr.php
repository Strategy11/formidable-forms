<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmFieldGdpr extends FrmFieldType {

	/**
	 * @since x.x
	 * @var string
	 */
	protected $type = 'gdpr';

	/**
	 * @since x.x
	 * @var bool
	 */
	protected $has_for_label = false;

	/**
	 * @since x.x
	 * @var string
	 */
	const VIEW_PATH = '/classes/views/frm-fields/front-end/gdpr/gdpr-field.php';

	/**
	 * Get the new field defaults.
	 *
	 * @since x.x
	 * @return array
	 */
	public function get_new_field_defaults() {
		if ( FrmFieldGdprHelper::hide_gdpr_field() ) {
			return array(
				'name'          => false,
				'description'   => false,
				'type'          => $this->type,
				'options'       => false,
				'required'      => false,
				'field_options' => false,
			);
		}

		return array(
			'name'          => $this->get_new_field_name() . ' Consent',
			'description'   => '',
			'type'          => $this->type,
			'options'       => '',
			'required'      => true,
			'field_options' => $this->get_default_field_options(),
		);
	}

	/**
	 * Get the field settings for the field type.
	 *
	 * @since x.x
	 * @return bool[]
	 */
	protected function field_settings_for_type() {
		if ( FrmFieldGdprHelper::hide_gdpr_field() ) {
			return array(
				'size'           => false,
				'clear_on_focus' => false,
				'default'        => false,
				'invalid'        => false,
				'max'            => false,
				'required'       => false,
				'label'          => false,
				'css'            => false,
				'label_position' => false,
				'description'    => false,
			);
		}

		return array(
			'size'              => true,
			'clear_on_focus'    => false,
			'default'           => false,
			'invalid'           => false,
			'max'               => false,
			'readonly_required' => true,
			'required'          => true,
		);
	}

	/**
	 * Show the primary options for the field.
	 *
	 * @since x.x
	 * @param array $args The arguments.
	 */
	public function show_primary_options( $args ) {
		if ( FrmFieldGdprHelper::hide_gdpr_field() ) {
			return;
		}
		$field = $args['field'];
		include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/gdpr/primary-options.php';
	}

	public function show_label_on_form_builder() {
		if ( FrmFieldGdprHelper::hide_gdpr_field() ) {
			return;
		}
		parent::show_label_on_form_builder();
	}

	/**
	 * Gets extra field options.
	 *
	 * @since x.x
	 * @return string[]
	 */
	protected function extra_field_opts() {
		return array(
			'gdpr_agreement_text' => __( 'I consent to having this website store my submitted information so they can respond to my inquiry.', 'formidable' ),
		);
	}

	/**
	 * Include the form builder file.
	 *
	 * @since x.x
	 * @return string
	 */
	protected function include_form_builder_file() {
		return FrmAppHelper::plugin_path() . self::VIEW_PATH;
	}

	/**
	 * Include the front form file.
	 *
	 * @since x.x
	 * @return string
	 */
	protected function include_front_form_file() {
		return FrmAppHelper::plugin_path() . self::VIEW_PATH;
	}

	/**
	 * Hide the field name/label if the GDPR field is disabled.
	 *
	 * @since x.x
	 *
	 * @param array  $args The arguments.
	 * @param string $html The HTML.
	 * @return string
	 */
	protected function before_replace_html_shortcodes( $args, $html ) {
		if ( FrmFieldGdprHelper::hide_gdpr_field() && ! current_user_can( 'frm_edit_forms' ) ) {
			return '';
		}
		return $html;
	}
}
