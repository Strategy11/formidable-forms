<?php

/**
 * @group entries
 * @group searching-entries
 */
class WP_Test_FrmProEntriesHelper extends FrmUnitTest {

	/**
	 * Test general searches that should return entries based on the frm_item_metas table values
	 * @covers FrmProEntriesHelper::get_search_str()
	 */
	function test_general_entries_search_on_frm_item_metas_single_word() {
		$form_id = FrmForm::getIdByKey( 'all_field_types' );
		$where_clause = array( 'it.form_id' => $form_id );

		// Single word is searched, matching entries should be returned
		$search_string = 'Wahlin';
		$items = self::run_search_query( $where_clause, $form_id, $search_string );
		$msg = 'A general search for ' . $search_string . ' in entry metas table';
		self::run_entries_found_tests( $msg, $items, 2, array( 'jamie_entry_key', 'jamie_entry_key_2' ) );

		// Single word is searched. Three matching entries should be found.
		$search_string = 'Ventura';
		$items = self::run_search_query( $where_clause, $form_id, $search_string );
		$msg = 'A general search for ' . $search_string . ' in entry metas table';
		self::run_entries_found_tests( $msg, $items, 3, array( 'steve_entry_key', 'jamie_entry_key', 'jamie_entry_key_2' ) );

		// Single word is searched, no matching entry should be found
		$search_string = 'StringThatWillNotBeFound';
		$items = self::run_search_query( $where_clause, $form_id, $search_string );
		$msg = 'A general search for ' . $search_string . ' in entry metas table';
		self::run_entries_not_found_tests( $msg, $items );
	}

	/**
	 * Test general searches that should return entries based on the frm_item_metas table values
	 * @covers FrmProEntriesHelper::get_search_str()
	 */
	function test_general_entries_search_on_frm_item_metas_multiple_words() {
		$form_id = FrmForm::getIdByKey( 'all_field_types' );
		$where_clause = array( 'it.form_id' => $form_id );

		// Multiple words are searched. Two matching entries should be found.
		$search_string = 'Wahlin http://www.stephtest.com';
		$items = self::run_search_query( $where_clause, $form_id, $search_string );
		$msg = 'A general search for ' . $search_string . ' in entry metas table';
		self::run_entries_found_tests( $msg, $items, 3, array( 'jamie_entry_key', 'steph_entry_key', 'jamie_entry_key_2' ) );

		// Multiple words are searched. One matching entry should be found.
		$search_string = 'Rebecca Wahlin';
		$items = self::run_search_query( $where_clause, $form_id, $search_string );
		$msg = 'A general search for ' . $search_string . ' in entry metas table';
		self::run_entries_found_tests( $msg, $items, 2, array( 'jamie_entry_key', 'jamie_entry_key_2' ) );

		// Multiple words are searched. No matching entries should be found.
		$search_string = 'StringOne StringTwo';
		$items = self::run_search_query( $where_clause, $form_id, $search_string );
		$msg = 'A general search for ' . $search_string . ' in entry metas table';
		self::run_entries_not_found_tests( $msg, $items );
	}

	/**
	 * Test general searches that should return entries based on the frm_item_metas table values
	 * This could fail if entry ID for California matches any other strings, like "2015"
	 *
	 * @covers FrmProEntriesHelper::get_search_str()
	 */
	function test_general_entries_search_on_dynamic_field_values() {
		// Single word is searched. One matching entry should be found.
		$search_string = 'Utah';
		$items = self::generate_and_run_search_query( 'all_field_types', $search_string );
		$msg = 'A general search for ' . $search_string . ' in entry metas table';
		self::run_entries_found_tests( $msg, $items, 1, array( 'steph_entry_key' ) );
	}

	/**
	 * Test general searches that should return entries based on the posts table values
	 * @covers FrmProEntriesHelper::get_search_str()
	 */
	function test_general_entries_search_on_post_title() {
		$search_string = "Jamie's";
		$items = self::generate_and_run_search_query( 'create-a-post', $search_string );
		$msg = 'A general search for ' . $search_string . ' in posts table';
		self::run_entries_found_tests( $msg, $items, 2, array( 'post-entry-1', 'post-entry-3' ) );
	}

	/**
	 * Test general searches that should return entries based on the posts table values
	 * @covers FrmProEntriesHelper::get_search_str()
	 */
	function test_general_entries_search_on_post_content() {
		$search_string = 'Different';
		$items = self::generate_and_run_search_query( 'create-a-post', $search_string );
		$msg = 'A general search for ' . $search_string . ' in posts table';
		self::run_entries_found_tests( $msg, $items, 1, array( 'post-entry-2' ) );
	}

