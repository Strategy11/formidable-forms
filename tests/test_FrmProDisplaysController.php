<?php

/**
 * @group views
 */
class WP_Test_FrmProDisplaysController extends FrmUnitTest {
	function setUp() {
		parent::setUp();

		// Set current userID to 1 so UserID filter will work in Views
		$this->set_current_user_to_1();
	}

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
	
	function test_view_reverse_compatibility() {
		$this->go_to_new_post();

		// Check that $display->frm_old_id still works
		$all_entries_view = get_posts( array(
            'name'          => 'all-entries',
            'post_type'     => 'frm_display',
            'post_status'   => 'any',
            'numberposts'   => 1,
		) );
		$all_entries_view = reset( $all_entries_view );
		$old_id = get_post_meta( $all_entries_view->ID, 'frm_old_id', true );
		$this->assertNotEmpty( $old_id, 'That view does not have an old ID.');

		$view = do_shortcode( '[display-frm-data id=' . $old_id . ']' );
		$result = strpos( $view, 'All Entries' ) !== false || strpos( $view, 'No Entries Found' ) !== false;
		$this->assertTrue( $result, 'View with old ID ' . $old_id . ' is not loading.');

		// Check that old single entry View settings ($display->frm_entry_id) still work
		$single_view = get_posts( array(
            'name'          => 'single-entry',
            'post_type'     => 'frm_display',
            'post_status'   => 'any',
            'numberposts'   => 1,
		) );
		$single_view = reset( $single_view );

		$form_id = get_post_meta( $single_view->ID, 'frm_form_id', true );

		$entry_data = $this->factory->field->generate_entry_array( $form_id );
		$entry = $this->factory->entry->create_and_get( $entry_data );
		$this->assertNotEmpty( $entry );
		update_post_meta( $single_view->ID, 'frm_entry_id', $entry->id );
		$single_view = do_shortcode( '[display-frm-data id=' . $single_view->ID . ']' );
		$single_result = strpos( $single_view, 'Favorite colors' );
		$this->assertTrue( $single_result !== false, 'Single entry View with old settings is not compatible with current version.');
	}
	
