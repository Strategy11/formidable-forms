#!/usr/bin/env php
<?php
/**
 * Generates the production (plugin build) version of `formidable.php`
 */

$f = fopen( dirname( dirname( __FILE__ ) ) . '/formidable.php', 'r' );

$plugin_version = $argv[1];

while ( true ) {
	$line = fgets( $f );
	if ( false === $line ) {
		break;
	}

	if ( preg_match( '@^\s*\*\s*Version:\s*([0-9.]+)@', $line, $matches ) ) {
		$old_plugin_version = $matches[1];
		if ( $old_plugin_version != $plugin_version ) {
			$line = 'Version: ' . $plugin_version;
		}
	}

	echo $line;
}

fclose( $f );
