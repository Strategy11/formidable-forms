<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmYoutubeFeedApi {

	//TO DO: UPDATE TO LIVE URLS
	private $api_url       = 'http://s11.localhost/src/wp-json/s11edd/v1/youtube-feed/';
	private $api_endpoints = array(
		'welcome-video' => 'get-welcome-video',
		'latest-video'  => 'get-latest-video',
	);

	private $cache_keys = array(
		'welcome-video' => 'frm-welcome-video',
		'latest-video'  => 'frm-latest-video',
	);

	private $cache_time = 3600; // 1h

	private function get_api_url( $api_endpoint ) {
		return $this->api_url . '?action=' . $api_endpoint;
	}

	public function get_latest_video() {

		$cached_data = get_transient( $this->cache_keys['latest-video'] );
		if ( false !== $cached_data ) {
			return $cached_data;
		}

		return $this->get_feed_by_api_endpoint( $this->api_endpoints['latest-video'], $this->cache_keys['latest-video'] );
	}

	public function get_welcome_video() {
		$cached_data = get_transient( $this->cache_keys['welcome-video'] );
		if ( false !== $cached_data ) {
			return $cached_data;
		}

		return $this->get_feed_by_api_endpoint( $this->api_endpoints['welcome-video'], $this->cache_keys['welcome-video'] );
	}

	private function get_feed_by_api_endpoint( $api_endpoint, $cache_key ) {
		$response = wp_remote_get( $this->get_api_url( $api_endpoint ) );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$results = json_decode( wp_remote_retrieve_body( $response ), true );

		set_transient(
			$cache_key,
			$results,
			$this->$cache_time
		);

		return $results;
	}
}
