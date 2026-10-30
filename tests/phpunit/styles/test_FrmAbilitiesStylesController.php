<?php

/**
 * Behavior tests for the style abilities Formidable itself owns: listing,
 * reading, and updating. create-style, delete-style, and assign-style-to-form
 * are Pro features in the styles-pro domain and are covered on that plugin.
 */
class test_FrmAbilitiesStylesController extends FrmUnitTest {

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		if ( ! function_exists( 'wp_get_abilities' ) ) {
			$this->markTestSkipped( 'The Abilities API is not available in this WordPress install.' );
		}

		$this->set_current_user_to_1();
		$this->enable_abilities();
	}

	/**
	 * @return void
	 */
	private function enable_abilities() {
		if ( ! class_exists( 'WP_Abilities_Registry' ) || ! is_callable( 'FrmMcpController::reset' ) ) {
			$this->markTestSkipped( 'This install has no abilities registry to register into.' );
		}

		$frm_settings      = FrmAppHelper::get_settings();
		$frm_settings->mcp = 1;
		$frm_settings->store();
		FrmMcpController::reset();

		WP_Ability_Categories_Registry::get_instance();
		WP_Abilities_Registry::get_instance();

		remove_all_actions( 'wp_abilities_api_categories_init' );
		remove_all_actions( 'wp_abilities_api_init' );

		add_action( 'wp_abilities_api_categories_init', array( 'FrmAbilitiesController', 'register_categories' ) );
		add_action( 'wp_abilities_api_init', array( 'FrmAbilitiesController', 'register_abilities' ) );

		do_action( 'wp_abilities_api_categories_init' );
		do_action( 'wp_abilities_api_init' );
	}

	/**
	 * @param string $slug  Ability slug, without the formidable-forms prefix.
	 * @param array  $input Ability input parameters.
	 *
	 * @return mixed
	 */
	private function execute( $slug, $input = array() ) {
		$ability = wp_get_ability( 'formidable-forms/' . $slug );

		$this->assertInstanceOf( \WP_Ability::class, $ability, 'The ' . $slug . ' ability should be registered.' );

		return $ability->execute( $input );
	}

	/**
	 * @return void
	 */
	public function test_get_style_resolves_the_default_style_by_keyword() {
		$result = $this->execute( 'get-style', array( 'id' => 'default' ) );

		$this->assertNotWPError( $result, 'get-style should resolve "default" to the site\'s default style.' );
		$this->assertNotEmpty( $result['post_name'] );
	}

	/**
	 * Both the ability's own description ("Retrieve a single Formidable
	 * style by ID or post_name.") and its id input's description ("Style ID
	 * or post_name.") advertise post_name lookup. This is currently false:
	 * FrmAbilitiesStylesController::get_style() does `new FrmStyle( $id )`
	 * for anything other than the literal string "default", and
	 * FrmStyle::get_one() resolves that id with a bare get_post( $this->id ),
	 * which only ever matches a numeric post ID, never a slug. Verified live
	 * against the real style post_name ("d3kj1b"), not guessed. The by-id
	 * and by-"default" assertions pass; the by-post_name assertion is
	 * expected to fail until get_style() adds a post_name lookup path (e.g.
	 * get_page_by_path() scoped to the style post type) the way the schema
	 * already promises.
	 *
	 * @return void
	 */
	public function test_get_style_resolves_by_id_or_post_name() {
		$default = $this->execute( 'get-style', array( 'id' => 'default' ) );
		$this->assertNotWPError( $default );

		$by_id = $this->execute( 'get-style', array( 'id' => $default['id'] ) );
		$this->assertNotWPError( $by_id );
		$this->assertSame( $default['id'], $by_id['id'] );

		$by_name = $this->execute( 'get-style', array( 'id' => $default['post_name'] ) );
		$this->assertNotWPError( $by_name, 'get-style should also resolve by post_name, as its own input schema documents.' );
		$this->assertSame( $default['id'], $by_name['id'] );
	}

	/**
	 * @return void
	 */
	public function test_list_styles_includes_the_default_style() {
		$default = $this->execute( 'get-style', array( 'id' => 'default' ) );
		$this->assertNotWPError( $default );

		$listed = $this->execute( 'list-styles', array( 'page_size' => 200 ) );

		$this->assertNotWPError( $listed );
		$this->assertArrayHasKey( $default['id'], $listed, 'The default style should appear in list-styles.' );
	}

	/**
	 * @return void
	 */
	public function test_update_style_changes_the_name_and_merges_settings() {
		$default = $this->execute( 'get-style', array( 'id' => 'default' ) );
		$this->assertNotWPError( $default );

		$updated = $this->execute(
			'update-style',
			array(
				'id'           => $default['id'],
				'name'         => 'Renamed Style',
				'post_content' => array( 'bg_color' => 'ff0000' ),
			)
		);

		$this->assertNotWPError( $updated );
		$this->assertSame( 'Renamed Style', $updated['name'] );
		$this->assertSame( 'ff0000', $updated['post_content']['bg_color'] ?? null );
	}

	/**
	 * A partial post_content update has to merge over the stored settings,
	 * not replace them, or every setting left out of the request resets.
	 *
	 * @return void
	 */
	public function test_update_style_leaves_other_settings_alone() {
		$default = $this->execute( 'get-style', array( 'id' => 'default' ) );
		$this->assertNotWPError( $default );

		$this->execute(
			'update-style',
			array(
				'id'           => $default['id'],
				'post_content' => array(
					'bg_color'   => 'ff0000',
					'text_color' => '00ff00',
				),
			)
		);

		$updated = $this->execute(
			'update-style',
			array(
				'id'           => $default['id'],
				'post_content' => array( 'bg_color' => '0000ff' ),
			)
		);

		$this->assertNotWPError( $updated );
		$this->assertSame( '0000ff', $updated['post_content']['bg_color'] ?? null );
		$this->assertSame( '00ff00', $updated['post_content']['text_color'] ?? null, 'A setting left out of the update should keep its stored value.' );
	}

	/**
	 * A hex color value stored with a leading # doubles up in the generated
	 * CSS (##ffffff), which the browser drops, so update-style has to strip
	 * it on the way in.
	 *
	 * @return void
	 */
	public function test_update_style_strips_a_leading_hash_from_hex_colors() {
		$default = $this->execute( 'get-style', array( 'id' => 'default' ) );
		$this->assertNotWPError( $default );

		$updated = $this->execute(
			'update-style',
			array(
				'id'           => $default['id'],
				'post_content' => array( 'bg_color' => '#ff0000' ),
			)
		);

		$this->assertNotWPError( $updated );
		$this->assertSame( 'ff0000', $updated['post_content']['bg_color'] ?? null, 'A leading # should be stripped from a hex color value.' );
	}

	/**
	 * @return void
	 */
	public function test_permission_follows_the_frm_view_forms_and_frm_change_settings_capabilities() {
		$default = $this->execute( 'get-style', array( 'id' => 'default' ) );
		$this->assertNotWPError( $default );

		$cases = array(
			'list-styles'  => array( 'frm_view_forms', array() ),
			'get-style'    => array( 'frm_view_forms', array( 'id' => $default['id'] ) ),
			'update-style' => array(
				'frm_change_settings',
				array(
					'id'   => $default['id'],
					'name' => 'X',
				),
			),
		);

		foreach ( $cases as $slug => list( $capability, $input ) ) {
			$subscriber_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
			wp_set_current_user( $subscriber_id );

			$ability = wp_get_ability( 'formidable-forms/' . $slug );
			$denied  = $ability->check_permissions( $input );
			$denied  = is_wp_error( $denied ) ? false : $denied;
			$this->assertFalse( $denied, $slug . ' should refuse a subscriber with no ' . $capability . ' capability.' );

			$subscriber = get_user_by( 'id', $subscriber_id );
			$subscriber->add_cap( $capability );
			wp_set_current_user( 0 );
			wp_set_current_user( $subscriber_id );

			$allowed = $ability->check_permissions( $input );
			$this->assertNotWPError( $allowed, $slug . ' should not error once ' . $capability . ' is granted.' );
			$this->assertTrue( $allowed, $slug . ' should allow a non-admin who holds ' . $capability . '.' );
		}

		$this->set_current_user_to_1();
	}
}
