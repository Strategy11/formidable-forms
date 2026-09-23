<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.5 Added with Stripe Lite.
 */
class FrmCurrencyHelper {

	/**
	 * Gets the currency data from the currency code.
	 *
	 * @since 6.30 The first parameter is optional.
	 *
	 * @param string|null $currency Currency code. Default is `null`, which use the currency in the global settings.
	 *
	 * @return array
	 */
	public static function get_currency( $currency = null ) {
		if ( ! $currency ) {
			$currency = trim( FrmAppHelper::get_settings()->currency );
		}

		if ( ! $currency ) {
			$currency = 'USD';
		}

		$currency   = strtoupper( $currency );
		$currencies = self::get_currencies();

		if ( isset( $currencies[ $currency ] ) ) {
			$currency = $currencies[ $currency ];
		} elseif ( isset( $currencies[ strtolower( $currency ) ] ) ) {
			$currency = $currencies[ strtolower( $currency ) ];
		} else {
			$currency = $currencies['USD'];
		}

		return $currency;
	}

	/**
	 * Checks if the given format is a valid currency format.
	 *
	 * @since 6.18
	 *
	 * @param string $format_value The format value to check.
	 *
	 * @return bool
	 */
	public static function is_currency_format( $format_value ) {
		return in_array( $format_value, array( 'currency', 'number' ), true );
	}

	/**
	 * Adds the currency symbol to the given amount.
	 *
	 * @param float|string $amount
	 * @param array|null   $currency
	 *
	 * @return string
	 */
	public static function format_price( $amount, $currency = null ) {
		if ( ! $currency ) {
			$currency = self::get_currency();
		}

		if ( is_string( $amount ) ) {
			$amount = floatval( self::prepare_price( $amount, $currency ) );
		}

		$amount = number_format( $amount, $currency['decimals'], $currency['decimal_separator'], $currency['thousand_separator'] );

		if ( '' !== $currency['symbol_left'] ) {
			$amount = $currency['symbol_left'] . $currency['symbol_padding'] . $amount;
		}

		if ( '' !== $currency['symbol_right'] ) {
			$amount .= $currency['symbol_padding'] . $currency['symbol_right'];
		}

		return $amount;
	}

	/**
	 * @since 6.30
	 *
	 * @param string $price
	 * @param array  $currency
	 *
	 * @return float|string
	 */
	public static function prepare_price( $price, $currency ) {
		$price = trim( $price );

		if ( ! $price ) {
			return 0;
		}

		preg_match_all( '/[\-]*[0-9,.]*\.?\,?[0-9]+/', $price, $matches );
		$price = $matches ? end( $matches[0] ) : 0;

		if ( ! $price ) {
			return 0;
		}

		$separators        = array( '.', ',' );
		$decimal_position  = self::find_decimal_position( $price, $currency );

		if ( false === $decimal_position ) {
			return str_replace( $separators, '', $price );
		}

		$integer_part    = str_replace( $separators, '', substr( $price, 0, $decimal_position ) );
		$fractional_part = str_replace( $separators, '', substr( $price, $decimal_position + 1 ) );

		return $integer_part . '.' . $fractional_part;
	}

	/**
	 * Find the position of the price's real decimal separator, or false if it has none (a
	 * whole-number amount, possibly with thousands grouping).
	 *
	 * A user can type an amount in a different locale's format than the form's configured
	 * currency expects (e.g. US-style "1,030.21" on a form whose currency configures '.' as
	 * the thousand separator). Trusting the currency's configured separator in that case --
	 * and blindly replacing every occurrence of it -- is what let this silently truncate:
	 * when both '.' and ',' appear, or the same character repeats, only the rightmost
	 * occurrence is ever the real decimal point; everything else is grouping noise.
	 *
	 * @since 6.35
	 *
	 * @param string $amount
	 * @param array  $currency
	 *
	 * @return false|int
	 */
	private static function find_decimal_position( $amount, $currency ) {
		$last_dot   = strrpos( $amount, '.' );
		$last_comma = strrpos( $amount, ',' );

		if ( false !== $last_dot && false !== $last_comma ) {
			return max( $last_dot, $last_comma );
		}

		if ( false === $last_dot && false === $last_comma ) {
			return false;
		}

		$present  = false !== $last_dot ? '.' : ',';
		$position = false !== $last_dot ? $last_dot : $last_comma;

		if ( $present === $currency['decimal_separator'] ) {
			return $position;
		}

		// The lone separator matches the currency's thousand separator instead. A dot in a
		// comma-decimal currency is ambiguous -- a single occurrence with a 1-2 digit tail
		// still reads as a decimal point even though the currency expects '.' as its thousand
		// separator. A comma in a dot-decimal currency is never ambiguous this way: it's
		// always thousands grouping (e.g. "1,23" on a GBP form is 123, not 1.23).
		if ( '.' !== $present || 1 !== substr_count( $amount, $present ) ) {
			return false;
		}

		$tail_length = strlen( $amount ) - $position - 1;

		return in_array( $tail_length, array( 1, 2 ), true ) ? $position : false;
	}

