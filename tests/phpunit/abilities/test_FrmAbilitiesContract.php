<?php

/**
 * Contract tests that run across every ability Formidable itself owns
 * (forms, fields, entries, styles, form-actions, payments, subscriptions).
 *
 * Formidable is the owner plugin for these domains, not an add-on
 * deferring to someone else, so unlike the sibling contract tests in Views,
 * Coupons, and Landing, this file does not test an MCP-off deferral gate for
 * abilities that already exist elsewhere. It does test the one piece every
 * other plugin in the family depends on: FrmAbilitiesController::owns(),
 * read through the frm_ability_domains filter, which is what lets Pro,
 * Views, Coupons, Landing, and the legacy API add-on all agree on who
 * registers what without colliding on a shared ability name.
 */
class test_FrmAbilitiesContract extends FrmUnitTest {

	/**
	 * @var array<string>
	 */
	private static $owned_domains = array( 'forms', 'fields', 'entries', 'styles', 'form-actions', 'payments', 'subscriptions' );

	/**
	 * Abilities that legitimately take no required input.
	 *
	 * @var array<string>
	 */
	private static $no_required_input_abilities = array(
		'formidable-forms/list-forms',
		'formidable-forms/list-entries',
		'formidable-forms/list-styles',
	);

	/**
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		if ( ! function_exists( 'wp_get_abilities' ) ) {
			$this->markTestSkipped( 'The Abilities API is not available in this WordPress install.' );
		}

		( new FrmTransLiteDb() )->upgrade();

		$this->set_current_user_to_1();
		$this->enable_abilities();
	}

	/**
	 * Turn the abilities surface on and register only Formidable's own
	 * abilities, so this file's assertions are not affected by whichever
	 * add-ons happen to be active in this process.
	 *
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
	 * @return array
	 */
	private function get_formidable_abilities() {
		$abilities = array();

		foreach ( wp_get_abilities() as $name => $ability ) {
			if ( str_starts_with( $name, 'formidable-forms/' ) ) {
				$abilities[ $name ] = $ability;
			}
		}

		return $abilities;
	}

	/**
	 * @covers FrmAbilitiesController::register_abilities
	 *
	 * @return void
	 */
	public function test_abilities_are_registered() {
		$abilities = $this->get_formidable_abilities();

		$expected = array(
			'list-forms',
			'get-form',
			'create-form',
			'update-form',
			'delete-form',
			'list-fields',
			'create-field',
			'update-field',
			'delete-field',
			'list-entries',
			'get-entry',
			'delete-entry',
			'list-styles',
			'get-style',
			'update-style',
			'list-form-actions',
			'get-form-action',
			'create-form-action',
			'update-form-action',
			'delete-form-action',
			'list-payments',
			'get-payment',
			'delete-payment',
			'refund-payment',
			'list-subscriptions',
			'get-subscription',
			'delete-subscription',
			'cancel-subscription',
		);

		foreach ( $expected as $slug ) {
			$this->assertArrayHasKey( 'formidable-forms/' . $slug, $abilities, 'The ' . $slug . ' ability should be registered.' );
		}
	}

	/**
	 * Every domain Formidable owns has to actually claim it through the
	 * shared filter, or an add-on with an older copy of a sibling plugin
	 * would keep registering it too.
	 *
	 * @covers FrmAbilitiesController::owns
	 * @covers FrmAbilitiesController::domains
	 *
	 * @return void
	 */
	public function test_owns_reflects_every_domain_formidable_registers() {
		foreach ( self::$owned_domains as $domain ) {
			$this->assertTrue( FrmAbilitiesController::owns( $domain ), 'Formidable should own the ' . $domain . ' domain.' );
		}

		$this->assertFalse( FrmAbilitiesController::owns( 'not-a-real-domain' ), 'A domain nothing maps to a class for should not be owned.' );
	}

	/**
	 * Owns() is what every deferring plugin calls before registering, so it
	 * has to answer false the moment the abilities surface itself is off,
	 * not just when a domain is unmapped.
	 *
	 * @covers FrmAbilitiesController::owns
	 * @covers FrmAbilitiesController::is_active
	 *
	 * @return void
	 */
	public function test_owns_is_false_for_every_domain_while_mcp_is_off() {
		$frm_settings      = FrmAppHelper::get_settings();
		$frm_settings->mcp = 0;
		$frm_settings->store();
		FrmMcpController::reset();

		foreach ( self::$owned_domains as $domain ) {
			$this->assertFalse(
				FrmAbilitiesController::owns( $domain ),
				'owns() should be false for ' . $domain . ' while MCP is off, even though the domain filter still maps it.'
			);
		}

		$frm_settings->mcp = 1;
		$frm_settings->store();
		FrmMcpController::reset();
	}

