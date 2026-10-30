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

// Several of the style templates gate a selector's only declarations behind
// a single `! empty( $defaults[...] )` check with no fallback (e.g. a
// font-family rule that only prints when a custom font is set) - under the
// stock defaults that selector renders with nothing between its braces.
// That's inert in real output either way, but stylelint's block-no-empty
// has no way to tell "false setting produced this on purpose" from "typo'd
// selector" without evaluating the template's PHP, so strip empty rules
// (including any block left empty once its only content is removed, e.g. a
// media query whose sole rule was itself emptied) before lint sees the file.
$css = file_get_contents( $target );
do {
	$before = $css;
	$css    = preg_replace( '/[^{}]*\{\}/', '', $css );
} while ( $css !== $before );
file_put_contents( $target, $css );

WP_CLI::success( "Generated $target" );
