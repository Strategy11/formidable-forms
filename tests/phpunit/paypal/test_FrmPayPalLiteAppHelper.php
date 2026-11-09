<?php

/**
 * @group paypal
 *
 * @covers FrmPayPalLiteAppHelper
 */
#[\PHPUnit\Framework\Attributes\Group( 'paypal' )]
#[\PHPUnit\Framework\Attributes\CoversClass( FrmPayPalLiteAppHelper::class )]
class test_FrmPayPalLiteAppHelper extends FrmUnitTest {

	public function tearDown(): void {
		remove_all_filters( 'frm_paypal_brand_name' );
		parent::tearDown();
	}

	/**
	 * Apple Pay rejects the merchant session when the display name has non-ASCII characters,
	 * like the en dash SEO plugins use as the title separator.
	 *
	 * @covers FrmPayPalLiteAppHelper::get_wallet_display_name
	 *
	 * @dataProvider wallet_display_name_provider
	 *
	 * @param string $brand_name
	 * @param string $expected
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'wallet_display_name_provider' )]
	public function test_get_wallet_display_name( $brand_name, $expected ) {
		add_filter(
			'frm_paypal_brand_name',
			function () use ( $brand_name ) {
				return $brand_name;
			}
		);

		$this->assertSame( $expected, FrmPayPalLiteAppHelper::get_wallet_display_name() );
	}

	/**
	 * @return array[]
	 */
	public static function wallet_display_name_provider() {
		return array(
			'plain ASCII is unchanged'        => array( 'My Store', 'My Store' ),
			'en dash becomes a hyphen'        => array( 'Page – My Store', 'Page - My Store' ),
			'em dash becomes a hyphen'        => array( 'Page — My Store', 'Page - My Store' ),
			'accents are transliterated'      => array( 'Café Münster', 'Cafe Munster' ),
			'other symbols are dropped'       => array( 'Shop ™ 日本', 'Shop' ),
			'tags are stripped'               => array( '<b>Shop</b>', 'Shop' ),
			'entities are decoded'            => array( 'Joe&#039;s Pizza &amp; Pasta', "Joe's Pizza & Pasta" ),
			'cut to 64 characters'            => array( str_repeat( 'a', 80 ), str_repeat( 'a', 64 ) ),
			'trailing space after cut is cut' => array( str_repeat( 'a', 63 ) . ' b', str_repeat( 'a', 63 ) ),
			'empty falls back to a label'     => array( '', 'Payment' ),
			'all non-ASCII falls back'        => array( '日本', 'Payment' ),
		);
	}
}
