<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmYoutubeFeedApi {

	/**
	 * The YouTube API URL.
	 *
	 * @var string
	 */
	private $api_url = 'https://formidableforms.com/wp-json/s11-sites/v1/youtube-feed/';

	/**
	 * The YouTube API endpoints.
	 *
	 * @var array
	 */
	private $api_endpoints = array(
		'welcome-video' => 'get-welcome-video',
		'latest-video'  => 'get-latest-video',
	);

	/**
	 * The cache key names.
	 *
	 * @var array
	 */
	private $cache_keys = array(
		'welcome-video' => 'frm-welcome-video',
		'latest-video'  => 'frm-latest-video',
	);

	/**
	 * The cache expiration time.
	 *
	 * @var int
	 */
	private $cache_expire = HOUR_IN_SECONDS * 2; // 2h

	/**
	 * Build the YouTube API URL.
	 *
	 * @param string $api_endpoint
	 *
	 * @return string
	 */
	private function get_api_url( $api_endpoint ) {
		return $this->api_url . '?action=' . $api_endpoint;
	}

	/**
	 * Get the latest YouTube video. It gets the data from cache, if cache is no available it will make a new request to YouTube API.
	 *
	 * @return array
	 */
	public function get_latest_video() {

		$cached_data = get_transient( $this->cache_keys['latest-video'] );
		if ( false !== $cached_data ) {
			return $cached_data;
		}

		return $this->get_feed_by_api_endpoint( $this->api_endpoints['latest-video'], $this->cache_keys['latest-video'] );
	}

	/**
	 * Get the welcome YouTube video. It gets the data from cache, if cache is no available it will make a new request to YouTube API.
	 *
	 * @return array
	 */
	public function get_welcome_video() {
		$cached_data = get_transient( $this->cache_keys['welcome-video'] );
		if ( false !== $cached_data ) {
			return $cached_data;
		}

		return $this->get_feed_by_api_endpoint( $this->api_endpoints['welcome-video'], $this->cache_keys['welcome-video'] );
	}

	/**
	 * Makes request to YouTube feed API. Gets data from specified endpoint. After request is done, it will store data to wp cache.
	 *
	 * @param string $api_endpoint. The YouTube API endpoint.
	 * @param string $cache_key The cache key used to store data to wp cache.
	 *
	 * @return array
	 */
	private function get_feed_by_api_endpoint( $api_endpoint, $cache_key ) {
		$response = wp_remote_get( $this->get_api_url( $api_endpoint ) );
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$results = json_decode( wp_remote_retrieve_body( $response ), true );
		set_transient(
			$cache_key,
			$results,
			$this->cache_expire
		);

		return $results;
	}
}
