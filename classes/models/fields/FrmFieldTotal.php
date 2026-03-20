<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since x.x
 */
class FrmFieldTotal extends FrmFieldText {

	protected $type = 'total';

	/**
	 * @var array
	 */
	private $posted_value_args = array();

	protected $has_input = false;

	public function default_html() {
		$input = $this->input_html();

		return <<<DEFAULT_HTML
<div id="frm_field_[id]_container" class="frm_form_field form-field [required_class][error_class]">
    <div id="field_[key]_label" class="frm_primary_label">[field_name]</div>
    <p class="frm_total_formatted"></p>
    $input
    [if description]<div class="frm_description" id="frm_desc_field_[key]">[description]</div>[/if description]
    [if error]<div class="frm_error" role="alert" id="frm_error_field_[key]">[error]</div>[/if error]
</div>
DEFAULT_HTML;
	}

	protected function field_settings_for_type() {
		$settings = parent::field_settings_for_type();

		$settings['read_only']      = false;
		$settings['default']        = false;
		$settings['required']       = false;
		$settings['unique']         = false;
		$settings['size']           = false;
		$settings['clear_on_focus'] = false;
		$settings['format']         = false;
		$settings['autopopulate']   = false;
		$settings['max']            = false;

		return $settings;
	}

	protected function html5_input_type() {
		return 'hidden';
	}

	protected function include_form_builder_file() {
		return FrmAppHelper::plugin_path() . '/classes/views/frm-fields/back-end/field-total.php';
	}

	public function validate( $args ) {
		$parent_errors = parent::validate( $args );

		if ( $parent_errors ) {
			return $parent_errors;
		}

		// In order to be sure that the total is only validated after all
		// product prices and quantities have been gathered, we use a hook.
		$this->posted_value_args = $args;
		add_filter( 'frm_entries_before_create', array( $this, 'validate_total' ), 10, 2 );

		return array();
	}

	/**
	 * Check to make sure the total hasn't been tampered with.
	 *
	 * @param array          $errors All form errors.
	 * @param array|stdClass $form
	 *
	 * @return array
	 */
	public function validate_total( $errors, $form ) {
		$value = $this->posted_value_args['value'];

		global $frm_products;

		$sum      = 0.0;
		$currency = FrmCurrencyHelper::get_currency();

		// Set a decimal separator for currency if no default for it.
		// first remove unnecessary space(s).
		$currency['decimal_separator'] = trim( $currency['decimal_separator'] );

		if ( ! strlen( $currency['decimal_separator'] ) ) {
			$currency['decimal_separator'] = '.';
		}

		if ( ! isset( $frm_products ) || ! is_array( $frm_products ) ) {
			$frm_products = array();
		}

		foreach ( $frm_products as $key => $price_quantity ) {
			if ( in_array( $key, array( 'repeat_quantity_fields', 'quantity_fields' ), true ) || ! isset( $price_quantity['price'] ) ) {
				continue;
			}

			$quantity = $this->get_quantity_for_validation( $price_quantity );

			if ( ! is_array( $price_quantity['price'] ) ) {
				$price = FrmCurrencyHelper::prepare_price( $price_quantity['price'], $currency );
				$sum  += (float) $price * (float) $quantity;
				continue;
			}

			// checkboxes

			foreach ( $price_quantity['price'] as $price ) {
				$price = FrmCurrencyHelper::prepare_price( $price, $currency );
				$sum  += (float) $price * (float) $quantity;
			}
		}

		$sum = $currency['decimals'] > 0 ? round( $sum, $currency['decimals'] ) : ceil( $sum );

		/**
		 * @since x.x
		 *
		 * @param float          $sum
		 * @param array|object   $field
		 * @param array|stdClass $form
		 */
		$sum    = apply_filters( 'frm_field_total_expected_sum', $sum, $this->field, $form );
		$posted = (float) FrmCurrencyHelper::prepare_price( $value, $currency );

		if ( $posted === $sum ) {
			return $errors;
		}

		$error_key            = 'field' . $this->get_field_column( 'id' );
		$errors[ $error_key ] = FrmFieldsHelper::get_error_msg( $this->field, 'invalid' );

		return $errors;
	}

	/**
	 * @param array $price_quantity
	 *
	 * @return int
	 */
	private function get_quantity_for_validation( $price_quantity ) {
		global $frm_products;

		// If there is no quantity field, assume 1.
		$quantity = 1;

		if ( isset( $price_quantity['quantity'] ) && is_numeric( $price_quantity['quantity'] ) ) {
			$quantity = $price_quantity['quantity'];
		} elseif ( ! empty( trim( $price_quantity['parent_field_id'] ) ) ) {
			if ( ! empty( $frm_products['repeat_quantity_fields'] ) ) {
				$k        = $price_quantity['parent_field_id'] . '_' . $price_quantity['key_pointer'];
				$quantity = $frm_products['repeat_quantity_fields'][ $k ] ?? 1;
			}
		} elseif ( ! empty( $frm_products['quantity_fields'] ) && 1 === count( $frm_products['quantity_fields'] ) ) {
			$quantity = $frm_products['quantity_fields'][0];
		}

		return $quantity;
	}

	/**
	 * @param array|string $value
	 * @param array        $atts
	 *
	 * @return array|string
	 */
	protected function prepare_display_value( $value, $atts ) {
		if ( ! empty( $atts['format'] ) && 'number' === $atts['format'] ) {
			return $value;
		}

		$currency = FrmCurrencyHelper::get_currency();

		return FrmCurrencyHelper::format_price( $value, $currency );
	}

	/**
	 * @return string
	 */
	public function get_builder_display_value() {
		return $this->prepare_display_value( '0', array() );
	}
}
