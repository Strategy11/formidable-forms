<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmXMLHelper {

	/**
	 * @var bool $installing_template true if importing an XML from API, false if importing an XML file manually.
	 */
	private static $installing_template = false;

	public static function get_xml_values( $opt, $padding ) {
		if ( is_array( $opt ) ) {
			foreach ( $opt as $ok => $ov ) {
				echo "\n" . esc_html( $padding );
				$tag = ( is_numeric( $ok ) ? 'key:' : '' ) . $ok;
				echo '<' . esc_html( $tag ) . '>';
				self::get_xml_values( $ov, $padding . '    ' );
				if ( is_array( $ov ) ) {
					echo "\n" . esc_html( $padding );
				}
				echo '</' . esc_html( $tag ) . '>';
			}
		} else {
			echo self::cdata( $opt ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	public static function import_xml( $file ) {
		if ( ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true );
		}

		if ( ! class_exists( 'DOMDocument' ) ) {
			return new WP_Error( 'SimpleXML_parse_error', __( 'Your server does not have XML enabled', 'formidable' ), libxml_get_errors() );
		}

		$xml_string = file_get_contents( $file );
		self::maybe_fix_xml( $xml_string );

		$dom = new DOMDocument();

		// LIBXML_COMPACT activates small nodes allocation optimization.
		// Use LIBXML_PARSEHUGE to avoid "parser error : internal error: Huge input lookup" for large (300MB) files.
		$success = $dom->loadXML( $xml_string, LIBXML_COMPACT | LIBXML_PARSEHUGE );
		if ( ! $success ) {
			return new WP_Error( 'SimpleXML_parse_error', __( 'There was an error when reading this XML file', 'formidable' ), libxml_get_errors() );
		}

		if ( ! function_exists( 'simplexml_import_dom' ) ) {
			return new WP_Error( 'SimpleXML_parse_error', __( 'Your server is missing the simplexml_import_dom function', 'formidable' ), libxml_get_errors() );
		}

		$xml = simplexml_import_dom( $dom );
		unset( $dom );

		// halt if loading produces an error
		if ( ! $xml ) {
			return new WP_Error( 'SimpleXML_parse_error', __( 'There was an error when reading this XML file', 'formidable' ), libxml_get_errors() );
		}

		return self::import_xml_now( $xml );
	}

	/**
	 * @since 6.2.3
	 *
	 * @param string $xml_string
	 * @return void
	 */
	private static function maybe_fix_xml( &$xml_string ) {
		if ( '<?xml' !== substr( $xml_string, 0, 5 ) ) {
			// Some XML files have may have unexpected characters at the start.
			$xml_string = substr( $xml_string, strpos( $xml_string, '<?xml' ) );
		}

		// The Equity theme adds a <meta name="generator" content="Equity 1.7.13" /> tag using the "the_generator" filter.
		// Strip that out as it breaks the XML import.
		$channel_start_position     = strpos( $xml_string, '<channel>' );
		$content_before_channel_tag = substr( $xml_string, 0, $channel_start_position );
		if ( 0 !== strpos( $content_before_channel_tag, '<meta name="generator" ' ) ) {
			$content_before_channel_tag = preg_replace(
				'/<meta\s+[^>]*name="generator"[^>]*\/>/i',
				'',
				$content_before_channel_tag,
				1
			);
			$xml_string = $content_before_channel_tag . substr( $xml_string, $channel_start_position );
		}
	}

	/**
	 * Add terms, forms (form and field ids), posts (post ids), and entries to db, in that order
	 *
	 * @since 3.06
	 *
	 * @param object $xml
	 * @param bool   $installing_template
	 * @return array The number of items imported
	 */
	public static function import_xml_now( $xml, $installing_template = false ) {
		if ( ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true );
		}

		self::$installing_template = $installing_template;
		$imported                  = self::pre_import_data();

		foreach ( array( 'term', 'form', 'view' ) as $item_type ) {
			// Grab cats, tags, and terms, or forms or posts.
			if ( isset( $xml->{$item_type} ) ) {
				$function_name = 'import_xml_' . $item_type . 's';
				$imported      = self::$function_name( $xml->{$item_type}, $imported );
				unset( $function_name, $xml->{$item_type} );
			}
		}

		$imported = apply_filters( 'frm_importing_xml', $imported, $xml );

		if ( ! isset( $imported['form_status'] ) || empty( $imported['form_status'] ) ) {
			// Check for an error message in the XML.
			if ( isset( $xml->Code ) && isset( $xml->Message ) ) { // phpcs:ignore WordPress.NamingConventions
				$imported['error'] = (string) $xml->Message; // phpcs:ignore WordPress.NamingConventions
			}
		}

		return $imported;
	}

	/**
	 * @since 3.06
	 * @return array
	 */
	private static function pre_import_data() {
		$defaults = array(
			'forms'   => 0,
			'fields'  => 0,
			'terms'   => 0,
			'posts'   => 0,
			'views'   => 0,
			'actions' => 0,
			'styles'  => 0,
		);

		return array(
			'imported' => $defaults,
			'updated'  => $defaults,
			'forms'    => array(),
			'terms'    => array(),
		);
	}

	public static function import_xml_terms( $terms, $imported ) {
		foreach ( $terms as $t ) {
			if ( term_exists( (string) $t->term_slug, (string) $t->term_taxonomy ) ) {
				continue;
			}

			$parent = self::get_term_parent_id( $t );

			$term = wp_insert_term(
				(string) $t->term_name,
				(string) $t->term_taxonomy,
				array(
					'slug'        => (string) $t->term_slug,
					'description' => (string) $t->term_description,
					'parent'      => empty( $parent ) ? 0 : $parent,
				)
			);

			if ( $term && is_array( $term ) ) {
				$imported['imported']['terms'] ++;
				$imported['terms'][ (int) $t->term_id ] = $term['term_id'];
			}

			unset( $term, $t );
		}

		return $imported;
	}

	/**
	 * @since 2.0.8
	 */
	private static function get_term_parent_id( $t ) {
		$parent = (string) $t->term_parent;
		if ( ! empty( $parent ) ) {
			$parent = term_exists( (string) $t->term_parent, (string) $t->term_taxonomy );
			if ( $parent ) {
				$parent = $parent['term_id'];
			} else {
				$parent = 0;
			}
		}

		return $parent;
	}

	public static function import_xml_forms( $forms, $imported ) {
		$child_forms = array();

		// Import child forms first
		self::put_child_forms_first( $forms );

		foreach ( $forms as $item ) {
			$form = self::fill_form( $item );

			self::update_custom_style_setting_on_import( $form );

			$this_form = self::maybe_get_form( $form );

			$old_id      = false;
			$form_fields = false;
			if ( ! empty( $this_form ) ) {
				$form_id = $this_form->id;
				$old_id  = $this_form->id;
				self::update_form( $this_form, $form, $imported );

				$form_fields = self::get_form_fields( $form_id );
			} else {
				$form_id = FrmForm::create( $form );
				if ( $form_id ) {
					if ( empty( $form['parent_form_id'] ) ) {
						// Don't include the repeater form in the imported count.
						$imported['imported']['forms'] ++;
					}

					// Keep track of whether this specific form was updated or not.
					$imported['form_status'][ $form_id ] = 'imported';
				}
			}

			if ( $form_id ) {
				self::track_imported_child_forms( (int) $form_id, $form['parent_form_id'], $child_forms );
			}

			self::import_xml_fields( $item->field, $form_id, $this_form, $form_fields, $imported );

			self::delete_removed_fields( $form_fields );

			// Update field ids/keys to new ones.
			do_action( 'frm_after_duplicate_form', $form_id, $form, array( 'old_id' => $old_id ) );

			$imported['forms'][ (int) $item->id ] = $form_id;

			// Send pre 2.0 form options through function that creates actions.
			self::migrate_form_settings_to_actions( $form['options'], $form_id, $imported, true );

			do_action( 'frm_after_import_form', $form_id, $form );

			unset( $form, $item );
		}

		self::maybe_update_child_form_parent_id( $imported['forms'], $child_forms );

		return $imported;
	}

	private static function fill_form( $item ) {
		$form = array(
			'id'             => (int) $item->id,
			'form_key'       => (string) $item->form_key,
			'name'           => (string) $item->name,
			'description'    => (string) $item->description,
			'options'        => (string) $item->options,
			'logged_in'      => (int) $item->logged_in,
			'is_template'    => (int) $item->is_template,
			'editable'       => (int) $item->editable,
			'status'         => (string) $item->status,
			'parent_form_id' => isset( $item->parent_form_id ) ? (int) $item->parent_form_id : 0,
			'created_at'     => gmdate( 'Y-m-d H:i:s', strtotime( (string) $item->created_at ) ),
		);

		if ( empty( $item->created_at ) ) {
			$form['created_at'] = current_time( 'mysql', 1 );
		}

		$form['options'] = FrmAppHelper::maybe_json_decode( $form['options'] );

		if ( self::$installing_template ) {
			// Templates don't necessarily have antispam on, but we want our templates to all have antispam on by default.
			$form['options']['antispam'] = 1;
		}

		return $form;
	}

	private static function maybe_get_form( $form ) {
		// if template, allow to edit if form keys match, otherwise, creation date must also match
		$edit_query = array(
			'form_key'    => $form['form_key'],
			'is_template' => $form['is_template'],
		);
		if ( ! $form['is_template'] ) {
			$edit_query['created_at'] = $form['created_at'];
		}

		$edit_query = apply_filters( 'frm_match_xml_form', $edit_query, $form );

		return FrmForm::getAll( $edit_query, '', 1 );
	}

	private static function update_form( $this_form, $form, &$imported ) {
		$form_id = $this_form->id;
		FrmForm::update( $form_id, $form );
		if ( empty( $form['parent_form_id'] ) ) {
			// Don't include the repeater form in the updated count.
			$imported['updated']['forms'] ++;
		}

		// Keep track of whether this specific form was updated or not
		$imported['form_status'][ $form_id ] = 'updated';
	}

	private static function get_form_fields( $form_id ) {
		$form_fields = FrmField::get_all_for_form( $form_id, '', 'exclude', 'exclude' );
		$old_fields  = array();
		foreach ( $form_fields as $f ) {
			$old_fields[ $f->id ]        = $f;
			$old_fields[ $f->field_key ] = $f->id;
			unset( $f );
		}
		$form_fields = $old_fields;

		return $form_fields;
	}

	/**
	 * Delete any fields attached to this form that were not included in the template
	 */
	private static function delete_removed_fields( $form_fields ) {
		if ( ! empty( $form_fields ) ) {
			foreach ( $form_fields as $field ) {
				if ( is_object( $field ) ) {
					FrmField::destroy( $field->id );
				}
				unset( $field );
			}
		}
	}

	/**
	 * Put child forms first so they will be imported before parents
	 *
	 * @since 2.0.16
	 *
	 * @param array $forms
	 */
	private static function put_child_forms_first( &$forms ) {
		$child_forms   = array();
		$regular_forms = array();

		foreach ( $forms as $form ) {
			$parent_form_id = isset( $form->parent_form_id ) ? (int) $form->parent_form_id : 0;

			if ( $parent_form_id ) {
				$child_forms[] = $form;
			} else {
				$regular_forms[] = $form;
			}
		}

		$forms = array_merge( $child_forms, $regular_forms );
	}

	/**
	 * Keep track of all imported child forms
	 *
	 * @since 2.0.16
	 *
	 * @param int $form_id
	 * @param int $parent_form_id
	 * @param array $child_forms
	 */
	private static function track_imported_child_forms( $form_id, $parent_form_id, &$child_forms ) {
		if ( $parent_form_id ) {
			$child_forms[ $form_id ] = $parent_form_id;
		}
	}

	/**
	 * Update the parent_form_id on imported child forms
	 * Child forms are imported first so their parent_form_id will need to be updated after the parent is imported
	 *
	 * @since 2.0.6
	 *
	 * @param array $imported_forms
	 * @param array $child_forms
	 */
	private static function maybe_update_child_form_parent_id( $imported_forms, $child_forms ) {
		foreach ( $child_forms as $child_form_id => $old_parent_form_id ) {
			if ( isset( $imported_forms[ $old_parent_form_id ] ) && (int) $imported_forms[ $old_parent_form_id ] !== (int) $old_parent_form_id ) {
				// Update all children with this old parent_form_id
				$new_parent_form_id = (int) $imported_forms[ $old_parent_form_id ];
				FrmForm::update( $child_form_id, array( 'parent_form_id' => $new_parent_form_id ) );
				do_action( 'frm_update_child_form_parent_id', $child_form_id, $new_parent_form_id );
			}
		}
	}

	/**
	 * Import all fields for a form
	 *
	 * @since 2.0.13
	 *
	 * TODO: Cut down on params
	 */
	private static function import_xml_fields( $xml_fields, $form_id, $this_form, &$form_fields, &$imported ) {
		$in_section                = 0;
		$keys_by_original_field_id = array();

		foreach ( $xml_fields as $field ) {
			$f = self::fill_field( $field, $form_id );

			self::set_default_value( $f );
			self::maybe_add_required( $f );
			self::maybe_update_in_section_variable( $in_section, $f );
			self::maybe_update_form_select( $f, $imported );
			self::maybe_update_get_values_form_setting( $imported, $f );
			self::migrate_placeholders( $f );

			if ( ! empty( $this_form ) ) {
				// check for field to edit by field id
				if ( isset( $form_fields[ $f['id'] ] ) ) {
					FrmField::update( $f['id'], $f );
					$imported['updated']['fields'] ++;

					unset( $form_fields[ $f['id'] ] );

					//unset old field key
					if ( isset( $form_fields[ $f['field_key'] ] ) ) {
						unset( $form_fields[ $f['field_key'] ] );
					}
				} elseif ( isset( $form_fields[ $f['field_key'] ] ) ) {
					$keys_by_original_field_id[ $f['id'] ] = $f['field_key'];

					// check for field to edit by field key
					unset( $f['id'] );

					FrmField::update( $form_fields[ $f['field_key'] ], $f );
					$imported['updated']['fields'] ++;

					unset( $form_fields[ $form_fields[ $f['field_key'] ] ] ); //unset old field id
					unset( $form_fields[ $f['field_key'] ] ); //unset old field key
				} else {
					// if no matching field id or key in this form, create the field
					self::create_imported_field( $f, $imported );
				}
			} else {

				self::create_imported_field( $f, $imported );
			}
		}

		if ( $keys_by_original_field_id ) {
			self::maybe_update_field_ids( $form_id, $keys_by_original_field_id );
		}
	}

	private static function fill_field( $field, $form_id ) {
		return array(
			'id'            => (int) $field->id,
			'field_key'     => (string) $field->field_key,
			'name'          => (string) $field->name,
			'description'   => (string) $field->description,
			'type'          => (string) $field->type,
			'default_value' => FrmAppHelper::maybe_json_decode( (string) $field->default_value ),
			'field_order'   => (int) $field->field_order,
			'form_id'       => (int) $form_id,
			'required'      => (int) $field->required,
			'options'       => FrmAppHelper::maybe_json_decode( (string) $field->options ),
			'field_options' => FrmAppHelper::maybe_json_decode( (string) $field->field_options ),
		);
	}

	/**
	 * @since 4.06
	 */
	private static function set_default_value( &$f ) {
		$has_default = array(
			'text',
			'email',
			'url',
			'textarea',
			'number',
			'phone',
			'date',
			'hidden',
			'password',
			'tag',
		);

		if ( is_array( $f['default_value'] ) && in_array( $f['type'], $has_default, true ) ) {
			if ( count( $f['default_value'] ) === 1 ) {
				$f['default_value'] = '[' . reset( $f['default_value'] ) . ']';
			} else {
				$f['default_value'] = reset( $f['default_value'] );
			}
		}
	}

	/**
	 * Make sure the required indicator is set.
	 *
	 * @since 4.05
	 */
	private static function maybe_add_required( &$f ) {
		if ( $f['required'] && ! isset( $f['field_options']['required_indicator'] ) ) {
			$f['field_options']['required_indicator'] = '*';
		}
	}

	/**
	 * Update the current in_section value at the beginning of the field loop
	 *
	 * @since 2.0.25
	 * @param int $in_section
	 * @param array $f
	 */
	private static function maybe_update_in_section_variable( &$in_section, &$f ) {
		// If we're at the end of a section, switch $in_section is 0
		if ( in_array( $f['type'], array( 'end_divider', 'break', 'form' ) ) ) {
			$in_section = 0;
		}

		// Update the current field's in_section value
		if ( ! isset( $f['field_options']['in_section'] ) ) {
			$f['field_options']['in_section'] = $in_section;
		}

		// If we're starting a new section, switch $in_section to ID of divider
		if ( $f['type'] == 'divider' ) {
			$in_section = $f['id'];
		}
	}

	/**
	 * Switch the form_select on a repeating field or embedded form if it needs to be switched
	 *
	 * @since 2.0.16
	 *
	 * @param array $f
	 * @param array $imported
	 */
	private static function maybe_update_form_select( &$f, $imported ) {
		if ( ! isset( $imported['forms'] ) ) {
			return;
		}

		if ( $f['type'] == 'form' || ( $f['type'] == 'divider' && FrmField::is_option_true( $f['field_options'], 'repeat' ) ) ) {
			if ( FrmField::is_option_true( $f['field_options'], 'form_select' ) ) {
				$form_select = (int) $f['field_options']['form_select'];
				if ( isset( $imported['forms'][ $form_select ] ) ) {
					$f['field_options']['form_select'] = $imported['forms'][ $form_select ];
				}
			}
		}
	}

	/**
	 * Update the get_values_form setting if the form was imported
	 *
	 * @since 2.01.0
	 *
	 * @param array $imported
	 * @param array $f
	 */
	private static function maybe_update_get_values_form_setting( $imported, &$f ) {
		if ( ! isset( $imported['forms'] ) ) {
			return;
		}

		if ( FrmField::is_option_true_in_array( $f['field_options'], 'get_values_form' ) ) {
			$old_form = $f['field_options']['get_values_form'];
			if ( isset( $imported['forms'][ $old_form ] ) ) {
				$f['field_options']['get_values_form'] = $imported['forms'][ $old_form ];
			}
		}
	}

	/**
	 * If field settings have been migrated, update the values during import.
	 *
	 * @since 4.0
	 */
	private static function run_field_migrations( &$f ) {
		self::migrate_placeholders( $f );
		$f = apply_filters( 'frm_import_xml_field', $f );
	}

	/**
	 * @since 4.0
	 */
	private static function migrate_placeholders( &$f ) {
		$update_values = self::migrate_field_placeholder( $f, 'clear_on_focus' );
		foreach ( $update_values as $k => $v ) {
			$f[ $k ] = $v;
		}

		$update_values = self::migrate_field_placeholder( $f, 'default_blank' );
		foreach ( $update_values as $k => $v ) {
			$f[ $k ] = $v;
		}
	}

	/**
	 * Move clear_on_focus or default_blank to placeholder.
	 * Also called during database migration in FrmMigrate.
	 *
	 * @since 4.0
	 * @return array
	 */
	public static function migrate_field_placeholder( $field, $type ) {
		$field = (array) $field;
		$field_options = $field['field_options'];
		if ( empty( $field_options[ $type ] ) || empty( $field['default_value'] ) ) {
			return array();
		}

		$field_options['placeholder'] = is_array( $field['default_value'] ) ? reset( $field['default_value'] ) : $field['default_value'];
		unset( $field_options['default_blank'], $field_options['clear_on_focus'] );

		$changes = array(
			'field_options' => $field_options,
			'default_value' => '',
		);

		// If a dropdown placeholder was used, remove the option so it won't be included twice.
		$options = $field['options'];
		if ( $type === 'default_blank' && is_array( $options ) ) {
			$default_value = $field['default_value'];
			if ( is_array( $default_value ) ) {
				$default_value = reset( $default_value );
			}

			foreach ( $options as $opt_key => $opt ) {
				if ( is_array( $opt ) ) {
					$opt = isset( $opt['value'] ) ? $opt['value'] : ( isset( $opt['label'] ) ? $opt['label'] : reset( $opt ) );
				}

				if ( $opt == $default_value ) {
					unset( $options[ $opt_key ] );
					break;
				}
			}
			$changes['options'] = $options;
		}

		return $changes;
	}

	/**
	 * Create an imported field
	 *
	 * @since 2.0.25
	 *
	 * @param array $f
	 * @param array $imported
	 */
	private static function create_imported_field( $f, &$imported ) {
		$defaults           = self::default_field_options( $f['type'] );
		$f['field_options'] = array_merge( $defaults, $f['field_options'] );

		if ( is_callable( 'FrmProFileImport::import_attachment' ) ) {
			$f = self::maybe_import_images_for_options( $f );
		}

		$new_id = FrmField::create( $f );
		if ( $new_id != false ) {
			$imported['imported']['fields'] ++;
			do_action( 'frm_after_field_is_imported', $f, $new_id );
		}
	}

	/**
	 * Import images for radio buttons and checkboxes from image src if available.
	 *
	 * @since 5.5.1
	 *
	 * @param array $field
	 * @return array
	 */
	private static function maybe_import_images_for_options( $field ) {
		if ( empty( $field['options'] ) || ! is_array( $field['options'] ) ) {
			return $field;
		}

		foreach ( $field['options'] as $key => $option ) {
			if ( ! is_array( $option ) || empty( $option['src'] ) ) {
				continue;
			}

			$field_object       = (object) $field;
			$field_object->type = 'file'; // Fake the file type as FrmProImport::import_attachment checks for file type.

			$image_id = FrmProFileImport::import_attachment( $option['src'], $field_object );
			unset( $field['options'][ $key ]['src'] ); // Remove the src from options as it isn't required after import.

			if ( is_numeric( $image_id ) ) {
				$field['options'][ $key ]['image'] = $image_id;
			}
		}

		return $field;
	}

	/**
	 * Fix field ids for fields that already exist prior to import.
	 *
	 * @since 4.07
	 * @param int $form_id
	 * @param array $keys_by_original_field_id
	 */
	protected static function maybe_update_field_ids( $form_id, $keys_by_original_field_id ) {
		global $frm_duplicate_ids;

		$former_duplicate_ids = $frm_duplicate_ids;
		$where                = array(
			array(
				'or'                => 1,
				'fi.form_id'        => $form_id,
				'fr.parent_form_id' => $form_id,
			),
		);
		$fields               = FrmField::getAll( $where, 'field_order' );
		$field_id_by_key      = wp_list_pluck( $fields, 'id', 'field_key' );

		foreach ( $fields as $field ) {
			$before            = (array) clone $field;
			$field             = (array) $field;
			$frm_duplicate_ids = $keys_by_original_field_id;
			$after             = FrmFieldsHelper::switch_field_ids( $field );

			if ( $before['field_options'] !== $after['field_options'] ) {
				$frm_duplicate_ids = $field_id_by_key;
				$after             = FrmFieldsHelper::switch_field_ids( $after );

				if ( $before['field_options'] !== $after['field_options'] ) {
					FrmField::update( $field['id'], array( 'field_options' => $after['field_options'] ) );
				}
			}
		}

		$frm_duplicate_ids = $former_duplicate_ids;
	}

	/**
	 * Updates the custom style setting on import
	 * Convert the post slug to an ID
	 *
	 * @since 2.0.19
	 *
	 * @param array $form
	 */
	private static function update_custom_style_setting_on_import( &$form ) {
		if ( ! isset( $form['options']['custom_style'] ) ) {
			return;
		}

		if ( is_numeric( $form['options']['custom_style'] ) && 1 === intval( $form['options']['custom_style'] ) ) {
			// Set to default
			$form['options']['custom_style'] = 1;
		} else {
			// Replace the style name with the style ID on import
			global $wpdb;
			$table    = $wpdb->prefix . 'posts';
			$where    = array(
				'post_name' => $form['options']['custom_style'],
				'post_type' => 'frm_styles',
			);
			$select   = 'ID';
			$style_id = FrmDb::get_var( $table, $where, $select );

			if ( $style_id ) {
				$form['options']['custom_style'] = $style_id;
			} else {
				// save the old style to maybe update after styles import
				$form['options']['old_style'] = $form['options']['custom_style'];

				// Set to default
				$form['options']['custom_style'] = 1;
			}
		}
	}

	/**
	 * After styles are imported, check for any forms that were linked
	 * and link them back up.
	 *
	 * @since 2.2.7
	 */
	private static function update_custom_style_setting_after_import( $form_id ) {
		$form = FrmForm::getOne( $form_id );

		if ( $form && isset( $form->options['old_style'] ) ) {
			$form                            = (array) $form;
			$saved_style                     = $form['options']['custom_style'];
			$form['options']['custom_style'] = $form['options']['old_style'];
			self::update_custom_style_setting_on_import( $form );
			$has_changed = ( $form['options']['custom_style'] != $saved_style && $form['options']['custom_style'] != $form['options']['old_style'] );
			if ( $has_changed ) {
				FrmForm::update( $form['id'], $form );
			}
		}
	}

	public static function import_xml_views( $views, $imported ) {
		$imported['posts'] = array();
		$form_action_type  = FrmFormActionsController::$action_post_type;

		$post_types = array(
			'frm_display'     => 'views',
			$form_action_type => 'actions',
			'frm_styles'      => 'styles',
		);

		$view_ids              = array();
		$posts_with_shortcodes = array();

		foreach ( $views as $item ) {
			$post = array(
				'post_title'     => (string) $item->title,
				'post_name'      => (string) $item->post_name,
				'post_type'      => (string) $item->post_type,
				'post_password'  => (string) $item->post_password,
				'guid'           => (string) $item->guid,
				'post_status'    => (string) $item->status,
				'post_author'    => FrmAppHelper::get_user_id_param( (string) $item->post_author ),
				'post_id'        => (int) $item->post_id,
				'post_parent'    => (int) $item->post_parent,
				'menu_order'     => (int) $item->menu_order,
				'post_content'   => FrmFieldsHelper::switch_field_ids( (string) $item->content ),
				'post_excerpt'   => FrmFieldsHelper::switch_field_ids( (string) $item->excerpt ),
				'is_sticky'      => (string) $item->is_sticky,
				'comment_status' => (string) $item->comment_status,
				'post_date'      => (string) $item->post_date,
				'post_date_gmt'  => (string) $item->post_date_gmt,
				'ping_status'    => (string) $item->ping_status,
				'postmeta'       => array(),
				'layout'         => array(),
				'tax_input'      => array(),
			);

			$post['post_content'] = self::switch_form_ids( $post['post_content'], $imported['forms'] );

			$old_id = $post['post_id'];
			self::populate_post( $post, $item, $imported );

			unset( $item );

			$post_id = false;
			if ( $post['post_type'] === $form_action_type ) {
				$action_control = FrmFormActionsController::get_form_actions( $post['post_excerpt'] );
				if ( $action_control && is_object( $action_control ) ) {
					$post_id = $action_control->maybe_create_action( $post, $imported['form_status'] );
				}
				unset( $action_control );
			} elseif ( $post['post_type'] === 'frm_styles' ) {
				// Properly encode post content before inserting the post
				$post['post_content'] = FrmAppHelper::maybe_json_decode( $post['post_content'] );
				$post['post_content'] = FrmAppHelper::prepare_and_encode( $post['post_content'] );

				// Create/update post now
				$post_id = wp_insert_post( $post );
			} else {
				if ( $post['post_type'] === 'frm_display' ) {
					$post['post_content'] = self::maybe_prepare_json_view_content( $post['post_content'] );
				} elseif ( 'page' === $post['post_type'] && isset( $imported['posts'][ $post['post_parent'] ] ) ) {
					$post['post_parent'] = $imported['posts'][ $post['post_parent'] ];
				}
				// Create/update post now
				$post_id = wp_insert_post( $post );
			}

			if ( ! is_numeric( $post_id ) ) {
				continue;
			}

			if ( false !== strpos( $post['post_content'], '[display-frm-data' ) || false !== strpos( $post['post_content'], '[formidable' ) ) {
				$posts_with_shortcodes[ $post_id ] = $post;
			}

			self::update_postmeta( $post, $post_id );
			self::update_layout( $post, $post_id );

			$this_type = 'posts';
			if ( isset( $post_types[ $post['post_type'] ] ) ) {
				$this_type = $post_types[ $post['post_type'] ];
			}

			if ( isset( $post['ID'] ) && $post_id == $post['ID'] ) {
				$imported['updated'][ $this_type ] ++;
			} else {
				$imported['imported'][ $this_type ] ++;
			}

			$imported['posts'][ (int) $old_id ] = $post_id;

			if ( $post['post_type'] === 'frm_display' ) {
				$view_ids[ (int) $old_id ] = $post_id;
			}

			do_action( 'frm_after_import_view', $post_id, $post );

			unset( $post );
		}

		if ( $posts_with_shortcodes && $view_ids ) {
			self::maybe_switch_view_ids_after_importing_posts( $posts_with_shortcodes, $view_ids );
		}
		unset( $posts_with_shortcodes, $view_ids );

		if ( ! empty( $imported['forms'] ) ) {
			// clear imported forms style cache to make sure the new styles are applied to the forms
			self::clear_forms_style_caches( $imported['forms'] );
		}

		self::maybe_update_stylesheet( $imported );

		flush_rewrite_rules();

		return $imported;
	}

	/**
	 * Clears styles from cache for imported forms
	 *
	 * @param array $imported_forms
	 */
	private static function clear_forms_style_caches( $imported_forms ) {
		$where = array(
			'id' => $imported_forms,
			'options LIKE' => '"old_style"',
		);
		$forms = FrmDb::get_results( 'frm_forms', $where );

		foreach ( $forms as $form ) {
			FrmAppHelper::unserialize_or_decode( $form->options );
			if ( ! $form->options ) {
				continue;
			}
			$where = array(
				'post_name' => $form->options['old_style'],
				'post_type' => FrmStylesController::$post_type,
			);

			$select = 'ID';

			$cache_key = FrmDb::generate_cache_key( $where, array( 'limit' => 1 ), $select, 'var' );
			FrmDb::delete_cache_and_transient( $cache_key, 'post' );
		}
	}

	/**
	 * Replace old form ids with new ones in a string.
	 *
	 * @param string     $string
	 * @param array<int> $form_ids new form ids indexed by old form id.
	 * @return string
	 */
	private static function switch_form_ids( $string, $form_ids ) {
		if ( false === strpos( $string, '[formidable' ) ) {
			// Skip string replacing if there are no form shortcodes in string.
			return $string;
		}

		foreach ( $form_ids as $old_id => $new_id ) {
			$string = str_replace(
				array(
					'[formidable id="' . $old_id . '"',
					'[formidable id=' . $old_id . ']',
					'[formidable id=' . $old_id . ' ',
					'"formId":"' . $old_id . '"',
				),
				array(
					'[formidable id="' . $new_id . '"',
					'[formidable id=' . $new_id . ']',
					'[formidable id=' . $new_id . ' ',
					'"formId":"' . $new_id . '"',
				),
				$string
			);
		}

		return $string;
	}

	/**
	 * @param array<array> $posts_with_shortcodes indexed by current post id.
	 * @param array<int>   $view_ids new view ids indexed by old view id.
	 * @return void
	 */
	private static function maybe_switch_view_ids_after_importing_posts( $posts_with_shortcodes, $view_ids ) {
		foreach ( $posts_with_shortcodes as $imported_post_id => $post ) {
			$post_content = self::switch_view_ids( $post['post_content'], $view_ids );
			if ( $post_content === $post['post_content'] ) {
				continue;
			}

			wp_update_post(
				array(
					'ID'           => $imported_post_id,
					'post_content' => $post_content,
				)
			);
		}
	}

	/**
	 * Replace old view ids with new ones in a string.
	 *
	 * @param string     $string
	 * @param array<int> $view_ids new view ids indexed by old view id.
	 * @return string
	 */
	private static function switch_view_ids( $string, $view_ids ) {
		if ( false === strpos( $string, '[display-frm-data' ) ) {
			// Skip string replacing if there are no view shortcodes in string.
			return $string;
		}

		foreach ( $view_ids as $old_id => $new_id ) {
			$string = str_replace(
				array(
					'[display-frm-data id="' . $old_id . '"',
					'[display-frm-data id=' . $old_id . ']',
					'[display-frm-data id=' . $old_id . ' ',
					'"viewId":"' . $old_id . '"',
				),
				array(
					'[display-frm-data id="' . $new_id . '"',
					'[display-frm-data id=' . $new_id . ']',
					'[display-frm-data id=' . $new_id . ' ',
					'"viewId":"' . $new_id . '"',
				),
				$string
			);
			unset( $old_id, $new_id );
		}

		return $string;
	}

	/**
	 * @param string $content
	 * @return string
	 */
	private static function maybe_prepare_json_view_content( $content ) {
		$maybe_decoded = FrmAppHelper::maybe_json_decode( $content );
		if ( is_array( $maybe_decoded ) && isset( $maybe_decoded[0] ) && isset( $maybe_decoded[0]['box'] ) ) {
			return FrmAppHelper::prepare_and_encode( $maybe_decoded );
		}
		return $content;
	}

	private static function populate_post( &$post, $item, $imported ) {
		if ( isset( $item->attachment_url ) ) {
			$post['attachment_url'] = (string) $item->attachment_url;
		}

		if ( $post['post_type'] == FrmFormActionsController::$action_post_type && isset( $imported['forms'][ (int) $post['menu_order'] ] ) ) {
			// update to new form id
			$post['menu_order'] = $imported['forms'][ (int) $post['menu_order'] ];
		}

		// Don't allow default styles to take over a site's default style
		if ( 'frm_styles' == $post['post_type'] ) {
			$post['menu_order'] = 0;
		}

		foreach ( $item->postmeta as $meta ) {
			self::populate_postmeta( $post, $meta, $imported );
			unset( $meta );
		}

		foreach ( $item->layout as $layout ) {
			self::populate_layout( $post, $layout );
			unset( $layout );
		}

		self::populate_taxonomies( $post, $item );

		self::maybe_editing_post( $post );
	}

	/**
	 * @param array    $post
	 * @param stdClass $meta
	 * @param array    $imported
	 */
	private static function populate_postmeta( &$post, $meta, $imported ) {
		global $frm_duplicate_ids;

		$m = array(
			'key'   => (string) $meta->meta_key,
			'value' => (string) $meta->meta_value,
		);

		//switch old form and field ids to new ones
		if ( 'frm_form_id' === $m['key'] && isset( $imported['forms'][ (int) $m['value'] ] ) ) {
			$m['value'] = $imported['forms'][ (int) $m['value'] ];
		} else {
			$m['value'] = FrmAppHelper::maybe_json_decode( $m['value'] );

			if ( ! empty( $frm_duplicate_ids ) ) {
				if ( 'frm_dyncontent' === $m['key'] ) {
					$m['value'] = self::maybe_prepare_json_view_content( $m['value'] );
					$m['value'] = FrmFieldsHelper::switch_field_ids( $m['value'] );
				} elseif ( 'frm_options' === $m['key'] ) {

					foreach ( array( 'date_field_id', 'edate_field_id' ) as $setting_name ) {
						if ( isset( $m['value'][ $setting_name ] ) && is_numeric( $m['value'][ $setting_name ] ) && isset( $frm_duplicate_ids[ $m['value'][ $setting_name ] ] ) ) {
							$m['value'][ $setting_name ] = $frm_duplicate_ids[ $m['value'][ $setting_name ] ];
						}
					}

					$check_dup_array = array();
					if ( isset( $m['value']['order_by'] ) && ! empty( $m['value']['order_by'] ) ) {
						if ( is_numeric( $m['value']['order_by'] ) && isset( $frm_duplicate_ids[ $m['value']['order_by'] ] ) ) {
							$m['value']['order_by'] = $frm_duplicate_ids[ $m['value']['order_by'] ];
						} elseif ( is_array( $m['value']['order_by'] ) ) {
							$check_dup_array[] = 'order_by';
						}
					}

					if ( isset( $m['value']['where'] ) && ! empty( $m['value']['where'] ) ) {
						$check_dup_array[] = 'where';
					}

					foreach ( $check_dup_array as $check_k ) {
						foreach ( (array) $m['value'][ $check_k ] as $mk => $mv ) {
							if ( isset( $frm_duplicate_ids[ $mv ] ) ) {
								$m['value'][ $check_k ][ $mk ] = $frm_duplicate_ids[ $mv ];
							}
							unset( $mk, $mv );
						}
					}
				}
			}
		}

		if ( ! is_array( $m['value'] ) ) {
			$m['value'] = FrmAppHelper::maybe_json_decode( $m['value'] );
		}

		$post['postmeta'][ (string) $meta->meta_key ] = $m['value'];
	}

	private static function populate_layout( &$post, $layout ) {
		$post['layout'][ (string) $layout->type ] = (string) $layout->data;
	}

	/**
	 * Add terms to post
	 *
	 * @param array $post by reference
	 * @param object $item The XML object data
	 */
	private static function populate_taxonomies( &$post, $item ) {
		foreach ( $item->category as $c ) {
			$att = $c->attributes();
			if ( ! isset( $att['nicename'] ) ) {
				continue;
			}

			$taxonomy = (string) $att['domain'];
			if ( is_taxonomy_hierarchical( $taxonomy ) ) {
				$name   = (string) $att['nicename'];
				$h_term = get_term_by( 'slug', $name, $taxonomy );
				if ( $h_term ) {
					$name = $h_term->term_id;
				}
				unset( $h_term );
			} else {
				$name = (string) $c;
			}

			if ( ! isset( $post['tax_input'][ $taxonomy ] ) ) {
				$post['tax_input'][ $taxonomy ] = array();
			}

			$post['tax_input'][ $taxonomy ][] = $name;
			unset( $name );
		}
	}

	/**
	 * Edit post if the key and created time match
	 */
	private static function maybe_editing_post( &$post ) {
		$match_by = array(
			'post_type'      => $post['post_type'],
			'name'           => $post['post_name'],
			'post_status'    => $post['post_status'],
			'posts_per_page' => 1,
		);

		if ( in_array( $post['post_status'], array( 'trash', 'draft' ) ) ) {
			$match_by['include'] = $post['post_id'];
			unset( $match_by['name'] );
		}

		$editing = get_posts( $match_by );

		if ( ! empty( $editing ) && current( $editing )->post_date == $post['post_date'] ) {
			// set the id of the post to edit
			$post['ID'] = current( $editing )->ID;
		}
	}

	/**
	 * @param array $post
	 * @param int   $post_id
	 * @return void
	 */
	private static function update_postmeta( &$post, $post_id ) {
		foreach ( $post['postmeta'] as $k => $v ) {
			switch ( $k ) {
				case '_edit_last':
					$v = FrmAppHelper::get_user_id_param( $v );
					break;

				case '_thumbnail_id':
					if ( FrmAppHelper::pro_is_installed() ) {
						// Change the attachment ID.
						$field_obj = FrmFieldFactory::get_field_type( 'file' );
						$v         = $field_obj->get_file_id( $v );
					}
					break;

				case 'frm_dyncontent':
					if ( is_array( $v ) ) {
						$v = json_encode( $v );
					}
					break;

				case 'frm_param':
					add_rewrite_endpoint( $v, EP_PERMALINK | EP_PAGES );
					break;
			}

			update_post_meta( $post_id, $k, $v );
			unset( $k, $v );
		}
	}

	/**
	 * @param array $post
	 * @param int   $post_id
	 */
	private static function update_layout( &$post, $post_id ) {
		if ( is_callable( 'FrmViewsLayout::maybe_create_layouts_for_view' ) ) {
			$listing_layout = ! empty( $post['layout']['listing'] ) ? json_decode( $post['layout']['listing'], true ) : array();
			$detail_layout  = ! empty( $post['layout']['detail'] ) ? json_decode( $post['layout']['detail'], true ) : array();
			if ( $listing_layout || $detail_layout ) {
				FrmViewsLayout::maybe_create_layouts_for_view( $post_id, $listing_layout, $detail_layout );
			}
		}
	}

	private static function maybe_update_stylesheet( $imported ) {
		$new_styles     = isset( $imported['imported']['styles'] ) && ! empty( $imported['imported']['styles'] );
		$updated_styles = isset( $imported['updated']['styles'] ) && ! empty( $imported['updated']['styles'] );
		if ( $new_styles || $updated_styles ) {
			if ( is_admin() && function_exists( 'get_filesystem_method' ) ) {
				$frm_style = new FrmStyle();
				$frm_style->update( 'default' );
			}
			foreach ( $imported['forms'] as $form_id ) {
				self::update_custom_style_setting_after_import( $form_id );
			}
		}
	}

	/**
	 * @param string $message
	 */
	public static function parse_message( $result, &$message, &$errors ) {
		if ( is_wp_error( $result ) ) {
			$errors[] = $result->get_error_message();
		} elseif ( ! $result ) {
			return;
		}

		if ( ! is_array( $result ) ) {
			$message = is_string( $result ) ? $result : htmlentities( print_r( $result, 1 ) );

			return;
		}

		$t_strings = array(
			'imported' => __( 'Imported', 'formidable' ),
			'updated'  => __( 'Updated', 'formidable' ),
		);

		$message = '<ul>';
		foreach ( $result as $type => $results ) {
			if ( ! isset( $t_strings[ $type ] ) ) {
				// only print imported and updated
				continue;
			}

			$s_message = array();
			foreach ( $results as $k => $m ) {
				self::item_count_message( $m, $k, $s_message );
				unset( $k, $m );
			}

			if ( ! empty( $s_message ) ) {
				$message .= '<li><strong>' . $t_strings[ $type ] . ':</strong> ';
				$message .= implode( ', ', $s_message );
				$message .= '</li>';
			}
		}

		if ( $message == '<ul>' ) {
			$message  = '';
			$errors[] = __( 'Nothing was imported or updated', 'formidable' );
		} else {
			self::add_form_link_to_message( $result, $message );

			/**
			 * @since 5.3
			 *
			 * @param string $message
			 * @param array  $result
			 */
			$message  = apply_filters( 'frm_xml_parsed_message', $message, $result );
			$message .= '</ul>';
		}
	}

	/**
	 * @param int           $m
	 * @param string        $type
	 * @param array<string> $s_message
	 */
	public static function item_count_message( $m, $type, &$s_message ) {
		if ( ! $m ) {
			return;
		}

		$strings = array(
			/* translators: %1$s: Number of items */
			'forms'   => sprintf( _n( '%1$s Form', '%1$s Forms', $m, 'formidable' ), $m ),
			/* translators: %1$s: Number of items */
			'fields'  => sprintf( _n( '%1$s Field', '%1$s Fields', $m, 'formidable' ), $m ),
			/* translators: %1$s: Number of items */
			'items'   => sprintf( _n( '%1$s Entry', '%1$s Entries', $m, 'formidable' ), $m ),
			/* translators: %1$s: Number of items */
			'views'   => sprintf( _n( '%1$s View', '%1$s Views', $m, 'formidable' ), $m ),
			/* translators: %1$s: Number of items */
			'posts'   => sprintf( _n( '%1$s Page/Post', '%1$s Pages/Posts', $m, 'formidable' ), $m ),
			/* translators: %1$s: Number of items */
			'styles'  => sprintf( _n( '%1$s Style', '%1$s Styles', $m, 'formidable' ), $m ),
			/* translators: %1$s: Number of items */
			'terms'   => sprintf( _n( '%1$s Term', '%1$s Terms', $m, 'formidable' ), $m ),
			/* translators: %1$s: Number of items */
			'actions' => sprintf( _n( '%1$s Form Action', '%1$s Form Actions', $m, 'formidable' ), $m ),
		);

		if ( isset( $strings[ $type ] ) ) {
			$s_message[] = $strings[ $type ];
		} else {
			$string = ' ' . $m . ' ' . ucfirst( $type );

			/**
			 * @since 5.3
			 *
			 * @param string $string Message string for imported item.
			 * @param int    $m      Number of item that was imported.
			 * }
			 */
			$string      = apply_filters( 'frm_xml_' . $type . '_count_message', $string, $m );
			$s_message[] = $string;
		}
	}

	/**
	 * If a single form was imported, include a link in the success message.
	 *
	 * @since 4.0
	 * @param array  $result The response from the XML import.
	 * @param string $message The response shown on the page after import.
	 */
	private static function add_form_link_to_message( $result, &$message ) {
		$total_forms = $result['imported']['forms'] + $result['updated']['forms'];
		if ( $total_forms > 1 ) {
			return;
		}

		$primary_form = reset( $result['forms'] );
		if ( ! empty( $primary_form ) ) {
			$primary_form = FrmForm::getOne( $primary_form );
			$form_id      = empty( $primary_form->parent_form_id ) ? $primary_form->id : $primary_form->parent_form_id;

			$message .= '<li><a href="' . esc_url( FrmForm::get_edit_link( $form_id ) ) . '">' . esc_html__( 'Go to imported form', 'formidable' ) . '</a></li>';
		}
	}

	/**
	 * Prepare the form options for export
	 *
	 * @since 2.0.19
	 *
	 * @param string $options
	 *
	 * @return string
	 */
	public static function prepare_form_options_for_export( $options ) {
		FrmAppHelper::unserialize_or_decode( $options );
		// Change custom_style to the post_name instead of ID (1 may be a string)
		$not_default = isset( $options['custom_style'] ) && 1 != $options['custom_style'];
		if ( $not_default ) {
			global $wpdb;
			$table  = $wpdb->prefix . 'posts';
			$where  = array( 'ID' => $options['custom_style'] );
			$select = 'post_name';

			$style_name = FrmDb::get_var( $table, $where, $select );

			if ( $style_name ) {
				$options['custom_style'] = $style_name;
			} else {
				$options['custom_style'] = 1;
			}
		}
		self::remove_default_form_options( $options );
		$options = serialize( $options );

		return self::cdata( $options );
	}

	/**
	 * If the saved value is the same as the default, remove it from the export
	 * This keeps file size down and prevents overriding global settings after import
	 *
	 * @since 3.06
	 */
	private static function remove_default_form_options( &$options ) {
		$defaults = FrmFormsHelper::get_default_opts();
		if ( is_callable( 'FrmProFormsHelper::get_default_opts' ) ) {
			$defaults += FrmProFormsHelper::get_default_opts();
		}
		self::remove_defaults( $defaults, $options );
	}

	/**
	 * Remove extra settings from field to keep file size down
	 *
	 * @since 3.06
	 */
	public static function prepare_field_for_export( &$field ) {
		self::remove_default_field_options( $field );
		self::add_image_src_to_image_options( $field );
	}

	/**
	 * Remove defaults from field options too
	 *
	 * @since 3.06
	 */
	private static function remove_default_field_options( &$field ) {
		$defaults = self::default_field_options( $field->type );
		if ( empty( $defaults['blank'] ) ) {
			$global_settings   = new FrmSettings();
			$global_defaults   = $global_settings->default_options();
			$defaults['blank'] = $global_defaults['blank_msg'];
		}

		$options = $field->field_options;
		FrmAppHelper::unserialize_or_decode( $options );
		self::remove_defaults( $defaults, $options );
		self::remove_default_html( 'custom_html', $defaults, $options );

		// Get variations on the defaults.
		if ( isset( $options['invalid'] ) ) {
			$defaults = array(
				/* translators: %s: Field name */
				'invalid' => sprintf( __( '%s is invalid', 'formidable' ), $field->name ),
			);
			self::remove_defaults( $defaults, $options );
		}

		$field->field_options = serialize( $options );
	}

	/**
	 * Add image "src" key to each image option so the image can be imported to another website.
	 *
	 * @since 5.5.1
	 *
	 * @param stdClass $field
	 * @return void
	 */
	private static function add_image_src_to_image_options( $field ) {
		if ( empty( $field->options ) || false === strpos( $field->options, 'image' ) ) {
			return;
		}

		$updated = false;
		$options = $field->options;
		FrmAppHelper::unserialize_or_decode( $options );

		if ( ! $options || ! is_array( $options ) ) {
			return;
		}

		foreach ( $options as $key => $option ) {
			if ( is_array( $option ) && ! empty( $option['image'] ) ) {
				$options[ $key ]['src'] = wp_get_attachment_url( $option['image'] );
				$updated                = true;
			}
		}

		if ( $updated ) {
			$field->options = maybe_serialize( $options );
		}
	}

	/**
	 * @since 3.06.03
	 */
	private static function default_field_options( $type ) {
		$defaults = FrmFieldsHelper::get_default_field_options( $type );
		if ( empty( $defaults['custom_html'] ) ) {
			$defaults['custom_html'] = FrmFieldsHelper::get_default_html( $type );
		}
		return $defaults;
	}

	/**
	 * Compare the default array to the saved values and
	 * remove if they are the same
	 *
	 * @since 3.06
	 */
	private static function remove_defaults( $defaults, &$saved ) {
		foreach ( $saved as $key => $value ) {
			if ( isset( $defaults[ $key ] ) && $defaults[ $key ] === $value ) {
				unset( $saved[ $key ] );
			}
		}
	}

	/**
	 * The line endings may prevent html from being equal when it should
	 *
	 * @since 3.06
	 */
	private static function remove_default_html( $html_name, $defaults, &$options ) {
		if ( ! isset( $options[ $html_name ] ) || ! isset( $defaults[ $html_name ] ) ) {
			return;
		}

		$old_html     = str_replace( "\r\n", "\n", $options[ $html_name ] );
		$default_html = $defaults[ $html_name ];
		if ( $old_html == $default_html ) {
			unset( $options[ $html_name ] );

			return;
		}

		// Account for some of the older field default HTML.
		$default_html = str_replace( ' id="frm_desc_field_[key]"', '', $default_html );
		if ( $old_html == $default_html ) {
			unset( $options[ $html_name ] );
		}
	}

	public static function cdata( $str ) {
		FrmAppHelper::unserialize_or_decode( $str );
		if ( is_array( $str ) ) {
			$str = json_encode( $str );
		} elseif ( seems_utf8( $str ) === false ) {
			$str = FrmAppHelper::maybe_utf8_encode( $str );
		}

		if ( is_numeric( $str ) ) {
			return $str;
		}

		self::remove_invalid_characters_from_xml( $str );

		// $str = ent2ncr(esc_html( $str));
		$str = '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $str ) . ']]>';

		return $str;
	}

	/**
	 * Remove <US> character (unit separator) from exported strings
	 *
	 * @since 2.0.22
	 *
	 * @param string $str
	 */
	private static function remove_invalid_characters_from_xml( &$str ) {
		// Remove <US> character
		$str = str_replace( '\x1F', '', $str );
	}

	public static function migrate_form_settings_to_actions( $form_options, $form_id, &$imported = array(), $switch = false ) {
		// Get post type
		$post_type = FrmFormActionsController::$action_post_type;

		// Set up imported index, if not set up yet
		if ( ! isset( $imported['imported']['actions'] ) ) {
			$imported['imported']['actions'] = 0;
		}

		// Migrate post settings to action
		self::migrate_post_settings_to_action( $form_options, $form_id, $post_type, $imported, $switch );

		// Migrate email settings to action
		self::migrate_email_settings_to_action( $form_options, $form_id, $post_type, $imported, $switch );
	}

	/**
	 * Migrate post settings to form action
	 *
	 * @param string $post_type
	 */
	private static function migrate_post_settings_to_action( $form_options, $form_id, $post_type, &$imported, $switch ) {
		if ( ! isset( $form_options['create_post'] ) || ! $form_options['create_post'] ) {
			return;
		}

		$new_action = array(
			'post_type'    => $post_type,
			'post_excerpt' => 'wppost',
			'post_title'   => __( 'Create Posts', 'formidable' ),
			'menu_order'   => $form_id,
			'post_status'  => 'publish',
			'post_content' => array(),
			'post_name'    => $form_id . '_wppost_1',
		);

		$post_settings = array(
			'post_type',
			'post_category',
			'post_content',
			'post_excerpt',
			'post_title',
			'post_name',
			'post_date',
			'post_status',
			'post_custom_fields',
			'post_password',
			'post_parent',
		);

		foreach ( $post_settings as $post_setting ) {
			if ( isset( $form_options[ $post_setting ] ) ) {
				$new_action['post_content'][ $post_setting ] = $form_options[ $post_setting ];
			}
			unset( $post_setting );
		}

		$new_action['event'] = array( 'create', 'update' );

		if ( $switch ) {
			// Fields with string or int saved.
			$basic_fields = array(
				'post_title',
				'post_content',
				'post_excerpt',
				'post_password',
				'post_date',
				'post_status',
				'post_parent',
			);

			// Fields with arrays saved.
			$array_fields = array( 'post_category', 'post_custom_fields' );

			$new_action['post_content'] = self::switch_action_field_ids( $new_action['post_content'], $basic_fields, $array_fields );
		}
		$new_action['post_content'] = json_encode( $new_action['post_content'] );

		$exists = get_posts(
			array(
				'name'        => $new_action['post_name'],
				'post_type'   => $new_action['post_type'],
				'post_status' => $new_action['post_status'],
				'numberposts' => 1,
			)
		);

		if ( ! $exists ) {
			// this isn't an email, but we need to use a class that will always be included
			FrmDb::save_json_post( $new_action );
			$imported['imported']['actions'] ++;
		}
	}

	/**
	 * Switch old field IDs for new field IDs in emails and post
	 *
	 * @since 2.0
	 *
	 * @param array $post_content - check for old field IDs
	 * @param array $basic_fields - fields with string or int saved
	 * @param array $array_fields - fields with arrays saved
	 *
	 * @return string $post_content - new field IDs
	 */
	private static function switch_action_field_ids( $post_content, $basic_fields, $array_fields = array() ) {
		global $frm_duplicate_ids;

		// If there aren't IDs that were switched, end now
		if ( ! $frm_duplicate_ids ) {
			return;
		}

		// Get old IDs
		$old = array_keys( $frm_duplicate_ids );

		// Get new IDs
		$new = array_values( $frm_duplicate_ids );

		// Do a str_replace with each item to set the new IDs
		foreach ( $post_content as $key => $setting ) {
			if ( ! is_array( $setting ) && in_array( $key, $basic_fields ) ) {
				// Replace old IDs with new IDs
				$post_content[ $key ] = str_replace( $old, $new, $setting );
			} elseif ( is_array( $setting ) && in_array( $key, $array_fields ) ) {
				foreach ( $setting as $k => $val ) {
					// Replace old IDs with new IDs
					$post_content[ $key ][ $k ] = str_replace( $old, $new, $val );
				}
			}
			unset( $key, $setting );
		}

		return $post_content;
	}

	private static function migrate_email_settings_to_action( $form_options, $form_id, $post_type, &$imported, $switch ) {
		// No old notifications or autoresponders to carry over
		if ( ! isset( $form_options['auto_responder'] ) && ! isset( $form_options['notification'] ) && ! isset( $form_options['email_to'] ) ) {
			return;
		}

		// Initialize notifications array
		$notifications = array();

		// Migrate regular notifications
		self::migrate_notifications_to_action( $form_options, $form_id, $notifications );

		// Migrate autoresponders
		self::migrate_autoresponder_to_action( $form_options, $form_id, $notifications );

		if ( empty( $notifications ) ) {
			return;
		}

		foreach ( $notifications as $new_notification ) {
			$new_notification['post_type']    = $post_type;
			$new_notification['post_excerpt'] = 'email';
			$new_notification['post_title']   = __( 'Email Notification', 'formidable' );
			$new_notification['menu_order']   = $form_id;
			$new_notification['post_status']  = 'publish';

			// Switch field IDs and keys, if needed
			if ( $switch ) {

				// Switch field IDs in email conditional logic
				self::switch_email_condition_field_ids( $new_notification['post_content'] );

				// Switch all other field IDs in email
				$new_notification['post_content'] = FrmFieldsHelper::switch_field_ids( $new_notification['post_content'] );
			}
			$new_notification['post_content'] = FrmAppHelper::prepare_and_encode( $new_notification['post_content'] );

			$exists = get_posts(
				array(
					'name'        => $new_notification['post_name'],
					'post_type'   => $new_notification['post_type'],
					'post_status' => $new_notification['post_status'],
					'numberposts' => 1,
				)
			);

			if ( empty( $exists ) ) {
				FrmDb::save_json_post( $new_notification );
				$imported['imported']['actions'] ++;
			}
			unset( $new_notification );
		}

		self::remove_deprecated_notification_settings( $form_id, $form_options );
	}

	/**
	 * Remove deprecated notification settings after migration
	 *
	 * @since 2.05
	 *
	 * @param int|string $form_id
	 * @param array $form_options
	 */
	private static function remove_deprecated_notification_settings( $form_id, $form_options ) {
		$delete_settings = array( 'notification', 'autoresponder', 'email_to' );
		foreach ( $delete_settings as $index ) {
			if ( isset( $form_options[ $index ] ) ) {
				unset( $form_options[ $index ] );
			}
		}
		FrmForm::update( $form_id, array( 'options' => $form_options ) );
	}

	private static function migrate_notifications_to_action( $form_options, $form_id, &$notifications ) {
		if ( ! isset( $form_options['notification'] ) && isset( $form_options['email_to'] ) && ! empty( $form_options['email_to'] ) ) {
			// add old settings into notification array
			$form_options['notification'] = array( 0 => $form_options );
		} elseif ( isset( $form_options['notification']['email_to'] ) ) {
			// make sure it's in the correct format
			$form_options['notification'] = array( 0 => $form_options['notification'] );
		}

		if ( isset( $form_options['notification'] ) && is_array( $form_options['notification'] ) ) {
			foreach ( $form_options['notification'] as $email_key => $notification ) {

				$atts = array(
					'email_to'      => '',
					'reply_to'      => '',
					'reply_to_name' => '',
					'event'         => '',
					'form_id'       => $form_id,
					'email_key'     => $email_key,
				);

				// Format the email data
				self::format_email_data( $atts, $notification );

				if ( isset( $notification['twilio'] ) && $notification['twilio'] ) {
					do_action( 'frm_create_twilio_action', $atts, $notification );
				}

				// Setup the new notification
				$new_notification = array();
				self::setup_new_notification( $new_notification, $notification, $atts );

				$notifications[] = $new_notification;
			}
		}
	}

	private static function format_email_data( &$atts, $notification ) {
		// Format email_to
		self::format_email_to_data( $atts, $notification );

		// Format the reply to email and name
		$reply_fields = array(
			'reply_to'      => '',
			'reply_to_name' => '',
		);
		foreach ( $reply_fields as $f => $val ) {
			if ( isset( $notification[ $f ] ) ) {
				$atts[ $f ] = $notification[ $f ];
				if ( 'custom' == $notification[ $f ] ) {
					$atts[ $f ] = $notification[ 'cust_' . $f ];
				} elseif ( is_numeric( $atts[ $f ] ) && ! empty( $atts[ $f ] ) ) {
					$atts[ $f ] = '[' . $atts[ $f ] . ']';
				}
			}
			unset( $f, $val );
		}

		// Format event
		$atts['event'] = array( 'create' );
		if ( isset( $notification['update_email'] ) && 1 == $notification['update_email'] ) {
			$atts['event'][] = 'update';
		} elseif ( isset( $notification['update_email'] ) && 2 == $notification['update_email'] ) {
			$atts['event'] = array( 'update' );
		}
	}

	private static function format_email_to_data( &$atts, $notification ) {
		if ( isset( $notification['email_to'] ) ) {
			$atts['email_to'] = preg_split( '/ (,|;) /', $notification['email_to'] );
		} else {
			$atts['email_to'] = array();
		}

		if ( isset( $notification['also_email_to'] ) ) {
			$email_fields     = (array) $notification['also_email_to'];
			$atts['email_to'] = array_merge( $email_fields, $atts['email_to'] );
			unset( $email_fields );
		}

		foreach ( $atts['email_to'] as $key => $email_field ) {

			if ( is_numeric( $email_field ) ) {
				$atts['email_to'][ $key ] = '[' . $email_field . ']';
			}

			if ( strpos( $email_field, '|' ) ) {
				$email_opt = explode( '|', $email_field );
				if ( isset( $email_opt[0] ) ) {
					$atts['email_to'][ $key ] = '[' . $email_opt[0] . ' show=' . $email_opt[1] . ']';
				}
				unset( $email_opt );
			}
		}
		$atts['email_to'] = implode( ', ', $atts['email_to'] );
	}

	private static function setup_new_notification( &$new_notification, $notification, $atts ) {
		// Set up new notification
		$new_notification = array(
			'post_content' => array(
				'email_to' => $atts['email_to'],
				'event'    => $atts['event'],
			),
			'post_name'    => $atts['form_id'] . '_email_' . $atts['email_key'],
		);

		// Add more fields to the new notification
		$add_fields = array( 'email_message', 'email_subject', 'plain_text', 'inc_user_info', 'conditions' );
		foreach ( $add_fields as $add_field ) {
			if ( isset( $notification[ $add_field ] ) ) {
				$new_notification['post_content'][ $add_field ] = $notification[ $add_field ];
			} elseif ( in_array( $add_field, array( 'plain_text', 'inc_user_info' ) ) ) {
				$new_notification['post_content'][ $add_field ] = 0;
			} else {
				$new_notification['post_content'][ $add_field ] = '';
			}
			unset( $add_field );
		}

		// Set reply to
		$new_notification['post_content']['reply_to'] = $atts['reply_to'];

		// Set from
		if ( ! empty( $atts['reply_to'] ) || ! empty( $atts['reply_to_name'] ) ) {
			$new_notification['post_content']['from'] = ( empty( $atts['reply_to_name'] ) ? '[sitename]' : $atts['reply_to_name'] ) . ' <' . ( empty( $atts['reply_to'] ) ? '[admin_email]' : $atts['reply_to'] ) . '>';
		}
	}

	/**
	 * Switch field IDs in pre-2.0 email conditional logic
	 *
	 * @param $post_content array, pass by reference
	 */
	private static function switch_email_condition_field_ids( &$post_content ) {
		// Switch field IDs in conditional logic
		if ( isset( $post_content['conditions'] ) && is_array( $post_content['conditions'] ) ) {
			foreach ( $post_content['conditions'] as $email_key => $val ) {
				if ( is_numeric( $email_key ) ) {
					$post_content['conditions'][ $email_key ] = self::switch_action_field_ids( $val, array( 'hide_field' ) );
				}
				unset( $email_key, $val );
			}
		}
	}

	private static function migrate_autoresponder_to_action( $form_options, $form_id, &$notifications ) {
		if ( isset( $form_options['auto_responder'] ) && $form_options['auto_responder'] && isset( $form_options['ar_email_message'] ) && $form_options['ar_email_message'] ) {
			// migrate autoresponder

			$email_field = isset( $form_options['ar_email_to'] ) ? $form_options['ar_email_to'] : 0;
			if ( strpos( $email_field, '|' ) ) {
				// data from entries field
				$email_field = explode( '|', $email_field );
				if ( isset( $email_field[1] ) ) {
					$email_field = $email_field[1];
				}
			}
			if ( is_numeric( $email_field ) && ! empty( $email_field ) ) {
				$email_field = '[' . $email_field . ']';
			}

			$notification      = $form_options;
			$new_notification2 = array(
				'post_content' => array(
					'email_message' => $notification['ar_email_message'],
					'email_subject' => isset( $notification['ar_email_subject'] ) ? $notification['ar_email_subject'] : '',
					'email_to'      => $email_field,
					'plain_text'    => isset( $notification['ar_plain_text'] ) ? $notification['ar_plain_text'] : 0,
					'inc_user_info' => 0,
				),
				'post_name'    => $form_id . '_email_' . count( $notifications ),
			);

			$reply_to      = isset( $notification['ar_reply_to'] ) ? $notification['ar_reply_to'] : '';
			$reply_to_name = isset( $notification['ar_reply_to_name'] ) ? $notification['ar_reply_to_name'] : '';

			if ( ! empty( $reply_to ) ) {
				$new_notification2['post_content']['reply_to'] = $reply_to;
			}

			if ( ! empty( $reply_to ) || ! empty( $reply_to_name ) ) {
				$new_notification2['post_content']['from'] = ( empty( $reply_to_name ) ? '[sitename]' : $reply_to_name ) . ' <' . ( empty( $reply_to ) ? '[admin_email]' : $reply_to ) . '>';
			}

			$notifications[] = $new_notification2;
			unset( $new_notification2 );
		}
	}

	/**
	 * PHP 8 backward compatibility for the libxml_disable_entity_loader function
	 *
	 * @param  boolean $disable
	 *
	 * @return boolean
	 */
	public static function maybe_libxml_disable_entity_loader( $loader ) {
		if ( version_compare( phpversion(), '8.0', '<' ) && function_exists( 'libxml_disable_entity_loader' ) ) {
			$loader = libxml_disable_entity_loader( $loader ); // phpcs:disable Generic.PHP.DeprecatedFunctions.Deprecated
		}

		return $loader;
	}

	public static function check_if_libxml_disable_entity_loader_exists() {
		return version_compare( phpversion(), '8.0', '<' ) && ! function_exists( 'libxml_disable_entity_loader' );
	}
}

