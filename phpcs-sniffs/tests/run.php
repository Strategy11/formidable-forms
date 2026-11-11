<?php
/**
 * Run the standalone regression fixtures with: php phpcs-sniffs/tests/run.php.
 *
 * @package Formidable\Sniffs
 */

if ( 'cli' !== PHP_SAPI ) {
	return;
}

$root     = dirname( __DIR__, 2 );
$fixtures = __DIR__ . '/fixtures';
$expected = json_decode( file_get_contents( $fixtures . '/expected.json' ), true );
$scratch  = sys_get_temp_dir() . '/formidable-sniffs-' . uniqid();
mkdir( $scratch );
$scratch = realpath( $scratch );

/**
 * Run a command without passing it through a shell.
 *
 * @param array $command Command and arguments.
 *
 * @return array Exit status, standard output, and standard error.
 */
function run_sniff_command( $command ) {
	$process = proc_open(
		$command,
		array(
			1 => array( 'pipe', 'w' ),
			2 => array( 'pipe', 'w' ),
		),
		$pipes
	);
	$output  = stream_get_contents( $pipes[1] );
	$error   = stream_get_contents( $pipes[2] );
	fclose( $pipes[1] );
	fclose( $pipes[2] );
	return array( proc_close( $process ), $output, $error );
}

/**
 * Count the violations the full Lite standard reports for a file, grouped by sniff source.
 *
 * @param string $root Lite checkout path.
 * @param string $path File to check.
 *
 * @return array<string, int> Violation counts keyed by sniff source.
 */
function count_standard_violations( $root, $path ) {
	$result = run_sniff_command( array( PHP_BINARY, $root . '/vendor/bin/phpcs', '--standard=' . $root . '/phpcs.xml', '-q', '--report=json', $path ) );
	$report = json_decode( $result[1], true );

	if ( ! is_array( $report ) || ! isset( $report['files'] ) ) {
		throw new RuntimeException( 'Cannot run phpcs.xml. ' . $result[1] . $result[2] );
	}
	$counts = array();

	foreach ( $report['files'] as $file ) {
		foreach ( $file['messages'] as $message ) {
			$counts[ $message['source'] ] = ( $counts[ $message['source'] ] ?? 0 ) + 1;
		}
	}
	return $counts;
}

