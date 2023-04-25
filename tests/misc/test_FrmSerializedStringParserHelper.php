<?php

class test_FrmSerialziedStringParserHelper extends FrmUnitTest {

	/**
	 * @covers FrmSerialziedStringParserHelper::parse
	 */
	public function test_parse() {
		// Test an unexpected serialized string format.
		// Arrays should never have an array as its key like in this example.
		$string = 'a:1:{a:1:{i:0;a:1:{i:1;s:4:"Text";}s:4:"Text";}}';
		$parsed = FrmSerializedStringParserHelper::get()->parse( $string );

		// Test a valid serialized string.
		$string = serialize( array( 'key' => 'value' ) );
		$parsed = FrmSerializedStringParserHelper::get()->parse( $string );
		$this->assertIsArray( $parsed );
		$this->assertArrayHasKey( 'key', $parsed );
		$this->assertEquals( 'value', $parsed['key'] );
	}
}