	/**
	 * Test general searches that should return entries based on the frm_item_metas table values
	 * @covers FrmProEntriesHelper::get_search_str()
	 */
	function test_general_entries_search_on_frm_items_key() {
		$form_id = FrmForm::getIdByKey( 'all_field_types' );
		$where_clause = array( 'it.form_id' => $form_id );

		// Single word is searched, matching entry should be returned
		$search_string = 'jamie_entry_key';
		$items = self::run_search_query( $where_clause, $form_id, $search_string );
		$msg = 'A general search for ' . $search_string . ' in entries table';
		self::run_entries_found_tests( $msg, $items, 2, array( 'jamie_entry_key', 'jamie_entry_key_2' ) );

		// Single word is searched. Three matching entries should be found.
		$search_string = '_entry_key';
		$items = self::run_search_query( $where_clause, $form_id, $search_string );
		$msg = 'A general search for ' . $search_string . ' in entries table';
		self::run_entries_found_tests( $msg, $items, 4, array( 'steph_entry_key', 'steve_entry_key', 'jamie_entry_key', 'jamie_entry_key_2' ) );

		// Multiple words are searched. Two matching entries should be found.
		$search_string = 'jamie_entry_key steph_entry_key';
		$items = self::run_search_query( $where_clause, $form_id, $search_string );
		$msg = 'A general search for ' . $search_string . ' in entries table';
		self::run_entries_found_tests( $msg, $items, 3, array( 'jamie_entry_key', 'steph_entry_key', 'jamie_entry_key_2' ) );
	}

	/**
	 * Test general searches that should return entries based on the frm_item_metas table values
	 * @covers FrmProEntriesHelper::get_search_str()
	 */
	function test_general_entries_search_on_frm_items_created_at() {

		// Search created at column. One matching entry should be found.
		$search_string = '2015-05-12';
		$items = self::generate_and_run_search_query( 'all_field_types', $search_string );
		$msg = 'A general search for ' . $search_string . ' in entries table';
		self::run_entries_found_tests( $msg, $items, 1, array( 'jamie_entry_key' ) );
	}

	/**
	 * Test general searches that should return entries based on the frm_item_metas table values
	 * @covers FrmProEntriesHelper::get_search_str()
	 */
	function test_general_entries_search_on_frm_items_created_at_multiple_words() {
		$this->markTestSkipped( 'Functionality not yet added.' );

		// Search created at column. Three matching entries should be found.
		$search_string = '2015-05-13 unrelated_string';
		$items = self::generate_and_run_search_query( 'all_field_types', $search_string );
		$msg = 'A general search for ' . $search_string . ' in entries table';
		self::run_entries_found_tests( $msg, $items, 3, array( 'steph_entry_key', 'steve_entry_key' ) );
	}

	/**
	 * Test general searches that should return entries based on the frm_item_metas table values
	 * @covers FrmProEntriesHelper::get_search_str()
	 */
	function test_general_entries_search_on_frm_items_user_id() {
		$search_string = 'admin';
		$items = self::generate_and_run_search_query( 'all_field_types', $search_string );
		$msg = 'A general search for ' . $search_string . ' in entries table';
		self::run_entries_found_tests( $msg, $items, 4, array( 'steph_entry_key', 'steve_entry_key', 'jamie_entry_key', 'jamie_entry_key_2' ) );
	}

	/**
	 * Test a field-specific search that should return entries based on the frm_item_metas table
	 * @covers FrmProEntriesHelper::get_search_str()
	 */
	function test_field_specific_search_frm_item_metas() {

		// Single word. One matching entry should be found.
		$search_string = 'Jamie';
		$field_key = '493ito';
		$items = self::generate_and_run_field_specific_query( 'all_field_types', $field_key, $search_string );
		$msg = 'A search for ' . $search_string . ' in field ' . $field_key;
		self::run_entries_found_tests( $msg, $items, 1, array( 'jamie_entry_key' ) );

		// Multiple words. Two matching entries should be found.
		$search_string = 'Jamie Rebecca Wahlin';
		$field_key = 'p3eiuk';
		$items = self::generate_and_run_field_specific_query( 'all_field_types', $field_key, $search_string );
		$msg = 'A search for ' . $search_string . ' in field ' . $field_key;
		self::run_entries_found_tests( $msg, $items, 2, array( 'jamie_entry_key', 'jamie_entry_key_2' ) );

		// Single word. No matching entries should be found.
		$search_string = 'TextThatWillNotBeFound';
		$field_key = '493ito';
		$items = self::generate_and_run_field_specific_query( 'all_field_types', $field_key, $search_string );
		$msg = 'A search for ' . $search_string . ' in field ' . $field_key;
		self::run_entries_not_found_tests( $msg, $items );
	}

