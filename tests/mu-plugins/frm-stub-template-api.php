<?php
// Serves the form-templates API from a local fixture in the e2e test
// environment, so the suite never depends on a live call to
// formidableforms.com.
//
// Two things go wrong without this. A fresh wp-env has an empty template
// cache, and FrmFormApi::get_api_info() returns an empty array outright when
// another request for the same data is already in flight (the is_running()
// guard) rather than waiting for it - so the templates list renders empty and
// assertions like [frm-search-text="user registration"] never match. The
// `Get Instant Access` signup flow then calls reset_cached(), clearing the
// cache mid-run and re-opening the same window for whatever runs next.
//
// Sharding sharpened this: every shard is its own wp-env with its own cold
// cache, so one live fetch per run became one per shard.
//
// Filtering pre_http_request rather than seeding the cache option keeps this
// independent of FrmFormApi's cache internals - the option key is derived
// from the license, and reset_cached() deletes it - and it is the standard
// WordPress way to stub an outbound request.
//
// The fixture sits beside this file on purpose. The whole mu-plugins
// directory is mapped to a known path inside the container, whereas the
// plugin directory is named `formidable` locally and `formidable-forms` in
// CI, so a path built from the plugin folder would break in one of the two.
// WordPress only auto-loads .php from the mu-plugins root, so the .json
// sibling is inert.
add_filter(
	'pre_http_request',
	function ( $preempt, $args, $url ) {
		if ( false === strpos( $url, '/wp-json/form-templates/v1/list' ) ) {
			return $preempt;
		}

		$fixture = __DIR__ . '/form-templates-api.json';

		if ( ! is_readable( $fixture ) ) {
			// Let the real request through rather than failing the run with an
			// empty template list, which is the confusing symptom this exists
			// to prevent.
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
