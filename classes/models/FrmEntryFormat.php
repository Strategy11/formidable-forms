<?php

class FrmEntryFormat {
	public static function show_entry( $atts ) {
		$atts = shortcode_atts( array(
			'id' => false, 'entry' => false, 'fields' => false, 'plain_text' => false,
			'user_info' => false, 'include_blank' => false, 'default_email' => false,
			'form_id' => false, 'format' => 'text', 'direction' => 'ltr',
			'font_size' => '', 'text_color' => '',
			'border_width' => '', 'border_color' => '',
			'bg_color' => '', 'alt_bg_color' => '',
			'clickable' => false,
			'exclude_fields' => '', 'include_fields' => '',
			'include_extras' => '', 'inline_style' => 1,
		), $atts );

		$atts['exclude_fields'] = self::comma_list_to_array( $atts['exclude_fields'] );
		$atts['include_fields'] = self::comma_list_to_array( $atts['include_fields'] );
		$atts['include_extras'] = self::comma_list_to_array( $atts['include_extras'] );

		if ( $atts['format'] != 'text' ) {
			//format options are text, array, or json
			$atts['plain_text'] = true;
		}

		if ( is_object( $atts['entry'] ) && ! isset( $atts['entry']->metas ) ) {
			// if the entry does not include metas, force it again
			$atts['entry'] = false;
		}

		if ( ! $atts['entry'] || ! is_object( $atts['entry'] ) ) {
			if ( ! $atts['id'] && ! $atts['default_email'] ) {
				return '';
			}

			if ( $atts['id'] ) {
				$atts['entry'] = FrmEntry::getOne( $atts['id'], true );
			}
		}

		if ( $atts['entry'] ) {
			$atts['form_id'] = $atts['entry']->form_id;
			$atts['id'] = $atts['entry']->id;
		}

		if ( ! $atts['fields'] || ! is_array( $atts['fields'] ) ) {
			$atts['fields'] = FrmField::get_all_for_form( $atts['form_id'], '', 'include' );
		}

		$values = array();
		foreach ( $atts['fields'] as $f ) {
			if ( ! self::skip_field( $atts, $f ) ) {
				self::fill_entry_values( $atts, $f, $values );
			}
			unset($f);
		}

		self::fill_entry_user_info( $atts, $values );
		$values = apply_filters( 'frm_show_entry_array', $values, $atts );

		if ( $atts['format'] == 'json' ) {
			$content = json_encode( $values );
		} else if ( $atts['format'] == 'array' ) {
			$content = $values;
		} else {
			$content = array();
			self::prepare_text_output( $values, $atts, $content );
		}

		return $content;
	}

	private static function comma_list_to_array( $list ) {
		$array = array_map( 'strtolower', array_map( 'trim', explode( ',', $list ) ) );
		$field_types = array(
			'section' => 'divider',
			'heading' => 'divider',
			'page'    => 'break',
		);
		foreach ( $field_types as $label => $field_type ) {
			if ( in_array( $label, $array ) ) {
				$array[] = $field_type;
			}
		}
		return $array;
	}

	private static function skip_field( $atts, $field ) {
		$skip = ( $field->type == 'password' || $field->type == 'credit_card' );

		if ( $skip && ! empty( $atts['include_extras'] ) ) {
			$skip = ! in_array( $field->type, $atts['include_extras'] );
		}

		if ( ! $skip && ! empty( $atts['exclude_fields'] ) ) {
			$skip = self::field_in_list( $field, $atts['exclude_fields'] );
		}

		if ( $skip && ! empty( $atts['include_fields'] ) ) {
			$skip = ! self::field_in_list( $field, $atts['include_fields'] );
		}

		return $skip;
	}

	private static function field_in_list( $field, $list ) {
		return ( in_array( $field->id, $list ) || in_array( $field->field_key, $list ) );
	}

	/**
	 * Get the labels and value shortcodes for fields in the Default HTML email message
	 *
	 * @since 2.0.23
	 * @param object $f
	 * @param array $values
	 */
	public static function get_field_shortcodes_for_default_email( $f, &$values ) {
		$field_shortcodes = array(
			'label' => '[' . $f->id . ' show=field_label]',
			'val'   => '[' . $f->id . ']',
			'type'  => $f->type,
		);

		$values[ $f->id ] = apply_filters( 'frm_field_shortcodes_for_default_html_email', $field_shortcodes, $f );
	}

