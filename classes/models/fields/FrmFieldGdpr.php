<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.19
 */
class FrmFieldGdpr extends FrmFieldType {

	/**
	 * @since 6.19
	 * @var string
	 */
	protected $type = 'gdpr';

	/**
	 * @since 6.19
	 * @var bool
	 */
	protected $has_for_label = false;

	/**
	 * @since 6.19
	 * @var string
	 */
	const VIEW_PATH = '/classes/views/frm-fields/front-end/gdpr/gdpr-field.php';

	/**
	 * Get the new field defaults.
	 *
	 * @since 6.19
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
			'name'          => $this->get_new_field_name() . __( ' Consent', 'formidable' ),
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
	 * @since 6.19
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
	 * @since 6.19
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
	 * @since 6.19
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
	 * @since 6.19
	 * @return string
	 */
	protected function include_form_builder_file() {
		return FrmAppHelper::plugin_path() . self::VIEW_PATH;
	}

	/**
	 * Include the front form file.
	 *
	 * @since 6.19
	 * @return string
	 */
	protected function include_front_form_file() {
		return FrmAppHelper::plugin_path() . self::VIEW_PATH;
	}

	/**
	 * Hide the field name/label if the GDPR field is disabled.
	 *
	 * @since 6.19
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

	/**
	 * Make sure the GDPR field is required even if the required setting is disabled.
	 *
	 * @since 6.20
	 *
	 * @param array $args
	 * @return array
	 */
	public function validate( $args ) {
		$errors = parent::validate( $args );

		if ( ! $errors && ! FrmFieldGdprHelper::hide_gdpr_field() ) {
			$required = FrmField::get_option( $this->field, 'required' );

			if ( ! $required && empty( $args['value'] ) ) {
				$frm_settings                    = FrmAppHelper::get_settings();
				$errors[ 'field' . $args['id'] ] = str_replace( '[field_name]', is_object( $this->field ) ? $this->field->name : $this->field['name'], $frm_settings->blank_msg );
			}
		}

		return $errors;
	}

	/**
	 * Make sure the GDPR field is required even if the required setting is disabled.
	 *
	 * @since 6.20
	 *
	 * @param bool  $required
	 * @param array $field
	 * @return bool
	 */
	public static function force_required_field( $required, $field ) {
		if ( ! $required && 'gdpr' === $field['type'] && ! FrmFieldGdprHelper::hide_gdpr_field() ) {
			$required = true;
		}

		return $required;
	}
}
