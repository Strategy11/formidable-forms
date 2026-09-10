<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Registers the Formidable form action abilities for the WordPress Abilities API.
 *
 * @since x.x
 */
class FrmAbilitiesFormActionsController {

	/**
	 * Register the form action abilities.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function register_abilities() {
		self::register_list_form_actions_ability();
		self::register_get_form_action_ability();
		self::register_create_form_action_ability();
		self::register_update_form_action_ability();
		self::register_delete_form_action_ability();
	}

	/**
	 * Register the list form actions ability.
	 *
	 * @return void
	 */
	private static function register_list_form_actions_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/list-form-actions',
			array(
				'label'               => __( 'List Form Actions', 'formidable' ),
				'description'         => __(
					'Retrieve the actions on a form. Returns id, type, form_id, post_title, post_status, and post_content. Use list-forms for the form_id.',
					'formidable'
				),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'form_id' ),
					'properties' => array(
						'form_id'     => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Form ID to get actions for. Required.', 'formidable' ),
						),
						'type'        => array(
							'type'        => 'string',
							'description' => __( 'Filter by action type (e.g., email, quiz, quiz_outcome, api, wppost, register, etc.). Optional.', 'formidable' ),
						),
						'post_status' => array(
							'type'        => 'string',
							'description' => __( 'Filter by post status. By default both publish and draft actions are listed.', 'formidable' ),
							'enum'        => array( 'publish', 'draft' ),
						),
					),
				),
				'output_schema'       => array(
					'type'                 => 'object',
					'description'          => __(
						'Array of form action objects keyed by action ID. Each action includes id, type, form_id, post_title, post_status, post_content, and dates.',
						'formidable'
					),
					'additionalProperties' => array(
						'type'       => 'object',
						'properties' => array(
							'id'           => array(
								'type'        => array( 'string', 'integer' ),
								'description' => __( 'Numeric action ID', 'formidable' ),
							),
							'type'         => array(
								'type'        => 'string',
								'description' => __( 'Action type (e.g., email, quiz, quiz_outcome, api, wppost, register)', 'formidable' ),
							),
							'form_id'      => array(
								'type'        => array( 'string', 'integer' ),
								'description' => __( 'ID of the form this action belongs to', 'formidable' ),
							),
							'post_title'   => array(
								'type'        => 'string',
								'description' => __( 'Action title', 'formidable' ),
							),
							'post_status'  => array(
								'type'        => 'string',
								'description' => __( 'Action status (publish or draft)', 'formidable' ),
							),
							'post_content' => array(
								'type'        => 'object',
								'description' => __( 'Action settings (JSON object)', 'formidable' ),
							),
							'created_at'   => array(
								'type'        => 'string',
								'description' => __( 'Action creation date in MySQL format', 'formidable' ),
							),
							'modified_at'  => array(
								'type'        => 'string',
								'description' => __( 'Action last modified date in MySQL format', 'formidable' ),
							),
						),
					),
				),
				'execute_callback'    => 'FrmAbilitiesFormActionsController::execute_list_form_actions',
				'permission_callback' => 'FrmAbilitiesFormActionsController::can_list_form_actions',
				'meta'                => FrmAbilitiesHelper::meta( true, false, true ),
			)
		);
	}

	/**
	 * Register the get form action ability.
	 *
	 * @return void
	 */
	private static function register_get_form_action_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/get-form-action',
			array(
				'label'               => __( 'Get Form Action', 'formidable' ),
				'description'         => __( 'Retrieve a single form action by ID.', 'formidable' ),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Form action ID. Required.', 'formidable' ),
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'Form action object with comprehensive properties.', 'formidable' ),
					'properties'  => array(
						'id'           => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Numeric action ID', 'formidable' ),
						),
						'type'         => array(
							'type'        => 'string',
							'description' => __( 'Action type', 'formidable' ),
						),
						'form_id'      => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'ID of the form this action belongs to', 'formidable' ),
						),
						'post_title'   => array(
							'type'        => 'string',
							'description' => __( 'Action title', 'formidable' ),
						),
						'post_status'  => array(
							'type'        => 'string',
							'description' => __( 'Action status', 'formidable' ),
						),
						'post_content' => array(
							'type'        => 'object',
							'description' => __( 'Action settings', 'formidable' ),
						),
						'created_at'   => array(
							'type'        => 'string',
							'description' => __( 'Action creation date', 'formidable' ),
						),
						'modified_at'  => array(
							'type'        => 'string',
							'description' => __( 'Action last modified date', 'formidable' ),
						),
					),
				),
				'execute_callback'    => 'FrmAbilitiesFormActionsController::execute_get_form_action',
				'permission_callback' => 'FrmAbilitiesFormActionsController::can_get_form_action',
				'meta'                => FrmAbilitiesHelper::meta( true, false, true ),
			)
		);
	}

	/**
	 * Register the create form action ability.
	 *
	 * @return void
	 */
	private static function register_create_form_action_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/create-form-action',
			array(
				'label'               => __( 'Create Form Action', 'formidable' ),
				'description'         => __(
					'Create an action on a form. Use list-forms for the form_id. Types include email, quiz, api, wppost, register, payment, and the marketing integrations.',
					'formidable'
				),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'form_id', 'type' ),
					'properties' => array(
						'form_id'      => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Form ID to add the action to. Required.', 'formidable' ),
						),
						'type'         => array(
							'type'        => 'string',
							'description' => __(
								'Action type. Required. Common types: email, quiz, quiz_outcome, api, wppost, register, payment, stripe, paypal, mailchimp, zapier, n8n.',
								'formidable'
							),
						),
						'post_title'   => array(
							'type'        => 'string',
							'description' => __( 'Action title. Optional, defaults to action type name.', 'formidable' ),
						),
						'post_status'  => array(
							'type'        => 'string',
							'description' => __( 'Action status. Default is publish.', 'formidable' ),
							'default'     => 'publish',
							'enum'        => array( 'publish', 'draft' ),
						),
						'post_content' => array(
							'type'                 => 'object',
							'description'          => __( 'Action settings (JSON object). Optional, varies by action type.', 'formidable' ),
							'additionalProperties' => true,
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'Created form action object.', 'formidable' ),
					'properties'  => array(
						'id'           => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Numeric action ID', 'formidable' ),
						),
						'type'         => array(
							'type'        => 'string',
							'description' => __( 'Action type', 'formidable' ),
						),
						'form_id'      => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'ID of the form this action belongs to', 'formidable' ),
						),
						'post_title'   => array(
							'type'        => 'string',
							'description' => __( 'Action title', 'formidable' ),
						),
						'post_status'  => array(
							'type'        => 'string',
							'description' => __( 'Action status', 'formidable' ),
						),
						'post_content' => array(
							'type'        => 'object',
							'description' => __( 'Action settings', 'formidable' ),
						),
						'created_at'   => array(
							'type'        => 'string',
							'description' => __( 'Action creation date', 'formidable' ),
						),
						'modified_at'  => array(
							'type'        => 'string',
							'description' => __( 'Action last modified date', 'formidable' ),
						),
					),
				),
				'execute_callback'    => 'FrmAbilitiesFormActionsController::execute_create_form_action',
				'permission_callback' => 'FrmAbilitiesFormActionsController::can_create_form_action',
				'meta'                => FrmAbilitiesHelper::meta( false, false, false ),
			)
		);
	}

	/**
	 * Register the update form action ability.
	 *
	 * @return void
	 */
	private static function register_update_form_action_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/update-form-action',
			array(
				'label'               => __( 'Update Form Action', 'formidable' ),
				'description'         => __( 'Update an existing form action.', 'formidable' ),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id'           => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Form action ID. Required.', 'formidable' ),
						),
						'post_title'   => array(
							'type'        => 'string',
							'description' => __( 'Action title.', 'formidable' ),
						),
						'post_status'  => array(
							'type'        => 'string',
							'description' => __( 'Action status.', 'formidable' ),
							'enum'        => array( 'publish', 'draft' ),
						),
						'post_content' => array(
							'type'                 => 'object',
							'description'          => __( 'Action settings (JSON object). Merges with existing settings.', 'formidable' ),
							'additionalProperties' => true,
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'Updated form action object.', 'formidable' ),
					'properties'  => array(
						'id'           => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Numeric action ID', 'formidable' ),
						),
						'type'         => array(
							'type'        => 'string',
							'description' => __( 'Action type', 'formidable' ),
						),
						'form_id'      => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'ID of the form this action belongs to', 'formidable' ),
						),
						'post_title'   => array(
							'type'        => 'string',
							'description' => __( 'Action title', 'formidable' ),
						),
						'post_status'  => array(
							'type'        => 'string',
							'description' => __( 'Action status', 'formidable' ),
						),
						'post_content' => array(
							'type'        => 'object',
							'description' => __( 'Action settings', 'formidable' ),
						),
						'created_at'   => array(
							'type'        => 'string',
							'description' => __( 'Action creation date', 'formidable' ),
						),
						'modified_at'  => array(
							'type'        => 'string',
							'description' => __( 'Action last modified date', 'formidable' ),
						),
					),
				),
				'execute_callback'    => 'FrmAbilitiesFormActionsController::execute_update_form_action',
				'permission_callback' => 'FrmAbilitiesFormActionsController::can_update_form_action',
				'meta'                => FrmAbilitiesHelper::meta( false, false, false ),
			)
		);
	}

	/**
	 * Register the delete form action ability.
	 *
	 * @return void
	 */
	private static function register_delete_form_action_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/delete-form-action',
			array(
				'label'               => __( 'Delete Form Action', 'formidable' ),
				'description'         => __( 'Delete a form action.', 'formidable' ),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Form action ID. Required.', 'formidable' ),
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'Deleted form action object.', 'formidable' ),
				),
				'execute_callback'    => 'FrmAbilitiesFormActionsController::execute_delete_form_action',
				'permission_callback' => 'FrmAbilitiesFormActionsController::can_delete_form_action',
				'meta'                => FrmAbilitiesHelper::meta( false, true, false ),
			)
		);
	}

	/**
	 * List the actions configured on a form.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_list_form_actions( $input ) {
		FrmAbilitiesHelper::set_current_user();

		if ( empty( $input['form_id'] ) ) {
			return new WP_Error( 'frm_form_actions_missing_form_id', __( 'A form ID is required.', 'formidable' ), array( 'status' => 400 ) );
		}

		$form = FrmAbilitiesHelper::get_form( $input['form_id'] );

		if ( is_wp_error( $form ) ) {
			return $form;
		}

		$status = isset( $input['post_status'] ) ? (string) $input['post_status'] : '';
		$type   = $input['type'] ?? '';

		$args = array(
			'post_type'   => FrmFormActionsController::$action_post_type,
			// Draft actions are disabled but still configured on the form, so
			// list them alongside published ones unless a status filter is set.
			'post_status' => '' !== $status ? $status : array( 'publish', 'draft' ),
			'numberposts' => -1,
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
			'menu_order'  => (int) $form->id,
		);

		if ( $type && 'all' !== $type ) {
			$args['post_excerpt'] = $type;
		}

		$actions = get_posts( $args );
		$data    = array();

		foreach ( $actions as $action ) {
			$data[ $action->ID ] = self::prepare_action_for_response( $action );
		}

		return $data;
	}

	/**
	 * Get one form action.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_get_form_action( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$action = self::get_action( $input['id'] );

		return is_wp_error( $action ) ? $action : self::prepare_action_for_response( self::prepare_action_content( $action ) );
	}

	/**
	 * Create a form action.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_create_form_action( $input ) {
		FrmAbilitiesHelper::set_current_user();

		if ( empty( $input['form_id'] ) ) {
			return new WP_Error( 'frm_form_actions_missing_form_id', __( 'A form ID is required.', 'formidable' ), array( 'status' => 400 ) );
		}

		if ( empty( $input['type'] ) ) {
			return new WP_Error( 'frm_form_actions_missing_type', __( 'An action type is required.', 'formidable' ), array( 'status' => 400 ) );
		}

		$form = FrmAbilitiesHelper::get_form( $input['form_id'] );

		if ( is_wp_error( $form ) ) {
			return $form;
		}

		$action_control = self::get_action_control( $input['type'] );

		if ( is_wp_error( $action_control ) ) {
			return $action_control;
		}

		// Actions are numbered per form, and the number is part of the stored id.
		$existing      = FrmFormAction::get_action_for_form( $form->id, 'all', array( 'post_status' => 'any' ) );
		$action_number = count( $existing ) + 1;
		$action_control->_set( $action_number );

		$action = $action_control->prepare_new( $form->id );

		if ( isset( $input['post_content'] ) && is_array( $input['post_content'] ) ) {
			$action->post_content = array_merge( (array) $action->post_content, $input['post_content'] );
		}

		if ( isset( $input['post_title'] ) ) {
			$action->post_title = sanitize_text_field( $input['post_title'] );
		}

		if ( isset( $input['post_status'] ) ) {
			$action->post_status = sanitize_text_field( $input['post_status'] );
		}

		// Run the action type's own update() the same way the admin save path
		// does. Add-on actions do real work there: the Quizzes actions insert the
		// hidden score field the form needs, and On Submit sanitizes its redirect
		// URL. Without this, an action created through an ability is only half
		// configured until something updates it. The base implementation returns
		// the instance unchanged.
		$new_instance = $action_control->update( (array) $action, (array) $action );

		if ( false === $new_instance ) {
			return new WP_Error( 'frm_form_actions_create_failed', __( 'The action update method returned false.', 'formidable' ), array( 'status' => 500 ) );
		}

		$action_id = $action_control->save_settings( $new_instance );

		if ( is_wp_error( $action_id ) ) {
			return FrmAbilitiesHelper::flatten_error( $action_id );
		}

		$saved_action = $action_control->get_single_action( $action_id );

		if ( ! $saved_action ) {
			return new WP_Error( 'frm_form_actions_create_failed', __( 'Failed to create the form action.', 'formidable' ), array( 'status' => 500 ) );
		}

		self::maybe_flag_on_submit_migrated( $saved_action, $form );

		return self::prepare_action_for_response( $saved_action );
	}

	/**
	 * Update a form action.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_update_form_action( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$action = self::get_action( $input['id'] );

		if ( is_wp_error( $action ) ) {
			return $action;
		}

		$action_type    = sanitize_title( $action->post_excerpt );
		$action_control = self::get_action_control( $action_type );

		if ( is_wp_error( $action_control ) ) {
			return new WP_Error( 'frm_form_actions_invalid_type', __( 'That action type no longer exists.', 'formidable' ), array( 'status' => 400 ) );
		}

		// post_excerpt has to be included: the save path uses wp_insert_post,
		// which does not merge with the existing fields, and an action saved
		// without its type excerpt no longer matches action queries.
		$update_data = array(
			'ID'           => $action->ID,
			'post_type'    => FrmFormActionsController::$action_post_type,
			'post_excerpt' => $action->post_excerpt,
			'menu_order'   => $action->menu_order,
			'post_name'    => $action->post_name,
			'post_date'    => $action->post_date,
			'post_title'   => isset( $input['post_title'] ) ? sanitize_text_field( $input['post_title'] ) : $action->post_title,
			'post_status'  => isset( $input['post_status'] ) ? sanitize_text_field( $input['post_status'] ) : $action->post_status,
		);

		$stored_content = (array) FrmAppHelper::maybe_json_decode( $action->post_content );

		if ( isset( $input['post_content'] ) && is_array( $input['post_content'] ) ) {
			// Merge over the stored settings so a partial update leaves the rest
			// of the action's configuration alone.
			$update_data['post_content'] = array_merge( $stored_content, $input['post_content'] );
		} else {
			$update_data['post_content'] = $stored_content;
		}

		$action->post_content = $stored_content;
		$new_instance         = $action_control->update( $update_data, (array) $action );

		if ( false === $new_instance ) {
			return new WP_Error( 'frm_form_actions_update_failed', __( 'The action update method returned false.', 'formidable' ), array( 'status' => 500 ) );
		}

		$action_id = $action_control->save_settings( $new_instance );

		if ( is_wp_error( $action_id ) ) {
			return FrmAbilitiesHelper::flatten_error( $action_id );
		}

		$saved_action = $action_control->get_single_action( $action_id );

		if ( ! $saved_action ) {
			return new WP_Error( 'frm_form_actions_update_failed', __( 'Failed to update the form action.', 'formidable' ), array( 'status' => 500 ) );
		}

		return self::prepare_action_for_response( $saved_action );
	}

	/**
	 * Delete a form action.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_delete_form_action( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$action = self::get_action( $input['id'] );

		if ( is_wp_error( $action ) ) {
			return $action;
		}

		// Read the action before it is gone, so the caller gets back what it deleted.

		if ( ! wp_delete_post( $action->ID, true ) ) {
			return new WP_Error( 'frm_form_actions_delete_failed', __( 'Failed to delete the form action.', 'formidable' ), array( 'status' => 500 ) );
		}

		$data = self::prepare_action_for_response( self::prepare_action_content( $action ) );

		FrmFormAction::clear_cache();

		return $data;
	}

	/**
	 * Load one form action post.
	 *
	 * @since x.x
	 *
	 * @param int|string $id Form action ID.
	 *
	 * @return WP_Error|WP_Post
	 */
	private static function get_action( $id ) {
		$action = get_post( absint( $id ) );

		if ( ! $action || FrmFormActionsController::$action_post_type !== $action->post_type ) {
			return new WP_Error( 'frm_form_action_invalid_id', __( 'Invalid form action ID.', 'formidable' ), array( 'status' => 404 ) );
		}

		return $action;
	}

	/**
	 * Resolve one action type to the control object that saves it.
	 *
	 * FrmFormActionsController::get_form_actions() answers with the full array of
	 * registered controls when the type does not match a registered id_base, so
	 * the result is narrowed to one control here rather than used as given.
	 *
	 * @since x.x
	 *
	 * @param string $type The action type, such as email or on_submit.
	 *
	 * @return FrmFormAction|WP_Error The action control, or an error when the type is not registered.
	 */
	private static function get_action_control( $type ) {
		$type           = sanitize_title( $type );
		$action_control = FrmFormActionsController::get_form_actions( $type );

		if ( is_array( $action_control ) ) {
			$action_control = $action_control[ $type ] ?? null;
		}

		if ( ! $action_control instanceof FrmFormAction ) {
			return new WP_Error( 'frm_form_actions_invalid_type', __( 'Invalid action type.', 'formidable' ), array( 'status' => 400 ) );
		}

		return $action_control;
	}

	/**
	 * Let the action type expand its own stored settings before they are read.
	 *
	 * @since x.x
	 *
	 * @param WP_Post $action The stored action post.
	 *
	 * @return object
	 */
	private static function prepare_action_content( $action ) {
		$action_control = self::get_action_control( $action->post_excerpt );

		if ( ! is_wp_error( $action_control ) ) {
			return $action_control->prepare_action( $action );
		}

		// The add-on that registered this action type is no longer active, so
		// there is nothing to expand the settings with. Hand back what is stored.
		$action->post_content = (array) FrmAppHelper::maybe_json_decode( $action->post_content );

		return $action;
	}

	/**
	 * Mark a form as migrated when an On Submit action is saved to it.
	 *
	 * On Submit actions only run when the form has the on_submit_migrated option
	 * set. Core sets it during its own settings migration, so a confirmation
	 * action created through an ability would otherwise never fire.
	 *
	 * @since x.x
	 *
	 * @param object   $saved_action The saved action post object.
	 * @param stdClass $form         The form the action belongs to.
	 *
	 * @return void
	 */
	private static function maybe_flag_on_submit_migrated( $saved_action, $form ) {
		$action_type = $saved_action->post_excerpt ?? '';

		if ( 'on_submit' !== $action_type || ! class_exists( 'FrmOnSubmitHelper' ) ) {
			return;
		}

		if ( FrmOnSubmitHelper::form_has_migrated( $form ) ) {
			return;
		}

		if ( ! is_array( $form->options ) ) {
			$form->options = array();
		}

		$form->options['on_submit_migrated'] = 1;

		FrmForm::update( $form->id, array( 'options' => $form->options ) );
	}

	/**
	 * Build the response shape for one form action.
	 *
	 * @since x.x
	 *
	 * @param object $action The action to describe.
	 *
	 * @return array
	 */
	public static function prepare_action_for_response( $action ) {
		$post_content = $action->post_content;

		if ( is_string( $post_content ) ) {
			$post_content = json_decode( $post_content, true );
		}

		return array(
			'id'           => (int) $action->ID,
			'type'         => sanitize_title( $action->post_excerpt ),
			'form_id'      => (int) $action->menu_order,
			'post_title'   => (string) $action->post_title,
			'post_status'  => (string) $action->post_status,
			'post_content' => $post_content,
			'created_at'   => (string) $action->post_date,
			'modified_at'  => (string) $action->post_modified,
		);
	}

	/**
	 * Permission callback for list form actions.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_list_form_actions( $input ) {
		return current_user_can( 'frm_view_forms' ) || current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for get form action.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_get_form_action( $input ) {
		return current_user_can( 'frm_view_forms' ) || current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for create form action.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_create_form_action( $input ) {
		return current_user_can( 'frm_edit_forms' ) || current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for update form action.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_update_form_action( $input ) {
		return current_user_can( 'frm_edit_forms' ) || current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for delete form action.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_delete_form_action( $input ) {
		return current_user_can( 'frm_delete_forms' ) || current_user_can( 'administrator' );
	}
}
