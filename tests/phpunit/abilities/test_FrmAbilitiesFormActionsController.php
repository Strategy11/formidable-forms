<?php

/**
 * Behavior tests for the form action abilities: create/list/get/update/delete
 * through the registered abilities, permission boundaries, and the
 * validation FrmAbilitiesFormActionsController enforces before writing.
 */
class test_FrmAbilitiesFormActionsController extends FrmUnitTest {

	/**
	 * @var stdClass
	 */
	private $form;

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
		$this->form = $this->factory->form->create_and_get();
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
	public function test_a_form_action_round_trips_through_create_list_get_update_delete() {
		$created = $this->execute(
			'create-form-action',
			array(
				'form_id'    => $this->form->id,
				'type'       => 'email',
				'post_title' => 'Notify Admin',
			)
		);

		$this->assertNotWPError( $created, 'create-form-action should succeed with a form_id and a type.' );
		$this->assertSame( 'email', $created['type'] );
		$this->assertSame( 'Notify Admin', $created['post_title'] );
		$this->assertSame( (string) $this->form->id, (string) $created['form_id'] );

		$listed = $this->execute( 'list-form-actions', array( 'form_id' => $this->form->id ) );
		$this->assertNotWPError( $listed );
		$this->assertArrayHasKey( $created['id'], $listed, 'The created action should be in the listing.' );

		$fetched = $this->execute( 'get-form-action', array( 'id' => $created['id'] ) );
		$this->assertNotWPError( $fetched );
		$this->assertSame( $created['id'], $fetched['id'] );

		$updated = $this->execute(
			'update-form-action',
			array(
				'id'         => $created['id'],
				'post_title' => 'Renamed Action',
			)
		);
		$this->assertNotWPError( $updated );
		$this->assertSame( 'Renamed Action', $updated['post_title'] );
		$this->assertSame( 'email', $updated['type'], 'The type should survive an update that does not touch it.' );

		$deleted = $this->execute( 'delete-form-action', array( 'id' => $created['id'] ) );
		$this->assertNotWPError( $deleted );
		$this->assertSame( $created['id'], $deleted['id'] );

		$after = $this->execute( 'get-form-action', array( 'id' => $created['id'] ) );
		$this->assertWPError( $after, 'A deleted action should no longer be found.' );
		$this->assertSame( 404, $after->get_error_data()['status'] ?? null );
	}

	/**
	 * @return void
	 */
	public function test_list_form_actions_requires_a_form_id() {
		$ability = wp_get_ability( 'formidable-forms/list-form-actions' );
		$result  = $ability->execute( array() );

		$this->assertWPError( $result, 'list-form-actions should refuse a request with no form_id, through the ability schema\'s own required check.' );
	}

	/**
	 * @return void
	 */
	public function test_create_form_action_requires_a_type() {
		$ability = wp_get_ability( 'formidable-forms/create-form-action' );
		$result  = $ability->execute( array( 'form_id' => $this->form->id ) );

		$this->assertWPError( $result, 'create-form-action should refuse a request with no type, through the ability schema\'s own required check.' );
	}

	/**
	 * @return void
	 */
	public function test_create_form_action_rejects_an_unregistered_type() {
		$result = $this->execute(
			'create-form-action',
			array(
				'form_id' => $this->form->id,
				'type'    => 'not-a-real-action-type',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 400, $result->get_error_data()['status'] ?? null );
	}

	/**
	 * @return void
	 */
	public function test_create_form_action_reports_not_found_for_a_missing_form() {
		$result = $this->execute(
			'create-form-action',
			array(
				'form_id' => 99999999,
				'type'    => 'email',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 404, $result->get_error_data()['status'] ?? null );
	}

	/**
	 * Post_content merges over the stored settings on update, so a partial
	 * change does not reset every other configured setting.
	 *
	 * @return void
	 */
	public function test_update_form_action_merges_post_content_rather_than_replacing_it() {
		$created = $this->execute(
			'create-form-action',
			array(
				'form_id'      => $this->form->id,
				'type'         => 'email',
				'post_content' => array(
					'email_to'      => 'a@example.com',
					'email_subject' => 'Hello',
				),
			)
		);
		$this->assertNotWPError( $created );

		$updated = $this->execute(
			'update-form-action',
			array(
				'id'           => $created['id'],
				'post_content' => array( 'email_to' => 'b@example.com' ),
			)
		);

		$this->assertNotWPError( $updated );
		$this->assertSame( 'b@example.com', $updated['post_content']['email_to'] ?? null );
		$this->assertSame( 'Hello', $updated['post_content']['email_subject'] ?? null, 'A setting left out of the update should keep its stored value.' );
	}

	/**
	 * The type input is documented as a filter ("Filter by action type...
	 * Optional.") and execute_list_form_actions() attempts it by adding
	 * 'post_excerpt' => $type to the get_posts() args. That key is not one
	 * WP_Query filters on (unlike post_status, which is), so the argument is
	 * silently ignored and every action on the form comes back regardless of
	 * the requested type. Verified live against a real form with one email
	 * and one api action: filtering for type "api" still returns both.
	 * Expected to fail until the filter is implemented, e.g. with a
	 * 'meta_query'-free direct WHERE via 'post_excerpt__in' is not a real
	 * WP_Query arg either — the fix needs a manual $wpdb filter or a
	 * post-fetch array_filter() on post_excerpt.
	 *
	 * @return void
	 */
	public function test_list_form_actions_filters_by_type() {
		$this->execute(
			'create-form-action',
			array(
				'form_id' => $this->form->id,
				'type'    => 'email',
			)
		);
		$webhook = $this->execute(
			'create-form-action',
			array(
				'form_id' => $this->form->id,
				'type'    => 'api',
			)
		);
		$this->assertNotWPError( $webhook );

		$filtered = $this->execute(
			'list-form-actions',
			array(
				'form_id' => $this->form->id,
				'type'    => 'api',
			)
		);

		$this->assertNotWPError( $filtered );
		$this->assertCount( 1, $filtered, 'Filtering by type should return only the matching action.' );
		$this->assertArrayHasKey( $webhook['id'], $filtered );
	}

	/**
	 * @return void
	 */
	public function test_permission_follows_the_frm_view_and_edit_and_delete_forms_capabilities() {
		$action_for_get    = $this->execute(
			'create-form-action',
			array(
				'form_id' => $this->form->id,
				'type'    => 'email',
			)
		);
		$action_for_update = $this->execute(
			'create-form-action',
			array(
				'form_id' => $this->form->id,
				'type'    => 'email',
			)
		);
		$action_for_delete = $this->execute(
			'create-form-action',
			array(
				'form_id' => $this->form->id,
				'type'    => 'email',
			)
		);

		$cases = array(
			'list-form-actions'  => array( 'frm_view_forms', array( 'form_id' => $this->form->id ) ),
			'get-form-action'    => array( 'frm_view_forms', array( 'id' => $action_for_get['id'] ) ),
			'create-form-action' => array(
				'frm_edit_forms',
				array(
					'form_id' => $this->form->id,
					'type'    => 'email',
				),
			),
			'update-form-action' => array(
				'frm_edit_forms',
				array(
					'id'         => $action_for_update['id'],
					'post_title' => 'X',
				),
			),
			'delete-form-action' => array( 'frm_delete_forms', array( 'id' => $action_for_delete['id'] ) ),
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
