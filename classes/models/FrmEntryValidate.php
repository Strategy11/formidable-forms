<?php

class FrmEntryValidate {
    public static function validate( $values, $exclude = false ) {
        global $wpdb;

        FrmEntry::sanitize_entry_post( $values );
        $errors = array();

        if ( ! isset($values['form_id']) || ! isset($values['item_meta']) ) {
            $errors['form'] = __( 'There was a problem with your submission. Please try again.', 'formidable' );
            return $errors;
        }

		if ( FrmAppHelper::is_admin() && is_user_logged_in() && ( ! isset( $values[ 'frm_submit_entry_' . $values['form_id'] ] ) || ! wp_verify_nonce( $values[ 'frm_submit_entry_' . $values['form_id'] ], 'frm_submit_entry_nonce' ) ) ) {
            $errors['form'] = __( 'You do not have permission to do that', 'formidable' );
        }

        if ( ! isset($values['item_key']) || $values['item_key'] == '' ) {
            $_POST['item_key'] = $values['item_key'] = FrmAppHelper::get_unique_key('', $wpdb->prefix .'frm_items', 'item_key');
        }

        $where = apply_filters('frm_posted_field_ids', array( 'fi.form_id' => $values['form_id'] ) );
		// Don't get subfields
		$where['fr.parent_form_id'] = array( null, 0 );
		// Don't get excluded fields (like file upload fields in the ajax validation)
		if ( ! empty( $exclude ) ) {
			$where['fi.type not'] = $exclude;
		}

        $posted_fields = FrmField::getAll($where, 'field_order');

        // Pass exclude value to validate_field function so it can be used for repeating sections
        $args = array( 'exclude' => $exclude );

        foreach ( $posted_fields as $posted_field ) {
            self::validate_field($posted_field, $errors, $values, $args);
            unset($posted_field);
        }

        // check for spam
        self::spam_check( $exclude, $values, $errors );

        $errors = apply_filters( 'frm_validate_entry', $errors, $values, compact('exclude') );

        return $errors;
    }

    public static function validate_field( $posted_field, &$errors, $values, $args = array() ) {
        $defaults = array(
            'id'              => $posted_field->id,
            'parent_field_id' => '', // the id of the repeat or embed form
            'key_pointer'     => '', // the pointer in the posted array
            'exclude'         => array(), // exclude these field types from validation
        );
        $args = wp_parse_args( $args, $defaults );

        if ( empty($args['parent_field_id']) ) {
			$value = isset( $values['item_meta'][ $args['id'] ] ) ? $values['item_meta'][ $args['id'] ] : '';
        } else {
            // value is from a nested form
            $value = $values;
        }

        // Check for values in "Other" fields
        FrmEntriesHelper::maybe_set_other_validation( $posted_field, $value, $args );

        if ( isset($posted_field->field_options['default_blank']) && $posted_field->field_options['default_blank'] && $value == $posted_field->default_value ) {
            $value = '';
        }

		// Check for an array with only one value
		// Don't reset values in "Other" fields because array keys need to be preserved
		if ( is_array($value) && count( $value ) == 1 && $args['other'] !== true ) {
			$value = reset($value);
		}

        if ( $posted_field->required == '1' && ! is_array( $value ) && trim( $value ) == '' ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $posted_field, 'blank' );
        } else if ( $posted_field->type == 'text' && ! isset( $_POST['item_name'] ) ) {
            $_POST['item_name'] = $value;
        }

		if ( $value != '' ) {
			self::validate_url_field( $errors, $posted_field, $value, $args );
			self::validate_email_field( $errors, $posted_field, $value, $args );
			self::validate_number_field( $errors, $posted_field, $value, $args );
			self::validate_phone_field( $errors, $posted_field, $value, $args );
		}

        FrmEntriesHelper::set_posted_value($posted_field, $value, $args);

        self::validate_recaptcha($errors, $posted_field, $args);

