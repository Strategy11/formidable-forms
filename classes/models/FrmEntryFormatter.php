<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 2.04
 */
class FrmEntryFormatter {

	/**
	 * @var stdClass
	 * @since 2.04
	 */
	protected $entry = null;

	/**
	 * @var FrmEntryValues
	 * @since 2.04
	 */
	protected $entry_values = null;

	/**
	 * @var bool
	 * @since 2.04
	 */
	protected $is_plain_text = false;

	/**
	 * @var bool
	 * @since 2.04
	 */
	protected $include_user_info = false;

	/**
	 * @var bool
	 * @since 2.04
	 */
	protected $include_blank = false;

	/**
	 * @var string
	 * @since 2.04
	 */
	protected $format = 'text';

	/**
	 * @var string
	 * @since 2.05
	 */
	protected $array_key = 'key';

	/**
	 * @var string
	 * @since 2.04
	 */
	protected $direction = 'ltr';

	/**
	 * @var FrmTableHTMLGenerator
	 * @since 2.04
	 */
	protected $table_generator = null;

	/**
	 * @var bool
	 * @since 2.04
	 */
	protected $is_clickable = false;

	/**
	 * @var array
	 * @since 2.04
	 */
	protected $include_extras = array();

	/**
	 * @var array
	 * @since 3.0
	 */
	protected $single_cell_fields = array();

	/**
	 * @var array
	 * @since 3.0
	 */
	protected $atts = array();

	/**
	 * FrmEntryFormat constructor
	 *
	 * @since 2.04
	 *
	 * @param $atts
	 */
	public function __construct( $atts ) {
		$this->init_entry( $atts );

		if ( $this->entry === null || $this->entry === false ) {
			return;
		}

		$this->init_is_plain_text( $atts );
		$this->init_format( $atts );
		$this->init_array_key( $atts );
		$this->init_include_blank( $atts );
		$this->init_direction( $atts );
		$this->init_include_user_info( $atts );
		$this->init_include_extras( $atts );
		$this->init_single_cell_fields();
		$this->init_entry_values( $atts );

		if ( $this->format === 'table' ) {
			$this->init_table_generator( $atts );
			$this->init_is_clickable( $atts );
		}

		$this->init_atts( $atts );
	}

	/**
	 * Set the entry property
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 */
	protected function init_entry( $atts ) {
		if ( isset( $atts['entry'] ) && is_object( $atts['entry'] ) ) {

			if ( isset( $atts['entry']->metas ) ) {
				$this->entry = $atts['entry'];
			} else {
				$this->entry = FrmEntry::getOne( $atts['entry']->id, true );
			}
		} elseif ( ! empty( $atts['id'] ) ) {
			$this->entry = FrmEntry::getOne( $atts['id'], true );
		}
	}

	/**
	 * Set the entry values property
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 */
	protected function init_entry_values( $atts ) {
		$entry_atts         = $this->prepare_entry_attributes( $atts );
		$this->entry_values = new FrmEntryValues( $this->entry->id, $entry_atts );
	}

	/**
	 * Prepare attributes array for FrmEntryValues constructor
	 *
	 * @since 2.05
	 *
	 * @param array $atts
	 *
	 * @return array
	 */
	protected function prepare_entry_attributes( $atts ) {
		$entry_atts = array();

		$conditionally_add = array( 'include_fields', 'fields', 'exclude_fields', 'entry' );
		foreach ( $conditionally_add as $index ) {
			if ( isset( $atts[ $index ] ) ) {
				$entry_atts[ $index ] = $atts[ $index ];
			}
		}

		return $entry_atts;
	}

	/**
	 * Set the format property
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 */
	protected function init_format( $atts ) {
		if ( $atts['format'] === 'array' ) {

			$this->format = 'array';

		} elseif ( $atts['format'] === 'json' ) {

			$this->format = 'json';

		} elseif ( $atts['format'] === 'text' ) {

			if ( $this->is_plain_text === true ) {
				$this->format = 'plain_text_block';
			} else {
				$this->format = 'table';
			}
		}

		/**
		 * Allows modifying the format property of FrmEntryFormatter object.
		 *
		 * @since 5.0.16
		 *
		 * @param string $format The format.
		 * @param array  $args   Includes `atts`, `entry`.
		 */
		$this->format = apply_filters(
			'frm_entry_formatter_format',
			$this->format,
			array(
				'atts'  => $atts,
				'entry' => $this->entry,
			)
		);
	}

