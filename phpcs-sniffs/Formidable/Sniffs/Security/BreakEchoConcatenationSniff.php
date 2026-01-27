<?php
/**
 * Formidable_Sniffs_Security_BreakEchoConcatenationSniff
 *
 * Detects echo statements with FrmAppHelper::kses concatenation and converts to kses_echo.
 * Only targets the specific pattern without affecting other code.
 *
 * @package Formidable\Sniffs
 */

namespace Formidable\Sniffs\Security;

use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Files\File;

/**
 * Detects and fixes echo statements with FrmAppHelper::kses concatenation.
 *
 * Bad:
 * echo ' ' . FrmAppHelper::kses( $label, 'all' ) . '</label>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
 *
 * Good:
 * echo ' ';
 * FrmAppHelper::kses_echo( $label, 'all' );
 * echo '</label>';
 */
class BreakEchoConcatenationSniff implements Sniff {

	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register() {
		return array( T_STRING );
	}

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the current token in the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Look for FrmAppHelper::kses calls
		if ( $tokens[ $stackPtr ]['content'] !== 'kses' ) {
			return;
		}

		// Check if this is FrmAppHelper::kses
		$prev = $phpcsFile->findPrevious( T_WHITESPACE, $stackPtr - 1, null, true );

		if ( false === $prev || $tokens[ $prev ]['content'] !== '::' ) {
			return;
		}

		$prevPrev = $phpcsFile->findPrevious( T_WHITESPACE, $prev - 1, null, true );

		if ( false === $prevPrev || $tokens[ $prevPrev ]['content'] !== 'FrmAppHelper' ) {
			return;
		}

		// Check if this is inside an echo statement
		$echoPtr = $this->findEnclosingEcho( $phpcsFile, $stackPtr );

		if ( false === $echoPtr ) {
			return;
		}

		// Determine the semicolon that ends this echo statement.
		$semicolon = $phpcsFile->findNext( T_SEMICOLON, $stackPtr + 1 );

		if ( false === $semicolon ) {
			return;
		}

		// Determine the start of the echo expression.
		$expressionStart = $phpcsFile->findNext( T_WHITESPACE, $echoPtr + 1, null, true );

		if ( false === $expressionStart || $expressionStart >= $semicolon ) {
			return;
		}

		// Check if there's concatenation around the kses call.
		if ( ! $this->hasConcatenationAroundKses( $phpcsFile, $stackPtr, $expressionStart, $semicolon ) ) {
			return;
		}

		// Only act when there's a phpcs ignore comment for the missing escaping warning.
		$hasIgnoreComment = $this->hasSecurityIgnoreComment( $phpcsFile, $expressionStart, $semicolon );

		if ( ! $hasIgnoreComment ) {
			return;
		}

		$fix = $phpcsFile->addFixableError(
			'Use FrmAppHelper::kses_echo() instead of FrmAppHelper::kses() in echo concatenation.',
			$stackPtr,
			'BreakEchoConcatenation',
			array()
		);

