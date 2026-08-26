<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Registers the Formidable form abilities for the WordPress Abilities API.
 *
 * @since x.x
 */
class FrmAbilitiesFormsController {

	/**
	 * Register the form abilities.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function register_abilities() {
		self::register_list_forms_ability();
		self::register_get_form_ability();
		self::register_create_form_ability();
		self::register_update_form_ability();
		self::register_delete_form_ability();
	}

	/**
	 * Register the list forms ability.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function register_list_forms_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/list-forms',
			array(
				'label'               => __( 'List Forms', 'formidable' ),
				'description'         => __(
					'Retrieve all Formidable forms. Returns id, form_key, name, and description. Use the form_key or id for create-entry, list-fields, or get-form.',
					'formidable'
				),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'page'      => array(
							'type'        => 'integer',
							'description' => __( 'Current page of the collection.', 'formidable' ),
							'default'     => 1,
						),
						'page_size' => array(
							'type'        => 'integer',
							'description' => __( 'Maximum number of items to return per page.', 'formidable' ),
							'default'     => 20,
						),
						'order'     => array(
							'type'        => 'string',
							'description' => __( 'Order of results (asc or desc, case-insensitive).', 'formidable' ),
							'default'     => 'asc',
							'enum'        => array( 'asc', 'desc', 'ASC', 'DESC' ),
						),
						'order_by'  => array(
							'type'        => 'string',
							'description' => __( 'Field to order by.', 'formidable' ),
							'default'     => 'created_at',
						),
						'search'    => array(
							'type'        => 'string',
							'description' => __( 'Search term to filter forms.', 'formidable' ),
						),
					),
				),
				'output_schema'       => array(
					'type'                 => 'object',
					'description'          => __( 'Object of form objects keyed by form_key. Each value is a form summary.', 'formidable' ),
					'additionalProperties' => array(
						'type'       => 'object',
						'properties' => array(
							'id'          => array(
								'type'        => array( 'string', 'integer' ),
								'description' => __( 'Numeric form ID', 'formidable' ),
							),
							'form_key'    => array(
								'type'        => 'string',
								'description' => __( 'Unique alphanumeric form key used for API calls', 'formidable' ),
							),
							'name'        => array(
								'type'        => 'string',
								'description' => __( 'Form name', 'formidable' ),
							),
							'description' => array(
								'type'        => 'string',
								'description' => __( 'Form description for internal reference', 'formidable' ),
							),
							'status'      => array(
								'type'        => 'string',
								'description' => __( 'Form status, published or draft. Trashed forms are not listed.', 'formidable' ),
								'enum'        => array( 'published', 'draft' ),
							),
							'created_at'  => array(
								'type'        => 'string',
								'description' => __( 'Form creation date in MySQL format', 'formidable' ),
							),
						),
					),
				),
				'execute_callback'    => 'FrmAbilitiesFormsController::execute_list_forms',
				'permission_callback' => 'FrmAbilitiesFormsController::can_list_forms',
				'meta'                => FrmAbilitiesHelper::meta( true, false, true ),
			)
		);
	}

	/**
	 * Register the get form ability.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function register_get_form_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/get-form',
			array(
				'label'               => __( 'Get Form', 'formidable' ),
				'description'         => __( 'Retrieve a single Formidable form by ID or key.', 'formidable' ),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id'     => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Form ID or form_key.', 'formidable' ),
						),
						'return' => array(
							'type'        => 'string',
							'description' => __( 'Return format: "array" or "html".', 'formidable' ),
							'default'     => 'array',
							'enum'        => array( 'array', 'html' ),
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'Form object or rendered HTML.', 'formidable' ),
				),
				'execute_callback'    => 'FrmAbilitiesFormsController::execute_get_form',
				'permission_callback' => 'FrmAbilitiesFormsController::can_get_form',
				'meta'                => FrmAbilitiesHelper::meta( true, false, true ),
			)
		);
	}

	/**
	 * Register the create form ability.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function register_create_form_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/create-form',
			array(
				'label'               => __( 'Create Form', 'formidable' ),
				'description'         => __(
					'Create a new Formidable form with optional fields. Use list-forms to get existing forms, and list-fields to see fields on a form.',
					'formidable'
				),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => self::get_create_form_input_schema(),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __(
						'Created form object with comprehensive properties. Use the form_key or id for subsequent operations like creating entries or listing fields.',
						'formidable'
					),
					'properties'  => array(
						'id'          => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Numeric form ID', 'formidable' ),
						),
						'form_key'    => array(
							'type'        => 'string',
							'description' => __( 'Unique alphanumeric form key used for API calls', 'formidable' ),
						),
						'name'        => array(
							'type'        => 'string',
							'description' => __( 'Form name', 'formidable' ),
						),
						'description' => array(
							'type'        => 'string',
							'description' => __( 'Form description', 'formidable' ),
						),
						'status'      => array(
							'type'        => 'string',
							'description' => __( 'Form status', 'formidable' ),
						),
						'created_at'  => array(
							'type'        => 'string',
							'description' => __( 'Form creation date', 'formidable' ),
						),
					),
				),
				'execute_callback'    => 'FrmAbilitiesFormsController::execute_create_form',
				'permission_callback' => 'FrmAbilitiesFormsController::can_create_form',
				'meta'                => FrmAbilitiesHelper::meta( false, false, false ),
			)
		);
	}

	/**
	 * Build the input schema for the create form ability.
	 *
	 * Kept out of the registrar because the inline fields array makes it long
	 * enough on its own to bury everything else the registration declares.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private static function get_create_form_input_schema() {
		return array(
			'type'       => 'object',
			'required'   => array( 'name' ),
			'properties' => array(
				'name'           => array(
					'type'        => 'string',
					'description' => __( 'Form name. Required.', 'formidable' ),
					'minLength'   => 1,
				),
				'description'    => array(
					'type'        => 'string',
					'description' => __( 'Form description for internal reference.', 'formidable' ),
				),
				'form_key'       => array(
					'type'        => 'string',
					'description' => __(
						'Unique identifier, usable in shortcodes in place of the ID. Derived from the form name when omitted, with a numeric suffix if that key is taken.',
						'formidable'
					),
				),
				'status'         => array(
					'type'        => 'string',
					'description' => __( 'Form status. Default is "published".', 'formidable' ),
					'default'     => 'published',
					'enum'        => array( 'published', 'draft' ),
				),
				'logged_in'      => array(
					'type'        => 'boolean',
					'description' => __( 'Whether the form requires users to be logged in to view. Default is false.', 'formidable' ),
					'default'     => false,
				),
				'is_template'    => array(
					'type'        => 'boolean',
					'description' => __( 'Whether this form is a template. Default is false.', 'formidable' ),
					'default'     => false,
				),
				'parent_form_id' => array(
					'type'        => array( 'string', 'integer' ),
					'description' => __( 'Parent form ID if this is a child form (e.g., for repeaters). Default is 0.', 'formidable' ),
					'default'     => 0,
				),
				'editable'       => array(
					'type'        => 'boolean',
					'description' => __( 'Whether entries can be edited after submission. Default is false.', 'formidable' ),
					'default'     => false,
				),
				'options'        => array(
					'type'        => 'object',
					'description' => __(
						'Form options such as submit_value, success_action, and success_msg, applied as the form is created. Anything left out takes its default.',
						'formidable'
					),
				),
				'fields'         => array(
					'type'        => 'array',
					'description' => __(
						'Array of field objects to create in the form. Each field represents a form input like text, dropdown, checkbox, etc.',
						'formidable'
					),
					'items'       => self::get_inline_field_schema(),
				),
			),
		);
	}

	/**
	 * Build the schema for one field sent inline with a new form.
	 *
	 * The same shape create-field accepts, minus form_id, which the form
	 * being created supplies.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private static function get_inline_field_schema() {
		return array(
			'type'       => 'object',
			'properties' => array(
				'type'          => array(
					'type'        => 'string',
					'description' => __(
						'Field type. Required. Common types: text, textarea, radio, checkbox, dropdown, email, number, date, file, hidden, html, user_id.',
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
					'description' => __( 'Display order of the field. Lower numbers appear first. Defaults to auto-increment.', 'formidable' ),
				),
				'field_key'     => array(
					'type'        => 'string',
					'description' => __( 'Unique identifier for the field. Autogenerated if not provided. Used for referencing in templates.', 'formidable' ),
				),
				'options'       => array(
					'type'                 => array( 'array', 'object' ),
					'description'          => __(
						'Choices for a radio, dropdown, or checkbox field. Strings, {"label", "value"} objects, or an object keyed by option key.',
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
					'description' => __( 'Additional field options and settings, merged over the type defaults.', 'formidable' ),
				),
			),
		);
	}

	/**
	 * Register the update form ability.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function register_update_form_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/update-form',
			array(
				'label'               => __( 'Update Form', 'formidable' ),
				'description'         => __(
					'Update an existing Formidable form: name, description, status, options, or parent_form_id (for repeater child forms).',
					'formidable'
				),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id'             => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'The form ID to update. Required.', 'formidable' ),
						),
						'name'           => array(
							'type'        => 'string',
							'description' => __( 'The new form name.', 'formidable' ),
						),
						'description'    => array(
							'type'        => 'string',
							'description' => __( 'The new form description.', 'formidable' ),
						),
						'status'         => array(
							'type'        => 'string',
							'description' => __( 'The form status. Use trash to move the form to the trash.', 'formidable' ),
							'enum'        => array( 'published', 'draft', 'trash' ),
						),
						'options'        => array(
							'type'        => 'object',
							'description' => __( 'Form options to merge (submit_value, success_msg, etc.).', 'formidable' ),
						),
						'parent_form_id' => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Parent form ID for repeater child forms. Set to 0 to detach.', 'formidable' ),
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'Updated form object.', 'formidable' ),
				),
				'execute_callback'    => 'FrmAbilitiesFormsController::execute_update_form',
				'permission_callback' => 'FrmAbilitiesFormsController::can_update_form',
				'meta'                => FrmAbilitiesHelper::meta( false, false, false ),
			)
		);
	}

	/**
	 * Register the delete form ability.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	private static function register_delete_form_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/delete-form',
			array(
				'label'               => __( 'Delete Form', 'formidable' ),
				'description'         => __( 'Delete a Formidable form.', 'formidable' ),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Form ID or form_key.', 'formidable' ),
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'Deleted form object.', 'formidable' ),
				),
				'execute_callback'    => 'FrmAbilitiesFormsController::execute_delete_form',
				'permission_callback' => 'FrmAbilitiesFormsController::can_delete_form',
				'meta'                => FrmAbilitiesHelper::meta( false, true, false ),
			)
		);
	}

	/**
	 * List the forms on the site.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array
	 */
	public static function execute_list_forms( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$input = FrmAbilitiesHelper::normalize_order( $input );

		$where = array(
			'is_template' => 0,
			'status'      => array( null, '', 'published', 'draft' ),
		);

		if ( ! empty( $input['search'] ) ) {
			$where[] = array(
				'name like'        => $input['search'],
				'description like' => $input['search'],
				'or'               => 1,
			);
		}

		list( $order, $limit ) = FrmAbilitiesHelper::prepare_order_and_limit( $input );

		$forms = FrmForm::getAll( $where, $order, $limit );

		if ( is_object( $forms ) ) {
			$forms = array( $forms );
		}

		$data = array();

		foreach ( $forms as $form ) {
			// Cast the values to match the declared output schema (integer ID,
			// non-null strings). A null or empty status is a legacy value that
			// means published.
			$data[ $form->form_key ] = array(
				'id'          => (int) $form->id,
				'form_key'    => (string) $form->form_key,
				'name'        => (string) $form->name,
				'description' => (string) $form->description,
				'status'      => $form->status ? (string) $form->status : 'published',
				'created_at'  => (string) $form->created_at,
			);
		}

		return $data;
	}