	public static function fill_entry_values( $atts, $f, array &$values ) {
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

	private static function fill_missing_fields( $atts, &$values ) {
		if ( $atts['entry'] && ! isset( $atts['entry']->metas[ $atts['field']->id ] ) ) {
			// In case include_blank is set
			$atts['entry']->metas[ $atts['field']->id ] = '';
			$atts['entry'] = apply_filters( 'frm_prepare_entry_content', $atts['entry'], array( 'field' => $atts['field'] ) );
			self::fill_values_from_entry( $atts, $values );
		}
	}

	public static function fill_values_from_entry( $atts, &$values ) {
		$values = apply_filters( 'frm_prepare_entry_array', $values, $atts );
	}

	private static function get_field_value( $atts, &$val ) {
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
	* Flatten multi-dimensional array for multi-file upload fields
	* @since 2.0.9
	*/
	public static function flatten_multi_file_upload( $field, &$val ) {
		if ( $field->type == 'file' && FrmField::is_option_true( $field, 'multiple' ) ) {
			$val = FrmAppHelper::array_flatten( $val );
		}
	}

	/**
	 * @since 2.03.02
	 */
	public static function prepare_field_output( $atts, &$val ) {
		$val = apply_filters( 'frm_display_' . $atts['field']->type . '_value_custom', $val, array(
			'field' => $atts['field'], 'atts' => $atts,
		) );

		self::flatten_array_value( $atts, $val );
		self::maybe_strip_html( $atts['plain_text'], $val );
	}

	/**
	 * @since 2.03.02
	 */
	private static function flatten_array_value( $atts, &$val ) {
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
	 */
	private static function maybe_strip_html( $plain_text, &$val ) {
		if ( $plain_text && ! is_array( $val ) ) {
			if ( strpos( $val, '<img' ) !== false ) {
				$val = str_replace( array( '<img', 'src=', '/>', '"' ), '', $val );
				$val = trim( $val );
			}
			$val = strip_tags( $val );
		}
	}

	public static function fill_entry_user_info( $atts, array &$values ) {
		if ( ! $atts['user_info'] || empty( $atts['entry'] ) ) {
			return;
		}

		$data  = self::get_entry_description_data( $atts );

		if ( $atts['default_email'] ) {
			$atts['entry']->ip = '[ip]';
		}

		if ( $atts['format'] != 'text' ) {
			$values['ip'] = $atts['entry']->ip;
			$values['browser'] = self::get_browser( $data['browser'] );
			$values['referrer'] = $data['referrer'];
		} else {
			$values['ip'] = array( 'label' => __( 'IP Address', 'formidable' ), 'val' => $atts['entry']->ip );
			$values['browser'] = array(
				'label' => __( 'User-Agent (Browser/OS)', 'formidable' ),
				'val'   => self::get_browser( $data['browser'] ),
			);
			$values['referrer'] = array( 'label' => __( 'Referrer', 'formidable' ), 'val' => $data['referrer'] );
		}
	}

	/**
	 * @param array $atts - include (object) entry, (boolean) default_email
	 * @since 2.0.9
	 */
	public static function get_entry_description_data( $atts ) {
		$default_data = array(
			'browser' => '',
			'referrer' => '',
		);
		$data = $default_data;

		if ( isset( $atts['entry']->description ) ) {
			$data = (array) maybe_unserialize( $atts['entry']->description );
		} else if ( $atts['default_email'] ) {
			$data = array(
				'browser'  => '[browser]',
				'referrer' => '[referrer]',
			);
		}

		return array_merge( $default_data, $data );
	}

	public static function get_browser( $u_agent ) {
		$bname = __( 'Unknown', 'formidable' );
		$platform = __( 'Unknown', 'formidable' );
		$ub = '';

		// Get the operating system
		if ( preg_match( '/windows|win32/i', $u_agent ) ) {
			$platform = 'Windows';
		} else if ( preg_match( '/android/i', $u_agent ) ) {
			$platform = 'Android';
		} else if ( preg_match( '/linux/i', $u_agent ) ) {
			$platform = 'Linux';
		} else if ( preg_match( '/macintosh|mac os x/i', $u_agent ) ) {
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
			$ub = 'MSIE';
		} else {
			foreach ( $agent_options as $agent_key => $agent_name ) {
				if ( strpos( $u_agent, $agent_key ) !== false ) {
					$bname = $agent_name;
					$ub = $agent_key;
					break;
				}
			}
		}

		// finally get the correct version number
		$known = array( 'Version', $ub, 'other' );
		$pattern = '#(?<browser>' . join( '|', $known ) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		preg_match_all( $pattern, $u_agent, $matches ); // get the matching numbers

		// see how many we have
		$i = count($matches['browser']);

		if ( $i > 1 ) {
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if ( strripos( $u_agent, 'Version' ) < strripos( $u_agent, $ub ) ) {
				$version = $matches['version'][0];
			} else {
				$version = $matches['version'][1];
			}
		} else if ( $i === 1 ) {
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

	private static function prepare_text_output( $values, $atts, &$content ) {
		self::convert_entry_to_content( $values, $atts, $content );

		if ( 'text' == $atts['format'] ) {
			$content = implode('', $content);
		}

		if ( $atts['clickable'] ) {
			$content = make_clickable( $content );
		}
	}

	public static function convert_entry_to_content( $values, $atts, array &$content ) {
		if ( $atts['plain_text'] ) {
			self::plain_text_content( $values, $atts, $content );
		} else {
			self::html_content( $values, $atts, $content );
		}
	}

	private static function plain_text_content( $values, $atts, &$content ) {
		foreach ( $values as $id => $value ) {
			$atts['id'] = $id;
			$atts['value'] = $value;
			self::single_plain_text_row( $atts, $content );
		}
	}

	private static function html_content( $values, $atts, &$content ) {
		self::setup_defaults( $atts );
		self::prepare_inline_styles( $atts );

		$content[] = '<table cellspacing="0" ' . $atts['table_style'] . '><tbody>' . "\r\n";

		$atts['odd'] = true;
		foreach ( $values as $id => $value ) {
			$atts['id'] = $id;
			$atts['value'] = $value;
			self::single_html_row( $atts, $content );
			$atts['odd'] = ! $atts['odd'];
		}

		$content[] = '</tbody></table>';
	}

	private static function setup_defaults( &$atts ) {
		$default_settings = apply_filters( 'frm_show_entry_styles', array(
			'border_color' => 'dddddd',
			'bg_color'     => 'f7f7f7',
			'text_color'   => '444444',
			'font_size'    => '12px',
			'border_width' => '1px',
			'alt_bg_color' => 'ffffff',
		) );

		// merge defaults, global settings, and shortcode options
		foreach ( $default_settings as $key => $setting ) {
			if ( $atts[ $key ] != '' ) {
				continue;
			}

			$atts[ $key ] = $setting;
			unset( $key, $setting );
		}
	}

	private static function prepare_inline_styles( &$atts ) {
		if ( empty( $atts['inline_style'] ) ) {
			$atts['table_style'] = $atts['bg_color'] = $atts['bg_color_alt'] = $atts['row_style'] = '';
		} else {
			$atts['table_style'] = ' style="' . esc_attr( 'font-size:' . $atts['font_size'] . ';line-height:135%; border-bottom:' . $atts['border_width'] . ' solid #' . $atts['border_color'] . ';' ) . '"';

			$row_style_attributes = 'text-align:' . ( $atts['direction'] == 'rtl' ? 'right' : 'left' ) . ';';
			$row_style_attributes .= 'color:#' . $atts['text_color'] . ';padding:7px 9px;vertical-align:top;';
			$row_style_attributes .= 'border-top:' . $atts['border_width'] . ' solid #' . $atts['border_color'] . ';';
			$atts['row_style'] = ' style="' . $row_style_attributes . '"';

			if ( $atts['default_email'] ) {
				$atts['bg_color'] = $atts['bg_color_alt'] = ' style="[frm-alt-color]"';
			} else {
				$atts['bg_color'] = ' style="background-color:#' . $atts['bg_color'] . ';"';
				$atts['bg_color_alt'] = ' style="background-color:#' . $atts['alt_bg_color'] . ';"';
			}
		}
	}

	public static function single_plain_text_row( $atts, &$content ) {
		$row = array();
		if ( 'rtl' == $atts['direction'] ) {
			$row[] = $atts['value']['val'] . ' :' . $atts['value']['label'] . "\r\n";
		} else {
			$row[] = $atts['value']['label'] . ': ' . $atts['value']['val'] . "\r\n";
		}
		$row = apply_filters( 'frm_entry_plain_text_row', $row, $atts );
		$content = array_merge( $content, $row );
	}

	public static function single_html_row( $atts, &$content ) {
		$row = array();
		if ( $atts['default_email'] && is_numeric( $atts['id'] ) ) {
			self::default_email_row( $atts, $row );
		} else {
			self::row_content( $atts, $row );
		}
		$row = apply_filters( 'frm_entry_html_row', $row, $atts );
		$content = array_merge( $content, $row );
	}

	public static function html_field_row( $atts, &$content ) {
		$content[] = '<tr ' . self::table_row_style( $atts ) . '>';
		$content[] = '<td colspan="2" ' . $atts['row_style'] . '>' . $atts['value']['val'] . '</td>';
		$content[] = '</tr>' . "\r\n";
	}

	private static function default_email_row( $atts, &$content ) {
		$content[] = '[if ' . $atts['id'] . ']';
		self::row_content( $atts, $content );
		$content[] = '[/if ' . $atts['id'] . ']' . "\r\n";
	}

	private static function row_content( $atts, &$content ) {
		$content[] = '<tr' . self::table_row_style( $atts ) . '>';

		$atts['value']['val'] = str_replace( "\r\n", '<br/>', $atts['value']['val'] );

		if ( 'rtl' == $atts['direction'] ) {
			$first = $atts['value']['val'];
			$second = $atts['value']['label'];
		} else {
			$first = $atts['value']['label'];
			$second = $atts['value']['val'];
		}

		$content[] = '<td ' . $atts['row_style'] . '>' . $first . '</td>';
		$content[] = '<td ' . $atts['row_style'] . '>' . $second . '</td>';

		$content[] = '</tr>' . "\r\n";
	}

	private static function table_row_style( $atts ) {
		return ( $atts['odd'] ? $atts['bg_color'] : $atts['bg_color_alt'] );
	}

	/**
	 * @deprecated 2.03.04
	 */
	public static function textarea_display_value() {
		_deprecated_function( __FUNCTION__, '2.03.04', 'custom code' );
	}
}