	/**
	 * Test a Dynamic field-specific search that should return entries based on the frm_item_metas table
	 * @covers FrmProEntriesHelper::get_search_str()
	 */
	function test_field_specific_search_on_dynamic_field() {

		// Single word. Two matching entries should technically be found, but there is an issue with importing
		// array values into Dynamic checkbox fields
		$search_string = 'California';
		$field_key = 'dynamic-state';
		$items = self::generate_and_run_field_specific_query( 'all_field_types', $field_key, $search_string );
		$msg = 'A search for ' . $search_string . ' in Dynamic field ' . $field_key;
		self::run_entries_found_tests( $msg, $items, 1, array( 'steve_entry_key' ) );

		// Entry ID. Two matching entries should technically be found, but there is an issue with importing
		// array values into Dynamic checkbox fields
		$search_string = FrmEntry::get_id_by_key( 'cali_entry' );
		$field_key = 'dynamic-state';
		$items = self::generate_and_run_field_specific_query( 'all_field_types', $field_key, $search_string );
		$msg = 'A search for ' . $search_string . ' in Dynamic field ' . $field_key;
		self::run_entries_found_tests( $msg, $items, 1, array( 'steve_entry_key' ) );

		// Single word. No entries should be found.
		$search_string = 'Utah';
		$field_key = 'dynamic-state';
		$items = self::generate_and_run_field_specific_query( 'all_field_types', $field_key, $search_string );
		$msg = 'A search for ' . $search_string . ' in Dynamic field ' . $field_key;
		self::run_entries_not_found_tests( $msg, $items );
	}

	/**
	 * Test a UserID field-specific search that should return entries based on the frm_item_metas table
	 * @covers FrmProEntriesHelper::get_search_str()
	 */
	function test_field_specific_search_on_user_id_field() {

		// Username. Three matching entries should be found.
		$search_string = 'admin';
		$items = self::generate_and_run_search_query( 'all_field_types', $search_string, 'user_id' );
		$msg = 'A search for ' . $search_string . ' in UserID field';
		self::run_entries_found_tests( $msg, $items, 2, array( 'jamie_entry_key', 'jamie_entry_key_2' ) );

		// UserID number. Three matching entries should be found.
		$search_string = '1';
		$items = self::generate_and_run_search_query( 'all_field_types', $search_string, 'user_id' );
		$msg = 'A search for ' . $search_string . ' in UserID field';
		self::run_entries_found_tests( $msg, $items, 2, array( 'jamie_entry_key', 'jamie_entry_key_2' ) );

		// UserID number. No matching entries should be found.
		$search_string = '7';
		$items = self::generate_and_run_search_query( 'all_field_types', $search_string, 'user_id' );
		$msg = 'A search for ' . $search_string . ' in UserID field';
		self::run_entries_not_found_tests( $msg, $items );
	}

	/**
	 * Test a Post Field field-specific search that should return entries based on the posts table
	 * @covers FrmProEntriesHelper::get_search_str()
	 */
	function test_field_specific_search_on_post_title() {

		// Single word. Two matching entries should be found.
		$search_string = "Jamie's";
		$field_key = 'yi6yvm';
		$items = self::generate_and_run_field_specific_query( 'create-a-post', $field_key, $search_string );
		$msg = 'A search for ' . $search_string . ' in post title field ' . $field_key;
		self::run_entries_found_tests( $msg, $items, 2, array( 'post-entry-1', 'post-entry-3' ) );

		// Single word. No entries should be found.
		$search_string = 'TextThatShouldNotBeFound';
		$items = self::generate_and_run_field_specific_query( 'create-a-post', $field_key, $search_string );
		$msg = 'A search for ' . $search_string . ' in post title field ' . $field_key;
		self::run_entries_not_found_tests( $msg, $items );
	}