	/**
	 * Get one form, as data or as rendered HTML.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_get_form( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$form = FrmAbilitiesHelper::get_form( $input['id'] );

		if ( is_wp_error( $form ) ) {
			return $form;
		}

		if ( isset( $input['return'] ) && 'html' === $input['return'] ) {
			return array(
				'renderedHtml' => FrmFormsController::get_form_shortcode( array( 'id' => $form->id ) ),
			);
		}

		return self::prepare_form_for_response( $form );
	}

	/**
	 * Create a form, and any fields sent with it.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_create_form( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$form = FrmFormsHelper::setup_new_vars( array() );

		foreach ( array( 'name', 'description', 'form_key', 'status', 'logged_in', 'is_template', 'parent_form_id', 'editable' ) as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$form[ $key ] = $input[ $key ];
			}
		}

		if ( isset( $input['options'] ) && is_array( $input['options'] ) ) {
			// FrmForm::create() reads every form option out of $values['options'],
			// but setup_new_vars() returns them flattened onto the top level, so
			// submitted options never reach the new form and each one silently
			// falls back to its default. Nest them again so they are applied at
			// creation. Options left out still pick up their defaults in
			// FrmFormsHelper::fill_form_options().
			$stored          = isset( $form['options'] ) && is_array( $form['options'] ) ? $form['options'] : array();
			$form['options'] = array_merge( $stored, self::sanitize_option_values( $input['options'] ) );
		}

		if ( empty( $input['form_key'] ) && ! empty( $input['name'] ) ) {
			// The builder derives the form key from the form name whenever the
			// name is set, in FrmFormsController::update_form_name(). It cannot
			// happen in setup_new_vars(), which runs before the form is named and
			// so falls back to a random key. Here the name arrives with the
			// request, so the readable key can be built right away. FrmForm::create()
			// still runs this through get_unique_key(), which handles length,
			// reserved words, and collision suffixes.
			$form['form_key'] = sanitize_title( $input['name'] );
		}

		$form_id = FrmForm::create( $form );

		if ( ! $form_id ) {
			return new WP_Error( 'frm_create_form', __( 'Form creation failed.', 'formidable' ), array( 'status' => 409 ) );
		}

		$new_form = FrmForm::getOne( $form_id );

		if ( ! empty( $input['fields'] ) && is_array( $input['fields'] ) ) {
			foreach ( $input['fields'] as $field ) {
				// Share create-field's pipeline so inline fields get the same
				// separate-value detection, repeater child form, and validation.
				$prepared = FrmAbilitiesFieldsController::prepare_new_field( $field, $new_form );

				if ( is_wp_error( $prepared ) ) {
					return FrmAbilitiesHelper::flatten_error( $prepared );
				}

				$prepared['form_id'] = $form_id;

				FrmField::create( $prepared );
				unset( $prepared, $field );
			}
		}

		FrmField::delete_form_transient( $form_id );
		FrmForm::clear_form_cache();

		return self::prepare_form_for_response( FrmForm::getOne( $form_id ) );
	}

	/**
	 * Update a form.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_update_form( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$form = FrmAbilitiesHelper::get_form( $input['id'] );

		if ( is_wp_error( $form ) ) {
			return $form;
		}

		$values = array();

		if ( isset( $input['name'] ) ) {
			$values['name'] = sanitize_text_field( $input['name'] );
		}

		if ( isset( $input['description'] ) ) {
			$values['description'] = sanitize_textarea_field( $input['description'] );
		}

		if ( isset( $input['status'] ) ) {
			$status = sanitize_text_field( $input['status'] );

			if ( 'publish' === $status ) {
				// Accept the WP post-status spelling, but store Formidable's.
				$status = 'published';
			}

			if ( ! in_array( $status, array( 'published', 'draft', 'trash' ), true ) ) {
				return new WP_Error( 'frm_form_invalid_status', __( 'Invalid form status. Use published, draft, or trash.', 'formidable' ), array( 'status' => 400 ) );
			}

			$values['status'] = $status;
		}

		if ( isset( $input['options'] ) && is_array( $input['options'] ) ) {
			// Merge over the stored options: FrmForm::update() rebuilds the
			// options column from the submitted array alone, so a partial update
			// would reset every omitted option to its default and zero
			// custom_style, dropping the form's assigned style.
			$values['options'] = array_merge( (array) $form->options, self::sanitize_option_values( $input['options'] ) );
		}

		if ( isset( $input['parent_form_id'] ) ) {
			$values['parent_form_id'] = absint( $input['parent_form_id'] );
		}

		if ( ! $values ) {
			return new WP_Error( 'frm_no_update_data', __( 'No data provided to update.', 'formidable' ), array( 'status' => 400 ) );
		}

		$values['id'] = $form->id;
		$result       = FrmForm::update( $form->id, $values );

		if ( ! $result ) {
			return new WP_Error( 'frm_form_update_failed', __( 'Form update failed.', 'formidable' ), array( 'status' => 500 ) );
		}

		return self::prepare_form_for_response( FrmForm::getOne( $form->id ) );
	}

	/**
	 * Delete a form.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_delete_form( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$form = FrmAbilitiesHelper::get_form( $input['id'] );

		if ( is_wp_error( $form ) ) {
			return $form;
		}

		// Read the form before it is gone, so the caller gets back what it deleted.

		if ( ! FrmForm::destroy( $form->id ) ) {
			return new WP_Error( 'frm_form_delete_failed', __( 'Form deletion failed.', 'formidable' ), array( 'status' => 500 ) );
		}

		return self::prepare_form_for_response( $form );
	}

	/**
	 * Build the response shape for one form.
	 *
	 * @since x.x
	 *
	 * @param stdClass $form The form to describe.
	 *
	 * @return array
	 */
	public static function prepare_form_for_response( $form ) {
		return array(
			'id'             => (int) $form->id,
			'form_key'       => (string) $form->form_key,
			'name'           => (string) $form->name,
			'description'    => (string) $form->description,
			'status'         => $form->status ? (string) $form->status : 'published',
			'parent_form_id' => (int) $form->parent_form_id,
			'logged_in'      => (int) $form->logged_in,
			'is_template'    => (int) $form->is_template,
			'options'        => (array) $form->options,
			'editable'       => (int) $form->editable,
			'created_at'     => (string) $form->created_at,
			'link'           => FrmFormsHelper::get_direct_link( $form->form_key, $form ),
		);
	}

