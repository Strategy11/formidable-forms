<?php

class FrmEntryFormat {

	/**
	 * @var stdClass
	 * @since 2.03.11
	 */
	protected $entry = null;

	/**
	 * @var FrmEntryValues
	 * @since 2.03.11
	 */
	protected $entry_values = null;

	/**
	 * @var bool
	 * @since 2.03.11
	 */
	protected $is_plain_text = false;

	/**
	 * @var bool
	 * @since 2.03.11
	 */
	protected $include_user_info = false;

	/**
	 * @var bool
	 * @since 2.03.11
	 */
	protected $include_blank = false;

	/**
	 * @var string
	 * @since 2.03.11
	 */
	protected $format = 'text';

	/**
	 * @var string
	 * @since 2.03.11
	 */
	protected $direction = 'ltr';

	/**
	 * @var array
	 * @since 2.03.11
	 */
	protected $style_settings = array();

	/**
	 * @var bool
	 * @since 2.03.11
	 */
	protected $use_inline_style = true;

	/**
	 * @var bool
	 * @since 2.03.11
	 */
	protected $is_clickable = false;

	/**
	 * @var array
	 * @since 2.03.11
	 */
	protected $include_extras = array();

	/**
	 * @var bool
	 * @since 2.03.11
	 */
	protected $odd = false;

	/**
	 * @var string
	 * @since 2.03.11
	 */
	protected $table_style = '';

	/**
	 * @var string
	 * @since 2.03.11
	 */
	protected $td_style = '';

	/**
	 * @var array
	 * @since 2.03.11
	 */
	protected $skip_fields = array( 'captcha' );

	/**
	 * FrmEntryFormat constructor
	 *
	 * @since 2.03.11
	 *
	 * @param $atts
	 */
	public function __construct( $atts ) {
		$this->set_entry( $atts );

		if ( $this->entry === null || $this->entry === false ) {
			return;
		}

		$this->set_entry_values( $atts );

		$this->set_format( $atts );
		$this->set_is_plain_text( $atts );
		$this->set_include_blank( $atts );
		$this->set_direction( $atts );
		$this->set_include_user_info( $atts );

		if ( $this->format === 'text' && $this->is_plain_text === false ) {
			$this->set_style_settings( $atts );
			$this->set_use_inline_style( $atts );
			$this->set_table_style();
			$this->set_td_style();
			$this->set_is_clickable( $atts );
		}
	}

	/**
	 * Set the entry property
	 *
	 * @since 2.03.11
	 *
	 * @param array $atts
	 */
	protected function set_entry( $atts ) {
		if ( is_object( $atts['entry'] ) ) {

			if ( isset( $atts['entry']->metas ) ) {
				$this->entry = $atts[ 'entry' ];
			} else {
				$this->entry = FrmEntry::getOne( $atts['entry']->id, true );
			}

		} else if ( $atts['id'] ) {
			$this->entry = FrmEntry::getOne( $atts['id'], true );
		}
	}

	/**
	 * Get the entry property
	 *
	 * @since 2.03.11
	 */
	protected function get_entry() {
		return $this->entry;
	}

	/**
	 * Set the entry values property
	 *
	 * @since 2.03.11
	 *
	 * @param array $atts
	 */
	protected function set_entry_values( $atts ) {
		$this->entry_values = new FrmEntryValues( $this->entry->id, $atts );
	}

	/**
	 * Set the format property
	 *
	 * @since 2.03.11
	 *
	 * @param array $atts
	 */
	protected function set_format( $atts ) {
		if ( isset( $atts['format'] ) && in_array( $atts['format'], array( 'text', 'json', 'array' ) ) ) {
			$this->format = $atts['format'];
		}
	}

	/**
	 * Set the is_plain_text property
	 *
	 * @since 2.03.11
	 *
	 * @param array $atts
	 */
	protected function set_is_plain_text( $atts ) {
		if ( isset( $atts['plain_text'] ) && $atts['plain_text'] ) {
			$this->is_plain_text = true;
		} else if ( $this->format !== 'text' ) {
			$this->is_plain_text = true;
		}
	}

	/**
	 * Set the include_blank property
	 *
	 * @since 2.03.11
	 *
	 * @param array $atts
	 */
	protected function set_include_blank( $atts ) {
		if ( isset( $atts['include_blank'] ) && $atts['include_blank'] ) {
			$this->include_blank = true;
		}
	}

