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
		foreach ( array( 'formidable-bulk-edit', 'formidable-tooltips', 'brand-new-boost' ) as $slug ) {
			$addon = $this->addon( $slug );
			$this->run_private_method( array( 'FrmAddonsController', 'override_addon_display' ), array( &$addon, $slug ) );

			if ( 'brand-new-boost' === $slug ) {
				$this->assertArrayNotHasKey( 'excerpt', $addon );
			} else {
				$this->assertNotEmpty( $addon['excerpt'] );
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
	 * Only the add-ons feed asks for Boosts. Other feeds share the request code but not the Boosts.
	 *
	 * @covers FrmFormApi::include_boosts
	 *
	 * @return void
	 */
	public function test_only_the_addon_api_requests_boosts() {
		$api = new FrmStyleApi( '' );
		$api->reset_cached();
		$urls    = array();
		$request = static function ( $preempt, $args, $url ) use ( &$urls ) {
			$urls[] = $url;
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode( array() ),
				'headers'  => array(),
			);
		};
		add_filter( 'pre_http_request', $request, 10, 3 );

		try {
			$api->get_api_info();
			$this->assertCount( 1, $urls );
			$this->assertStringNotContainsString( 'include_boosts', $urls[0] );
		} finally {
			remove_filter( 'pre_http_request', $request, 10 );
			$api->reset_cached();
		}

		foreach ( array( 'FrmStyleApi', 'FrmFormTemplateApi', 'FrmApplicationApi', 'FrmYoutubeFeedApi', 'FrmSalesApi', 'FrmInbox' ) as $class ) {
			$this->assertFalse( $this->run_private_method( array( ( new ReflectionClass( $class ) )->newInstanceWithoutConstructor(), 'include_boosts' ) ), $class );
		}
		$this->assertTrue( $this->run_private_method( array( new FrmFormApi( '' ), 'include_boosts' ) ) );

		// A subclass that keeps the add-ons cache, like FrmAILicense, reads the same feed and must keep its Boosts.
		$addons_subclass = new class( '' ) extends FrmFormApi {};
		$this->assertTrue( $this->run_private_method( array( $addons_subclass, 'include_boosts' ) ) );
		$this->assertTrue( $this->run_private_method( array( new FrmFormApi( 'license-key' ), 'include_boosts' ) ) );
	}

	/**
	 * Boosts are bought separately, so they are not counted or suggested as add-ons.
	 *
	 * @covers FrmAddonsController::get_addons_count
	 * @covers FrmPluginSearch::matching_addon
	 *
	 * @return void
	 */
	public function test_boosts_are_not_counted_or_searched_as_addons() {
		// The Boost comes first so a search that does not skip Boosts would match it.
		$feed = array(
			'2' => $this->addon( 'formidable-tooltips' ) + array(
				'name'         => 'Tooltips',
				'excerpt'      => 'Add tooltips.',
				'search_terms' => 'tooltips',
			),
			'1' => array(
				'title'        => 'Signature',
				'name'         => 'Signature',
				'excerpt'      => 'Add a signature.',
				'search_terms' => 'tooltips signature',
			),
		);
		$api  = new FrmFormApi( '' );
		$api->reset_cached();
		$request = static function () use ( $feed ) {
			return array(
				'response' => array( 'code' => 200 ),
				'body'     => wp_json_encode( $feed ),
				'headers'  => array(),
			);
		};
		add_filter( 'pre_http_request', $request );

		try {
			$this->assertSame( 1, FrmAddonsController::get_addons_count() );
		} finally {
			remove_filter( 'pre_http_request', $request );
			$api->reset_cached();
		}

		$search = ( new ReflectionClass( 'FrmPluginSearch' ) )->newInstanceWithoutConstructor();
		$this->assertSame( 1, $this->run_private_method( array( $search, 'matching_addon' ), array( $feed, array( 'tooltips' ) ) ) );
		unset( $feed['1'] );
		$this->assertNull( $this->run_private_method( array( $search, 'matching_addon' ), array( $feed, array( 'tooltips' ) ) ) );
	}

	/**
	 * An installed Boost keeps its toggle when the API plugin path differs from the installed folder.
	 *
	 * @covers FrmAddonsController::get_boost_plugin_file
	 *
	 * @return void
	 */
	public function test_installed_boost_is_found_when_the_api_path_differs() {
		// The API sends this path, but Advanced Modals ships its main file as frm-advanced-modals.php.
		$addon          = $this->addon( 'formidable-advanced-modals' );
		$addon['id']    = 123;
		$addon['title'] = 'Advanced Modals';
		$api_file       = 'formidable-advanced-modals/formidable-advanced-modals.php';
		$cached         = wp_cache_get( 'plugins', 'plugins' );

		wp_cache_set(
			'plugins',
			array(
				'' => array(
					'formidable-advanced-modals/frm-advanced-modals.php' => array( 'Name' => 'Formidable Advanced Modals' ),
					'formidable-other/formidable-other.php'              => array( 'Name' => 'Formidable Other' ),
				),
			),
			'plugins'
		);

		$updater                = ( new ReflectionClass( 'FrmAddon' ) )->newInstanceWithoutConstructor();
		$updater->download_id   = 123;
		$updater->plugin_folder = 'renamed-modals/frm-advanced-modals.php';
		$register               = static function ( $plugins ) use ( $updater ) {
			$plugins['advanced-modals'] = $updater;
			return $plugins;
		};

		$resolve = function ( $addon ) use ( $api_file ) {
			return $this->run_private_method( array( 'FrmAddonsController', 'get_boost_plugin_file' ), array( $addon, $api_file ) );
		};

		try {
			// Inactive Boost: matched by its plugin name.
			$this->assertSame( 'formidable-advanced-modals/frm-advanced-modals.php', $resolve( $addon ) );

			// Active Boost: matched by its updater download id first.
			add_filter( 'frm_installed_addons', $register );
			$this->assertSame( 'renamed-modals/frm-advanced-modals.php', $resolve( $addon ) );
			remove_filter( 'frm_installed_addons', $register );

			// Not installed: the API path is kept.
			$addon['title'] = 'Celebrations';
			$this->assertSame( $api_file, $resolve( $addon ) );
		} finally {
			remove_filter( 'frm_installed_addons', $register );
			wp_cache_set( 'plugins', $cached, 'plugins' );
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
