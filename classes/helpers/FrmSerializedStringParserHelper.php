<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Parses serialized strings without using the unsafe unserialize function.
 */

class FrmSerializedStringParserHelper {

	/**
	 * Parse a string containing a serialized data structure.
	 * This is the initial entry point into the recursive parser.
	 *
	 * @param string $string
	 * @return mixed
	 */
	public function parse( $string ) {
		return $this->doParse( new FrmStringReaderHelper( $string ) );
	}

	/**
	 * This is the recursive parser.
	 *
	 * @param FrmStringReaderHelper $string
	 * @return mixed
	 */
	protected function doParse( $string ) {
		$val = null;

		// May be : or ; as a terminator, depending on what the data type is.
		$type = substr( $string->read( 2 ), 0, 1 );

		switch ( $type ) {
			case 'a':
				// Associative array: a:length:{[index][value]...}
				$count = (int) $string->readUntil( ':' );

				// Eat the opening "{" of the array.
				$string->read( 1 );

				$val = [];
				for ( $i = 0; $i < $count; $i++ ) {
					$array_key         = $this->doParse( $string );
					$array_value       = $this->doParse( $string );
					$val[ $array_key ] = $array_value;
				}

				// Eat "}" terminating the array.
				$string->read( 1 );

				break;

			case 's':
				$len = (int) $string->readUntil( ':' );
				$val = $string->read( $len + 2 );

				// Eat the separator.
				$string->read( 1 );
				break;

			case 'i':
				$val = (int) $string->readUntil( ';' );
				break;

			case 'd':
				$val = (float) $string->readUntil( ';' );
				break;

			case 'b':
				// Boolean is 0 or 1
				$bool = $string->read( 2 );
				$val  = substr( $bool, 0, 1 ) == '1';
				break;

			default:
				// Includes case 'N' and case 'O'.
				// Treat a serialized object or anything unexpected as Null.
				$val = null;
				break;
		}

		return $val;
	}
}