	/**
	 * Set the array_key property that sets whether the keys in the
	 * returned array are field keys or ids
	 *
	 * @since 2.05
	 *
	 * @param array $atts
	 */
	protected function init_array_key( $atts ) {
		if ( isset( $atts['array_key'] ) && $atts['array_key'] == 'id' ) {
			$this->array_key = 'id';
		}
	}

	/**
	 * Set the is_plain_text property
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 */
	protected function init_is_plain_text( $atts ) {
		if ( isset( $atts['plain_text'] ) && $atts['plain_text'] ) {
			$this->is_plain_text = true;
		} elseif ( $atts['format'] !== 'text' ) {
			$this->is_plain_text = true;
		}
	}

	/**
	 * Set the include_blank property
	 *
	 * @since 2.04
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
	 * @since 2.04
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
	 * @since 2.04
	 *
	 * @param array $atts
	 */
	protected function init_include_user_info( $atts ) {
		if ( isset( $atts['user_info'] ) && $atts['user_info'] ) {
			$this->include_user_info = true;
		}
	}

	/**
	 * Which fields to skip by default
	 *
	 * @since 3.0
	 */
	protected function skip_fields() {
		return array( 'captcha', 'html' );
	}

	/**
	 * Set the include_extras property
	 *
	 * @since 3.0
	 *
	 * @param array $atts
	 */
	protected function init_include_extras( $atts ) {
		if ( isset( $atts['include_extras'] ) && $atts['include_extras'] ) {
			$this->include_extras = array_map( 'strtolower', array_map( 'trim', explode( ',', $atts['include_extras'] ) ) );
		}
	}

	/**
	 * Initialize the single_cell_fields property
	 *
	 * @since 3.0
	 */
	protected function init_single_cell_fields() {
		$this->single_cell_fields = array( 'html' );
	}

	/**
	 * Set the table_generator property
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 */
	protected function init_table_generator( $atts ) {
		$this->table_generator = new FrmTableHTMLGenerator( 'entry', $atts );
	}

	/**
	 * Set the is_clickable property
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 */
	protected function init_is_clickable( $atts ) {
		if ( isset( $atts['clickable'] ) && $atts['clickable'] ) {
			$this->is_clickable = true;
		}
	}

	/**
	 * Save the passed atts for other calls. Exclude some attributes to prevent
	 * interaction with processing field values like time format.
	 *
	 * @since 3.0
	 */
	protected function init_atts( $atts ) {
		$atts['source']       = 'entry_formatter';
		$atts['wpautop']      = false;
		$atts['return_array'] = true;

		$unset = array( 'id', 'entry', 'form_id', 'format' );
		foreach ( $unset as $param ) {
			if ( isset( $atts[ $param ] ) ) {
				unset( $atts[ $param ] );
			}
		}

		$this->atts = $atts;
	}

	/**
	 * Get the field key or ID, depending on array_key property
	 *
	 * @since 2.05
	 *
	 * @param FrmFieldValue $field_value
	 *
	 * @return string|int
	 */
	protected function get_key_or_id( $field_value ) {
		return $this->array_key == 'key' ? $field_value->get_field_key() : $field_value->get_field_id();
	}

	/**
	 * Package and return the formatted entry values
	 *
	 * @since 2.04
	 *
	 * @return array|string
	 */
	public function get_formatted_entry_values() {
		if ( $this->entry === null || $this->entry === false ) {
			return '';
		}

		if ( $this->format === 'json' ) {
			$content = json_encode( $this->prepare_array() );

		} elseif ( $this->format === 'array' ) {
			$content = $this->prepare_array();

		} elseif ( $this->format === 'table' ) {
			$content = $this->prepare_html_table();

		} elseif ( $this->format === 'plain_text_block' ) {
			$content = $this->prepare_plain_text_block();

		} else {
			$content = '';
		}

		/**
		 * Allows modifying the formatted entry values content.
		 *
		 * @since 5.0.16
		 *
		 * @param string $content The formatted entry values content.
		 * @param array  $args    Includes `entry`, `atts`, `format`, `entry_values`.
		 */
		return apply_filters(
			'frm_formatted_entry_values_content',
			$content,
			array(
				'entry'        => $this->entry,
				'atts'         => $this->atts,
				'format'       => $this->format,
				'entry_values' => $this->entry_values,
			)
		);
	}