	function _test_detail_param(){
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
	function _test_all_entries_view_with_entry_param( $detail_type ){
		// Get all the entries in the form
		$where['form_key'] = 'all_field_types';
		$total_entries = count( FrmEntry::getAll( $where ) );

		// Get the All Entries View Content
		$all_entries_view = do_shortcode( '[display-frm-data id="all-entries"]' );
		$entry_num = substr_count( $all_entries_view, 'All Entries' );

		$this->assertTrue( $total_entries == $entry_num, 'All Entries View is affected by entry ' . $detail_type . ' parameter' );
	}
	
	function _test_dynamic_view_with_entry_param( $detail_type ) {
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

	/**
	 * Test View with a password set
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_password_view() {
		$password_view = self::get_view_by_key( 'all-entries-password' );
		$expected_content = array( 'This content is password protected. To view it please enter your password below:' );

		$d = self::get_default_args( $password_view, $expected_content, array() );

		self::run_get_display_data_tests( $d, 'view with password set' );

		self::run_frm_vars_test( 0 );
	}

	/**
	 * Tests Dynamic View, listing page no filters
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_no_filter_listing_view() {
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$expected_content = array( 'Jamie', 'Steph', 'Steve' );

		$d = self::get_default_args( $dynamic_view, $expected_content, array() );

		self::run_get_display_data_tests( $d, 'dynamic view with no filters' );

		self::run_frm_vars_test( 1 );
	}

	/**
	 * Tests View with Entry ID = 0 filter
	 * Meant to test what happens when frm_empty_msg is not set
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_no_entries_no_msg_view() {
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		unset( $dynamic_view->frm_empty_msg );

		// Add filter "Entry ID is equal to 0"
		$filter_args = array(
			array( 'type' => 'col', 'col' => 'id', 'op' => '=', 'val' => '0' ),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$expected_content = array( 'No Entries Found');

		$d = self::get_default_args( $dynamic_view, $expected_content, array() );

		self::run_get_display_data_tests( $d, 'dynamic view with entry_id=0 and no empty message' );
	}

	/**
	 * Test with no form ID selected
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_no_form_id_view() {
		$all_entries_view = self::get_view_by_key( 'all-entries' );
		$all_entries_view->frm_form_id = '';

		$d = array(
			'display' => $all_entries_view,
			'content' => 'No form selected',
			'entry_id' => false,
			'extra_atts' => array(),
			'expected_content' => array( 'No form selected' ),
			'not_in_content' => array(),
		);

		self::run_get_display_data_tests( $d, 'view with no form ID selected' );
	}

	/**
	 * Tests old single entry View with specific entry selected
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_single_entry_view_old_with_entry_id_selected() {
		$single_entry_view = self::get_view_by_key( 'single-entry' );
		$single_entry_view->frm_entry_id = FrmEntry::get_id_by_key( 'steph_entry_key' );

		$d = self::get_default_args( $single_entry_view, array( 'Steph' ), array( 'Jamie', 'Steve' ) );

		self::run_get_display_data_tests( $d, 'old single entry view with entry ID selected' );
	}

	/**
	 * Tests Dynamic View that should be on the detail page
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_dynamic_view_on_detail_page() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$_GET['detail'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );

		$not_in_content = array( 'Steph', 'Steve', 'href' );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), $not_in_content );

		self::run_get_display_data_tests( $d, 'dynamic view on detail page' );
	}

	/**
	 * Test Dynamic View that should be on the "Jamie" detail page, but an entry param is set for the Steph entry
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_dynamic_view_on_detail_page_with_extra_entry_param_set() {
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$_GET['detail'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );

		// Red herring
		$_GET['entry'] = FrmEntry::get_id_by_key( 'steph_entry_key' );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steph', 'Steve' ) );

		self::run_get_display_data_tests( $d, 'dynamic view on detail page with extra entry param set' );
	}

	/**
	 * Tests Dynamic View that should NOT be on the detail page but an 'entry' parameter is set
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_dynamic_view_not_detail_page() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		// Red herring
		$_GET['entry'] = FrmEntry::get_id_by_key( 'steph_entry_key' );

		$expected_content = array( 'Jamie', 'Steph', 'Steve', 'href' );
		$d = self::get_default_args( $dynamic_view, $expected_content, array() );

		self::run_get_display_data_tests( $d, 'dynamic view not on detail page with extra entry param set' );
	}

	/**
	 * Tests Single Entry View with an entry parameter set
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_single_view_with_entry_param() {
		self::clear_get_values();
		$single_entry_view = self::get_view_by_key( 'single-entry' );

		$_GET['entry'] = FrmEntry::get_id_by_key( 'steph_entry_key' );

		$d = self::get_default_args( $single_entry_view, array( 'Steph' ), array( 'Jamie', 'Steve' ) );

		self::run_get_display_data_tests( $d, 'single entry view with entry param set' );
	}

	/**
	 * Check if detail link is replaced with post link
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_detaillink_for_post_listing_view(){
		self::clear_get_values();
		$post_view = self::get_view_by_key( 'create-a-post-view' );

		$entry = FrmEntry::getOne( 'post-entry-1' );
		$post_id = $entry->post_id;

		$expected_content = array( '<a href="http://example.org/?p=' . $post_id . '">Jamie\'s Post</a>' );

		$d = self::get_default_args( $post_view, $expected_content, array() );

		self::run_get_display_data_tests( $d, 'detaillink for post listing view' );
	}

	/**
	 * Check if post content is filtered by linked View content
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_post_content_filtered_by_view(){
		self::clear_get_values();
		$post_view = self::get_view_by_key( 'create-a-post-view' );
		$post_view->frm_dyncontent = 'This is test content';
		// Saved post content: Hello! My name is Jamie. - Jamie Wahlin

		// Add entry key to extra_atts
		$extra_atts = array( 'auto_id' => 'post-entry-1' );

		// Set the global post to the correct post
		global $post;
		$entry = FrmEntry::getOne( 'post-entry-1' );
		$post = get_post( $entry->post_id );

		$d = self::get_default_args( $post_view, array( 'This is test content' ), array( 'Jamie' ), $extra_atts );
		$d['entry_id'] = 'post-entry-1';
		self::run_get_display_data_tests( $d, 'post with frm_display_id set' );

		// Add post ID filter - posts should not be affected by View filters
		$_GET['test'] = 12345;
		self::run_get_display_data_tests( $d, 'post with frm_display_id set and a filter' );
	}

	/**
	 * Tests with filter "Text field is equal to [get param=test]"
	 * test param is blank so all entries should be returned
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_empty_get_param_filter() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		// Add filter "Single Line Text is equal to [get param=test]"
		$filter_args = array(
			array( 'type' => 'field',
				'col' => '493ito',
				'op' => '=',
				'val' => '[get param=test]',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$expected_content = array( 'Jamie', 'Steph', 'Steve' );
		$d = self::get_default_args( $dynamic_view, $expected_content, array() );

		self::run_get_display_data_tests( $d, 'view with empty get param filter' );
	}

	/**
	 * Tests filter "Field is equal to [get param]"
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_view_with_get_param_filter() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		// Add filter "Single Line Text is equal to [get param=test]"
		$filter_args = array(
			array( 'type' => 'field',
				'col' => '493ito',
				'op' => '=',
				'val' => '[get param=test]',
			),
		);
		$_GET['test'] = 'Jamie';
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steph', 'Steve' ) );
		self::run_get_display_data_tests( $d, 'view with get param filter' );
	}

	/**
	 * Tests "Field is equal to value"
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_view_with_basic_field_filter() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		// Add filter "Single Line Text is equal to Jamie"
		$filter_args = array(
			array( 'type' => 'field',
				'col' => '493ito',
				'op' => '=',
				'val' => 'Jamie',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steph', 'Steve' ) );

		self::run_get_display_data_tests( $d, 'view with single field=value filter' );
	}

	/**
	 * Tests "Field is equal to shortcode"
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_view_with_shortcode_in_filter() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		// Add filter "Single Line Text is equal to [frm-field-value field_id=x entry=e_key]"
		//$text_field_id = FrmField::get_id_by_key( '493ito' );
		$filter_args = array(
			array( 'type' => 'field',
				'col' => '493ito',
				'op' => '=',
				'val' => '[frm-field-value field_id="493ito" entry="jamie_entry_key"]',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steph', 'Steve' ) );

		self::run_get_display_data_tests( $d, 'view with single field=shortcode filter' );
	}

	/**
	 * Tests "UserID is equal to current user"
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_user_id_is_equal_to_current_user_filter() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		// Add filter "UserID is equal to current user"
		$filter_args = array(
			array( 'type' => 'field',
				'col' => 't1eqkj',
				'op' => '=',
				'val' => 'current_user',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );
		$this->set_current_user_to_1();

		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steph', 'Steve' ) );

		self::run_get_display_data_tests( $d, 'view with user ID is equal to current user filter' );
	}

	/**
	 * Tests user_id=current parameter
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_user_id_is_equal_to_current_user_param() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		// Add user_id='current_user' parameter
		$this->set_current_user_to_1();
		$extra_atts = array( 'user_id' => 'current' );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steph', 'Steve' ), $extra_atts );

		self::run_get_display_data_tests( $d, 'view with user ID is equal to current user parameter' );
	}

	/**
	 * Tests user_id=2 parameter
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_user_id_is_equal_to_specific_user_param() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		// Create new user and set the current user to 1
		$this->factory->user->create_and_get( array( 'role' => 'subscriber' ) );
		$this->set_current_user_to_1();

		$extra_atts = array( 'user_id' => '2' );
		$d = self::get_default_args( $dynamic_view, array( 'Steph' ), array( 'Jamie', 'Steve' ), $extra_atts );

		self::run_get_display_data_tests( $d, 'view with user ID is equal to 2 parameter' );
	}

	/**
	 * Tests user_id=2 parameter with userID equals 1 parameter set
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_user_id_param_with_current_user_filter() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		// Create new user and set the current user to 1
		$this->factory->user->create_and_get( array( 'role' => 'subscriber' ) );
		$this->set_current_user_to_1();

		$extra_atts = array( 'user_id' => '2' );
		$filter_args = array(
			array( 'type' => 'field',
				'col' => 't1eqkj',
				'op' => '=',
				'val' => 'current_user',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );
		$d = self::get_default_args( $dynamic_view, array( 'Steph' ), array( 'Jamie', 'Steve' ), $extra_atts );

		self::run_get_display_data_tests( $d, 'view with user ID is equal to 2 parameter and "UserID equals current" filter' );
	}

	/**
	 * Tests user_id=2 parameter with userID equals 1 parameter set
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_user_id_param_with_filter() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		// Create new user and set the current user to 1
		$this->factory->user->create_and_get( array( 'role' => 'subscriber' ) );
		$this->set_current_user_to_1();

		$extra_atts = array( 'user_id' => '2' );
		$filter_args = array(
			array( 'type' => 'field',
				'col' => 't1eqkj',
				'op' => '=',
				'val' => '1',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );
		$d = self::get_default_args( $dynamic_view, array( 'Steph' ), array( 'Jamie', 'Steve' ), $extra_atts );

		self::run_get_display_data_tests( $d, 'view with user ID is equal to 2 parameter and "UserID equals 1" filter' );
	}

	/**
	 * Tests "Entry ID is equal to x,y,z"
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_entry_id_equal_to_list() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$entry_list = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$entry_list .= ',' . FrmEntry::get_id_by_key( 'steph_entry_key' );

		$filter_args = array(
			array( 'type' => 'col',
				'col' => 'id',
				'op' => '=',
				'val' => $entry_list,
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steph' ), array( 'Steve' ) );

		self::run_get_display_data_tests( $d, 'entry ID is equal to list filter' );
	}

	/**
	 * Tests "Entry ID is NOT equal to x,y,z," (with trailing comma')
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_entry_id_not_equal_to_list_filter() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$entry_list = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$entry_list .= ',' . FrmEntry::get_id_by_key( 'steph_entry_key' ) . ',';

		$filter_args = array(
			array( 'type' => 'col',
				'col' => 'id',
				'op' => '!=',
				'val' => $entry_list,
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Steve' ), array( 'Jamie', 'Steph' ) );

		self::run_get_display_data_tests( $d, 'view with entry ID is not equal to list filter' );
	}

	/**
	 * Tests "Field is NOT equal to value"
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_field_not_equal_to_value_filter() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$filter_args = array(
			array( 'type' => 'field',
				'col' => '493ito',
				'op' => '!=',
				'val' => 'Jamie',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Steve', 'Steph' ), array( 'Jamie' ) );

		self::run_get_display_data_tests( $d, 'field is NOT equal to value filter' );
	}

	/**
	 * Tests two field filters
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_two_field_filters() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$filter_args = array(
			array( 'type' => 'field',
				'col' => '493ito',
				'op' => '!=',
				'val' => 'Jamie',
			),
			array( 'type' => 'field',
				'col' => '493ito',
				'op' => 'LIKE',
				'val' => 'Stev',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Steve' ), array( 'Jamie', 'Steph' ) );

		self::run_get_display_data_tests( $d, 'two field filters' );
	}

	/**
	 * Test created_at is equal to specific value
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_created_at_filter() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$filter_args = array(
			array( 'type' => 'col',
				'col' => 'created_at',
				'op' => '=',
				'val' => '2015-05-13 19:30:23',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steve', 'Steph' ) );

		self::run_get_display_data_tests( $d, 'created at filter' );
	}

	/**
	 * Test created_at filter with field ID filter
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_created_at_with_field_id_filter() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$filter_args = array(
			array( 'type' => 'col',
				'col' => 'created_at',
				'op' => 'LIKE',
				'val' => '2015-05-13',
			),
			array( 'type' => 'field',
				'col' => '493ito',
				'op' => 'LIKE',
				'val' => 'Ste',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Steve', 'Steph' ), array( 'Jamie' ) );

		self::run_get_display_data_tests( $d, 'created_at with field ID filter' );
	}

	/**
	 * Test single entry ID filter
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_entry_id_filter() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$filter_args = array(
			array( 'type' => 'col',
				'col' => 'id',
				'op' => '=',
				'val' => FrmEntry::get_id_by_key( 'jamie_entry_key' ),
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steve', 'Steph' ) );

		self::run_get_display_data_tests( $d, 'entry ID is equal to x filter' );
	}

	/**
	 * Test entry_id=x parameter
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_entry_id_parameter() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'href' ), array( 'Steve', 'Steph' ) );
		$d['entry_id'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );

		self::run_get_display_data_tests( $d, 'entry_id=x parameter' );

		// Test with entry key
		$d['entry_id'] = 'jamie_entry_key';
		self::run_get_display_data_tests( $d, 'entry_id=entry_key parameter' );

		// Test with an added filter - entry_id should override all other filters
		$filter_args = array(
			array( 'type' => 'field',
				'col' => '493ito',
				'op' => '=',
				'val' => 'Steve',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array() );
		$d['entry_id'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );

		self::run_get_display_data_tests( $d, 'entry_id=x parameter with filter set' );

		// Test with an entry ID filter - entry_id should override all other filters
		$filter_args = array(
			array( 'type' => 'col',
				'col' => 'id',
				'op' => '=',
				'val' => '12345',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array() );
		$d['entry_id'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );

		self::run_get_display_data_tests( $d, 'entry_id=x parameter with entry ID filter set' );

	}

	/**
	 * Entry ID and field ID filter
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_entry_id_with_field_id_filter() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$filter_args = array(
			array( 'type' => 'col',
				'col' => 'id',
				'op' => '=',
				'val' => FrmEntry::get_id_by_key( 'jamie_entry_key' ) . ',' . FrmEntry::get_id_by_key( 'steph_entry_key' ),
			),
			array( 'type' => 'field',
				'col' => '493ito',
				'op' => '=',
				'val' => 'Steve',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'No Entries Found' ), array() );

		self::run_get_display_data_tests( $d, 'entry ID and field ID filter' );
	}

	/**
	 * Test Scale field is unique
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_unique_filter() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$filter_args = array(
			array( 'type' => 'field',
				'col' => 'qbrd2o',
				'op' => 'group_by',
				'val' => '',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steph' ), array( 'Steve' ) );

		self::run_get_display_data_tests( $d, 'unique filter' );
	}

	/**
	 * Test a detail page with filters set - entry should be displayed
	 * Test is detail page content is shown (not listing page content)
	 */
	function test_detail_page_with_filters() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$_GET['detail'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );

		$filter_args = array(
			array( 'type' => 'field',
				'col' => '493ito',
				'op' => 'LIKE',
				'val' => 'e',
			),
			array( 'type' => 'field',
				'col' => 'qbrd2o',
				'op' => '>',
				'val' => '3',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steph', 'Steve', 'href' ) );

		self::run_get_display_data_tests( $d, 'detail page with filters' );
	}

	/**
	 * Test a detail page with filters set - entry should NOT be displayed
	 */
	function test_detail_page_with_filters_no_match() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$_GET['detail'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );

		$filter_args = array(
			array( 'type' => 'field',
				'col' => '493ito',
				'op' => 'LIKE',
				'val' => 'Ste',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'No Entries Found' ), array( 'Jamie', 'Steph', 'Steve' ) );

		self::run_get_display_data_tests( $d, 'detail page with filters that prevent entry from showing' );
	}

	/**
	 * Test Dynamic View on the "Jamie" detail page, but with page size and limit set
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_detail_page_with_page_size_set() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_page_size = 100;
		$dynamic_view->frm_limit = 100;

		$_GET['detail'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steph', 'Steve' ) );

		self::run_get_display_data_tests( $d, 'dynamic view on detail page with page size and limit set' );
	}

	/**
	 * Test drafts in Views
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_drafts_in_view() {
		self::clear_get_values();

		// Create a draft entry in the all fields form
		$jamie_entry_id = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$new_id = FrmEntry::duplicate( $jamie_entry_id );
		global $wpdb;
		$wpdb->update( $wpdb->prefix . 'frm_items', array( 'is_draft' => 1 ), array( 'id' => $new_id ) );

		// Change text field value
		$field_id = FrmField::get_id_by_key( '493ito' );
		$wpdb->update( $wpdb->prefix . 'frm_item_metas', array( 'meta_value' => 'Celeste' ), array( 'item_id' => $new_id, 'field_id' => $field_id ) );

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		// No drafts should show by default
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steph', 'Steve', 'href' ), array( 'Celeste' ) );
		self::run_get_display_data_tests( $d, 'no drafts by default' );

		// Drafts and non-drafts should show with drafts=both
		$extra_atts['drafts'] = 'both';
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steph', 'Steve', 'Celeste', 'href' ), array(), $extra_atts );
		self::run_get_display_data_tests( $d, 'drafts=both parameter' );

		// Only drafts should show with drafts=1
		$extra_atts['drafts'] = '1';
		$d = self::get_default_args( $dynamic_view, array( 'Celeste', 'href' ), array( 'Jamie', 'Steph', 'Steve' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'drafts=1 parameter' );

		// No should show with drafts=0
		$extra_atts['drafts'] = '0';
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steph', 'Steve', 'href' ), array( 'Celeste' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'drafts=0 parameter' );
	}

	/**
	 * Test frm_search with View
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_frm_search_with_view(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		// Set search param
		$_GET['frm_search'] = 'Steve';

		$d = self::get_default_args( $dynamic_view, array( 'Steve' ), array( 'Steph', 'Jamie', 'Celeste' ) );

		self::run_get_display_data_tests( $d, 'view with frm_search param' );

		// Search only drafts this time
		global $post;
		$post = get_post( $dynamic_view->ID );
		$extra_atts = array( 'drafts' => '1' );
		$d = self::get_default_args( $dynamic_view, array( 'No Entries Found' ), array(), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with frm_search param and no drafts' );

	}

	/**
	 * Test limit on a View
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_limit_with_view(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_limit = 1;

		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steve', 'Steph' ) );

		self::run_get_display_data_tests( $d, 'view with limit set' );

		// See if limit param overrides limit setting
		$extra_atts = array( 'limit' => 100 );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steve', 'Steph' ), array(), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with limit set and limit param' );

		// See if limit param works on its own
		$extra_atts = array( 'limit' => 1 );
		$dynamic_view->frm_limit = 100;
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steph', 'Steve' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with limit param' );

	}


	/**
	 * Test page size on a View
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_page_size_with_view(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_page_size = 1;

		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steve', 'Steph' ) );

		self::run_get_display_data_tests( $d, 'view with page size set' );

		// See if page_size param overrides page size setting
		$extra_atts = array( 'page_size' => 100 );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steve', 'Steph' ), array(), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with page size set and page_size param' );

		// See if limit param works on its own
		$extra_atts = array( 'page_size' => 1 );
		$dynamic_view->frm_page_size = 100;
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steph', 'Steve' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with page size param' );

	}

	/**
	 * Test page 2 of a View
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_page_2_of_a_view(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_page_size = 1;
		$_GET['frm-page-'. $dynamic_view->ID] = 2;

		$d = self::get_default_args( $dynamic_view, array( 'Steph', 'frm_pagination_cont' ), array( 'Jamie', 'Steve' ) );

		self::run_get_display_data_tests( $d, 'view on page 2' );
	}

	/**
	 * Test limit with page size on a View
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_limit_with_page_size_on_view(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_page_size = 5;
		$dynamic_view->frm_limit = 1;

		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steve', 'Steph' ) );

		self::run_get_display_data_tests( $d, 'view with page size and limit set' );
	}

	/**
	 * Test View order - entry ID descending
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_entry_id_desc_order_in_view(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_page_size = 1;

		self::remove_view_order( $dynamic_view );
		$order_row = array(
			'type' => 'col',
			'col' => 'id',
			'dir' => 'DESC',
		);
		self::add_order_to_view( $dynamic_view, $order_row );

		$d = self::get_default_args( $dynamic_view, array( 'Steve', 'frm_pagination_cont' ), array( 'Jamie', 'Steph' ) );

		self::run_get_display_data_tests( $d, 'view with entry ID - DESC' );
	}

	/**
	 * Test View order - field ID ascending
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_field_id_asc_order_in_view(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_page_size = 1;

		self::remove_view_order( $dynamic_view );
		$order_row = array(
			'type' => 'field',
			'col' => '493ito',
			'dir' => 'ASC',
		);
		self::add_order_to_view( $dynamic_view, $order_row );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'frm_pagination_cont' ), array( 'Steve', 'Steph' ) );

		self::run_get_display_data_tests( $d, 'view with field ASC' );

		// Add order param
		$extra_atts = array( 'order_by' => 'id', 'order' => 'DESC' );
		$d = self::get_default_args( $dynamic_view, array( 'Steve', 'frm_pagination_cont' ), array( 'Jamie', 'Steph' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with field ASC and order=id order_by=DESC params' );
	}


	/**
	 * Make sure Before Content is shown on listing page
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_before_content_in_view(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_before_content = 'Before content';

		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Before content' ), array() );

		self::run_get_display_data_tests( $d, 'view with before content' );

		// Before content should not show on detail page
		$_GET['detail'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Before content' ) );
		self::run_get_display_data_tests( $d, 'view with before content on detail page' );
	}

	/**
	 * Test [evenodd] shortcode
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_evenodd_shortcode_in_view(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->post_content .= '[evenodd]';
		$dynamic_view->frm_limit = 1;

		$d = self::get_default_args( $dynamic_view, array( 'odd', 'Jamie' ), array( 'even' ) );

		self::run_get_display_data_tests( $d, 'view with [evenodd] shortcode' );
	}

	/**
	 * Test [entry_count] shortcode
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_entry_count_shortcode_in_view(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_before_content = 'Count: [entry_count]';

		$d = self::get_default_args( $dynamic_view, array( 'Count: 3', 'Jamie' ), array() );

		self::run_get_display_data_tests( $d, 'view with [entry_count] shortcode' );

		// Add page size
		$dynamic_view->frm_page_size = 1;
		$d = self::get_default_args( $dynamic_view, array( 'Count: 3', 'Jamie' ), array() );
		self::run_get_display_data_tests( $d, 'view with [entry_count] shortcode and page size' );
	}

	/**
	 * Make sure After Content is shown on listing page
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_after_content_in_view(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_after_content = 'After content';

		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'After content' ), array() );

		self::run_get_display_data_tests( $d, 'view with after content' );

		// Before content should not show on detail page
		$_GET['detail'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'After content' ) );
		self::run_get_display_data_tests( $d, 'view with after content on detail page' );
	}

	/**
	 * Make sure content is filtered when it is supposed to be
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_filtering_in_view(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		// No filter=1
		$extra_atts = array( 'filter' => 0 );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( '<br />'), $extra_atts );
		self::run_get_display_data_tests( $d, 'view without filter=1' );

		// Add filter=1
		$extra_atts = array( 'filter' => 1 );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', '<br />' ), array(), $extra_atts );
		self::run_get_display_data_tests( $d, 'view without filter=1' );
	}

	function clear_get_values(){
		$_GET = array();
	}

	function get_view_by_key( $view_key ) {
		$dynamic_view_id = FrmProDisplay::get_id_by_key( $view_key );
		return FrmProDisplay::getOne( $dynamic_view_id, false, true );
	}

	function add_filter_to_view( &$dynamic_view, $filter_args ) {
		foreach ( $filter_args as $where_row ) {
			if ( $where_row['type'] == 'field' ) {
				$dynamic_view->frm_where[] = FrmField::get_id_by_key($where_row['col']);
			} else {
				$dynamic_view->frm_where[] = $where_row['col'];
			}
			$dynamic_view->frm_where_is[] = $where_row['op'];
			$dynamic_view->frm_where_val[] = $where_row['val'];
		}
	}

	function remove_view_order( &$dynamic_view ) {
		$dynamic_view->frm_order_by = array();
		$dynamic_view->frm_order = array();
	}

	function add_order_to_view( &$dynamic_view, $order_row ) {
		if ( $order_row['type'] == 'field' ) {
			$dynamic_view->frm_order_by[] = FrmField::get_id_by_key($order_row['col']);
		} else {
			$dynamic_view->frm_order_by[] = $order_row['col'];
		}
		$dynamic_view->frm_order[] = $order_row['dir'];
	}

	function get_default_args( $view, $c, $n, $extra_atts = array() ) {
		$d = array(
			'display' => $view,
			'content' => '',
			'entry_id' => false,
			'extra_atts' => $extra_atts,
			'expected_content' => $c,
			'not_in_content' => $n,
		);
		return $d;
	}

	function run_get_display_data_tests( $d, $test_name ) {
		$content = FrmProDisplaysController::get_display_data( $d['display'], $d['content'], $d['entry_id'], $d['extra_atts'] );

		self::_test_view_content( $content, $test_name, $d );
	}

	function _test_view_content( $content, $test_name, $d ) {
		foreach ( $d['expected_content'] as $e ) {
			$this->assertContains( $e, $content, 'The ' . $test_name . ' is not getting the expected content.' );
		}

		foreach ( $d['not_in_content'] as $n ) {
			$this->assertNotContains( $n, $content, 'The ' . $test_name . ' is not getting the expected content.' );
		}
	}

	function run_frm_vars_test( $expected_count ) {
		global $frm_vars;

		$this->assertEquals( $expected_count, count( $frm_vars['forms_loaded'] ), 'frm_vars is not updated as expected with Views' );

		if ( $expected_count > 0 ) {
			$this->assertTrue( $frm_vars['forms_loaded'][ $expected_count - 1 ] === true, 'frm_vars is not updated as expected with Views' );
		}
	}
}