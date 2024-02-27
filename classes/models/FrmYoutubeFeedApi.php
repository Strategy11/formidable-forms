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
	private static $base_api_url = 'https://formidableforms.com/wp-json/s11-sites/v1/youtube-feed/';

	/**
	 * The cache key names.
	 *
	 * @var string
	 */
	protected $cache_key = 'frm_yt_videos';

	public function __construct() {
		// Don't load license key or dynamic cache key.
	}

	/**
	 * @return string
	 */
	protected function api_url() {
		return self::$base_api_url;
	}

	/**
	 * Get the YouTube video. It gets the data from cache, if cache is no available it will make a new request to YouTube API.
	 *
	 * @param string $type The video type that will be fetched: welcome|featured|latest.
	 *
	 * @return array
	 */
	public function get_video( $type = 'welcome' ) {
		$videos = $this->get_api_info();
		if ( isset( $videos[ $type ] ) ) {
			return $videos[ $type ];
		}
		return array();
	}
}
