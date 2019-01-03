#!/usr/bin/env php
<?php
/**
 * Generates the production (plugin build) version of `formidable.php`
 */

$filename = $argv[1] . '.php';
$f = fopen( dirname( dirname( __FILE__ ) ) . '/' . $filename, 'r' );

$plugin_version = $argv[2];

while ( true ) {
	$line = fgets( $f );
	if ( false === $line ) {
		break;
	}

	if ( preg_match( '@^Version:\s*([0-9a-z.]+)@', $line, $matches ) ) {
		$old_plugin_version = $matches[1];
		if ( $old_plugin_version != $plugin_version ) {
			$line = 'Version: ' . $plugin_version . "\n";
		}
	} elseif ( preg_match( '@public static [$]plug_version = \'([0-9a-z.]+)\';@', $line, $matches ) ) {
		$old_plugin_version = $matches[1];
		if ( $old_plugin_version != $plugin_version ) {
			$line = "\t" . 'public static $plug_version = \'' . $plugin_version . '\';' . "\n";
		}
	}

	echo $line;
}

fclose( $f );
