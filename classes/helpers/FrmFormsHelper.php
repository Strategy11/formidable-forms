<?php
if ( ! defined('ABSPATH') ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmFormsHelper {

	public static function maybe_get_form( &$form ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmForm::maybe_get_form' );
		FrmForm::maybe_get_form( $form );
	}

	/**
	 * @since 2.2.10
	 */
	public static function form_error_class() {
		return apply_filters( 'frm_form_error_class', 'frm_error_style' );
	}

	public static function get_direct_link( $key, $form = false ) {
		$target_url = esc_url( admin_url( 'admin-ajax.php?action=frm_forms_preview&form=' . $key ) );
        $target_url = apply_filters('frm_direct_link', $target_url, $key, $form);

        return $target_url;
    }

    public static function forms_dropdown( $field_name, $field_value = '', $args = array() ) {
        $defaults = array(
            'blank'     => true,
            'field_id'  => false,
            'onchange'  => false,
            'exclude'   => false,
            'class'     => '',
			'inc_children' => 'exclude',
        );
        $args = wp_parse_args( $args, $defaults );

        if ( ! $args['field_id'] ) {
            $args['field_id'] = $field_name;
        }

		$query = array();
        if ( $args['exclude'] ) {
			$query['id !'] = $args['exclude'];
        }

        $where = apply_filters('frm_forms_dropdown', $query, $field_name);
		$forms = FrmForm::get_published_forms( $where, 999, $args['inc_children'] );
		$add_html = array();
		self::add_html_attr( $args['onchange'], 'onchange', $add_html );
		self::add_html_attr( $args['class'], 'class', $add_html );

        ?>
		<select name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $args['field_id'] ) ?>" <?php echo implode( ' ', $add_html ); ?>>
		<?php if ( $args['blank'] ) { ?>
			<option value=""><?php echo ( $args['blank'] == 1 ) ? ' ' : '- ' . esc_attr( $args['blank'] ) . ' -'; ?></option>
		<?php } ?>
		<?php foreach ( $forms as $form ) { ?>
			<option value="<?php echo esc_attr( $form->id ); ?>" <?php selected( $field_value, $form->id ); ?>><?php
				echo ( '' == $form->name ) ? esc_html__( '(no title)', 'formidable' ) : esc_html( FrmAppHelper::truncate( $form->name, 50 ) ) . ( $form->parent_form_id ? esc_html__( ' (child)', 'formidable' ) : '' ) ;
			?></option>
		<?php } ?>
        </select>
        <?php
    }

	/**
	 * @param string $class
	 * @param string $param
	 * @param array $add_html
	 *
	 * @since 2.0.6
	 */
	public static function add_html_attr( $class, $param, &$add_html ) {
		if ( ! empty( $class ) ) {
			$add_html[ $param ] = sanitize_title( $param ) . '="' . esc_attr( trim( sanitize_text_field( $class ) ) ) . '"';
		}
	}

    public static function form_switcher() {
		$where = apply_filters( 'frm_forms_dropdown', array(), '' );
		$forms = FrmForm::get_published_forms( $where );

		$args = array( 'id' => 0, 'form' => 0 );
		if ( isset( $_GET['id'] ) && ! isset( $_GET['form'] ) ) {
			unset( $args['form'] );
		} else if ( isset( $_GET['form']) && ! isset( $_GET['id'] ) ) {
			unset( $args['id'] );
        }

		$frm_action = FrmAppHelper::simple_get( 'frm_action', 'sanitize_title' );
		if ( FrmAppHelper::is_admin_page( 'formidable-entries' ) && in_array( $frm_action, array( 'edit', 'show', 'destroy_all' ) ) ) {
            $args['frm_action'] = 'list';
            $args['form'] = 0;
		} else if ( FrmAppHelper::is_admin_page('formidable' ) && in_array( $frm_action, array( 'new', 'duplicate' ) ) ) {
            $args['frm_action'] = 'edit';
		} else if ( isset( $_GET['post'] ) ) {
            $args['form'] = 0;
            $base = admin_url('edit.php?post_type=frm_display');
        }

        ?>
		<li class="dropdown last" id="frm_bs_dropdown">
			<a href="#" id="frm-navbarDrop" class="frm-dropdown-toggle" data-toggle="dropdown"><?php _e( 'Switch Form', 'formidable' ) ?> <b class="caret"></b></a>
		    <ul class="frm-dropdown-menu frm-on-top" role="menu" aria-labelledby="frm-navbarDrop">
			<?php
			foreach ( $forms as $form ) {
				if ( isset( $args['id'] ) ) {
			        $args['id'] = $form->id;
				}
			    if ( isset( $args['form'] ) ) {
			        $args['form'] = $form->id;
				}
                ?>
				<li><a href="<?php echo esc_url( isset( $base ) ? add_query_arg( $args, $base ) : add_query_arg( $args ) ); ?>" tabindex="-1"><?php echo esc_html( empty( $form->name ) ? __( '(no title)') : FrmAppHelper::truncate( $form->name, 60 ) ); ?></a></li>
			<?php
				unset( $form );
			} ?>
			</ul>
		</li>
        <?php
    }

	public static function get_sortable_classes( $col, $sort_col, $sort_dir ) {
        echo ($sort_col == $col) ? 'sorted' : 'sortable';
        echo ($sort_col == $col && $sort_dir == 'desc') ? ' asc' : ' desc';
    }

	/**
	 * Get the invalid form error message
	 *
	 * @since 2.02.07
	 * @param array $args
	 * @return string
	 */
	public static function get_invalid_error_message( $args ) {
		$frm_settings = FrmAppHelper::get_settings();

		$invalid_msg = apply_filters( 'frm_invalid_error_message', $frm_settings->invalid_msg, $args );

		return $invalid_msg;
	}

	public static function get_success_message( $atts ) {
		$message = apply_filters( 'frm_content', $atts['message'], $atts['form'], $atts['entry_id'] );
		$message = FrmAppHelper::use_wpautop( do_shortcode( $message ) );
		$message = '<div class="' . esc_attr( $atts['class'] ) . '">' . $message . '</div>';
		return $message;
	}

    /**
     * Used when a form is created
     */
    public static function setup_new_vars( $values = array() ) {
        global $wpdb;

        if ( ! empty( $values ) ) {
            $post_values = $values;
        } else {
            $values = array();
            $post_values = isset($_POST) ? $_POST : array();
        }

		foreach ( array( 'name' => '', 'description' => '' ) as $var => $default ) {
			if ( ! isset( $values[ $var ] ) ) {
				$values[ $var ] = FrmAppHelper::get_param( $var, $default );
            }
        }

        $values['description'] = FrmAppHelper::use_wpautop($values['description']);

		foreach ( array( 'form_id' => '', 'logged_in' => '', 'editable' => '', 'default_template' => 0, 'is_template' => 0, 'status' => 'draft', 'parent_form_id' => 0 ) as $var => $default ) {
            if ( ! isset( $values[ $var ] ) ) {
				$values[ $var ] = FrmAppHelper::get_param( $var, $default );
            }
        }

        if ( ! isset( $values['form_key'] ) ) {
			$values['form_key'] = ( $post_values && isset( $post_values['form_key'] ) ) ? $post_values['form_key'] : FrmAppHelper::get_unique_key( '', $wpdb->prefix . 'frm_forms', 'form_key' );
        }

		$values = self::fill_default_opts( $values, false, $post_values );
		$values['custom_style'] = FrmAppHelper::custom_style_value( $post_values );

        return apply_filters('frm_setup_new_form_vars', $values);
    }

    /**
     * Used when editing a form
     */
    public static function setup_edit_vars( $values, $record, $post_values = array() ) {
		if ( empty( $post_values ) ) {
			$post_values = stripslashes_deep( $_POST );
		}

        $values['form_key'] = isset($post_values['form_key']) ? $post_values['form_key'] : $record->form_key;
        $values['default_template'] = isset($post_values['default_template']) ? $post_values['default_template'] : $record->default_template;
        $values['is_template'] = isset($post_values['is_template']) ? $post_values['is_template'] : $record->is_template;
        $values['status'] = $record->status;

        $values = self::fill_default_opts($values, $record, $post_values);

        return apply_filters('frm_setup_edit_form_vars', $values);
    }

	public static function fill_default_opts( $values, $record, $post_values ) {

        $defaults = self::get_default_opts();
		foreach ( $defaults as $var => $default ) {
            if ( is_array($default) ) {
                if ( ! isset( $values[ $var ] ) ) {
					$values[ $var ] = ( $record && isset( $record->options[ $var ] ) ) ? $record->options[ $var ] : array();
                }

                foreach ( $default as $k => $v ) {
					$values[ $var ][ $k ] = ( $post_values && isset( $post_values[ $var ][ $k ] ) ) ? $post_values[ $var ][ $k ] : ( ( $record && isset( $record->options[ $var ] ) && isset( $record->options[ $var ][ $k ] ) ) ? $record->options[ $var ][ $k ] : $v);

                    if ( is_array( $v ) ) {
                        foreach ( $v as $k1 => $v1 ) {
							$values[ $var ][ $k ][ $k1 ] = ( $post_values && isset( $post_values[ $var ][ $k ][ $k1 ] ) ) ? $post_values[ $var ][ $k ][ $k1 ] : ( ( $record && isset( $record->options[ $var ] ) && isset( $record->options[ $var ][ $k ] ) && isset( $record->options[ $var ][ $k ][ $k1 ] ) ) ? $record->options[ $var ][ $k ][ $k1 ] : $v1 );
                            unset( $k1, $v1 );
                        }
                    }

                    unset($k, $v);
                }
            } else {
				$values[ $var ] = ( $post_values && isset( $post_values['options'][ $var ] ) ) ? $post_values['options'][ $var ] : ( ( $record && isset( $record->options[ $var ] ) ) ? $record->options[ $var ] : $default );
            }

            unset($var, $default);
        }

        return $values;
    }

    public static function get_default_opts() {
        $frm_settings = FrmAppHelper::get_settings();

        return array(
            'submit_value' => $frm_settings->submit_value, 'success_action' => 'message',
            'success_msg' => $frm_settings->success_msg, 'show_form' => 0, 'akismet' => '',
            'no_save' => 0, 'ajax_load' => 0, 'form_class' => '', 'custom_style' => 1,
            'before_html' => self::get_default_html('before'),
            'after_html' => '',
            'submit_html' => self::get_default_html('submit'),
        );
    }

	/**
	 * @param array $options
	 * @param array $values
	 * @since 2.0.6
	 */
	public static function fill_form_options( &$options, $values ) {
		$defaults = self::get_default_opts();
		foreach ( $defaults as $var => $default ) {
			$options[ $var ] = isset( $values['options'][ $var ] ) ? $values['options'][ $var ] : $default;
			unset( $var, $default );
		}
	}

    /**
     * @param string $loc
     */
	public static function get_default_html( $loc ) {
		if ( $loc == 'submit' ) {
            $draft_link = self::get_draft_link();
            $default_html = <<<SUBMIT_HTML
<div class="frm_submit">
[if back_button]<button type="submit" name="frm_prev_page" formnovalidate="formnovalidate" class="frm_prev_page" [back_hook]>[back_label]</button>[/if back_button]
<button class="frm_button_submit" type="submit"  [button_action]>[button_label]</button>
$draft_link
</div>
SUBMIT_HTML;
		} else if ( $loc == 'before' ) {
            $default_html = <<<BEFORE_HTML
<legend class="frm_hidden">[form_name]</legend>
[if form_name]<h3 class="frm_form_title">[form_name]</h3>[/if form_name]
[if form_description]<div class="frm_description">[form_description]</div>[/if form_description]
BEFORE_HTML;
		} else {
            $default_html = '';
        }

        return $default_html;
    }

    public static function get_draft_link() {
        $link = '[if save_draft]<a href="#" class="frm_save_draft" [draft_hook]>[draft_label]</a>[/if save_draft]';
        return $link;
    }

	public static function get_custom_submit( $html, $form, $submit, $form_action, $values ) {
		$button = self::replace_shortcodes( $html, $form, $submit, $form_action, $values );
		if ( ! strpos( $button, '[button_action]' ) ) {
			echo $button;
			return;
		}

		$button_parts = explode( '[button_action]', $button );

		$classes = apply_filters( 'frm_submit_button_class', array(), $form );
		if ( ! empty( $classes ) ) {
			$classes = implode( ' ', $classes );
			$button_class = ' class="frm_button_submit';
			if ( strpos( $button_parts[0], $button_class ) !== false ) {
				$button_parts[0] = str_replace( $button_class, $button_class . ' ' . esc_attr( $classes ), $button_parts[0] );
			} else {
				$button_parts[0] .= ' class="' . esc_attr( $classes ) . '"';
			}
		}

		echo $button_parts[0];
		do_action( 'frm_submit_button_action', $form, $form_action );
		echo $button_parts[1];
	}

    /**
     * Automatically add end section fields if they don't exist (2.0 migration)
     * @since 2.0
     *
     * @param boolean $reset_fields
     */
    public static function auto_add_end_section_fields( $form, $fields, &$reset_fields ) {
		if ( empty( $fields ) ) {
			return;
		}

		$end_section_values = apply_filters( 'frm_before_field_created', FrmFieldsHelper::setup_new_vars( 'end_divider', $form->id ) );
		$open = false;
		$prev_order = false;
		$add_order = 0;
		$last_field = false;
        foreach ( $fields as $field ) {
			if ( $prev_order === $field->field_order ) {
				$add_order++;
			}

			if ( $add_order ) {
				$reset_fields = true;
				$field->field_order = $field->field_order + $add_order;
				FrmField::update( $field->id, array( 'field_order' => $field->field_order ) );
			}

            switch ( $field->type ) {
                case 'divider':
                    // create an end section if open
					self::maybe_create_end_section( $open, $reset_fields, $add_order, $end_section_values, $field, 'move' );

                    // mark it open for the next end section
                    $open = true;
                break;
                case 'break';
					self::maybe_create_end_section( $open, $reset_fields, $add_order, $end_section_values, $field, 'move' );
                break;
                case 'end_divider':
                    if ( ! $open ) {
                        // the section isn't open, so this is an extra field that needs to be removed
                        FrmField::destroy( $field->id );
                        $reset_fields = true;
                    }

                    // There is already an end section here, so there is no need to create one
                    $open = false;
            }
			$prev_order = $field->field_order;

			$last_field = $field;
			unset( $field );
        }

		self::maybe_create_end_section( $open, $reset_fields, $add_order, $end_section_values, $last_field );
    }

	/**
	 * Create end section field if it doesn't exist. This is for migration from < 2.0
	 * Fix any ordering that may be messed up
	 */
	public static function maybe_create_end_section( &$open, &$reset_fields, &$add_order, $end_section_values, $field, $move = 'no' ) {
        if ( ! $open ) {
            return;
        }

		$end_section_values['field_order'] = $field->field_order + 1;

        FrmField::create( $end_section_values );

		if ( $move == 'move' ) {
			// bump the order of current field unless we're at the end of the form
			FrmField::update( $field->id, array( 'field_order' => $field->field_order + 2 ) );
		}

		$add_order += 2;
        $open = false;
        $reset_fields = true;
    }

    public static function replace_shortcodes( $html, $form, $title = false, $description = false, $values = array() ) {
		foreach ( array( 'form_name' => $title, 'form_description' => $description, 'entry_key' => true ) as $code => $show ) {
            if ( $code == 'form_name' ) {
                $replace_with = $form->name;
            } else if ( $code == 'form_description' ) {
                $replace_with = FrmAppHelper::use_wpautop($form->description);
            } else if ( $code == 'entry_key' && isset($_GET) && isset($_GET['entry']) ) {
                $replace_with = FrmAppHelper::simple_get( 'entry' );
            } else {
                $replace_with = '';
            }

            FrmFieldsHelper::remove_inline_conditions( ( FrmAppHelper::is_true($show) && $replace_with != '' ), $code, $replace_with, $html );
        }

        //replace [form_key]
        $html = str_replace('[form_key]', $form->form_key, $html);

        //replace [frmurl]
        $html = str_replace('[frmurl]', FrmFieldsHelper::dynamic_default_values( 'frmurl' ), $html);

		if ( strpos( $html, '[button_label]' ) ) {
			add_filter( 'frm_submit_button', 'FrmFormsHelper::submit_button_label', 1 );
			$submit_label = apply_filters( 'frm_submit_button', $title, $form );
			$submit_label = esc_attr( do_shortcode( $submit_label ) );
			$html = str_replace( '[button_label]', $submit_label, $html );
        }

        $html = apply_filters('frm_form_replace_shortcodes', $html, $form, $values);

		if ( strpos( $html, '[if back_button]' ) ) {
			$html = preg_replace( '/(\[if\s+back_button\])(.*?)(\[\/if\s+back_button\])/mis', '', $html );
		}

		if ( strpos( $html, '[if save_draft]' ) ) {
			$html = preg_replace( '/(\[if\s+save_draft\])(.*?)(\[\/if\s+save_draft\])/mis', '', $html );
		}

		if ( apply_filters( 'frm_do_html_shortcodes', true ) ) {
			$html = do_shortcode( $html );
		}

        return $html;
    }

	public static function submit_button_label( $submit ) {
        if ( ! $submit || empty($submit) ) {
            $frm_settings = FrmAppHelper::get_settings();
            $submit = $frm_settings->submit_value;
        }

        return $submit;
    }

	/**
	 * If the Formidable styling isn't being loaded,
	 * use inline styling to hide the element
	 * @since 2.03.05
	 */
	public static function maybe_hide_inline() {
		$frm_settings = FrmAppHelper::get_settings();
		if ( $frm_settings->load_style == 'none' ) {
			echo ' style="display:none;"';
		} elseif ( $frm_settings->load_style == 'dynamic' ) {
			FrmStylesController::enqueue_style();
		}
	}

	public static function get_form_style_class( $form = false ) {
        $style = self::get_form_style($form);
        $class = ' with_frm_style';

        if ( empty($style) ) {
            if ( FrmAppHelper::is_admin_page('formidable-entries') ) {
                return $class;
            } else {
                return;
            }
        }

        //If submit button needs to be inline or centered
        if ( is_object($form) ) {
			$form = $form->options;
		}

		$submit_align = isset( $form['submit_align'] ) ? $form['submit_align'] : '';

		if ( $submit_align == 'inline' ) {
			$class .= ' frm_inline_form';
		} else if ( $submit_align == 'center' ) {
			$class .= ' frm_center_submit';
		}

        $class = apply_filters('frm_add_form_style_class', $class, $style);

        return $class;
    }

    /**
     * @param string|boolean $form
     *
     * @return string
     */
    public static function get_form_style( $form ) {
		$style = 1;
		if ( empty( $form ) || 'default' == 'form' ) {
			return $style;
		} else if ( is_object( $form ) && $form->parent_form_id ) {
			// get the parent form if this is a child
			$form = $form->parent_form_id;
		} else if ( is_array( $form ) && isset( $form['parent_form_id'] ) && $form['parent_form_id'] ) {
			$form = $form['parent_form_id'];
		} else if ( is_array( $form ) && isset( $form['custom_style'] ) ) {
			$style = $form['custom_style'];
		}

		if ( $form && is_string( $form ) ) {
			$form = FrmForm::getOne( $form );
		}

		$style = ( $form && is_object( $form ) && isset( $form->options['custom_style'] ) ) ? $form->options['custom_style'] : $style;

		return $style;
    }

	/**
	 * Display the validation error messages when an entry is submitted
	 *
	 * @param array $args - includes img, errors
	 * @since 2.0.6
	 */
	public static function show_errors( $args ) {
		$invalid_msg = self::get_invalid_error_message( $args );

		if ( empty( $invalid_msg ) ) {
			$show_img = false;
		} else {
			echo wp_kses_post( $invalid_msg );
			$show_img = true;
		}

		self::show_error( array( 'img' => $args['img'], 'errors' => $args['errors'], 'show_img' => $show_img ) );
	}

	/**
	 * Display the error message in the front-end along with the image if set
	 * The image was removed from the styling settings, but it may still be set with a hook
	 * If the message in the global settings is empty, show every validation message in the error box
	 *
	 * @param array $args - includes img, errors, and show_img
	 * @since 2.0.6
	 */
	public static function show_error( $args ) {
		$line_break_first = $args['show_img'];
		foreach ( $args['errors'] as $error_key => $error ) {
			if ( $line_break_first && ! is_numeric( $error_key ) && ( $error_key == 'cptch_number' || strpos( $error_key, 'field' ) === 0 ) ) {
				continue;
			}

			if ( $line_break_first ) {
				echo '<br/>';
			}

			if ( $args['show_img'] && ! empty( $args['img'] ) ) {
				?><img src="<?php echo esc_attr( $args['img'] ) ?>" alt="" /><?php
			} else {
				$args['show_img'] = true;
			}

			echo wp_kses_post( $error );

			if ( ! $line_break_first ) {
				echo '<br/>';
			}
		}
	}

	public static function maybe_get_scroll_js( $id ) {
		$offset = apply_filters( 'frm_scroll_offset', 4, array( 'form_id' => $id ) );
		if ( $offset != -1 ) {
			self::get_scroll_js( $id );
		}
	}

	public static function get_scroll_js( $form_id ) {
        ?><script type="text/javascript">document.addEventListener('DOMContentLoaded',function(){frmFrontForm.scrollMsg(<?php echo (int) $form_id ?>);})</script><?php
    }

	public static function edit_form_link( $form_id ) {
        if ( is_object($form_id) ) {
            $form = $form_id;
            $name = $form->name;
            $form_id = $form->id;
        } else {
            $name = FrmForm::getName($form_id);
        }

        if ( $form_id ) {
			$val = '<a href="' . esc_url( admin_url( 'admin.php?page=formidable&frm_action=edit&id=' . $form_id ) ) . '">' . ( '' == $name ? __( '(no title)' ) : FrmAppHelper::truncate( $name, 40 ) ) . '</a>';
	    } else {
	        $val = '';
	    }

	    return $val;
	}

	public static function delete_trash_link( $id, $status, $length = 'long' ) {
        $link = '';
        $labels = array(
            'restore' => array(
                'long'  => __( 'Restore from Trash', 'formidable' ),
                'short' => __( 'Restore', 'formidable' ),
            ),
            'trash' => array(
                'long'  => __( 'Move to Trash', 'formidable' ),
                'short' => __( 'Trash', 'formidable' ),
            ),
            'delete' => array(
                'long'  => __( 'Delete Permanently', 'formidable' ),
                'short' => __( 'Delete', 'formidable' ),
            ),
        );

        $current_page = isset( $_REQUEST['form_type'] ) ? $_REQUEST['form_type'] : '';
		$base_url = '?page=formidable&form_type=' . $current_page . '&id=' . $id;
        if ( 'trash' == $status ) {
			$link = '<a href="' . esc_url( wp_nonce_url( $base_url . '&frm_action=untrash', 'untrash_form_' . $id ) ) . '" class="submitdelete deletion">' . $labels['restore'][ $length ] . '</a>';
        } else if ( current_user_can('frm_delete_forms') ) {
            if ( EMPTY_TRASH_DAYS ) {
				$link = '<a href="' . esc_url( wp_nonce_url( $base_url . '&frm_action=trash', 'trash_form_' . $id ) ) . '" class="submitdelete deletion">' . $labels['trash'][ $length ] . '</a>';
            } else {
				$link = '<a href="' . esc_url( wp_nonce_url( $base_url . '&frm_action=destroy', 'destroy_form_' . $id ) ) . '" class="submitdelete deletion" onclick="return confirm(\'' . esc_attr( __( 'Are you sure you want to delete this form and all its entries?', 'formidable' ) ) . '\')">' . $labels['delete'][ $length ] . '</a>';
            }
        }

        return $link;
    }

	public static function status_nice_name( $status ) {
        $nice_names = array(
            'draft'     => __( 'Draft', 'formidable' ),
            'trash'     => __( 'Trash', 'formidable' ),
            'publish'   => __( 'Published', 'formidable' ),
        );

        if ( ! in_array($status, array_keys($nice_names)) ) {
            $status = 'publish';
        }

		$name = $nice_names[ $status ];

        return $name;
    }

	public static function get_params() {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmForm::list_page_params' );
		return FrmForm::list_page_params();
	}

	public static function form_loaded( $form, $this_load, $global_load ) {
		_deprecated_function( __FUNCTION__, '2.0.9', 'FrmFormsController::maybe_load_css' );
		FrmFormsController::maybe_load_css( $form, $this_load, $global_load );
	}
}
