<?php

class test_FrmCurrencyHelper extends FrmUnitTest {

	/**
	 * @covers FrmCurrencyHelper::get_currency
	 */
	public function test_get_currency() {
		// Do a lower case check.
		$usd = FrmCurrencyHelper::get_currency( 'usd' );
		$this->assert_currency( $usd );
		$this->assert_usd( $usd );

		// Do an upper case check.
		$usd = FrmCurrencyHelper::get_currency( 'USD' );
		$this->assert_currency( $usd );
		$this->assert_usd( $usd );

		// Test another currency in lower case (euros).
		$euro = FrmCurrencyHelper::get_currency( 'eur' );
		$this->assert_currency( $euro );
		$this->assert_euro( $euro );

		// Test another currency in caps (canadian).
		$cad = FrmCurrencyHelper::get_currency( 'CAD' );
		$this->assert_currency( $cad );
		$this->assert_cad( $cad );
	}

	/**
	 * @param array $currency
	 */
	private function assert_currency( $currency ) {
		$this->assertIsArray( $currency );
		$this->assertIsString( $currency['name'] );
	}

	/**
	 * @param array $currency
	 *
	 * @return void
	 */
	private function assert_usd( $currency ) {
		$this->assertSame( 'U.S. Dollar', $currency['name'] );
		$this->assertSame( '$', $currency['symbol_left'] );
		$this->assertSame( '.', $currency['decimal_separator'] );
	}

	/**
	 * @param array $currency
	 *
	 * @return void
	 */
	private function assert_euro( $currency ) {
		$this->assertSame( 'Euro', $currency['name'] );
		$this->assertSame( '&#8364;', $currency['symbol_right'] );
		$this->assertSame( ',', $currency['decimal_separator'] );
	}

	/**
	 * @param array $currency
	 *
	 * @return void
	 */
	private function assert_cad( $currency ) {
		$this->assertSame( 'Canadian Dollar', $currency['name'] );
		$this->assertSame( '$', $currency['symbol_left'] );
		$this->assertSame( '.', $currency['decimal_separator'] );
	}

	/**
	 * @covers FrmCurrencyHelper::prepare_price
	 */
	public function test_prepare_price() {
		$eur = array(
			'thousand_separator' => '.',
			'decimal_separator'  => ',',
			'decimals'           => 2,
		);
		$gbp = array(
			'thousand_separator' => ',',
			'decimal_separator'  => '.',
			'decimals'           => 2,
		);

		// A shopper can type an amount in a different locale's format than the form's
		// configured currency expects. Trusting the currency's configured separators here
		// used to collide the two into one, silently truncating "1,030.21" to 1.03
		// (formidable-forms#3381, same root cause as #3379/#3380).
		$this->assertSame( '1030.21', FrmCurrencyHelper::prepare_price( '1,030.21', $eur ) );
		$this->assertSame( '1030.21', FrmCurrencyHelper::prepare_price( '1.030,21', $gbp ) );

		// A dot in a comma-decimal currency is ambiguous: one or two trailing digits read as
		// a decimal, three read as thousands.
		$this->assertSame( '20.00', FrmCurrencyHelper::prepare_price( '20.00', $eur ) );
		$this->assertSame( '1.5', FrmCurrencyHelper::prepare_price( '1.5', $eur ) );
		$this->assertSame( '1234', FrmCurrencyHelper::prepare_price( '1.234', $eur ) );

		// A comma in a dot-decimal currency is never ambiguous -- always thousands grouping.
		$this->assertSame( '123', FrmCurrencyHelper::prepare_price( '1,23', $gbp ) );

		// A repeated occurrence of whichever character turns out to be the decimal separator
		// doesn't collide into a second decimal point.
		$this->assertSame( '1234567.89', FrmCurrencyHelper::prepare_price( '1,234.567,89', $eur ) );
	}
}
