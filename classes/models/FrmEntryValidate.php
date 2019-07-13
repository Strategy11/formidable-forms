<?php

class FrmEntryValidate {
	public static function validate( $values, $exclude = false ) {
		FrmEntry::sanitize_entry_post( $values );
		$errors = array();

		if ( ! isset( $values['form_id'] ) || ! isset( $values['item_meta'] ) ) {
			$errors['form'] = __( 'There was a problem with your submission. Please try again.', 'formidable' );

			return $errors;
		}

		if ( FrmAppHelper::is_admin() && is_user_logged_in() && ( ! isset( $values[ 'frm_submit_entry_' . $values['form_id'] ] ) || ! wp_verify_nonce( $values[ 'frm_submit_entry_' . $values['form_id'] ], 'frm_submit_entry_nonce' ) ) ) {
			$errors['form'] = __( 'You do not have permission to do that', 'formidable' );
		}

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

		$errors = apply_filters( 'frm_validate_entry', $errors, $values, compact( 'exclude' ) );

		return $errors;
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

		return FrmField::getAll( $where, 'field_order' );
	}

	public static function validate_field( $posted_field, &$errors, $values, $args = array() ) {
		$defaults = array(
			'id'              => $posted_field->id,
			'parent_field_id' => '', // the id of the repeat or embed form
			'key_pointer'     => '', // the pointer in the posted array
			'exclude'         => array(), // exclude these field types from validation
		);
		$args     = wp_parse_args( $args, $defaults );

		if ( empty( $args['parent_field_id'] ) ) {
			$value = isset( $values['item_meta'][ $args['id'] ] ) ? $values['item_meta'][ $args['id'] ] : '';
		} else {
			// value is from a nested form
			$value = $values;
		}

		// Check for values in "Other" fields
		FrmEntriesHelper::maybe_set_other_validation( $posted_field, $value, $args );

		self::maybe_clear_value_for_default_blank_setting( $posted_field, $value );

		// Reset arrays with only one value if it's not a field where array keys need to be preserved
		if ( is_array( $value ) && count( $value ) == 1 && isset( $value[0] ) ) {
			$value = reset( $value );
		}

		if ( ! is_array( $value ) ) {
			$value = trim( $value );
		}

		if ( $posted_field->required == '1' && FrmAppHelper::is_empty_value( $value ) ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $posted_field, 'blank' );
		} elseif ( $posted_field->type == 'text' && ! isset( $_POST['item_name'] ) ) { // WPCS: CSRF ok.
			$_POST['item_name'] = $value;
		}

		FrmEntriesHelper::set_posted_value( $posted_field, $value, $args );

		self::validate_field_types( $errors, $posted_field, $value, $args );

		// Field might want to modify value before other parts of the system
		// e.g. trim off excess values like in the case of fields with limit.
		$value = apply_filters( 'frm_modify_posted_field_value', $value, $errors, $posted_field, $args );

		if ( $value != '' ) {
			self::validate_phone_field( $errors, $posted_field, $value, $args );
		}

