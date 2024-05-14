<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmForm {

	/**
	 * @param array $values
	 * @return bool|int id on success or false on failure.
	 */
	public static function create( $values ) {
		global $wpdb;

		$values = FrmAppHelper::maybe_filter_array( $values, array( 'name', 'description' ) );

		$new_values = array(
			'form_key'       => FrmAppHelper::get_unique_key( $values['form_key'], $wpdb->prefix . 'frm_forms', 'form_key' ),
			'name'           => $values['name'],
			'description'    => $values['description'],
			'status'         => isset( $values['status'] ) ? $values['status'] : 'published',
			'logged_in'      => isset( $values['logged_in'] ) ? $values['logged_in'] : 0,
			'is_template'    => isset( $values['is_template'] ) ? (int) $values['is_template'] : 0,
			'parent_form_id' => isset( $values['parent_form_id'] ) ? absint( $values['parent_form_id'] ) : 0,
			'editable'       => isset( $values['editable'] ) ? (int) $values['editable'] : 0,
			'created_at'     => isset( $values['created_at'] ) ? $values['created_at'] : current_time( 'mysql', 1 ),
		);

		$options = isset( $values['options'] ) ? (array) $values['options'] : array();
		FrmFormsHelper::fill_form_options( $options, $values );

		$options['before_html'] = isset( $values['options']['before_html'] ) ? $values['options']['before_html'] : FrmFormsHelper::get_default_html( 'before' );
		$options['after_html']  = isset( $values['options']['after_html'] ) ? $values['options']['after_html'] : FrmFormsHelper::get_default_html( 'after' );
		$options['submit_html'] = isset( $values['options']['submit_html'] ) ? $values['options']['submit_html'] : FrmFormsHelper::get_default_html( 'submit' );

		/**
		 * Allows modifying form options before updating or creating.
		 *
		 * @since 5.4 Add the third param.
		 *
		 * @param array $options Form options.
		 * @param array $values  Form data.
		 * @param bool  $update  Is form updating or creating. It's `true` if is updating.
		 */
		$options               = apply_filters( 'frm_form_options_before_update', $options, $values, false );
		$options               = self::maybe_filter_form_options( $options );
		$new_values['options'] = serialize( $options );

		$wpdb->insert( $wpdb->prefix . 'frm_forms', $new_values );

		$id = $wpdb->insert_id;

		// Clear form caching
		self::clear_form_cache();

		return $id;
	}

	/**
	 * @since 5.0.08
	 *
	 * @param array $options
	 * @return array
	 */
	private static function maybe_filter_form_options( $options ) {
		if ( ! FrmAppHelper::allow_unfiltered_html() && ! empty( $options['submit_html'] ) ) {
			$options['submit_html'] = FrmAppHelper::kses_submit_button( $options['submit_html'] );
		}
		return FrmAppHelper::maybe_filter_array( $options, array( 'submit_value', 'success_msg', 'before_html', 'after_html' ) );
	}

	/**
	 * @return bool|int ID on success or false on failure
	 */
	public static function duplicate( $id, $template = false, $copy_keys = false, $blog_id = false ) {
		global $wpdb;

		$values = self::getOne( $id, $blog_id );
		if ( ! $values ) {
			return false;
		}

		$new_key = $copy_keys ? $values->form_key : '';

		$new_values = array(
			'form_key'    => FrmAppHelper::get_unique_key( $new_key, $wpdb->prefix . 'frm_forms', 'form_key' ),
			'name'        => $values->name,
			'description' => $values->description,
			'status'      => $values->status ? $values->status : 'published',
			'logged_in'   => $values->logged_in ? $values->logged_in : 0,
			'editable'    => $values->editable ? $values->editable : 0,
			'created_at'  => current_time( 'mysql', 1 ),
			'is_template' => $template ? 1 : 0,
		);

		if ( $blog_id ) {
			$new_values['status'] = 'published';
			$new_options          = $values->options;
			FrmAppHelper::unserialize_or_decode( $new_options );
			$new_options['email_to'] = get_option( 'admin_email' );
			$new_options['copy']     = false;
			$new_values['options']   = $new_options;
		} else {
			$new_values['options'] = $values->options;
		}

		if ( is_array( $new_values['options'] ) ) {
			$new_values['options'] = serialize( $new_values['options'] );
		}

		$query_results = $wpdb->insert( $wpdb->prefix . 'frm_forms', $new_values );

		if ( $query_results ) {
			// Clear form caching
			self::clear_form_cache();

			$form_id = $wpdb->insert_id;
			FrmField::duplicate( $id, $form_id, $copy_keys, $blog_id );

			// update form settings after fields are created
			do_action( 'frm_after_duplicate_form', $form_id, $new_values, array( 'old_id' => $id ) );

			return $form_id;
		}

		return false;
	}

	public static function after_duplicate( $form_id, $values ) {
		$new_opts = $values['options'];
		FrmAppHelper::unserialize_or_decode( $new_opts );
		$values['options'] = $new_opts;

		if ( isset( $new_opts['success_msg'] ) ) {
			$new_opts['success_msg'] = FrmFieldsHelper::switch_field_ids( $new_opts['success_msg'] );
		}

		$new_opts = apply_filters( 'frm_after_duplicate_form_values', $new_opts, $form_id );

		if ( $new_opts != $values['options'] ) {
			global $wpdb;
			$wpdb->update( $wpdb->prefix . 'frm_forms', array( 'options' => maybe_serialize( $new_opts ) ), array( 'id' => $form_id ) );
		}

		self::switch_field_ids_in_fields( $form_id );
	}

	/**
	 * Switches field ID in fields.
	 *
	 * @since 5.3
	 *
	 * @param int $form_id Form ID.
	 */
	private static function switch_field_ids_in_fields( $form_id ) {
		global $wpdb;

		// Keys of fields that you want to check to replace field ID.
		$keys     = array( 'default_value', 'field_options' );
		$sql_cols = 'fi.id';
		foreach ( $keys as $key ) {
			$sql_cols .= ',fi.' . $key;
		}

		$fields = FrmDb::get_results(
			"{$wpdb->prefix}frm_fields AS fi LEFT OUTER JOIN {$wpdb->prefix}frm_forms AS fr ON fi.form_id = fr.id",
			array(
				'or'                => 1,
				'fi.form_id'        => $form_id,
				'fr.parent_form_id' => $form_id,
			),
			$sql_cols
		);

		if ( ! $fields || ! is_array( $fields ) ) {
			return;
		}

		foreach ( $fields as $field ) {
			self::switch_field_ids_in_field( (array) $field );
		}
	}

	/**
	 * Switches field ID in a field.
	 *
	 * @since 5.3
	 *
	 * @param array $field Field array.
	 */
	private static function switch_field_ids_in_field( $field ) {
		$new_values = array();
		foreach ( $field as $key => $value ) {
			if ( 'id' === $key || ! $value ) {
				continue;
			}

			if ( ! is_string( $value ) && ! is_array( $value ) ) {
				continue;
			}

			if ( 'field_options' === $key ) {
				// Need to loop through field_options to prevent breaking serialized string when length changed.
				FrmAppHelper::unserialize_or_decode( $value );
				$new_val = FrmFieldsHelper::switch_field_ids( $value );
				$new_val = serialize( $new_val );
			} else {
				$new_val = FrmFieldsHelper::switch_field_ids( $value );
			}

			if ( $new_val !== $value ) {
				$new_values[ $key ] = $new_val;
			}
		}//end foreach

		if ( ! empty( $new_values ) ) {
			FrmField::update( $field['id'], $new_values );
		}
	}

	/**
	 * @return bool|int
	 */
	public static function update( $id, $values, $create_link = false ) {
		global $wpdb;

		$values = FrmAppHelper::maybe_filter_array( $values, array( 'name', 'description' ) );

		if ( ! isset( $values['status'] ) && ( $create_link || isset( $values['options'] ) || isset( $values['item_meta'] ) || isset( $values['field_options'] ) ) ) {
			$values['status'] = 'published';
		}

		if ( isset( $values['form_key'] ) ) {
			$values['form_key'] = FrmAppHelper::get_unique_key( $values['form_key'], $wpdb->prefix . 'frm_forms', 'form_key', $id );
		}

		$form_fields = array( 'form_key', 'name', 'description', 'status', 'parent_form_id' );

		$new_values = self::set_update_options( array(), $values, array( 'form_id' => $id ) );

		foreach ( $values as $value_key => $value ) {
			if ( $value_key && in_array( $value_key, $form_fields ) ) {
				$new_values[ $value_key ] = $value;
			}
		}

		if ( ! empty( $values['new_status'] ) ) {
			$new_values['status'] = $values['new_status'];
		}

		if ( ! empty( $new_values ) ) {
			$query_results = $wpdb->update( $wpdb->prefix . 'frm_forms', $new_values, array( 'id' => $id ) );
			if ( $query_results ) {
				self::clear_form_cache();
			}
		} else {
			$query_results = true;
		}
		unset( $new_values );

		$values = self::update_fields( $id, $values );

		do_action( 'frm_update_form', $id, $values );
		do_action( 'frm_update_form_' . $id, $values );

		return $query_results;
	}

	/**
	 * @param array $new_values
	 * @param array $values
	 * @param array $args
	 * @return array
	 */
	public static function set_update_options( $new_values, $values, $args = array() ) {
		if ( ! isset( $values['options'] ) ) {
			return $new_values;
		}

		$options = ! empty( $values['options'] ) ? (array) $values['options'] : array();
		FrmFormsHelper::fill_form_options( $options, $values );

		$options['custom_style'] = isset( $values['options']['custom_style'] ) ? $values['options']['custom_style'] : 0;
		$options['before_html']  = isset( $values['options']['before_html'] ) ? $values['options']['before_html'] : FrmFormsHelper::get_default_html( 'before' );
		$options['after_html']   = isset( $values['options']['after_html'] ) ? $values['options']['after_html'] : FrmFormsHelper::get_default_html( 'after' );
		$options['submit_html']  = isset( $values['options']['submit_html'] ) && '' !== $values['options']['submit_html'] ? $values['options']['submit_html'] : FrmFormsHelper::get_default_html( 'submit' );

		/**
		 * Allows modifying form options before updating or creating.
		 *
		 * @since 5.4 Added the third param.
		 *
		 * @param array $options Form options.
		 * @param array $values  Form data.
		 * @param bool  $update  Is form updating or creating. It's `true` if is updating.
		 */
		$options               = apply_filters( 'frm_form_options_before_update', $options, $values, true );
		$options               = self::maybe_filter_form_options( $options );
		$new_values['options'] = serialize( $options );

		return $new_values;
	}

	/**
	 * @return array
	 */
	public static function update_fields( $id, $values ) {

		if ( ! isset( $values['item_meta'] ) && ! isset( $values['field_options'] ) ) {
			return $values;
		}

		$all_fields = FrmField::get_all_for_form( $id );
		if ( empty( $all_fields ) ) {
			return $values;
		}

		if ( ! isset( $values['item_meta'] ) ) {
			$values['item_meta'] = array();
		}

		$field_array   = array();
		$existing_keys = array_keys( $values['item_meta'] );
		foreach ( $all_fields as $fid ) {
			if ( ! in_array( $fid->id, $existing_keys ) && ( isset( $values['frm_fields_submitted'] ) && in_array( $fid->id, $values['frm_fields_submitted'] ) ) || isset( $values['options'] ) ) {
				$values['item_meta'][ $fid->id ] = '';
			}
			$field_array[ $fid->id ] = $fid;
		}
		unset( $all_fields );

		foreach ( $values['item_meta'] as $field_id => $default_value ) {
			if ( isset( $field_array[ $field_id ] ) ) {
				$field = $field_array[ $field_id ];
			} else {
				$field = FrmField::getOne( $field_id );
			}

			if ( ! $field ) {
				continue;
			}

			$is_settings_page = ( isset( $values['options'] ) || isset( $values['field_options'][ 'custom_html_' . $field_id ] ) );
			if ( $is_settings_page ) {
				self::get_settings_page_html( $values, $field );

				if ( ! defined( 'WP_IMPORTING' ) ) {
					continue;
				}
			}

			// Updating the form.
			$update_options = FrmFieldsHelper::get_default_field_options_from_field( $field );
			// Don't check for POST html.
			unset( $update_options['custom_html'] );
			$update_options = apply_filters( 'frm_field_options_to_update', $update_options );

			foreach ( $update_options as $opt => $default ) {
				$field->field_options[ $opt ] = isset( $values['field_options'][ $opt . '_' . $field_id ] ) ? $values['field_options'][ $opt . '_' . $field_id ] : $default;
				self::sanitize_field_opt( $opt, $field->field_options[ $opt ] );
			}

			$field->field_options = apply_filters( 'frm_update_field_options', $field->field_options, $field, $values );

			$new_field = array(
				'field_options' => $field->field_options,
				'default_value' => isset( $values[ 'default_value_' . $field_id ] ) ? FrmAppHelper::maybe_json_encode( $values[ 'default_value_' . $field_id ] ) : '',
			);

			if ( ! FrmAppHelper::allow_unfiltered_html() && isset( $values['field_options'][ 'options_' . $field_id ] ) && is_array( $values['field_options'][ 'options_' . $field_id ] ) ) {
				foreach ( $values['field_options'][ 'options_' . $field_id ] as $option_key => $option ) {
					if ( is_array( $option ) ) {
						foreach ( $option as $key => $item ) {
							$values['field_options'][ 'options_' . $field_id ][ $option_key ][ $key ] = FrmAppHelper::kses( $item, 'all' );
						}
					}
				}
			}

			self::prepare_field_update_values( $field, $values, $new_field );
			self::maybe_update_max_option( $field, $values, $new_field );

			FrmField::update( $field_id, $new_field );

			FrmField::delete_form_transient( $field->form_id );
		}//end foreach
		self::clear_form_cache();

		return $values;
	}

	/**
	 * Resets the 'max' option of a field when changing paragraph field type to other field types like text, email etc.
	 *
	 * @since 6.7
	 *
	 * @param array $field
	 * @param array $values
	 * @param array $new_field
	 * @return void
	 */
	private static function maybe_update_max_option( $field, $values, &$new_field ) {
		if ( $field->type === 'textarea' &&
			! empty( $values['field_options'][ 'type_' . $field->id ] ) &&
			in_array( $values['field_options'][ 'type_' . $field->id ], array( 'text', 'email', 'url', 'password', 'phone' ), true ) ) {

			$new_field['field_options']['max'] = '';

			/**
			 * Update posted field setting so that new 'max' option is displayed after form is saved and page reloads.
			 * FrmFieldsHelper::fill_default_field_opts populates field options by calling self::get_posted_field_setting.
			 */
			$_POST['field_options'][ 'max_' . $field->id ] = '';
		}
	}

	/**
	 * @param string $opt
	 * @param mixed  $value
	 * @return void
	 */
	private static function sanitize_field_opt( $opt, &$value ) {
		if ( ! is_string( $value ) ) {
			return;
		}

		/**
		 * Allow the option to turn off sanitization for a field. This way a custom rule can be used instead.
		 * Make sure to add custom sanitization using the frm_update_field_options filter as the data will no longer be sanitized.
		 *
		 * @since 6.0
		 *
		 * @param bool   $should_sanitize
		 * @param string $opt
		 */
		$should_sanitize = apply_filters( 'frm_should_sanitize_field_opt_string', true, $opt );

		if ( ! $should_sanitize ) {
			return;
		}

		if ( $opt === 'calc' ) {
			$value = self::sanitize_calc( $value );
		} else {
			$value = FrmAppHelper::kses( $value, 'all' );
		}

		$value = trim( $value );
	}

	/**
	 * @param string $value
	 * @return string
	 */
	private static function sanitize_calc( $value ) {
		if ( false !== strpos( $value, '<' ) ) {
			$value = self::normalize_calc_spaces( $value );
		}
		// Allow <= and >=.
		$allow = array( '<= ', ' >=' );
		$temp  = array( '< = ', ' > =' );
		$value = str_replace( $allow, $temp, $value );
		$value = strip_tags( $value );
		$value = str_replace( $temp, $allow, $value );
		return $value;
	}

	/**
	 * Format a comparison like 5<10 to 5 < 10. Also works on 5< 10, 5 <10, 5<=10 variations.
	 * This is to avoid an issue with unspaced calculations being recognized as HTML that gets removed when strip_tags is called.
	 *
	 * @param string $calc
	 * @return string
	 */
	private static function normalize_calc_spaces( $calc ) {
		// Check for a pattern with 5 parts
		// $1 \d the first comparison digit.
		// $2 a space (optional).
		// $3 an equals sign (optional) that follows the < operator for <= comparisons.
		// $4 another space (optional).
		// $5 \d the second comparison digit.
		return preg_replace( '/(\d)( ){0,1}<(=){0,1}( ){0,1}(\d)/', '$1 <$3 $5', $calc );
	}

	/**
	 * Updating the settings page
	 */
	private static function get_settings_page_html( $values, &$field ) {
		if ( isset( $values['field_options'][ 'custom_html_' . $field->id ] ) ) {
			$prev_opts     = array();
			$fallback_html = isset( $field->field_options['custom_html'] ) ? $field->field_options['custom_html'] : FrmFieldsHelper::get_default_html( $field->type );

			$field->field_options['custom_html'] = isset( $values['field_options'][ 'custom_html_' . $field->id ] ) ? $values['field_options'][ 'custom_html_' . $field->id ] : $fallback_html;
		} elseif ( $field->type === 'hidden' || $field->type === 'user_id' ) {
			$prev_opts = $field->field_options;
		}

		if ( isset( $prev_opts ) ) {
			$field->field_options = apply_filters( 'frm_update_form_field_options', $field->field_options, $field, $values );
			if ( $prev_opts != $field->field_options ) {
				FrmField::update( $field->id, array( 'field_options' => $field->field_options ) );
			}
		}
	}

	private static function prepare_field_update_values( $field, $values, &$new_field ) {
		$field_cols = array(
			'field_order' => 0,
			'field_key'   => '',
			'required'    => false,
			'type'        => '',
			'description' => '',
			'options'     => '',
			'name'        => '',
		);
		foreach ( $field_cols as $col => $default ) {
			$default = $default === '' ? $field->{$col} : $default;

			$new_field[ $col ] = isset( $values['field_options'][ $col . '_' . $field->id ] ) ? $values['field_options'][ $col . '_' . $field->id ] : $default;
		}

		// Don't save the template option.
		if ( is_array( $new_field['options'] ) && isset( $new_field['options']['000'] ) ) {
			unset( $new_field['options']['000'] );
		}
	}

	/**
	 * Get a list of all form settings that should be translated
	 * on a multilingual site.
	 *
	 * @since 3.06.01
	 * @param object $form The form object.
	 */
	public static function translatable_strings( $form ) {
		$strings = array(
			'name',
			'description',
			'submit_value',
			'submit_msg',
			'success_msg',
			'invalid_msg',
			'failed_msg',
			'login_msg',
			'admin_permission',
		);

		return apply_filters( 'frm_form_strings', $strings, $form );
	}

	/**
	 * @param int    $id
	 * @param string $status
	 *
	 * @return bool|int
	 */
	public static function set_status( $id, $status ) {
		if ( 'trash' == $status ) {
			return self::trash( $id );
		}

		$statuses = array( 'published', 'draft', 'trash' );
		if ( ! in_array( $status, $statuses, true ) ) {
			return false;
		}

		global $wpdb;

		if ( is_array( $id ) ) {
			$where = array(
				'id'             => $id,
				'parent_form_id' => $id,
				'or'             => 1,
			);
			FrmDb::get_where_clause_and_values( $where );
			array_unshift( $where['values'], $status );

			$query_results = $wpdb->query( $wpdb->prepare( 'UPDATE ' . $wpdb->prefix . 'frm_forms SET status = %s ' . $where['where'], $where['values'] ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		} else {
			$query_results = $wpdb->update( $wpdb->prefix . 'frm_forms', array( 'status' => $status ), array( 'id' => $id ) );
			$wpdb->update( $wpdb->prefix . 'frm_forms', array( 'status' => $status ), array( 'parent_form_id' => $id ) );
		}

		if ( $query_results ) {
			self::clear_form_cache();
		}

		return $query_results;
	}

	/**
	 * @return bool|int
	 */
	public static function trash( $id ) {
		if ( ! EMPTY_TRASH_DAYS ) {
			return self::destroy( $id );
		}

		$form = self::getOne( $id );
		if ( ! $form ) {
			return false;
		}

		$options               = $form->options;
		$options['trash_time'] = time();

		global $wpdb;
		$query_results = $wpdb->update(
			$wpdb->prefix . 'frm_forms',
			array(
				'status'  => 'trash',
				'options' => serialize( $options ),
			),
			array(
				'id' => $id,
			)
		);

		$wpdb->update(
			$wpdb->prefix . 'frm_forms',
			array(
				'status'  => 'trash',
				'options' => serialize( $options ),
			),
			array(
				'parent_form_id' => $id,
			)
		);

		if ( $query_results ) {
			self::clear_form_cache();
		}

		return $query_results;
	}

	/**
	 * @return bool|int
	 */
	public static function destroy( $id ) {
		global $wpdb;

		$form = self::getOne( $id );
		if ( ! $form ) {
			return false;
		}
		$id = $form->id;

		// Disconnect the entries from this form
		$entries = FrmDb::get_col( $wpdb->prefix . 'frm_items', array( 'form_id' => $id ) );
		foreach ( $entries as $entry_id ) {
			FrmEntry::destroy( $entry_id );
			unset( $entry_id );
		}

		// Disconnect the fields from this form
		$wpdb->query( $wpdb->prepare( 'DELETE fi FROM ' . $wpdb->prefix . 'frm_fields AS fi LEFT JOIN ' . $wpdb->prefix . 'frm_forms fr ON (fi.form_id = fr.id) WHERE fi.form_id=%d OR parent_form_id=%d', $id, $id ) );

		$query_results = $wpdb->query( $wpdb->prepare( 'DELETE FROM ' . $wpdb->prefix . 'frm_forms WHERE id=%d OR parent_form_id=%d', $id, $id ) );
		if ( $query_results ) {
			// Delete all form actions linked to this form
			$action_control = FrmFormActionsController::get_form_actions( 'email' );
			$action_control->destroy( $id, 'all' );

			// Clear form caching
			self::clear_form_cache();

			do_action( 'frm_destroy_form', $id );
			do_action( 'frm_destroy_form_' . $id );
		}

		return $query_results;
	}

	/**
	 * Delete trashed forms based on how long they have been trashed
	 *
	 * @return int The number of forms deleted
	 */
	public static function scheduled_delete( $delete_timestamp = '' ) {
		global $wpdb;

		$trash_forms = FrmDb::get_results( $wpdb->prefix . 'frm_forms', array( 'status' => 'trash' ), 'id, parent_form_id, options' );

		if ( ! $trash_forms ) {
			return 0;
		}

		if ( empty( $delete_timestamp ) ) {
			$delete_timestamp = time() - ( DAY_IN_SECONDS * EMPTY_TRASH_DAYS );
		}

		$count = 0;
		foreach ( $trash_forms as $form ) {
			FrmAppHelper::unserialize_or_decode( $form->options );
			if ( ! isset( $form->options['trash_time'] ) || $form->options['trash_time'] < $delete_timestamp ) {
				self::destroy( $form->id );
				if ( empty( $form->parent_form_id ) ) {
					++$count;
				}
			}

			unset( $form );
		}

		return $count;
	}

	/**
	 * @return string form name
	 */
	public static function getName( $id ) {
		$form = FrmDb::check_cache( $id, 'frm_form' );
		if ( $form ) {
			$r = stripslashes( $form->name );

			return $r;
		}

		$query_key = is_numeric( $id ) ? 'id' : 'form_key';
		$r         = FrmDb::get_var( 'frm_forms', array( $query_key => $id ), 'name' );

		// An empty form name can result in a null value.
		$r = is_null( $r ) ? '' : stripslashes( $r );

		return $r;
	}

	/**
	 * @since 3.0
	 *
	 * @param string $key
	 *
	 * @return int form id
	 */
	public static function get_id_by_key( $key ) {
		return (int) FrmDb::get_var( 'frm_forms', array( 'form_key' => sanitize_title( $key ) ) );
	}

	/**
	 * @since 3.0
	 *
	 * @param int $id
	 *
	 * @return string form key
	 */
	public static function get_key_by_id( $id ) {
		$id    = (int) $id;
		$cache = FrmDb::check_cache( $id, 'frm_form' );
		if ( $cache ) {
			return $cache->form_key;
		}

		$key = FrmDb::get_var( 'frm_forms', array( 'id' => $id ), 'form_key' );

		return $key;
	}

	/**
	 * If $form is numeric, get the form object
	 *
	 * @since 2.0.9
	 * @param int|object $form
	 */
	public static function maybe_get_form( &$form ) {
		if ( ! is_object( $form ) && ! is_array( $form ) && ! empty( $form ) ) {
			$form = self::getOne( $form );
		}
	}

	/**
	 * @param int|string $id
	 * @param false|int  $blog_id
	 * @return stdClass|null
	 */
	public static function getOne( $id, $blog_id = false ) {
		global $wpdb;

		if ( $blog_id && is_multisite() ) {
			global $wpmuBaseTablePrefix;
			$prefix = $wpmuBaseTablePrefix ? $wpmuBaseTablePrefix . $blog_id . '_' : $wpdb->get_blog_prefix( $blog_id );

			$table_name = $prefix . 'frm_forms';
		} else {
			$table_name = $wpdb->prefix . 'frm_forms';
			$cache      = wp_cache_get( $id, 'frm_form' );
			if ( $cache ) {
				if ( isset( $cache->options ) ) {
					FrmAppHelper::unserialize_or_decode( $cache->options );
				}
				return self::prepare_form_row_data( $cache );
			}
		}

		if ( is_numeric( $id ) ) {
			$where = array( 'id' => $id );
		} else {
			$where = array( 'form_key' => $id );
		}

		$results = FrmDb::get_row( $table_name, $where );

		if ( isset( $results->options ) ) {
			FrmDb::set_cache( $results->id, $results, 'frm_form' );
			FrmAppHelper::unserialize_or_decode( $results->options );
		}

		return self::prepare_form_row_data( $results );
	}

	/**
	 * Make sure that if $row is an object, that $row->options is an array and not a string.
	 *
	 * @since 6.8.3
	 *
	 * @param stdClass|null $row The database row for a target form.
	 * @return stdClass|null
	 */
	private static function prepare_form_row_data( $row ) {
		$row = wp_unslash( $row );
		if ( ! is_object( $row ) ) {
			return $row;
		}

		if ( ! is_array( $row->options ) ) {
			$row->options = FrmFormsHelper::get_default_opts();
		}

		/**
		 * @since 4.03.02
		 *
		 * @param stdClass $row
		 */
		return apply_filters( 'frm_form_object', $row );
	}

	/**
	 * @return array|object of objects
	 */
	public static function getAll( $where = array(), $order_by = '', $limit = '' ) {
		if ( is_array( $where ) && ! empty( $where ) ) {
			if ( isset( $where['is_template'] ) && $where['is_template'] && ! isset( $where['status'] ) && ! isset( $where['status !'] ) ) {
				// don't get trashed templates
				$where['status'] = array( null, '', 'published' );
			}

			$results = FrmDb::get_results( 'frm_forms', $where, '*', compact( 'order_by', 'limit' ) );
		} else {
			global $wpdb;

			// The query has already been prepared if this is not an array.
			$query   = 'SELECT * FROM ' . $wpdb->prefix . 'frm_forms' . FrmDb::prepend_and_or_where( ' WHERE ', $where ) . FrmDb::esc_order( $order_by ) . FrmDb::esc_limit( $limit );
			$results = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		if ( $results ) {
			foreach ( $results as $result ) {
				FrmDb::set_cache( $result->id, $result, 'frm_form' );
				FrmAppHelper::unserialize_or_decode( $result->options );
			}
		}

		if ( $limit == ' LIMIT 1' || $limit == 1 ) {
			// return the first form object if we are only getting one form
			$results = reset( $results );
		}

		return wp_unslash( $results );
	}

	/**
	 * Get all published forms
	 *
	 * @since 2.0
	 *
	 * @param array  $query
	 * @param int    $limit
	 * @param string $inc_children
	 * @return array|object of forms A single form object would be passed if $limit was set to 1.
	 */
	public static function get_published_forms( $query = array(), $limit = 999, $inc_children = 'exclude' ) {
		$query['is_template'] = 0;
		$query['status']      = array( null, '', 'published' );
		if ( $inc_children == 'exclude' ) {
			$query['parent_form_id'] = array( null, 0 );
		}

		$forms = self::getAll( $query, 'name', $limit );

		return $forms;
	}

	/**
	 * @return object count of forms
	 */
	public static function get_count() {
		global $wpdb;

		$cache_key = 'frm_form_counts';

		$counts = wp_cache_get( $cache_key, 'frm_form' );
		if ( false !== $counts ) {
			return $counts;
		}

		$results = (array) FrmDb::get_results(
			'frm_forms',
			array(
				'or'               => 1,
				'parent_form_id'   => null,
				'parent_form_id <' => 0,
			),
			'status, is_template'
		);

		$statuses = array( 'published', 'draft', 'template', 'trash' );
		$counts   = array_fill_keys( $statuses, 0 );

		foreach ( $results as $row ) {
			if ( 'trash' != $row->status ) {
				if ( $row->is_template ) {
					++$counts['template'];
				} else {
					++$counts['published'];
				}
			} else {
				++$counts['trash'];
			}

			if ( 'draft' == $row->status ) {
				++$counts['draft'];
			}

			unset( $row );
		}

		$counts = (object) $counts;
		FrmDb::set_cache( $cache_key, $counts, 'frm_form' );

		return $counts;
	}

	/**
	 * Clear form caching
	 * Called when a form is created, updated, duplicated, or deleted
	 * or when the form status is changed
	 *
	 * @since 2.0.4
	 */
	public static function clear_form_cache() {
		FrmDb::cache_delete_group( 'frm_form' );
	}

	/**
	 * @return array of errors
	 */
	public static function validate( $values ) {
		$errors = array();

		return apply_filters( 'frm_validate_form', $errors, $values );
	}

	public static function get_params( $form = null ) {
		global $frm_vars;

		if ( ! $form ) {
			$form = self::getAll( array(), 'name', 1 );
		} else {
			self::maybe_get_form( $form );
		}

		if ( isset( $frm_vars['form_params'] ) && is_array( $frm_vars['form_params'] ) && isset( $frm_vars['form_params'][ $form->id ] ) ) {
			return $frm_vars['form_params'][ $form->id ];
		}

		$action_var = isset( $_REQUEST['frm_action'] ) ? 'frm_action' : 'action'; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$action     = apply_filters( 'frm_show_new_entry_page', FrmAppHelper::get_param( $action_var, 'new', 'get', 'sanitize_title' ), $form );

		$default_values = array(
			'id'        => '',
			'form_name' => '',
			'paged'     => 1,
			'form'      => $form->id,
			'form_id'   => $form->id,
			'field_id'  => '',
			'search'    => '',
			'sort'      => '',
			'sdir'      => '',
			'action'    => $action,
		);

		$values                   = array();
		$values['posted_form_id'] = FrmAppHelper::get_param( 'form_id', '', 'get', 'absint' );
		if ( ! $values['posted_form_id'] ) {
			$values['posted_form_id'] = FrmAppHelper::get_param( 'form', '', 'get', 'absint' );
		}

		if ( $form->id == $values['posted_form_id'] ) {
			// If there are two forms on the same page, make sure not to submit both.
			foreach ( $default_values as $var => $default ) {
				if ( $var === 'action' ) {
					$values[ $var ] = FrmAppHelper::get_param( $action_var, $default, 'get', 'sanitize_title' );
				} else {
					$values[ $var ] = FrmAppHelper::get_param( $var, $default, 'get', 'sanitize_text_field' );
				}
				unset( $var, $default );
			}
		} else {
			foreach ( $default_values as $var => $default ) {
				$values[ $var ] = $default;
				unset( $var, $default );
			}
		}

		if ( in_array( $values['action'], array( 'create', 'update' ) ) &&
			( ! $_POST || ( ! isset( $_POST['action'] ) && ! isset( $_POST['frm_action'] ) ) ) // phpcs:ignore WordPress.Security.NonceVerification.Missing
			) {
			$values['action'] = 'new';
		}

		return $values;
	}

	public static function list_page_params() {
		$values   = array();
		$defaults = array(
			'template' => 0,
			'id'       => '',
			'paged'    => 1,
			'form'     => '',
			'search'   => '',
			'sort'     => '',
			'sdir'     => '',
		);
		foreach ( $defaults as $var => $default ) {
			$values[ $var ] = FrmAppHelper::get_param( $var, $default, 'get', 'sanitize_text_field' );
		}

		return $values;
	}

	public static function get_admin_params( $form = null ) {
		$form_id = $form;
		if ( $form === null ) {
			$form_id = self::get_current_form_id();
		} elseif ( $form && is_object( $form ) ) {
			$form_id = $form->id;
		}

		$values   = array();
		$defaults = array(
			'id'        => '',
			'form_name' => '',
			'paged'     => 1,
			'form'      => $form_id,
			'field_id'  => '',
			'search'    => '',
			'sort'      => '',
			'sdir'      => '',
			'fid'       => '',
			'keep_post' => '',
		);
		foreach ( $defaults as $var => $default ) {
			$values[ $var ] = FrmAppHelper::get_param( $var, $default, 'get', 'sanitize_text_field' );
		}

		return $values;
	}

	public static function get_current_form_id( $default_form = 'none' ) {
		if ( 'first' === $default_form ) {
			$form = self::get_current_form();
		} else {
			$form = self::maybe_get_current_form();
		}
		$form_id = $form ? $form->id : 0;

		return $form_id;
	}

	public static function maybe_get_current_form( $form_id = 0 ) {
		global $frm_vars;

		if ( isset( $frm_vars['current_form'] ) && $frm_vars['current_form'] && ( ! $form_id || $form_id == $frm_vars['current_form']->id ) ) {
			return $frm_vars['current_form'];
		}

		$form_id = FrmAppHelper::get_param( 'form', $form_id, 'get', 'absint' );
		if ( $form_id ) {
			$form_id = self::set_current_form( $form_id );
		}

		return $form_id;
	}

	public static function get_current_form( $form_id = 0 ) {
		$form = self::maybe_get_current_form( $form_id );
		if ( is_numeric( $form ) ) {
			$form = self::set_current_form( $form );
		}

		return $form;
	}

	public static function set_current_form( $form_id ) {
		global $frm_vars;

		$query = array();
		if ( $form_id ) {
			$query['id'] = $form_id;
		}

		$frm_vars['current_form'] = self::get_published_forms( $query, 1 );

		return $frm_vars['current_form'];
	}

	public static function is_form_loaded( $form, $this_load, $global_load ) {
		global $frm_vars;
		$small_form = new stdClass();
		foreach ( array( 'id', 'form_key', 'name' ) as $var ) {
			$small_form->{$var} = $form->{$var};
			unset( $var );
		}

		$frm_vars['forms_loaded'][] = $small_form;

		if ( $this_load && empty( $global_load ) ) {
			$global_load          = true;
			$frm_vars['load_css'] = true;
		}

		return ( ( ! isset( $frm_vars['css_loaded'] ) || ! $frm_vars['css_loaded'] ) && $global_load );
	}

	/**
	 * @since 4.06.03
	 *
	 * @param object $form
	 *
	 * @return bool
	 */
	public static function &is_visible_to_user( $form ) {
		if ( $form->logged_in && isset( $form->options['logged_in_role'] ) ) {
			$visible = FrmAppHelper::user_has_permission( $form->options['logged_in_role'] );
		} else {
			$visible = true;
		}

		return $visible;
	}

	public static function show_submit( $form ) {
		$show = ( ! $form->is_template && $form->status === 'published' && ! FrmAppHelper::is_admin() );
		$show = apply_filters( 'frm_show_submit_button', $show, $form );

		return $show;
	}

	/**
	 * @since 2.3
	 */
	public static function get_option( $atts ) {
		$form    = $atts['form'];
		$default = isset( $atts['default'] ) ? $atts['default'] : '';

		return isset( $form->options[ $atts['option'] ] ) ? $form->options[ $atts['option'] ] : $default;
	}

	/**
	 * Get the link to edit this form.
	 *
	 * @since 4.0
	 * @param int $form_id The id of the form.
	 */
	public static function get_edit_link( $form_id ) {
		return admin_url( 'admin.php?page=formidable&frm_action=edit&id=' . $form_id );
	}

	/**
	 * Check if the "Submit this form with AJAX" setting is toggled on.
	 *
	 * @since 6.2
	 *
	 * @param stdClass $form
	 * @return bool
	 */
	public static function is_ajax_on( $form ) {
		return ! empty( $form->options['ajax_submit'] );
	}

	/**
	 * Get the latest form available.
	 *
	 * @since 6.8
	 * @return object
	 */
	public static function get_latest_form() {

		$args = array(
			array(
				'or'               => 1,
				'parent_form_id'   => null,
				'parent_form_id <' => 1,
			),
			'is_template' => 0,
			'status !'    => 'trash',
		);

		return self::getAll( $args, 'created_at desc', 1 );
	}

	/**
	 * Count and return total forms.
	 *
	 * @since 6.8
	 * @return int
	 */
	public static function get_forms_count() {

		$args = array(
			array(
				'or'               => 1,
				'parent_form_id'   => null,
				'parent_form_id <' => 1,
			),
			'is_template' => 0,
			'status !'    => 'trash',
		);

		return FrmDb::get_count( 'frm_forms', $args );
	}

	/**
	 * @deprecated 2.03.05 This is still referenced in a few add ons (API, locations).
	 * @codeCoverageIgnore
	 *
	 * @param string $key
	 * @return int form id
	 */
	public static function getIdByKey( $key ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::get_id_by_key' );
		return self::get_id_by_key( $key );
	}

	/**
	 * @deprecated 2.03.05 This is still referenced in the API add on as of v1.13.
	 * @codeCoverageIgnore
	 *
	 * @param int|string $id
	 * @return string
	 */
	public static function getKeyById( $id ) {
		_deprecated_function( __FUNCTION__, '2.03.05', 'FrmForm::get_key_by_id' );
		return self::get_key_by_id( $id );
	}
}
