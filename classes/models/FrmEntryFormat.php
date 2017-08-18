<?php

class FrmEntryFormat {

	/***********************************************************************
	 * Deprecated Functions
	 ************************************************************************/

	/**
	 * @deprecated 2.03.04
	 */
	public static function textarea_display_value() {
		_deprecated_function( __FUNCTION__, '2.03.04', 'custom code' );
	}

	/**
	 * @deprecated 2.04
	 */
	public static function single_html_row( $atts, &$content ) {
		_deprecated_function( __FUNCTION__, '2.04', 'custom code' );
	}

	/**
	 * @deprecated 2.04
	 *
	 * @param stdClass $field
	 * @param array|string $val
	 */
	public static function flatten_multi_file_upload( $field, &$val ) {
		if ( $field->type == 'file' && FrmField::is_option_true( $field, 'multiple' ) ) {
			$val = FrmAppHelper::array_flatten( $val );
		}

		_deprecated_function( __FUNCTION__, '2.04', 'custom code' );
	}

	/**
	 * @deprecated 2.04
	 */
	public static function fill_entry_user_info() {
		_deprecated_function( __FUNCTION__, '2.04', 'custom code' );
	}

	/**
	 * @deprecated 2.04
	 */
	public static function get_entry_description_data() {
		_deprecated_function( __FUNCTION__, '2.04', 'custom code' );

		return array();
	}

	/**
	 * @deprecated 2.04
	 */
	public static function single_plain_text_row() {
		_deprecated_function( __FUNCTION__, '2.04', 'custom code' );
	}

	/**
	 * @deprecated 2.04
	 */
	public static function html_field_row() {
		_deprecated_function( __FUNCTION__, '2.04', 'custom code' );
	}

	/**
	 * @deprecated 2.04
	 */
	public static function fill_entry_values( $atts, $f, array &$values ) {
		_deprecated_function( __FUNCTION__, '2.04', 'instance of FrmEntryValues or FrmProEntryValues' );

		$no_save_field = FrmField::is_no_save_field( $f->type );
		if ( $no_save_field ) {
			if ( ! in_array( $f->type, $atts['include_extras'] ) ) {
				return;
			}
			$atts['include_blank'] = true;
		}

		if ( $atts['default_email'] ) {
			self::get_field_shortcodes_for_default_email( $f, $values );
			return;
		}

		$atts['field'] = $f;

		self::fill_missing_fields( $atts, $values );

		$val = '';
		self::get_field_value( $atts, $val );

		// Don't include blank values
		if ( ! $atts['include_blank'] && FrmAppHelper::is_empty_value( $val ) ) {
			return;
		}

		self::prepare_field_output( $atts, $val );

		if ( $atts['format'] != 'text' ) {
			$values[ $f->field_key ] = $val;
			if ( $atts['entry'] && $f->type != 'textarea' ) {
				$prev_val = maybe_unserialize( $atts['entry']->metas[ $f->id ] );
				if ( $prev_val != $val ) {
					$values[ $f->field_key . '-value' ] = $prev_val;
				}
			}
		} else {
			$values[ $f->id ] = array( 'label' => $f->name, 'val' => $val, 'type' => $f->type );
		}
	}

	/**
	 * @deprecated 2.04
	 */
	private static function fill_missing_fields( $atts, &$values ) {
		_deprecated_function( __FUNCTION__, '2.04', 'instance of FrmEntryValues or FrmProEntryValues' );

		if ( $atts['entry'] && ! isset( $atts['entry']->metas[ $atts['field']->id ] ) ) {
			// In case include_blank is set
			$atts['entry']->metas[ $atts['field']->id ] = '';
			$atts['entry'] = apply_filters( 'frm_prepare_entry_content', $atts['entry'], array( 'field' => $atts['field'] ) );
			self::fill_values_from_entry( $atts, $values );
		}
	}

	/**
	 * @deprecated 2.04
	 */
	public static function fill_values_from_entry( $atts, &$values ) {
		_deprecated_function( __FUNCTION__, '2.04', 'instance of FrmEntryValues or FrmProEntryValues' );

		$values = apply_filters( 'frm_prepare_entry_array', $values, $atts );
	}

