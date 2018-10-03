<?php

class FrmFieldsController {

    public static function load_field() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
        check_ajax_referer( 'frm_ajax', 'nonce' );

        $fields = $_POST['field'];
        if ( empty( $fields ) ) {
            wp_die();
        }

        $_GET['page'] = 'formidable';
        $fields = stripslashes_deep( $fields );

		$values = array(
			'id' => FrmAppHelper::get_post_param( 'form_id', '', 'absint' ),
			'doing_ajax' => true,
		);
        $field_html = array();

        foreach ( $fields as $field ) {
			$field = htmlspecialchars_decode( nl2br( $field ) );
			$field = json_decode( $field );
			if ( ! isset( $field->id ) || ! is_numeric( $field->id ) ) {
				// this field may have already been loaded
				continue;
			}

			if ( ! isset( $field->value ) ) {
				$field->value = '';
			}
			$field->field_options = json_decode( json_encode( $field->field_options ), true );
			$field->options = json_decode( json_encode( $field->options ), true );
			$field->default_value = json_decode( json_encode( $field->default_value ), true );

			ob_start();
			self::load_single_field( $field, $values );
			$field_html[ absint( $field->id ) ] = ob_get_contents();
			ob_end_clean();
        }

		echo json_encode( $field_html );

        wp_die();
    }

	/**
	 * Create a new field with ajax
	 */
    public static function create() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
        check_ajax_referer( 'frm_ajax', 'nonce' );

		$field_type = FrmAppHelper::get_post_param( 'field_type', '', 'sanitize_text_field' );
		$form_id = FrmAppHelper::get_post_param( 'form_id', 0, 'absint' );

		$field = self::include_new_field( $field_type, $form_id );

		// this hook will allow for multiple fields to be added at once
		do_action( 'frm_after_field_created', $field, $form_id );

        wp_die();
    }

    /**
     * Set up and create a new field
     *
     * @param string $field_type
     * @param integer $form_id
     * @return array|bool
     */
	public static function include_new_field( $field_type, $form_id ) {
		$field_values = FrmFieldsHelper::setup_new_vars( $field_type, $form_id );
        $field_values = apply_filters( 'frm_before_field_created', $field_values );

        $field_id = FrmField::create( $field_values );

        if ( ! $field_id ) {
            return false;
        }

		$field = self::get_field_array_from_id( $field_id );

		$values = array();
		if ( FrmAppHelper::pro_is_installed() ) {
			$values['post_type'] = FrmProFormsHelper::post_type( $form_id );

			$parent_form_id = FrmDb::get_var( 'frm_forms', array( 'id' => $form_id ), 'parent_form_id' );
			if ( $parent_form_id ) {
				$field['parent_form_id'] = $parent_form_id;
			}
		}

		self::load_single_field( $field, $values, $form_id );

        return $field;
    }

	/**
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 */
	public static function edit_name( $field = 'name', $id = '' ) {
		_deprecated_function( __FUNCTION__, '3.0' );

		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		if ( empty( $field ) ) {
			$field = 'name';
		}

        if ( empty( $id ) ) {
			$id = FrmAppHelper::get_post_param( 'element_id', '', 'sanitize_title' );
			$id = str_replace( 'field_label_', '', $id );
        }

		$value = FrmAppHelper::get_post_param( 'update_value', '', 'wp_kses_post' );
		$value = trim( $value );
        if ( trim( strip_tags( $value ) ) === '' ) {
            // set blank value if there is no content
            $value = '';
        }

		FrmField::update( $id, array( $field => $value ) );

		do_action( 'frm_after_update_field_' . $field, compact( 'id', 'value' ) );

		echo stripslashes( wp_kses_post( $value ) ); // WPCS: XSS ok.
        wp_die();
    }

    public static function update_ajax_option() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
        check_ajax_referer( 'frm_ajax', 'nonce' );

		$field_id = FrmAppHelper::get_post_param( 'field', 0, 'absint' );
		if ( ! $field_id ) {
			wp_die();
		}

		$field = FrmField::getOne( $field_id );

		if ( isset( $_POST['separate_value'] ) ) {
			$new_val = FrmField::is_option_true( $field, 'separate_value' ) ? 0 : 1;
			$field->field_options['separate_value'] = $new_val;
			unset( $new_val );
		}

		FrmField::update(
			$field_id,
			array(
				'field_options' => $field->field_options,
				'form_id'       => $field->form_id,
			)
		);
        wp_die();
    }

    public static function duplicate() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
        check_ajax_referer( 'frm_ajax', 'nonce' );

		$field_id = FrmAppHelper::get_post_param( 'field_id', 0, 'absint' );
		$form_id = FrmAppHelper::get_post_param( 'form_id', 0, 'absint' );

		$copy_field = FrmField::getOne( $field_id );
        if ( ! $copy_field ) {
			wp_die();
        }

		do_action( 'frm_duplicate_field', $copy_field, $form_id );
		do_action( 'frm_duplicate_field_' . $copy_field->type, $copy_field, $form_id );

		$values = array(
			'id' => $copy_field->id,
		);
		FrmFieldsHelper::fill_field( $values, $copy_field, $copy_field->form_id );
		$values = apply_filters( 'frm_prepare_single_field_for_duplication', $values );

		$field_id = FrmField::create( $values );
		if ( $field_id ) {
			self::load_single_field( $field_id, $values );
		}

        wp_die();
    }

	/**
	 * Load a single field in the form builder along with all needed variables
	 *
	 * @deprecated 3.0
	 * @codeCoverageIgnore
	 *
	 * @param int $field_id
	 * @param array $values
	 * @param int $form_id
	 *
	 * @return array
	 */
	public static function include_single_field( $field_id, $values, $form_id = 0 ) {
		_deprecated_function( __FUNCTION__, '3.0', 'FrmFieldsController::load_single_field' );

		$field = FrmFieldsHelper::setup_edit_vars( FrmField::getOne( $field_id ) );
		self::load_single_field( $field, $values, $form_id );

		return $field;
	}

	/**
	 * @since 3.0
	 *
	 * @param int $field_id
	 *
	 * @return array
	 */
	public static function get_field_array_from_id( $field_id ) {
		$field = FrmField::getOne( $field_id );
		return FrmFieldsHelper::setup_edit_vars( $field );
	}

	/**
	 * @since 3.0
	 *
	 * @param int|array|object $field_object
	 * @param array $values
	 * @param int $form_id
	 */
	public static function load_single_field( $field_object, $values, $form_id = 0 ) {
		global $frm_vars;
		$frm_vars['is_admin'] = true;

		if ( is_numeric( $field_object ) ) {
			$field_object = FrmField::getOne( $field_object );
		} elseif ( is_array( $field_object ) ) {
			$field = $field_object;
			$field_object = FrmField::getOne( $field['id'] );
		}

		$field_obj = FrmFieldFactory::get_field_factory( $field_object );
		$display = self::display_field_options( array(), $field_obj );

		$ajax_loading = isset( $values['ajax_load'] ) && $values['ajax_load'];
		$ajax_this_field = isset( $values['count'] ) && $values['count'] > 10 && ! in_array( $field_object->type, array( 'divider', 'end_divider' ) );

		if ( $ajax_loading && $ajax_this_field ) {
			$li_classes = self::get_classes_for_builder_field( array(), $display, $field_obj );
			include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/ajax-field-placeholder.php' );
		} else {
			$frm_settings = FrmAppHelper::get_settings();

			$pro_field_selection = FrmField::pro_field_selection();
			$frm_all_field_selection = array_merge( FrmField::field_selection(), $pro_field_selection );
			$disabled_fields = FrmAppHelper::pro_is_installed() ? array() : $pro_field_selection;

			if ( ! isset( $field ) && is_object( $field_object ) ) {
				$field_object->parent_form_id = isset( $values['id'] ) ? $values['id'] : $field_object->form_id;
				$field = FrmFieldsHelper::setup_edit_vars( $field_object );
			}

			$li_classes = self::get_classes_for_builder_field( $field, $display, $field_obj );
			$li_classes .= ' ui-state-default widgets-holder-wrap';

			require( FrmAppHelper::plugin_path() . '/classes/views/frm-forms/add_field.php' );
		}
	}

	/**
	 * @since 3.0
	 */
	private static function get_classes_for_builder_field( $field, $display, $field_info ) {
		$li_classes = $field_info->form_builder_classes( $display['type'] );
		if ( ! empty( $field ) ) {
			$li_classes = apply_filters( 'frm_build_field_class', $li_classes, $field );
		}
		return $li_classes;
	}

    public static function destroy() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
        check_ajax_referer( 'frm_ajax', 'nonce' );

		$field_id = FrmAppHelper::get_post_param( 'field_id', 0, 'absint' );
		FrmField::destroy( $field_id );
        wp_die();
    }

    /* Field Options */

    //Add Single Option or Other Option
    public static function add_option() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
        check_ajax_referer( 'frm_ajax', 'nonce' );

		$id = FrmAppHelper::get_post_param( 'field_id', 0, 'absint' );
		$opt_type = FrmAppHelper::get_post_param( 'opt_type', '', 'sanitize_text_field' );
		$opt_key = FrmAppHelper::get_post_param( 'opt_key', 0, 'absint' );

		$field = FrmField::getOne( $id );

		if ( 'other' == $opt_type ) {
			$opt = __( 'Other', 'formidable' );
			$opt_key = 'other_' . $opt_key;
		} else {
			$opt = __( 'New Option', 'formidable' );
		}

		$field_data = $field;
		$field = (array) $field;
		$field['separate_value'] = isset( $field_data->field_options['separate_value'] ) ? $field_data->field_options['separate_value'] : 0;
		unset( $field_data );
		$field['html_name'] = 'item_meta[' . $field['id'] . ']';

		$field['options'] = array( $opt_key => $opt );
		FrmFieldsHelper::show_single_option( $field );

		wp_die();
    }

	/**
	 * @deprecated 2.3
	 * @codeCoverageIgnore
	 */
    public static function edit_option() {
		_deprecated_function( __FUNCTION__, '2.3' );
    }

	/**
	 * @deprecated 2.3
	 * @codeCoverageIgnore
	 */
    public static function delete_option() {
		_deprecated_function( __FUNCTION__, '2.3' );
    }

    public static function import_choices() {
        FrmAppHelper::permission_check( 'frm_edit_forms', 'hide' );

		$field_id = absint( $_REQUEST['field_id'] );

        global $current_screen, $hook_suffix;

        // Catch plugins that include admin-header.php before admin.php completes.
        if ( empty( $current_screen ) && function_exists( 'set_current_screen' ) ) {
            $hook_suffix = '';
        	set_current_screen();
        }

        if ( function_exists( 'register_admin_color_schemes' ) ) {
            register_admin_color_schemes();
        }

		$hook_suffix = '';
		$admin_body_class = '';

        if ( get_user_setting( 'mfold' ) == 'f' ) {
        	$admin_body_class .= ' folded';
        }

        if ( function_exists( 'is_admin_bar_showing' ) && is_admin_bar_showing() ) {
        	$admin_body_class .= ' admin-bar';
        }

        if ( is_rtl() ) {
        	$admin_body_class .= ' rtl';
        }

        $admin_body_class .= ' admin-color-' . sanitize_html_class( get_user_option( 'admin_color' ), 'fresh' );
        $prepop = array();
		FrmFieldsHelper::get_bulk_prefilled_opts( $prepop );

		$field = FrmField::getOne( $field_id );

        wp_enqueue_script( 'utils' );
		wp_enqueue_style( 'formidable-admin', FrmAppHelper::plugin_url() . '/css/frm_admin.css' );
        FrmAppHelper::load_admin_wide_js();

		include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/import_choices.php' );
        wp_die();
    }

	public static function import_options() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
		check_ajax_referer( 'frm_ajax', 'nonce' );

		if ( ! is_admin() || ! current_user_can( 'frm_edit_forms' ) ) {
			return;
		}

		$field_id = absint( $_POST['field_id'] );
		$field = FrmField::getOne( $field_id );

		if ( ! in_array( $field->type, array( 'radio', 'checkbox', 'select' ) ) ) {
            return;
        }

		$field = FrmFieldsHelper::setup_edit_vars( $field );
		$opts = FrmAppHelper::get_param( 'opts', '', 'post', 'wp_kses_post' );
		$opts = explode( "\n", rtrim( $opts, "\n" ) );
		$opts = array_map( 'trim', $opts );

		if ( $field['separate_value'] ) {
			foreach ( $opts as $opt_key => $opt ) {
				if ( strpos( $opt, '|' ) !== false ) {
					$vals = explode( '|', $opt );
					if ( $vals[0] != $vals[1] ) {
						$opts[ $opt_key ] = array(
							'label' => trim( $vals[0] ),
							'value' => trim( $vals[1] ),
						);
					}
					unset( $vals );
				}
				unset( $opt_key, $opt );
			}
		}

        //Keep other options after bulk update
        if ( isset( $field['field_options']['other'] ) && $field['field_options']['other'] == true ) {
            $other_array = array();
            foreach ( $field['options'] as $opt_key => $opt ) {
				if ( FrmFieldsHelper::is_other_opt( $opt_key ) ) {
					$other_array[ $opt_key ] = $opt;
				}
				unset( $opt_key, $opt );
			}
			if ( ! empty( $other_array ) ) {
				$opts = array_merge( $opts, $other_array );
			}
		}

        $field['options'] = $opts;

		FrmFieldsHelper::show_single_option( $field );

        wp_die();
    }

    public static function update_order() {
		FrmAppHelper::permission_check( 'frm_edit_forms' );
        check_ajax_referer( 'frm_ajax', 'nonce' );

		$fields = FrmAppHelper::get_post_param( 'frm_field_id' );
		foreach ( (array) $fields as $position => $item ) {
			FrmField::update( absint( $item ), array( 'field_order' => absint( $position ) ) );
		}
        wp_die();
    }

	public static function change_type( $type ) {
        $type_switch = array(
            'scale'     => 'radio',
			'star'      => 'radio',
            '10radio'   => 'radio',
            'rte'       => 'textarea',
            'website'   => 'url',
			'image'     => 'url',
        );
        if ( isset( $type_switch[ $type ] ) ) {
            $type = $type_switch[ $type ];
        }

		$pro_fields = FrmField::pro_field_selection();
		$types = array_keys( $pro_fields );
		if ( in_array( $type, $types ) ) {
			$type = 'text';
		}

        return $type;
    }

	/**
	 * @param array $settings
	 * @param object $field_info
	 *
	 * @return array
	 */
	public static function display_field_options( $settings, $field_info = null ) {
		if ( $field_info ) {
			$settings = $field_info->display_field_settings();
			$settings['field_data'] = $field_info->field;
		}

		return apply_filters( 'frm_display_field_options', $settings );
    }

	/**
	 * Display the format option
	 *
	 * @since 3.0
	 * @param array $field
	 */
	public static function show_format_option( $field ) {
		include( FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/value-format.php' );
	}

    public static function input_html( $field, $echo = true ) {
        $class = array(); //$field['type'];
		self::add_input_classes( $field, $class );

		$add_html = array();
		self::add_html_size( $field, $add_html );
		self::add_html_length( $field, $add_html );
		self::add_html_placeholder( $field, $add_html, $class );
		self::add_validation_messages( $field, $add_html );

		$class = apply_filters( 'frm_field_classes', implode( ' ', $class ), $field );

		FrmFormsHelper::add_html_attr( $class, 'class', $add_html );

		self::add_shortcodes_to_html( $field, $add_html );
		self::add_pattern_attribute( $field, $add_html );

		$add_html = apply_filters( 'frm_field_extra_html', $add_html, $field );
		$add_html = ' ' . implode( ' ', $add_html ) . '  ';

        if ( $echo ) {
            echo $add_html; // WPCS: XSS ok.
        }

        return $add_html;
    }

	private static function add_input_classes( $field, array &$class ) {
		if ( isset( $field['input_class'] ) && ! empty( $field['input_class'] ) ) {
			$class[] = $field['input_class'];
		}

        if ( $field['type'] == 'hidden' || $field['type'] == 'user_id' ) {
            return;
        }

		if ( isset( $field['size'] ) && $field['size'] > 0 ) {
			$class[] = 'auto_width';
		}
    }

	private static function add_html_size( $field, array &$add_html ) {
		if ( ! isset( $field['size'] ) || $field['size'] <= 0 || in_array( $field['type'], array( 'select', 'data', 'time', 'hidden', 'file', 'lookup' ) ) ) {
            return;
        }

		if ( FrmAppHelper::is_admin_page( 'formidable' ) ) {
            return;
        }

		if ( is_numeric( $field['size'] ) ) {
			$field['size'] .= 'px';
		}

		$important = apply_filters( 'frm_use_important_width', 1, $field );
        // Note: This inline styling must stay since we cannot realistically set a class for every possible field size
		$add_html['style'] = 'style="width:' . esc_attr( $field['size'] ) . ( $important ? ' !important' : '' ) . '"';

		self::add_html_cols( $field, $add_html );
    }

	private static function add_html_cols( $field, array &$add_html ) {
		if ( ! in_array( $field['type'], array( 'textarea', 'rte' ) ) ) {
            return;
        }

        // convert to cols for textareas
        $calc = array(
            ''      => 9,
            'px'    => 9,
            'rem'   => 0.444,
            'em'    => 0.544,
        );

		// include "col" for valid html
		$unit = trim( preg_replace( '/[0-9]+/', '', $field['size'] ) );

        if ( ! isset( $calc[ $unit ] ) ) {
            return;
        }

        $size = (float) str_replace( $unit, '', $field['size'] ) / $calc[ $unit ];

		$add_html['cols'] = 'cols="' . absint( $size ) . '"';
    }

	private static function add_html_length( $field, array &$add_html ) {
        // check for max setting and if this field accepts maxlength
		if ( FrmField::is_option_empty( $field, 'max' ) || in_array( $field['type'], array( 'textarea', 'rte', 'hidden', 'file' ) ) ) {
            return;
        }

		if ( FrmAppHelper::is_admin_page( 'formidable' ) ) {
            // don't load on form builder page
            return;
        }

		$add_html['maxlength'] = 'maxlength="' . esc_attr( $field['max'] ) . '"';
    }

	private static function add_html_placeholder( $field, array &$add_html, array &$class ) {
		if ( FrmAppHelper::is_admin_page( 'formidable' ) ) {
			return;
		}

		if ( $field['default_value'] != '' && ! FrmField::is_option_true( $field, 'clear_on_focus' ) ) {
			if ( is_array( $field['default_value'] ) ) {
				$field['default_value'] = json_encode( $field['default_value'] );
				$add_html['data-frmval'] = 'data-frmval="' . esc_attr( $field['default_value'] ) . '"';
			} else {
				self::add_frmval_to_input( $field, $add_html );
			}
			$field['default_value'] = '';
		}

		$field['default_value'] = self::prepare_default_value( $field );
		if ( $field['default_value'] == '' || is_array( $field['default_value'] ) ) {
			// don't include a json placeholder
			return;
		}

		$frm_settings = FrmAppHelper::get_settings();

		if ( $frm_settings->use_html ) {
			self::add_placeholder_to_input( $field, $add_html );
		} else {
			self::add_frmval_to_input( $field, $add_html );

			$class[] = 'frm_toggle_default';

			if ( $field['value'] == $field['default_value'] ) {
				$class[] = 'frm_default';
			}
		}
	}

	private static function prepare_default_value( $field ) {
		$is_placeholder_field = FrmFieldsHelper::is_placeholder_field_type( $field['type'] );
		$is_combo_field = in_array( $field['type'], array( 'address', 'credit_card' ) );

		$default_value = $field['default_value'];
		if ( empty( $default_value ) ) {
			if ( $is_placeholder_field && ! $is_combo_field ) {
				$default_value = self::get_default_value_from_name( $field );
			}
		}

		return $default_value;
	}

	/**
	 * If the label position is "inside",
	 * get the label to use as the placeholder
	 *
	 * @since 2.05
	 *
	 * @param array $field
	 *
	 * @return string
	 */
	public static function get_default_value_from_name( $field ) {
		$position = FrmField::get_option( $field, 'label' );
		if ( $position == 'inside' ) {
			$default_value = $field['name'];
		} else {
			$default_value = '';
		}
		return $default_value;
	}

	/**
	 * use HMTL5 placeholder with js fallback
	 *
	 * @param array $field
	 * @param array $add_html
	 */
	private static function add_placeholder_to_input( $field, &$add_html ) {
		if ( FrmFieldsHelper::is_placeholder_field_type( $field['type'] ) ) {
			$add_html['placeholder'] = 'placeholder="' . esc_attr( $field['default_value'] ) . '"';
		}
	}

	private static function add_frmval_to_input( $field, &$add_html ) {
		if ( $field['default_value'] != '' ) {
			$add_html['data-frmval'] = 'data-frmval="' . esc_attr( $field['default_value'] ) . '"';

			if ( 'select' === $field['type'] ) {
				$is_placeholder = FrmField::is_option_true( $field, 'default_blank' );
				if ( $is_placeholder ) {
					$add_html['data-frmplaceholder'] = 'data-frmplaceholder="' . esc_attr( $field['default_value'] ) . '"';
				}
			}
		}
	}

	private static function add_validation_messages( $field, array &$add_html ) {
		if ( FrmField::is_required( $field ) ) {
			$required_message = FrmFieldsHelper::get_error_msg( $field, 'blank' );
			$add_html['data-reqmsg'] = 'data-reqmsg="' . esc_attr( $required_message ) . '"';
		}

		if ( ! FrmField::is_option_empty( $field, 'invalid' ) ) {
			$invalid_message = FrmFieldsHelper::get_error_msg( $field, 'invalid' );
			$add_html['data-invmsg'] = 'data-invmsg="' . esc_attr( $invalid_message ) . '"';
		}
	}

    private static function add_shortcodes_to_html( $field, array &$add_html ) {
        if ( FrmField::is_option_empty( $field, 'shortcodes' ) ) {
            return;
        }

        foreach ( $field['shortcodes'] as $k => $v ) {
            if ( 'opt' === $k ) {
                continue;
            }

			if ( is_numeric( $k ) && strpos( $v, '=' ) ) {
                $add_html[] = $v;
            } else if ( ! empty( $k ) && isset( $add_html[ $k ] ) ) {
				$add_html[ $k ] = str_replace( $k . '="', $k . '="' . $v, $add_html[ $k ] );
            } else {
				$add_html[ $k ] = $k . '="' . esc_attr( $v ) . '"';
            }

			unset( $k, $v );
        }
    }

	/**
	 * Add pattern attribute
	 *
	 * @since 3.0
	 * @param array $field
	 * @param array $add_html
	 */
	private static function add_pattern_attribute( $field, array &$add_html ) {
		$has_format = FrmField::is_option_true_in_array( $field, 'format' );
		$format_field = FrmField::is_field_type( $field, 'text' );

		if ( $field['type'] == 'phone' || ( $has_format && $format_field ) ) {
			$frm_settings = FrmAppHelper::get_settings();

			if ( $frm_settings->use_html ) {
				$format = FrmEntryValidate::phone_format( $field );
				$format = substr( $format, 2, -1 );
				$add_html['pattern'] = 'pattern="' . esc_attr( $format ) . '"';
			}
		}
	}

    public static function check_value( $opt, $opt_key, $field ) {
        if ( is_array( $opt ) ) {
            if ( FrmField::is_option_true( $field, 'separate_value' ) ) {
                $opt = isset( $opt['value'] ) ? $opt['value'] : ( isset( $opt['label'] ) ? $opt['label'] : reset( $opt ) );
            } else {
                $opt = isset( $opt['label'] ) ? $opt['label'] : reset( $opt );
            }
        }
        return $opt;
    }

	public static function check_label( $opt ) {
		if ( is_array( $opt ) ) {
			$opt = ( isset( $opt['label'] ) ? $opt['label'] : reset( $opt ) );
		}

		return $opt;
	}
}
