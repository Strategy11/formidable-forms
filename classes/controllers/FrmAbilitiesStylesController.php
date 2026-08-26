<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Registers the Formidable style abilities for the WordPress Abilities API.
 *
 * @since x.x
 */
class FrmAbilitiesStylesController {

	/**
	 * Register the style abilities.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function register_abilities() {
		self::register_list_styles_ability();
		self::register_get_style_ability();
		self::register_update_style_ability();
	}

	/**
	 * Register the list styles ability.
	 *
	 * @return void
	 */
	private static function register_list_styles_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/list-styles',
			array(
				'label'               => __( 'List Styles', 'formidable' ),
				'description'         => __(
					'Retrieve all Formidable styles. Returns id, post_name, name, and settings. Use the id or post_name for get-style or update-style.',
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
							'default'     => 'title',
						),
					),
				),
				'output_schema'       => array(
					'type'                 => 'object',
					'description'          => __( 'Array of style objects keyed by style ID. Each style includes comprehensive style properties.', 'formidable' ),
					'additionalProperties' => array(
						'type'       => 'object',
						'properties' => array(
							'id'           => array(
								'type'        => array( 'string', 'integer' ),
								'description' => __( 'Numeric style ID', 'formidable' ),
							),
							'post_name'    => array(
								'type'        => 'string',
								'description' => __( 'CSS scope slug for the style', 'formidable' ),
							),
							'name'         => array(
								'type'        => 'string',
								'description' => __( 'Style name', 'formidable' ),
							),
							'post_content' => array(
								'type'        => 'object',
								'description' => __( 'Style settings array containing colors, fonts, spacing, etc.', 'formidable' ),
							),
							'menu_order'   => array(
								'type'        => 'integer',
								'description' => __( 'Whether this is the default style (1) or not (0)', 'formidable' ),
							),
							'created_at'   => array(
								'type'        => 'string',
								'description' => __( 'Style creation date in MySQL format', 'formidable' ),
							),
						),
					),
				),
				'execute_callback'    => 'FrmAbilitiesStylesController::execute_list_styles',
				'permission_callback' => 'FrmAbilitiesStylesController::can_list_styles',
				'meta'                => FrmAbilitiesHelper::meta( true, false, true ),
			)
		);
	}

	/**
	 * Register the get style ability.
	 *
	 * @return void
	 */
	private static function register_get_style_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/get-style',
			array(
				'label'               => __( 'Get Style', 'formidable' ),
				'description'         => __( 'Retrieve a single Formidable style by ID or post_name.', 'formidable' ),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Style ID or post_name. Use "default" to get the default style.', 'formidable' ),
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'Style object with comprehensive properties.', 'formidable' ),
				),
				'execute_callback'    => 'FrmAbilitiesStylesController::execute_get_style',
				'permission_callback' => 'FrmAbilitiesStylesController::can_get_style',
				'meta'                => FrmAbilitiesHelper::meta( true, false, true ),
			)
		);
	}

	/**
	 * Register the update style ability.
	 *
	 * @return void
	 */
	private static function register_update_style_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/update-style',
			array(
				'label'               => __( 'Update Style', 'formidable' ),
				'description'         => __( 'Update an existing Formidable style.', 'formidable' ),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id'           => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Style ID or post_name.', 'formidable' ),
						),
						'name'         => array(
							'type'        => 'string',
							'description' => __( 'Style name.', 'formidable' ),
						),
						'post_content' => array(
							'type'                 => 'object',
							'description'          => __( 'Style settings array containing colors, fonts, spacing, etc.', 'formidable' ),
							'additionalProperties' => true,
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'Updated style object.', 'formidable' ),
				),
				'execute_callback'    => 'FrmAbilitiesStylesController::execute_update_style',
				'permission_callback' => 'FrmAbilitiesStylesController::can_update_style',
				'meta'                => FrmAbilitiesHelper::meta( false, false, false ),
			)
		);
	}

	/**
	 * List the styles on the site.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array
	 */
	public static function execute_list_styles( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$input     = FrmAbilitiesHelper::normalize_order( $input );
		$frm_style = new FrmStyle();

		$order_by  = ! empty( $input['order_by'] ) ? $input['order_by'] : 'post_title';
		$order     = ! empty( $input['order'] ) ? $input['order'] : 'ASC';
		$page_size = ! empty( $input['page_size'] ) ? absint( $input['page_size'] ) : 20;

		$styles = $frm_style->get_all( $order_by, $order, $page_size );
		$data   = array();

		foreach ( $styles as $style ) {
			$data[ $style->ID ] = self::prepare_style_for_response( $style );
		}

		return $data;
	}

	/**
	 * Get one style.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_get_style( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$style = self::get_style( $input['id'] );

		return is_wp_error( $style ) ? $style : self::prepare_style_for_response( $style );
	}

	/**
	 * Update a style.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_update_style( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$id    = sanitize_text_field( $input['id'] );
		$style = 'default' === $id ? self::get_style( 'default' ) : get_post( $id );

		if ( is_wp_error( $style ) ) {
			return $style;
		}

		if ( ! $style || FrmStylesController::$post_type !== $style->post_type ) {
			return self::get_invalid_style_error();
		}

		$style_array = array(
			'ID'           => $style->ID,
			'post_type'    => FrmStylesController::$post_type,
			'post_title'   => $style->post_title,
			'post_name'    => $style->post_name,
			'post_content' => FrmAppHelper::maybe_json_decode( $style->post_content ),
			'menu_order'   => $style->menu_order,
			'post_status'  => $style->post_status,
		);

		if ( isset( $input['name'] ) ) {
			$style_array['post_title'] = sanitize_text_field( $input['name'] );
		}

		if ( isset( $input['post_content'] ) && is_array( $input['post_content'] ) ) {
			// Merge over the stored settings so a partial update leaves every
			// other setting alone.
			$existing                    = is_array( $style_array['post_content'] ) ? $style_array['post_content'] : array();
			$style_array['post_content'] = array_merge( $existing, self::strip_hex_prefix( $input['post_content'] ) );
		}

		$frm_style = new FrmStyle( $style->ID );
		$result    = $frm_style->save( $style_array );

		if ( ! $result || is_wp_error( $result ) ) {
			return new WP_Error( 'frm_update_style', __( 'Style update failed.', 'formidable' ), array( 'status' => 409 ) );
		}

		// FrmStyle->save() does not respect post_type, so set it explicitly.
		wp_update_post(
			array(
				'ID'        => $style->ID,
				'post_type' => FrmStylesController::$post_type,
			)
		);

		// Regenerate the stylesheet and refresh the transients.
		$frm_style->save_settings();

		$updated = self::get_style( $style->ID );

		return is_wp_error( $updated ) ? $updated : self::prepare_style_for_response( $updated );
	}

	/**
	 * Load one style, by ID or as the default.
	 *
	 * @since x.x
	 *
	 * @param int|string $id Style ID, or 'default' for the default style.
	 *
	 * @return stdClass|WP_Error|WP_Post
	 */
	private static function get_style( $id ) {
		$id        = sanitize_text_field( $id );
		$frm_style = 'default' === $id ? new FrmStyle( 'default' ) : new FrmStyle( $id );
		$style     = $frm_style->get_one();

		return $style ? $style : self::get_invalid_style_error();
	}

	/**
	 * Strip the # prefix from hex color values in style settings.
	 *
	 * Formidable stores color values without it, and a stored # makes the
	 * generated CSS emit ##ffffff, which the browser drops.
	 *
	 * Public because Pro's create-style ability normalizes the same way.
	 *
	 * @since x.x
	 *
	 * @param array $post_content The style settings.
	 *
	 * @return array
	 */
	public static function strip_hex_prefix( $post_content ) {
		foreach ( $post_content as $key => $value ) {
			if ( is_string( $value ) && str_starts_with( $value, '#' ) ) {
				$post_content[ $key ] = substr( $value, 1 );
			}
		}

		return $post_content;
	}

	/**
	 * Build the response shape for one style.
	 *
	 * @since x.x
	 *
	 * @param stdClass|WP_Post $style The style to describe.
	 *
	 * @return array
	 */
	public static function prepare_style_for_response( $style ) {
		return array(
			'id'           => (int) $style->ID,
			'post_name'    => (string) $style->post_name,
			'name'         => (string) $style->post_title,
			'post_content' => $style->post_content,
			'menu_order'   => (int) $style->menu_order,
			'created_at'   => (string) $style->post_date,
			'updated_at'   => (string) $style->post_modified,
		);
	}

	/**
	 * Build the error returned when no style matches the given ID.
	 *
	 * @since x.x
	 *
	 * @return WP_Error
	 */
	private static function get_invalid_style_error() {
		return new WP_Error( 'frm_style_invalid_id', __( 'Invalid style ID.', 'formidable' ), array( 'status' => 404 ) );
	}

	/**
	 * Permission callback for list styles.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_list_styles( $input ) {
		return current_user_can( 'frm_view_forms' ) || current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for get style.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_get_style( $input ) {
		return current_user_can( 'frm_view_forms' ) || current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for update style.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_update_style( $input ) {
		return current_user_can( 'frm_change_settings' ) || current_user_can( 'administrator' );
	}
}