	/**
	 * Return the formatted HTML table with entry values
	 *
	 * @since 2.04
	 *
	 * @return string
	 */
	protected function prepare_html_table() {
		$content = $this->table_generator->generate_table_header();

		$this->add_field_values_to_content( $content );
		$this->add_user_info_to_html_table( $content );

		$content .= $this->table_generator->generate_table_footer();

		if ( $this->is_clickable ) {
			$content = make_clickable( $content );
		}

		return $content;
	}

	/**
	 * Add field values to table or plain text content
	 *
	 * @since 2.05
	 *
	 * @param string $content
	 */
	protected function add_field_values_to_content( &$content ) {
		foreach ( $this->entry_values->get_field_values() as $field_id => $field_value ) {

			/**
			 * @var FrmFieldValue $field_value
			 */
			$field_value->prepare_displayed_value( $this->atts );
			$this->add_field_value_to_content( $field_value, $content );
		}
	}

	/**
	 * Return the formatted plain text content
	 *
	 * @since 2.04
	 *
	 * @return string
	 */
	protected function prepare_plain_text_block() {
		$content = '';

		$this->add_field_values_to_content( $content );
		$this->add_user_info_to_plain_text_content( $content );

		return $content;
	}

	/**
	 * Prepare the array output
	 *
	 * @since 2.04
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
	 * @since 2.04
	 *
	 * @param array $field_values
	 * @param array $output
	 */
	protected function push_field_values_to_array( $field_values, &$output ) {
		foreach ( $field_values as $field_value ) {
			/**
			 * @var FrmFieldValue $field_value
			 */
			$field_value->prepare_displayed_value( $this->atts );
			$this->push_single_field_to_array( $field_value, $output );
		}
	}

	/**
	 * Push a single field to the array content
	 *
	 * @since 2.04
	 *
	 * @param FrmFieldValue $field_value
	 * @param array $output
	 */
	protected function push_single_field_to_array( $field_value, &$output ) {
		if ( $this->include_field_in_content( $field_value ) ) {

			$displayed_value = $this->prepare_display_value_for_array( $field_value->get_displayed_value() );

			$output[ $this->get_key_or_id( $field_value ) ] = $displayed_value;

			$has_separate_value = (bool) $field_value->get_field_option( 'separate_value' );
			if ( $has_separate_value || $displayed_value !== $field_value->get_saved_value() ) {
				$output[ $this->get_key_or_id( $field_value ) . '-value' ] = $field_value->get_saved_value();
			}
		}
	}

	/**
	 * Add a row of values to the plain text content
	 *
	 * @since 2.04
	 *
	 * @param string $label
	 * @param mixed $display_value
	 * @param string $content
	 */
	protected function add_plain_text_row( $label, $display_value, &$content ) {
		$display_value = $this->prepare_display_value_for_plain_text_content( $display_value );

		if ( 'rtl' == $this->direction ) {
			$content .= wp_kses_post( $display_value . ' :' . $label ) . "\r\n";
		} else {
			$content .= wp_kses_post( $label . ': ' . $display_value ) . "\r\n";
		}
	}

	/**
	 * Add a field value to the HTML table or plain text content
	 *
	 * @since 2.04
	 *
	 * @param FrmFieldValue $field_value
	 * @param string $content
	 */
	protected function add_field_value_to_content( $field_value, &$content ) {
		if ( $this->is_extra_field( $field_value ) ) {
			$this->add_row_for_extra_field( $field_value, $content );

		} else {
			$this->add_row_for_standard_field( $field_value, $content );
		}
	}

	/**
	 * Add an extra field to plain text or html table content
	 *
	 * @since 3.0
	 *
	 * @param FrmFieldValue $field_value
	 * @param string $content
	 */
	protected function add_row_for_extra_field( $field_value, &$content ) {
		if ( ! $this->include_field_in_content( $field_value ) ) {
			return;
		}

		if ( $this->format === 'plain_text_block' ) {
			$this->add_plain_text_row_for_included_extra( $field_value, $content );
		} elseif ( $this->format === 'table' ) {
			$this->add_html_row_for_included_extra( $field_value, $content );
		}
	}