	/**
	 * Test a frm_items column-specific search
	 * @covers FrmProEntriesHelper::get_search_str()
	 */
	function test_field_specific_search_on_entry_id() {
		$jamie_id = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$steph_id = FrmEntry::get_id_by_key( 'steph_entry_key' );

		// Single ID. One matching entry should be found.
		$search_string = $jamie_id;
		$items = self::generate_and_run_search_query( 'all_field_types', $search_string, 'id' );
		$msg = 'A search for ' . $search_string . ' in entry ID column';
		self::run_entries_found_tests( $msg, $items, 1, array( 'jamie_entry_key' ) );

		// Two IDs. Two entries should be found.
		$search_string = $jamie_id . ' ' . $steph_id;
		$items = self::generate_and_run_search_query( 'all_field_types', $search_string, 'id' );
		$msg = 'A search for ' . $search_string . ' in entry ID column';
		self::run_entries_found_tests( $msg, $items, 2, array( 'jamie_entry_key', 'steph_entry_key' ) );

		// Two IDs separated with comma and space. Two entries should be found.
		$search_string = $jamie_id . ', ' . $steph_id;
		$items = self::generate_and_run_search_query( 'all_field_types', $search_string, 'id' );
		$msg = 'A search for ' . $search_string . ' in entry ID column';
		self::run_entries_found_tests( $msg, $items, 2, array( 'jamie_entry_key', 'steph_entry_key' ) );

		// Two IDs separated with comma. Two entries should be found.
		$search_string = $jamie_id . ',' . $steph_id;
		$items = self::generate_and_run_search_query( 'all_field_types', $search_string, 'id' );
		$msg = 'A search for ' . $search_string . ' in entry ID column';
		self::run_entries_found_tests( $msg, $items, 2, array( 'jamie_entry_key', 'steph_entry_key' ) );

		// Fake ID. No entry should be found
		$search_string = 'nalsndo83';
		$items = self::generate_and_run_search_query( 'all_field_types', $search_string, 'id' );
		$msg = 'A search for ' . $search_string . ' in entry ID column';
		self::run_entries_not_found_tests( $msg, $items );
	}

	/**
	 * Test a frm_items column-specific search
	 * @covers FrmProEntriesHelper::get_search_str()
	 */
	function test_field_specific_search_on_creation_date() {

		// Y-m-d. Two matching entries should be found.
		$search_string = '2015-05-13';
		$items = self::generate_and_run_search_query( 'all_field_types', $search_string, 'created_at' );
		$msg = 'A search for ' . $search_string . ' in creation date column';
		self::run_entries_found_tests( $msg, $items, 3, array( 'steph_entry_key', 'steve_entry_key', 'jamie_entry_key_2' ) );

		// Y-m-d H:i:s. One matching entry should be found.
		$search_string = '2015-05-12 19:30';
		$items = self::generate_and_run_search_query( 'all_field_types', $search_string, 'created_at' );
		$msg = 'A search for ' . $search_string . ' in creation date column';
		self::run_entries_found_tests( $msg, $items, 1, array( 'jamie_entry_key' ) );
	}

	/**
	 * Test a frm_items column-specific search
	 * @covers FrmProEntriesHelper::get_search_str()
	 */
	function test_field_specific_search_on_creation_date_multiple_words() {
		$this->markTestSkipped( 'Functionality not yet added.' );

		// Y-m-d H:i:s. Two matching entries should be found.
		$search_string = '2015-05-13 10:10:10';
		$items = self::generate_and_run_search_query( 'all_field_types', $search_string, 'created_at' );
		$msg = 'A search for ' . $search_string . ' in creation date column';
		self::run_entries_found_tests( $msg, $items, 3, array( 'steph_entry_key', 'steve_entry_key' ) );
	}

	function run_search_query( $where_clause, $form_id, $search_string ) {
		$search_query = FrmProEntriesHelper::get_search_str( $where_clause, $search_string, $form_id, '' );
		return FrmEntry::getAll( $search_query, '', '', true, false );
	}

	function generate_and_run_search_query( $form_key, $search_string, $field_id = 0 ) {
		$form_id = FrmForm::getIdByKey( $form_key );
		$where_clause = array( 'it.form_id' => $form_id );
		$search_query = FrmProEntriesHelper::get_search_str( $where_clause, $search_string, $form_id, $field_id );
		return FrmEntry::getAll( $search_query, '', '', true, false );
	}

	function generate_and_run_field_specific_query( $form_key, $field_key, $search_string ) {
		$field_id = FrmField::get_id_by_key( $field_key );
		return self::generate_and_run_search_query( $form_key, $search_string, $field_id );
	}

	function run_entries_found_tests( $msg, $items, $expected_count, $expected_keys ) {
		$this->assertNotEmpty( $items, $msg . ' is not returning entries.' );
		$this->assertEquals( $expected_count, count( $items ), $msg . ' is not returning the correct number of entries.' );

		foreach ( $items as $item ) {
			$this->assertContains( $item->item_key, $expected_keys,  $msg . ' is not returning the correct entries.' );
		}
	}

	function run_entries_not_found_tests( $msg, $items ) {
		$this->assertEmpty( $items, $msg . ' is returning entries.' );
	}
}