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
		), $atts );

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
				return;
			}

			if ( $atts['id'] ) {
				$atts['entry'] = FrmEntry::getOne( $atts['id'], true );
			}
		}

		if ( $atts['entry'] ) {
			$atts['form_id'] = $atts['entry']->form_id;
			$atts['id'] = $atts['entry']->id;
		}

		if ( ! $atts['fields'] || ! is_array($atts['fields']) ) {
			$atts['fields'] = FrmField::get_all_for_form( $atts['form_id'], '', 'include' );
		}

		$values = array();
		foreach ( $atts['fields'] as $f ) {
			self::fill_entry_values( $atts, $f, $values );
			unset($f);
		}

		self::fill_entry_user_info( $atts, $values );

		if ( $atts['format'] == 'json' ) {
			return json_encode($values);
		} else if ( $atts['format'] == 'array' ) {
			return $values;
		}

		$content = array();
		self::convert_entry_to_content( $values, $atts, $content );

		if ( 'text' == $atts['format'] ) {
			$content = implode('', $content);
		}

		if ( $atts['clickable'] ) {
			$content = make_clickable( $content );
		}

		return $content;
	}

	public static function fill_entry_values( $atts, $f, array &$values ) {
		if ( FrmField::is_no_save_field( $f->type ) ) {
			return;
		}

		if ( $atts['default_email'] ) {
			$values[ $f->id ] = array( 'label' => '[' . $f->id . ' show=field_label]', 'val' => '[' . $f->id . ']' );
			return;
		}

		//Remove signature from default-message shortcode
		if ( $f->type == 'signature' ) {
			return;
		}

		if ( $atts['entry'] && ! isset( $atts['entry']->metas[ $f->id ] ) ) {
			// In case include_blank is set
			$atts['entry']->metas[ $f->id ] = '';

			if ( FrmAppHelper::pro_is_installed() ) {
				FrmProEntryMeta::add_post_value_to_entry( $f, $atts['entry'] );
				FrmProEntryMeta::add_repeating_value_to_entry( $f, $atts['entry'] );
			}
		}

		$val = '';
		if ( $atts['entry'] ) {
			$prev_val = maybe_unserialize( $atts['entry']->metas[ $f->id ] );
			$meta = array( 'item_id' => $atts['id'], 'field_id' => $f->id, 'meta_value' => $prev_val, 'field_type' => $f->type );

			//This filter applies to the default-message shortcode and frm-show-entry shortcode only
			if ( isset( $atts['filter'] ) && $atts['filter'] == false ) {
				$val = $prev_val;
			} else {
				$val = apply_filters( 'frm_email_value', $prev_val, (object) $meta, $atts['entry'] );
			}
		}

		// Don't include blank values
		if ( ! $atts['include_blank'] && FrmAppHelper::is_empty_value( $val ) ) {
			return;
		}

		self::textarea_display_value( $f->type, $atts['plain_text'], $val );

		if ( is_array( $val ) && $atts['format'] == 'text' ) {
			$val = implode( ', ', $val );
		}

		if ( $atts['format'] != 'text' ) {
			$values[ $f->field_key ] = $val;
		} else {
			$values[ $f->id ] = array( 'label' => $f->name, 'val' => $val );
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
     * Replace returns with HTML line breaks for display
     * @since 2.0.9
     */
	public static function textarea_display_value( $type, $plain_text, &$value ) {
		if ( $type == 'textarea' && ! $plain_text ) {
			$value = str_replace( array( "\r\n", "\r", "\n" ), ' <br/>', $value );
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
		if ( $i != 1 ) {
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if ( strripos( $u_agent, 'Version' ) < strripos( $u_agent, $ub ) ) {
				$version = $matches['version'][0];
			} else {
				$version = $matches['version'][1];
			}
		} else {
			$version = $matches['version'][0];
		}

		// check if we have a number
		if ( $version == '' ) {
			$version = '?';
		}

		return $bname .' '. $version .' / '. $platform;
	}

	public static function convert_entry_to_content( $values, $atts, array &$content ) {

		if ( $atts['plain_text'] ) {
			$bg_color_alt = $row_style = '';
		} else {
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

			unset($default_settings);

			$content[] = '<table cellspacing="0" style="font-size:'. $atts['font_size'] .';line-height:135%; border-bottom:'. $atts['border_width'] . ' solid #' . $atts['border_color'] . ';"><tbody>' . "\r\n";
			$atts['bg_color'] = ' style="background-color:#'. $atts['bg_color'] .';"';
			$bg_color_alt = ' style="background-color:#'. $atts['alt_bg_color'] .';"';
			$row_style = 'style="text-align:' . ( $atts['direction'] == 'rtl' ? 'right' : 'left' ) .';color:#'. $atts['text_color'] . ';padding:7px 9px;border-top:' . $atts['border_width'] .' solid #' . $atts['border_color'] . '"';
		}

		$odd = true;
		foreach ( $values as $id => $value ) {
			if ( $atts['plain_text'] ) {
				if ( 'rtl' == $atts['direction'] ) {
					$content[] = $value['val'] . ' :'. $value['label'] ."\r\n";
				} else {
					$content[] = $value['label'] . ': '. $value['val'] ."\r\n";
				}
				continue;
			}

			if ( $atts['default_email'] && is_numeric($id) ) {
				$content[] = '[if ' . $id . ']<tr style="[frm-alt-color]">';
			} else {
				$content[] = '<tr' . ( $odd ? $atts['bg_color'] : $bg_color_alt ) . '>';
			}

			$value['val'] = str_replace( "\r\n", '<br/>', $value['val'] );
			if ( 'rtl' == $atts['direction'] ) {
				$content[] = '<td ' . $row_style . '>' . $value['val'] . '</td><th ' . $row_style . '>' . $value['label'] . '</th>';
			} else {
				$content[] = '<th ' . $row_style . '>' . $value['label'] . '</th><td '. $row_style . '>' . $value['val'] . '</td>';
			}
			$content[] = '</tr>' . "\r\n";

			if ( $atts['default_email'] && is_numeric( $id ) ) {
				$content[] = '[/if ' . $id . ']';
			}
			$odd = $odd ? false : true;
		}

		if ( ! $atts['plain_text'] ) {
			$content[] = '</tbody></table>';
		}
	}
}
