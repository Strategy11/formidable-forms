<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

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
	 * @since 4.03
	 *
	 * @var object
	 */
	protected $entry;

	/**
	 * @since 2.04
	 *
	 * @var int
	 */
	protected $entry_id = 0;

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
	protected $displayed_value = 'frm_not_prepared';

	/**
	 * FrmFieldValue constructor.
	 *
	 * @param stdClass $field
	 * @param stdClass $entry
	 */
	public function __construct( $field, $entry ) {
		if ( ! is_object( $field ) || ! is_object( $entry ) || ! isset( $entry->metas ) ) {
			return;
		}

		$this->entry    = $entry;
		$this->entry_id = $entry->id;
		$field = apply_filters( 'frm_field_value_object', $field );
		$this->field    = $field;
		$this->init_saved_value( $entry );
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
		} elseif ( isset( $entry->metas[ $this->field->id ] ) ) {
			$this->saved_value = $entry->metas[ $this->field->id ];
		} else {
			$this->saved_value = '';
		}

		$this->clean_saved_value();
	}

	/**
	 * Prepare the display value
	 *
	 * @since 2.05
	 *
	 * @param array $atts
	 */
	public function prepare_displayed_value( $atts = array() ) {
		$this->displayed_value = $this->saved_value;
		unset( $atts['class'] ); // This class shouldn't affect values.
		$this->generate_displayed_value_for_field_type( $atts );
		$this->filter_displayed_value( $atts );
	}

	/**
	 * Get a value from the field settings
	 *
	 * @since 2.05.06
	 */
	public function get_field_option( $value ) {
		return FrmField::get_option( $this->field, $value );
	}

	/**
	 * @since 4.03
	 */
	public function get_field_attr( $option ) {
		return is_object( $this->field ) ? $this->field->{$option} : '';
	}

	/**
	 * @since 4.03
	 */
	public function get_field() {
		return $this->field;
	}

	/**
	 * Get the field property's label
	 *
	 * @since 2.04
	 */
	public function get_field_label() {
		return $this->get_field_attr( 'name' );
	}

	/**
	 * Get the field property's id
	 *
	 * @since 2.05
	 */
	public function get_field_id() {
		return $this->get_field_attr( 'id' );
	}

	/**
	 * Get the field property's key
	 *
	 * @since 2.04
	 */
	public function get_field_key() {
		return $this->get_field_attr( 'field_key' );
	}

	/**
	 * Get the field property's type
	 *
	 * @since 2.04
	 */
	public function get_field_type() {
		return $this->get_field_attr( 'type' );
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
		if ( $this->displayed_value === 'frm_not_prepared' ) {
			return __( 'The display value has not been prepared. Please use the prepare_display_value() method before calling get_displayed_value().', 'formidable' );
		}

		return $this->displayed_value;
	}

	/**
	 * Get the displayed value for different field types
	 *
	 * @since 3.0
	 *
	 * @param array $atts
	 *
	 * @return mixed
	 */
	protected function generate_displayed_value_for_field_type( $atts ) {
		if ( ! FrmAppHelper::is_empty_value( $this->displayed_value, '' ) ) {
			$field_obj = FrmFieldFactory::get_field_object( $this->field );

			$this->displayed_value = $field_obj->get_display_value( $this->displayed_value, $atts );
		}
	}

	/**
	 * Filter the displayed_value property
	 *
	 * @since 2.04
	 *
	 * @param array $atts
	 */
	protected function filter_displayed_value( $atts ) {
		if ( ! is_object( $this->entry ) || empty( $this->entry->metas ) ) {
			$this->entry = FrmEntry::getOne( $this->entry_id, true );
			if ( ! is_object( $this->entry ) ) {
				return;
			}
		}

		// TODO: maybe change from 'source' to 'run_filters' = 'email'
		if ( isset( $atts['source'] ) && $atts['source'] === 'entry_formatter' ) {
			// Deprecated frm_email_value hook
			$meta = array(
				'item_id'    => $this->entry->id,
				'field_id'   => $this->field->id,
				'meta_value' => $this->saved_value,
				'field_type' => $this->field->type,
			);

			if ( has_filter( 'frm_email_value' ) ) {
				_deprecated_function( 'The frm_email_value filter', '2.04', 'the frm_display_{fieldtype}_value_custom filter' );
				$this->displayed_value = apply_filters(
					'frm_email_value',
					$this->displayed_value,
					(object) $meta,
					$this->entry,
					array(
						'field' => $this->field,
					)
				);
			}
		}

		// frm_display_{fieldtype}_value_custom hook
		$this->displayed_value = apply_filters(
			'frm_display_' . $this->field->type . '_value_custom',
			$this->displayed_value,
			array(
				'field' => $this->field,
				'entry' => $this->entry,
			)
		);

		$this->displayed_value = apply_filters( 'frm_display_value', $this->displayed_value, $this->field, $atts );
	}

	/**
	 * Clean a field's saved value
	 *
	 * @since 2.04
	 */
	protected function clean_saved_value() {
		if ( $this->saved_value !== '' ) {
			if ( ! is_array( $this->saved_value ) && ! is_object( $this->saved_value ) ) {
				FrmAppHelper::unserialize_or_decode( $this->saved_value );
			}

			if ( is_array( $this->saved_value ) && empty( $this->saved_value ) ) {
				$this->saved_value = '';
			}
		}
	}
}
