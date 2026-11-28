<?php

/**
 * @covers FrmFormApi
 */
#[\PHPUnit\Framework\Attributes\CoversClass( FrmFormApi::class )]
class test_FrmFormApi extends FrmUnitTest {

	/**
	 * The add-on data the licensed API request returns, keyed by download id.
	 *
	 * @var array
	 */
	private $licensed_addons = array();

	/**
	 * The add-on data the public API request returns, or a WP_Error for a failed request.
	 *
	 * @var array|WP_Error
	 */
	private $public_addons = array();

	/**
	 * The URLs requested from the add-ons API during the test.
	 *
	 * @var string[]
	 */
	private $requested_urls = array();

	public function tearDown(): void {
		remove_filter( 'pre_http_request', array( $this, 'mock_addons_request' ) );
		parent::tearDown();
	}

	public function test_get_error_from_response() {
		$api = new FrmFormApi();

		// Test an array error.
		$message = 'Your license has expired.';
		$addons  = array(
			'error' => array(
				'license' => 'license_123',
				'code'    => 'expired',
				'message' => $message,
			),
		);
		$error   = $api->get_error_from_response( $addons );
		$this->assertSame( array( $message ), $error );

		// Test a string error.
		$message = 'Your site has been blocked!';
		$addons  = array(
			'error' => $message,
		);
		$error   = $api->get_error_from_response( $addons );

		$this->assertSame( array( $message ), $error );
	}

	/**
	 * @covers FrmFormApi::get_addon_with_changelog
	 */
	public function test_get_addon_with_changelog_uses_public_changelog() {
		$api   = $this->get_api_with_warm_cache();
		$addon = $api->get_addon_with_changelog( 123 );

		$this->assertCount( 1, $this->requested_urls, 'Only the public request should run when the licensed data is cached.' );
		$this->assertStringNotContainsString( '?l=', $this->requested_urls[0] );
		$this->assertSame( 'Public changelog', $addon['changelog'] );
		$this->assertSame( 'Public banners', $addon['banners'] );
		$this->assertSame( 'https://example.com/licensed.zip', $addon['package'] );
		$this->assert_cache_has_no_changelog( $api );
	}

	/**
	 * @covers FrmFormApi::get_addon_with_changelog
	 */
	public function test_get_addon_with_changelog_uses_licensed_changelog_for_different_version() {
		$api = $this->get_api_with_warm_cache();

		$this->public_addons[123]['version'] = '1.0';

		$this->assert_licensed_changelog( $api->get_addon_with_changelog( 123 ) );
	}

	/**
	 * @covers FrmFormApi::get_addon_with_changelog
	 */
	public function test_get_addon_with_changelog_uses_licensed_changelog_when_public_has_none() {
		$api = $this->get_api_with_warm_cache();

		unset( $this->public_addons[123]['changelog'] );

		$this->assert_licensed_changelog( $api->get_addon_with_changelog( 123 ) );
	}

	/**
	 * @covers FrmFormApi::get_addon_with_changelog
	 */
	public function test_get_addon_with_changelog_uses_licensed_changelog_when_public_request_fails() {
		$api = $this->get_api_with_warm_cache();

		$this->public_addons = new WP_Error( 'http_request_failed', 'Request failed' );

		$this->assert_licensed_changelog( $api->get_addon_with_changelog( 123 ) );
	}

	/**
	 * @covers FrmFormApi::get_addon_with_changelog
	 */
	public function test_get_addon_with_changelog_for_unknown_addon() {
		$api = $this->get_api_with_warm_cache();

		$this->assertSame( array(), $api->get_addon_with_changelog( 999 ) );
		$this->assert_cache_has_no_changelog( $api );
	}

	/**
	 * Get an API object whose licensed data is already cached, with the request log cleared.
	 *
	 * @return FrmFormApi
	 */
	private function get_api_with_warm_cache() {
		$addon = array(
			'id'        => 123,
			'title'     => 'Test Add-on',
			'slug'      => 'test-addon',
			'version'   => '2.0',
			'package'   => 'https://example.com/licensed.zip',
			'changelog' => 'Licensed changelog',
			'banners'   => 'Licensed banners',
		);

		$this->licensed_addons = array( 123 => $addon );

		$public_addon = $addon;
		unset( $public_addon['package'] );
		$public_addon['changelog'] = 'Public changelog';
		$public_addon['banners']   = 'Public banners';

		$this->public_addons = array( 123 => $public_addon );

		add_filter( 'pre_http_request', array( $this, 'mock_addons_request' ), 10, 3 );

		$api = new FrmFormApi( 'test-license' );
		$api->reset_cached();
		$api->get_api_info();

		$this->assertCount( 1, $this->requested_urls, 'The licensed data should be requested to warm the cache.' );
		$this->assertStringContainsString( '?l=', $this->requested_urls[0] );
		$this->assert_cache_has_no_changelog( $api );

		$this->requested_urls = array();

		return $api;
	}

	/**
	 * @param array $addon The add-on data returned by get_addon_with_changelog.
	 *
	 * @return void
	 */
	private function assert_licensed_changelog( $addon ) {
		$this->assertSame( 'Licensed changelog', $addon['changelog'] );
		$this->assertSame( 'Licensed banners', $addon['banners'] );
		$this->assertSame( 'https://example.com/licensed.zip', $addon['package'] );
		$this->assertCount( 2, $this->requested_urls, 'The licensed data should be requested after the public request.' );
		$this->assertStringContainsString( '?l=', $this->requested_urls[1] );
	}

	/**
	 * @param FrmFormApi $api The API object whose cache is checked.
	 *
	 * @return void
	 */
	private function assert_cache_has_no_changelog( $api ) {
		$cache = get_option( $api->get_cache_key() );

		$this->assertIsArray( $cache );
		$this->assertStringNotContainsString( 'changelog', $cache['value'] );
	}

	/**
	 * Answer add-on API requests with the licensed or public data, depending on the license in the URL.
	 *
	 * @param array|false|WP_Error $response Preemptive HTTP response.
	 * @param array                $args     HTTP request arguments.
	 * @param string               $url      The request URL.
	 *
	 * @return array|false|WP_Error
	 */
	public function mock_addons_request( $response, $args, $url ) {
		if ( ! str_contains( $url, 's11edd/v1/updates/' ) ) {
			return $response;
		}

		$this->requested_urls[] = $url;

		$addons = str_contains( $url, '?l=' ) ? $this->licensed_addons : $this->public_addons;

		if ( is_wp_error( $addons ) ) {
			return $addons;
		}

		return array(
			'headers'  => array(),
			'body'     => wp_json_encode( $addons ),
			'response' => array(
				'code'    => 200,
				'message' => 'OK',
			),
			'cookies'  => array(),
			'filename' => null,
		);
	}
}
