<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * @since 6.5 Added with Stripe Lite.
 */
class FrmCurrencyHelper {

	/**
	 * @param string $currency
	 * @return array
	 */
	public static function get_currency( $currency ) {
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
	 * Get a list of all supported currencies.
	 *
	 * @since 6.5.
	 *
	 * @return array
	 */
	public static function get_currencies() {
		$currencies = array(
			'AUD' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'Australian Dollar', 'formidable' ),
				'symbol_left'        => '$',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => ',',
			),
			'BDT' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'Bangladeshi Taka', 'formidable' ),
				'symbol_left'        => '৳',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => ',',
			),
			'BRL' => array(
				'decimals'           => 2,
				'decimal_separator'  => ',',
				'name'               => __( 'Brazilian Real', 'formidable' ),
				'symbol_left'        => 'R$',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => '.',
			),
			'CAD' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'Canadian Dollar', 'formidable' ),
				'symbol_left'        => '$',
				'symbol_padding'     => ' ',
				'symbol_right'       => 'CAD',
				'thousand_separator' => ',',
			),
			'CHF' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'Swiss Franc', 'formidable' ),
				'symbol_left'        => 'Fr.',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => "'",
			),
			'CNY' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'Chinese Renminbi Yuan', 'formidable' ),
				'symbol_left'        => '¥',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => ',',
			),
			'CZK' => array(
				'decimals'           => 2,
				'decimal_separator'  => ',',
				'name'               => __( 'Czech Koruna', 'formidable' ),
				'symbol_left'        => '',
				'symbol_padding'     => ' ',
				'symbol_right'       => '&#75;&#269;',
				'thousand_separator' => ' ',
			),
			'DKK' => array(
				'decimals'           => 2,
				'decimal_separator'  => ',',
				'name'               => __( 'Danish Krone', 'formidable' ),
				'symbol_left'        => 'Kr',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => '.',
			),
			'EUR' => array(
				'decimals'           => 2,
				'decimal_separator'  => ',',
				'name'               => __( 'Euro', 'formidable' ),
				'symbol_left'        => '',
				'symbol_padding'     => ' ',
				'symbol_right'       => '&#8364;',
				'thousand_separator' => '.',
			),
			'GBP' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'Pound Sterling', 'formidable' ),
				'symbol_left'        => '&#163;',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => ',',
			),
			'HKD' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'Hong Kong Dollar', 'formidable' ),
				'symbol_left'        => 'HK$',
				'symbol_padding'     => '',
				'symbol_right'       => '',
				'thousand_separator' => ',',
			),
			'HUF' => array(
				'decimals'           => 2,
				'decimal_separator'  => ',',
				'name'               => __( 'Hungarian Forint', 'formidable' ),
				'symbol_left'        => '',
				'symbol_padding'     => ' ',
				'symbol_right'       => 'Ft',
				'thousand_separator' => '.',
			),
			'ILS' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'Israeli New Sheqel', 'formidable' ),
				'symbol_left'        => '&#8362;',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => ',',
			),
			'INR' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'Indian Rupee', 'formidable' ),
				'symbol_left'        => '&#8377;',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => ',',
			),
			'JPY' => array(
				'decimals'           => 0,
				'decimal_separator'  => '',
				'name'               => __( 'Japanese Yen', 'formidable' ),
				'symbol_left'        => '&#165;',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => ',',
			),
			'LKR' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'Sri Lankan Rupee', 'formidable' ),
				'symbol_left'        => '₨',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => '',
			),
			'MXN' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'Mexican Peso', 'formidable' ),
				'symbol_left'        => '$',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => ',',
			),
			'MYR' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'Malaysian Ringgit', 'formidable' ),
				'symbol_left'        => '&#82;&#77;',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => ',',
			),
			'NOK' => array(
				'decimals'           => 2,
				'decimal_separator'  => ',',
				'name'               => __( 'Norwegian Krone', 'formidable' ),
				'symbol_left'        => 'Kr',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => '.',
			),
			'NZD' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'New Zealand Dollar', 'formidable' ),
				'symbol_left'        => '$',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => ',',
			),
			'PHP' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'Philippine Peso', 'formidable' ),
				'symbol_left'        => 'Php',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => ',',
			),
			'PKR' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'Pakistani Rupee', 'formidable' ),
				'symbol_left'        => '₨',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => '',
			),
			'PLN' => array(
				'decimals'           => 2,
				'decimal_separator'  => ',',
				'name'               => __( 'Polish Zloty', 'formidable' ),
				'symbol_left'        => '&#122;&#322;',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => '.',
			),
			'SEK' => array(
				'decimals'           => 2,
				'decimal_separator'  => ',',
				'name'               => __( 'Swedish Krona', 'formidable' ),
				'symbol_left'        => '',
				'symbol_padding'     => ' ',
				'symbol_right'       => 'Kr',
				'thousand_separator' => ' ',
			),
			'SGD' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'Singapore Dollar', 'formidable' ),
				'symbol_left'        => '$',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => ',',
			),
			'THB' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'Thai Baht', 'formidable' ),
				'symbol_left'        => '&#3647;',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => ',',
			),
			'TRY' => array(
				'decimals'           => 2,
				'decimal_separator'  => ',',
				'name'               => __( 'Turkish Liras', 'formidable' ),
				'symbol_left'        => '',
				'symbol_padding'     => ' ',
				'symbol_right'       => '&#8364;',
				'thousand_separator' => '.',
			),
			'TWD' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'Taiwan New Dollar', 'formidable' ),
				'symbol_left'        => '$',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => ',',
			),
			'USD' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'U.S. Dollar', 'formidable' ),
				'symbol_left'        => '$',
				'symbol_padding'     => '',
				'symbol_right'       => '',
				'thousand_separator' => ',',
			),
			'UYU' => array(
				'decimals'           => 0,
				'decimal_separator'  => ',',
				'name'               => __( 'Uruguayan Peso', 'formidable' ),
				'symbol_left'        => '$U',
				'symbol_padding'     => '',
				'symbol_right'       => '',
				'thousand_separator' => '.',
			),
			'ZAR' => array(
				'decimals'           => 2,
				'decimal_separator'  => '.',
				'name'               => __( 'South African Rand', 'formidable' ),
				'symbol_left'        => 'R',
				'symbol_padding'     => ' ',
				'symbol_right'       => '',
				'thousand_separator' => ' ',
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
			_doing_it_wrong( __FUNCTION__, 'Only arrays should be returned when using the frm_currencies filter.', '6.5' );
		}

		return $currencies;
	}
}
