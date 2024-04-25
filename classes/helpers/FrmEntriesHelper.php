<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmEntriesHelper {

	/**
	 * "Submitted" entry status.
	 *
	 * @since 6.4.2
	 * @var int
	 */
	const SUBMITTED_ENTRY_STATUS = 0;

	/**
	 * "Draft" entry status.
	 *
	 * @since 6.4.2
	 * @var int
	 */
	const DRAFT_ENTRY_STATUS = 1;

	public static function setup_new_vars( $fields, $form = '', $reset = false, $args = array() ) {
		remove_action( 'media_buttons', 'FrmFormsController::insert_form_button' );

		$values = array(
			'name'        => '',
			'description' => '',
			'item_key'    => '',
		);

		$values['fields'] = array();
		if ( empty( $fields ) ) {
			return apply_filters( 'frm_setup_new_entry', $values );
		}

		foreach ( (array) $fields as $field ) {
			$original_default = $field->default_value;
			self::prepare_field_default_value( $field );
			$new_value = self::get_field_value_for_new_entry( $field, $reset, $args );

			$field_array                     = FrmAppHelper::start_field_array( $field );
			$field_array['value']            = $new_value;
			$field_array['type']             = apply_filters( 'frm_field_type', $field->type, $field, $new_value );
			$field_array['parent_form_id']   = isset( $args['parent_form_id'] ) ? $args['parent_form_id'] : $field->form_id;
			$field_array['reset_value']      = $reset;
			$field_array['in_embed_form']    = isset( $args['in_embed_form'] ) ? $args['in_embed_form'] : '0';
			$field_array['original_default'] = $original_default;

			FrmFieldsHelper::prepare_new_front_field( $field_array, $field, $args );

			if ( ! is_array( $field->field_options ) ) {
				$field->field_options = array();
			}

			$field_array = array_merge( $field->field_options, $field_array );

			$values['fields'][] = $field_array;

			if ( ! $form || ! isset( $form->id ) ) {
				$form = FrmForm::getOne( $field->form_id );
			}
		}//end foreach

		FrmAppHelper::unserialize_or_decode( $form->options );
		if ( is_array( $form->options ) ) {
			$values = array_merge( $values, $form->options );
		}

		$form_defaults                 = FrmFormsHelper::get_default_opts();
		$form_defaults['custom_style'] = FrmAppHelper::custom_style_value( array() );

		$values = array_merge( $form_defaults, $values );

		return apply_filters( 'frm_setup_new_entry', $values );
	}

	/**
	 * @since 2.05
	 *
	 * @param object $field
	 */
	private static function prepare_field_default_value( &$field ) {
		// If checkbox, multi-select dropdown, or checkbox data from entries field, the value should be an array.
		$return_array = FrmField::is_field_with_multiple_values( $field );

		/**
		 * Do any shortcodes in default value and allow customization of default value.
		 * Calls FrmProFieldsHelper::get_default_value
		 */
		$field->default_value = apply_filters( 'frm_get_default_value', $field->default_value, $field, true, $return_array );

		if ( isset( $field->field_options['placeholder'] ) ) {
			$field->field_options['placeholder'] = apply_filters( 'frm_get_default_value', $field->field_options['placeholder'], $field, false, false );
		}
	}

	/**
	 * Set the value for each field
	 * This function is used when the form is first loaded and on all page turns *for a new entry*
	 *
	 * @since 2.0.13
	 *
	 * @param object $field - this is passed by reference since it is an object.
	 * @param bool   $reset
	 * @param array  $args
	 *
	 * @return array|string $new_value
	 */
	private static function get_field_value_for_new_entry( $field, $reset, $args ) {
		$new_value = $field->default_value;

		if ( ! $reset && self::value_is_posted( $field, $args ) ) {
			self::get_posted_value( $field, $new_value, $args );
		}

		if ( ! is_array( $new_value ) ) {
			$new_value = str_replace( '"', '&quot;', $new_value );
		}

		return $new_value;
	}

	/**
	 * Check if a field has a posted value
	 *
	 * @since 2.01.0
	 *
	 * @param object $field
	 * @param array  $args
	 *
	 * @return bool $value_is_posted
	 */
	public static function value_is_posted( $field, $args ) {
		$value_is_posted = false;
		if ( $_POST ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$repeating = isset( $args['repeating'] ) && $args['repeating'];
			if ( $repeating ) {
				if ( isset( $_POST['item_meta'][ $args['parent_field_id'] ][ $args['key_pointer'] ][ $field->id ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$value_is_posted = true;
				}
			} elseif ( isset( $_POST['item_meta'][ $field->id ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$value_is_posted = true;
			}
		}

		return $value_is_posted;
	}

	public static function setup_edit_vars( $values, $record ) {
		remove_action( 'media_buttons', 'FrmFormsController::insert_form_button' );

		$values['item_key'] = FrmAppHelper::get_post_param( 'item_key', $record->item_key, 'sanitize_title' );
		$values['form_id']  = $record->form_id;
		$values['is_draft'] = $record->is_draft;

		return apply_filters( 'frm_setup_edit_entry_vars', $values, $record );
	}

	public static function replace_default_message( $message, $atts ) {
		if ( strpos( $message, '[default-message' ) === false &&
			strpos( $message, '[default_message' ) === false &&
			! empty( $message ) ) {
			return $message;
		}

		if ( empty( $message ) ) {
			$message = '[default-message]';
		}

		preg_match_all( "/\[(default-message|default_message)\b(.*?)(?:(\/))?\]/s", $message, $shortcodes, PREG_PATTERN_ORDER );

		foreach ( $shortcodes[0] as $short_key => $tag ) {
			$add_atts = FrmShortcodeHelper::get_shortcode_attribute_array( $shortcodes[2][ $short_key ] );
			if ( ! empty( $add_atts ) ) {
				$this_atts = array_merge( $atts, $add_atts );
			} else {
				$this_atts = $atts;
			}

			$default = FrmEntriesController::show_entry_shortcode( $this_atts );

			// Add the default message.
			$message = str_replace( $shortcodes[0][ $short_key ], $default, $message );
		}

		return $message;
	}

	/**
	 * @param stdClass $entry
	 * @param stdClass $field
	 * @param array    $atts
	 * @return string
	 */
	public static function prepare_display_value( $entry, $field, $atts ) {
		$field_value = isset( $entry->metas[ $field->id ] ) ? $entry->metas[ $field->id ] : false;

		if ( FrmAppHelper::pro_is_installed() ) {
			$empty = empty( $field_value );
			FrmProEntriesHelper::get_dynamic_list_values( $field, $entry, $field_value );
			if ( $empty && ! empty( $field_value ) ) {
				// We've got an entry id, so switch it to a value.
				$atts['force_id'] = true;
			}
		}

		if ( $field->form_id == $entry->form_id || empty( $atts['embedded_field_id'] ) ) {
			return self::display_value( $field_value, $field, $atts );
		}

		if ( ! FrmAppHelper::pro_is_installed() ) {
			return '';
		}

		// This is an embeded form.
		if ( strpos( $atts['embedded_field_id'], 'form' ) === 0 ) {
			// This is a repeating section.
			$child_entries = FrmEntry::getAll( array( 'it.parent_item_id' => $entry->id ), '', '', true );
		} else {
			// Get all values for this field.
			$child_values = isset( $entry->metas[ $atts['embedded_field_id'] ] ) ? $entry->metas[ $atts['embedded_field_id'] ] : false;

			if ( $child_values ) {
				$child_entries = FrmEntry::getAll( array( 'it.id' => (array) $child_values ) );
			}
		}

		$field_value = array();

		if ( empty( $child_entries ) ) {
			return '';
		}

		foreach ( $child_entries as $child_entry ) {
			$atts['item_id'] = $child_entry->id;
			$atts['post_id'] = $child_entry->post_id;

			// Fet the value for this field -- check for post values as well.
			$entry_val = FrmProEntryMetaHelper::get_post_or_meta_value( $child_entry, $field );

			if ( $entry_val || '0' === $entry_val ) {
				// foreach entry get display_value.
				$field_value[] = self::display_value( $entry_val, $field, $atts );
			}

			unset( $child_entry );
		}

		$sep = ', ';
		if ( strpos( implode( ' ', $field_value ), '<img' ) !== false ) {
			$sep = '<br/>';
		}
		$val = implode( $sep, $field_value );

		return FrmAppHelper::kses( $val, 'all' );
	}

	/**
	 * Prepare the saved value for display
	 *
	 * @param array|string $value
	 * @param object       $field
	 * @param array        $atts
	 *
	 * @return string
	 */
	public static function display_value( $value, $field, $atts = array() ) {

		$defaults = array(
			'type'          => '',
			'html'          => false,
			'show_filename' => true,
			'truncate'      => false,
			'sep'           => ', ',
			'post_id'       => 0,
			'form_id'       => $field->form_id,
			'field'         => $field,
			'keepjs'        => 0,
			'return_array'  => false,
		);

		$atts = wp_parse_args( $atts, $defaults );

		if ( FrmField::is_image( $field ) || $field->type === 'star' ) {
			$atts['truncate'] = false;
			$atts['html']     = true;
		}

		$atts = apply_filters( 'frm_display_value_atts', $atts, $field, $value );

		if ( ! isset( $field->field_options['post_field'] ) ) {
			$field->field_options['post_field'] = '';
		}

		if ( ! isset( $field->field_options['custom_field'] ) ) {
			$field->field_options['custom_field'] = '';
		}

		if ( FrmAppHelper::pro_is_installed() && $atts['post_id'] && ( $field->field_options['post_field'] || $atts['type'] === 'tag' ) ) {
			$atts['pre_truncate'] = $atts['truncate'];
			$atts['truncate']     = true;
			$atts['exclude_cat']  = isset( $field->field_options['exclude_cat'] ) ? $field->field_options['exclude_cat'] : 0;

			$value            = FrmProEntryMetaHelper::get_post_value( $atts['post_id'], $field->field_options['post_field'], $field->field_options['custom_field'], $atts );
			$atts['truncate'] = $atts['pre_truncate'];
		}

		if ( $value == '' ) {
			return $value;
		}

		$unfiltered_value = $value;
		FrmFieldsHelper::prepare_field_value( $unfiltered_value, $field->type );

		$value = apply_filters( 'frm_display_value_custom', $unfiltered_value, $field, $atts );
		$value = apply_filters( 'frm_display_' . $field->type . '_value_custom', $value, compact( 'field', 'atts' ) );

		if ( $value == $unfiltered_value ) {
			$value = FrmFieldsHelper::get_unfiltered_display_value( compact( 'value', 'field', 'atts' ) );
		}

		if ( $atts['truncate'] && $atts['type'] !== 'url' ) {
			$value = FrmAppHelper::truncate( $value, 50 );
		}

		if ( ! $atts['keepjs'] && ! is_array( $value ) ) {
			$value = FrmAppHelper::kses( $value, 'all' );
		}

		return apply_filters( 'frm_display_value', $value, $field, $atts );
	}

	public static function set_posted_value( $field, $value, $args ) {
		// If validating a field with "other" opt, set back to prev value now.
		if ( isset( $args['other'] ) && $args['other'] ) {
			$value = $args['temp_value'];
		}
		if ( empty( $args['parent_field_id'] ) ) {
			$_POST['item_meta'][ $field->id ] = $value;
		} else {
			self::set_parent_field_posted_value( $field, $value, $args );
		}
	}

	/**
	 * Init arrays if necessary, else we get fatal error.
	 *
	 * @since 4.01
	 */
	private static function set_parent_field_posted_value( $field, $value, $args ) {
		if ( isset( $_POST['item_meta'][ $args['parent_field_id'] ] ) && is_array( $_POST['item_meta'][ $args['parent_field_id'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			if ( ! isset( $_POST['item_meta'][ $args['parent_field_id'] ][ $args['key_pointer'] ] ) || ! is_array( $_POST['item_meta'][ $args['parent_field_id'] ][ $args['key_pointer'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$_POST['item_meta'][ $args['parent_field_id'] ][ $args['key_pointer'] ] = array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			}
		} else {
			// All of the section was probably removed.
			$_POST['item_meta'][ $args['parent_field_id'] ]                         = array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$_POST['item_meta'][ $args['parent_field_id'] ][ $args['key_pointer'] ] = array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		$_POST['item_meta'][ $args['parent_field_id'] ][ $args['key_pointer'] ][ $field->id ] = $value; // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	public static function get_posted_value( $field, &$value, $args ) {
		if ( is_array( $field ) ) {
			$field_id  = $field['id'];
			$field_obj = FrmFieldFactory::get_field_object( $field['id'] );
		} elseif ( is_object( $field ) ) {
			$field_id  = $field->id;
			$field_obj = FrmFieldFactory::get_field_object( $field );
		} elseif ( is_numeric( $field ) ) {
			$field_id  = $field;
			$field_obj = FrmFieldFactory::get_field_object( $field );
		} else {
			$value = self::get_posted_meta( $field, $args );
			FrmAppHelper::sanitize_value( 'sanitize_text_field', $value );
			return;
		}

		$value = self::get_posted_meta( $field_id, $args );

		$field_obj->sanitize_value( $value );
	}

	/**
	 * @since 4.02.04
	 */
	private static function get_posted_meta( $field_id, $args ) {
		if ( empty( $args['parent_field_id'] ) ) {
			// Sanitizing is done next.
			$value = isset( $_POST['item_meta'][ $field_id ] ) ? wp_unslash( $_POST['item_meta'][ $field_id ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
		} else {
			$value = isset( $_POST['item_meta'][ $args['parent_field_id'] ][ $args['key_pointer'] ][ $field_id ] ) ? wp_unslash( $_POST['item_meta'][ $args['parent_field_id'] ][ $args['key_pointer'] ][ $field_id ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
		}
		return $value;
	}

	/**
	 * Check if field has an "Other" option and if any other values are posted
	 *
	 * @since 2.0
	 *
	 * @param object       $field
	 * @param array|string $value
	 * @param array        $args
	 */
	public static function maybe_set_other_validation( $field, &$value, &$args ) {
		$args['other'] = false;
		if ( ! $value || ! FrmAppHelper::pro_is_installed() ) {
			return;
		}

		// Trim excess values if selection limit is exceeded for checkbox. Necessary to do here
		// as the value set here will be used later in this class's set_posted_value() method.
		if ( FrmField::is_checkbox( $field ) ) {
			$field_obj = FrmFieldFactory::get_field_object( $field );
			$field_obj->maybe_trim_excess_values( $value );
		}

		// Get other value for fields in repeating section.
		self::set_other_repeating_vals( $field, $value, $args );

		// Check if there are any posted "Other" values.
		if ( FrmField::is_option_true( $field, 'other' ) && isset( $_POST['item_meta']['other'][ $field->id ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

			// Save original value.
			$args['temp_value'] = $value;
			$args['other']      = true;

			// Sanitizing is done next.
			$other_vals = wp_unslash( $_POST['item_meta']['other'][ $field->id ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
			FrmAppHelper::sanitize_value( 'sanitize_text_field', $other_vals );

			// Set the validation value now
			self::set_other_validation_val( $value, $other_vals, $field, $args );
		}
	}

	/**
	 * Sets radio or checkbox value equal to "other" value if it is set - FOR REPEATING SECTIONS
	 *
	 * @since 2.0
	 *
	 * @param object       $field
	 * @param array|string $value
	 * @param array        $args
	 */
	public static function set_other_repeating_vals( $field, &$value, &$args ) {
		if ( ! $args['parent_field_id'] ) {
			return;
		}

		// Check if there are any other posted "other" values for this field.
		if ( FrmField::is_option_true( $field, 'other' ) && isset( $_POST['item_meta'][ $args['parent_field_id'] ][ $args['key_pointer'] ]['other'][ $field->id ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Save original value
			$args['temp_value'] = $value;
			$args['other']      = true;

			$other_vals = wp_unslash( $_POST['item_meta'][ $args['parent_field_id'] ][ $args['key_pointer'] ]['other'][ $field->id ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing
			FrmAppHelper::sanitize_value( 'sanitize_text_field', $other_vals );

			// Set the validation value now.
			self::set_other_validation_val( $value, $other_vals, $field, $args );
		}
	}

	/**
	 * Modify value used for validation
	 * This function essentially removes the "Other" radio or checkbox value from the $value being validated.
	 * It also adds any text from the free text fields to the value
	 *
	 * Needs to accommodate for times when other opt is selected, but no other free text is entered
	 *
	 * @since 2.0
	 *
	 * @param array|string $value
	 * @param array|string $other_vals (usually of posted values).
	 * @param object       $field
	 * @param array        $args
	 */
	public static function set_other_validation_val( &$value, $other_vals, $field, &$args ) {
		// Checkboxes and multi-select dropdowns.
		if ( is_array( $value ) && $field->type === 'checkbox' ) {
			// Combine "Other" values with checked values. "Other" values will override checked box values.
			foreach ( $other_vals as $k => $v ) {
				if ( isset( $value[ $k ] ) && trim( $v ) === '' ) {
					// If the other box is checked, but doesn't have a value.
					$value = '';
					break;
				}
			}

			if ( is_array( $value ) && ! empty( $value ) ) {
				$value = array_merge( $value, $other_vals );
			}
		} else {
			// Radio and dropdowns.
			$other_key = array_filter( array_keys( $field->options ), 'is_string' );
			$other_key = reset( $other_key );

			// Multi-select dropdown.
			if ( is_array( $value ) ) {
				$o_key = array_search( $field->options[ $other_key ], $value );

				if ( $o_key !== false ) {
					// Modify the original value so other key will be preserved.
					$value[ $other_key ] = $value[ $o_key ];

					// By default, the array keys will be numeric for multi-select dropdowns.
					// If going backwards and forwards between pages, the array key will match the other key.
					if ( $o_key !== $other_key ) {
						unset( $value[ $o_key ] );
					}

					$args['temp_value']  = $value;
					$value[ $other_key ] = reset( $other_vals );
					if ( FrmAppHelper::is_empty_value( $value[ $other_key ] ) ) {
						unset( $value[ $other_key ] );
					}
				}
			} elseif ( $field->options[ $other_key ] == $value ) {
				$value = $other_vals;
			}//end if
		}//end if
	}

	/**
	 * Add submitted values to a string for spam checking.
	 *
	 * @param array $values
	 * @return string
	 */
	public static function entry_array_to_string( $values ) {
		$content = '';
		foreach ( $values['item_meta'] as $val ) {
			if ( $content != '' ) {
				$content .= "\n\n";
			}

			if ( is_array( $val ) ) {
				$val = FrmAppHelper::array_flatten( $val );
				$val = implode( ', ', $val );
			}

			$content .= $val;
		}

		return $content;
	}

	/**
	 * Get the browser from the user agent
	 *
	 * @since 2.04
	 *
	 * @param string $u_agent
	 *
	 * @return string
	 */
	public static function get_browser( $u_agent ) {
		$bname    = __( 'Unknown', 'formidable' );
		$platform = __( 'Unknown', 'formidable' );
		$ub       = '';

		// Get the operating system
		if ( preg_match( '/windows|win32/i', $u_agent ) ) {
			$platform = 'Windows';
		} elseif ( preg_match( '/android/i', $u_agent ) ) {
			$platform = 'Android';
		} elseif ( preg_match( '/linux/i', $u_agent ) ) {
			$platform = 'Linux';
		} elseif ( preg_match( '/macintosh|mac os x/i', $u_agent ) ) {
			$platform = 'OS X';
		}

		$agent_options = array(
			'Chrome'   => 'Google Chrome',
			'Safari'   => 'Apple Safari',
			'Opera'    => 'Opera',
			'Netscape' => 'Netscape',
			'Firefox'  => 'Mozilla Firefox',
		);

		// Next get the name of the useragent yes seperately and for good reason
		if ( strpos( $u_agent, 'MSIE' ) !== false && strpos( $u_agent, 'Opera' ) === false ) {
			$bname = 'Internet Explorer';
			$ub    = 'MSIE';
		} else {
			foreach ( $agent_options as $agent_key => $agent_name ) {
				if ( strpos( $u_agent, $agent_key ) !== false ) {
					$bname = $agent_name;
					$ub    = $agent_key;
					break;
				}
			}
		}

		// finally get the correct version number
		$known   = array( 'Version', $ub, 'other' );
		$pattern = '#(?<browser>' . join( '|', $known ) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		// Get the matching numbers.
		preg_match_all( $pattern, $u_agent, $matches );

		// see how many we have
		$i = count( $matches['browser'] );

		if ( $i > 1 ) {
			// We will have two since we are not using 'other' argument yet
			// see if version is before or after the name.
			if ( strripos( $u_agent, 'Version' ) < strripos( $u_agent, $ub ) ) {
				$version = $matches['version'][0];
			} else {
				$version = $matches['version'][1];
			}
		} elseif ( $i === 1 ) {
			$version = $matches['version'][0];
		} else {
			$version = '';
		}

		// check if we have a number
		if ( $version == '' ) {
			$version = '?';
		}

		return $bname . ' ' . $version . ' / ' . $platform;
	}

	/**
	 * @since 3.0
	 */
	public static function actions_dropdown( $atts ) {
		$id    = isset( $atts['id'] ) ? $atts['id'] : FrmAppHelper::get_param( 'id', 0, 'get', 'absint' );
		$links = self::get_action_links( $id, $atts['entry'] );

		foreach ( $links as $link ) {
			?>
		<div class="misc-pub-section">
			<a href="<?php echo esc_url( $link['url'] ); ?>"
				<?php
				if ( isset( $link['data'] ) ) {
					foreach ( $link['data'] as $data => $value ) {
						echo 'data-' . esc_attr( $data ) . '="' . esc_attr( $value ) . '" ';
					}
				}
				if ( isset( $link['class'] ) ) {
					echo 'class="' . esc_attr( $link['class'] ) . '" ';
				}
				if ( isset( $link['id'] ) ) {
					echo 'id="' . esc_attr( $link['id'] ) . '" ';
				}
				?>
				>
				<?php FrmAppHelper::icon_by_class( $link['icon'], array( 'aria-hidden' => 'true' ) ); ?>
				<span class="frm_link_label"><?php echo esc_html( $link['label'] ); ?></span>
			</a>
		</div>
			<?php
		}//end foreach
	}

	/**
	 * @since 3.0
	 */
	private static function get_action_links( $id, $entry ) {
		$page    = FrmAppHelper::get_param( 'frm_action' );
		$actions = array();

		if ( $page != 'show' ) {
			$actions['frm_view'] = array(
				'url'   => admin_url( 'admin.php?page=formidable-entries&frm_action=show&id=' . $id . '&form=' . $entry->form_id ),
				'label' => __( 'View Entry', 'formidable' ),
				'icon'  => 'frm_icon_font frm_save_icon',
			);
		}

		if ( current_user_can( 'frm_delete_entries' ) ) {
			$actions['frm_delete'] = array(
				'url'   => wp_nonce_url( admin_url( 'admin.php?page=formidable-entries&frm_action=destroy&id=' . $id . '&form=' . $entry->form_id ) ),
				'label' => __( 'Delete Entry', 'formidable' ),
				'icon'  => 'frm_icon_font frm_delete_icon',
				'data'  => array(
					'frmverify' => __( 'Delete this form entry?', 'formidable' ),
				),
			);
		}

		if ( $page === 'show' ) {
			$actions['frm_print'] = array(
				'url'   => '#',
				'label' => __( 'Print Entry', 'formidable' ),
				'data'  => array(
					'frmprint' => '1',
				),
				'icon'  => 'frm_icon_font frm_printer_icon',
			);
		}

		$actions['frm_resend'] = array(
			'url'   => '#',
			'label' => __( 'Resend Emails', 'formidable' ),
			'class' => 'frm_noallow',
			'data'  => array(
				'upgrade' => __( 'Resend Emails', 'formidable' ),
				'medium'  => 'resend-email',
				'content' => 'entry',
			),
			'icon'  => 'frm_icon_font frm_email_icon',
		);

		if ( ! function_exists( 'frm_pdfs_autoloader' ) && FrmAppHelper::show_new_feature( 'pdfs' ) ) {
			$actions['frm_download_pdf'] = array(
				'url'   => '#',
				'label' => __( 'Download as PDF', 'formidable' ),
				'class' => 'frm_noallow',
				'data'  => self::get_pdfs_upgrade_link_data( 'download-pdf-entry' ),
				'icon'  => 'frm_icon_font frm_download_icon',
			);
		}

		$actions['frm_edit'] = array(
			'url'   => '#',
			'label' => __( 'Edit Entry', 'formidable' ),
			'class' => 'frm_noallow',
			'data'  => array(
				'upgrade' => __( 'Entry edits', 'formidable' ),
				'medium'  => 'edit-entries',
				'content' => 'entry',
			),
			'icon'  => 'frm_icon_font frm_pencil_icon',
		);

		return apply_filters( 'frm_entry_actions_dropdown', $actions, compact( 'id', 'entry' ) );
	}

	/**
	 * Gets data attributes for PDFs addon upgrade link.
	 *
	 * @param string $medium The source of the upgrade link used for analytics data.
	 * @return array
	 */
	private static function get_pdfs_upgrade_link_data( $medium = 'pdfs' ) {
		$data = array(
			'oneclick' => '',
			'requires' => '',
			'upgrade'  => __( 'Forms to PDF', 'formidable' ),
			'medium'   => $medium,
		);

		$upgrading = FrmAddonsController::install_link( 'pdfs' );
		if ( isset( $upgrading['url'] ) ) {
			$data['oneclick'] = json_encode( $upgrading );
		} else {
			$data['requires'] = FrmAddonsController::get_addon_required_plan( 28136428 );
		}

		return $data;
	}

	/**
	 * @since 5.0.15
	 *
	 * @param int|string $entry_id
	 * @return void
	 */
	public static function maybe_render_captcha_score( $entry_id ) {
		$query                 = array(
			'item_id'  => (int) $entry_id,
			'field_id' => 0,
		);
		$metas_without_a_field = (array) FrmEntryMeta::getAll( $query, ' ORDER BY it.created_at DESC', '', true );
		foreach ( $metas_without_a_field as $meta ) {
			if ( ! empty( $meta->meta_value['captcha_score'] ) ) {
				echo '<div class="misc-pub-section">';
				FrmAppHelper::icon_by_class( 'frm_icon_font frm_shield_check_icon', array( 'aria-hidden' => 'true' ) );
				echo ' ' . esc_html__( 'reCAPTCHA Score', 'formidable' ) . ': ';
				echo '<b>' . esc_html( $meta->meta_value['captcha_score'] ) . '</b>';
				echo '</div>';
				return;
			}
		}
	}

	/**
	 * Return entry status based on is_draft column value.
	 *
	 * @since 6.5
	 *
	 * @param int $status is_draft column.
	 *
	 * @return int
	 */
	public static function get_entry_status( $status ) {
		$statuses = self::get_entry_statuses();

		if ( array_key_exists( $status, $statuses ) ) {
			return $status;
		}

		if ( empty( $status ) ) {
			// If the status is empty, let's default to 0.
			return self::SUBMITTED_ENTRY_STATUS;
		}

		// If it has a value that isn't in the array, let's default to 1. There may be old entries that don't have a value for is_draft.
		return self::DRAFT_ENTRY_STATUS;
	}

	/**
	 * Return entry status label based on passed value.
	 *
	 * @since 6.5
	 *
	 * @param int $status is_draft column.
	 *
	 * @return string
	 */
	public static function get_entry_status_label( $status ) {
		$statuses = self::get_entry_statuses();

		return $statuses[ self::get_entry_status( $status ) ];
	}

	/**
	 * Get all entry statuses.
	 *
	 * @since 6.5
	 * @since 6.6 function went from private to public.
	 *
	 * @return array<string>
	 */
	public static function get_entry_statuses() {

		$default_entry_statuses = array(
			self::SUBMITTED_ENTRY_STATUS => __( 'Submitted', 'formidable' ),
			self::DRAFT_ENTRY_STATUS     => __( 'Draft', 'formidable' ),
		);

		/**
		 * Register entry status.
		 *
		 * "2" is used in abandonment-addon and reserved for "In progress".
		 * "3" is used in abandonment-addon and reserved for "Abandoned".
		 *
		 * @since 6.5
		 *
		 * @param array<string> $extended_entry_status Entry statuses.
		 */
		$extended_entry_status = apply_filters( 'frm_entry_statuses', array() );

		if ( ! is_array( $extended_entry_status ) ) {
			_doing_it_wrong( __METHOD__, esc_html__( 'Entry status must be return in array format.', 'formidable' ), '6.5' );
			$extended_entry_status = array();
		}

		$existing_entry_statuses = array_replace( $default_entry_statuses, $extended_entry_status );

		return $existing_entry_statuses;
	}
}