	/**
	 * Recursively sanitize option values, following core Formidable semantics.
	 *
	 * Strings are left unfiltered when FrmAppHelper::allow_unfiltered_html()
	 * allows it (user capability plus DISALLOW_UNFILTERED_HTML and the
	 * frm_disallow_unfiltered_html filter), and go through Formidable's kses
	 * otherwise. Other scalars pass through, and anything that is not scalar
	 * drops to an empty string.
	 *
	 * @since x.x
	 *
	 * @param array $values Option values to sanitize.
	 *
	 * @return array
	 */
	private static function sanitize_option_values( $values ) {
		$allow_unfiltered = FrmAppHelper::allow_unfiltered_html();

		foreach ( $values as $key => $value ) {
			if ( is_array( $value ) ) {
				$values[ $key ] = self::sanitize_option_values( $value );
			} elseif ( is_string( $value ) ) {
				$values[ $key ] = $allow_unfiltered ? $value : FrmAppHelper::kses( $value, 'all' );
			} elseif ( ! is_scalar( $value ) ) {
				$values[ $key ] = '';
			}
		}

		return $values;
	}

	/**
	 * Permission callback for list forms.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_list_forms( $input ) {
		return current_user_can( 'frm_view_forms' ) || current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for get form.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_get_form( $input ) {
		return current_user_can( 'frm_view_forms' ) || current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for create form.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_create_form( $input ) {
		return current_user_can( 'frm_edit_forms' ) || current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for update form.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_update_form( $input ) {
		return current_user_can( 'frm_edit_forms' ) || current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for delete form.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_delete_form( $input ) {
		return current_user_can( 'frm_delete_forms' ) || current_user_can( 'administrator' );
	}
}
