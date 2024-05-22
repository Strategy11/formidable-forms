<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 2.04
 */
class FrmEntryShortcodeFormatter {

	/**
	 * @var int
	 * @since 2.04
	 */
	protected $form_id = 0;

	/**
	 * @var array
	 * @since 2.04
	 */
	protected $skip_fields = array( 'captcha', 'html' );

	/**
	 * @var array
	 * @since 3.0
	 */
	protected $single_cell_fields = array( 'html' );

	/**
	 * @var array
	 * @since 2.04
	 */
	protected $fields = array();

	/**
	 * @var bool
	 * @since 2.05
	 */
	protected $is_plain_text = false;

	/**
	 * @var string
	 * @since 2.04
	 */
	protected $format = 'text';

	/**
	 * @var FrmTableHTMLGenerator|null
	 * @since 2.04
	 */
	protected $table_generator;

	/**
	 * @var array
	 * @since 2.04
	 */
	protected $array_content = array();

	/**
	 * FrmEntryShortcodeFormatter constructor
	 *
	 * @param int|string $form_id
	 * @param array      $atts
	 */
	public function __construct( $form_id, $atts ) {
		if ( ! $form_id ) {
			return;
		}

		$this->init_form_id( $form_id );
		$this->init_fields();

		if ( empty( $this->fields ) ) {
			return;
		}

		$this->init_plain_text( $atts );
		$this->init_format( $atts );

		if ( $this->is_table_format() ) {
			$this->init_table_generator();
		}
	}

	/**
	 * Initialize the form_id property
	 *
	 * @since 2.04
	 *
	 * @param int|string $form_id
	 *
	 * @return void
	 */
	protected function init_form_id( $form_id ) {
		$this->form_id = (int) $form_id;
	}

	/**
	 * Initialize the fields property
	 *
	 * @since 2.04
	 *
	 * @return void
	 */
	protected function init_fields() {
		$this->fields = FrmField::get_all_for_form( $this->form_id, '', 'exclude', 'exclude' );
	}

	/**
	 * Initialize the is_plain_text property
	 *
	 * @since 2.05
	 *
	 * @param array $atts
	 *
	 * @return void
	 */
	protected function init_plain_text( $atts ) {
		if ( isset( $atts['plain_text'] ) && $atts['plain_text'] ) {
			$this->is_plain_text = true;
		}
	}

	/**
	 * Initialize the format property
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 *
	 * @return void
	 */
	protected function init_format( $atts ) {
		if ( isset( $atts['format'] ) && is_string( $atts['format'] ) && $atts['format'] !== '' ) {
			$this->format = $atts['format'];
		} else {
			$this->format = 'text';
		}
	}

	/**
	 * Initialize the table_generator property
	 *
	 * @since 2.04
	 *
	 * @return void
	 */
	protected function init_table_generator() {
		$this->table_generator = new FrmTableHTMLGenerator( 'shortcode' );
	}

	/**
	 * Return the default HTML for an entry
	 *
	 * @since 2.04
	 */
	public function content() {
		if ( ! $this->form_id || empty( $this->fields ) ) {
			return '';
		}

		if ( $this->format === 'array' ) {
			$content = $this->get_array();
		} elseif ( $this->is_plain_text_format() ) {
			$content = $this->get_plain_text();
		} else {
			$content = $this->get_table();
		}

		return $content;
	}

	/**
	 * Return the default HTML array
	 *
	 * @since 2.04
	 *
	 * @return array
	 */
	protected function get_array() {
		foreach ( $this->fields as $field ) {
			$this->add_field_array( $field );
		}

		return $this->array_content;
	}

	/**
	 * Return the default plain text for an email message
	 *
	 * @since 2.04
	 *
	 * @return string
	 */
	protected function get_plain_text() {
		return $this->generate_content_for_all_fields();
	}

	/**
	 * Return the default HTML for an email message
	 *
	 * @since 2.04
	 *
	 * @return string
	 */
	protected function get_table() {
		$content  = $this->table_generator->generate_table_header();
		$content .= $this->generate_content_for_all_fields();
		$content .= $this->table_generator->generate_table_footer();

		return $content;
	}

	/**
	 * Generate the content for all fields
	 *
	 * @since 2.05
	 *
	 * @return string
	 */
	protected function generate_content_for_all_fields() {
		$content = '';

		foreach ( $this->fields as $field ) {
			$content .= $this->generate_field_content( $field );
		}

		return $content;
	}

	/**
	 * Generate a field's HTML or plain text shortcodes
	 *
	 * @since 2.04
	 *
	 * @param stdClass $field
	 *
	 * @return string
	 */
	protected function generate_field_content( $field ) {
		if ( in_array( $field->type, $this->skip_fields ) ) {
			return '';
		}

		$row = $this->generate_two_cell_shortcode_row( $field );

		return $row;
	}

	/**
	 * Generate a two cell row of shortcodes for an HTML or plain text table
	 *
	 * @since 2.04
	 *
	 * @param stdClass $field
	 * @param mixed    $value
	 *
	 * @return string
	 */
	protected function generate_two_cell_shortcode_row( $field, $value = null ) {
		$row = '[if ' . $field->id . ']';

		$label = '[' . $field->id . ' show=field_label]';

		if ( $value === null ) {
			$value = '[' . $field->id . ']';
		}

		if ( $this->is_plain_text_format() ) {
			$row .= $label . ': ' . $value . "\r\n";
		} else {
			$row .= $this->table_generator->generate_two_cell_table_row( $label, $value );
		}

		$row .= '[/if ' . $field->id . ']';

		if ( $this->is_table_format() ) {
			$row .= "\r\n";
		}

		return $row;
	}

	/**
	 * Generate a field's array for the default HTML array
	 *
	 * @since 2.04
	 *
	 * @param stdClass $field
	 *
	 * @return void
	 */
	protected function add_field_array( $field ) {
		if ( in_array( $field->type, $this->skip_fields ) ) {
			return;
		}

		$this->add_single_field_array( $field, $field->id );
	}

	/**
	 * Generate a single field's array
	 *
	 * @since 2.04
	 *
	 * @param stdClass $field
	 * @param string   $value
	 *
	 * @return void
	 */
	protected function add_single_field_array( $field, $value ) {
		$array = array(
			'label' => '[' . $field->id . ' show=field_label]',
			'val'   => '[' . $value . ']',
			'type'  => $field->type,
		);

		$this->array_content[ $field->id ] = apply_filters( 'frm_field_shortcodes_for_default_html_email', $array, $field );
	}

	/**
	 * Check if the format is default plain text
	 *
	 * @since 2.05
	 *
	 * @return bool
	 */
	protected function is_plain_text_format() {
		return ( $this->format === 'text' && $this->is_plain_text === true );
	}

	/**
	 * Check if the format is default HTML
	 *
	 * @since 2.05
	 *
	 * @return bool
	 */
	protected function is_table_format() {
		return ( $this->format === 'text' && $this->is_plain_text === false );
	}
}