	/**
	 * Set the direction property
	 *
	 * @since 2.03.11
	 *
	 * @param array $atts
	 */
	protected function set_direction( $atts ) {
		if ( isset( $atts['direction'] ) && $atts['direction'] === 'rtl' ) {
			$this->direction = 'rtl';
		}
	}

	/**
	 * Set the include_user_info property
	 *
	 * @since 2.03.11
	 *
	 * @param array $atts
	 */
	protected function set_include_user_info( $atts ) {
		if ( isset( $atts['user_info'] ) && $atts['user_info'] ) {
			$this->include_user_info = true;
		}
	}

	/**
	 * Set the style_settings property
	 *
	 * @since 2.03.11
	 *
	 * @param array $atts
	 */
	protected function set_style_settings( $atts ) {
		$this->style_settings = self::generate_style_settings();

		foreach ( $this->style_settings as $key => $setting ) {
			if ( isset( $atts[ $key ] ) && $atts[ $key ] !== '' ) {
				$this->style_settings[ $key ] = str_replace( '#', '', $atts[ $key ] );
			}
		}
	}

	/**
	 * Set the use_inline_style property
	 *
	 * @since 2.03.11
	 *
	 * @param array $atts
	 */
	protected function set_use_inline_style( $atts ) {
		if ( isset( $atts['inline_style'] ) && ! $atts['inline_style'] ) {
			$this->use_inline_style = false;
		}
	}

	/**
	 * Set the table_style property
	 *
	 * @since 2.03.11
	 */
	protected function set_table_style() {
		if ( $this->use_inline_style === true ) {
			$this->table_style = self::generate_table_style( $this->style_settings );
		}
	}

	/**
	 * Set the td_style property
	 *
	 * @since 2.03.11
	 */
	protected function set_td_style() {
		if ( $this->use_inline_style === true ) {
			$this->td_style = self::generate_td_style( $this->style_settings, $this->direction );
		}
	}

	/**
	 * Set the is_clickable property
	 *
	 * @since 2.03.11
	 *
	 * @param array $atts
	 */
	protected function set_is_clickable( $atts ) {
		if ( isset( $atts['clickable'] ) && $atts['clickable'] ) {
			$this->is_clickable = true;
		}
	}

	/**
	 * Package and return the formatted entry values
	 *
	 * @since 2.03.11
	 *
	 * @return array|string
	 */
	public function formatted_entry_values() {
		if ( $this->format == 'json' ) {
			$content = json_encode( $this->prepare_array_output() );

		} else if ( $this->format == 'array' ) {
			$content = $this->prepare_array_output();

		} else {
			$content = $this->prepare_text_output();

		}

		return $content;
	}

	/**
	 * Flatten an array
	 *
	 * @since 2.03.11
	 *
	 * @param array|string $value
	 */
	protected function flatten_array( &$value ) {
		if ( is_array( $value ) ) {
			$value = implode( ', ', $value );
		}
	}

	/**
	 * Strip HTML if from email value if plain text is selected
	 *
	 * @since 2.0.21
	 *
	 * @param mixed $value
	 */
	protected function strip_html( &$value ) {
		if ( $this->is_plain_text && ! is_array( $value ) ) {
			if ( strpos( $value, '<img' ) !== false ) {
				$value = str_replace( array( '<img', 'src=', '/>', '"' ), '', $value );
				$value = trim( $value );
			}
			$value = strip_tags( $value );
		}
	}

	/**
	 * Prepare the text output
	 *
	 * @since 2.03.11
	 *
	 * @return string
	 */
	protected function prepare_text_output() {
		if ( $this->is_plain_text ) {
			$content = $this->plain_text_content();
		} else {
			$content = $this->html_content();
		}

		return $content;
	}

	/**
	 * Prepare the array output
	 *
	 * @since 2.03.11
	 *
	 * @return array
	 */
	protected function prepare_array_output() {
		$array_output = array();

		$this->push_field_values_to_array( $this->entry_values->get_field_values(), $array_output );

		return $array_output;
	}

	/**
	 * Push field values to array content
	 *
	 * @since 2.03.11
	 *
	 * @param array $field_values
	 * @param array $output
	 */
	protected function push_field_values_to_array( $field_values, &$output ) {
		foreach ( $field_values as $field_info ) {
			$this->push_single_field_to_array( $field_info, $output );
		}
	}