		if ( true === $fix ) {
			$this->fixKsesCall( $phpcsFile, $stackPtr, $echoPtr, $expressionStart, $semicolon );
		}
	}

	/**
	 * Find the enclosing echo statement.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  The position of the kses call.
	 *
	 * @return false|int Position of echo token or false if not found.
	 */
	private function findEnclosingEcho( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();

		// Look backwards for echo
		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['code'] === T_ECHO ) {
				return $i;
			}

			if ( $tokens[ $i ]['code'] === T_SEMICOLON || $tokens[ $i ]['code'] === T_OPEN_CURLY_BRACKET ) {
				break;
			}
		}

		return false;
	}

	/**
	 * Check if there's concatenation around the kses call.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr        The position of the kses call.
	 * @param int  $expressionStart The first token after echo.
	 * @param int  $semicolon       The semicolon that ends the echo statement.
	 *
	 * @return bool True if concatenation is found.
	 */
	private function hasConcatenationAroundKses( File $phpcsFile, $stackPtr, $expressionStart, $semicolon ) {
		$tokens = $phpcsFile->getTokens();

		$openParen = $phpcsFile->findNext( T_OPEN_PARENTHESIS, $stackPtr + 1 );

		if ( false === $openParen ) {
			return false;
		}
		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];

		$beforeConcat = $phpcsFile->findPrevious( T_STRING_CONCAT, $stackPtr - 1, $expressionStart );

		if ( false !== $beforeConcat ) {
			return true;
		}

		$afterConcat = $phpcsFile->findNext( T_STRING_CONCAT, $closeParen + 1, $semicolon );
		return ( false !== $afterConcat );
	}

	/**
	 * Check if there's a security ignore comment after the semicolon.
	 *
	 * @param File $phpcsFile      The file being scanned.
	 * @param int  $expressionStart The first token after echo.
	 * @param int  $semicolon       Position of the semicolon.
	 *
	 * @return bool True if ignore comment is found.
	 */
	private function hasSecurityIgnoreComment( File $phpcsFile, $expressionStart, $semicolon ) {
		$tokens = $phpcsFile->getTokens();

		// Look for comment on the same line or next line
		$currentLine = $tokens[ $semicolon ]['line'];

		// Check tokens between the start of the expression and the semicolon
		for ( $i = $expressionStart; $i <= $semicolon; $i++ ) {
			if ( $tokens[ $i ]['line'] !== $currentLine ) {
				continue;
			}

			if ( $this->isEscapeIgnoreToken( $tokens[ $i ] ) ) {
				return true;
			}
		}

		// Check the next line for ignore comment
		for ( $i = $semicolon + 1; $i < count( $tokens ); $i++ ) {
			if ( $tokens[ $i ]['line'] > $currentLine + 1 ) {
				break;
			}

			if ( $this->isEscapeIgnoreToken( $tokens[ $i ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Fix the echo statement by breaking the concatenation and calling kses_echo directly.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr        The position of the kses call.
	 * @param int  $echoPtr         The position of the echo token.
	 * @param int  $expressionStart The first token after echo.
	 * @param int  $semicolon       The semicolon that terminates the echo statement.
	 *
	 * @return void
	 */
	private function fixKsesCall( File $phpcsFile, $stackPtr, $echoPtr, $expressionStart, $semicolon ) {
		$tokens = $phpcsFile->getTokens();

		$segments = $this->splitEchoSegments( $phpcsFile, $expressionStart, $semicolon );

		if ( count( $segments ) < 2 ) {
			return;
		}

		$targetSegmentIndex = null;

		foreach ( $segments as $index => $segment ) {
			if ( $stackPtr >= $segment['start'] && $stackPtr <= $segment['end'] ) {
				$targetSegmentIndex = $index;
				break;
			}
		}

		if ( null === $targetSegmentIndex ) {
			return;
		}

		$indentation = $this->getIndentationForToken( $phpcsFile, $echoPtr );
		$args        = $this->getKsesArguments( $phpcsFile, $stackPtr );

		if ( null === $args ) {
			return;
		}

		$leadingWhitespace = $this->getLeadingWhitespaceBeforeToken( $phpcsFile, $echoPtr );

		if ( '' === $indentation && '' !== $leadingWhitespace ) {
			$indentation = $leadingWhitespace;
		}

		$prevNonWhitespace = $phpcsFile->findPrevious( T_WHITESPACE, $echoPtr - 1, null, true );
		$inlineWithPhpTag  = false;

		if ( false !== $prevNonWhitespace && T_OPEN_TAG === $tokens[ $prevNonWhitespace ]['code'] && $tokens[ $prevNonWhitespace ]['line'] === $tokens[ $echoPtr ]['line'] ) {
			$inlineWithPhpTag = true;
			$indentation = $this->getIndentationForToken( $phpcsFile, $prevNonWhitespace );
			$leadingWhitespace = '';
		}

		$hasLeadingWhitespace = ( '' !== $leadingWhitespace );
		$lineIndent = $indentation;

		$newLines = array();

		foreach ( $segments as $index => $segment ) {
			$segmentString = trim(
				$phpcsFile->getTokensAsString(
					$segment['start'],
					( $segment['end'] - $segment['start'] + 1 )
				)
			);

			if ( '' === $segmentString ) {
				continue;
			}

			if ( $index === $targetSegmentIndex ) {
				$newLines[] = 'FrmAppHelper::kses_echo( ' . $args . ' );';
				continue;
			}

			$newLines[] = 'echo ' . $segmentString . ';';
		}

		if ( empty( $newLines ) ) {
			return;
		}

		$eol        = $phpcsFile->eolChar;
		$newContent = '';

		if ( $inlineWithPhpTag ) {
			$newContent .= $eol;
		}

		foreach ( $newLines as $index => $line ) {
			$needsIndent = $inlineWithPhpTag || $index > 0 || ! $hasLeadingWhitespace;

			if ( $needsIndent ) {
				$newContent .= $lineIndent;
			}

			$newContent .= $line . $eol;
		}

		$fixer = $phpcsFile->fixer;
		$fixer->beginChangeset();
		$fixer->replaceToken( $echoPtr, $newContent );

		for ( $i = $echoPtr + 1; $i <= $semicolon; $i++ ) {
			$fixer->replaceToken( $i, '' );
		}
		$fixer->endChangeset();

		if ( $inlineWithPhpTag ) {
			$nextNonWhitespace = $phpcsFile->findNext( T_WHITESPACE, $semicolon + 1, null, true );

			if ( false !== $nextNonWhitespace && T_CLOSE_TAG === $tokens[ $nextNonWhitespace ]['code'] ) {
				$prefix = $eol;
				$prefix .= $lineIndent;
				$fixer->addContentBefore( $nextNonWhitespace, $prefix );
			}
		}

		$this->removeIgnoreComment( $phpcsFile, $semicolon );
	}

	/**
	 * Split the echo statement into individual segments divided by concatenation operators.
	 *
	 * @param File $phpcsFile      The file being scanned.
	 * @param int  $expressionStart The first token after the echo keyword.
	 * @param int  $semicolon       The semicolon ending the echo statement.
	 *
	 * @return array
	 */
	private function splitEchoSegments( File $phpcsFile, $expressionStart, $semicolon ) {
		$tokens   = $phpcsFile->getTokens();
		$segments = array();
		$depth    = 0;
		$segmentStart = $expressionStart;

		for ( $i = $expressionStart; $i < $semicolon; $i++ ) {
			$code = $tokens[ $i ]['code'];

			if ( in_array( $code, array( T_OPEN_PARENTHESIS, T_OPEN_SQUARE_BRACKET, T_OPEN_CURLY_BRACKET ), true ) ) {
				$depth++;
			} elseif ( in_array( $code, array( T_CLOSE_PARENTHESIS, T_CLOSE_SQUARE_BRACKET, T_CLOSE_CURLY_BRACKET ), true ) ) {
				$depth = max( 0, $depth - 1 );
			}

			if ( $code === T_STRING_CONCAT && 0 === $depth ) {
				$segments[] = array(
					'start' => $segmentStart,
					'end'   => $i - 1,
				);
				$segmentStart = $i + 1;
			}
		}

		if ( $segmentStart < $semicolon ) {
			$segments[] = array(
				'start' => $segmentStart,
				'end'   => $semicolon - 1,
			);
		}

		return $segments;
	}

	/**
	 * Extract the arguments passed to FrmAppHelper::kses.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  Position of the kses token.
	 *
	 * @return string|null
	 */
	private function getKsesArguments( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$openParen = $phpcsFile->findNext( T_OPEN_PARENTHESIS, $stackPtr + 1 );

		if ( false === $openParen || ! isset( $tokens[ $openParen ]['parenthesis_closer'] ) ) {
			return null;
		}

		$closeParen = $tokens[ $openParen ]['parenthesis_closer'];
		$length     = $closeParen - $openParen - 1;

		return trim( $phpcsFile->getTokensAsString( $openParen + 1, $length ) );
	}

	/**
	 * Determine the indentation used for a token.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  Token position.
	 *
	 * @return string
	 */
	private function getIndentationForToken( File $phpcsFile, $stackPtr ) {
		$tokens = $phpcsFile->getTokens();
		$indentation = '';

		for ( $i = $stackPtr - 1; $i >= 0; $i-- ) {
			if ( $tokens[ $i ]['code'] !== T_WHITESPACE ) {
				break;
			}

			$content = $tokens[ $i ]['content'];
			$eolPos  = strrpos( $content, $phpcsFile->eolChar );

			if ( false !== $eolPos ) {
				$indentation = substr( $content, $eolPos + strlen( $phpcsFile->eolChar ) );
				break;
			}
		}

		if ( '' === $indentation && isset( $tokens[ $stackPtr ]['column'] ) ) {
			$spaces = max( 0, $tokens[ $stackPtr ]['column'] - 1 );
			$indentation = str_repeat( ' ', $spaces );
		}

		return $indentation;
	}

	/**
	 * Get the leading whitespace before a token on the same line.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  Token position.
	 *
	 * @return string
	 */
	private function getLeadingWhitespaceBeforeToken( File $phpcsFile, $stackPtr ) {
		$tokens   = $phpcsFile->getTokens();
		$line     = $tokens[ $stackPtr ]['line'];
		$eolChar  = $phpcsFile->eolChar;
		$firstPtr = $stackPtr;

		while ( $firstPtr > 0 && $tokens[ $firstPtr - 1 ]['line'] === $line ) {
			$firstPtr--;
		}

		$prefix = '';

		for ( $i = $firstPtr; $i < $stackPtr; $i++ ) {
			$prefix .= $tokens[ $i ]['content'];
		}

		$lastBreak = strrpos( $prefix, $eolChar );

		if ( false !== $lastBreak ) {
			return substr( $prefix, $lastBreak + strlen( $eolChar ) );
		}

		return $prefix;
	}

	/**
	 * Determine if there's leading whitespace before a token on its line.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $stackPtr  Token position.
	 *
	 * @return bool
	 */
	private function hasLeadingWhitespaceBeforeToken( File $phpcsFile, $stackPtr ) {
		return ( '' !== $this->getLeadingWhitespaceBeforeToken( $phpcsFile, $stackPtr ) );
	}

	/**
	 * Remove the phpcs ignore comment.
	 *
	 * @param File $phpcsFile The file being scanned.
	 * @param int  $semicolon Position of the semicolon.
	 *
	 * @return void
	 */
	private function removeIgnoreComment( File $phpcsFile, $semicolon ) {
		$fixer = $phpcsFile->fixer;
		$tokens = $phpcsFile->getTokens();

		$currentLine = $tokens[ $semicolon ]['line'];

		for ( $i = $semicolon + 1; $i < count( $tokens ); $i++ ) {
			if ( $tokens[ $i ]['line'] > $currentLine + 1 ) {
				break;
			}

			if ( $this->isEscapeIgnoreToken( $tokens[ $i ] ) ) {
				$fixer->replaceToken( $i, '' );
				break;
			}
		}
	}

	/**
	 * Determine if a token represents the escape ignore comment we care about.
	 *
	 * @param array $token Token data from PHPCS.
	 *
	 * @return bool
	 */
	private function isEscapeIgnoreToken( $token ) {
		$matchCodes = array( T_COMMENT );

		if ( defined( 'T_PHPCS_IGNORE' ) ) {
			$matchCodes[] = T_PHPCS_IGNORE;
		}

		if ( defined( 'T_PHPCS_IGNORE_ON' ) ) {
			$matchCodes[] = T_PHPCS_IGNORE_ON;
		}

		if ( defined( 'T_PHPCS_IGNORE_FILE' ) ) {
			$matchCodes[] = T_PHPCS_IGNORE_FILE;
		}

		return in_array( $token['code'], $matchCodes, true )
			&& strpos( $token['content'], 'WordPress.Security.EscapeOutput.OutputNotEscaped' ) !== false;
	}
}
