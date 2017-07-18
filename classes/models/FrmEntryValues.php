<?php

/**
 * @since 2.03.11
 */
class FrmEntryValues {

	/**
	 * @var stdClass
	 */
	protected $entry = null;
	protected $form_id;

	/**
	 * @var array
	 * TODO: create class for individual fields instead of using array
	 */
	protected $fields = array();
	protected $field_values = array();
	protected $user_info = array();
	protected $include_fields = array();
	protected $exclude_fields = array();

	/**
	 * FrmEntryValues constructor
	 *
	 * @since 2.03.11
	 *
	 * @param int|string $entry_id
	 * @param array $atts
	 */
	public function __construct( $entry_id, $atts ) {
		$this->set_entry( $entry_id );

		if ( $this->entry === null || $this->entry === false ) {
			return;
		}

		$this->set_form_id();
		$this->set_include_fields( $atts );
		$this->set_exclude_fields( $atts );
		$this->set_fields();
		$this->set_field_values();
		$this->set_user_info();
	}

	/**
	 * Set the entry property
	 *
	 * @since 2.03.11
	 *
	 * @param int|string $entry_id
	 */
	protected function set_entry( $entry_id ) {
		$this->entry = FrmEntry::getOne( $entry_id, true );
	}

	/**
	 * Set the form_id property
	 *
	 * @since 2.03.11
	 */
	protected function set_form_id() {
		$this->form_id = $this->entry->form_id;
	}

	/**
	 * Set the include_fields property
	 *
	 * @since 2.03.11
	 *
	 * @param array $atts
	 */
	protected function set_include_fields( $atts ) {
		$this->include_fields = $this->prepare_array_property( 'include_fields', $atts );
	}

	/**
	 * Set the exclude_fields property
	 *
	 * @since 2.03.11
	 *
	 * @param array $atts
	 */
	protected function set_exclude_fields( $atts ) {
		$this->exclude_fields = $this->prepare_array_property( 'exclude_fields', $atts );
	}

	/**
	 * Prepare an array property value, such as include_fields and exclude_fields
	 *
	 * @since 2.03.11
	 *
	 * @param string $index
	 * @param array $atts
	 *
	 * @return array
	 */
	private function prepare_array_property( $index, $atts ) {
		if ( isset( $atts[ $index ] ) && ! empty( $atts[ $index ] ) ) {

			if ( is_array( $atts[ $index ] ) ) {
				$property = $atts[ $index ];
			} else {
				$property = explode( ',', $atts[ $index ] );
			}

		} else {
			$property = array();
		}

		return $property;
	}

	/**
	 * Set the fields property
	 *
	 * @since 2.03.11
	 */
	protected function set_fields() {
		$this->fields = FrmField::get_all_for_form( $this->form_id, '', 'exclude', 'exclude' );
	}

	/**
	 * Set the field_values property
	 *
	 * @since 2.03.11
	 */
	protected function set_field_values() {
		foreach ( $this->fields as $field ) {
			if ( $this->is_field_included( $field ) ) {
				$this->add_field_values( $field );
			}
		}
	}

	/**
	 * Get the field_values property
	 *
	 * @since 2.03.11
	 *
	 * @return array
	 */
	public function get_field_values() {
		return $this->field_values;
	}

	/**
	 * Set the user_info property
	 *
	 * @since 2.03.11
	 */
	protected function set_user_info() {
		if ( isset( $this->entry->description ) ) {
			$entry_description = (array) maybe_unserialize( $this->entry->description );
		} else {
			$entry_description = array(
				'browser' => '',
				'referrer' => '',
			);
		}

		$ip = array(
			'label' => __( 'IP Address', 'formidable' ),
			'value' => $this->entry->ip
		);

		$browser = array(
			'label' => __( 'User-Agent (Browser/OS)', 'formidable' ),
			'value'   => FrmEntryFormat::get_browser( $entry_description['browser'] ),
		);

		$referrer = array(
			'label' => __( 'Referrer', 'formidable' ),
			'value' => $entry_description['referrer']
		);

		$this->user_info = array(
			'ip' => $ip,
			'browser' => $browser,
			'referrer' => $referrer,
		);
	}

