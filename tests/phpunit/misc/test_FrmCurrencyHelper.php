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
		$this->assertEquals( 'U.S. Dollar', $currency['name'] );
		$this->assertEquals( '$', $currency['symbol_left'] );
		$this->assertEquals( '.', $currency['decimal_separator'] );
	}

	/**
	 * @param array $currency
	 *
	 * @return void
	 */
	private function assert_euro( $currency ) {
		$this->assertEquals( 'Euro', $currency['name'] );
		$this->assertEquals( '&#8364;', $currency['symbol_right'] );
		$this->assertEquals( ',', $currency['decimal_separator'] );
	}

	/**
	 * @param array $currency
	 *
	 * @return void
	 */
	private function assert_cad( $currency ) {
		$this->assertEquals( 'Canadian Dollar', $currency['name'] );
		$this->assertEquals( '$', $currency['symbol_left'] );
		$this->assertEquals( '.', $currency['decimal_separator'] );
	}
}
