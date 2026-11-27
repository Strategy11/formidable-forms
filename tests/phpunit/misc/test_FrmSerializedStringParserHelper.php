<?php

/**
 * @covers FrmSerializedStringParserHelper
 */
#[\PHPUnit\Framework\Attributes\CoversClass( FrmSerializedStringParserHelper::class )]
class test_FrmSerializedStringParserHelper extends FrmUnitTest {

	/**
	 * @param string $string
	 *
	 * @return mixed
	 */
	private function parse( $string ) {
		return FrmSerializedStringParserHelper::get()->parse( $string );
	}

	public function test_get_returns_singleton() {
		$this->assertSame( FrmSerializedStringParserHelper::get(), FrmSerializedStringParserHelper::get() );
	}

	public function test_parse() {
		// Test an unexpected serialized string format.
		// Arrays should never have an array as its key like in this example.
		$string = 'a:1:{a:1:{i:0;a:1:{i:1;s:4:"Text";}s:4:"Text";}}';
		$parsed = $this->parse( $string );
		$this->assertSame( array(), $parsed, 'An array key should be skipped.' );

		// Test a valid serialized string.
		$string = serialize( array( 'key' => 'value' ) );
		$parsed = $this->parse( $string );
		$this->assertIsArray( $parsed );
		$this->assertArrayHasKey( 'key', $parsed );
		$this->assertSame( 'value', $parsed['key'] );
	}

	/**
	 * Every value that serialize() can produce without objects should parse back to the original value.
	 *
	 * @dataProvider round_trip_provider
	 *
	 * @param mixed $value
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'round_trip_provider' )]
	public function test_parse_round_trips_serialized_values( $value ) {
		$this->assertSame( $value, $this->parse( serialize( $value ) ) );
	}

	/**
	 * @return array
	 */
	public static function round_trip_provider() {
		return array(
			'empty string'              => array( '' ),
			'simple string'             => array( 'Hello world' ),
			'zero string'               => array( '0' ),
			'string with separators'    => array( 'a:1;b:2;{}' ),
			'string with quotes'        => array( 'She said "hi"' ),
			'string of only quotes'     => array( '""' ),
			'serialized looking string' => array( 's:3:"abc";' ),
			'multiline string'          => array( "Line 1\nLine 2\r\n\tLine 3" ),
			'multibyte string'          => array( 'Café 漢字 😀' ),
			'positive int'              => array( 42 ),
			'negative int'              => array( -42 ),
			'zero int'                  => array( 0 ),
			'max int'                   => array( PHP_INT_MAX ),
			'float'                     => array( 1.5 ),
			'negative float'            => array( -0.25 ),
			'true'                      => array( true ),
			'false'                     => array( false ),
			'null'                      => array( null ),
			'empty array'               => array( array() ),
			'list'                      => array( array( 'Option 1', 'Option 2', 'Option 3' ) ),
			'associative array'         => array(
				array(
					'first' => 'John',
					'last'  => 'Smith',
				),
			),
			'non sequential int keys'   => array(
				array(
					5  => 'five',
					-1 => 'minus one',
					0  => 'zero',
				),
			),
			'mixed value types'         => array(
				array(
					'string' => 'value',
					'int'    => 7,
					'float'  => 2.75,
					'true'   => true,
					'false'  => false,
					'null'   => null,
					'empty'  => '',
				),
			),
			'nested arrays'             => array(
				array(
					'row' => array(
						array(
							'name'  => 'One',
							'items' => array( 1, 2, 3 ),
						),
						array(
							'name'  => 'Two',
							'items' => array(),
						),
					),
				),
			),
			'repeater style data'       => array(
				array(
					'form'    => '12',
					'row_ids' => array( 0, 1 ),
					0         => array(
						0    => '',
						'25' => 'First row',
					),
					1         => array(
						0    => '',
						'25' => 'Second row',
					),
				),
			),
		);
	}

	public function test_parse_long_string() {
		$long = str_repeat( 'Lorem ipsum dolor sit amet; s:1:"x"; ', 3000 );
		$this->assertSame( array( 'body' => $long ), $this->parse( serialize( array( 'body' => $long ) ) ) );
	}

	public function test_parse_large_array() {
		$values = range( 1, 5000 );
		$this->assertSame( $values, $this->parse( serialize( $values ) ) );
	}

	public function test_parse_deeply_nested_array() {
		$value = 'leaf';

		for ( $i = 0; $i < 50; $i++ ) {
			$value = array( 'level' . $i => $value );
		}

		$this->assertSame( $value, $this->parse( serialize( $value ) ) );
	}

	public function test_parse_empty_string() {
		$this->assertNull( $this->parse( '' ) );
	}

	/**
	 * Objects must never be instantiated. They are treated as null.
	 */
	public function test_parse_object_returns_null() {
		$object    = new stdClass();
		$object->a = 1;

		$this->assertNull( $this->parse( serialize( $object ) ) );
		$this->assertNull( $this->parse( 'O:8:"stdClass":1:{s:1:"a";i:1;}' ) );
		$this->assertNull( $this->parse( 'C:11:"ArrayObject":21:{x:i:0;a:0:{};m:a:0:{}}' ) );
	}

