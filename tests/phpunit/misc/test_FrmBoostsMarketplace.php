<?php

/**
 * @group addons
 */
class test_FrmBoostsMarketplace extends FrmUnitTest {

	/** @var array */
	private $saved_state = array();

	public function setUp(): void {
		parent::setUp();

		foreach ( array(
			'FrmAddonsController' => array( 'categories' ),
			'FrmAddonsHelper'     => array( 'plan_required', 'built_in', 'boost', 'boost_locked' ),
		) as $class => $names ) {
			foreach ( $names as $name ) {
				$property = new ReflectionProperty( $class, $name );
				$property->setAccessible( true );
				$this->saved_state[] = array( $property, $property->getValue() );
			}
		}
	}

	public function tearDown(): void {
		foreach ( $this->saved_state as $saved ) {
			$saved[0]->setValue( null, $saved[1] );
		}
		parent::tearDown();
	}

	/** @return void */
	public function test_boost_detection_and_categories() {
		$cases = array(
			array( 'is_boost' => true ),
			array( 'categories' => array( 'Boosts' ) ),
			array( 'categories' => array( 'boost' ) ),
			array( 'category-slugs' => array( 'boosts' ) ),
			array( 'category-slugs' => array( 'boost' ) ),
		);

		foreach ( $cases as $addon ) {
			$this->assertTrue( FrmAddonsHelper::is_boost( $addon ) );
			$this->run_private_method( array( 'FrmAddonsController', 'set_categories' ), array( &$addon ) );
			$this->assertSame( array( 'boosts' ), $addon['category-slugs'] );
		}
		$this->assertFalse( FrmAddonsHelper::is_boost( array( 'categories' => array( 'Integrations' ) ) ) );
	}

	/** @return void */
	public function test_missing_excerpts_render_without_empty_paragraphs() {
		foreach ( array( 'bulk-edit', 'brand-new-boost' ) as $slug ) {
			$addon = $this->addon( $slug );
			$this->run_private_method( array( 'FrmAddonsController', 'override_addon_display' ), array( &$addon, $slug ) );

			if ( 'bulk-edit' === $slug ) {
				$this->assertNotEmpty( $addon['excerpt'] );
			} else {
				$this->assertArrayNotHasKey( 'excerpt', $addon );
			}

			$pricing      = 'https://formidableforms.com/pricing/';
			$license_type = '';
			ob_start();
			include FrmAppHelper::plugin_path() . '/classes/views/addons/addon.php';
			$html = ob_get_clean();
			$this->assertStringContainsString( $addon['title'], $html );

			if ( 'brand-new-boost' === $slug ) {
				$this->assertStringNotContainsString( 'frm-line-clamp-2', $html );
			}
		}
	}

	/** @return void */
	public function test_boost_lock_state_does_not_leak_to_the_next_addon() {
		$addon = $this->addon( 'boost' );
		$this->run_private_method( array( 'FrmAddonsHelper', 'set_plan_required' ), array( $addon ) );
		$this->assertTrue( FrmAddonsHelper::is_locked() );

		foreach ( array( 'installed', 'url' ) as $key ) {
			$available         = $addon;
			$available[ $key ] = 'available';
			$this->run_private_method( array( 'FrmAddonsHelper', 'set_plan_required' ), array( $available ) );
			$this->assertFalse( FrmAddonsHelper::is_locked() );
		}
		$this->run_private_method( array( 'FrmAddonsHelper', 'set_plan_required' ), array( $addon ) );
		$this->run_private_method(
			array( 'FrmAddonsHelper', 'set_plan_required' ),
			array(
				array(
					'slug'     => 'plain',
					'built_in' => true,
				),
			)
		);
		$this->assertFalse( FrmAddonsHelper::is_locked() );
	}

	/** @return void */
	public function test_purchase_links_keep_the_boost_tracking_slug() {
		foreach ( array( 'buy_url', 'link', 'neither' ) as $source ) {
			$addon            = $this->addon( 'new-boost' );
			$addon['buy_url'] = 'https://formidableforms.com/buy-boost/';

			if ( 'buy_url' !== $source ) {
				unset( $addon['buy_url'] );
			}

			if ( 'neither' === $source ) {
				unset( $addon['link'] );
			}

			ob_start();
			FrmAddonsController::addon_upgrade_link( $addon, 'https://formidableforms.com/pricing/?from=test' );
			$html = ob_get_clean();
			preg_match( '/href="([^"]+)"/', $html, $matches );
			$url = html_entity_decode( $matches[1] );
			parse_str( wp_parse_url( $url, PHP_URL_QUERY ), $query );
			$this->assertSame( 'new-boost', $query['utm_content'] );
			$paths = array(
				'buy_url' => '/buy-boost/',
				'link'    => '/boost/',
				'neither' => '/pricing/',
			);
			$path  = $paths[ $source ];
			$this->assertSame( $path, wp_parse_url( $url, PHP_URL_PATH ) );
		}
	}

	/** @return void */
	public function test_addon_api_requests_boosts_and_reuses_the_cache() {
		$api = new FrmFormApi( '' );
		$api->reset_cached();
		$requests = 0;
		$request  = static function ( $preempt, $args, $url ) use ( &$requests ) {
			++$requests;
			parse_str( wp_parse_url( $url, PHP_URL_QUERY ), $query );
			self::assertSame( '1', $query['include_boosts'] );
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode( array( 'boost' => array( 'is_boost' => true ) ) ),
				'headers'  => array(),
			);
		};
		add_filter( 'pre_http_request', $request, 10, 3 );

		try {
			$this->assertTrue( $api->get_api_info()['boost']['is_boost'] );
			$this->assertTrue( $api->get_api_info()['boost']['is_boost'] );
			$this->assertSame( 1, $requests );
		} finally {
			remove_filter( 'pre_http_request', $request, 10 );
			$api->reset_cached();
		}
	}

	/**
	 * @param string $slug Boost slug.
	 *
	 * @return array
	 */
	private function addon( $slug ) {
		return array(
			'slug'       => $slug,
			'title'      => 'Test Boost',
			'is_boost'   => true,
			'categories' => array( 'Boosts' ),
			'status'     => array( 'type' => 'not-installed' ),
			'link'       => 'https://formidableforms.com/boost/',
		);
	}
}
