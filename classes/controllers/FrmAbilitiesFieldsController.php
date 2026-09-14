<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Registers the Formidable field abilities for the WordPress Abilities API.
 *
 * @since x.x
 */
class FrmAbilitiesFieldsController {

	/**
	 * Register the field abilities.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function register_abilities() {
		self::register_list_fields_ability();
		self::register_create_field_ability();
		self::register_update_field_ability();
		self::register_delete_field_ability();
	}

	/**
	 * Register the list fields ability.
	 *
	 * @return void
	 */
	private static function register_list_fields_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/list-fields',
			array(
				'label'               => __( 'List Fields', 'formidable' ),
				'description'         => __(
					'Retrieve all fields for a specific form. Returns field id, field_key, name, type, and options. Use the field_key when creating entries to map field values.',
					'formidable'
				),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'form_id' ),
					'properties' => array(
						'form_id' => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Form ID or form_key to get fields for. Required. Use list-forms to find available forms.', 'formidable' ),
						),
					),
				),
				'output_schema'       => array(
					'type'                 => 'object',
					'description'          => __( 'Array of field objects keyed by field_key. Each field includes comprehensive field properties.', 'formidable' ),
					'additionalProperties' => array(
						'type'       => 'object',
						'properties' => array(
							'id'            => array(
								'type'        => array( 'string', 'integer' ),
								'description' => __( 'Numeric field ID', 'formidable' ),
							),
							'field_key'     => array(
								'type'        => 'string',
								'description' => __( 'Unique field key used for entry submission', 'formidable' ),
							),
							'name'          => array(
								'type'        => 'string',
								'description' => __( 'Field label displayed to users', 'formidable' ),
							),
							'description'   => array(
								'type'        => 'string',
								'description' => __( 'Field description or help text shown below the field', 'formidable' ),
							),
							'type'          => array(
								'type'        => 'string',
								'description' => __( 'Field type (text, textarea, radio, checkbox, dropdown, email, number, date, file, etc.)', 'formidable' ),
							),
							'default_value' => array(
								'type'        => 'string',
								'description' => __( 'Default value for the field', 'formidable' ),
							),
							'options'       => array(
								'type'        => 'array',
								'description' => __( 'Array of choice options for radio, dropdown, or checkbox fields', 'formidable' ),
								'items'       => array( 'type' => 'string' ),
							),
							'field_order'   => array(
								'type'        => 'integer',
								'description' => __( 'Display order of the field (lower numbers appear first)', 'formidable' ),
							),
							'required'      => array(
								'type'        => 'boolean',
								'description' => __( 'Whether the field must be filled before form submission', 'formidable' ),
							),
							'field_options' => array(
								'type'        => 'object',
								'description' => __( 'Additional field options and settings', 'formidable' ),
							),
							'form_id'       => array(
								'type'        => array( 'string', 'integer' ),
								'description' => __( 'ID of the form this field belongs to', 'formidable' ),
							),
							'created_at'    => array(
								'type'        => 'string',
								'description' => __( 'Field creation date in MySQL format', 'formidable' ),
							),
						),
					),
				),
				'execute_callback'    => 'FrmAbilitiesFieldsController::execute_list_fields',
				'permission_callback' => 'FrmAbilitiesFieldsController::can_list_fields',
				'meta'                => FrmAbilitiesHelper::meta( true, false, true ),
			)
		);
	}

	/**
	 * Register the create field ability.
	 *
	 * @return void
	 */
	private static function register_create_field_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/create-field',
			array(
				'label'               => __( 'Create Field', 'formidable' ),
				'description'         => __(
					'Add a new field to an existing Formidable form. Use list-forms to find the form, and list-fields to see existing fields.',
					'formidable'
				),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'form_id', 'type' ),
					'properties' => array(
						'form_id'       => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Form ID or form_key to add the field to. Required.', 'formidable' ),
						),
						'type'          => array(
							'type'        => 'string',
							'description' => __(
								'Field type. Required. Common types: text, textarea, radio, checkbox, dropdown, email, number, date, file, hidden, html, user_id, captcha.',
								'formidable'
							),
							'enum'        => FrmAbilitiesHelper::get_creatable_field_types(),
						),
						'name'          => array(
							'type'        => 'string',
							'description' => __( 'Field label. Defaults to field type name.', 'formidable' ),
						),
						'description'   => array(
							'type'        => 'string',
							'description' => __( 'Optional field description.', 'formidable' ),
						),
						'required'      => array(
							'type'        => 'boolean',
							'description' => __( 'Whether the field must be filled before form submission. Defaults to false.', 'formidable' ),
							'default'     => false,
						),
						'field_order'   => array(
							'type'        => 'integer',
							'description' => __( 'Display order of the field. Lower numbers appear first.', 'formidable' ),
						),
						'field_key'     => array(
							'type'        => 'string',
							'description' => __( 'Unique identifier for the field. Autogenerated if not provided.', 'formidable' ),
						),
						'options'       => array(
							'type'                 => array( 'array', 'object' ),
							'description'          => __(
								'Choices for radio, dropdown, or checkbox fields. An array of strings, an array of {"label", "value"} objects, or an object keyed by option key.',
								'formidable'
							),
							'items'                => array(
								'type' => array( 'string', 'object' ),
							),
							'additionalProperties' => array(
								'type' => array( 'string', 'object' ),
							),
						),
						'default_value' => array(
							'type'        => 'string',
							'description' => __( 'Default value for the field.', 'formidable' ),
						),
						'placeholder'   => array(
							'type'        => 'string',
							'description' => __( 'Placeholder text shown in empty fields.', 'formidable' ),
						),
						'field_options' => array(
							'type'        => 'object',
							'description' => __(
								'Field options, merged over the type defaults: format, minnum, maxnum, step, classes, conditional logic, calculations, and the rest.',
								'formidable'
							),
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'The newly created field object, including its id and field_key.', 'formidable' ),
				),
				'execute_callback'    => 'FrmAbilitiesFieldsController::execute_create_field',
				'permission_callback' => 'FrmAbilitiesFieldsController::can_create_field',
				'meta'                => FrmAbilitiesHelper::meta( false, false, false ),
			)
		);
	}

	/**
	 * Register the update field ability.
	 *
	 * @return void
	 */
	private static function register_update_field_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/update-field',
			array(
				'label'               => __( 'Update Field', 'formidable' ),
				'description'         => __( 'Update an existing field on a Formidable form. Use list-fields to find the field id.', 'formidable' ),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'form_id'       => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Form ID or form_key the field belongs to. Optional — derived from the field when omitted.', 'formidable' ),
						),
						'id'            => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Field ID to update. Required. Use list-fields to find it.', 'formidable' ),
						),
						'type'          => array(
							'type'        => 'string',
							'description' => __( 'Change the field to this type. The new type default options are applied.', 'formidable' ),
							'enum'        => FrmAbilitiesHelper::get_creatable_field_types(),
						),
						'name'          => array(
							'type'        => 'string',
							'description' => __( 'Field label.', 'formidable' ),
						),
						'description'   => array(
							'type'        => 'string',
							'description' => __( 'Field description.', 'formidable' ),
						),
						'required'      => array(
							'type'        => 'boolean',
							'description' => __( 'Whether the field must be filled before form submission.', 'formidable' ),
						),
						'field_order'   => array(
							'type'        => 'integer',
							'description' => __( 'Display order of the field. Lower numbers appear first.', 'formidable' ),
						),
						'options'       => array(
							'type'        => array( 'array', 'object' ),
							'description' => __(
								'Choices for radio, dropdown, or checkbox fields. An array, or an object keyed by option key.',
								'formidable'
							),
						),
						'field_options' => array(
							'type'        => 'object',
							'description' => __(
								'Field options, merged into the ones already stored. Send only the keys to change. A key sent empty is cleared.',
								'formidable'
							),
						),
						'default_value' => array(
							'type'        => 'string',
							'description' => __( 'Default value for the field.', 'formidable' ),
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'Update result including the updated field_id.', 'formidable' ),
				),
				'execute_callback'    => 'FrmAbilitiesFieldsController::execute_update_field',
				'permission_callback' => 'FrmAbilitiesFieldsController::can_update_field',
				'meta'                => FrmAbilitiesHelper::meta( false, false, false ),
			)
		);
	}

	/**
	 * Register the delete field ability.
	 *
	 * @return void
	 */
	private static function register_delete_field_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/delete-field',
			array(
				'label'               => __( 'Delete Field', 'formidable' ),
				'description'         => __( 'Delete a field from a Formidable form. Use list-fields to find the field id.', 'formidable' ),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'form_id' => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Form ID or form_key the field belongs to. Optional, derived from the field when omitted.', 'formidable' ),
						),
						'id'      => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Field ID to delete. Required. Use list-fields to find it.', 'formidable' ),
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'Deleted field object.', 'formidable' ),
				),
				'execute_callback'    => 'FrmAbilitiesFieldsController::execute_delete_field',
				'permission_callback' => 'FrmAbilitiesFieldsController::can_delete_field',
				'meta'                => FrmAbilitiesHelper::meta( false, true, false ),
			)
		);
	}

	/**
	 * List the fields on a form.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_list_fields( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$form = FrmAbilitiesHelper::get_form( $input['form_id'] );

		if ( is_wp_error( $form ) ) {
			return $form;
		}

		$fields = FrmField::get_all_for_form( $form->id, '', 'include' );
		$data   = array();

		foreach ( $fields as $field ) {
			$data[ $field->field_key ] = self::prepare_field_for_response( $field );
		}

		return $data;
	}

	/**
	 * Create one field on a form.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_create_field( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$form = FrmAbilitiesHelper::get_form( $input['form_id'] );

		if ( is_wp_error( $form ) ) {
			return $form;
		}

		$field = $input;
		unset( $field['form_id'] );

		$prepared = self::prepare_new_field( $field, $form );

		if ( is_wp_error( $prepared ) ) {
			return FrmAbilitiesHelper::flatten_error( $prepared );
		}

		$prepared['form_id'] = (int) $form->id;
		$field_id            = FrmField::create( $prepared );

		if ( ! $field_id ) {
			return new WP_Error( 'frm_create_field', __( 'Field creation failed.', 'formidable' ), array( 'status' => 409 ) );
		}

		FrmField::delete_form_transient( $form->id );
		FrmForm::clear_form_cache();

		return self::prepare_field_for_response( FrmField::getOne( $field_id ) );
	}

	/**
	 * Update one field.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_update_field( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$field = FrmField::getOne( $input['id'] );

		if ( ! $field ) {
			return self::get_invalid_field_error();
		}

		$field_data = self::normalize_field_type( $input );
		unset( $field_data['id'], $field_data['form_id'] );

		if ( isset( $field_data['placeholder'] ) && is_scalar( $field_data['placeholder'] ) ) {
			// Formidable stores the placeholder inside field_options. FrmField::update()
			// only writes real columns, so a top level placeholder is silently dropped.

			$field_data['field_options']                = isset( $field_data['field_options'] ) && is_array( $field_data['field_options'] )
			? $field_data['field_options']
			: array();
			$field_data['field_options']['placeholder'] = $field_data['placeholder'];
			unset( $field_data['placeholder'] );
		}

		$field_data = self::merge_field_options_with_existing( $field, $field_data );

		if ( ! $field_data ) {
			return new WP_Error( 'frm_no_update_data', __( 'No data provided to update.', 'formidable' ), array( 'status' => 400 ) );
		}

		$new_type      = ! empty( $field_data['type'] ) ? $field_data['type'] : $field->type;
		$field_options = isset( $field_data['field_options'] ) && is_array( $field_data['field_options'] ) ? $field_data['field_options'] : (array) $field->field_options;
		$checked       = self::check_form_select( $new_type, $field_options );

		if ( is_wp_error( $checked ) ) {
			return $checked;
		}

		if ( ! FrmField::update( $field->id, $field_data ) ) {
			return new WP_Error( 'frm_field_update_failed', __( 'Field update failed.', 'formidable' ), array( 'status' => 500 ) );
		}

		FrmField::delete_form_transient( $field->form_id );
		FrmForm::clear_form_cache();

		return self::prepare_field_for_response( FrmField::getOne( $field->id ) );
	}

	/**
	 * Delete one field.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_delete_field( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$field = FrmField::getOne( $input['id'] );

		if ( ! $field ) {
			return self::get_invalid_field_error();
		}

		// form_id is optional, and is only a guard against deleting a field that
		// belongs to a different form than the caller believes it does.
		if ( ! empty( $input['form_id'] ) ) {
			$form = FrmAbilitiesHelper::get_form( $input['form_id'] );

			if ( is_wp_error( $form ) ) {
				return $form;
			}

			if ( ! self::field_belongs_to_form( $field, $form->id ) ) {
				return new WP_Error(
					'frm_field_wrong_form',
					__( 'That field does not belong to that form.', 'formidable' ),
					array( 'status' => 404 )
				);
			}
		}

		// Read the field before it is gone, so the caller gets back what it deleted.
		$data    = self::prepare_field_for_response( $field );
		$form_id = $field->form_id;

		if ( FrmField::is_repeating_field( $field ) && ! empty( $field->field_options['form_select'] ) ) {
			self::destroy_fields_in_repeater( $field );
		}

		if ( ! FrmField::destroy( $field->id ) ) {
			return self::get_invalid_field_error();
		}

		FrmField::delete_form_transient( $form_id );
		FrmForm::clear_form_cache();

		return $data;
	}

	/**
	 * Build the field data for a new field, ready for FrmField::create().
	 *
	 * Public because create-form creates its inline fields through here too, so
	 * a field created alongside its form goes through the same separate value
	 * detection, repeater child form creation, and validation as one created on
	 * its own.
	 *
	 * @since x.x
	 *
	 * @param array    $field Raw field input.
	 * @param stdClass $form  The form the field is created on.
	 *
	 * @return array|WP_Error
	 */
	public static function prepare_new_field( $field, $form ) {
		$form_id = (int) $form->id;
		$field   = self::normalize_field_type( $field );

		if ( empty( $field['type'] ) ) {
			return new WP_Error( 'frm_field_no_type', __( 'A field type is required.', 'formidable' ), array( 'status' => 400 ) );
		}

		$prepared            = self::apply_field_input( FrmFieldsHelper::setup_new_vars( $field['type'], $form_id ), $field );
		$prepared['form_id'] = $form_id;

		// The builder merges the raw input before this filter fires. Pro's
		// FrmProField::create() relies on that to see repeat and create the
		// repeater's child form. Input is applied again after the filter so
		// explicit input still wins over filter defaults.
		$prepared            = apply_filters( 'frm_before_field_created', $prepared );
		$prepared            = self::apply_field_input( $prepared, $field );
		$prepared['form_id'] = $form_id;

		$prepared = self::maybe_create_repeat_form( $prepared, $form_id );
		$prepared = self::maybe_position_in_repeater( $prepared, $field, $form );
		$prepared = self::maybe_enable_separate_values( $prepared, $field );

		if ( empty( $field['field_key'] ) && ! empty( $field['name'] ) ) {
			// Same reasoning as the form key: setup_new_vars() seeds a random key
			// because the builder inserts a field before it is named, while an
			// ability sends the name with the field. FrmField::create() runs this
			// through get_unique_key() for length and uniqueness.
			$prepared['field_key'] = sanitize_title( $field['name'] );
		}

		$checked = self::check_form_select( $prepared['type'], (array) $prepared['field_options'] );

		return is_wp_error( $checked ) ? $checked : $prepared;
	}

	/**
	 * Map the field type aliases the schemas accept to the canonical type names.
	 *
	 * Without this, an alias like "dropdown" is stored verbatim and the field
	 * renders no input on the frontend.
	 *
	 * @since x.x
	 *
	 * @param array $field Raw field input, possibly containing a type key.
	 *
	 * @return array
	 */
	public static function normalize_field_type( $field ) {
		if ( empty( $field['type'] ) ) {
			return $field;
		}

		$aliases = array(
			'dropdown'    => 'select',
			'star_rating' => 'star',
			'section'     => 'divider',
		);

		if ( isset( $aliases[ $field['type'] ] ) ) {
			$field['type'] = $aliases[ $field['type'] ];
		}

		return $field;
	}

	/**
	 * Apply raw field input onto a prepared new field array.
	 *
	 * The field_options input is merged over the type's seeded defaults instead
	 * of replacing them, so a partial options object cannot wipe the default
	 * validation messages and settings. The top level placeholder param is
	 * mapped into field_options, where Formidable stores it, because
	 * FrmField::create() only writes real columns and would drop it.
	 *
	 * @since x.x
	 *
	 * @param array $prepared Prepared field data from FrmFieldsHelper::setup_new_vars().
	 * @param array $field    Raw field input.
	 *
	 * @return array
	 */
	public static function apply_field_input( $prepared, $field ) {
		foreach ( $field as $option => $value ) {
			if ( 'field_options' === $option && is_array( $value ) ) {
				$prepared['field_options'] = array_merge( (array) $prepared['field_options'], $value );
				continue;
			}

			$prepared[ $option ] = $value;
		}

		if ( isset( $field['placeholder'] ) && is_scalar( $field['placeholder'] ) ) {
			$prepared['field_options']['placeholder'] = $field['placeholder'];
			unset( $prepared['placeholder'] );
		}

		return $prepared;
	}

	/**
	 * Create the child form for a new repeater when Pro is available.
	 *
	 * The builder gets this through the frm_before_field_created filter, but Pro
	 * registers that callback in load_admin_hooks() only, so REST and MCP
	 * requests never run it. Call the Pro model directly instead of relying on
	 * the hook context.
	 *
	 * @since x.x
	 *
	 * @param array $prepared Prepared field data.
	 * @param int   $form_id  Parent form ID.
	 *
	 * @return array
	 */
	private static function maybe_create_repeat_form( $prepared, $form_id ) {
		$is_new_repeater = 'divider' === $prepared['type'] && ! empty( $prepared['field_options']['repeat'] ) && empty( $prepared['field_options']['form_select'] );

		if ( $is_new_repeater && is_callable( 'FrmProField::create_repeat_form' ) ) {
			$prepared['field_options']['form_select'] = FrmProField::create_repeat_form(
				0,
				array(
					'parent_form_id' => $form_id,
					'field_name'     => $prepared['name'],
				)
			);
		}

		return $prepared;
	}

	/**
	 * Slot a new repeater child field after the last field of its section.
	 *
	 * Repeater child fields sort into the parent form's field list purely by
	 * field_order, so an order computed against the child form alone can land
	 * before the repeater's own divider and render outside the section in the
	 * builder. When the caller gives no explicit order, the field goes right
	 * after the last field of its section and the fields that follow shift down.
	 *
	 * @since x.x
	 *
	 * @param array    $prepared Prepared field data.
	 * @param array    $field    Raw field input.
	 * @param stdClass $form     The form the field is created on.
	 *
	 * @return array
	 */
	private static function maybe_position_in_repeater( $prepared, $field, $form ) {
		if ( isset( $field['field_order'] ) || empty( $form->parent_form_id ) ) {
			return $prepared;
		}

		$parent_fields = FrmField::get_all_for_form( $form->parent_form_id, '', 'include' );
		$section_order = 0;

		foreach ( $parent_fields as $parent_field ) {
			$form_select         = isset( $parent_field->field_options['form_select'] ) ? (int) $parent_field->field_options['form_select'] : 0;
			$starts_this_section = 'divider' === $parent_field->type && $form_select === (int) $form->id;
			$in_this_section     = (int) $parent_field->form_id === (int) $form->id;

			if ( $starts_this_section || $in_this_section ) {
				$section_order = max( $section_order, (int) $parent_field->field_order );
			}
		}

		if ( ! $section_order ) {
			return $prepared;
		}

		$prepared['field_order'] = $section_order + 1;

		foreach ( $parent_fields as $parent_field ) {
			if ( (int) $parent_field->field_order > $section_order ) {
				FrmField::update( $parent_field->id, array( 'field_order' => (int) $parent_field->field_order + 1 ) );
			}
		}

		return $prepared;
	}

	/**
	 * Reject a repeater or embedded form field with no usable child form.
	 *
	 * Both render their rows from the child form referenced by
	 * field_options.form_select. Without a valid form there, the field is stored
	 * but cannot add or remove rows, so the write is rejected instead.
	 *
	 * @since x.x
	 *
	 * @param string $type          Field type being written.
	 * @param array  $field_options Complete field_options for the field.
	 *
	 * @return true|WP_Error
	 */
	private static function check_form_select( $type, $field_options ) {
		$needs_child_form = 'form' === $type || ( 'divider' === $type && ! empty( $field_options['repeat'] ) );

		if ( ! $needs_child_form ) {
			return true;
		}

		$form_select = isset( $field_options['form_select'] ) ? (int) $field_options['form_select'] : 0;

		if ( $form_select > 0 && FrmForm::getOne( $form_select ) ) {
			return true;
		}

		return new WP_Error(
			'frm_field_missing_form_select',
			__(
				'Repeater and embedded form fields need field_options.form_select set to an existing child form ID. Formidable Pro creates that child form automatically.',
				'formidable'
			),
			array( 'status' => 400 )
		);
	}

	/**
	 * Turn on separate values when the options imply them.
	 *
	 * Options passed as label and value objects with differing values imply
	 * separate values. Without the flag, entry validation compares submissions
	 * against the labels and rejects the values.
	 *
	 * @since x.x
	 *
	 * @param array $prepared Prepared field data.
	 * @param array $field    Raw field input.
	 *
	 * @return array
	 */
	private static function maybe_enable_separate_values( $prepared, $field ) {
		$explicitly_set = isset( $field['separate_value'] ) || isset( $field['field_options']['separate_value'] );

		if ( empty( $prepared['options'] ) || ! is_array( $prepared['options'] ) || $explicitly_set ) {
			return $prepared;
		}

		foreach ( $prepared['options'] as $option ) {
			$option = (array) $option;

			if ( isset( $option['label'], $option['value'] ) && $option['label'] !== $option['value'] ) {
				$prepared['field_options']['separate_value'] = 1;
				break;
			}
		}

		return $prepared;
	}

	/**
	 * Merge a partial field_options update over the field's stored options.
	 *
	 * FrmField::update() serializes exactly what it is given, so without this
	 * merge a partial field_options object silently wipes every other stored
	 * setting (conditional logic, calculations, validation messages, and the
	 * rest). A key sent explicitly set to an empty value still clears it.
	 *
	 * @since x.x
	 *
	 * @param stdClass $field      The stored field object.
	 * @param array    $field_data Field data to update, possibly with a partial field_options array.
	 *
	 * @return array
	 */
	private static function merge_field_options_with_existing( $field, $field_data ) {
		if ( isset( $field_data['field_options'] ) && is_array( $field_data['field_options'] ) ) {
			$field_data['field_options'] = array_merge( (array) $field->field_options, $field_data['field_options'] );
		}

		return $field_data;
	}

	/**
	 * Check whether a field is on a form, directly or inside one of its repeaters.
	 *
	 * @since x.x
	 *
	 * @param stdClass   $field   The field object.
	 * @param int|string $form_id The form ID.
	 *
	 * @return bool
	 */
	private static function field_belongs_to_form( $field, $form_id ) {
		if ( (int) $field->form_id === (int) $form_id ) {
			return true;
		}

		$field_form = FrmForm::getOne( $field->form_id );

		return $field_form && (int) $field_form->parent_form_id === (int) $form_id;
	}

	/**
	 * Delete the fields inside a repeater's child form.
	 *
	 * @since x.x
	 *
	 * @param stdClass $field Repeater field.
	 *
	 * @return void
	 */
	private static function destroy_fields_in_repeater( $field ) {
		$repeater_form = FrmForm::getOne( $field->field_options['form_select'] );

		if ( ! $repeater_form ) {
			return;
		}

		$field_ids = FrmDb::get_col( 'frm_fields', array( 'form_id' => $repeater_form->id ) );

		foreach ( $field_ids as $field_id ) {
			FrmField::destroy( $field_id );
		}
	}

	/**
	 * Build the response shape for one field.
	 *
	 * @since x.x
	 *
	 * @param stdClass $field The field to describe.
	 *
	 * @return array
	 */
	public static function prepare_field_for_response( $field ) {
		return array(
			'id'            => (int) $field->id,
			'field_key'     => (string) $field->field_key,
			'name'          => (string) $field->name,
			'description'   => (string) $field->description,
			'type'          => (string) $field->type,
			'default_value' => $field->default_value,
			'options'       => $field->options,
			'field_order'   => (int) $field->field_order,
			'required'      => (int) $field->required,
			'field_options' => $field->field_options,
			'form_id'       => (int) $field->form_id,
			'created_at'    => (string) $field->created_at,
		);
	}

	/**
	 * Build the error returned when no field matches the given ID.
	 *
	 * @since x.x
	 *
	 * @return WP_Error
	 */
	private static function get_invalid_field_error() {
		return new WP_Error( 'frm_field_invalid_id', __( 'Invalid field ID.', 'formidable' ), array( 'status' => 404 ) );
	}

	/**
	 * Permission callback for list fields.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_list_fields( $input ) {
		return current_user_can( 'frm_view_forms' ) || current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for create field.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_create_field( $input ) {
		return current_user_can( 'frm_edit_forms' ) || current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for update field.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_update_field( $input ) {
		return current_user_can( 'frm_edit_forms' ) || current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for delete field.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_delete_field( $input ) {
		return current_user_can( 'frm_delete_forms' ) || current_user_can( 'administrator' );
	}
}
