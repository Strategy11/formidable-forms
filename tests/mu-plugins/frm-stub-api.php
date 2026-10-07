<?php
/**
 * Serves the formidableforms.com APIs from local fixtures in the e2e test
 * environment, so the suite never depends on a live call: form templates,
 * style templates, application (view) templates and the add-ons list.
 *
 * Two things go wrong without this. A fresh wp-env has an empty cache, and
 * FrmFormApi::get_api_info() returns an empty array outright when another
 * request for the same data is already in flight (the is_running() guard)
 * rather than waiting for it - so the list renders empty and assertions like
 * [frm-search-text="user registration"] never match. The `Get Instant Access`
 * signup flow then calls reset_cached(), clearing the cache mid-run and
 * re-opening the same window for whatever runs next.
 *
 * Sharding sharpened this: every shard is its own wp-env with its own cold
 * cache, so one live fetch per run became one per shard.
 *
 * Filtering pre_http_request rather than seeding the cache options keeps this
 * independent of FrmFormApi's cache internals - the option keys are derived
 * from the license, and reset_cached() deletes them - and it is the standard
 * WordPress way to stub an outbound request.
 *
 * The fixtures sit beside this file on purpose. The whole mu-plugins
 * directory is mapped to a known path inside the container, whereas the
 * plugin directory is named `formidable` locally and `formidable-forms` in
 * CI, so a path built from the plugin folder would break in one of the two.
 * WordPress only auto-loads .php from the mu-plugins root, so the .json
 * siblings are inert.
 *
 * @package Formidable
 */

add_filter(
	'pre_http_request',
	function ( $preempt, $args, $url ) {
		// URL fragment => fixture file. The add-ons list has two endpoints:
		// unlicensed installs (which is what wp-env is) hit the Cloudflare
		// worker, licensed ones hit s11edd.
		$fixtures = array(
			'/wp-json/form-templates/v1/list'  => 'form-templates-api.json',
			'/wp-json/style-templates/v1/list' => 'style-templates-api.json',
			'/wp-json/view-templates/v1/list'  => 'view-templates-api.json',
			'plapi.formidableforms.com/list/'  => 'addons-api.json',
			'/wp-json/s11edd/v1/updates/'      => 'addons-api.json',
		);

		$fixture = '';

		foreach ( $fixtures as $fragment => $file ) {
			if ( str_contains( $url, $fragment ) ) {
				$fixture = __DIR__ . '/' . $file;
				break;
			}
		}

		if ( ! $fixture ) {
			return $preempt;
		}

		if ( ! is_readable( $fixture ) ) {
			// Let the real request through rather than failing the run with an
			// empty list, which is the confusing symptom this exists to
			// prevent.
			return $preempt;
		}

		return array(
			'headers'  => array(),
			'body'     => file_get_contents( $fixture ),
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
