<?php

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class FrmYoutubeFeedApi extends FrmFormApi {

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
		'welcome'  => 'get-welcome-video',
		'latest'   => 'get-latest-video',
		'featured' => 'get-featured-video',
	);

	/**
	 * The active API endpoint.
	 *
	 * @var string
	 */
	private $api_active_endpoint = '';

	/**
	 * The cache key names.
	 *
	 * @var array
	 */
	private $cache_keys = array(
		'welcome'  => 'frm-welcome-video',
		'latest'   => 'frm-latest-video',
		'featured' => 'frm-featured-video',
	);

	public function __construct() {
		$this->init_api_options();
	}

	/**
	 * Build the YouTube API URL.
	 *
	 * @return string
	 */
	protected function api_url() {
		return $this->api_url . '?action=' . $this->get_api_endpoint();
	}

	/**
	 * Build the API endpoint
	 *
	 * @return string
	 */
	private function get_api_endpoint() {
		return $this->api_active_endpoint;
	}

	/**
	 * Init api options: cache key and url endpoint.
	 *
	 * @return void
	 */
	private function init_api_options( $endpoint = null, $cache_key = null ) {
		$this->cache_key           = $this->cache_keys['welcome'];
		$this->api_active_endpoint = $this->api_endpoints['welcome'];

		if ( $endpoint ) {
			$this->api_active_endpoint = $endpoint;
		}
		if ( $cache_key ) {
			$this->cache_key = $cache_key;
		}
	}

	/**
	 * Get the YouTube video. It gets the data from cache, if cache is no available it will make a new request to YouTube API.
	 *
	 * @param string $type The video type that will be fetched: welcome-video|featured-video|latest-video.
	 *
	 * @return array
	 */
	public function get_video( $type = 'welcome' ) {
		return $this->get_feed_by_api_endpoint( $this->api_endpoints[ $type ], $this->cache_keys[ $type ] );
	}

	/**
	 * Makes request to YouTube feed API. Gets data from specified endpoint. After request is done, it will store data to wp cache.
	 *
	 * @param string $api_endpoint The YouTube API endpoint.
	 * @param string $cache_key The cache key used to store data to wp cache.
	 *
	 * @return array
	 */
	private function get_feed_by_api_endpoint( $api_endpoint, $cache_key ) {
		$this->init_api_options( $api_endpoint, $cache_key );
		return $this->get_api_info();
	}
}