	/**
	 * Add a standard row to plain text or html table content
	 *
	 * @since 3.0
	 *
	 * @param FrmFieldValue $field_value
	 * @param string $content
	 */
	protected function add_row_for_standard_field( $field_value, &$content ) {
		if ( ! $this->include_field_in_content( $field_value ) ) {
			return;
		}

		if ( $this->format === 'plain_text_block' ) {
			$this->add_plain_text_row( $field_value->get_field_label(), $field_value->get_displayed_value(), $content );
		} elseif ( $this->format === 'table' ) {
			$value_args = $this->package_value_args( $field_value );
			$this->add_html_row( $value_args, $content );
		}
	}

	/**
	 * Add a row to table for included extra
	 *
	 * @since 3.0
	 *
	 * @param FrmFieldValue $field_value
	 * @param string $content
	 */
	protected function add_html_row_for_included_extra( $field_value, &$content ) {
		$this->prepare_html_display_value_for_extra_fields( $field_value, $display_value );

		if ( in_array( $field_value->get_field_type(), $this->single_cell_fields ) ) {
			$this->add_single_cell_html_row( $display_value, $content );
		} else {
			$value_args = $this->package_value_args( $field_value );
			$this->add_html_row( $value_args, $content );
		}
	}

	/**
	 * Add a plain text row for included extra
	 *
	 * @since 3.0
	 *
	 * @param FrmFieldValue $field_value
	 * @param string $content
	 */
	protected function add_plain_text_row_for_included_extra( $field_value, &$content ) {
		$this->prepare_plain_text_display_value_for_extra_fields( $field_value, $display_value );

		if ( in_array( $field_value->get_field_type(), $this->single_cell_fields ) ) {
			$this->add_single_value_plain_text_row( $display_value, $content );
		} else {
			$this->add_plain_text_row( $field_value->get_field_label(), $display_value, $content );
		}
	}

	/**
	 * Add a single cell row to an HTML table
	 *
	 * @since 3.0
	 *
	 * @param string $display_value
	 * @param string $content
	 */
	protected function add_single_cell_html_row( $display_value, &$content ) {
		// TODO: maybe move to FrmFieldValue
		$display_value = $this->prepare_display_value_for_html_table( $display_value );

		$content .= $this->table_generator->generate_single_cell_table_row( $display_value );
	}

	/**
	 * Add a single value plain text row
	 *
	 * @since 3.0
	 *
	 * @param string $display_value
	 * @param string $content
	 */
	protected function add_single_value_plain_text_row( $display_value, &$content ) {
		$content .= $this->prepare_display_value_for_plain_text_content( $display_value );
	}

	/**
	 * Prepare the display value for extra fields an HTML table
	 *
	 * @since 3.0
	 *
	 * @param FrmFieldValue $field_value
	 * @param mixed $display_value
	 */
	protected function prepare_html_display_value_for_extra_fields( $field_value, &$display_value ) {
		$display_value = $field_value->get_displayed_value();
	}

	/**
	 * Prepare a plain text value for extra fields
	 *
	 * @since 3.0
	 *
	 * @param FrmFieldValue $field_value
	 * @param mixed $display_value
	 */
	protected function prepare_plain_text_display_value_for_extra_fields( $field_value, &$display_value ) {
		$display_value = $field_value->get_displayed_value() . "\r\n";
	}

	/**
	 * Add a standard row to plain text or html table content
	 *
	 * @since 2.04
	 *
	 * @param FrmFieldValue $field_value
	 * @param string $content
	 */
	protected function add_standard_row( $field_value, &$content ) {
		if ( $this->format === 'plain_text_block' ) {
			$this->add_plain_text_row( $field_value->get_field_label(), $field_value->get_displayed_value(), $content );
		} elseif ( $this->format === 'table' ) {
			$value_args = $this->package_value_args( $field_value );
			$this->add_html_row( $value_args, $content );
		}
	}

	/**
	 * Package the value arguments for an HTML row
	 *
	 * @since 2.04
	 *
	 * @param FrmFieldValue $field_value
	 *
	 * @return array
	 */
	protected function package_value_args( $field_value ) {
		return array(
			'label'      => $field_value->get_field_label(),
			'value'      => $field_value->get_displayed_value(),
			'field_type' => $field_value->get_field_type(),
		);
	}