	/**
	 * @deprecated 2.04
	 */
	public static function get_field_shortcodes_for_default_email( $f, &$values ) {
		// TODO: adjust this message
		_deprecated_function( __FUNCTION__, '2.04', 'instance of FrmEntryValues or FrmProEntryValues' );

		$field_shortcodes = array(
			'label' => '[' . $f->id . ' show=field_label]',
			'val'   => '[' . $f->id . ']',
			'type'  => $f->type,
		);

		$values[ $f->id ] = apply_filters( 'frm_field_shortcodes_for_default_html_email', $field_shortcodes, $f );
	}

	/**
	 * @deprecated 2.04
	 */
	private static function get_field_value( $atts, &$val ) {
		_deprecated_function( __FUNCTION__, '2.04', 'instance of FrmEntryValues or FrmProEntryValues' );

		$f = $atts['field'];
		if ( $atts['entry'] ) {
			$prev_val = maybe_unserialize( $atts['entry']->metas[ $f->id ] );
			$meta = array( 'item_id' => $atts['id'], 'field_id' => $f->id, 'meta_value' => $prev_val, 'field_type' => $f->type );

			//This filter applies to the default-message shortcode and frm-show-entry shortcode only
			if ( in_array( $f->type, array( 'html', 'divider', 'break' ) ) ) {
				$val = apply_filters( 'frm_content', $f->description, $atts['form_id'], $atts['entry'] );
			} elseif ( isset( $atts['filter'] ) && $atts['filter'] == false ) {
				$val = $prev_val;
			} else {
				$email_value_atts = array( 'field' => $f, 'format' => $atts['format'] );
				$val = apply_filters( 'frm_email_value', $prev_val, (object) $meta, $atts['entry'], $email_value_atts );
			}
		}
	}

	/**
	 * @since 2.03.02
	 *
	 * @deprecated 2.04
	 */
	public static function prepare_field_output( $atts, &$val ) {
		_deprecated_function( __FUNCTION__, '2.04', 'instance of FrmEntryValues or FrmProEntryValues' );

		$val = apply_filters( 'frm_display_' . $atts['field']->type . '_value_custom', $val, array(
			'field' => $atts['field'], 'atts' => $atts,
		) );

		self::flatten_array_value( $atts, $val );
		self::maybe_strip_html( $atts['plain_text'], $val );
	}

	/**
	 * @since 2.03.02
	 *
	 * @deprecated 2.04
	 */
	private static function flatten_array_value( $atts, &$val ) {
		_deprecated_function( __FUNCTION__, '2.04', 'instance of FrmEntryValues or FrmProEntryValues' );

		if ( is_array( $val ) ) {
			if ( $atts['format'] == 'text' ) {
				$val = implode( ', ', $val );
			} else if ( $atts['field']->type == 'checkbox' ) {
				$val = array_values( $val );
			}
		}
	}

	/**
	 * Strip HTML if from email value if plain text is selected
	 *
	 * @since 2.0.21
	 * @param boolean $plain_text
	 * @param mixed $val
	 *
	 * @deprecated 2.04
	 */
	private static function maybe_strip_html( $plain_text, &$val ) {
		_deprecated_function( __FUNCTION__, '2.04', 'instance of FrmEntryValues or FrmProEntryValues' );

		if ( $plain_text && ! is_array( $val ) ) {
			if ( strpos( $val, '<img' ) !== false ) {
				$val = str_replace( array( '<img', 'src=', '/>', '"' ), '', $val );
				$val = trim( $val );
			}
			$val = strip_tags( $val );
		}
	}

	/**
	 * @deprecated 2.04
	 */
	public static function get_browser( $u_agent ) {
		_deprecated_function( __FUNCTION__, '2.04', 'FrmEntriesHelper::get_browser' );
		return FrmEntriesHelper::get_browser( $u_agent );
	}

	/**
	 * @deprecated 2.04
	 */
	public static function show_entry( $atts ) {
		_deprecated_function( __FUNCTION__, '2.04', 'FrmEntriesController::show_entry_shortcode' );
		return FrmEntriesController::show_entry_shortcode( $atts );
	}

	/**
	 * @deprecated 2.04
	 */
	public static function convert_entry_to_content() {
		_deprecated_function( __FUNCTION__, '2.04', 'FrmEntriesController::show_entry_shortcode' );
	}
}