try {
	$rules = '<ruleset name="FormidableFixtures">';

	foreach ( $expected as $name => $lines ) {
		$fixturePath = $root . '/phpcs-sniffs/Formidable/Sniffs/CodeAnalysis/' . $name . 'Sniff.php';
		$rules      .= '<rule ref="' . htmlspecialchars( $fixturePath, ENT_QUOTES, 'UTF-8' ) . '" />';
	}
	$rules .= '</ruleset>';
	file_put_contents( $scratch . '/ruleset.xml', $rules );

	foreach ( $expected as $name => $lines ) {
		$fixturePath = $scratch . '/' . $name . '.php';
		copy( $fixtures . '/' . $name . '.inc', $fixturePath );
		$command = array( PHP_BINARY, $root . '/vendor/bin/phpcs', '--standard=' . $scratch . '/ruleset.xml', '--report=json', $fixturePath );
		$result  = run_sniff_command( $command );
		$report  = json_decode( $result[1], true );
		$actual  = array();

		if ( ! is_array( $report ) || ! isset( $report['files'][ $fixturePath ] ) ) {
			throw new RuntimeException( $name . ': PHPCS failed. ' . $result[1] . $result[2] );
		}

		foreach ( $report['files'][ $fixturePath ]['messages'] as $message ) {
			$actual[] = $message['line'];

			if ( ! $message['fixable'] ) {
				throw new RuntimeException( $name . ': non-fixable violation.' );
			}
		}

		if ( $lines !== $actual ) {
			throw new RuntimeException( $name . ': unexpected violation lines: ' . json_encode( $actual ) );
		}
		$standardBefore = count_standard_violations( $root, $fixturePath );
		$fix            = run_sniff_command( array( PHP_BINARY, $root . '/vendor/bin/phpcbf', '--standard=' . $scratch . '/ruleset.xml', $fixturePath ) );

		if ( ! in_array( $fix[0], array( 0, 1 ), true ) || file_get_contents( $fixturePath ) !== file_get_contents( $fixtures . '/' . $name . '.fixed' ) ) {
			throw new RuntimeException( $name . ': unexpected fixed output. ' . $fix[1] . $fix[2] );
		}
		$lint   = run_sniff_command( array( PHP_BINARY, '-l', $fixturePath ) );
		$second = run_sniff_command( array( PHP_BINARY, $root . '/vendor/bin/phpcbf', '--standard=' . $scratch . '/ruleset.xml', $fixturePath ) );
		$clean  = run_sniff_command( $command );

		if ( 0 !== $lint[0] || 0 !== $second[0] || 0 !== $clean[0] ) {
			throw new RuntimeException( $name . ': syntax or idempotency check failed.' );
		}

		// The fixed output must not need a second pass under the full Lite standard.
		foreach ( count_standard_violations( $root, $fixturePath ) as $source => $count ) {
			if ( $count > ( $standardBefore[ $source ] ?? 0 ) ) {
				throw new RuntimeException( $name . ': fixed output adds phpcs.xml violations for ' . $source );
			}
		}

		if ( 'MergeAdjacentPhpBlocks' === $name ) {
			$before = run_sniff_command( array( PHP_BINARY, $fixtures . '/' . $name . '.inc' ) );
			$after  = run_sniff_command( array( PHP_BINARY, $fixturePath ) );

			if ( $before !== $after ) {
				throw new RuntimeException( $name . ': runtime output changed.' );
			}
		}

		if ( 'SimplifyBooleanTernary' === $name ) {
			foreach ( array( 'null', 'false', 'true', '0', '1', "'0'", "'1'", "''", 'array()', 'array( 1 )' ) as $value ) {
				$code   = '$x = ' . $value . '; $y = true; $guard = true; $other = false; $fallback = true; include $argv[1]; echo serialize( array( $a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k ) );';
				$before = run_sniff_command( array( PHP_BINARY, '-r', $code, $fixtures . '/' . $name . '.inc' ) );
				$after  = run_sniff_command( array( PHP_BINARY, '-r', $code, $fixturePath ) );

				if ( $before !== $after ) {
					throw new RuntimeException( $name . ': runtime values changed for ' . $value );
				}
			}
		}

		if ( 'PreferWpAttributeHelpers' === $name ) {
			$runtimeBefore = $scratch . '/attributes-before.php';
			$runtimeAfter  = $scratch . '/attributes-after.php';
			file_put_contents( $runtimeBefore, implode( '', array_slice( file( $fixtures . '/' . $name . '.inc' ), 0, 4 ) ) );
			file_put_contents( $runtimeAfter, implode( '', array_slice( file( $fixturePath ), 0, 4 ) ) );

			foreach ( array( 'null', 'false', 'true', '0', '1', "'0'", "'1'", "'yes'", "''", 'array()', 'array( 1 )' ) as $value ) {
				$code      = 'function attr_helper( $value, $name ) { $result = $value ? " $name=\"$name\"" : ""; echo $result; return $result; } function checked( $value ) { return attr_helper( $value, "checked" ); } function selected( $value ) { return attr_helper( $value, "selected" ); } function disabled( $value ) { return attr_helper( $value, "disabled" ); } function wp_readonly( $value ) { return attr_helper( $value, "readonly" ); } $value = ' . $value . '; include $argv[1];';
				$before    = run_sniff_command( array( PHP_BINARY, '-r', $code, $runtimeBefore ) );
				$after     = run_sniff_command( array( PHP_BINARY, '-r', $code, $runtimeAfter ) );
				$normalize = static function ( $html ) {
					return preg_replace( '/\s+(checked|selected|disabled|readonly)(?:="\1")?/', ' $1', $html );
				};

				if ( 0 !== $before[0] || 0 !== $after[0] || $normalize( $before[1] ) !== $normalize( $after[1] ) ) {
					throw new RuntimeException( $name . ': attribute values changed for ' . $value );
				}
			}
		}
		fwrite( STDOUT, $name . ': passed' . PHP_EOL );
	}
	// The shared standard discovers new files automatically, so verify the staged opt-in.
	$rolloutPath = $scratch . '/rollout.php';
	copy( $fixtures . '/PreferDbGetCol.inc', $rolloutPath );

	foreach ( array(
		'phpcs-sniffs/Formidable/ruleset.xml' => 0,
		'phpcs.xml'                           => 2,
	) as $standard => $count ) {
		$result = run_sniff_command( array( PHP_BINARY, $root . '/vendor/bin/phpcs', '--standard=' . $root . '/' . $standard, '-q', '--report=json', $rolloutPath ) );
		$report = json_decode( $result[1], true );
		$actual = 0;

		if ( ! is_array( $report ) || ! isset( $report['files'] ) ) {
			throw new RuntimeException( 'Cannot verify staged rollout. ' . $result[1] . $result[2] );
		}

		foreach ( $report['files'] as $file ) {
			foreach ( $file['messages'] as $message ) {
				$sourceParts = explode( '.', $message['source'] );

				if ( array_slice( $sourceParts, 0, 3 ) === array( 'Formidable', 'CodeAnalysis', 'PreferDbGetCol' ) ) {
					++$actual;
				}
			}
		}

		if ( $count !== $actual ) {
			throw new RuntimeException( 'Unexpected staged rollout count for ' . $standard );
		}
	}
	fwrite( STDOUT, 'Shared standard opt-in: passed' . PHP_EOL );

} finally {
	foreach ( glob( $scratch . '/*' ) as $fixturePath ) {
		unlink( $fixturePath );
	}
	rmdir( $scratch );
}
