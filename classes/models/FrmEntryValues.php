<?php

/**
 * @since 2.04
 */
class FrmEntryValues {

	/**
	 * @var stdClass
	 */
	protected $entry = null;

	/**
	 * @var int
	 */
	protected $form_id;

	/**
	 * @var array
	 */
	protected $fields = array();

	/**
	 * @var FrmFieldValue[]
	 */
	protected $field_values = array();

	/**
	 * @var array
	 */
	protected $user_info = array();

	/**
	 * @var array
	 */
	protected $include_fields = array();

	/**
	 * @var array
	 */
	protected $exclude_fields = array();

	/**
	 * @since 2.04
	 *
	 * @var string
	 */
	protected $source = '';

	/**
	 * FrmEntryValues constructor
	 *
	 * @since 2.04
	 *
	 * @param int|string $entry_id
	 * @param array $atts
	 */
	public function __construct( $entry_id, $atts = array() ) {
		$this->init_entry( $entry_id );

		if ( $this->entry === null || $this->entry === false ) {
			return;
		}

		$this->init_form_id();
		$this->init_source( $atts );
		$this->init_include_fields( $atts );
		$this->init_exclude_fields( $atts );
		$this->init_fields();
		$this->init_field_values();
		$this->init_user_info();
	}

	/**
	 * Set the entry property
	 *
	 * @since 2.04
	 *
	 * @param int|string $entry_id
	 */
	protected function init_entry( $entry_id ) {
		$this->entry = FrmEntry::getOne( $entry_id, true );
	}

	/**
	 * Set the form_id property
	 *
	 * @since 2.04
	 */
	protected function init_form_id() {
		$this->form_id = (int) $this->entry->form_id;
	}

	/**
	 * Set the include_fields property
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 */
	protected function init_include_fields( $atts ) {

		// For reverse compatibility with the fields parameter
		if ( ! isset( $atts['include_fields'] ) || empty( $atts['include_fields'] ) ) {

			if ( isset( $atts['fields'] ) && ! empty( $atts['fields'] ) ) {

				$atts['include_fields'] = '';
				foreach ( $atts['fields'] as $included_field ) {
					$atts['include_fields'] .= $included_field->id . ',';
				}

				$atts['include_fields'] = rtrim( $atts['include_fields'], ',' );
			}
		}

		$this->include_fields = $this->prepare_array_property( 'include_fields', $atts );
	}

	/**
	 * Set the exclude_fields property
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 */
	protected function init_exclude_fields( $atts ) {
		$this->exclude_fields = $this->prepare_array_property( 'exclude_fields', $atts );
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
	 * Prepare an array property value, such as include_fields and exclude_fields
	 *
	 * @since 2.04
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
	 * @since 2.04
	 */
	protected function init_fields() {
		$this->fields = FrmField::get_all_for_form( $this->form_id, '', 'exclude', 'exclude' );
	}

	/**
	 * Set the field_values property
	 *
	 * @since 2.04
	 */
	protected function init_field_values() {
		foreach ( $this->fields as $field ) {
			if ( $this->is_field_included( $field ) ) {
				$this->add_field_values( $field );
			}
		}
	}

	/**
	 * Get the field_values property
	 *
	 * @since 2.04
	 *
	 * @return array
	 */
	public function get_field_values() {
		return $this->field_values;
	}

	/**
	 * Set the user_info property
	 *
	 * @since 2.04
	 */
	protected function init_user_info() {
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
			'value' => $this->entry->ip,
		);

		$browser = array(
			'label' => __( 'User-Agent (Browser/OS)', 'formidable' ),
			'value'   => FrmEntriesHelper::get_browser( $entry_description['browser'] ),
		);

		$referrer = array(
			'label' => __( 'Referrer', 'formidable' ),
			'value' => $entry_description['referrer'],
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
	 * @since 2.04
	 *
	 * @return array
	 */
	public function get_user_info() {
		return $this->user_info;
	}

	/**
	 * Check if a field should be included in the values
	 *
	 * @since 2.04
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
	 * @since 2.04
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
	 * @since 2.04
	 *
	 * @param stdClass $field
	 */
	protected function add_field_values( $field ) {
		$this->field_values[ $field->id ] = new FrmFieldValue( $field, $this->entry, array( 'source' => $this->source ) );
	}
}