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
		$field = array(
			'name'          => $this->get_new_field_name() . ' Agreement',
			'description'   => '',
			'type'          => $this->type,
			'options'       => '',
			'default_value' => '',
			'required'      => true,
			'field_options' => $this->get_default_field_options(),
		);

		$field_options = $this->new_field_settings();

		return array_merge( $field, $field_options );
	}

	/**
	 * Get the field settings for the field type.
	 *
	 * @since x.x
	 * @return bool[]
	 */
	protected function field_settings_for_type() {
		return array(
			'size'           => true,
			'clear_on_focus' => true,
			'invalid'        => true,
		);
	}

	/**
	 * Show the primary options for the field.
	 *
	 * @since x.x
	 * @param array $args The arguments.
	 */
	public function show_primary_options( $args ) {
		$field = $args['field'];
		include FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/gdpr/primary-options.php';
		FrmFieldsController::show_format_option( $field );

		parent::show_primary_options( $args );
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
			'gdpr_description'    => '',
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
}
