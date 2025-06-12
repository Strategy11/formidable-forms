<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmEntryValidate {

	/**
	 * @since 6.17
	 *
	 * @var array|null
	 */
	private static $name_text_fields;

	/**
	 * @param array         $values
	 * @param bool|string[] $exclude
	 * @return array
	 */
	public static function validate( $values, $exclude = false ) {
		FrmEntry::sanitize_entry_post( $values );
		$errors = array();

		if ( ! isset( $values['form_id'] ) || ! isset( $values['item_meta'] ) ) {
			$errors['form'] = __( 'There was a problem with your submission. Please try again.', 'formidable' );

			return $errors;
		}

		if ( FrmAppHelper::is_admin() && is_user_logged_in() && ( ! isset( $values[ 'frm_submit_entry_' . $values['form_id'] ] ) || ! wp_verify_nonce( $values[ 'frm_submit_entry_' . $values['form_id'] ], 'frm_submit_entry_nonce' ) ) ) {
			$frm_settings   = FrmAppHelper::get_settings();
			$errors['form'] = $frm_settings->admin_permission;
		}

		self::maybe_fix_item_meta();
		self::set_item_key( $values );

		$posted_fields = self::get_fields_to_validate( $values, $exclude );

		// Pass exclude value to validate_field function so it can be used for repeating sections
		$args = array( 'exclude' => $exclude );

		foreach ( $posted_fields as $posted_field ) {
			self::validate_field( $posted_field, $errors, $values, $args );
			unset( $posted_field );
		}

		if ( empty( $errors ) ) {
			self::spam_check( $exclude, $values, $errors );
		}

		/**
		 * Allows modifying the validation errors after validating all fields.
		 *
		 * @since 5.0.04 Added `posted_fields` to the third param.
		 *
		 * @param array $errors Errors data.
		 * @param array $values Value data of the form.
		 * @param array $args   Custom arguments. Contains `exclude` and `posted_fields`.
		 */
		$filtered_errors = apply_filters( 'frm_validate_entry', $errors, $values, compact( 'exclude', 'posted_fields' ) );

		if ( is_array( $filtered_errors ) ) {
			$errors = $filtered_errors;
		} else {
			_doing_it_wrong( __METHOD__, 'Only arrays should be returned when using the frm_validate_entry filter.', '6.3' );
		}

		return $errors;
	}

	/**
	 * In case $_POST['item_meta'] is not an array, change it to an empty array.
	 * This helps to avoid some warnings and errors when $_POST['item_meta'] is updated.
	 *
	 * @since 6.6
	 *
	 * @return void
	 */
	private static function maybe_fix_item_meta() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		if ( ! isset( $_POST['item_meta'] ) || ! is_array( $_POST['item_meta'] ) ) {
			$_POST['item_meta'] = array();
		}
	}

	private static function set_item_key( &$values ) {
		if ( ! isset( $values['item_key'] ) || $values['item_key'] == '' ) {
			global $wpdb;
			$values['item_key'] = FrmAppHelper::get_unique_key( '', $wpdb->prefix . 'frm_items', 'item_key' );
			$_POST['item_key']  = $values['item_key'];
		}
	}

	private static function get_fields_to_validate( $values, $exclude ) {
		$where = apply_filters( 'frm_posted_field_ids', array( 'fi.form_id' => $values['form_id'] ) );

		// Don't get subfields
		$where['fr.parent_form_id'] = array( null, 0 );

		// Don't get excluded fields (like file upload fields in the ajax validation)
		if ( ! empty( $exclude ) ) {
			$where['fi.type not'] = $exclude;
		}

		$fields = FrmField::getAll( $where, 'field_order' );

		/**
		 * Allows modifying fields to validate.
		 *
		 * @since 5.0.06
		 *
		 * @param array $fields List of fields.
		 * @param array $args   Includes `values`, `exclude`, `where`.
		 */
		return apply_filters( 'frm_fields_to_validate', $fields, compact( 'values', 'exclude', 'where' ) );
	}

	public static function validate_field( $posted_field, &$errors, $values, $args = array() ) {
		$defaults = array(
			'id'              => $posted_field->id,
			// The id of the repeat or embed form.
			'parent_field_id' => '',
			// The pointer in the posted array.
			'key_pointer'     => '',
			// Exclude these field types from validation.
			'exclude'         => array(),

		);
		$args = wp_parse_args( $args, $defaults );

		if ( empty( $args['parent_field_id'] ) ) {
			$value = isset( $values['item_meta'][ $args['id'] ] ) ? $values['item_meta'][ $args['id'] ] : '';
		} else {
			// value is from a nested form
			$value = $values;
		}

		// Check for values in "Other" fields
		FrmEntriesHelper::maybe_set_other_validation( $posted_field, $value, $args );

		self::maybe_clear_value_for_default_blank_setting( $posted_field, $value );

		$should_trim = is_array( $value ) && count( $value ) == 1 && isset( $value[0] ) && $posted_field->type !== 'checkbox';
		if ( $should_trim ) {
			$value = reset( $value );
		}

		if ( ! is_array( $value ) ) {
			$value = trim( $value );
		}

		if ( $posted_field->required == '1' && FrmAppHelper::is_empty_value( $value ) ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $posted_field, 'blank' );
		} elseif ( ! isset( $_POST['item_name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			self::maybe_add_item_name( $value, $posted_field );
		}

		FrmEntriesHelper::set_posted_value( $posted_field, $value, $args );

		self::validate_options( $errors, $posted_field, $value, $args );
		self::validate_field_types( $errors, $posted_field, $value, $args );

		// Field might want to modify value before other parts of the system
		// e.g. trim off excess values like in the case of fields with limit.
		$value = apply_filters( 'frm_modify_posted_field_value', $value, $errors, $posted_field, $args );

		if ( $value != '' ) {
			self::validate_phone_field( $errors, $posted_field, $value, $args );
		}

		$errors = apply_filters( 'frm_validate_' . $posted_field->type . '_field_entry', $errors, $posted_field, $value, $args );
		$errors = apply_filters( 'frm_validate_field_entry', $errors, $posted_field, $value, $args );

		if ( ! FrmAppHelper::pro_is_installed() && empty( $args['other'] ) ) {
			FrmEntriesHelper::get_posted_value( $posted_field, $value, $args );
		}
	}

	/**
	 * @since 6.21
	 *
	 * @param array        $errors
	 * @param object       $posted_field
	 * @param array|string $value
	 * @param array        $args
	 *
	 * @return void
	 */
	private static function validate_options( &$errors, $posted_field, $value, $args ) {
		if ( empty( $posted_field->options ) ) {
			return;
		}

		$option_is_valid = self::option_is_valid( $posted_field, $value, $posted_field->options );

		/**
		 * @since 6.21
		 *
		 * @param bool         $option_is_valid
		 * @param array|string $value
		 * @param object       $field
		 */
		$option_is_valid = (bool) apply_filters( 'frm_option_is_valid', $option_is_valid, $value, $posted_field );

		if ( ! $option_is_valid ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $posted_field, 'invalid' );
		}
	}

	/**
	 * Validate that value matches one of the options for the field.
	 *
	 * @since 6.21
	 *
	 * @param stdClass     $field
	 * @param array|string $value
	 * @param array        $options
	 * @return bool
	 */
	private static function option_is_valid( $field, $value, $options ) {
		if ( '' === $value ) {
			return true;
		}

		if ( in_array( $field->type, array( 'likert', 'ranking' ), true ) ) {
			// Ignore these field types automatically.
			return true;
		}

		if ( 'product' === $field->type && 'user_def' === FrmField::get_option( $field, 'data_type' ) ) {
			return true;
		}

		if ( ! empty( $field->field_options['post_field'] ) ) {
			return true;
		}

		$value = (array) $value;

		foreach ( $value as $current_value ) {
			$match = false;

			foreach ( $options as $key => $option ) {
				if ( strpos( $key, 'other_' ) === 0 ) {
					// Always return true if an other option is found.
					return true;
				}

				if ( is_array( $option ) ) {
					$separate_value = FrmField::get_option( $field, 'separate_value' );
					$option_value   = $separate_value ? $option['value'] : $option['label'];
				} else {
					$option_value = $option;
				}

				$match = trim( $current_value ) === trim( $option_value );
				if ( $match ) {
					break;
				}

				$match = trim( $current_value ) === trim( do_shortcode( $option_value ) );
				if ( $match ) {
					break;
				}

				$match = self::is_filtered_match( $current_value, $option_value );
				if ( $match ) {
					break;
				}

				if ( is_numeric( $current_value ) ) {
					$match = (int) $current_value === (int) $option_value;
					if ( $match ) {
						break;
					}
				}
			}//end foreach

			if ( ! $match ) {
				return self::options_are_dynamic_based_on_hook( $field, $value );
			}
		}//end foreach

		return true;
	}

	/**
	 * Make an extra check after passing $option_value through the_content filter.
	 * This is to help catch cases where the option's formatting has been modified using
	 * the_content filter.
	 *
	 * @since 6.22
	 *
	 * @param string $value
	 * @param string $option_value
	 * @return bool
	 */
	private static function is_filtered_match( $value, $option_value ) {
		// First remove the wpautop filter so it doesn't add extra tags to $option_value.
		$filter_priority = has_filter( 'the_content', 'wpautop' );
		if ( is_numeric( $filter_priority ) ) {
			remove_filter( 'the_content', 'wpautop', $filter_priority );
		}
		$filtered_option = apply_filters( 'the_content', $option_value );
		if ( is_numeric( $filter_priority ) ) {
			add_filter( 'the_content', 'wpautop', $filter_priority );
		}
		return trim( $value ) === trim( $filtered_option );
	}

	/**
	 * Do not validate options if they have been modified with a hook.
	 * This is to help avoid issues where the options could be based on a URL param for example.
	 *
	 * @since 6.21
	 *
	 * @return bool
	 */
	private static function options_are_dynamic_based_on_hook( $field_object, $value ) {
		$values          = (array) $field_object;
		$values['value'] = $value;
		FrmFieldsHelper::prepare_new_front_field( $values, $field_object );

		$separate_value = FrmField::get_option( $field_object, 'separate_value' );
		$map_callback   = function ( $option ) use ( $separate_value ) {
			if ( is_array( $option ) ) {
				$option_value = $separate_value ? $option['value'] : $option['label'];
			} else {
				$option_value = $option;
			}
			$option_value = do_shortcode( $option_value );
			return $option_value;
		};

		$values_options       = array_map( $map_callback, $values['options'] );
		$field_object_options = array_map( $map_callback, $field_object->options );

		return $values_options !== $field_object_options;
	}

	/**
	 * Maybe add item_name to $_POST to save it in items table.
	 *
	 * @since 5.2.02
	 *
	 * @param array|string $value Field value.
	 * @param object       $field Field object.
	 */
	private static function maybe_add_item_name( $value, $field ) {
		$item_name = false;
		if ( 'name' === $field->type ) {
			$field_obj = FrmFieldFactory::get_field_object( $field );
			$item_name = $field_obj->get_display_value( $value );
		} elseif ( 'text' === $field->type ) {
			$item_name = $value;
		}

		if ( false !== $item_name ) {
			// Item name has a max length of 255 characters so truncate it so it doesn't fail to save in the database.
			$_POST['item_name'] = substr( $item_name, 0, 255 );
		}
	}

	/**
	 * Set $value to an empty string if it matches its label
	 *
	 * @param object $field
	 * @param string $value
	 */
	private static function maybe_clear_value_for_default_blank_setting( $field, &$value ) {
		$position = FrmField::get_option( $field, 'label' );
		if ( ! $position ) {
			$position = FrmStylesController::get_style_val( 'position', $field->form_id );
		}

		if ( $position === 'inside' && FrmFieldsHelper::is_placeholder_field_type( $field->type ) && $value === $field->name ) {
			$value = '';
		}
	}

	public static function validate_field_types( &$errors, $posted_field, $value, $args ) {
		$field_obj      = FrmFieldFactory::get_field_object( $posted_field );
		$args['value']  = $value;
		$args['errors'] = $errors;

		$new_errors = $field_obj->validate( $args );
		if ( ! empty( $new_errors ) ) {
			$errors = array_merge( $errors, $new_errors );
		}
	}

	public static function validate_phone_field( &$errors, $field, $value, $args ) {
		$format_value = FrmField::get_option( $field, 'format' );

		if ( $field->type === 'phone' || ( $field->type === 'text' && $format_value && ! FrmCurrencyHelper::is_currency_format( $format_value ) ) ) {
			$pattern = self::phone_format( $field );

			if ( ! preg_match( $pattern, $value ) ) {
				$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $field, 'invalid' );
			}
		}
	}

	public static function phone_format( $field ) {
		if ( FrmField::is_option_empty( $field, 'format' ) ) {
			$pattern = self::default_phone_format();
		} else {
			$pattern = FrmField::get_option( $field, 'format' );
		}

		// Ampersands are saved as &amp;.
		// Reverse it here so we are checking for the correct character.
		$pattern = html_entity_decode( $pattern );
		$pattern = apply_filters( 'frm_phone_pattern', $pattern, $field );

		// Create a regexp if format is not already a regexp
		if ( strpos( $pattern, '^' ) !== 0 ) {
			$pattern = self::create_regular_expression_from_format( $pattern );
		}

		$pattern = '/' . $pattern . '/';

		return $pattern;
	}

	/**
	 * @since 3.01
	 */
	private static function default_phone_format() {
		return '^((\+\d{1,3}(-|.| )?\(?\d\)?(-| |.)?\d{1,5})|(\(?\d{2,6}\)?))(-|.| )?(\d{3,4})(-|.| )?(\d{4})(( x| ext)\d{1,5}){0,1}$';
	}

	/**
	 * Create a regular expression from a phone number format
	 *
	 * @since 2.02.02
	 *
	 * @param string $pattern
	 *
	 * @return string
	 */
	private static function create_regular_expression_from_format( $pattern ) {
		$pattern = preg_quote( $pattern );

		// Firefox doesn't like escaped dashes or colons
		$pattern = str_replace( array( '\-', '\:' ), array( '-', ':' ), $pattern );

		// Switch generic values out for their regular expression
		$pattern = preg_replace( '/\d/', '\d', $pattern );
		$pattern = str_replace( 'A', '[A-Z]', $pattern );
		$pattern = str_replace( 'a', '[a-zA-Z]', $pattern );
		$pattern = str_replace( '*', 'w', $pattern );
		$pattern = str_replace( '/', '\/', $pattern );

		if ( strpos( $pattern, '\?' ) !== false ) {
			$parts   = explode( '\?', $pattern );
			$pattern = '';
			foreach ( $parts as $part ) {
				if ( empty( $pattern ) ) {
					$pattern .= $part;
				} else {
					$pattern .= '(' . $part . ')?';
				}
			}
		}
		$pattern = '^' . $pattern . '$';

		return $pattern;
	}

	/**
	 * Check for spam.
	 *
	 * @param bool  $exclude
	 * @param array $values
	 * @param array $errors By reference.
	 */
	public static function spam_check( $exclude, $values, &$errors ) {
		if ( defined( 'WP_IMPORTING' ) && WP_IMPORTING ) {
			// Do not check spam on importing.
			return;
		}

		if ( ! empty( $exclude ) || empty( $values['item_meta'] ) || ! empty( $errors ) ) {
			// only check spam if there are no other errors
			return;
		}

		$antispam_check = self::is_antispam_check( $values['form_id'] );
		$spam_msg       = FrmAntiSpamController::get_default_spam_message();
		if ( is_string( $antispam_check ) ) {
			$errors['spam'] = $antispam_check;
		} elseif ( self::is_honeypot_spam( $values ) || self::is_spam_bot() ) {
			$errors['spam'] = $spam_msg;
		} else {
			$is_spam = FrmAntiSpamController::is_spam( $values );
			if ( $is_spam ) {
				$errors['spam'] = $is_spam;
			}
		}

		if ( isset( $errors['spam'] ) || self::form_is_in_progress( $values ) ) {
			return;
		}

		if ( self::is_akismet_enabled_for_user( $values['form_id'] ) && self::is_akismet_spam( $values ) ) {
			$errors['spam'] = __( 'Your entry appears to be spam!', 'formidable' );
		}
	}

	/**
	 * Checks if form is in progress.
	 *
	 * @since 5.0.13
	 *
	 * @param array $values The values.
	 * @return bool
	 */
	private static function form_is_in_progress( $values ) {
		return FrmAppHelper::pro_is_installed() &&
			( isset( $values[ 'frm_page_order_' . $values['form_id'] ] ) || FrmAppHelper::get_post_param( 'frm_next_page' ) ) &&
			FrmField::get_all_types_in_form( $values['form_id'], 'break' );
	}

	/**
	 * @param int $form_id
	 *
	 * @return bool|string
	 */
	private static function is_antispam_check( $form_id ) {
		$aspm = new FrmAntiSpam( $form_id );
		return $aspm->validate();
	}

	/**
	 * @param array $values
	 * @return bool
	 */
	private static function is_honeypot_spam( $values ) {
		$honeypot = new FrmHoneypot( $values['form_id'] );
		return ! $honeypot->validate();
	}

	/**
	 * @return bool
	 */
	private static function is_spam_bot() {
		$ip = FrmAppHelper::get_ip_address();

		return empty( $ip );
	}

	/**
	 * @param array $values
	 * @return bool
	 */
	private static function is_akismet_spam( $values ) {
		global $wpcom_api_key;

		return ( is_callable( 'Akismet::http_post' ) && ( get_option( 'wordpress_api_key' ) || $wpcom_api_key ) && self::akismet( $values ) );
	}

	/**
	 * @param int $form_id
	 * @return bool
	 */
	private static function is_akismet_enabled_for_user( $form_id ) {
		$form = FrmForm::getOne( $form_id );

		return ( ! empty( $form->options['akismet'] ) && ( $form->options['akismet'] !== 'logged' || ! is_user_logged_in() ) );
	}

	/**
	 * Checks spam using WordPress disallowed words and Frm denylist.
	 *
	 * @param array $values Entry values.
	 *
	 * @return bool
	 */
	public static function blacklist_check( $values ) {
		return FrmAntiSpamController::contains_wp_disallowed_words( $values ) || FrmAntiSpamController::is_denylist_spam( $values );
	}

	/**
	 * Check entries for Akismet spam
	 *
	 * @return bool true if is spam
	 */
	public static function akismet( $values ) {
		if ( empty( $values['item_meta'] ) ) {
			return false;
		}

		$datas = array(
			'comment_type' => 'formidable',
		);
		self::parse_akismet_array( $datas, $values );

		/**
		 * Allows modifying the values sent to Akismet.
		 *
		 * @since 5.0.07
		 *
		 * @param array $datas The array of values being sent to Akismet.
		 */
		$datas = apply_filters( 'frm_akismet_values', $datas );

		$query_string = _http_build_query( $datas, '', '&' );
		$response     = Akismet::http_post( $query_string, 'comment-check' );

		return ( is_array( $response ) && $response[1] === 'true' );
	}

	/**
	 * @since 2.0
	 */
	private static function parse_akismet_array( &$datas, $values ) {
		self::add_site_info_to_akismet( $datas );
		self::add_server_values_to_akismet( $datas );

		self::prepare_values_for_spam_check( $values );
		self::skip_adding_values_to_akismet( $values );

		self::add_user_info_to_akismet( $datas, $values );
		self::add_comment_content_to_akismet( $datas, $values );
	}

	private static function add_site_info_to_akismet( &$datas ) {
		$datas['blog']         = FrmAppHelper::site_url();
		$datas['user_ip']      = preg_replace( '/[^0-9., ]/', '', FrmAppHelper::get_ip_address() );
		$datas['user_agent']   = FrmAppHelper::get_server_value( 'HTTP_USER_AGENT' );
		$datas['referrer']     = isset( $_SERVER['HTTP_REFERER'] ) ? FrmAppHelper::get_server_value( 'HTTP_REFERER' ) : false;
		$datas['blog_lang']    = get_locale();
		$datas['blog_charset'] = get_option( 'blog_charset' );

		if ( akismet_test_mode() ) {
			$datas['is_test'] = 'true';
		}
	}

	private static function add_user_info_to_akismet( &$datas, $values ) {
		$user_info = self::get_spam_check_user_info( $values );
		$datas     = $datas + $user_info;

		if ( isset( $user_info['user_ID'] ) ) {
			$datas['user_role'] = Akismet::get_user_roles( $user_info['user_ID'] );
		}
	}

	/**
	 * Gets user info for Akismet spam check.
	 *
	 * @since 5.0.13 Separate code for guest. Handle value of embedded|repeater.
	 * @since 6.21 This changed from private to public.
	 *
	 * @param array $values Entry values after running through {@see FrmEntryValidate::prepare_values_for_spam_check()}.
	 * @return array
	 */
	public static function get_spam_check_user_info( $values ) {
		if ( ! is_user_logged_in() ) {
			return self::get_spam_check_user_info_for_guest( $values );
		}

		$user = wp_get_current_user();

		return array(
			'user_ID'              => $user->ID,
			'user_id'              => $user->ID,
			'comment_author'       => $user->display_name,
			'comment_author_email' => $user->user_email,
			'comment_author_url'   => $user->user_url,
		);
	}

	/**
	 * Gets user info for Akismet spam check for guest.
	 *
	 * @since 5.0.13
	 *
	 * @param array $values Entry values after flattened.
	 * @return array
	 */
	private static function get_spam_check_user_info_for_guest( $values ) {
		$datas = array(
			'comment_author'       => '',
			'comment_author_email' => '',
			'comment_author_url'   => '',
			'name_field_ids'       => $values['name_field_ids'],
			'missing_keys'         => array( 'comment_author_email', 'comment_author_url', 'comment_author' ),
			'frm_duplicated'       => array(),
		);

		if ( isset( $values['item_meta'] ) ) {
			$values = $values['item_meta'];
		}

		$values = array_filter( $values );

		self::recursive_add_akismet_guest_info( $datas, $values );
		unset( $datas['name_field_ids'] );
		unset( $datas['missing_keys'] );

		return $datas;
	}

	/**
	 * Recursive adds akismet guest info.
	 *
	 * @since 5.0.13
	 *
	 * @param array    $datas        Guest data.
	 * @param array    $values       The values.
	 * @param int|null $custom_index Custom index (or field ID).
	 */
	private static function recursive_add_akismet_guest_info( &$datas, $values, $custom_index = null ) {
		foreach ( $values as $index => $value ) {
			if ( ! $datas['missing_keys'] ) {
				// Found all info.
				return;
			}

			if ( is_array( $value ) ) {
				self::recursive_add_akismet_guest_info( $datas, $value, $index );
				continue;
			}

			$field_id = ! is_null( $custom_index ) ? $custom_index : $index;
			foreach ( $datas['missing_keys'] as $key_index => $key ) {
				$found = self::is_akismet_guest_info_value( $key, $value, $field_id, $datas['name_field_ids'], $values );
				if ( $found ) {
					$datas[ $key ]             = $value;
					$datas['frm_duplicated'][] = $field_id;
					unset( $datas['missing_keys'][ $key_index ] );
				}
			}
		}//end foreach
	}

	/**
	 * Checks if given value is an akismet guest info.
	 *
	 * @since 5.0.13
	 *
	 * @param string $key            Guest info key.
	 * @param string $value          Value to check.
	 * @param int    $field_id       Field ID.
	 * @param array  $name_field_ids Name field IDs.
	 * @param array  $values         Array of posted values.
	 *
	 * @return bool
	 */
	private static function is_akismet_guest_info_value( $key, &$value, $field_id, $name_field_ids, $values ) {
		if ( ! $value || is_numeric( $value ) ) {
			return false;
		}

		switch ( $key ) {
			case 'comment_author_email':
				return strpos( $value, '@' ) && is_email( $value );

			case 'comment_author_url':
				return 0 === strpos( $value, 'http' );

			case 'comment_author':
				if ( $name_field_ids && in_array( $field_id, $name_field_ids, true ) ) {
					// If there is name field in the form, we should always use it as author name.
					return true;
				}
				$form_id = FrmAppHelper::get_post_param( 'form_id', 0, 'absint' );
				$fields  = self::get_name_text_fields( $form_id );

				foreach ( $fields as $index => $field ) {
					if ( 'Name' !== $field->name ) {
						continue;
					}
					if ( isset( $fields[ $index + 1 ] ) && 'Last' === $fields[ $index + 1 ]->name ) {
						if ( empty( $values[ absint( $fields[ $index + 1 ]->id ) ] ) ) {
							continue;
						}
						$value .= ' ' . $values[ $fields[ $index + 1 ]->id ];
						return true;
					}
				}
		}//end switch

		return false;
	}

	/**
	 * Returns fields that have 'Name' and 'Last' as their name.
	 *
	 * @since 6.17
	 *
	 * @param int $form_id
	 * @return array
	 */
	private static function get_name_text_fields( $form_id ) {
		$name_text_fields_is_initialized = is_array( self::$name_text_fields );
		if ( $name_text_fields_is_initialized && isset( self::$name_text_fields[ $form_id ] ) ) {
			return self::$name_text_fields[ $form_id ];
		}
		if ( ! $name_text_fields_is_initialized ) {
			self::$name_text_fields = array();
		}
		self::$name_text_fields[ $form_id ] = FrmDb::get_results(
			'frm_fields',
			array(
				'form_id' => $form_id,
				'type'    => 'text',
				'name'    => array( 'Name', 'Last' ),
			),
			'id,name',
			array( 'order_by' => 'field_order ASC' )
		);

		return self::$name_text_fields[ $form_id ];
	}

	private static function add_server_values_to_akismet( &$datas ) {
		foreach ( $_SERVER as $key => $value ) {
			$include_value = is_string( $value ) && ! preg_match( '/^HTTP_COOKIE/', $key ) && preg_match( '/^(HTTP_|REMOTE_ADDR|REQUEST_URI|DOCUMENT_URI)/', $key );

			// Send any potentially useful $_SERVER vars, but avoid sending junk we don't need.
			if ( $include_value ) {
				$datas[ $key ] = $value;
			}
			unset( $key, $value );
		}
	}

	/**
	 * Adds comment content to Akismet data.
	 *
	 * @since 5.0.09
	 *
	 * @param array $datas  The array of values being sent to Akismet.
	 * @param array $values Entry values.
	 */
	private static function add_comment_content_to_akismet( &$datas, $values ) {
		if ( isset( $datas['frm_duplicated'] ) ) {
			foreach ( $datas['frm_duplicated'] as $index ) {
				if ( isset( $values['item_meta'][ $index ] ) ) {
					unset( $values['item_meta'][ $index ] );
				} else {
					unset( $values[ $index ] );
				}
			}
			unset( $datas['frm_duplicated'] );
		}

		$datas['comment_content'] = FrmEntriesHelper::entry_array_to_string( $values );
	}

	/**
	 * Skips adding field values to Akismet.
	 *
	 * @since 5.0.09
	 *
	 * @param array $values Entry values.
	 */
	private static function skip_adding_values_to_akismet( &$values ) {
		$skipped_fields = self::get_akismet_skipped_field_ids( $values );
		foreach ( $skipped_fields as $skipped_field ) {
			if ( ! isset( $values['item_meta'][ $skipped_field->id ] ) ) {
				continue;
			}

			if ( self::should_really_skip_field( $skipped_field, $values ) ) {
				unset( $values['item_meta'][ $skipped_field->id ] );
				if ( isset( $values['item_meta']['other'][ $skipped_field->id ] ) ) {
					unset( $values['item_meta']['other'][ $skipped_field->id ] );
				}
			}
		}
	}

	/**
	 * Checks if a skip field should be really skipped.
	 *
	 * @since 5.02.04
	 *
	 * @param object $field_data Object contains `id` and `options`.
	 * @param array  $values     Entry values.
	 * @return bool
	 */
	private static function should_really_skip_field( $field_data, $values ) {
		if ( empty( $field_data->options ) ) {
			// This is skipped field types.
			return true;
		}

		FrmAppHelper::unserialize_or_decode( $field_data->options );
		if ( ! $field_data->options ) {
			// Check if an error happens when unserializing, or empty options.
			return true;
		}

		end( $field_data->options );
		$last_key = key( $field_data->options );

		// If a choice field has no Other option.
		if ( is_numeric( $last_key ) || 0 !== strpos( $last_key, 'other_' ) ) {
			return true;
		}

		// If a choice field has Other option, but Other is not selected.
		if ( empty( $values['item_meta']['other'][ $field_data->id ] ) ) {
			return true;
		}

		// Check if submitted value is same as one of field option.
		foreach ( $field_data->options as $option ) {
			$option_value = ! is_array( $option ) ? $option : ( isset( $option['value'] ) ? $option['value'] : '' );
			if ( $values['item_meta']['other'][ $field_data->id ] === $option_value ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Gets field IDs that are skipped from sending to Akismet spam check.
	 *
	 * @since 5.0.09
	 * @since 5.0.13 Move out get_all_form_ids_and_flatten_meta() call and get `form_ids` from `$values`.
	 * @since 5.2.04 This method returns array of object contains `id` and `options` instead of array of `id` only.
	 *
	 * @param array $values Entry values after running through {@see FrmEntryValidate::prepare_values_for_spam_check()}.
	 * @return array
	 */
	private static function get_akismet_skipped_field_ids( $values ) {
		if ( empty( $values['form_ids'] ) ) {
			return array();
		}

		$skipped_types   = array( 'divider', 'form', 'hidden', 'user_id', 'file', 'date', 'time', 'scale', 'star', 'range', 'toggle', 'data', 'lookup', 'likert', 'nps' );
		$has_other_types = array( 'radio', 'checkbox', 'select' );

		$where = array(
			array(
				'form_id' => $values['form_ids'],
				'type'    => array_merge( $skipped_types, $has_other_types ),
			),
		);

		return FrmDb::get_results( 'frm_fields', $where, 'id,options' );
	}

	/**
	 * Prepares values array for spam check.
	 *
	 * @since 5.0.13
	 * @since 6.21 This changed from private to public.
	 *
	 * @param array $values Entry values.
	 */
	public static function prepare_values_for_spam_check( &$values ) {
		$form_ids           = self::get_all_form_ids_and_flatten_meta( $values );
		$values['form_ids'] = $form_ids;
	}

	/**
	 * Gets all form IDs (include child form IDs) and flatten item_meta array. Used for skipping values sent to Akismet.
	 * This also removes some unused data from the item_meta.
	 *
	 * @since 5.0.09
	 * @since 5.0.13 Convert name field value to string.
	 *
	 * @param array $values Entry values.
	 * @return array Form IDs.
	 */
	private static function get_all_form_ids_and_flatten_meta( &$values ) {
		$values['name_field_ids'] = array();

		// Blacklist check for File field in the old version doesn't contain `form_id`.
		$form_ids = isset( $values['form_id'] ) ? array( absint( $values['form_id'] ) ) : array();
		foreach ( $values['item_meta'] as $field_id => $value ) {
			if ( ! is_numeric( $field_id ) ) {
				// Maybe `other`.
				continue;
			}

			// Convert name array to string.
			if ( isset( $value['first'] ) && isset( $value['last'] ) ) {
				$values['item_meta'][ $field_id ] = trim( implode( ' ', $value ) );
				$values['name_field_ids'][]       = $field_id;
				continue;
			}

			if ( ! is_array( $value ) || empty( $value['form'] ) ) {
				continue;
			}

			$form_ids[] = absint( $value['form'] );

			foreach ( $value as $subindex => $subvalue ) {
				if ( ! is_numeric( $subindex ) || ! is_array( $subvalue ) ) {
					continue;
				}

				foreach ( $subvalue as $subsubindex => $subsubvalue ) {
					if ( ! $subsubvalue ) {
						continue;
					}

					if ( ! isset( $values['item_meta'][ $subsubindex ] ) ) {
						$values['item_meta'][ $subsubindex ] = array();
					}

					// Convert name array to string.
					if ( isset( $subsubvalue['first'] ) && isset( $subsubvalue['last'] ) ) {
						$subsubvalue = trim( implode( ' ', $subsubvalue ) );

						$values['name_field_ids'][] = $subsubindex;
					}

					if ( is_array( $values['item_meta'][ $subsubindex ] ) ) {
						$values['item_meta'][ $subsubindex ][] = $subsubvalue;
					}
				}
			}//end foreach

			unset( $values['item_meta'][ $field_id ] );
		}//end foreach

		return $form_ids;
	}
}
