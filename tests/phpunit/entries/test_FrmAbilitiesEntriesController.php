<?php

/**
 * Behavior tests for the entry abilities Formidable itself owns: reading,
 * marking as spam and deleting. create-entry and update-entry are Pro features and are
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
	public function test_mark_entry_spam_sets_the_spam_status_and_returns_the_entry() {
		$entry_id = $this->factory->entry->create( array( 'form_id' => $this->form->id ) );
		$entry    = $this->factory->entry->get_object_by_id( $entry_id );
		$marked   = $this->execute( 'mark-entry-spam', array( 'id' => $entry->item_key ) );
		$this->assertNotWPError( $marked, 'An item_key should resolve like an ID.' );
		$this->assertSame( FrmSpamEntriesHelper::SPAM_ENTRY_STATUS, $marked['is_draft'] );
		$this->assertSame( (int) $entry_id, $marked['id'] );
		$this->assertSame( $marked, $this->execute( 'get-entry', array( 'id' => $entry_id ) ), 'The response should match get-entry.' );

		$stored = FrmEntry::getOne( $entry_id );
		$this->assertTrue( FrmSpamEntriesHelper::is_manual_spam( $stored ), 'It should be recorded as marked by hand, like the Entries screen does.' );
	}

	/**
	 * @return void
	 */
	public function test_mark_entry_spam_succeeds_again_on_a_spam_entry() {
		$entry_id = $this->factory->entry->create( array( 'form_id' => $this->form->id ) );

		$this->assertNotWPError( $this->execute( 'mark-entry-spam', array( 'id' => $entry_id ) ) );

		$again = $this->execute( 'mark-entry-spam', array( 'id' => $entry_id ) );
		$this->assertNotWPError( $again );
		$this->assertSame( FrmSpamEntriesHelper::SPAM_ENTRY_STATUS, $again['is_draft'] );
	}

	/**
	 * @return void
	 */
	public function test_mark_entry_spam_refuses_drafts_and_unknown_entries() {
		$draft_id = $this->factory->entry->create(
			array(
				'form_id'  => $this->form->id,
				'is_draft' => 1,
			)
		);

		$draft = $this->execute( 'mark-entry-spam', array( 'id' => $draft_id ) );
		$this->assertWPError( $draft );
		$this->assertSame( 'frm_entry_not_markable', $draft->get_error_code() );
		$this->assertSame( 400, $draft->get_error_data()['status'] ?? null );
		$this->assertSame( 1, (int) FrmEntry::getOne( $draft_id )->is_draft, 'A refused draft should keep its status.' );

		$missing = $this->execute( 'mark-entry-spam', array( 'id' => 0 ) );
		$this->assertWPError( $missing );
		$this->assertSame( 404, $missing->get_error_data()['status'] ?? null );
	}

	/**
	 * @return void
	 */
	public function test_mark_entry_spam_moves_child_entries_with_their_parent() {
		$parent_id = $this->factory->entry->create( array( 'form_id' => $this->form->id ) );
		$child_id  = $this->factory->entry->create(
			array(
				'form_id'        => $this->form->id,
				'parent_item_id' => $parent_id,
			)
		);

		$this->assertNotWPError( $this->execute( 'mark-entry-spam', array( 'id' => $parent_id ) ) );

		FrmEntry::clear_cache();
		$this->assertTrue( FrmSpamEntriesHelper::is_spam( FrmEntry::getOne( $child_id ) ), 'A child entry should follow its parent.' );
	}

	/**
	 * @return void
	 */
	public function test_mark_entry_spam_refuses_a_child_entry() {
		$parent_id = $this->factory->entry->create( array( 'form_id' => $this->form->id ) );
		$child_id  = $this->factory->entry->create(
			array(
				'form_id'        => $this->form->id,
				'parent_item_id' => $parent_id,
			)
		);

		$child = $this->execute( 'mark-entry-spam', array( 'id' => $child_id ) );
		$this->assertWPError( $child );
		$this->assertSame( 'frm_entry_not_markable', $child->get_error_code() );
		$this->assertSame( 400, $child->get_error_data()['status'] ?? null );

		FrmEntry::clear_cache();

		foreach ( array( $parent_id, $child_id ) as $id ) {
			$this->assertSame( 0, (int) FrmEntry::getOne( $id )->is_draft, 'A refused child should leave its parent and itself submitted.' );
		}
	}

	/**
	 * @return void
	 */
	public function test_mark_entry_spam_succeeds_when_another_request_marks_it_first() {
		global $wpdb;

		$entry_id = $this->factory->entry->create( array( 'form_id' => $this->form->id ) );

		// Prime the cache so this call still sees the entry as submitted after the other request wins.
		FrmEntry::getOne( $entry_id );
		$wpdb->update( $wpdb->prefix . 'frm_items', array( 'is_draft' => FrmSpamEntriesHelper::SPAM_ENTRY_STATUS ), array( 'id' => $entry_id ) );

		$marked = $this->execute( 'mark-entry-spam', array( 'id' => $entry_id ) );
		$this->assertNotWPError( $marked );
		$this->assertSame( FrmSpamEntriesHelper::SPAM_ENTRY_STATUS, $marked['is_draft'] );
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
	 * Spam entries are left out of a listing unless is_draft asks for them.
	 *
	 * @return void
	 */
	public function test_list_entries_can_filter_spam_entries() {
		global $wpdb;

		$entry_data            = $this->factory->field->generate_entry_array( $this->form );
		$entry_data['form_id'] = $this->form->id;
		$entry_id              = $this->factory->entry->create( $entry_data );

		$draft_data             = $entry_data;
		$draft_data['is_draft'] = 1;
		$draft_data['item_key'] = 'ability-entries-draft-for-spam';
		$draft_id               = $this->factory->entry->create( $draft_data );

		$spam_data             = $entry_data;
		$spam_data['item_key'] = 'ability-entries-spam';
		$spam_id               = $this->factory->entry->create( $spam_data );

		// Set the status directly so the test does not depend on spam storage being available.
		$wpdb->update( $wpdb->prefix . 'frm_items', array( 'is_draft' => FrmSpamEntriesHelper::SPAM_ENTRY_STATUS ), array( 'id' => $spam_id ) );

		$spam_only = $this->execute(
			'list-entries',
			array(
				'form_id'  => $this->form->id,
				'is_draft' => FrmSpamEntriesHelper::SPAM_ENTRY_STATUS,
			)
		);
		$this->assertNotWPError( $spam_only );
		$this->assertSame( array( (string) $spam_id ), array_values( array_map( 'strval', wp_list_pluck( $spam_only, 'id' ) ) ), 'is_draft=4 should return only spam entries.' );

		foreach ( array( 0, 1 ) as $status ) {
			$listed = $this->execute(
				'list-entries',
				array(
					'form_id'  => $this->form->id,
					'is_draft' => $status,
				)
			);
			$this->assertNotWPError( $listed );
			$this->assertNotContains( (string) $spam_id, array_map( 'strval', wp_list_pluck( $listed, 'id' ) ), 'is_draft=' . $status . ' should not return spam.' );
		}

		$default = $this->execute( 'list-entries', array( 'form_id' => $this->form->id ) );
		$this->assertNotWPError( $default );
		$default_ids = array_map( 'strval', wp_list_pluck( $default, 'id' ) );
		$this->assertContains( (string) $entry_id, $default_ids );
		$this->assertContains( (string) $draft_id, $default_ids );
		$this->assertNotContains( (string) $spam_id, $default_ids, 'Spam should stay out of the default listing.' );
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
	 * The search term is only applied with Pro, so Lite must not advertise it.
	 *
	 * @see https://github.com/Strategy11/formidable-pro/issues/6854
	 *
	 * @return void
	 */
	public function test_list_entries_schema_has_no_search_without_pro() {
		if ( class_exists( 'FrmProEntriesHelper' ) ) {
			$this->markTestSkipped( 'Pro is active, so search is supported.' );
		}

		$schema = wp_get_ability( 'formidable-forms/list-entries' )->get_input_schema();

		$this->assertArrayNotHasKey( 'search', $schema['properties'] );
		$this->assertArrayHasKey( 'form_id', $schema['properties'], 'The other inputs should still be listed.' );
	}

	/**
	 * The start_date and end_date inputs both include their whole day.
	 *
	 * @see https://github.com/Strategy11/formidable-pro/issues/6857
	 *
	 * @return void
	 */
	public function test_list_entries_date_filters_include_the_boundary_days() {
		$created = array(
			'before'    => '2026-03-09 23:59:59',
			'start'     => '2026-03-10 00:00:00',
			'end'       => '2026-03-12 23:59:59',
			'after'     => '2026-03-13 00:00:00',
			'start_end' => '2026-03-12 00:00:00',
		);
		$ids     = $this->create_entries_created_at( $created );

		$listed = $this->execute(
			'list-entries',
			array(
				'form_id'    => $this->form->id,
				'start_date' => '2026-03-10',
				'end_date'   => '2026-03-12',
			)
		);
		$this->assertNotWPError( $listed );
		$listed_ids = array_map( 'intval', wp_list_pluck( $listed, 'id' ) );

		$this->assertContains( $ids['start'], $listed_ids, 'An entry created at midnight on start_date should be included.' );
		$this->assertContains( $ids['end'], $listed_ids, 'An entry created at 23:59:59 on end_date should be included.' );
		$this->assertContains( $ids['start_end'], $listed_ids, 'An entry created at midnight on end_date should be included.' );
		$this->assertNotContains( $ids['before'], $listed_ids, 'An entry from the day before start_date should be left out.' );
		$this->assertNotContains( $ids['after'], $listed_ids, 'An entry from the day after end_date should be left out.' );

		$single_day = $this->execute(
			'list-entries',
			array(
				'form_id'    => $this->form->id,
				'start_date' => '2026-03-12',
				'end_date'   => '2026-03-12',
			)
		);
		$this->assertNotWPError( $single_day );
		$single_day_ids = array_map( 'intval', wp_list_pluck( $single_day, 'id' ) );
		sort( $single_day_ids );
		$expected = array( $ids['end'], $ids['start_end'] );
		sort( $expected );
		$this->assertSame( $expected, $single_day_ids, 'The same start and end date should return that whole day.' );

		$with_time = $this->execute(
			'list-entries',
			array(
				'form_id'    => $this->form->id,
				'start_date' => '2026-03-10',
				'end_date'   => '2026-03-12 10:00:00',
			)
		);
		$this->assertNotWPError( $with_time );
		$with_time_ids = array_map( 'intval', wp_list_pluck( $with_time, 'id' ) );

		$this->assertContains( $ids['start_end'], $with_time_ids, 'An entry created before the end_date time should be included.' );
		$this->assertNotContains( $ids['end'], $with_time_ids, 'An end_date with a time should not be extended to the end of the day.' );
	}

	/**
	 * Calling list-entries with no ordering input matches the schema defaults.
	 *
	 * @see https://github.com/Strategy11/formidable-pro/issues/6857
	 *
	 * @return void
	 */
	public function test_list_entries_defaults_match_the_schema() {
		$this->create_entries_created_at(
			array(
				'middle' => '2026-03-11 12:00:00',
				'oldest' => '2026-03-10 12:00:00',
				'newest' => '2026-03-12 12:00:00',
			)
		);

		$properties = wp_get_ability( 'formidable-forms/list-entries' )->get_input_schema()['properties'];
		$defaults   = array(
			'order'     => $properties['order']['default'],
			'order_by'  => $properties['order_by']['default'],
			'page_size' => $properties['page_size']['default'],
		);

		$implicit = $this->execute( 'list-entries', array( 'form_id' => $this->form->id ) );
		$explicit = $this->execute( 'list-entries', array_merge( array( 'form_id' => $this->form->id ), $defaults ) );

		$this->assertNotWPError( $implicit );
		$this->assertSame(
			wp_list_pluck( $explicit, 'id' ),
			wp_list_pluck( $implicit, 'id' ),
			'Omitting order, order_by and page_size should behave like sending the schema defaults.'
		);
		$this->assertSame( 50, $defaults['page_size'], 'The page_size default should match the 50 the helper uses.' );
	}

	/**
	 * Create one entry per label with a fixed created_at.
	 *
	 * @param array $created Created at value (UTC) keyed by label.
	 *
	 * @return array<string, int> Entry id keyed by label.
	 */
	private function create_entries_created_at( $created ) {
		global $wpdb;

		$ids = array();

		foreach ( $created as $label => $date ) {
			$ids[ $label ] = (int) $this->factory->entry->create( array( 'form_id' => $this->form->id ) );
			$wpdb->update( $wpdb->prefix . 'frm_items', array( 'created_at' => $date ), array( 'id' => $ids[ $label ] ) );
		}

		FrmEntry::clear_cache();

		return $ids;
	}

	/**
	 * @return void
	 */
	public function test_permission_follows_the_frm_view_and_delete_entries_capabilities() {
		$entry_for_view   = $this->factory->entry->create( array( 'form_id' => $this->form->id ) );
		$entry_for_spam   = $this->factory->entry->create( array( 'form_id' => $this->form->id ) );
		$entry_for_delete = $this->factory->entry->create( array( 'form_id' => $this->form->id ) );

		$cases = array(
			'list-entries'    => array( 'frm_view_entries', array( 'form_id' => $this->form->id ) ),
			'get-entry'       => array( 'frm_view_entries', array( 'id' => $entry_for_view ) ),
			'mark-entry-spam' => array( 'frm_delete_entries', array( 'id' => $entry_for_spam ) ),
			'delete-entry'    => array( 'frm_delete_entries', array( 'id' => $entry_for_delete ) ),
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
