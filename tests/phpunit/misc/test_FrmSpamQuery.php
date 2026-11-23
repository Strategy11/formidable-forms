<?php

/**
 * @covers FrmSpamEntriesHelper
 */
class test_FrmSpamQuery extends FrmUnitTest {

	/**
	 * Applying exclusion twice preserves a single condition for either table alias.
	 */
	public function test_exclusion_is_idempotent() {
		foreach ( array( 'it.', '' ) as $prefix ) {
			$where    = array(
				array(
					'or'               => 1,
					'parent_form_id'   => null,
					'parent_form_id <' => 1,
				),
			);
			$excluded = FrmSpamEntriesHelper::exclude_spam( $where, $prefix );
			$this->assertCount( 2, $excluded );
			$this->assertSame( $excluded, FrmSpamEntriesHelper::exclude_spam( $excluded, $prefix ) );
		}
	}

	/**
	 * Exclusion inside an OR branch cannot protect the other branches from spam.
	 */
	public function test_or_branch_still_gets_outer_exclusion() {
		$exclusion = FrmSpamEntriesHelper::get_exclude_spam_where();
		$where     = array(
			'or'         => 1,
			'it.form_id' => 123,
			$exclusion,
		);
		$this->assertSame( array( $where, $exclusion ), FrmSpamEntriesHelper::exclude_spam( $where ) );
	}

	/**
	 * An OR group that only selects specific entries and their children, like the CSV export rows, keeps spam.
	 *
	 * @return void
	 */
	public function test_or_group_of_specific_entries_keeps_spam() {
		$targets = array(
			'or'             => 1,
			'id'             => array( 5, 6 ),
			'parent_item_id' => array( 5, 6 ),
		);
		$this->assertSame( $targets, FrmSpamEntriesHelper::exclude_spam( $targets, '' ) );

		// Parent zero selects every top-level entry, so it is not a specific selection.
		$top_level = array(
			'or'             => 1,
			'id'             => 5,
			'parent_item_id' => 0,
		);
		$this->assertCount( 2, FrmSpamEntriesHelper::exclude_spam( $top_level, '' ) );
	}

	/**
	 * Exporting selected spam entries returns their rows.
	 *
	 * @return void
	 */
	public function test_selected_spam_entries_load_for_export() {
		$form   = $this->factory->form->create();
		$parent = $this->factory->entry->create( array( 'form_id' => $form ) );
		$child  = $this->factory->entry->create(
			array(
				'form_id'        => $form,
				'parent_item_id' => $parent,
			)
		);
		$this->assertTrue( FrmSpamEntriesHelper::mark_as_spam( $parent ) );

		$entries = FrmEntry::getAll(
			array(
				'or'             => 1,
				'id'             => array( $parent ),
				'parent_item_id' => array( $parent ),
			),
			'',
			'',
			true,
			false
		);
		$this->assertEqualsCanonicalizing( array( $parent, $child ), array_map( 'intval', array_keys( $entries ) ) );
	}
}
