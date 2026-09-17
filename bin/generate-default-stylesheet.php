<?php
/**
 * Writes css/formidableforms.css so stylelint has a real generated file to
 * check instead of an ignored one, using the plugin's own static-file
 * generator (FrmStyle::save_settings(), the same method a real save of the
 * Styles settings triggers) rather than reimplementing its render logic.
 *
 * Usage: wp eval-file bin/generate-default-stylesheet.php
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

( new FrmStyle() )->save_settings();

WP_CLI::success( 'Generated ' . FrmAppHelper::plugin_path() . '/css/formidableforms.css' );
