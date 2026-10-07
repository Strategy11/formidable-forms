<?php

/**
 * Behavior tests for the entry abilities Formidable itself owns: reading and
 * deleting only. create-entry and update-entry are Pro features and are
 * covered on that plugin.
 */
class test_FrmAbilitiesEntriesController extends FrmUnitTest {

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
	public function test_get_and_delete_entry_round_trip() {
		$entry_id = $this->factory->entry->create( array( 'form_id' => $this->form->id ) );
		$entry    = $this->factory->entry->get_object_by_id( $entry_id );
		$fetched  = $this->execute( 'get-entry', array( 'id' => $entry_id ) );
		$this->assertNotWPError( $fetched );
		$this->assertSame( (string) $entry_id, (string) $fetched['id'] );

		$fetched_by_key = $this->execute( 'get-entry', array( 'id' => $entry->item_key ) );
		$this->assertNotWPError( $fetched_by_key, 'get-entry should also resolve by item_key.' );
		$this->assertSame( (string) $entry_id, (string) $fetched_by_key['id'] );

		$deleted = $this->execute( 'delete-entry', array( 'id' => $entry_id ) );
		$this->assertNotWPError( $deleted );
		$this->assertSame( (string) $entry_id, (string) $deleted['id'] );

		$after = $this->execute( 'get-entry', array( 'id' => $entry_id ) );
		$this->assertWPError( $after, 'A deleted entry should no longer be found.' );
		$this->assertSame( 404, $after->get_error_data()['status'] ?? null );
	}

	/**
	 * @return void
	 */
	public function test_list_entries_scopes_to_a_form_when_given_one() {
		$this->factory->entry->create( array( 'form_id' => $this->form->id ) );
		$other_form = $this->factory->form->create_and_get();
		$this->factory->entry->create( array( 'form_id' => $other_form->id ) );

		$scoped = $this->execute( 'list-entries', array( 'form_id' => $this->form->id ) );

		$this->assertNotWPError( $scoped );

		foreach ( $scoped as $entry ) {
			$this->assertSame( (string) $this->form->id, (string) $entry['form_id'], 'Every listed entry should belong to the requested form.' );
		}
	}

	/**
	 * Listings include drafts unless is_draft is sent explicitly, since an
	 * ability is a data read, not the front-end submission flow that hides
	 * drafts by default.
	 *
	 * @return void
	 */
	public function test_list_entries_includes_drafts_by_default_and_can_be_filtered() {
		$entry_data            = $this->factory->field->generate_entry_array( $this->form );
		$entry_data['form_id'] = $this->form->id;
		$entry_id              = $this->factory->entry->create( $entry_data );

		$draft_data             = $entry_data;
		$draft_data['is_draft'] = 1;
		$draft_data['item_key'] = 'ability-entries-draft';
		$draft_id               = $this->factory->entry->create( $draft_data );

		$all = $this->execute( 'list-entries', array( 'form_id' => $this->form->id ) );
		$this->assertNotWPError( $all );
		$ids = wp_list_pluck( $all, 'id' );
		$this->assertContains( (string) $entry_id, array_map( 'strval', $ids ), 'Drafts should not hide the submitted entry.' );
		$this->assertContains( (string) $draft_id, array_map( 'strval', $ids ), 'Abilities should include drafts by default.' );

		$submitted_only = $this->execute(
			'list-entries',
			array(
				'form_id'  => $this->form->id,
				'is_draft' => 0,
			)
		);
		$this->assertNotWPError( $submitted_only );
		$submitted_ids = array_map( 'strval', wp_list_pluck( $submitted_only, 'id' ) );
		$this->assertContains( (string) $entry_id, $submitted_ids );
		$this->assertNotContains( (string) $draft_id, $submitted_ids, 'is_draft=0 should exclude drafts.' );

		$drafts_only = $this->execute(
			'list-entries',
			array(
				'form_id'  => $this->form->id,
				'is_draft' => 1,
			)
		);
		$this->assertNotWPError( $drafts_only );
		$draft_ids = array_map( 'strval', wp_list_pluck( $drafts_only, 'id' ) );
		$this->assertContains( (string) $draft_id, $draft_ids );
		$this->assertNotContains( (string) $entry_id, $draft_ids, 'is_draft=1 should exclude submitted entries.' );
	}

	/**
	 * @return void
	 */
	public function test_list_entries_order_accepts_any_casing() {
		$this->factory->entry->create( array( 'form_id' => $this->form->id ) );
		$this->factory->entry->create( array( 'form_id' => $this->form->id ) );

		$results = array();

		foreach ( array( 'asc', 'ASC', 'desc', 'DESC' ) as $order ) {
			$listed            = $this->execute(
				'list-entries',
				array(
					'form_id'  => $this->form->id,
					'order'    => $order,
					'order_by' => 'id',
				)
			);
			$results[ $order ] = wp_list_pluck( $listed, 'id' );
		}

		$this->assertSame( $results['asc'], $results['ASC'], 'ASC should sort the same as asc.' );
		$this->assertSame( $results['desc'], $results['DESC'], 'DESC should sort the same as desc.' );
		$this->assertSame( array_reverse( $results['asc'] ), $results['desc'], 'desc should return the reverse of asc.' );
	}

	/**
	 * @return void
	 */
	public function test_permission_follows_the_frm_view_and_delete_entries_capabilities() {
		$entry_for_view   = $this->factory->entry->create( array( 'form_id' => $this->form->id ) );
		$entry_for_delete = $this->factory->entry->create( array( 'form_id' => $this->form->id ) );

		$cases = array(
			'list-entries' => array( 'frm_view_entries', array( 'form_id' => $this->form->id ) ),
			'get-entry'    => array( 'frm_view_entries', array( 'id' => $entry_for_view ) ),
			'delete-entry' => array( 'frm_delete_entries', array( 'id' => $entry_for_delete ) ),
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
