<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Parses serialized strings without using the unsafe unserialize function.
 *
 * @since 6.2
 */
class FrmSerializedStringParserHelper {

	/**
	 * @var FrmSerializedStringParserHelper|null
	 */
	private static $instance;

	/**
	 * Get a singleton instance of the parser.
	 *
	 * @return FrmSerializedStringParserHelper
	 */
	public static function get() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor to enforce the use of FrmSerializedStringParserHelper::get.
	 */
	private function __construct() {}

	/**
	 * Parse a string containing a serialized data structure.
	 * This is the initial entry point into the recursive parser.
	 *
	 * @param string $string
	 * @return mixed
	 */
	public function parse( $string ) {
		return $this->do_parse( new FrmStringReaderHelper( $string ) );
	}

	/**
	 * This is the recursive parser.
	 *
	 * @param FrmStringReaderHelper $string
	 * @return array|bool|float|int|string|null
	 */
	private function do_parse( $string ) {
		// May be : or ; as a terminator, depending on what the data type is.
		$type = $string->read( 1 );
		$string->skip_next_character();

		switch ( $type ) {
			case 'a':
				return $this->parse_array( $string );

			case 's':
				return $this->parse_string( $string );

			case 'i':
				return $this->parse_int( $string );

			case 'd':
				return $this->parse_float( $string );

			case 'b':
				return $this->parse_bool( $string );
		}

		// Includes case 'N' and case 'O'.
		// Treat a serialized object or anything unexpected as Null.
		return null;
	}

	/**
	 * @param FrmStringReaderHelper $string
	 * @return array
	 */
	private function parse_array( $string ) {
		// Associative array: a:length:{[index][value]...}
		$count = (int) $string->read_until( ':' );

		// Eat the opening "{" of the array.
		$string->skip_next_character();

		$val = array();
		for ( $i = 0; $i < $count; $i++ ) {
			$array_key   = $this->do_parse( $string );
			$array_value = $this->do_parse( $string );

			if ( ! is_array( $array_key ) ) {
				$val[ $array_key ] = $array_value;
			}
		}

		// Eat "}" terminating the array.
		$string->skip_next_character();

		return $val;
	}

	/**
	 * @param FrmStringReaderHelper $string
	 * @return string
	 */
	private function parse_string( $string ) {
		$len = (int) $string->read_until( ':' );
		$val = $string->read( $len + 2 );

		// Eat the separator.
		$string->skip_next_character();

		return $val;
	}

	/**
	 * @param FrmStringReaderHelper $string
	 * @return int
	 */
	private function parse_int( $string ) {
		return (int) $string->read_until( ';' );
	}

	/**
	 * @param FrmStringReaderHelper $string
	 * @return float
	 */
	private function parse_float( $string ) {
		return (float) $string->read_until( ';' );
	}

	/**
	 * @param FrmStringReaderHelper $string
	 * @return bool
	 */
	private function parse_bool( $string ) {
		// Boolean is 0 or 1.
		$val = $string->read( 1 ) === '1';
		$string->skip_next_character();
		return $val;
	}
}
