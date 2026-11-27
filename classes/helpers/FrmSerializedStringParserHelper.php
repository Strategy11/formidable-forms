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
	 * The serialized string currently being parsed.
	 *
	 * @since x.x
	 *
	 * @var string
	 */
	private $string = '';

	/**
	 * The current read position in $string.
	 *
	 * @since x.x
	 *
	 * @var int
	 */
	private $pos = 0;

	/**
	 * The byte length of $string.
	 *
	 * @since x.x
	 *
	 * @var int
	 */
	private $length = 0;

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
	 *
	 * @return mixed
	 */
	public function parse( $string ) {
		$this->string = $string;
		$this->pos    = 0;
		$this->length = strlen( $string );

		$unserialized_data = $this->do_parse();

		// Release the reference so a large string is not held in memory by the singleton.
		$this->string = '';

		if ( is_array( $unserialized_data ) && $this->serialized_string_is_invalid( $string ) ) {
			return array_filter( $unserialized_data, array( $this, 'serialized_value_is_valid' ) );
		}

		return $unserialized_data;
	}

	/**
	 * Check if an unserialized value is valid.
	 *
	 * @since 6.20
	 *
	 * @param mixed $value
	 *
	 * @return bool
	 */
	private function serialized_value_is_valid( $value ) {
		return ! is_string( $value ) || ! str_contains( $value, ';s:' );
	}

	/**
	 * @since 6.20
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	private function serialized_string_is_invalid( $string ) {
		return str_contains( $string, ';s:10:\"a"' ) || str_contains( $string, ';s:";' );
	}

	/**
	 * This is the recursive parser.
	 *
	 * @return array|bool|float|int|string|null
	 */
	private function do_parse() {
		$pos    = $this->pos;
		$length = $this->length;

		if ( $pos >= $length ) {
			// Nothing left to read.
			$this->pos = $pos + 1;
			return null;
		}

		$string = $this->string;
		$type   = $string[ $pos ];

		// Skip the type and the ":" or ";" that follows it.
		$pos += 2;

		switch ( $type ) {
			case 's':
				// String: s:length:"value";
				if ( $pos >= $length ) {
					$this->pos = $pos + 1;
					return '';
				}

				$end = strpos( $string, ':', $pos );

				if ( false === $end ) {
					$this->pos = $length + 1;
					return '';
				}

				// Include the enclosing quotes in the length to read.
				$count = (int) substr( $string, $pos, $end - $pos ) + 2;
				$pos   = $end + 1;

				if ( $count <= 0 || $pos >= $length ) {
					$this->pos = $pos + 1;
					return '';
				}

				$available = $length - $pos;
				$count     = $count > $available ? $available : $count;

				// Move past the value and the ";" separator.
				$this->pos = $pos + $count + 1;

				if ( $count >= 2 && '"' === $string[ $pos ] && '"' === $string[ $pos + $count - 1 ] ) {
					// Remove exactly one quote from each end.
					return $count > 2 ? substr( $string, $pos + 1, $count - 2 ) : '';
				}

				return substr( $string, $pos, $count );

			case 'i':
				return (int) $this->read_until( ';', $pos );

			case 'a':
				return $this->parse_array( $pos );

			case 'b':
				// Boolean is 0 or 1.
				if ( $pos >= $length ) {
					$this->pos = $pos + 1;
					return false;
				}

				$this->pos = $pos + 2;
				return '1' === $string[ $pos ];

			case 'd':
				return (float) $this->read_until( ';', $pos );
		}//end switch

		$this->pos = $pos;

		// Includes case 'N' and case 'O'.
		// Treat a serialized object or anything unexpected as Null.
		return null;
	}

	/**
	 * Read from $start until the next $char, and move past it.
	 * If $char is not found, read to the end of the string.
	 *
	 * @since x.x
	 *
	 * @param string $char  A single character.
	 * @param int    $start The position to start reading from.
	 *
	 * @return string
	 */
	private function read_until( $char, $start ) {
		if ( $start >= $this->length ) {
			$this->pos = $start;
			return '';
		}

		$end = strpos( $this->string, $char, $start );

		if ( false === $end ) {
			$this->pos = $this->length;
			return substr( $this->string, $start );
		}

		$this->pos = $end + 1;
		return substr( $this->string, $start, $end - $start );
	}

	/**
	 * @param int $start The position of the array length.
	 *
	 * @return array
	 */
	private function parse_array( $start ) {
		// Associative array: a:length:{[index][value]...}
		$count = (int) $this->read_until( ':', $start );

		// Eat the opening "{" of the array.
		++$this->pos;

		$val = array();

		for ( $i = 0; $i < $count; $i++ ) {
			if ( $this->pos >= $this->length ) {
				// Every remaining key would parse as null and be skipped, so stop early.
				break;
			}

			$array_key   = $this->do_parse();
			$array_value = $this->do_parse();

			if ( ! is_array( $array_key ) && null !== $array_key ) {
				$val[ $array_key ] = $array_value;
			}
		}

		// Eat "}" terminating the array.
		++$this->pos;

		return $val;
	}
}
