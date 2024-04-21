<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

/**
 * Given a string, this class will read through the string using
 * one of a number of terminating rules:
 * - One character.
 * - A specified number of characters.
 * - Until a matching character is found.
 *
 * @since 6.2
 */
class FrmStringReaderHelper {

	/**
	 * @var int
	 */
	private $pos = 0;

	/**
	 * @var int
	 */
	private $max = 0;

	/**
	 * @var string
	 */
	private $string;

	/**
	 * @param string $string
	 */
	public function __construct( $string ) {
		// Split the string up into an array of UTF-8 characters.
		// As an array we can read through it one character at a time.

		$this->string = $string;
		$this->max    = strlen( $this->string ) - 1;
	}

	/**
	 * Read characters until we reach the given character $char.
	 * By default, discard that final matching character and return the rest.
	 *
	 * @param string $char
	 * @param bool   $discard_char
	 * @return string
	 */
	public function read_until( $char, $discard_char = true ) {
		$value = '';

		if ( $discard_char ) {
			while ( ! is_null( $one = $this->read_one() ) && $one !== $char ) {
				$value .= $one;
			}
			return $value;
		}

		while ( ! is_null( $one = $this->read_one() ) ) {
			$value .= $one;
			if ( $one === $char ) {
				break;
			}
		}
		return $value;
	}

	/**
	 * Read $count characters, or until we have reached the end,
	 * whichever comes first.
	 * By default, remove enclosing double-quotes from the result.
	 *
	 * @param int $count
	 * @return string
	 */
	public function read( $count ) {
		$value = '';

		while ( $count > 0 && ! is_null( $one = $this->read_one() ) ) {
			$value .= $one;
			--$count;
		}

		/**
		 * Remove a single set of double-quotes from around a string.
		 *
		 * abc => abc
		 * "abc" => abc
		 * ""abc"" => "abc"
		 */
		if ( strlen( $value ) < 2 || substr( $value, 0, 1 ) !== '"' || substr( $value, -1, 1 ) !== '"' ) {
			// Only remove exactly one quote from the start and the end and then only if there is one at each end.
			// Too short, or does not start or end with a quote.
			return $value;
		}

		// Return the middle of the string, from the second character to the second-but-last.
		return substr( $value, 1, -1 );
	}

	/**
	 * Shift the position. This is used to skip characters that we don't need to read.
	 *
	 * @since x.x This was added as an optimization.
	 *
	 * @param int $count
	 * @return void
	 */
	public function increment_position( $count = 0 ) {
		$this->pos += $count;
	}

	/**
	 * Read the next character from the supplied string.
	 * Return null when we have run out of characters.
	 *
	 * @return string|null
	 */
	private function read_one() {
		if ( $this->pos <= $this->max ) {
			$value      = $this->string[ $this->pos ];
			$this->pos += 1;
			return $value;
		}
		return null;
	}
}