		$errors = apply_filters( 'frm_validate_' . $posted_field->type . '_field_entry', $errors, $posted_field, $value, $args );
		$errors = apply_filters( 'frm_validate_field_entry', $errors, $posted_field, $value, $args );
	}

	private static function maybe_clear_value_for_default_blank_setting( $field, &$value ) {
		$placeholder = FrmField::get_option( $field, 'placeholder' );
		$is_default  = ( ! empty( $placeholder ) && $value == $placeholder );
		$is_label    = false;

		if ( ! $is_default ) {
			$position = FrmField::get_option( $field, 'label' );
			if ( empty( $position ) ) {
				$position = FrmStylesController::get_style_val( 'position', $field->form_id );
			}

			$is_label = ( $position == 'inside' && FrmFieldsHelper::is_placeholder_field_type( $field->type ) && $value == $field->name );
		}

		if ( $is_label || $is_default ) {
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
		if ( $field->type == 'phone' || ( $field->type == 'text' && FrmField::is_option_true_in_object( $field, 'format' ) ) ) {

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
		$pattern = str_replace( 'a', '[a-z]', $pattern );
		$pattern = str_replace( 'A', '[A-Z]', $pattern );
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
	 * Check for spam
	 *
	 * @param boolean $exclude
	 * @param array $values
	 * @param array $errors by reference
	 */
	public static function spam_check( $exclude, $values, &$errors ) {
		if ( ! empty( $exclude ) || ! isset( $values['item_meta'] ) || empty( $values['item_meta'] ) || ! empty( $errors ) ) {
			// only check spam if there are no other errors
			return;
		}

		if ( self::is_honeypot_spam() || self::is_spam_bot() ) {
			$errors['spam'] = __( 'Your entry appears to be spam!', 'formidable' );
		}

		if ( self::blacklist_check( $values ) ) {
			$errors['spam'] = __( 'Your entry appears to be blacklist spam!', 'formidable' );
		}

		if ( self::is_akismet_spam( $values ) ) {
			if ( self::is_akismet_enabled_for_user( $values['form_id'] ) ) {
				$errors['spam'] = __( 'Your entry appears to be spam!', 'formidable' );
			}
		}
	}

	private static function is_honeypot_spam() {
		$honeypot_value = FrmAppHelper::get_param( 'frm_verify', '', 'get', 'sanitize_text_field' );

		return ( $honeypot_value !== '' );
	}

	private static function is_spam_bot() {
		$ip = FrmAppHelper::get_ip_address();

		return empty( $ip );
	}

	private static function is_akismet_spam( $values ) {
		global $wpcom_api_key;

		return ( is_callable( 'Akismet::http_post' ) && ( get_option( 'wordpress_api_key' ) || $wpcom_api_key ) && self::akismet( $values ) );
	}

	private static function is_akismet_enabled_for_user( $form_id ) {
		$form = FrmForm::getOne( $form_id );

		return ( isset( $form->options['akismet'] ) && ! empty( $form->options['akismet'] ) && ( $form->options['akismet'] != 'logged' || ! is_user_logged_in() ) );
	}

	public static function blacklist_check( $values ) {
		if ( ! apply_filters( 'frm_check_blacklist', true, $values ) ) {
			return false;
		}

		$mod_keys = trim( get_option( 'blacklist_keys' ) );
		if ( empty( $mod_keys ) ) {
			return false;
		}

		$content = FrmEntriesHelper::entry_array_to_string( $values );
		if ( empty( $content ) ) {
			return false;
		}

		$ip         = FrmAppHelper::get_ip_address();
		$user_agent = FrmAppHelper::get_server_value( 'HTTP_USER_AGENT' );
		$user_info  = self::get_spam_check_user_info( $values );

		return wp_blacklist_check( $user_info['comment_author'], $user_info['comment_author_email'], $user_info['comment_author_url'], $content, $ip, $user_agent );
	}

	/**
	 * Check entries for Akismet spam
	 *
	 * @return boolean true if is spam
	 */
	public static function akismet( $values ) {
		$content = FrmEntriesHelper::entry_array_to_string( $values );
		if ( empty( $content ) ) {
			return false;
		}

		$datas = array(
			'comment_type'    => 'formidable',
			'comment_content' => $content,
		);
		self::parse_akismet_array( $datas, $values );

		$query_string = _http_build_query( $datas, '', '&' );
		$response     = Akismet::http_post( $query_string, 'comment-check' );

		return ( is_array( $response ) && $response[1] == 'true' );
	}

	/**
	 * @since 2.0
	 */
	private static function parse_akismet_array( &$datas, $values ) {
		self::add_site_info_to_akismet( $datas );
		self::add_user_info_to_akismet( $datas, $values );
		self::add_server_values_to_akismet( $datas );
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

	private static function get_spam_check_user_info( $values ) {
		$datas = array();

		if ( is_user_logged_in() ) {
			$user = wp_get_current_user();

			$datas['user_ID']              = $user->ID;
			$datas['user_id']              = $user->ID;
			$datas['comment_author']       = $user->display_name;
			$datas['comment_author_email'] = $user->user_email;
			$datas['comment_author_url']   = $user->user_url;
		} else {
			$datas['comment_author']       = '';
			$datas['comment_author_email'] = '';
			$datas['comment_author_url']   = '';

			$values = array_filter( $values );
			foreach ( $values as $value ) {
				if ( ! is_array( $value ) ) {
					if ( $datas['comment_author_email'] == '' && strpos( $value, '@' ) && is_email( $value ) ) {
						$datas['comment_author_email'] = $value;
					} elseif ( $datas['comment_author_url'] == '' && strpos( $value, 'http' ) === 0 ) {
						$datas['comment_author_url'] = $value;
					} elseif ( $datas['comment_author'] == '' && ! is_numeric( $value ) && strlen( $value ) < 200 ) {
						$datas['comment_author'] = $value;
					}
				}
			}
		}

		return $datas;
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
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function validate_url_field( &$errors, $field, $value, $args ) {
		FrmDeprecated::validate_url_field( $errors, $field, $value, $args );
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function validate_email_field( &$errors, $field, $value, $args ) {
		FrmDeprecated::validate_email_field( $errors, $field, $value, $args );
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function validate_number_field( &$errors, $field, $value, $args ) {
		FrmDeprecated::validate_number_field( $errors, $field, $value, $args );
	}

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function validate_recaptcha( &$errors, $field, $args ) {
		FrmDeprecated::validate_recaptcha( $errors, $field, $args );
	}
}