	/**
	 * Push a single field to the array content
	 *
	 * @since 2.03.11
	 *
	 * @param array $field_info
	 * @param array $output
	 */
	protected function push_single_field_to_array( $field_info, &$output ) {
		$field_key = $field_info['field_key'];

		if ( $this->include_field_in_content( $field_info ) ) {

			$displayed_value = $this->filter_display_value( $field_info['displayed_value'] );

			$output[ $field_key ] = $displayed_value;

			if ( $displayed_value !== $field_info['saved_value'] ) {
				$output[ $field_key . '-value' ] = $field_info['saved_value'];
			}
		}
	}

	/**
	 * Filter the displayed value
	 *
	 * @since 2.03.11
	 *
	 * @param mixed $value
	 *
	 * @return mixed|string
	 */
	private function filter_display_value( $value ) {
		if ( $this->is_plain_text && ! is_array( $value ) ) {
			if ( strpos( $value, '<img' ) !== false ) {
				$value = str_replace( array( '<img', 'src=', '/>', '"' ), '', $value );
				$value = trim( $value );
			}
			$value = strip_tags( $value );
		}

		return $value;
	}

	/**
	 * Return the formatted plain text content
	 *
	 * @since 2.03.11
	 *
	 * @return string
	 */
	protected function plain_text_content() {
		$content = '';

		foreach ( $this->entry_values->get_field_values() as $field_id => $field_information ) {
			$this->add_field_value_to_plain_text_content( $field_information, $content );
		}

		$this->add_user_info_to_plain_text_content( $content );

		return $content;
	}

	/**
	 * Return the formatted HTML entry content
	 *
	 * @since 2.03.11
	 *
	 * @return string
	 */
	protected function html_content() {
		$content = '<table cellspacing="0"' . $this->table_style . '><tbody>' . "\r\n";

		$this->odd = true;

		foreach ( $this->entry_values->get_field_values() as $field_id => $field_info ) {
			$this->add_field_value_to_html_table( $field_info, $content );
		}

		$this->add_user_info_to_html_table( $content );

		$content .= '</tbody></table>';

		if ( $this->is_clickable ) {
			$content = make_clickable( $content );
		}

		return $content;
	}

	/**
	 * Add a field value to plain text content
	 *
	 * @since 2.03.11
	 *
	 * @param array $field_info
	 * @param array $content
	 */
	protected function add_field_value_to_plain_text_content( $field_info, &$content ) {
		if ( ! $this->include_field_in_content( $field_info ) ) {
			return;
		}

		$this->add_plain_text_row( $field_info['label'], $field_info['displayed_value'], $content );
	}

	/**
	 * Add a row of values to the plain text content
	 *
	 * @since 2.03.11
	 *
	 * @param string $label
	 * @param mixed $display_value
	 * @param string $content
	 */
	protected function add_plain_text_row( $label, $display_value, &$content ) {
		$this->prepare_display_value_for_plain_text_content( $display_value );

		if ( 'rtl' == $this->direction ) {
			$content .= $display_value . ' :' . $label . "\r\n";
		} else {
			$content .= $label . ': ' . $display_value . "\r\n";
		}
	}

	/**
	 * Add a field value to the HTML table content
	 *
	 * @since 2.03.11
	 *
	 * @param array $field_info
	 * @param string $content
	 */
	protected function add_field_value_to_html_table( $field_info, &$content ) {
		if ( ! $this->include_field_in_content( $field_info ) ) {
			return;
		}

		$display_value = $this->prepare_display_value_for_html_table( $field_info['displayed_value'], $field_info['type'] );
		$this->add_html_row( $field_info['label'], $display_value, $content );
	}

	/**
	 * Add user info to an HTML table
	 *
	 * @since 2.03.11
	 *
	 * @param string $content
	 */
	protected function add_user_info_to_html_table( &$content ) {
		if ( $this->include_user_info ) {

			foreach ( $this->entry_values->get_user_info() as $user_info ) {
				$value = $this->prepare_display_value_for_html_table( $user_info['value'] );

				$this->add_html_row( $user_info['label'], $value, $content );
			}

		}
	}

	/**
	 * Add user info to plain text content
	 *
	 * @since 2.03.11
	 *
	 * @param string $content
	 */
	protected function add_user_info_to_plain_text_content( &$content ) {
		if ( $this->include_user_info ) {

			foreach ( $this->entry_values->get_user_info() as $user_info ) {
				$this->add_plain_text_row( $user_info['label'], $user_info['value'], $content );
			}

		}
	}

