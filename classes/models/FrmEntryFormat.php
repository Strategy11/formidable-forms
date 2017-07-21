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
		$this->init_entry( $atts );

		if ( $this->entry === null || $this->entry === false ) {
			return;
		}

		$this->init_entry_values( $atts );

		$this->init_format( $atts );
		$this->init_is_plain_text( $atts );
		$this->init_include_blank( $atts );
		$this->init_direction( $atts );
		$this->init_include_user_info( $atts );

		if ( $this->format === 'text' && $this->is_plain_text === false ) {
			$this->init_style_settings( $atts );
			$this->init_use_inline_style( $atts );
			$this->init_table_style();
			$this->init_td_style();
			$this->init_is_clickable( $atts );
		}
	}

	/**
	 * Set the entry property
	 *
	 * @since 2.03.11
	 *
	 * @param array $atts
	 */
	protected function init_entry( $atts ) {
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
	public function get_entry() {
		return $this->entry;
	}

	/**
	 * Set the entry values property
	 *
	 * @since 2.03.11
	 *
	 * @param array $atts
	 */
	protected function init_entry_values( $atts ) {
		$this->entry_values = new FrmEntryValues( $this->entry->id, $atts );
	}

	/**
	 * Set the format property
	 *
	 * @since 2.03.11
	 *
	 * @param array $atts
	 */
	protected function init_format( $atts ) {
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
	protected function init_is_plain_text( $atts ) {
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
	protected function init_include_blank( $atts ) {
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
	protected function init_direction( $atts ) {
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
	protected function init_include_user_info( $atts ) {
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
	protected function init_style_settings( $atts ) {
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
	protected function init_use_inline_style( $atts ) {
		if ( isset( $atts['inline_style'] ) && ! $atts['inline_style'] ) {
			$this->use_inline_style = false;
		}
	}

	/**
	 * Set the table_style property
	 *
	 * @since 2.03.11
	 */
	protected function init_table_style() {
		if ( $this->use_inline_style === true ) {
			$this->table_style = self::generate_table_style( $this->style_settings );
		}
	}

	/**
	 * Set the td_style property
	 *
	 * @since 2.03.11
	 */
	protected function init_td_style() {
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
	protected function init_is_clickable( $atts ) {
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
		foreach ( $field_values as $field_value ) {
			$this->push_single_field_to_array( $field_value, $output );
		}
	}

	/**
	 * Push a single field to the array content
	 *
	 * @since 2.03.11
	 *
	 * @param FrmFieldValue $field_value
	 * @param array $output
	 */
	protected function push_single_field_to_array( $field_value, &$output ) {
		if ( $this->include_field_in_content( $field_value ) ) {

			// TODO: maybe do filtering in FrmFieldValue instead
			$displayed_value = $this->filter_display_value( $field_value->get_displayed_value() );

			$output[ $field_value->get_field_key() ] = $displayed_value;

			if ( $displayed_value !== $field_value->get_saved_value() ) {
				$output[ $field_value->get_field_key() . '-value' ] = $field_value->get_saved_value();
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
	protected function filter_display_value( $value ) {
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

		foreach ( $this->entry_values->get_field_values() as $field_id => $field_value ) {
			$this->add_field_value_to_plain_text_content( $field_value, $content );
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

		foreach ( $this->entry_values->get_field_values() as $field_id => $field_value ) {
			$this->add_field_value_to_html_table( $field_value, $content );
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
	 * @param FrmFieldValue $field_value
	 * @param array $content
	 */
	protected function add_field_value_to_plain_text_content( $field_value, &$content ) {
		if ( ! $this->include_field_in_content( $field_value ) ) {
			return;
		}

		$this->add_plain_text_row( $field_value->get_field_label(), $field_value->get_displayed_value(), $content );
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
		// TODO: move to pro field value?
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
	 * @param FrmFieldValue $field_value
	 * @param string $content
	 */
	protected function add_field_value_to_html_table( $field_value, &$content ) {
		if ( ! $this->include_field_in_content( $field_value ) ) {
			return;
		}

		// TODO: add display value prep to ProFieldValue?
		$display_value = $this->prepare_display_value_for_html_table( $field_value->get_displayed_value(), $field_value->get_field_type() );
		$this->add_html_row( $field_value->get_field_label(), $display_value, $content );
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
	 * @param FrmFieldValue $field_value
	 *
	 * @return bool
	 */
	protected function include_field_in_content( $field_value ) {
		$include = true;

		if ( $this->is_extra_field( $field_value ) ) {

			$include = $this->is_extra_field_included( $field_value );

		} else if ( $field_value->get_displayed_value() === '' || empty( $field_value->get_displayed_value() ) ) {

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
	 * @param FrmFieldValue $field_value
	 *
	 * @return bool
	 */
	protected function is_extra_field( $field_value ) {
		return in_array( $field_value->get_field_type(), $this->skip_fields );
	}

	/**
	 * Check if an extra field is included
	 *
	 * @since 2.03.11
	 *
	 * @param FrmFieldValue $field_value
	 *
	 * @return bool
	 */
	protected function is_extra_field_included( $field_value ) {
		return in_array( $field_value->get_field_type(), $this->include_extras );
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

	/**
	 * @deprecated 2.03.11
	 */
	public static function fill_entry_values( $atts, $f, array &$values ) {
		_deprecated_function( __FUNCTION__, '2.03.11', 'instance of FrmEntryValues or FrmProEntryValues' );

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
	 * @deprecated 2.03.11
	 */
	private static function fill_missing_fields( $atts, &$values ) {
		_deprecated_function( __FUNCTION__, '2.03.11', 'instance of FrmEntryValues or FrmProEntryValues' );

		if ( $atts['entry'] && ! isset( $atts['entry']->metas[ $atts['field']->id ] ) ) {
			// In case include_blank is set
			$atts['entry']->metas[ $atts['field']->id ] = '';
			$atts['entry'] = apply_filters( 'frm_prepare_entry_content', $atts['entry'], array( 'field' => $atts['field'] ) );
			self::fill_values_from_entry( $atts, $values );
		}
	}

	/**
	 * @deprecated 2.03.11
	 */
	public static function fill_values_from_entry( $atts, &$values ) {
		_deprecated_function( __FUNCTION__, '2.03.11', 'instance of FrmEntryValues or FrmProEntryValues' );

		$values = apply_filters( 'frm_prepare_entry_array', $values, $atts );
	}

	/**
	 * @deprecated 2.03.11
	 */
	public static function get_field_shortcodes_for_default_email( $f, &$values ) {
		// TODO: adjust this message
		_deprecated_function( __FUNCTION__, '2.03.11', 'instance of FrmEntryValues or FrmProEntryValues' );

		$field_shortcodes = array(
			'label' => '[' . $f->id . ' show=field_label]',
			'val'   => '[' . $f->id . ']',
			'type'  => $f->type,
		);

		$values[ $f->id ] = apply_filters( 'frm_field_shortcodes_for_default_html_email', $field_shortcodes, $f );
	}

	/**
	 * @deprecated 2.03.11
	 */
	private static function get_field_value( $atts, &$val ) {
		_deprecated_function( __FUNCTION__, '2.03.11', 'instance of FrmEntryValues or FrmProEntryValues' );

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
	 * @deprecated 2.03.11
	 */
	public static function prepare_field_output( $atts, &$val ) {
		_deprecated_function( __FUNCTION__, '2.03.11', 'instance of FrmEntryValues or FrmProEntryValues' );

		$val = apply_filters( 'frm_display_' . $atts['field']->type . '_value_custom', $val, array(
			'field' => $atts['field'], 'atts' => $atts,
		) );

		self::flatten_array_value( $atts, $val );
		self::maybe_strip_html( $atts['plain_text'], $val );
	}

	/**
	 * @since 2.03.02
	 *
	 * @deprecated 2.03.11
	 */
	private static function flatten_array_value( $atts, &$val ) {
		_deprecated_function( __FUNCTION__, '2.03.11', 'instance of FrmEntryValues or FrmProEntryValues' );

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
	 * @deprecated 2.03.11
	 */
	private static function maybe_strip_html( $plain_text, &$val ) {
		_deprecated_function( __FUNCTION__, '2.03.11', 'instance of FrmEntryValues or FrmProEntryValues' );

		if ( $plain_text && ! is_array( $val ) ) {
			if ( strpos( $val, '<img' ) !== false ) {
				$val = str_replace( array( '<img', 'src=', '/>', '"' ), '', $val );
				$val = trim( $val );
			}
			$val = strip_tags( $val );
		}
	}

	/**
	 * @deprecated 2.03.11
	 */
	public static function get_browser( $u_agent ) {
		_deprecated_function( __FUNCTION__, '2.03.11', 'FrmEntriesHelper::get_browser' );
		return FrmEntriesHelper::get_browser( $u_agent );
	}

	/**
	 * @deprecated 2.03.11
	 */
	public static function show_entry( $atts ) {
		_deprecated_function( __FUNCTION__, '2.03.11', 'FrmEntriesController::show_entry_shortcode' );
		return FrmEntriesController::show_entry_shortcode( $atts );
	}
}
