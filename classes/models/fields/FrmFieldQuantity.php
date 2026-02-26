<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 4.04
 */
class FrmProFieldQuantity extends FrmProFieldNumber {

	protected $type = 'quantity';

	protected function field_settings_for_type() {
		$settings           = parent::field_settings_for_type();
		$settings['unique'] = false;
		$settings['format'] = false;

		return $settings;
	}

	protected function extra_field_opts() {
		return array_merge(
			parent::extra_field_opts(),
			array(
				'product_field' => array(),
				'step'          => '1',
			)
		);
	}

	protected function new_field_settings() {
		return array(
			'default_value' => 1,
		);
	}

	/**
	 * @param array $args - Includes 'field', 'display', and 'values'
	 */
	public function show_primary_options( $args ) {
		$field = $args['field'];
		// Cast to array cos of existing fields that are already using single product fields in production
		$field['product_field'] = $field['product_field'] ? (array) $field['product_field'] : array();
		include FrmProAppHelper::plugin_path() . '/classes/views/frmpro-fields/back-end/quantity-options.php';

		parent::show_primary_options( $args );
	}

	protected function html5_input_type() {
		return 'number';
	}

	public function validate( $args ) {
		global $frm_products;
		$parent_errors = parent::validate( $args );

		if ( $parent_errors ) {
			return $parent_errors;
		}

		if ( ! $frm_products ) {
			$frm_products = array();
		}

		$value = trim( $args['value'] );
		$value = is_numeric( $value ) ? $value : 0;

		$product_fields = FrmField::get_option( $this->field, 'product_field' );

		if ( $product_fields ) {
			// Cast to array cos of existing fields that are already using single product fields in production
			$product_fields = array_map( 'trim', (array) $product_fields );

			foreach ( $product_fields as $product_field ) {
				$product_field_key = $product_field . '_' . $args['parent_field_id'] . '_' . $args['key_pointer'];

				if ( ! isset( $frm_products[ $product_field_key ] ) || ! is_array( $frm_products[ $product_field_key ] ) ) {
					$frm_products[ $product_field_key ] = array();
				}
				$frm_products[ $product_field_key ]['quantity'] = $value;
			}
		} elseif ( ! empty( $args['parent_field_id'] ) ) {
			// Get it ready for the corresponding product field in the row that will use it to calc total
			if ( ! isset( $frm_products['repeat_quantity_fields'] ) || ! is_array( $frm_products['repeat_quantity_fields'] ) ) {
				$frm_products['repeat_quantity_fields'] = array();
			}
			$frm_products['repeat_quantity_fields'][ $args['parent_field_id'] . '_' . $args['key_pointer'] ] = $value;
		} else {
			// This quantity field is in the form but not in a repeater
			if ( ! isset( $frm_products['quantity_fields'] ) || ! is_array( $frm_products['quantity_fields'] ) ) {
				$frm_products['quantity_fields'] = array();
			}
			$frm_products['quantity_fields'][] = $value;
		}

		return array();
	}
}