	/**
	 * If the currency is needed for this form, add it to the global.
	 * This is later included in the footer.
	 *
	 * @since 6.30
	 *
	 * @param int|string $form_id Form ID. This is used for Pro compatibility.
	 *
	 * @return void
	 */
	public static function add_currency_to_global( $form_id ) {
		global $frm_vars;

		if ( ! isset( $frm_vars['currency'] ) ) {
			$frm_vars['currency'] = array();
		}

		if ( ! isset( $frm_vars['currency'][ $form_id ] ) ) {
			$frm_vars['currency'][ $form_id ] = self::get_currency();
		}
	}

	/**
	 * Get a list of all supported currencies.
	 *
	 * @since 6.5.
	 *
	 * @return array
	 */
	public static function get_currencies() {
		$currencies = array(
			'AUD' => array(
				'name'               => __( 'Australian Dollar', 'formidable' ),
				'symbol_left'        => '$',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'BDT' => array(
				'name'               => __( 'Bangladeshi Taka', 'formidable' ),
				'symbol_left'        => '৳',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'BRL' => array(
				'name'               => __( 'Brazilian Real', 'formidable' ),
				'symbol_left'        => 'R$',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => '.',
				'decimal_separator'  => ',',
				'decimals'           => 2,
			),
			'CAD' => array(
				'name'               => __( 'Canadian Dollar', 'formidable' ),
				'symbol_left'        => '$',
				'symbol_right'       => 'CAD',
				'symbol_padding'     => ' ',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'CNY' => array(
				'name'               => __( 'Chinese Renminbi Yuan', 'formidable' ),
				'symbol_left'        => '¥',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'CZK' => array(
				'name'               => __( 'Czech Koruna', 'formidable' ),
				'symbol_left'        => '',
				'symbol_right'       => '&#75;&#269;',
				'symbol_padding'     => ' ',
				'thousand_separator' => ' ',
				'decimal_separator'  => ',',
				'decimals'           => 2,
			),
			'DKK' => array(
				'name'               => __( 'Danish Krone', 'formidable' ),
				'symbol_left'        => 'Kr',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => '.',
				'decimal_separator'  => ',',
				'decimals'           => 2,
			),
			'EUR' => array(
				'name'               => __( 'Euro', 'formidable' ),
				'symbol_left'        => '',
				'symbol_right'       => '&#8364;',
				'symbol_padding'     => ' ',
				'thousand_separator' => '.',
				'decimal_separator'  => ',',
				'decimals'           => 2,
			),
			'HKD' => array(
				'name'               => __( 'Hong Kong Dollar', 'formidable' ),
				'symbol_left'        => 'HK$',
				'symbol_right'       => '',
				'symbol_padding'     => '',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'HUF' => array(
				'name'               => __( 'Hungarian Forint', 'formidable' ),
				'symbol_left'        => '',
				'symbol_right'       => 'Ft',
				'symbol_padding'     => ' ',
				'thousand_separator' => '.',
				'decimal_separator'  => ',',
				'decimals'           => 2,
			),
			'INR' => array(
				'name'               => __( 'Indian Rupee', 'formidable' ),
				'symbol_left'        => '&#8377;',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'ILS' => array(
				'name'               => __( 'Israeli New Sheqel', 'formidable' ),
				'symbol_left'        => '&#8362;',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'JPY' => array(
				'name'               => __( 'Japanese Yen', 'formidable' ),
				'symbol_left'        => '&#165;',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => ',',
				'decimal_separator'  => '',
				'decimals'           => 0,
			),
			'MYR' => array(
				'name'               => __( 'Malaysian Ringgit', 'formidable' ),
				'symbol_left'        => '&#82;&#77;',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'MXN' => array(
				'name'               => __( 'Mexican Peso', 'formidable' ),
				'symbol_left'        => '$',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'NOK' => array(
				'name'               => __( 'Norwegian Krone', 'formidable' ),
				'symbol_left'        => 'Kr',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => '.',
				'decimal_separator'  => ',',
				'decimals'           => 2,
			),
			'NZD' => array(
				'name'               => __( 'New Zealand Dollar', 'formidable' ),
				'symbol_left'        => '$',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'PKR' => array(
				'name'               => __( 'Pakistani Rupee', 'formidable' ),
				'symbol_left'        => '₨',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => '',
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'PHP' => array(
				'name'               => __( 'Philippine Peso', 'formidable' ),
				'symbol_left'        => 'Php',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'PLN' => array(
				'name'               => __( 'Polish Zloty', 'formidable' ),
				'symbol_left'        => '&#122;&#322;',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => '.',
				'decimal_separator'  => ',',
				'decimals'           => 2,
			),
			'GBP' => array(
				'name'               => __( 'Pound Sterling', 'formidable' ),
				'symbol_left'        => '&#163;',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'SGD' => array(
				'name'               => __( 'Singapore Dollar', 'formidable' ),
				'symbol_left'        => '$',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'ZAR' => array(
				'name'               => __( 'South African Rand', 'formidable' ),
				'symbol_left'        => 'R',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => ' ',
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'LKR' => array(
				'name'               => __( 'Sri Lankan Rupee', 'formidable' ),
				'symbol_left'        => '₨',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => '',
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'SEK' => array(
				'name'               => __( 'Swedish Krona', 'formidable' ),
				'symbol_left'        => '',
				'symbol_right'       => 'Kr',
				'symbol_padding'     => ' ',
				'thousand_separator' => ' ',
				'decimal_separator'  => ',',
				'decimals'           => 2,
			),
			'CHF' => array(
				'name'               => __( 'Swiss Franc', 'formidable' ),
				'symbol_left'        => 'Fr.',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => "'",
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'TWD' => array(
				'name'               => __( 'Taiwan New Dollar', 'formidable' ),
				'symbol_left'        => '$',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'THB' => array(
				'name'               => __( 'Thai Baht', 'formidable' ),
				'symbol_left'        => '&#3647;',
				'symbol_right'       => '',
				'symbol_padding'     => ' ',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'TRY' => array(
				'name'               => __( 'Turkish Liras', 'formidable' ),
				'symbol_left'        => '',
				'symbol_right'       => '&#8378;',
				'symbol_padding'     => ' ',
				'thousand_separator' => '.',
				'decimal_separator'  => ',',
				'decimals'           => 2,
			),
			'USD' => array(
				'name'               => __( 'U.S. Dollar', 'formidable' ),
				'symbol_left'        => '$',
				'symbol_right'       => '',
				'symbol_padding'     => '',
				'thousand_separator' => ',',
				'decimal_separator'  => '.',
				'decimals'           => 2,
			),
			'UYU' => array(
				'name'               => __( 'Uruguayan Peso', 'formidable' ),
				'symbol_left'        => '$U',
				'symbol_right'       => '',
				'symbol_padding'     => '',
				'thousand_separator' => '.',
				'decimal_separator'  => ',',
				'decimals'           => 0,
			),
		);

		/**
		 * @since 6.5
		 *
		 * @param array $currencies
		 */
		$filtered_currencies = apply_filters( 'frm_currencies', $currencies );

		if ( is_array( $filtered_currencies ) ) {
			$currencies = $filtered_currencies;
		} else {
			_doing_it_wrong( __METHOD__, 'Only arrays should be returned when using the frm_currencies filter.', '6.5' );
		}

		return $currencies;
	}
}