	/**
	 * The MCP setting is one switch for the whole AI surface, so nothing
	 * registers while it is off.
	 *
	 * @covers FrmAbilitiesController::register_abilities
	 *
	 * @return void
	 */
	public function test_nothing_registers_while_mcp_is_off() {
		foreach ( array_keys( $this->get_formidable_abilities() ) as $name ) {
			wp_unregister_ability( $name );
		}

		$frm_settings      = FrmAppHelper::get_settings();
		$frm_settings->mcp = 0;
		$frm_settings->store();
		FrmMcpController::reset();

		FrmAbilitiesController::register_abilities();

		$this->assertSame( array(), $this->get_formidable_abilities(), 'No ability should register while MCP is off.' );

		$frm_settings->mcp = 1;
		$frm_settings->store();
		FrmMcpController::reset();
		$this->enable_abilities();
	}

	/**
	 * @return void
	 */
	public function test_every_ability_is_described() {
		foreach ( $this->get_formidable_abilities() as $name => $ability ) {
			$this->assertNotEmpty( $ability->get_label(), $name . ' should have a label.' );
			$this->assertNotEmpty( $ability->get_description(), $name . ' should have a description.' );
		}
	}

	/**
	 * @return void
	 */
	public function test_every_input_property_is_described() {
		foreach ( $this->get_formidable_abilities() as $name => $ability ) {
			$schema = $ability->get_input_schema();

			if ( in_array( $name, self::$no_required_input_abilities, true ) ) {
				// These abilities' schemas still declare properties (page,
				// page_size, order...), just none of them required.
				continue;
			}

			$this->assertNotEmpty( $schema['properties'] ?? array(), $name . ' should declare its input properties.' );
		}

		foreach ( $this->get_formidable_abilities() as $name => $ability ) {
			$schema = $ability->get_input_schema();

			foreach ( $schema['properties'] ?? array() as $property => $definition ) {
				$this->assertNotEmpty(
					$definition['description'] ?? '',
					$name . ' is missing a description for the ' . $property . ' input.'
				);
			}
		}
	}

	/**
	 * @return void
	 */
	public function test_no_ability_is_callable_when_logged_out() {
		$abilities = $this->get_formidable_abilities();

		wp_set_current_user( 0 );

		foreach ( $abilities as $name => $ability ) {
			$allowed = $ability->check_permissions( array() );

			if ( is_wp_error( $allowed ) ) {
				continue;
			}

			$this->assertFalse( $allowed, $name . ' should not be callable by a logged out visitor.' );
		}

		$this->set_current_user_to_1();
	}

	/**
	 * @return void
	 */
	public function test_abilities_are_callable_by_an_administrator() {
		$this->set_current_user_to_1();

		foreach ( $this->get_formidable_abilities() as $name => $ability ) {
			$allowed = $ability->check_permissions( array() );

			$this->assertNotWPError( $allowed, $name . ' should not error when checking administrator permission.' );
			$this->assertTrue( $allowed, $name . ' should be callable by an administrator.' );
		}
	}

	/**
	 * @return void
	 */
	public function test_annotations_match_what_the_ability_does() {
		foreach ( $this->get_formidable_abilities() as $name => $ability ) {
			$annotations = $ability->get_meta()['annotations'] ?? array();

			$this->assertArrayHasKey( 'readonly', $annotations, $name . ' should annotate readonly.' );
			$this->assertArrayHasKey( 'destructive', $annotations, $name . ' should annotate destructive.' );
			$this->assertArrayHasKey( 'idempotent', $annotations, $name . ' should annotate idempotent.' );

			$slug     = str_replace( 'formidable-forms/', '', $name );
			$is_read  = str_starts_with( $slug, 'get-' ) || str_starts_with( $slug, 'list-' );
			$is_write = str_starts_with( $slug, 'create-' ) || str_starts_with( $slug, 'update-' ) || str_starts_with( $slug, 'delete-' );

			if ( $is_read ) {
				$this->assertTrue( $annotations['readonly'], $name . ' reads data, so it should be readonly.' );
				$this->assertFalse( $annotations['destructive'], $name . ' reads data, so it should not be destructive.' );
			}

			if ( $is_write ) {
				$this->assertFalse( $annotations['readonly'], $name . ' writes data, so it should not be readonly.' );
			}

			if ( str_starts_with( $slug, 'delete-' ) ) {
				$this->assertTrue( $annotations['destructive'], $name . ' deletes data, so it should be destructive.' );
			}
		}
	}

	/**
	 * @return void
	 */
	public function test_every_ability_is_exposed_to_mcp_and_rest() {
		foreach ( $this->get_formidable_abilities() as $name => $ability ) {
			$meta = $ability->get_meta();

			$this->assertNotEmpty( $meta['show_in_rest'], $name . ' should be exposed in wp-abilities/v1.' );
			$this->assertNotEmpty( $meta['mcp']['public'], $name . ' should be public to the MCP server.' );
		}
	}

	/**
	 * @return void
	 */
	public function test_every_ability_is_in_the_formidable_category() {
		foreach ( $this->get_formidable_abilities() as $name => $ability ) {
			$this->assertSame( 'formidable-forms', $ability->get_category(), $name . ' should be in the formidable-forms category.' );
		}
	}

