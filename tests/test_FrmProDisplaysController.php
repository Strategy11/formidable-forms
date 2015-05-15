<?php

class WP_Test_FrmProDisplaysController extends FrmUnitTest {
	function test_pro_version_active() {
		$is_pro_active = FrmAppHelper::pro_is_installed();
		$this->assertTrue( $is_pro_active, 'The pro version is not active.' );
	}

	/**
	* @covers FrmProDisplaysController::register_post_types
	*/
	function test_register_post_types() {
		$post_types = get_post_types();
		$post_type = FrmProDisplaysController::$post_type;
		$this->assertTrue( in_array( $post_type, $post_types ), 'The ' . $post_type . ' post type is missing' );
	}
	
	function setup(){
		// Set current userID to 1 so UserID filter will work in Views
		$this->set_current_user_to_1();
	}
	
	function test_view_reverse_compatibility() {
		// Check that $display->frm_old_id still works
		$all_entries_view = get_page_by_title( 'All Entries', OBJECT, 'frm_display' );
		echo "post ID: " . $all_entries_view;
		get_post_meta( $all_entries_view->ID, 'frm_old_id', true );
		$view = do_shortcode( '[display-frm-data id=1]' );
		$result = strpos( $view, 'All Entries' );
		$this->assertTrue( $result, 'View with old ID is not loading.');

		// Check that old single entry View settings ($display->frm_entry_id) still work
		$single_view = get_page_by_title( 'Single Entry', OBJECT, 'frm_display' );
		update_post_meta( $single_view->ID, 'frm_entry_id', 60417 );
		$single_view = do_shortcode( '[display-frm-data id=' . $single_id . ']' );
		$single_result = strpos( $single_view, 'Jamie' );
		$this->assertTrue( $single_result, 'Single entry View with old settings is not compatible with current version.');
	}
	
	/*function test_detail_param(){
		// Dynamic
		global $_GET;

		// Check keys
		$_GET['entry'] = $_GET['detail'] = 'thx9u15';

		self::test_all_entries_view_with_entry_param( 'key' );
		self::test_dynamic_view_with_entry_param( 'key' );
		self::test_calendar_view_with_entry_param( 'key' );
		self::test_single_view_with_entry_param( 'key' );
		
		$dynamic_view = get_page_by_title( 'Single Entry', OBJECT, 'frm_display' );
		update_post_meta( $single_view->ID, 'frm_entry_id', 60417 );

		// Check IDs
		$_GET['entry'] = $_GET['detail'] = 60422;

		$view = do_shortcode( '[display-frm-data id="dynamic-view"]' );
		
		// Calendar
		$view = do_shortcode( '[display-frm-data id="all-entries"]' );
		
		// Single
		$view = do_shortcode( '[display-frm-data id="all-entries"]' );
	}

	/**
	* Make sure all entries are still retrieved with All Entries View even if entry parameter is in the URL
	*/
	/*function test_all_entries_view_with_entry_param( $detail_type ){
		// Get all the entries in the form
		$where['form_key'] = 'all_field_types';
		$total_entries = count( FrmEntry::getAll( $where ) );

		// Get the All Entries View Content
		$all_entries_view = do_shortcode( '[display-frm-data id="all-entries"]' );
		$entry_num = substr_count( $all_entries_view, 'All Entries' );

		$this->assertTrue( $total_entries == $entry_num, 'All Entries View is affected by entry ' . $detail_type . ' parameter' );
	}
	
	function test_dynamic_view_with_entry_param( $detail_type ) {
		if ( $detail_type == 'key' ) {
			$col_key = 'display_key';
		} else {
			$col_key = 'id';
		}

		// Set the detail page slug to use the entry key or ID
		$dynamic_view = get_page_by_title( 'Dynamic View', OBJECT, 'frm_display' );
		update_post_meta( $dynamic_view->ID, 'frm_type', $col_key );

		$dynamic_view_content = do_shortcode( '[display-frm-data id="dynamic-view"]' );

		$is_showing_list_page = strpos( $dynamic_view_content, 'Listing Page' );
		$this->assertTrue( $is_showing_list_page === false, 'Dynamic view with entry ' . $detail_type . ' parameter is showing listing page instead of detail page.' );

		$number_of_entries = substr_count( $dynamic_view_content, 'Favorite dessert' );
		$this->assertTrue( $number_of_entries == 1, 'Dynamic view with entry ' . $detail_type . 'parameter is showing more than one entry.');

		$is_showing_correct_entry = strpos( $dynamic_view_content, 'Steph' );
		$this->assertTrue( $is_showing_correct_entry !== false, 'Dynamic view with entry' . $detail_type . ' parameter is getting the wrong entry.');
	}
	
	function test_where_val(){
		
	}*/
}