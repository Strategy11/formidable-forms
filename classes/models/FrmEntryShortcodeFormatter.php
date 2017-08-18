<?php

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
	protected $skip_fields = array( 'captcha' );

	/**
	 * @var array
	 * @since 2.04
	 */
	protected $fields = array();

	/**
	 * @var string
	 * @since 2.04
	 */
	protected $format = 'text';

	/**
	 * @var FrmTableHTMLGenerator
	 * @since 2.04
	 */
	protected $table_generator = null;
	/**
	 * @var array
	 * @since 2.04
	 */
	protected $array_content = array();

	public function __construct( $form_id, $format ) {
		if ( ! $form_id ) {
			return;
		}

		$this->init_form_id( $form_id );
		$this->init_fields();
		$this->init_format( $format );

		if ( empty( $this->fields ) ) {
			return;
		}

		if ( $this->format == 'text' ) {
			$this->init_table_generator();
		}
	}

	/**
	 * Set the form_id property
	 *
	 * @since 2.04
	 * @param $form_id
	 */
	private function init_form_id( $form_id ) {
		$this->form_id = (int) $form_id;
	}

	/**
	 * Set the fields property
	 *
	 * @since 2.04
	 */
	private function init_fields() {
		$this->fields = FrmField::get_all_for_form( $this->form_id, '', 'exclude', 'exclude' );
	}

	/**
	 * Set the format property
	 *
	 * @since 2.04
	 *
	 * @param string|mixed $format
	 */
	private function init_format( $format ) {
		if ( is_string( $format ) && $format !== '' ) {
			$this->format = $format;
		}
	}

	/**
	 * Set the table_generator property
	 *
	 * @since 2.04
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
		if ( $this->form_id === 0 ) {
			$content = '';
		} else if ( $this->format == 'array' ) {
			$content = $this->get_array();
		} else {
			$content = $this->text();
		}

		return $content;
	}

	/**
	 * Return the default HTML array
	 *
	 * @since 2.04
	 */
	private function get_array() {
		if ( ! $this->form_id || empty( $this->fields ) ) {
			return '';
		}

		foreach ( $this->fields as $field ) {
			$this->add_field_array( $field );
		}

		return $this->array_content;
	}

	/**
	 * Return the default HTML for an email message
	 *
	 * @since 2.04
	 */
	private function text() {
		if ( ! $this->form_id || empty( $this->fields ) ) {
			return '';
		}

		$content = $this->table_generator->generate_table_header();

		foreach ( $this->fields as $field ) {
			$content .= $this->generate_field_html( $field );
		}

		$content .= $this->table_generator->generate_table_footer();

		return $content;
	}

	/**
	 * Generate a field's HTML for the default HTML
	 *
	 * @since 2.04
	 *
	 * @param stdClass $field
	 *
	 * @return string
	 */
	protected function generate_field_html( $field ) {
		if ( in_array( $field->type, $this->skip_fields ) ) {
			return '';
		}

		$row = $this->generate_single_row( $field );

		return $row;
	}

	/**
	 * Generate a single table row
	 *
	 * @since 2.04
	 *
	 * @param stdClass $field
	 * @param null|string $value
	 *
	 * @return string
	 */
	protected function generate_single_row( $field, $value = null ) {
		return $this->table_generator->generate_two_cell_shortcode_row( $field, $value );
	}

	/**
	 * Generate a field's array for the default HTML array
	 *
	 * @since 2.04
	 *
	 * @param stdClass $field
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
	 * @param string $value
	 *
	 * @return array
	 */
	protected function add_single_field_array( $field, $value ) {
		$array = array(
			'label' => '[' . $field->id . ' show=field_label]',
			'val'   => '[' . $value . ']',
			'type'  => $field->type,
		);

		$this->array_content[ $field->id ] = apply_filters( 'frm_field_shortcodes_for_default_html_email', $array, $field );

		return $array;
	}

}