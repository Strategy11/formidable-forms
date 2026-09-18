<?php
/**
 * Protect the files wp-env shares with a real dev site from e2e test runs.
 *
 * Wp-env's "plugins": ["."] mapping bind-mounts the plugin's actual working directory into the
 * container rather than a disposable copy, the same as a symlinked PHPUnit checkout, so anything
 * the container writes under wp-content/plugins/formidable lands in the real working tree too:
 *
 * 1. FrmMigrate::maybe_delete_htaccess_file() checks its own `css/frm_fonts.css` over HTTP, and
 *    deletes the plugin's .htaccess (tracked in git) when that request doesn't come back 200. In
 *    wp-env that request goes out for real and has no reason to succeed, so admin page loads
 *    during the suite delete the working tree's .htaccess - breaking permalinks and Basic Auth on
 *    any dev site sharing this checkout. Answer the plugin's own asset requests locally instead,
 *    mirroring FrmUnitTest::respond_to_plugin_asset_request() in tests/phpunit/base/FrmUnitTest.php.
 * 2. Saving a style regenerates css/formidableforms.css in place by default. wp-env has no Pro
 *    add-on, so that regeneration is Lite-only and overwrites a Pro dev site's stylesheet with one
 *    missing every Pro per-style block - the exact "gutted stylesheet" failure the dev-site skill
 *    warns about for PHPUnit runs, caused the same way. `frm_add_css_to_uploads_dir` redirects the
 *    write into wp-env's own disposable uploads dir instead, the same filter FrmUnitTest applies.
 *
 * @package Formidable
 */

add_filter( 'frm_add_css_to_uploads_dir', '__return_true' );

add_filter(
	'pre_http_request',
	function ( $response, $args, $url ) {
		if ( ! class_exists( 'FrmAppHelper' ) || ! str_starts_with( $url, FrmAppHelper::plugin_url() . '/' ) ) {
			return $response;
		}

		return array(
			'headers'  => array(),
			'body'     => '',
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'  => array(),
			'filename' => null,
		);
	},
	10,
	3
);