	/**
	 * Get the user_info property
	 *
	 * @since 2.03.11
	 *
	 * @return array
	 */
	public function get_user_info() {
		return $this->user_info;
	}

	/**
	 * Check if a field should be included in the values
	 *
	 * @since 2.03.11
	 *
	 * @param stdClass $field
	 *
	 * @return bool
	 */
	protected function is_field_included( $field ) {
		if ( ! empty( $this->include_fields ) ) {
			$is_included = $this->is_field_in_array( $field, $this->include_fields );
		} else if ( ! empty( $this->exclude_fields ) ) {
			$is_included = ! $this->is_field_in_array( $field, $this->include_fields );
		} else {
			$is_included = true;
		}

		return $is_included;
	}

	/**
	 * Check if a field is in the include fields or exclude fields array
	 *
	 * @since 2.03.11
	 *
	 * @param stdClass $field
	 * @param array $array
	 *
	 * @return bool
	 */
	protected function is_field_in_array( $field, $array ) {
		return in_array( $field->id, $array ) || in_array( $field->field_key, $array );
	}

	/**
	 * Add a field's values to the field_values property
	 *
	 * @since 2.03.11
	 *
	 * @param stdClass $field
	 */
	protected function add_field_values( $field ) {
		$saved_value = $this->get_field_saved_value( $field );

		$displayed_value = $this->get_field_displayed_value( $field, $saved_value );

		$this->field_values[ $field->id ] = array(
			'label' => $field->name,
			'field_key' => $field->field_key,
			'type' => $field->type,
			'has_child_entries' => false,
			'is_empty_container' => false,
			'saved_value' => $saved_value,
			'displayed_value' => $displayed_value,
		);
	}

	/**
	 * Get a field's displayed value
	 *
	 * @since 2.03.11
	 *
	 * @param stdClass $field
	 * @param mixed $saved_value
	 *
	 * @return mixed|string|void
	 */
	protected function get_field_displayed_value( $field, $saved_value ) {
		$displayed_value = $saved_value;

		// Deprecated frm_email_value hook
		$meta = array(
			'item_id' => $this->entry->id,
			'field_id' => $field->id,
			'meta_value' => $saved_value,
			'field_type' => $field->type
		);
		$displayed_value = apply_filters( 'frm_email_value', $displayed_value, (object) $meta, $this->entry, array(
			'field'  => $field,
		) );
		if ( has_filter( 'frm_email_value' ) ) {
			_deprecated_function( 'The frm_email_value filter', '2.03.11', 'the frm_display_{fieldtype}_value_custom filter' );
		}

		// frm_display_{fieldtype}_value_custom hook
		$displayed_value = apply_filters( 'frm_display_' . $field->type . '_value_custom', $displayed_value, array(
			'field' => $field,
		) );

		return $displayed_value;
	}

	/**
	 * Get a field's saved value
	 *
	 * @since 2.03.11
	 *
	 * @param stdClass $field
	 *
	 * @return mixed|string
	 */
	protected function get_field_saved_value( $field ) {
		if ( isset( $this->entry->metas[ $field->id ] ) ) {
			$saved_value = $this->entry->metas[ $field->id ];
		} else {
			$saved_value = '';
		}

		return $this->clean_saved_value( $saved_value );
	}

	/**
	 * Clean a field's saved value
	 *
	 * @since 2.03.11
	 *
	 * @param mixed $saved_value
	 *
	 * @return mixed|string
	 */
	protected function clean_saved_value( $saved_value ) {
		if ( $saved_value !== '' ) {

			$saved_value = maybe_unserialize( $saved_value );

			if ( is_array( $saved_value ) && empty( $saved_value ) ) {
				$saved_value = '';
			}
		}

		return $saved_value;
	}
}