	/**
	 * Add user info to an HTML table
	 *
	 * @since 2.04
	 *
	 * @param string $content
	 */
	protected function add_user_info_to_html_table( &$content ) {
		if ( $this->include_user_info ) {

			foreach ( $this->entry_values->get_user_info() as $user_info ) {

				$value_args = array(
					'label'      => $user_info['label'],
					'value'      => $user_info['value'],
					'field_type' => 'none',
				);

				$this->add_html_row( $value_args, $content );
			}
		}
	}

	/**
	 * Add user info to plain text content
	 *
	 * @since 2.04
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
	 * @since 2.04
	 *
	 * @param FrmFieldValue $field_value
	 *
	 * @return bool
	 */
	protected function include_field_in_content( $field_value ) {
		$include = true;

		if ( $this->is_extra_field( $field_value ) ) {
			$include = $this->is_extra_field_included( $field_value );
		} elseif ( FrmAppHelper::is_empty_value( $field_value->get_displayed_value() ) && ! $this->include_blank ) {
			$include = false;
		}

		return apply_filters( 'frm_include_field_in_content', $include, $field_value );
	}

	/**
	 * Check if a field is normally a skipped type
	 *
	 * @since 2.04
	 *
	 * @param FrmFieldValue $field_value
	 *
	 * @return bool
	 */
	protected function is_extra_field( $field_value ) {
		return in_array( $field_value->get_field_type(), $this->skip_fields() );
	}

	/**
	 * Check if an extra field is included
	 *
	 * @since 2.04
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
	 * @since 2.04
	 *
	 * @param array $value_args
	 *   $value_args = [
	 *     'label' => (string) The label. Required
	 *     'value' => (mixed) The value to add. Required
	 *     'field_type' => (string) The field type. Blank string if not a field.
	 *   ]
	 * @param string $content
	 */
	protected function add_html_row( $value_args, &$content ) {
		$display_value = $this->prepare_display_value_for_html_table( $value_args['value'], $value_args['field_type'] );

		$content .= $this->table_generator->generate_two_cell_table_row( $value_args['label'], $display_value );
	}

	/**
	 * Prepare the displayed value for an array
	 *
	 * @since 2.04
	 *
	 * @param mixed $value
	 *
	 * @return mixed|string
	 */
	protected function prepare_display_value_for_array( $value ) {
		return $this->strip_html( $value );
	}

	/**
	 * Prepare a field's display value for an HTML table
	 *
	 * @since 2.04
	 *
	 * @param mixed $display_value
	 * @param string $field_type
	 *
	 * @return mixed|string
	 */
	protected function prepare_display_value_for_html_table( $display_value, $field_type = '' ) {
		$display_value = $this->flatten_array( $display_value );
		if ( ! isset( $this->atts['line_breaks'] ) || ! empty( $this->atts['line_breaks'] ) ) {
			$display_value = str_replace( array( "\r\n", "\n" ), '<br/>', $display_value );
		}

		return $display_value;
	}

	/**
	 * Prepare a field's display value for plain text content
	 *
	 * @since 2.04
	 *
	 * @param mixed $display_value
	 *
	 * @return string|int
	 */
	protected function prepare_display_value_for_plain_text_content( $display_value ) {
		$display_value = $this->flatten_array( $display_value );
		$display_value = $this->strip_html( $display_value );

		return $display_value;
	}

	/**
	 * Flatten an array
	 *
	 * @since 2.04
	 *
	 * @param array|string|int $value
	 *
	 * @return string|int
	 */
	protected function flatten_array( $value ) {
		if ( is_array( $value ) ) {
			$separator = isset( $this->atts['array_separator'] ) ? $this->atts['array_separator'] : ', ';
			$value     = implode( $separator, $value );
		}

		return $value;
	}

	/**
	 * Strip HTML if from email value if plain text is selected
	 *
	 * @since 2.0.21
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	protected function strip_html( $value ) {

		if ( $this->is_plain_text ) {

			if ( is_array( $value ) ) {
				foreach ( $value as $key => $single_value ) {
					$value[ $key ] = $this->strip_html( $single_value );
				}
			} elseif ( $this->is_plain_text && ! is_array( $value ) ) {
				if ( strpos( $value, '<img' ) !== false ) {
					$value = str_replace( array( '<img', 'src=', '/>', '"' ), '', $value );
					$value = trim( $value );
				}
				$value = strip_tags( $value );
			}
		}

		return $value;
	}
}
