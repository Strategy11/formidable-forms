<?php

/**
 * @since 2.03.11
 */
class FrmDefaultHTMLGenerator {

	/**
	 * @var int
	 * @since 2.03.11
	 */
	protected $form_id = 0;

	/**
	 * @var array
	 * @since 2.03.11
	 */
	protected $skip_fields = array( 'captcha' );

	/**
	 * @var array
	 * @since 2.03.11
	 */
	protected $fields = array();

	/**
	 * @var string
	 * @since 2.03.11
	 */
	protected $format = 'text';

	/**
	 * @var array
	 * @since 2.03.11
	 */
	protected $style_settings = array();

	/**
	 * @var string
	 * @since 2.03.11
	 */
	protected $table_style = '';

	/**
	 * @var string
	 * @since 2.03.11
	 */
	protected $tr_style = ' style="[frm-alt-color]"';


	/**
	 * @var string
	 * @since 2.03.11
	 */
	protected $td_style = '';

	/**
	 * @var array
	 * @since 2.03.11
	 */
	protected $array_content = array();

	public function __construct( $form_id, $format ) {
		if ( ! $form_id ) {
			return;
		}

		$this->set_form_id( $form_id );
		$this->set_fields();
		$this->set_format( $format );

		if ( empty( $this->fields ) ) {
			return;
		}

		if ( $this->format == 'text' ) {
			$this->set_style_settings();
			$this->set_table_style();
			$this->set_td_style();
		}
	}

	/**
	 * Set the form_id property
	 *
	 * @since 2.03.11
	 * @param $form_id
	 */
	private function set_form_id( $form_id ) {
		$this->form_id = (int) $form_id;
	}

	/**
	 * Set the fields property
	 *
	 * @since 2.03.11
	 */
	private function set_fields() {
		$this->fields = FrmField::get_all_for_form( $this->form_id, '', 'exclude', 'exclude' );
	}

	/**
	 * Set the format property
	 *
	 * @since 2.03.11
	 *
	 * @param string|mixed $format
	 */
	private function set_format( $format ) {
		if ( is_string( $format ) && $format !== '' ) {
			$this->format = $format;
		}
	}

	/**
	 * Set the style_settings property
	 *
	 * @since 2.03.11
	 */
	private function set_style_settings() {
		$this->style_settings = FrmEntryFormat::generate_style_settings();
	}

	/**
	 * Set the table_style property
	 *
	 * @since 2.03.11
	 */
	private function set_table_style() {
		$this->table_style = FrmEntryFormat::generate_table_style( $this->style_settings );
	}

	/**
	 * Set the td_style property
	 *
	 * @since 2.03.11
	 */
	private function set_td_style() {
		$this->td_style = FrmEntryFormat::generate_td_style( $this->style_settings );
	}

	/**
	 * Return the default HTML for an entry
	 *
	 * @since 2.03.11
	 */
	public function content() {
		if ( $this->format == 'array' ) {
			$content = $this->array();
		} else {
			$content = $this->text();
		}

		return $content;
	}

	/**
	 * Return the default HTML array
	 *
	 * @since 2.03.11
	 */
	private function array() {
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
	 * @since 2.03.11
	 */
	private function text() {
		if ( ! $this->form_id || empty( $this->fields ) ) {
			return '';
		}

		$content = '<table cellspacing="0"' . $this->table_style . '><tbody>' . "\r\n";;

		foreach ( $this->fields as $field ) {
			$content .= $this->generate_field_html( $field );
		}

		$content .= '</tbody></table>';

		return $content;
	}

	/**
	 * Generate a field's HTML for the default HTML
	 *
	 * @since 2.03.11
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
	 * @since 2.03.11
	 *
	 * @param stdClass $field
	 * @param null|string $value
	 *
	 * @return string
	 */
	protected function generate_single_row( $field, $value = null ) {
		$row = '[if ' . $field->id . ']';
		$row .= '<tr' . $this->tr_style . '>';

		$label = '[' . $field->id . ' show=field_label]';

		if ( $value === null ) {
			$value = '[' . $field->id . ']';
		}

		$row .= '<td' . $this->td_style . '>' . $label . '</td>';
		$row .= '<td' . $this->td_style . '>' . $value . '</td>';

		$row .= '</tr>';
		$row .= '[/if ' . $field->id . ']' . "\r\n";

		return $row;
	}

	/**
	 * Generate a field's array for the default HTML array
	 *
	 * @since 2.03.11
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
	 * @since 2.03.11
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