	public function test_parse_object_inside_array_is_not_instantiated() {
		$parsed = $this->parse( 'a:2:{i:0;O:8:"stdClass":0:{}i:1;s:1:"x";}' );

		$this->assertIsArray( $parsed );
		$this->assertArrayHasKey( 0, $parsed );
		$this->assertNull( $parsed[0] );

		foreach ( $parsed as $value ) {
			$this->assertIsNotObject( $value );
		}
	}

	public function test_parse_unknown_type_returns_null() {
		$this->assertNull( $this->parse( 'x:1;' ) );
	}

	/**
	 * @dataProvider key_handling_provider
	 *
	 * @param string $string
	 * @param array  $expected
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'key_handling_provider' )]
	public function test_parse_key_handling( $string, $expected ) {
		$this->assertSame( $expected, $this->parse( $string ) );
	}

	/**
	 * @return array
	 */
	public static function key_handling_provider() {
		return array(
			'null key is skipped'            => array( 'a:1:{N;s:1:"x";}', array() ),
			'array key is skipped'           => array( 'a:2:{a:0:{}s:1:"x";i:1;s:1:"y";}', array( 1 => 'y' ) ),
			'bool keys are cast to int'      => array(
				'a:2:{b:1;s:1:"t";b:0;s:1:"f";}',
				array(
					1 => 't',
					0 => 'f',
				),
			),
			'numeric string key becomes int' => array( 'a:1:{s:1:"5";s:4:"five";}', array( 5 => 'five' ) ),
			'duplicate key keeps last value' => array( 'a:2:{i:0;s:1:"a";i:0;s:1:"b";}', array( 0 => 'b' ) ),
		);
	}

	/**
	 * Malformed or truncated strings must parse without errors and give stable results.
	 *
	 * @dataProvider malformed_provider
	 *
	 * @param string $string
	 * @param mixed  $expected
	 */
	#[\PHPUnit\Framework\Attributes\DataProvider( 'malformed_provider' )]
	public function test_parse_malformed( $string, $expected ) {
		$this->assertSame( $expected, $this->parse( $string ) );
	}

	/**
	 * @return array
	 */
	public static function malformed_provider() {
		return array(
			'truncated after first item'  => array( 'a:2:{i:0;s:3:"abc";', array( 0 => 'abc' ) ),
			'truncated after second key'  => array(
				'a:2:{i:0;s:3:"abc";i:1;',
				array(
					0 => 'abc',
					1 => null,
				),
			),
			'truncated string length'     => array( 'a:1:{i:0;s:', array( 0 => '' ) ),
			'truncated value after key'   => array( 'a:1:{s:1:"k";s:', array( 'k' => '' ) ),
			'truncated array header'      => array( 'a:1:{', array() ),
			'negative string length'      => array( 'a:1:{i:0;s:-5:"abc";}', array( 0 => '' ) ),
			'string length too long'      => array( 'a:1:{i:0;s:50:"abc";}', array( 0 => '"abc";}' ) ),
			'string length too short'     => array( 'a:1:{i:0;s:2:"abc";}', array( 0 => '"abc' ) ),
			'huge string length'          => array( 'a:1:{i:0;s:99999999999999999999:"abc";}', array( 0 => '"abc";}' ) ),
			'string with inner quote'     => array( 's:3:"a"b";', 'a"b' ),
			'float in array'              => array( 'a:1:{i:0;d:1.5;}', array( 0 => 1.5 ) ),
			'quoted string length marker' => array( 'a:2:{i:0;s:";i:1;s:2:"ok";}', array( 0 => '1;' ) ),
		);
	}

	/**
	 * A huge array count with no items must return quickly instead of looping over every missing item.
	 */
	public function test_parse_huge_array_count_with_no_items() {
		$start  = microtime( true );
		$parsed = $this->parse( 'a:999999999:{' );

		$this->assertSame( array(), $parsed );
		$this->assertLessThan( 1, microtime( true ) - $start );
	}

	/**
	 * Strings with known invalid substrings have values that contain serialized fragments removed.
	 */
	public function test_parse_filters_values_from_invalid_strings() {
		$this->assertSame( array(), $this->parse( 'a:2:{i:0;s:10:\"a";i:1;s:5:"ok;s:";}' ) );

		// The filter only runs for strings that contain an invalid substring.
		$this->assertSame( array( 0 => 'x;s:y' ), $this->parse( 'a:1:{i:0;s:5:"x;s:y";}' ) );
	}

	/**
	 * State from one parse must not leak into the next.
	 */
	public function test_parse_is_repeatable() {
		$this->parse( 'a:2:{i:0;s:3:"abc";' );
		$this->assertSame( array( 'a' => 1 ), $this->parse( 'a:1:{s:1:"a";i:1;}' ) );
		$this->assertSame( 'x', $this->parse( 's:1:"x";' ) );
	}

	public function test_maybe_unserialize_array_uses_parser() {
		$array = array(
			'a' => 1,
			'b' => array( 'c' ),
		);
		$this->assertSame( $array, FrmAppHelper::maybe_unserialize_array( serialize( $array ) ) );

		// Non-array serialized values are returned unchanged.
		$this->assertSame( 's:1:"x";', FrmAppHelper::maybe_unserialize_array( 's:1:"x";' ) );

		$object = serialize( new stdClass() );
		$this->assertSame( $object, FrmAppHelper::maybe_unserialize_array( $object ) );
	}
}
