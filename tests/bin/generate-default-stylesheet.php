<?php
/**
 * Writes css/formidableforms.css from the default style settings, with no
 * saved customizations, so stylelint has a real generated file to check
 * instead of an ignored one. Run via `wp eval-file` (a CLI request, so it
 * never touches the admin_init onboarding-wizard redirect that an
 * admin-ajax.php request would).
 *
 * Usage: wp eval-file tests/bin/generate-default-stylesheet.php
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	return;
}

// FrmStylesHelper::get_settings_for_output() only fills every default value
// in via FrmStyle::sanitize_post_content() when previewing_style() is true
// ($_GET['flat'] set) - the same condition every real caller already sets
// (see FrmStylesController::enqueue_css()'s own '&flat=1' query arg).
// Without it, get_settings_for_output() reads a live $style post instead,
// which doesn't exist here, and several properties (e.g. text_color) are
// left unset.
$_GET['flat'] = '1';

$frm_style = new FrmStyle();
$defaults  = $frm_style->get_defaults();
$style     = '';

ob_start();
include FrmAppHelper::plugin_path() . '/css/_single_theme.css.php';
$css = ob_get_clean();

$target = FrmAppHelper::plugin_path() . '/css/formidableforms.css';
file_put_contents( $target, $css );

WP_CLI::success( "Generated $target (" . strlen( $css ) . ' bytes)' );