	/**
	 * A missing resource is a 404 through every ability that takes an id, not
	 * a bare error, and the status has to survive whatever wraps the error on
	 * the way out.
	 *
	 * @return void
	 */
	public function test_get_abilities_report_not_found_for_a_missing_id() {
		$missing_id = 99999999;

		$cases = array(
			'formidable-forms/get-form'         => array( 'id' => $missing_id ),
			'formidable-forms/get-entry'        => array( 'id' => $missing_id ),
			'formidable-forms/get-style'        => array( 'id' => $missing_id ),
			'formidable-forms/get-form-action'  => array( 'id' => $missing_id ),
			'formidable-forms/get-payment'      => array( 'id' => $missing_id ),
			'formidable-forms/get-subscription' => array( 'id' => $missing_id ),
		);

		$abilities = $this->get_formidable_abilities();

		foreach ( $cases as $name => $input ) {
			$this->assertArrayHasKey( $name, $abilities, $name . ' should be registered.' );

			$result = $abilities[ $name ]->execute( $input );

			$this->assertWPError( $result, $name . ' should error for an id that does not exist.' );
			$this->assertSame(
				404,
				$result->get_error_data()['status'] ?? null,
				$name . ' should report a 404 for an id that does not exist.'
			);
		}
	}

	/**
	 * This is the headline correctness case from Garret's report: list-forms
	 * failed with a generic error on empty parameters ({}) but succeeded the
	 * instant any key, even a made up one, was added. Vivi traced the root
	 * cause to the vendored mcp-adapter's AbilityArgumentNormalizer, which
	 * collapses both null and {} to [] for a schema with no top level
	 * default — a layer that sits in front of WP_Ability::execute() and is
	 * only reached through the real MCP JSON-RPC transport.
	 *
	 * This test calls the ability directly with an empty array, which is
	 * what a normalized {} becomes either way. It passes here because
	 * FrmAbilitiesFormsController::execute_list_forms() builds every default
	 * itself through FrmAbilitiesHelper::prepare_order_and_limit(), so it
	 * never depends on the schema's own per-property defaults having been
	 * applied. That means a green result here does NOT confirm Garret's bug
	 * is fixed — it confirms Formidable's own controller has no such bug of
	 * its own. The actual defect lives in the mcp-adapter package one layer
	 * further out, in front of every ability's execute(), and needs an
	 * integration-level test that goes through the real MCP dispatch path
	 * (or a unit test directly against AbilityArgumentNormalizer) to cover.
	 *
	 * @covers FrmAbilitiesFormsController::execute_list_forms
	 *
	 * @return void
	 */
	public function test_list_forms_succeeds_with_no_parameters() {
		$ability = wp_get_ability( 'formidable-forms/list-forms' );
		$result  = $ability->execute( array() );
		$this->assertNotWPError( $result, 'list-forms should succeed with an empty parameters array, not error the way {} does through the real MCP transport.' );
		$this->assertIsArray( $result );

		$result_with_extra_key = $ability->execute( array( 'zzz' => 1 ) );
		$this->assertNotWPError( $result_with_extra_key, 'list-forms should also succeed with an irrelevant extra key, confirming this is the same ability Garret tested.' );
		$this->assertSame( $result, $result_with_extra_key, 'An unrecognized extra key should not change the result.' );
	}

	/**
	 * Same check for list-entries and list-styles, the other two Formidable
	 * owns among the abilities Garret's report named or resembles: neither
	 * has a required input, so both are exposed to the same normalizer path.
	 * Same caveat as above: green here is about Formidable's own controllers,
	 * not proof the mcp-adapter defect is fixed.
	 *
	 * @return void
	 */
	public function test_list_entries_and_list_styles_succeed_with_no_parameters() {
		foreach ( array( 'formidable-forms/list-entries', 'formidable-forms/list-styles' ) as $name ) {
			$result = wp_get_ability( $name )->execute( array() );
			$this->assertNotWPError( $result, $name . ' should succeed with an empty parameters array.' );
			$this->assertIsArray( $result );
		}
	}

	/**
	 * Get-style and delete-style used to answer for any WordPress post, not
	 * just a Formidable style, because FrmStyle::get_one() reads the row with
	 * get_post(), which does not check post_type. FrmAbilitiesStylesController::get_style()
	 * now guards with is_style_post() before returning, so an unrelated post
	 * (a page, in this test) has to read back as a 404, not as fabricated
	 * style data.
	 *
	 * @covers FrmAbilitiesStylesController::get_style
	 * @covers FrmAbilitiesStylesController::is_style_post
	 *
	 * @return void
	 */
	public function test_get_style_refuses_a_post_that_is_not_a_style() {
		$unrelated_post_id = self::factory()->post->create(
			array(
				'post_title' => 'Not a style',
				'post_type'  => 'page',
			)
		);
		$ability           = wp_get_ability( 'formidable-forms/get-style' );
		$result            = $ability->execute( array( 'id' => $unrelated_post_id ) );

		$this->assertWPError( $result, 'get-style should refuse a post ID that is not a Formidable style.' );
		$this->assertSame( 404, $result->get_error_data()['status'] ?? null, 'A non-style post should read back as a 404, not as style data.' );

		wp_delete_post( $unrelated_post_id, true );
	}
}
