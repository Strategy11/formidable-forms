<?php

/**
 * @since 2.03.11
 */
class FrmEntryFormatter {

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
	 * @var FrmTableHTMLHelper
	 * @since 2.03.11
	 */
	protected $table_helper = null;

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
		$this->init_is_plain_text( $atts );
		$this->init_format( $atts );
		$this->init_include_blank( $atts );
		$this->init_direction( $atts );
		$this->init_include_user_info( $atts );

		if ( $this->format === 'table' ) {
			$this->init_table_helper( $atts );
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
		if ( $atts['format'] === 'array' ) {

			$this->format = 'array';

		} else if ( $atts['format'] === 'json' ) {

			$this->format = 'json';

		} else if ( $atts['format'] === 'text' ) {

			if ( $this->is_plain_text === true ) {
				$this->format = 'plain_text_block';
			} else {
				$this->format = 'table';
			}
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
		} else if ( $atts['format'] !== 'text' ) {
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
	 * Set the table_helper property
	 *
	 * @since 2.03.11
	 *
	 * @param array $atts
	 */
	protected function init_table_helper( $atts ) {
		$this->table_helper = new FrmTableHTMLHelper( 'entry', $atts );
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
	public function get_formatted_entry_values() {
		if ( $this->entry === null || $this->entry === false ) {
			return '';
		}

		if ( $this->format === 'json' ) {
			$content = json_encode( $this->prepare_array() );

		} else if ( $this->format === 'array' ) {
			$content = $this->prepare_array();

		} else if ( $this->format === 'table' ) {
			$content = $this->prepare_html_table();

		} else if ( $this->format === 'plain_text_block' ) {
			$content = $this->prepare_plain_text_block();

		} else {
			$content = '';
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
	 * Return the formatted HTML table with entry values
	 *
	 * @since 2.03.11
	 *
	 * @return string
	 */
	protected function prepare_html_table() {
		$content = $this->table_helper->generate_table_header();

		foreach ( $this->entry_values->get_field_values() as $field_id => $field_value ) {
			$this->add_field_value_to_html_table( $field_value, $content );
		}

		$this->add_user_info_to_html_table( $content );

		$content .= $this->table_helper->generate_table_footer();

		if ( $this->is_clickable ) {
			$content = make_clickable( $content );
		}

		return $content;
	}

	/**
	 * Return the formatted plain text content
	 *
	 * @since 2.03.11
	 *
	 * @return string
	 */
	protected function prepare_plain_text_block() {
		$content = '';

		foreach ( $this->entry_values->get_field_values() as $field_id => $field_value ) {
			$this->add_field_value_to_plain_text_content( $field_value, $content );
		}

		$this->add_user_info_to_plain_text_content( $content );

		return $content;
	}

	/**
	 * Prepare the array output
	 *
	 * @since 2.03.11
	 *
	 * @return array
	 */
	protected function prepare_array() {
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
		$content .= $this->table_helper->generate_two_cell_table_row( $label, $display_value );
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

}