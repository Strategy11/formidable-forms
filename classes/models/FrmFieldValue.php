<?php

/**
 * @since 2.04
 */
class FrmFieldValue {

	/**
	 * @since 2.04
	 *
	 * @var stdClass
	 */
	protected $field = null;

	/**
	 * @since 2.04
	 *
	 * @var int
	 */
	protected $entry_id = 0;

	/**
	 * @since 2.04
	 *
	 * @var string
	 */
	protected $source = '';

	/**
	 * @since 2.04
	 *
	 * @var mixed
	 */
	protected $saved_value = '';

	/**
	 * @since 2.04
	 *
	 * @var mixed
	 */
	protected $displayed_value = '';

	/**
	 * FrmFieldValue constructor.
	 *
	 * @param stdClass $field
	 * @param stdClass $entry
	 * @param array $atts
	 */
	public function __construct( $field, $entry, $atts = array() ) {
		if ( ! is_object( $field ) || ! is_object( $entry ) || ! isset( $entry->metas ) ) {
			return;
		}

		$this->entry_id = $entry->id;
		$this->field = $field;
		$this->init_source( $atts );
		$this->init_saved_value( $entry );
		$this->init_displayed_value( $entry );
	}

	/**
	 * Initialize the source property
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 */
	protected function init_source( $atts ) {
		if ( isset( $atts['source'] ) && is_string( $atts['source'] ) && $atts['source'] !== '' ) {
			$this->source = (string) $atts['source'];
		} else {
			$this->source = 'general';
		}
	}

	/**
	 * Initialize the saved_value property
	 *
	 * @since 2.04
	 *
	 * @param stdClass $entry
	 */
	protected function init_saved_value( $entry ) {
		if ( $this->field->type === 'html' ) {
			$this->saved_value = $this->field->description;
		} else if ( isset( $entry->metas[ $this->field->id ] ) ) {
			$this->saved_value = $entry->metas[ $this->field->id ];
		} else {
			$this->saved_value = '';
		}

		$this->clean_saved_value();
	}

	/**
	 * Initialize a field's displayed value
	 *
	 * @since 2.04
	 *
	 * @param stdClass $entry
	 */
	protected function init_displayed_value( $entry ) {
		$this->displayed_value = $this->saved_value;

		$this->generate_displayed_value_for_field_type();
		$this->filter_displayed_value( $entry );
	}

	/**
	 * Get the field property's label
	 *
	 * @since 2.04
	 */
	public function get_field_label() {
		return $this->field->name;
	}

	/**
	 * Get the field property's key
	 *
	 * @since 2.04
	 */
	public function get_field_key() {
		return $this->field->field_key;
	}

	/**
	 * Get the field property's type
	 *
	 * @since 2.04
	 */
	public function get_field_type() {
		return $this->field->type;
	}

	/**
	 * Get the saved_value property
	 *
	 * @since 2.04
	 */
	public function get_saved_value() {
		return $this->saved_value;
	}

	/**
	 * Get the displayed_value property
	 *
	 * @since 2.04
	 */
	public function get_displayed_value() {
		return $this->displayed_value;
	}

	/**
	 * Get the displayed value for different field types
	 *
	 * @since 3.0
	 *
	 * @return mixed
	 */
	protected function generate_displayed_value_for_field_type() {
		$field_obj = FrmFieldFactory::get_field_object( $this->field );
		$this->displayed_value = $field_obj->get_display_value( $this->displayed_value );
	}

	/**
	 * Filter the displayed_value property
	 *
	 * @since 2.04
	 *
	 * @param stdClass $entry
	 */
	protected function filter_displayed_value( $entry ) {

		if ( $this->source === 'entry_formatter' ) {
			// Deprecated frm_email_value hook
			$meta                  = array(
				'item_id'    => $entry->id,
				'field_id'   => $this->field->id,
				'meta_value' => $this->saved_value,
				'field_type' => $this->field->type,
			);
			$this->displayed_value = apply_filters( 'frm_email_value', $this->displayed_value, (object) $meta, $entry, array(
				'field' => $this->field,
			) );
			if ( has_filter( 'frm_email_value' ) ) {
				_deprecated_function( 'The frm_email_value filter', '2.04', 'the frm_display_{fieldtype}_value_custom filter' );
			}
		}

		// frm_display_{fieldtype}_value_custom hook
		$this->displayed_value = apply_filters( 'frm_display_' . $this->field->type . '_value_custom', $this->displayed_value, array(
			'field' => $this->field, 'entry' => $entry,
		) );
	}

	/**
	 * Clean a field's saved value
	 *
	 * @since 2.04
	 */
	protected function clean_saved_value() {
		if ( $this->saved_value !== '' ) {

			$this->saved_value = maybe_unserialize( $this->saved_value );

			if ( is_array( $this->saved_value ) && empty( $this->saved_value ) ) {
				$this->saved_value = '';
			}
		}
	}
}
