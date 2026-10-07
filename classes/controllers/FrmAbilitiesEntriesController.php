<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Registers the Formidable entry abilities for the WordPress Abilities API.
 *
 * Reading and removing entries only. Creating and editing one through an
 * ability are Pro features, and live in FrmProAbilitiesEntriesController. The
 * helpers they share are public here, since the data they write has to be
 * shaped the same way whichever plugin does the writing.
 *
 * @since x.x
 */
class FrmAbilitiesEntriesController {

	/**
	 * Register the entry abilities.
	 *
	 * @since x.x
	 *
	 * @return void
	 */
	public static function register_abilities() {
		self::register_list_entries_ability();
		self::register_get_entry_ability();
		self::register_delete_entry_ability();
	}

	/**
	 * Register the list entries ability.
	 *
	 * @return void
	 */
	private static function register_list_entries_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/list-entries',
			array(
				'label'               => __( 'List Entries', 'formidable' ),
				'description'         => __( 'Retrieve a list of Formidable entries (form submissions). Use list-forms to find the form_id.', 'formidable' ),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'properties' => array(
						'form_id'    => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Form ID or form_key to filter entries. Optional, returns entries from all forms if not provided.', 'formidable' ),
						),
						'page'       => array(
							'type'        => 'integer',
							'description' => __( 'Current page of the collection.', 'formidable' ),
							'default'     => 1,
						),
						'page_size'  => array(
							'type'        => 'integer',
							'description' => __( 'Maximum number of items to return per page.', 'formidable' ),
							'default'     => 25,
						),
						'order'      => array(
							'type'        => 'string',
							'description' => __( 'Order of results (asc or desc, case-insensitive).', 'formidable' ),
							'default'     => 'asc',
							'enum'        => array( 'asc', 'desc', 'ASC', 'DESC' ),
						),
						'order_by'   => array(
							'type'        => 'string',
							'description' => __( 'Field to order by (id, created_at, etc.).', 'formidable' ),
							'default'     => 'id',
						),
						'search'     => array(
							'type'        => 'string',
							'description' => __( 'Search term to filter entries by field values.', 'formidable' ),
						),
						'start_date' => array(
							'type'        => 'string',
							'description' => __( 'Start date for filtering entries (YYYY-MM-DD format).', 'formidable' ),
						),
						'end_date'   => array(
							'type'        => 'string',
							'description' => __( 'End date for filtering entries (YYYY-MM-DD format).', 'formidable' ),
						),
						'is_draft'   => array(
							'type'        => 'integer',
							'enum'        => array( 0, 1 ),
							'description' => __( 'Filter by draft status: 0 returns only submitted entries, 1 returns only drafts. Both are included when omitted.', 'formidable' ),
						),
					),
				),
				'output_schema'       => self::get_list_entries_output_schema(),
				'execute_callback'    => 'FrmAbilitiesEntriesController::execute_list_entries',
				'permission_callback' => 'FrmAbilitiesEntriesController::can_list_entries',
				'meta'                => FrmAbilitiesHelper::meta( true, false, true ),
			)
		);
	}

	/**
	 * Build the output schema for the list entries ability.
	 *
	 * Kept out of the registrar because one entry carries enough properties
	 * to bury everything else the registration declares.
	 *
	 * @since x.x
	 *
	 * @return array
	 */
	private static function get_list_entries_output_schema() {
		return array(
			'type'                 => 'object',
			'description'          => __(
				'Array of entry objects keyed by item_key. Each entry includes id, item_key, form_id, user_id, created_at, and meta (field values).',
				'formidable'
			),
			'additionalProperties' => array(
				'type'       => 'object',
				'properties' => array(
					'id'         => array(
						'type'        => array( 'string', 'integer' ),
						'description' => __( 'Numeric entry ID', 'formidable' ),
					),
					'item_key'   => array(
						'type'        => 'string',
						'description' => __( 'Unique alphanumeric entry key', 'formidable' ),
					),
					'form_id'    => array(
						'type'        => array( 'string', 'integer' ),
						'description' => __( 'ID of the form this entry belongs to', 'formidable' ),
					),
					'user_id'    => array(
						'type'        => array( 'string', 'integer' ),
						'description' => __( 'ID of the user who submitted the entry (0 if guest)', 'formidable' ),
					),
					'created_at' => array(
						'type'        => 'string',
						'description' => __( 'Entry creation date in MySQL format', 'formidable' ),
					),
					'updated_at' => array(
						'type'        => 'string',
						'description' => __( 'Entry last update date in MySQL format', 'formidable' ),
					),
					'is_draft'   => array(
						'type'        => array( 'string', 'boolean' ),
						'description' => __( 'Whether the entry is a draft, returned as "0" or "1"', 'formidable' ),
					),
					'meta'       => array(
						'type'                 => 'object',
						'description'          => __( 'Field values keyed by field ID or field_key', 'formidable' ),
						'additionalProperties' => true,
					),
				),
			),
		);
	}

	/**
	 * Register the get entry ability.
	 *
	 * @return void
	 */
	private static function register_get_entry_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/get-entry',
			array(
				'label'               => __( 'Get Entry', 'formidable' ),
				'description'         => __( 'Retrieve a single Formidable entry by ID or key. Returns all field values and entry metadata.', 'formidable' ),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Entry ID or item_key. Required.', 'formidable' ),
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'Entry object with comprehensive field values and metadata.', 'formidable' ),
					'properties'  => array(
						'id'         => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Numeric entry ID', 'formidable' ),
						),
						'item_key'   => array(
							'type'        => 'string',
							'description' => __( 'Unique alphanumeric entry key', 'formidable' ),
						),
						'form_id'    => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'ID of the form this entry belongs to', 'formidable' ),
						),
						'user_id'    => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'ID of the user who submitted the entry (0 if guest)', 'formidable' ),
						),
						'created_at' => array(
							'type'        => 'string',
							'description' => __( 'Entry creation date in MySQL format', 'formidable' ),
						),
						'updated_at' => array(
							'type'        => 'string',
							'description' => __( 'Entry last update date in MySQL format', 'formidable' ),
						),
						'is_draft'   => array(
							'type'        => array( 'string', 'boolean' ),
							'description' => __( 'Whether the entry is a draft, returned as "0" or "1"', 'formidable' ),
						),
						'meta'       => array(
							'type'                 => 'object',
							'description'          => __( 'Field values keyed by field ID or field_key', 'formidable' ),
							'additionalProperties' => true,
						),
					),
				),
				'execute_callback'    => 'FrmAbilitiesEntriesController::execute_get_entry',
				'permission_callback' => 'FrmAbilitiesEntriesController::can_get_entry',
				'meta'                => FrmAbilitiesHelper::meta( true, false, true ),
			)
		);
	}

	/**
	 * Register the delete entry ability.
	 *
	 * @return void
	 */
	private static function register_delete_entry_ability() {
		FrmAbilitiesHelper::register(
			'formidable-forms/delete-entry',
			array(
				'label'               => __( 'Delete Entry', 'formidable' ),
				'description'         => __( 'Delete a Formidable entry.', 'formidable' ),
				'category'            => FrmAbilitiesController::CATEGORY,
				'input_schema'        => array(
					'type'       => 'object',
					'required'   => array( 'id' ),
					'properties' => array(
						'id' => array(
							'type'        => array( 'string', 'integer' ),
							'description' => __( 'Entry ID or item_key.', 'formidable' ),
						),
					),
				),
				'output_schema'       => array(
					'type'        => 'object',
					'description' => __( 'Deleted entry object.', 'formidable' ),
				),
				'execute_callback'    => 'FrmAbilitiesEntriesController::execute_delete_entry',
				'permission_callback' => 'FrmAbilitiesEntriesController::can_delete_entry',
				'meta'                => FrmAbilitiesHelper::meta( false, true, false ),
			)
		);
	}
	/**
	 * List entries, optionally scoped to one form.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_list_entries( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$input = FrmAbilitiesHelper::normalize_order( $input );
		$where = array();

		// Listings include drafts unless is_draft is sent explicitly.
		if ( isset( $input['is_draft'] ) ) {
			$where['is_draft'] = absint( $input['is_draft'] );
		}

		if ( ! empty( $input['form_id'] ) ) {
			$form = FrmAbilitiesHelper::get_form( $input['form_id'] );

			if ( is_wp_error( $form ) ) {
				return $form;
			}

			$where['form_id'] = (int) $form->id;

			if ( ! empty( $input['search'] ) && class_exists( 'FrmProEntriesHelper' ) ) {
				$search_args = array();

				if ( isset( $where['is_draft'] ) ) {
					$search_args['is_draft'] = $where['is_draft'];
				}

				$where['it.id'] = FrmProEntriesHelper::get_search_ids( $input['search'], $form->id, $search_args );
			}
		}

		if ( ! empty( $input['start_date'] ) ) {
			$where['it.created_at >'] = gmdate( 'Y-m-d H:i:s', strtotime( $input['start_date'] ) );
		}

		if ( ! empty( $input['end_date'] ) ) {
			$where['it.created_at <'] = gmdate( 'Y-m-d H:i:s', strtotime( $input['end_date'] ) );
		}

		if ( isset( $where['it.id'] ) && ! $where['it.id'] ) {
			// The search matched nothing. Querying on an empty id list would drop
			// the condition and return every entry instead.
			return array();
		}

		list( $order, $limit ) = FrmAbilitiesHelper::prepare_order_and_limit( $input );

		$entries      = FrmEntry::getAll( $where, $order, $limit, false, false );
		$item_form_id = 0;
		$fields       = array();
		$data         = array();

		foreach ( $entries as $entry ) {
			if ( (int) $item_form_id !== (int) $entry->form_id ) {
				$fields       = FrmField::get_all_for_form( $entry->form_id, '', 'include' );
				$item_form_id = $entry->form_id;
			}

			$entry->meta = FrmEntriesController::show_entry_shortcode(
				array(
					'format'        => 'array',
					'include_blank' => true,
					'id'            => $entry->id,
					'user_info'     => false,
					'fields'        => $fields,
				)
			);

			$data[ $entry->item_key ] = self::prepare_entry_for_response( $entry );
		}

		return $data;
	}

	/**
	 * Get one entry, with its field values.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_get_entry( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$entry = self::get_entry_with_meta( $input['id'] );

		return is_wp_error( $entry ) ? $entry : self::prepare_entry_for_response( $entry );
	}

	/**
	 * Delete an entry.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return array|WP_Error
	 */
	public static function execute_delete_entry( $input ) {
		FrmAbilitiesHelper::set_current_user();

		$entry = FrmEntry::getOne( $input['id'] );

		if ( ! $entry ) {
			return self::get_invalid_entry_error();
		}

		// Read the entry before it is gone, so the caller gets back what it deleted.
		$entry->meta = array();

		if ( ! FrmEntry::destroy( $entry->id ) ) {
			return self::get_invalid_entry_error();
		}

		return self::prepare_entry_for_response( $entry );
	}

	/**
	 * Load one entry with its field values attached.
	 *
	 * @since x.x
	 *
	 * @param int|string $id Entry ID or item_key.
	 *
	 * @return object|WP_Error
	 */
	private static function get_entry_with_meta( $id ) {
		$entry = FrmEntry::getOne( $id );

		if ( ! $entry ) {
			return self::get_invalid_entry_error();
		}

		// The Surveys add-on strips a Likert field's row fields out of the entry
		// values so its own display can group them. That is right for a rendered
		// entry and wrong for one being read as data, where the rows are the value.
		$likert_filter = array( 'FrmSurveys\controllers\LikertController', 'remove_row_fields_from_form' );
		$has_likert    = class_exists( 'FrmSurveys\controllers\LikertController' );

		if ( $has_likert ) {
			remove_filter( 'frm_entry_values_fields', $likert_filter );
		}

		$entry->meta = FrmEntriesController::show_entry_shortcode(
			array(
				'format'        => 'array',
				'include_blank' => true,
				'id'            => $id,
				'user_info'     => false,
				'child_array'   => true,
				'date_format'   => 'Y-m-d',
			)
		);

		if ( $has_likert ) {
			add_filter( 'frm_entry_values_fields', $likert_filter );
		}

		return $entry;
	}

	/**
	 * Re-key item_meta values that use field keys to their numeric field ids.
	 *
	 * The input schema documents item_meta as keyed by field id or field key, but
	 * validation and the save only recognize numeric ids. When a value is present
	 * under both the id and the key, the id-keyed value wins.
	 *
	 * @since x.x
	 *
	 * Public because Pro's update-entry ability re-keys the same way before it
	 * merges the submitted values over the stored ones.
	 *
	 * @param array      $item_meta Field values keyed by field id or field key.
	 * @param int|string $form_id   The form the fields belong to.
	 *
	 * @return void
	 */
	public static function normalize_item_meta_keys( &$item_meta, $form_id ) {
		if ( ! is_array( $item_meta ) || array() === $item_meta ) {
			return;
		}

		foreach ( FrmField::get_all_for_form( $form_id ) as $field ) {
			if ( ! isset( $item_meta[ $field->field_key ] ) ) {
				continue;
			}

			if ( ! isset( $item_meta[ $field->id ] ) ) {
				$item_meta[ $field->id ] = $item_meta[ $field->field_key ];
			}

			// Always consume the key entry. Core resolves leftover field key keys
			// when metas are saved, so a duplicate would overwrite the id keyed
			// value that is supposed to win.
			unset( $item_meta[ $field->field_key ] );
		}
	}

	/**
	 * Put each submitted value into the shape the database stores.
	 *
	 * Public because Pro's update-entry ability puts its merged values through
	 * the same conversions before saving them.
	 *
	 * @since x.x
	 *
	 * @param array $entry  Raw entry input.
	 * @param array $fields Fields on the form.
	 *
	 * @return array
	 */
	public static function prepare_entry_data( $entry, $fields ) {
		$set_meta      = ! isset( $entry['item_meta'] );
		$data          = array();
		$possible_data = array(
			'id',
			'item_key',
			'name',
			'description',
			'ip',
			'form_id',
			'post_id',
			'user_id',
			'parent_item_id',
			'is_draft',
			'updated_by',
			'created_at',
			'updated_at',
		);

		foreach ( $possible_data as $possible ) {
			if ( isset( $entry[ $possible ] ) ) {
				$data[ $possible ] = $entry[ $possible ];
			}
		}

		$data['item_meta'] = $set_meta ? array() : $entry['item_meta'];

		// The import value conversions below are Pro field behavior. Without Pro
		// the values are stored as sent.
		$include = class_exists( 'FrmProAppHelper' );

		foreach ( $fields as $field ) {
			if ( $set_meta ) {
				if ( isset( $entry[ $field->id ] ) ) {
					$data['item_meta'][ $field->id ] = $entry[ $field->id ];
				} elseif ( isset( $entry[ $field->field_key ] ) ) {
					$data['item_meta'][ $field->id ] = $entry[ $field->field_key ];
				}
			}

			if ( 'divider' === $field->type && FrmField::is_option_true( $field, 'repeat' ) ) {
				if ( ! isset( $data['item_meta'][ $field->id ]['form'] ) ) {
					$data['item_meta'][ $field->id ]['form'] = $field->field_options['form_select'];
				}

				self::normalize_repeater_rows( $data['item_meta'][ $field->id ], $field );
			}

			if ( ! $include || ! isset( $data['item_meta'][ $field->id ] ) ) {
				continue;
			}

			switch ( $field->type ) {
				case 'user_id':
					$data['item_meta'][ $field->id ] = FrmAppHelper::get_user_id_param( trim( $data['item_meta'][ $field->id ] ) );
					$data['frm_user_id']             = $data['item_meta'][ $field->id ];
					break;
				case 'checkbox':
				case 'select':
					if ( ! is_array( $data['item_meta'][ $field->id ] ) ) {
						self::format_field_value( $field, $data['item_meta'][ $field->id ] );
					}
					break;
				case 'file':
					self::format_file_id( $data['item_meta'][ $field->id ], $field );
					break;
				case 'data':
				case 'date':
					self::format_field_value( $field, $data['item_meta'][ $field->id ] );
			}
		}//end foreach

		/**
		 * Filter the entry data an ability is about to save.
		 *
		 * @since x.x
		 *
		 * @param array $data   Prepared entry data.
		 * @param array $fields Fields on the form.
		 */
		return (array) apply_filters( 'frm_abilities_prepare_entry_data', $data, $fields );
	}

	/**
	 * Put a repeater's rows into the indexed shape the save expects.
	 *
	 * Rows arrive keyed however the caller wrote them. Formidable identifies a
	 * new row by an 'i' prefixed index and lists every row in row_ids, so a row
	 * under any other key is renumbered into a free index rather than dropped.
	 *
	 * @since x.x
	 *
	 * @param array    $rows  The repeater's rows, by reference.
	 * @param stdClass $field The repeater field.
	 *
	 * @return void
	 */
	private static function normalize_repeater_rows( &$rows, $field ) {
		if ( ! is_array( $rows ) ) {
			return;
		}

		$child_form_id = isset( $field->field_options['form_select'] ) ? (int) $field->field_options['form_select'] : 0;
		$child_fields  = $child_form_id ? FrmField::get_all_for_form( $child_form_id ) : array();
		$ids_by_key    = array();

		foreach ( $child_fields as $child_field ) {
			$ids_by_key[ $child_field->field_key ] = $child_field->id;
		}

		$normalized = array();
		$next_index = 0;

		foreach ( $rows as $row_key => $row ) {
			if ( ! is_array( $row ) || in_array( $row_key, array( 'form', 'row_ids' ), true ) ) {
				// Not a row. form and row_ids are the repeater's own metadata.
				continue;
			}

			foreach ( $ids_by_key as $child_key => $child_id ) {
				if ( ! isset( $row[ $child_key ] ) || isset( $row[ $child_id ] ) ) {
					continue;
				}

				$row[ $child_id ] = $row[ $child_key ];
				unset( $row[ $child_key ] );
			}

			if ( preg_match( '/^i?\d+$/', (string) $row_key ) ) {
				$normalized[ $row_key ] = $row;
				continue;
			}

			// Skip past any index an untouched key already occupies.
			while ( isset( $rows[ $next_index ] ) || isset( $rows[ 'i' . $next_index ] ) ) {
				++$next_index;
			}

			$normalized[ 'i' . $next_index ] = $row;
			++$next_index;
		}//end foreach

		$leading = array(
			'form'    => $rows['form'] ?? $child_form_id,
			'row_ids' => array_keys( $normalized ),
		);

		$rows = $leading + $normalized;
	}

	/**
	 * Run a submitted value through the field's own import conversion.
	 *
	 * @since x.x
	 *
	 * @param stdClass $field The field the value belongs to.
	 * @param mixed    $value The value, by reference.
	 *
	 * @return void
	 */
	private static function format_field_value( $field, &$value ) {
		if ( ! is_callable( 'FrmFieldFactory::get_field_object' ) ) {
			return;
		}

		$field_object = FrmFieldFactory::get_field_object( $field );
		$value        = $field_object->get_import_value( $value, array( 'ids' => array() ) );
	}

	/**
	 * Turn a submitted file value into the attachment ID a file field stores.
	 *
	 * @since x.x
	 *
	 * @param mixed    $value The value, by reference.
	 * @param stdClass $field The file field.
	 *
	 * @return void
	 */
	private static function format_file_id( &$value, $field ) {
		if ( is_callable( 'FrmProFileImport::import_attachment' ) && is_object( $field ) ) {
			$_REQUEST['csv_files'] = 1;
			$value                 = FrmProFileImport::import_attachment( $value, $field );
		} else {
			$field_object = FrmFieldFactory::get_field_type( 'file' );

			// get_file_id() belongs to Pro's file field. Without Pro the type
			// resolves to a class that does not have it, and the value is stored
			// as sent.
			if ( is_callable( array( $field_object, 'get_file_id' ) ) ) {
				$value = $field_object->get_file_id( $value );
			}
		}

		if ( is_array( $value ) || ! strpos( (string) $value, ',' ) ) {
			return;
		}

		$ids = array_filter( explode( ',', $value ), 'is_numeric' );

		if ( $ids && count( $ids ) > 1 ) {
			$value = $ids;
		}
	}

	/**
	 * Treat the fields nobody can answer over an ability as hidden.
	 *
	 * A captcha has no challenge to solve without a browser, and a password
	 * field's confirmation cannot be typed, so validating either would fail every
	 * create on a form that has one.
	 *
	 * @since x.x
	 * @see filter hook frm_is_field_hidden
	 *
	 * @param bool         $hidden Whether the field is treated as hidden.
	 * @param array|object $field  Field data.
	 *
	 * @return bool
	 */
	public static function skip_unanswerable_field( $hidden, $field ) {
		return in_array( FrmField::get_field_type( $field ), array( 'captcha', 'password' ), true ) ? true : $hidden;
	}

	/**
	 * Build the response shape for one entry.
	 *
	 * @since x.x
	 *
	 * @param stdClass $entry The entry to describe.
	 *
	 * @return array
	 */
	public static function prepare_entry_for_response( $entry ) {
		return array(
			'id'             => (int) $entry->id,
			'item_key'       => (string) $entry->item_key,
			'name'           => (string) $entry->name,
			'ip'             => (string) $entry->ip,
			'meta'           => $entry->meta,
			'form_id'        => (int) $entry->form_id,
			'post_id'        => (int) $entry->post_id,
			'user_id'        => (int) $entry->user_id,
			'parent_item_id' => (int) $entry->parent_item_id,
			'is_draft'       => (int) $entry->is_draft,
			'updated_by'     => (int) $entry->updated_by,
			'created_at'     => (string) $entry->created_at,
			'updated_at'     => (string) $entry->updated_at,
		);
	}

	/**
	 * Build the error returned when no entry matches the given ID.
	 *
	 * @since x.x
	 *
	 * @return WP_Error
	 */
	private static function get_invalid_entry_error() {
		return new WP_Error( 'frm_entry_invalid_id', __( 'Nothing was found with that id.', 'formidable' ), array( 'status' => 404 ) );
	}

	/**
	 * Permission callback for list entries.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_list_entries( $input ) {
		return current_user_can( 'frm_view_entries' ) || current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for get entry.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_get_entry( $input ) {
		return current_user_can( 'frm_view_entries' ) || current_user_can( 'administrator' );
	}

	/**
	 * Permission callback for delete entry.
	 *
	 * @since x.x
	 *
	 * @param array $input Ability input parameters.
	 *
	 * @return bool
	 */
	public static function can_delete_entry( $input ) {
		return current_user_can( 'frm_delete_entries' ) || current_user_can( 'administrator' );
	}
}
