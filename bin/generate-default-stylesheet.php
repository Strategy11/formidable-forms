<?php
/**
 * Writes css/formidableforms.css so stylelint has a real generated file to
 * check instead of an ignored one, using the plugin's own static-file
 * generator (FrmStyle::save_settings(), the same method a real save of the
 * Styles settings triggers) rather than reimplementing its render logic.
 *
 * Usage: wp eval 'require FrmAppHelper::plugin_path() . "/bin/generate-default-stylesheet.php";'
 * (not `wp eval-file` directly - wp-env's cli container's working
 * directory is the WordPress root, not this plugin's checkout, and the
 * mounted plugin folder name isn't guaranteed.)
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

( new FrmStyle() )->save_settings();

// save_settings() writes wherever add_css_to_uploads_dir() resolves to,
// which can be outside this checkout (wp_upload_dir()) depending on file
// mod permissions - confirm the file actually landed where stylelint
// will look, rather than letting a silent miss pass as a green run.
$target = FrmStyle::get_generated_css_file_path( FrmStyle::add_css_to_uploads_dir() ) . '/' . FrmStylesController::get_file_name();

if ( ! is_file( $target ) || ! filesize( $target ) ) {
	WP_CLI::error( "No stylesheet generated at $target" );
}

WP_CLI::success( "Generated $target" );