	/**
	 * Check if a field should be included in the content
	 *
	 * @since 2.03.11
	 *
	 * @param array $field_info
	 *
	 * @return bool
	 */
	protected function include_field_in_content( $field_info ) {
		$include = true;

		if ( $this->is_extra_field( $field_info ) ) {

			$include = $this->is_extra_field_included( $field_info );

		} else if ( $field_info['displayed_value'] === '' || empty( $field_info['displayed_value'] ) ) {

			if ( ! $this->include_blank ) {
				$include = false;
			}
		}

		return $include;
	}

	/**
	 * Check if a field is normally a skipped type
	 *
	 * @since 2.03.11
	 *
	 * @param array $field_info
	 *
	 * @return bool
	 */
	protected function is_extra_field( $field_info ) {
		return in_array( $field_info['type'], $this->skip_fields );
	}

	/**
	 * Check if an extra field is included
	 *
	 * @since 2.03.11
	 *
	 * @param array $field_info
	 *
	 * @return bool
	 */
	protected function is_extra_field_included( $field_info ) {
		return in_array( $field_info['type'], $this->include_extras );
	}

	/**
	 * Add a row in an HTML table
	 *
	 * @since 2.03.11
	 *
	 * @param string $label
	 * @param mixed $display_value
	 * @param string $content
	 */
	protected function add_html_row( $label, $display_value, &$content ) {
		$content .= '<tr ' . $this->tr_style() . '>';

		if ( 'rtl' == $this->direction ) {
			$first = $display_value;
			$second = $label;
		} else {
			$first = $label;
			$second = $display_value;
		}

		$content .= '<td' . $this->td_style . '>' . $first . '</td>';
		$content .= '<td' . $this->td_style . '>' . $second . '</td>';

		$content .= '</tr>' . "\r\n";

		$this->odd = ! $this->odd;
	}

	/**
	 * Prepare a field's display value for an HTML table
	 *
	 * @since 2.03.11
	 *
	 * @param mixed $display_value
	 * @param string $field_type
	 *
	 * @return mixed|string
	 */
	protected function prepare_display_value_for_html_table( $display_value, $field_type = '' ) {
		$this->flatten_array( $display_value );
		$display_value = str_replace( "\r\n", '<br/>', $display_value );

		return $display_value;
	}

	/**
	 * Prepare a field's display value for plain text content
	 *
	 * @since 2.03.11
	 *
	 * @param mixed $display_value
	 */
	protected function prepare_display_value_for_plain_text_content( &$display_value ) {
		$this->flatten_array( $display_value );
		$this->strip_html( $display_value );
	}

	/**
	 * Get the table row style
	 *
	 * @since 2.03.11
	 *
	 * @return string
	 */
	private function tr_style() {
		if ( $this->use_inline_style ) {
			$tr_style = 'style="background-color:#' . $this->table_row_background_color() . ';"';
		} else {
			$tr_style = '';
		}

		return $tr_style;
	}

	/**
	 * Get the table row background color
	 *
	 * @since 2.03.11
	 *
	 * @return string
	 */
	protected function table_row_background_color() {
		return ( $this->odd ? $this->style_settings['bg_color'] : $this->style_settings['alt_bg_color'] );
	}

	// TODO: Maybe deprecate and move to controller
	public static function show_entry( $atts ) {
		// TODO: maybe set defaults in relevant classes
		$defaults = array(
			'id'             => false,
			'entry'          => false,
			'fields'         => false,
			'plain_text'     => false,
			'user_info'      => false,
			'include_blank'  => false,
			'default_email'  => false,
			'form_id'        => false,
			'format'         => 'text',
			'direction'      => 'ltr',
			'font_size'      => '',
			'text_color'     => '',
			'border_width'   => '',
			'border_color'   => '',
			'bg_color'       => '',
			'alt_bg_color'   => '',
			'clickable'      => false,
			'exclude_fields' => '',
			'include_fields' => '',
			'include_extras' => '',
			'inline_style'   => 1,
		);

		$atts = shortcode_atts( $defaults, $atts );
		// TODO: handle these atts differently?

		if ( $atts['default_email'] ) {
			$default_html_generator = FrmEntryFactory::create_html_generator_instance( $atts['form_id'], $atts['format'] );
			return $default_html_generator->content();
		}

		if ( $atts['format'] != 'text' ) {
			$atts['plain_text'] = true;
		}

		if ( is_array( $atts['fields'] ) && ! empty( $atts['fields'] ) && ! $atts['include_fields'] ) {
			$atts['include_fields'] = '';
			foreach ( $atts['fields'] as $included_field ) {
				$atts['include_fields'] .= $included_field->id . ',';
			}

			$atts['include_fields'] = rtrim( $atts['include_fields'], ',' );
		}

		$entry_data = FrmEntryFactory::create_entry_format_instance( $atts );

		if ( $entry_data->get_entry() === null || $entry_data->get_entry() === false ) {
			return '';
		}

		$formatted_entry = $entry_data->formatted_entry_values();

		return $formatted_entry;
	}