        $errors = apply_filters('frm_validate_field_entry', $errors, $posted_field, $value, $args);
    }

	public static function validate_url_field( &$errors, $field, &$value, $args ) {
		if ( $value == '' || ! in_array( $field->type, array( 'website', 'url', 'image' ) ) ) {
            return;
        }

        if ( trim($value) == 'http://' ) {
            $value = '';
        } else {
            $value = esc_url_raw( $value );
            $value = preg_match('/^(https?|ftps?|mailto|news|feed|telnet):/is', $value) ? $value : 'http://'. $value;
        }

        //validate the url format
        if ( ! preg_match('/^http(s)?:\/\/([\da-z\.-]+)\.([\da-z\.-]+)/i', $value) ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $field, 'invalid' );
        }
    }

	public static function validate_email_field( &$errors, $field, $value, $args ) {
        if ( $value == '' || $field->type != 'email' ) {
            return;
        }

        //validate the email format
        if ( ! is_email($value) ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $field, 'invalid' );
        }
    }

	public static function validate_number_field( &$errors, $field, $value, $args ) {
		//validate the number format
		if ( $field->type != 'number' ) {
			return;
		}

		if ( ! is_numeric( $value) ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $field, 'invalid' );
		}

		// validate number settings
		if ( $value != '' ) {
			$frm_settings = FrmAppHelper::get_settings();
			// only check if options are available in settings
			if ( $frm_settings->use_html && isset( $field->field_options['minnum'] ) && isset( $field->field_options['maxnum'] ) ) {
				//minnum maxnum
				if ( (float) $value < $field->field_options['minnum'] ) {
					$errors[ 'field' . $args['id'] ] = __( 'Please select a higher number', 'formidable' );
				} else if ( (float) $value > $field->field_options['maxnum'] ) {
					$errors[ 'field' . $args['id'] ] = __( 'Please select a lower number', 'formidable' );
				}
			}
		}
	}

	public static function validate_phone_field( &$errors, $field, $value, $args ) {
		if ( $field->type != 'phone' ) {
			return;
		}

		$pattern = self::phone_format( $field );

		if ( ! preg_match( $pattern, $value ) ) {
			$errors[ 'field' . $args['id'] ] = FrmFieldsHelper::get_error_msg( $field, 'invalid' );
		}
	}

	public static function phone_format( $field ) {
		$default_format = '^((\+\d{1,3}(-|.| )?\(?\d\)?(-| |.)?\d{1,5})|(\(?\d{2,6}\)?))(-|.| )?(\d{3,4})(-|.| )?(\d{4})(( x| ext)\d{1,5}){0,1}$';
		if ( FrmField::is_option_empty( $field, 'format' ) ) {
			$pattern = $default_format;
		} else {
			$pattern = FrmField::get_option( $field, 'format' );
		}

		$pattern = apply_filters( 'frm_phone_pattern', $pattern, $field );

		//check if format is already a regular expression
		if ( strpos( $pattern, '^' ) !== 0 ) {
			//if not, create a regular expression
			$pattern = preg_replace( '/\d/', '\d', preg_quote( $pattern ) );
			$pattern = str_replace( 'a', '[a-z]', $pattern );
			$pattern = str_replace( 'A', '[A-Z]', $pattern );
			$pattern = str_replace( '*', 'w', $pattern );
			$pattern = str_replace( '/', '\/', $pattern );

			if ( strpos( $pattern, '\?' ) !== false ) {
				$parts = explode( '\?', $pattern );
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
		}

		$pattern = '/' . $pattern . '/';
		return $pattern;
	}

	public static function validate_recaptcha( &$errors, $field, $args ) {
        if ( $field->type != 'captcha' || FrmAppHelper::is_admin() || apply_filters( 'frm_is_field_hidden', false, $field, stripslashes_deep( $_POST ) ) ) {
            return;
        }

		$frm_settings = FrmAppHelper::get_settings();
		if ( empty( $frm_settings->pubkey ) ) {
			// don't require the captcha if it shouldn't be shown
			return;
		}

        if ( ! isset($_POST['g-recaptcha-response']) ) {
            // If captcha is missing, check if it was already verified
			if ( ! isset( $_POST['recaptcha_checked'] ) || ! wp_verify_nonce( $_POST['recaptcha_checked'], 'frm_ajax' ) ) {
                // There was no captcha submitted
				$errors[ 'field' . $args['id'] ] = __( 'The captcha is missing from this form', 'formidable' );
            }
            return;
        }

        $arg_array = array(
            'body'      => array(
				'secret'   => $frm_settings->privkey,
				'response' => $_POST['g-recaptcha-response'],
				'remoteip' => FrmAppHelper::get_ip_address(),
			),
		);
        $resp = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', $arg_array );
        $response = json_decode(wp_remote_retrieve_body( $resp ), true);

        if ( isset( $response['success'] ) && ! $response['success'] ) {
            // What happens when the CAPTCHA was entered incorrectly
			$errors[ 'field' . $args['id'] ] = ( ! isset( $field->field_options['invalid'] ) || $field->field_options['invalid'] == '' ) ? $frm_settings->re_msg : $field->field_options['invalid'];
        }
    }

    /**
     * check for spam
     * @param boolean $exclude
     * @param array $values
     * @param array $errors by reference
     */
    public static function spam_check( $exclude, $values, &$errors ) {
        if ( ! empty( $exclude ) || ! isset( $values['item_meta'] ) || empty( $values['item_meta'] ) || ! empty( $errors ) ) {
            // only check spam if there are no other errors
            return;
        }

        if ( self::is_akismet_spam( $values ) ) {
			if ( self::is_akismet_enabled_for_user( $values['form_id'] ) ) {
				$errors['spam'] = __( 'Your entry appears to be spam!', 'formidable' );
			}
	    }

    	if ( self::blacklist_check( $values ) ) {
            $errors['spam'] = __( 'Your entry appears to be spam!', 'formidable' );
    	}
    }

	private static function is_akismet_spam( $values ) {
		global $wpcom_api_key;
		return ( is_callable('Akismet::http_post') && ( get_option('wordpress_api_key') || $wpcom_api_key ) && self::akismet( $values ) );
	}

	private static function is_akismet_enabled_for_user( $form_id ) {
		$form = FrmForm::getOne( $form_id );
		return ( isset( $form->options['akismet'] ) && ! empty( $form->options['akismet'] ) && ( $form->options['akismet'] != 'logged' || ! is_user_logged_in() ) );
	}

    public static function blacklist_check( $values ) {
        if ( ! apply_filters('frm_check_blacklist', true, $values) ) {
            return false;
        }

    	$mod_keys = trim( get_option( 'blacklist_keys' ) );

    	if ( empty( $mod_keys ) ) {
    		return false;
    	}

    	$content = FrmEntriesHelper::entry_array_to_string($values);

		if ( empty($content) ) {
		    return false;
		}

    	$words = explode( "\n", $mod_keys );

    	foreach ( (array) $words as $word ) {
    		$word = trim( $word );

    		if ( empty($word) ) {
    			continue;
    		}

    		if ( preg_match('#' . preg_quote( $word, '#' ) . '#', $content) ) {
    			return true;
    		}
    	}

    	return false;
    }

    /**
     * Check entries for spam
     *
     * @return boolean true if is spam
     */
    public static function akismet( $values ) {
	    $content = FrmEntriesHelper::entry_array_to_string( $values );

		if ( empty( $content ) ) {
		    return false;
		}

        $datas = array();
        self::parse_akismet_array( $datas, $content );

		$query_string = '';
		foreach ( $datas as $key => $data ) {
			$query_string .= $key . '=' . urlencode( stripslashes( $data ) ) . '&';
			unset( $key, $data );
		}

        $response = Akismet::http_post($query_string, 'comment-check');

		return ( is_array( $response ) && $response[1] == 'true' );
    }

    /**
     * @since 2.0
     * @param string $content
     */
    private  static function parse_akismet_array( &$datas, $content ) {
        $datas['blog'] = FrmAppHelper::site_url();
        $datas['user_ip'] = preg_replace( '/[^0-9., ]/', '', FrmAppHelper::get_ip_address() );
		$datas['user_agent'] = FrmAppHelper::get_server_value( 'HTTP_USER_AGENT' );
		$datas['referrer'] = isset( $_SERVER['HTTP_REFERER'] ) ? FrmAppHelper::get_server_value( 'HTTP_REFERER' ) : false;
        $datas['comment_type'] = 'formidable';
        $datas['comment_content'] = $content;

        if ( $permalink = get_permalink() ) {
            $datas['permalink'] = $permalink;
        }

        foreach ( $_SERVER as $key => $value ) {
			if ( ! in_array( $key, array( 'HTTP_COOKIE', 'HTTP_COOKIE2', 'PHP_AUTH_PW' ) ) && is_string( $value ) ) {
				$datas[ $key ] = wp_strip_all_tags( $value );
            } else {
				$datas[ $key ] = '';
            }

            unset($key, $value);
        }
    }
}