	// TODO: deprecate or delete most functions below this point

	/**
	 * Get the labels and value shortcodes for fields in the Default HTML email message
	 *
	 * @since 2.0.23
	 * @param object $f
	 * @param array $values
	 */
	public static function get_field_shortcodes_for_default_email( $f, &$values ) {
		// TODO: deprecate?
		$field_shortcodes = array(
			'label' => '[' . $f->id . ' show=field_label]',
			'val'   => '[' . $f->id . ']',
			'type'  => $f->type,
		);

		$values[ $f->id ] = apply_filters( 'frm_field_shortcodes_for_default_html_email', $field_shortcodes, $f );
	}

	// TODO: deprecate this
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

	// TODO: maybe move to helper class
	public static function generate_td_style( $style_settings, $direction = 'ltr' ) {
		$td_style_attributes = 'text-align:' . ( $direction == 'rtl' ? 'right' : 'left' ) . ';';
		$td_style_attributes .= 'color:#' . $style_settings[ 'text_color' ] . ';padding:7px 9px;vertical-align:top;';
		$td_style_attributes .= 'border-top:' . $style_settings[ 'border_width' ] . ' solid #' . $style_settings[ 'border_color' ] . ';';

		return ' style="' . $td_style_attributes . '"';
	}

	// TODO: maybe move to helper class
	public static function generate_table_style( $style_settings ) {
		$table_style = ' style="' . esc_attr( 'font-size:' . $style_settings[ 'font_size' ] . ';line-height:135%;' );
		$table_style .= esc_attr( 'border-bottom:' . $style_settings[ 'border_width' ] . ' solid #' . $style_settings[ 'border_color' ] . ';' ) . '"';

		return $table_style;
	}

	// TODO: move to helper?
	public static function generate_style_settings() {
		return apply_filters( 'frm_show_entry_styles', array(
			'border_color' => 'dddddd',
			'bg_color'     => 'f7f7f7',
			'text_color'   => '444444',
			'font_size'    => '12px',
			'border_width' => '1px',
			'alt_bg_color' => 'ffffff',
		) );
	}

	/**
	 * @deprecated 2.03.04
	 */
	public static function textarea_display_value() {
		_deprecated_function( __FUNCTION__, '2.03.04', 'custom code' );
	}

	/**
	 * @deprecated 2.03.11
	 */
	public static function single_html_row( $atts, &$content ) {
		_deprecated_function( __FUNCTION__, '2.03.11', 'custom code' );
	}

	/**
	 * @deprecated 2.03.11
	 *
	 * @param stdClass $field
	 * @param array|string $val
	 */
	public static function flatten_multi_file_upload( $field, &$val ) {
		if ( $field->type == 'file' && FrmField::is_option_true( $field, 'multiple' ) ) {
			$val = FrmAppHelper::array_flatten( $val );
		}

		_deprecated_function( __FUNCTION__, '2.03.11', 'custom code' );
	}

	/**
	 * @deprecated 2.03.11
	 */
	public static function fill_entry_user_info() {
		_deprecated_function( __FUNCTION__, '2.03.11', 'custom code' );
	}

	/**
	 * @deprecated 2.03.11
	 */
	public static function get_entry_description_data() {
		_deprecated_function( __FUNCTION__, '2.03.11', 'custom code' );

		return array();
	}

	/**
	 * @deprecated 2.03.11
	 */
	public static function single_plain_text_row() {
		_deprecated_function( __FUNCTION__, '2.03.11', 'custom code' );
	}

	/**
	 * @deprecated 2.03.11
	 */
	public static function html_field_row() {
		_deprecated_function( __FUNCTION__, '2.03.11', 'custom code' );
	}
}
