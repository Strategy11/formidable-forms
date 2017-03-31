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
	 * Tests Dynamic View, listing page no filters, shortcode in content
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_no_filter_listing_view_with_shortcode() {
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$this->set_current_user_to_1();
		$dynamic_view->post_content .= 'user_id:[user_id]';
		$expected_content = array( 'Jamie', 'Steph', 'Steve', 'user_id:1' );

		$d = self::get_default_args( $dynamic_view, $expected_content, array() );

		self::run_get_display_data_tests( $d, 'dynamic view with no filters and shortcode' );
	}

	/**
	 * Tests Single Entry View, no filters, shortcode in content
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_no_filter_detail_view_with_shortcode() {
		$single_view = self::get_view_by_key( 'single-entry' );
		$this->set_current_user_to_1();
		$single_view->post_content .= 'user_id:[user_id]';
		$expected_content = array( 'Jamie', 'user_id:1' );

		$d = self::get_default_args( $single_view, $expected_content, array() );

		self::run_get_display_data_tests( $d, 'single entry view with no filters and shortcode' );
	}

	/**
	 * Set up a single entry View with an entry ID filter
	 *
	 * @param $filter_value
	 * @return object
	 */
	function _set_up_single_view_with_filter( $filter_value ) {
		$single_view = self::get_view_by_key( 'single-entry' );

		// Add View filter
		$filter_args = array(
			array( 'type' => 'col',
				'col' => 'id',
				'op' => '=',
				'val' => $filter_value,
			),
		);
		self::add_filter_to_view( $single_view, $filter_args );

		return $single_view;
	}

	/**
	 * Tests Single Entry View with [get param=test] filter
	 * Test parameter contains a valid entry ID
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_detail_view_with_get_param_test_filter() {
		$single_view = self::_set_up_single_view_with_filter( '[get param=test]' );

		// Set valid entry ID parameter
		$_GET['test'] = FrmEntry::get_id_by_key( 'steph_entry_key' );

		$expected_content = array( 'Name: Steph' );
		$d = self::get_default_args( $single_view, $expected_content, array() );
		self::run_get_display_data_tests( $d, 'single entry view with Entry ID equals get param=test filter' );
	}

	/**
	 * Tests Single Entry View with [get param=test] filter
	 * Test parameter contains an invalid entry ID
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_detail_view_with_get_param_test_filter_invalid_value() {
		$single_view = self::_set_up_single_view_with_filter( '[get param=test]' );

		// Set invalid entry ID parameter
		$_GET['test'] = FrmEntry::get_id_by_key( 'i0xioc' );

		$expected_content = array( 'No Entries Found' );
		$d = self::get_default_args( $single_view, $expected_content, array() );
		self::run_get_display_data_tests( $d, 'single entry view with Entry ID equals get param=test filter, invalid entry ID set in URL' );
	}

	/**
	 * Tests Single Entry View with [get param=test] filter
	 * Test parameter contains an entry key
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_detail_view_with_get_param_test_filter_entry_key() {
		$single_view = self::_set_up_single_view_with_filter( '[get param=test]' );

		// Set entry key parameter
		$_GET['test'] = 'steph_entry_key';

		$expected_content = array( 'No Entries Found' );
		$d = self::get_default_args( $single_view, $expected_content, array() );
		self::run_get_display_data_tests( $d, 'single entry view with Entry ID equals get param=test filter, invalid entry ID set in URL' );
	}

	/**
	 * Tests Single Entry View with [get param=entry old_filter=1] filter
	 * Entry parameter contains a valid entry ID
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_detail_view_with_get_param_entry_old_filter_valid_id() {
		$single_view = self::_set_up_single_view_with_filter( '[get param=entry old_filter=1]' );

		// Set valid entry ID parameter
		$_GET['entry'] = FrmEntry::get_id_by_key( 'steph_entry_key' );

		$expected_content = array( 'Name: Steph' );
		$d = self::get_default_args( $single_view, $expected_content, array() );
		self::run_get_display_data_tests( $d, 'single entry view with Entry ID equals get param=entry filter, valid entry ID set in URL' );
	}

	/**
	 * Tests Single Entry View with [get param=entry old_filter=1] filter
	 * Entry parameter contains an invalid entry ID
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_detail_view_with_get_param_entry_old_filter_invalid_id() {
		$single_view = self::_set_up_single_view_with_filter( '[get param=entry old_filter=1]' );

		// Set invalid entry ID parameter
		$_GET['entry'] = FrmEntry::get_id_by_key( 'i0xioc' );

		$expected_content = array( 'Name: Jamie' );
		$d = self::get_default_args( $single_view, $expected_content, array() );
		self::run_get_display_data_tests( $d, 'single entry view with Entry ID equals get param=entry old_filter=1 filter, invalid entry ID set in URL' );
	}

	/**
	 * Tests Single Entry View with [get param=entry old_filter=1] filter
	 * entry parameter contains entry key
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_detail_view_with_get_param_entry_old_filter_entry_key() {
		$single_view = self::_set_up_single_view_with_filter( '[get param=entry old_filter=1]' );

		// Set invalid entry ID parameter
		$_GET['entry'] = 'steph_entry_key';

		$expected_content = array( 'Name: Steph' );
		$d = self::get_default_args( $single_view, $expected_content, array() );
		self::run_get_display_data_tests( $d, 'single entry view with Entry ID equals get param=entry old_filter=1 filter, entry keys separated by comma' );
	}

	/**
	 * Tests Single Entry View with [get param=entry old_filter=1] and [get param=test] filter
	 * Both parameters contain valid IDs
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_detail_view_with_multiple_get_param_entry_filters() {
		$single_view = self::_set_up_single_view_with_filter( '[get param=entry old_filter=1]' );

		// Add second filter
		$filter_args = array(
			array( 'type' => 'col',
				'col' => 'id',
				'op' => '=',
				'val' => '[get param=test]',
			),
		);
		self::add_filter_to_view( $single_view, $filter_args );

		// Set two different entry parameters
		$_GET['entry'] = FrmEntry::get_id_by_key( 'steph_entry_key' );
		$_GET['test'] = FrmEntry::get_id_by_key( 'steve_entry_key' );

		$expected_content = array( 'No Entries Found' );
		$d = self::get_default_args( $single_view, $expected_content, array() );
		self::run_get_display_data_tests( $d, 'single entry view with two Entry ID filters' );
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

		// This was for old functionality
		//$d = self::get_default_args( $single_entry_view, array( 'Steph' ), array( 'Jamie', 'Steve' ) );

		// This is for new functionality
		$d = self::get_default_args( $single_entry_view, array( 'Jamie' ), array( 'Steph', 'Steve' ) );

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

		$d = self::get_default_args( $post_view, $expected_content, array( '?entry=') );

		self::run_get_display_data_tests( $d, 'detaillink for post listing view' );
	}

	/**
	 * Check if post content is filtered by linked View content
	 * Uses entry key
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_post_content_filtered_by_view_use_entry_key(){
		self::clear_get_values();
		$post_view = self::get_view_by_key( 'create-a-post-view' );
		$post_field_id = FrmField::get_id_by_key( 'yi6yvm' );
		$regular_field_id = FrmField::get_id_by_key( 'knzfvv' );
		$post_view->frm_dyncontent = 'This is my test content: [' . $post_field_id . '], [' . $regular_field_id . ']';
		// Saved post content: Hello! My name is Jamie. - Jamie Wahlin

		// Set the global post to the correct post
		global $post;
		$entry = FrmEntry::getOne( 'post-entry-1' );
		$post = get_post( $entry->post_id );

		// Add auto_id=entry key in extra_atts
		$extra_atts = array( 'auto_id' => 'post-entry-1' );

		$d = self::get_default_args( $post_view, array( 'This is my test content', 'Jamie\'s Post', 'Hello! My name is Jamie.' ), array(), $extra_atts );
		$d['entry_id'] = 'post-entry-1';
		self::run_get_display_data_tests( $d, 'post with frm_display_id set (passing entry key)' );
	}

	/**
	 * Check if post content is filtered by linked View content
	 * Uses entry ID
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_post_content_filtered_by_view_use_entry_id(){
		self::clear_get_values();
		$post_view = self::get_view_by_key( 'create-a-post-view' );
		$post_field_id = FrmField::get_id_by_key( 'yi6yvm' );
		$regular_field_id = FrmField::get_id_by_key( 'knzfvv' );
		$post_view->frm_dyncontent = 'This is my test content: [' . $post_field_id . '], [' . $regular_field_id . ']';
		// Saved post content: Hello! My name is Jamie. - Jamie Wahlin

		// Set the global post to the correct post
		global $post;
		$entry = FrmEntry::getOne( 'post-entry-1' );
		$post = get_post( $entry->post_id );

		// Check auto_id=entry ID in extra atts
		$extra_atts = array( 'auto_id' => $entry->id );

		$d = self::get_default_args( $post_view, array( 'This is my test content', 'Jamie\'s Post', 'Hello! My name is Jamie.' ), array(), $extra_atts );
		$d['entry_id'] = $entry->id;
		self::run_get_display_data_tests( $d, 'post with frm_display_id set (passing entry ID)' );
	}

	/**
	 * Check if single post is affected by View filters
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_single_post_with_view_filter(){
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

		// Add post ID filter - posts should not be affected by View filters
		$_GET['test'] = 12345;
		$d = self::get_default_args( $post_view, array( 'This is test content' ), array( 'Jamie' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'post with frm_display_id set and a filter' );
	}


	/**
	 * Check if post listing View gets the correct content
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_post_listing_view_content(){
		self::clear_get_values();
		$post_view = self::get_view_by_key( 'create-a-post-view' );

		// Check for the correct content
		$d = self::get_default_args( $post_view, array( 'href', 'Jamie\'s Post', 'Dragon\'s Post'), array() );
		self::run_get_display_data_tests( $d, 'post listing View' );
	}

	/**
	 * Check if post listing View gets the correct content with a post ID filter
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_post_listing_view_with_post_id_filter(){
		self::clear_get_values();
		$post_view = self::get_view_by_key( 'create-a-post-view' );

		// Set up the post ID filter
		$entry = FrmEntry::getOne( 'post-entry-1' );
		$_GET['test'] = $entry->post_id;

		// Check for the correct content
		$d = self::get_default_args( $post_view, array( 'href', 'Jamie\'s Post', 'ID: ' . $entry->post_id ), array() );
		self::run_get_display_data_tests( $d, 'post listing View with post ID filter' );
	}

	/**
	 * Check post listing View with field ID (post field) filter
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_post_listing_view_with_post_field_id_filter(){
		self::clear_get_values();
		$post_view = self::get_view_by_key( 'create-a-post-view' );

		// Add filter "Post Title is like Jamie"
		$filter_args = array(
			array( 'type' => 'field',
				'col' => 'yi6yvm',
				'op' => 'LIKE',
				'val' => 'Dragon',
			),
		);
		self::add_filter_to_view( $post_view, $filter_args );

		// Check for the correct content
		$d = self::get_default_args( $post_view, array( 'href', 'Dragon\'s Post' ), array( 'Jamie' ) );
		self::run_get_display_data_tests( $d, 'post listing View with post field ID filter' );
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

		// Check listing page
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steph', 'Steve' ) );
		self::run_get_display_data_tests( $d, 'view with user ID is equal to current user filter' );

		// Check detail page - entry should show up
		$_GET['detail'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$d['display'] = self::reset_view( 'dynamic-view', $filter_args );
		self::run_get_display_data_tests( $d, 'view with user ID is equal to current user filter on detail page' );

		// Check detail page - entry should NOT show up
		$_GET['detail'] = FrmEntry::get_id_by_key( 'steph_entry_key' );
		$dynamic_view = self::reset_view( 'dynamic-view', $filter_args );
		$d = self::get_default_args( $dynamic_view, array( 'No Entries Found' ), array( 'Steph' ) );
		self::run_get_display_data_tests( $d, 'view with user ID is equal to current user filter on detail page (entry should NOT show)' );

	}

	/**
	 * Tests user_id=current parameter
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_user_id_is_equal_to_current_user_param() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$this->set_current_user_to_1();

		// Add user_id='current' parameter
		$extra_atts = array( 'user_id' => 'current' );

		// Check listing page
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steph', 'Steve' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with user ID is equal to current user parameter' );

		// Check detail page - entry should show up
		$_GET['detail'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$d['display'] = self::reset_view( 'dynamic-view' );
		self::run_get_display_data_tests( $d, 'view with user_id=current param on detail page' );

		// Check detail page - entry should NOT show up
		$_GET['detail'] = FrmEntry::get_id_by_key( 'steph_entry_key' );
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$d = self::get_default_args( $dynamic_view, array( 'No Entries Found' ), array( 'Steph' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with user_id=current param on detail page (entry should NOT show)' );

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

		// Check listing page
		$d = self::get_default_args( $dynamic_view, array( 'Steph' ), array( 'Jamie', 'Steve' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with user ID is equal to 2 parameter' );

		// Check detail page - entry should show up
		$_GET['detail'] = FrmEntry::get_id_by_key( 'steph_entry_key' );
		$d['display'] = self::reset_view( 'dynamic-view' );
		self::run_get_display_data_tests( $d, 'view with user_id=2 param on detail page' );

		// Check detail page - entry should NOT show up
		$_GET['detail'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$d = self::get_default_args( $dynamic_view, array( 'No Entries Found' ), array( 'Steph', 'Jamie' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with user_id=2 param on detail page (entry should NOT show)' );
	}

	/**
	 * Tests user_id=2 parameter with userID equals current filter set
	 * user_id=2 parameter should override a "current user" filter
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

		// Check listing page
		$d = self::get_default_args( $dynamic_view, array( 'Steph' ), array( 'Jamie', 'Steve' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with user ID is equal to 2 parameter and "UserID equals current" filter' );

		// Check detail page - entry should show up
		$_GET['detail'] = FrmEntry::get_id_by_key( 'steph_entry_key' );
		$d['display'] = self::reset_view( 'dynamic-view', $filter_args );
		self::run_get_display_data_tests( $d, 'view with user_id=2 param and userID equals current filter on detail page' );

		// Check detail page - entry should NOT show up
		$_GET['detail'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$dynamic_view = self::reset_view( 'dynamic-view', $filter_args );
		$d = self::get_default_args( $dynamic_view, array( 'No Entries Found' ), array( 'Steph', 'Jamie' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with user_id=2 and userID equals current filter on detail page (entry should NOT show)' );

	}

	/**
	 * Tests user_id=2 parameter with userID equals 1 filter set
	 * user_id=2 should override userID equals 1 filter
	 * Both the parameter and the filter will be used
	 *
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

		// Test listing page
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

		// Check listing page
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steph' ), array( 'Steve' ) );
		self::run_get_display_data_tests( $d, 'entry ID is equal to list filter' );

		// Check detail page - entry should show up
		$_GET['detail'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$dynamic_view = self::reset_view( 'dynamic-view', $filter_args );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steve', 'Steph' ) );
		self::run_get_display_data_tests( $d, 'view with entry ID equal to list filter on detail page' );

		// Check detail page - entry should NOT show up
		$_GET['detail'] = FrmEntry::get_id_by_key( 'steve_entry_key' );
		$dynamic_view = self::reset_view( 'dynamic-view', $filter_args );
		$d = self::get_default_args( $dynamic_view, array( 'No Entries Found' ), array() );
		self::run_get_display_data_tests( $d, 'view with entry ID equal to list filter on detail page (entry should NOT show)' );

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
	 * Tests two field filters - detail page of View
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_two_field_filters_on_detail_page() {
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

		// Entry should show up
		$_GET['detail'] = FrmEntry::get_id_by_key( 'steve_entry_key' );
		$d = self::get_default_args( $dynamic_view, array( 'Steve' ), array( 'Jamie', 'Steph' ) );
		self::run_get_display_data_tests( $d, 'two field filters on detail page of View' );

		// No entries should show up
		$_GET['detail'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$dynamic_view = self::reset_view( 'dynamic-view', $filter_args );
		$d = self::get_default_args( $dynamic_view, array( 'No Entries Found' ), array() );
		self::run_get_display_data_tests( $d, 'two field filters on detail page of View (entry should not show)' );
	}

	function reset_view( $view_key, $filter_args = array() ) {
		$view = self::get_view_by_key( $view_key );
		if ( ! empty( $filter_args ) ) {
			self::add_filter_to_view( $view, $filter_args );
		}

		return $view;
	}

	/**
	 * Test created_at is equal to specific value
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_created_at_filter_with_specific_date() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$filter_args = array(
			array( 'type' => 'col',
				'col' => 'created_at',
				'op' => '=',
				'val' => '2015-05-12 19:30:23',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steve', 'Steph' ) );
		self::run_get_display_data_tests( $d, 'created at filter' );

		// Check detail page - should show entry
		$_GET['detail'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$dynamic_view = self::reset_view( 'dynamic-view', $filter_args );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steve', 'Steph' ) );
		self::run_get_display_data_tests( $d, 'created at filter on detail page' );

		// Check detail page - should NOT show entry
		$_GET['detail'] = FrmEntry::get_id_by_key( 'steph_entry_key' );
		$dynamic_view = self::reset_view( 'dynamic-view', $filter_args );
		$d = self::get_default_args( $dynamic_view, array( 'No Entries Found' ), array( 'Jamie', 'Steve', 'Steph' ) );
		self::run_get_display_data_tests( $d, 'created at filter on detail page (should not show entry)' );
	}

	/**
	 * Test created_at is less than NOW
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_created_at_filter_with_now() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$filter_args = array(
			array( 'type' => 'col',
				'col' => 'created_at',
				'op' => '<',
				'val' => 'NOW',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steve', 'Steph' ), array() );

		self::run_get_display_data_tests( $d, 'created at filter' );
	}

	/**
	 * Test created_at is greater than -1 day
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_created_at_filter_with_minus_one_day() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		// Update creation date on Steve's entry to NOW
		global $wpdb;
		$now = date( 'Y-m-d H:i:s' );
		$entry_id = FrmEntry::get_id_by_key( 'steve_entry_key' );
		$wpdb->update( $wpdb->prefix . 'frm_items', array( 'created_at' => $now ), array( 'id' => $entry_id ) );

		$filter_args = array(
			array( 'type' => 'col',
				'col' => 'created_at',
				'op' => '>',
				'val' => '-1 day',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Steve' ), array( 'Jamie', 'Steph' ) );

		self::run_get_display_data_tests( $d, 'created at filter' );

		// Set data back after testing
		$original_date = '2015-05-13 19:40:11';
		$wpdb->update( $wpdb->prefix . 'frm_items', array( 'created_at' => $original_date ), array( 'id' => $entry_id ) );
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
				'val' => 'Stev',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Steve' ), array( 'Jamie', 'Steph' ) );
		self::run_get_display_data_tests( $d, 'created_at with field ID filter' );

		// Check detail page - should show entry
		$_GET['detail'] = FrmEntry::get_id_by_key( 'steve_entry_key' );
		$dynamic_view = self::reset_view( 'dynamic-view', $filter_args );
		$d = self::get_default_args( $dynamic_view, array( 'Steve' ), array( 'Steph', 'Jamie' ) );
		self::run_get_display_data_tests( $d, 'created at with field ID filter on detail page' );

		// Check detail page - should NOT show entry
		$_GET['detail'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$dynamic_view = self::reset_view( 'dynamic-view', $filter_args );
		$d = self::get_default_args( $dynamic_view, array( 'No Entries Found' ), array( 'Jamie', 'Steve', 'Steph' ) );
		self::run_get_display_data_tests( $d, 'created at with field ID filter on detail page (should not show entry)' );
	}

	/**
	 * Test single entry ID filter
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_entry_id_filter() {
		self::clear_get_values();

		$filter_args = array(
			array( 'type' => 'col',
				'col' => 'id',
				'op' => '=',
				'val' => FrmEntry::get_id_by_key( 'jamie_entry_key' ),
			),
		);

		// Check listing page
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		self::add_filter_to_view( $dynamic_view, $filter_args );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steve', 'Steph' ) );
		self::run_get_display_data_tests( $d, 'entry ID is equal to x filter' );

		// Check detail page
		$_GET['detail'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$dynamic_view = self::reset_view( 'dynamic-view', $filter_args );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steve', 'Steph' ) );
		self::run_get_display_data_tests( $d, 'entry ID is equal to x filter on detail page' );

		// Check detail page - entry should not be shown
		$_GET['detail'] = FrmEntry::get_id_by_key( 'steph_entry_key' );
		$dynamic_view = self::reset_view( 'dynamic-view', $filter_args );
		$d = self::get_default_args( $dynamic_view, array( 'No Entries Found' ), array( 'Jamie', 'Steve', 'Steph' ) );
		self::run_get_display_data_tests( $d, 'entry ID is equal to x filter on detail page (no entry should be shown)' );

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

		// Basic entry_id=ID
		self::run_get_display_data_tests( $d, 'entry_id=x parameter' );

		// Test with entry key
		$d['entry_id'] = 'jamie_entry_key';
		$d['display'] = self::reset_view( 'dynamic-view' );
		self::run_get_display_data_tests( $d, 'entry_id=entry_key parameter' );
	}

	/**
	 * Test entry_id=key parameter
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_entry_id_parameter_with_key() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'href' ), array( 'Steve', 'Steph' ) );
		$d['entry_id'] = 'jamie_entry_key';
		self::run_get_display_data_tests( $d, 'entry_id=entry_key parameter' );
	}

	/**
	 * Test entry_id=x parameter with field filter set
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_entry_id_param_with_field_filter() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		// Test with an added filter - entry_id should override all other filters
		$filter_args = array(
			array( 'type' => 'field',
				'col' => '493ito',
				'op' => '=',
				'val' => 'Steve',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'href' ), array( 'Steve', 'Steph' ) );
		$d['entry_id'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );

		self::run_get_display_data_tests( $d, 'entry_id=x parameter with filter set' );
	}

	/**
	 * Test entry_id=x parameter with entry ID filter set
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_entry_id_param_with_entry_id_filter() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		// Test with an entry ID filter - entry_id should override all other filters
		$filter_args = array(
			array( 'type' => 'col',
				'col' => 'id',
				'op' => '=',
				'val' => '12345',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'href' ), array( 'Steve', 'Steph' ) );
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
	 * Test Scale field is unique (get oldest entries)
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_unique_oldest_filter_on_scale_field() {
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
	 * Test Scale field is unique (get newest entries)
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_unique_newest_filter_on_scale_field() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$filter_args = array(
			array( 'type' => 'field',
			       'col' => 'qbrd2o',
			       'op' => 'group_by_newest',
			       'val' => '',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Jwahlin', 'Steve' ), array( 'Steph' ) );

		self::run_get_display_data_tests( $d, 'unique filter' );
	}

	/**
	 * Test Post Title field is unique (get oldest entries)
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_unique_oldest_filter_on_post_title() {
		self::clear_get_values();
		$post_view = self::get_view_by_key( 'create-a-post-view' );

		$filter_args = array(
			array( 'type' => 'field',
				'col' => 'yi6yvm',
				'op' => 'group_by',
				'val' => '',
			),
		);
		self::add_filter_to_view( $post_view, $filter_args );

		// Get post ID
		global $wpdb;
		$query = "SELECT ID from " . $wpdb->prefix . "posts WHERE post_name='jamies_post'";
		$jamie_post_id = $wpdb->get_var( $query );

		$d = self::get_default_args( $post_view, array( 'Jamie\'s Post', '?p=' . $jamie_post_id, 'Dragon' ), array() );

		self::run_get_display_data_tests( $d, 'unique filter with post title' );
	}

	/**
	 * Test Post Title field is unique (get newest entries)
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_unique_newest_filter_on_post_title() {
		self::clear_get_values();
		$post_view = self::get_view_by_key( 'create-a-post-view' );

		$filter_args = array(
			array( 'type' => 'field',
			       'col' => 'yi6yvm',
			       'op' => 'group_by_newest',
			       'val' => '',
			),
		);
		self::add_filter_to_view( $post_view, $filter_args );

		// Get post ID
		global $wpdb;
		$query = "SELECT ID from " . $wpdb->prefix . "posts WHERE post_name='jamies_post-2'";
		$jamie_post_id = $wpdb->get_var( $query );

		$d = self::get_default_args( $post_view, array( 'Jamie\'s Post', '?p=' . $jamie_post_id, 'Dragon' ), array() );

		self::run_get_display_data_tests( $d, 'unique filter with post title' );
	}

	/**
	 * Test user_id is unique (get oldest entries)
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_unique_oldest_filter_on_user_id() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$filter_args = array(
			array( 'type' => 'field',
				'col' => 't1eqkj',
				'op' => 'group_by',
				'val' => '',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steph', 'Steve' ), array() );

		self::run_get_display_data_tests( $d, 'unique filter with user_id' );
	}

	/**
	 * Test user_id is unique (get newest entries)
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_unique_newest_filter_on_user_id() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$filter_args = array(
			array( 'type' => 'field',
			       'col' => 't1eqkj',
			       'op' => 'group_by_newest',
			       'val' => '',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Jwahlin', 'Steph', 'Steve' ), array() );

		self::run_get_display_data_tests( $d, 'unique filter with user_id' );
	}


	/**
	 * Test entry ID is unique (get oldest entries)
	 * This filter should be ignored
	 *
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_unique_oldest_filter_on_entry_id() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$filter_args = array(
			array( 'type' => 'col',
				'col' => 'id',
				'op' => 'group_by',
				'val' => '',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steph', 'Steve' ), array() );

		self::run_get_display_data_tests( $d, 'unique filter with entry ID' );
	}

	/**
	 * Test entry creation date is unique (get oldest entries)
	 * This filter should be ignored
	 *
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_unique_oldest_filter_on_entry_creation_date() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$filter_args = array(
			array( 'type' => 'col',
				'col' => 'created_at',
				'op' => 'group_by',
				'val' => '',
			),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steph', 'Steve' ), array() );

		self::run_get_display_data_tests( $d, 'unique filter with creation date' );
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
	 * Test Detail page of View with page size set
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_detail_page_with_low_page_size() {
		self::clear_get_values();
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_page_size = 1;

		// Test Jamie entry
		$_GET['detail'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steph', 'Steve' ) );
		self::run_get_display_data_tests( $d, 'dynamic view on detail page with low page size 1' );

		// Test Steve entry
		$_GET['detail'] = FrmEntry::get_id_by_key( 'steve_entry_key' );
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$d = self::get_default_args( $dynamic_view, array( 'Steve' ), array( 'Jamie', 'Steph' ) );
		self::run_get_display_data_tests( $d, 'dynamic view on detail page with low page size 2' );
	}

	/**
	 * Test drafts in Views
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_drafts_in_view() {
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		self::maybe_create_draft_entry_in_all_fields_form( $dynamic_view->frm_form_id );

		// No drafts should show by default
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steph', 'Steve', 'href' ), array( 'Celeste' ) );
		self::run_get_display_data_tests( $d, 'no drafts by default' );

		// Drafts and non-drafts should show with drafts=both
		$extra_atts['drafts'] = 'both';
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steph', 'Steve', 'Celeste', 'href' ), array(), $extra_atts );
		self::run_get_display_data_tests( $d, 'drafts=both parameter' );

		// Only drafts should show with drafts=1
		$extra_atts['drafts'] = '1';
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$d = self::get_default_args( $dynamic_view, array( 'Celeste', 'href' ), array( 'Jamie', 'Steph', 'Steve' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'drafts=1 parameter' );

		// No should show with drafts=0
		$extra_atts['drafts'] = '0';
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steph', 'Steve', 'href' ), array( 'Celeste' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'drafts=0 parameter' );
	}

	/**
	 * Test "Draft Status is equal to both"
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_draft_status_equals_both_filter() {
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		self::maybe_create_draft_entry_in_all_fields_form( $dynamic_view->frm_form_id );

		// Drafts and non-drafts should show with drafts=both
		$filter_args = array(
			array( 'type' => 'col', 'col' => 'is_draft', 'op' => '=', 'val' => 'both' ),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steph', 'Steve', 'Celeste', 'href' ), array() );
		self::run_get_display_data_tests( $d, '"drafts is equal to both" filter' );
	}

	/**
	 * Test "Draft Status is equal to complete entry"
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_draft_status_equal_to_complete_entry() {
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		self::maybe_create_draft_entry_in_all_fields_form( $dynamic_view->frm_form_id );

		$filter_args = array(
			array( 'type' => 'col', 'col' => 'is_draft', 'op' => '=', 'val' => '0' ),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		// Only non-draft entries should show
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steph', 'Steve', 'href' ), array( 'Celeste' ) );
		self::run_get_display_data_tests( $d, '"drafts is equal to complete entry" filter' );
	}

	/**
	 * Test "Draft Status is equal to draft"
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_draft_status_equals_draft_filter() {
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		self::maybe_create_draft_entry_in_all_fields_form( $dynamic_view->frm_form_id );

		// Only drafts should show with drafts=1
		$filter_args = array(
			array( 'type' => 'col', 'col' => 'is_draft', 'op' => '=', 'val' => '1' ),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		$d = self::get_default_args( $dynamic_view, array( 'Celeste', 'href' ), array( 'Jamie', 'Steph', 'Steve' ) );
		self::run_get_display_data_tests( $d, '"drafts is equal to both" filter' );
	}


	/**
	 * Test "Draft Status is equal to complete entry" with drafts="both" param
	 * drafts="both" should override draft filter
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_draft_status_equal_to_complete_entry_with_drafts_param() {
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		self::maybe_create_draft_entry_in_all_fields_form( $dynamic_view->frm_form_id );

		// Drafts and non-drafts should show with drafts=both
		$filter_args = array(
			array( 'type' => 'col', 'col' => 'is_draft', 'op' => '=', 'val' => '0' ),
		);
		self::add_filter_to_view( $dynamic_view, $filter_args );

		// All entries should be shown
		$extra_atts['drafts'] = 'both';

		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steph', 'Celeste', 'Steve', 'href' ), array(), $extra_atts );
		self::run_get_display_data_tests( $d, '"drafts is equal to complete entry" filter with drafts=both param' );
	}

	/**
	 * Test drafts on detail page of View
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_detail_page_of_view_with_drafts(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$entry_id = self::maybe_create_draft_entry_in_all_fields_form( $dynamic_view->frm_form_id );

		$_GET['detail'] = $entry_id;

		// Add drafts=1 (string)
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$d = self::get_default_args( $dynamic_view, array( 'Celeste' ), array( 'No Entries Found' ), array( 'drafts' => '1' ) );
		self::run_get_display_data_tests( $d, 'drafts in detail page with drafts=1 (string)' );

		// Add drafts=1 (int)
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$d = self::get_default_args( $dynamic_view, array( 'Celeste' ), array( 'No Entries Found' ), array( 'drafts' => 1 ) );
		self::run_get_display_data_tests( $d, 'drafts in detail page with drafts=1 (int)' );

		// Add drafts=1 (bool)
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$d = self::get_default_args( $dynamic_view, array( 'Celeste' ), array( 'No Entries Found' ), array( 'drafts' => true ) );
		self::run_get_display_data_tests( $d, 'drafts in detail page with drafts=true' );

		// Add drafts=both
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$d = self::get_default_args( $dynamic_view, array( 'Celeste' ), array( 'No Entries Found' ), array( 'drafts' => 'both' ) );
		self::run_get_display_data_tests( $d, 'drafts in detail page with drafts=both' );
	}

	/**
	 * Test drafts on detail page of View
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_detail_page_of_view_with_no_drafts(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		$entry_id = self::maybe_create_draft_entry_in_all_fields_form( $dynamic_view->frm_form_id );

		$_GET['detail'] = $entry_id;

		// Make sure draft entry doesn't show up in detail page, by default
		$d = self::get_default_args( $dynamic_view, array( 'No Entries Found' ), array( 'Celeste' ) );
		self::run_get_display_data_tests( $d, 'no drafts in detail page by default' );

		// Add drafts=0 (string)
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$d = self::get_default_args( $dynamic_view, array( 'No Entries Found' ), array( 'Celeste' ), array( 'drafts' => '0' ) );
		self::run_get_display_data_tests( $d, 'no drafts in detail page with drafts=0 string' );

		// Add drafts=0 (int)
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$d = self::get_default_args( $dynamic_view, array( 'No Entries Found' ), array( 'Celeste' ), array( 'drafts' => 0 ) );
		self::run_get_display_data_tests( $d, 'no drafts in detail page with drafts=0 int' );

		// Add drafts=false (bool)
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$d = self::get_default_args( $dynamic_view, array( 'No Entries Found' ), array( 'Celeste' ), array( 'drafts' => false ) );
		self::run_get_display_data_tests( $d, 'no drafts in detail page with drafts=false' );

		// Add drafts=fake
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$d = self::get_default_args( $dynamic_view, array( 'Celeste' ), array(), array( 'drafts' => 'fake' ) );
		self::run_get_display_data_tests( $d, 'drafts in detail page with drafts=fake' );
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
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$d = self::get_default_args( $dynamic_view, array( 'No Entries Found' ), array(), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with frm_search param and no drafts' );

	}

	/**
	 * Test limit on a View
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_limit_with_view(){
		self::clear_get_values();

		// Check limit=1
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_limit = 1;
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steve', 'Steph' ) );
		self::run_get_display_data_tests( $d, 'view with limit set' );

		// See if limit param overrides limit setting
		$extra_atts = array( 'limit' => 100 );
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$dynamic_view->frm_limit = 1;
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steve', 'Steph' ), array(), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with limit set and limit param' );

		// See if limit param works on its own
		$extra_atts = array( 'limit' => 1 );
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$dynamic_view->frm_limit = 100;
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steph', 'Steve' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with limit param' );

	}


	/**
	 * Test page size on a View
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_page_size_on_view(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );

		// Page size is equal to 1
		$dynamic_view->frm_page_size = 1;
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'frm_pagination_cont' ), array( 'Steve', 'Steph' ) );
		self::run_get_display_data_tests( $d, 'view with page size set' );

		// Page size is equal to 2
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$dynamic_view->frm_page_size = 2;
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Jwahlin', 'frm_pagination_cont' ), array( 'Steph', 'Steve' ) );
		self::run_get_display_data_tests( $d, 'view with page size set' );

		// Page size is equal to 4
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$dynamic_view->frm_page_size = 4;
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Jwahlin', 'Steph', 'Steve' ), array( 'frm_pagination_cont') );
		self::run_get_display_data_tests( $d, 'view with page size set' );
	}

	/**
	 * Test page size on a View with a page_size parameter set
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_page_size_with_page_size_param(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_page_size = 1;

		// See if page_size param (100) overrides page size setting (1)
		$extra_atts = array( 'page_size' => 100 );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Steve', 'Steph' ), array(), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with page size set and page_size param' );

		// See if page_size param (1) overrides page size setting(100)
		$extra_atts = array( 'page_size' => 1 );
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$dynamic_view->frm_page_size = 100;
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steph', 'Steve' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with page size param' );

	}

	/**
	 * Test page 2 & 3 of a View
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_page_2_of_a_view(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$_GET['frm-page-'. $dynamic_view->ID] = 2;

		// On page 2 with page size of 1
		$dynamic_view->frm_page_size = 1;
		$d = self::get_default_args( $dynamic_view, array( 'Jwahlin', 'frm_pagination_cont' ), array( 'Steph', 'Jamie', 'Steve' ) );
		self::run_get_display_data_tests( $d, 'view on page 2 with page size of 1' );

		// On page 2 with page size of 2
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$dynamic_view->frm_page_size = 2;
		$d = self::get_default_args( $dynamic_view, array( 'Steph', 'Steve', 'frm_pagination_cont' ), array( 'Jamie', 'Jwahlin' ) );
		self::run_get_display_data_tests( $d, 'view on page 2 with a page size of 2' );

		// On page 3 with page size of 2
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$dynamic_view->frm_page_size = 2;
		$_GET['frm-page-'. $dynamic_view->ID] = 3;
		$d = self::get_default_args( $dynamic_view, array( 'frm_no_entries' ), array( 'Jamie', 'Jwahlin', 'Steph', 'Steve' ) );
		self::run_get_display_data_tests( $d, 'view on page 3 with no entries showing' );
	}

	/**
	 * Test limit with page size on a View
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_limit_with_page_size_on_view(){
		self::clear_get_values();

		// Test page_size of 5 and limit of 1
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_page_size = 5;
		$dynamic_view->frm_limit = 1;
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Steve', 'Steph', 'frm_pagination' ) );
		self::run_get_display_data_tests( $d, 'view with limit lower than page size' );

		// Test page size of 1 and limit of 2
		// Checks if pagination is correct
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$dynamic_view->frm_page_size = 1;
		$dynamic_view->frm_limit = 2;
		$expected_values = array( 'Jamie', 'frm-page-' . $dynamic_view->ID . '=2' );
		$values_that_should_not_be_present = array( 'frm-page-' . $dynamic_view->ID . '=3', 'Steve', 'Steph' );
		$d = self::get_default_args( $dynamic_view, $expected_values, $values_that_should_not_be_present );
		self::run_get_display_data_tests( $d, 'view with page size lower than limit' );

		// Test page size of 1 and limit of 2
		// Makes sure no entries are loaded on page 3
		self::clear_get_values();
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$dynamic_view->frm_page_size = 1;
		$dynamic_view->frm_limit = 2;
		$_GET['frm-page-' . $dynamic_view->ID ] = 3;
		$expected_values = array( 'No Entries Found' );
		$values_that_should_not_be_present = array( 'Jamie', 'Steve', 'Steph' );
		$d = self::get_default_args( $dynamic_view, $expected_values, $values_that_should_not_be_present );
		self::run_get_display_data_tests( $d, 'view with page size lower than limit (#2)' );
	}

	/**
	 * Make sure calendar View has the calendar HTML
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_calendar_view_html(){
		self::clear_get_values();

		$calendar_view = self::get_view_by_key( 'calendar-view' );

		$expected_strings = array( 'frmcal-' . $calendar_view->ID, 'frmcal-header', 'frmcal_date', 'frmcal-content' );
		$no_strings = array( 'Jamie', 'Steph', 'Steve' );
		$d = self::get_default_args( $calendar_view, $expected_strings, $no_strings );
		self::run_get_display_data_tests( $d, 'basic calendar view' );
	}

	/**
	* Test the number of rows and days showing in a calendar
	* @covers FrmProDisplaysController::get_display_data
	*/
	function test_number_of_days_in_calendar(){
		self::clear_get_values();

		$calendar_view = self::get_view_by_key( 'calendar-view' );

		$content = FrmProDisplaysController::get_display_data( $calendar_view, '', false, array() );

		// Number of rows
		$row_count = substr_count( $content, '<tr' );
		$this->assertTrue( $row_count > 3 && $row_count < 7, 'There are ' . $row_count . ' rows in a calendar View' );

		// Number of day boxes
		$day_count = substr_count( $content, '<td' );
		$this->assertTrue( $day_count > 20 && $day_count < 43, 'There are ' . $day_count . ' days in a calendar View' );
	}

	/**
	 * Make sure calendar View pulls up the correct data depending on the month/year shown
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_calendar_view_content(){
		self::clear_get_values();

		$calendar_view = self::get_view_by_key( 'calendar-view' );

		// Jamie's entry should be shown
		$_GET['frmcal-month'] = '08';
		$_GET['frmcal-year'] = '2015';

		$expected_strings = array( 'frmcal-' . $calendar_view->ID, 'frmcal-header', 'frmcal_date', 'Jamie' );
		$no_strings = array( 'Steph', 'Steve' );
		$d = self::get_default_args( $calendar_view, $expected_strings, $no_strings );
		self::run_get_display_data_tests( $d, 'basic calendar view' );
	}

	/**
	 * Make sure calendar View pulls up the correct data depending on the month/year shown
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_calendar_view_content_no_entries_in_month(){
		self::clear_get_values();

		$calendar_view = self::get_view_by_key( 'calendar-view' );

		// No entries should be shown on current page
		$_GET['frmcal-month'] = '08';
		$_GET['frmcal-year'] = '2010';

		$expected_strings = array( 'frmcal-' . $calendar_view->ID, 'frmcal-header', 'frmcal_date' );
		$no_strings = array( 'Jamie', 'Steph', 'Steve' );

		$d = self::get_default_args( $calendar_view, $expected_strings, $no_strings );
		self::run_get_display_data_tests( $d, 'basic calendar view with no entries on current page' );
	}

	/**
	 * Make sure calendar View displays an empty calendar when there aren't any entries
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_calendar_view_no_entries(){
		self::clear_get_values();

		$calendar_view = self::get_view_by_key( 'calendar-view' );

		// Add filter "Entry ID is equal to 0"
		$filter_args = array(
			array( 'type' => 'col', 'col' => 'id', 'op' => '=', 'val' => '0' ),
		);
		self::add_filter_to_view( $calendar_view, $filter_args );

		$expected_strings = array( 'frmcal-' . $calendar_view->ID, 'frmcal-header', 'frmcal_date' );
		$d = self::get_default_args( $calendar_view, $expected_strings, array( 'No Entries Found' ) );
		self::run_get_display_data_tests( $d, 'calendar view with no entries found' );
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
	}

	/**
	 * Test View order - field ID ascending
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_field_id_asc_with_order_param(){
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

		// Add order param, should override order in View
		$extra_atts = array( 'order_by' => 'id', 'order' => 'DESC' );
		$d = self::get_default_args( $dynamic_view, array( 'Steve', 'frm_pagination_cont' ), array( 'Jamie', 'Steph' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with field ASC and order=id order_by=DESC params' );
	}

	/**
	 * Test View order with parameters
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_order_parameters_in_view(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_page_size = 1;

		self::remove_view_order( $dynamic_view );

		// Both order and order_by
		$extra_atts = array( 'order_by' => 'id', 'order' => 'DESC' );
		$d = self::get_default_args( $dynamic_view, array( 'Steve', 'frm_pagination_cont' ), array( 'Jamie', 'Steph' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with order_by=id order=DESC' );
	}


	/**
	 * Test order=DESC with no order_by
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_incomplete_order_parameters_in_view(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_page_size = 1;

		self::remove_view_order( $dynamic_view );

		// Only order - should be ignored completely
		$extra_atts = array( 'order_by' => '', 'order' => 'DESC' );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'frm_pagination_cont' ), array( 'Steve', 'Steph' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with no order_by and order=DESC' );
	}

	/**
	 * Test order_by=id with no order
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_incomplete_order_parameters_in_view_2(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_page_size = 1;

		self::remove_view_order( $dynamic_view );

		// Only order_by - should automatically set order to ASC if this is the case
		$extra_atts = array( 'order_by' => 'id', 'order' => '' );
		$d = self::get_default_args( $dynamic_view, array( 'Steve', 'frm_pagination_cont' ), array( 'Jamie', 'Steph' ), $extra_atts );
		self::run_get_display_data_tests( $d, 'view with order_by=id and no order' );
	}


	/**
	 * Make sure Before Content is shown on listing page
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_before_content_in_view_listing_page(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_before_content = 'Before content, user_id:[user_id], siteurl:[siteurl]';

		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Before content', 'user_id:1', 'siteurl:http://example.org' ), array() );

		self::run_get_display_data_tests( $d, 'view with before content' );
	}

	/**
	 * Make sure Before Content is shown on listing page
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_before_content_in_view_detail_page(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_before_content = 'Before content';

		// Before content should not show on detail page
		$_GET['detail'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'Before content' ) );
		self::run_get_display_data_tests( $d, 'view with before content on detail page' );
	}

	/**
	 * Make sure Before Content is filtered with the frm_before_display_content hook
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_before_content_with_custom_filter(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_before_content = 'Before Content: [sum_msyehy]';
		add_filter( 'frm_before_display_content', 'dynamic_frm_stats', 10, 4 );

		// Make sure before content includes the dynamic total
		$field_id = FrmField::get_id_by_key( 'msyehy' );
		$expected_total = (string) FrmProStatisticsController::stats_shortcode( array( 'id' => $field_id, 'type' => 'total' ) );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', $expected_total ), array() );
		self::run_get_display_data_tests( $d, 'view with before content and frm_before_display_content filter' );
	}

	/**
	 * Make sure Before Content is filtered with the frm_before_display_content hook even when a page size is set
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_before_content_with_custom_filter_and_page_size(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_before_content = 'Before Content: [sum_msyehy]';
		add_filter( 'frm_before_display_content', 'dynamic_frm_stats', 10, 4 );

		$field_id = FrmField::get_id_by_key( 'msyehy' );

		// make sure before content includes dynamic total when a page size is set
		$dynamic_view->frm_page_size = 1;
		$expected_total = FrmProEntriesController::get_field_value_shortcode(array('field_id' => $field_id, 'entry' => 'jamie_entry_key' ) );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', $expected_total ), array() );
		self::run_get_display_data_tests( $d, 'view with before content and frm_before_display_content filter, page size set to 1' );
	}

	/**
	 * Test [row_num] shortcode, added with frm_display_entry_content hook
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_row_num_custom_filter(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->post_content = 'Row: [row_num]' . $dynamic_view->post_content;
		add_filter('frm_display_entry_content', 'frm_get_row_num', 20, 7);

		// Check for row nums, no page size
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Row: 1', 'Row: 2', 'Row: 3', 'Row: 4' ), array( 'Row: 5' ) );
		self::run_get_display_data_tests( $d, 'view with row_num shortcode' );
	}

	/**
	 * Test [row_num] shortcode, added with frm_display_entry_content hook, page size set
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_row_num_custom_filter_with_page_size(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->post_content = 'Row: [row_num]' . $dynamic_view->post_content;
		add_filter('frm_display_entry_content', 'frm_get_row_num', 20, 7);

		// Check for row nums, page size set
		$dynamic_view->frm_page_size = 1;
		$_GET['frm-page-'. $dynamic_view->ID] = 2;
		$d = self::get_default_args( $dynamic_view, array( 'Row: 2', ), array( 'Row: 1', 'Row: 3' ) );
		self::run_get_display_data_tests( $d, 'view with row_num shortcode and page size' );
	}

	/**
	 * Test record_count and total_count with frm_display_entry_content hook
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_record_count_and_total_count_with_filter(){
		self::clear_get_values();

		add_filter('frm_display_entry_content', 'frm_get_current_entry_num_out_of_total', 20, 7);

		// Check page 1, page size of 1
		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_page_size = 1;
		$string = 'Viewing entry 1 to 1 (of 4 entries)';
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', $string ), array( 'Jwahlin', 'Steph', 'Steve' ) );
		self::run_get_display_data_tests( $d, 'view with record_count and total_count test' );

		// Check page 2, page size of 1
		$_GET['frm-page-'. $dynamic_view->ID] = 2;
		$string = 'Viewing entry 2 to 2 (of 4 entries)';
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$dynamic_view->frm_page_size = 1;
		$d = self::get_default_args( $dynamic_view, array( 'Jwahlin', $string ), array( 'Jamie', 'Steph', 'Steve' ) );
		self::run_get_display_data_tests( $d, 'view with record_count and total_count test 2' );

		// Check page 1 with a page size of 2
		$_GET['frm-page-'. $dynamic_view->ID] = 1;
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$dynamic_view->frm_page_size = 2;
		$string = 'Viewing entry 1 to 2 (of 4 entries)';
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'Jwahlin', $string ), array( 'Steph', 'Steve' ) );
		self::run_get_display_data_tests( $d, 'view with record_count and total_count test 3' );
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

		$d = self::get_default_args( $dynamic_view, array( 'Count: 4', 'Jamie' ), array() );

		self::run_get_display_data_tests( $d, 'view with [entry_count] shortcode' );
	}

	/**
	 * Test [entry_count] shortcode with page size
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_entry_count_shortcode_with_page_size(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_before_content = 'Count: [entry_count]';
		$dynamic_view->frm_page_size = 1;

		$d = self::get_default_args( $dynamic_view, array( 'Count: 4', 'Jamie' ), array() );
		self::run_get_display_data_tests( $d, 'view with [entry_count] shortcode and page size' );
	}

	/**
	 * Make sure After Content is shown on listing page
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_after_content_on_listing_page(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_after_content = 'After content, user_id:[user_id], siteurl:[siteurl]';

		$d = self::get_default_args( $dynamic_view, array( 'Jamie', 'After content', 'user_id:1', 'siteurl:http://example.org' ), array() );

		self::run_get_display_data_tests( $d, 'view with after content' );
	}

	/**
	 * Make sure After Content is shown on listing page
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_after_content_on_detail_page(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_after_content = 'After content';

		// Before content should not show on detail page
		$_GET['detail'] = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie' ), array( 'After content' ) );
		self::run_get_display_data_tests( $d, 'view with after content on detail page' );
	}

	/**
	 * Make sure [sum_x] works in After Content
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_after_content_with_custom_filter(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_after_content = 'After Content: [sum_msyehy]';
		add_filter( 'frm_after_display_content', 'dynamic_frm_stats', 10, 4 );

		// Make sure after content includes the dynamic total
		$field_id = FrmField::get_id_by_key( 'msyehy' );
		$expected_total = (string) FrmProStatisticsController::stats_shortcode( array( 'id' => $field_id, 'type' => 'total' ) );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', $expected_total ), array() );
		self::run_get_display_data_tests( $d, 'view with after content and frm_after_content filter' );
	}

	/**
	 * Make sure [sum_x] works in After Content with page size
	 * @covers FrmProDisplaysController::get_display_data
	 */
	function test_after_content_with_custom_filter_and_page_size(){
		self::clear_get_values();

		$dynamic_view = self::get_view_by_key( 'dynamic-view' );
		$dynamic_view->frm_after_content = 'After Content: [sum_msyehy]';
		$dynamic_view->frm_page_size = 1;

		add_filter( 'frm_after_display_content', 'dynamic_frm_stats', 10, 4 );

		$field_id = FrmField::get_id_by_key( 'msyehy' );
		$expected_total = FrmProEntriesController::get_field_value_shortcode(array('field_id' => $field_id, 'entry' => 'jamie_entry_key' ) );

		$d = self::get_default_args( $dynamic_view, array( 'Jamie', $expected_total ), array() );
		self::run_get_display_data_tests( $d, 'view with after content and frm_after_content filter, page size set to 1' );
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
		$dynamic_view = self::reset_view( 'dynamic-view' );
		$extra_atts = array( 'filter' => 1 );
		$d = self::get_default_args( $dynamic_view, array( 'Jamie', '<br />' ), array(), $extra_atts );
		self::run_get_display_data_tests( $d, 'view without filter=1' );
	}

	/**
	 * Test non-Formidable shortcodes in Before Content, Content, and After Content
	 * filter=1 is NOT used
	 * All shortcodes in this test View should be processed excluding [formidable id=x]
	 *
	 * @covers FrmProDisplaysController::get_shortcode
	 */
	function test_shortcodes_in_all_parts_of_content_with_no_wp_filter() {
		self::make_sure_easy_tables_is_active();
		self::clear_get_values();

		$test_view = self::get_view_by_key( 'shortcode-checking' );
		$content = FrmProDisplaysController::get_shortcode( array( 'id' => $test_view->ID ) );

		// Check for contained values
		$expected_values = self::get_standard_expected_values();
		$expected_values[] = 'FormShortcodeBeforeContent: [formidable id=';// Make sure formidable id=x is NOT filtered in Before Content
		$expected_values[] = 'Formshortcode:[formidable id="dynamic';// Make sure formidable id=x is NOT filtered in Content
		foreach ( $expected_values as $e ) {
			$this->assertContains( $e, $content, 'The View with all types of shortcodes (without filter=1) is not getting the expected content.' );
		}

		// Make sure certain strings aren't present
		$form_id = FrmForm::getIdByKey( 'dynamic-field-num-form' );
		$not_expected_values = array( 'id="frm_form_' . $form_id . '_container"' );
		foreach ( $not_expected_values as $n ) {
			$this->assertNotContains( $n, $content, 'The View with all types of shortcodes (without filter=1) is missing some expected content.' );
		}

		self::check_easy_table_html( $content );
		self::check_counts_for_shortcodes( $content );

		// Check Formshortcode:[formidable id=x] occurrences (should not be filtered)
		$form_shortcode_count = substr_count( $content, 'Formshortcode:[formidable id="dynamic' );
		$this->assertEquals( 3, $form_shortcode_count, 'The number of form shortcodes is not the expected value in a View.' );
	}

	/**
	 * Test non-Formidable shortcodes in Before Content, Content, and After Content
	 * Uses filter=1 so all shortcodes should be processed
	 *
	 * @covers FrmProDisplaysController::get_shortcode
	 */
	function test_shortcodes_in_all_parts_of_content_with_wp_filter() {
		self:: make_sure_easy_tables_is_active();
		self::clear_get_values();

		$test_view = self::get_view_by_key( 'shortcode-checking' );
		$content = FrmProDisplaysController::get_shortcode( array( 'id' => $test_view->ID, 'filter' => '1' ) );

		// Check for contained values
		$expected_values = self::get_standard_expected_values();
		foreach ( $expected_values as $e ) {
			$this->assertContains( $e, $content, 'The View with all types of shortcodes (with filter=1) is not getting the expected content.' );
		}

		// Check for "does not contain" values
		$does_not_contain = array(
			'FormShortcodeBeforeContent: [formidable id=',// Make sure formidable id=x is NOT filtered in Before Content
			'Formshortcode::[formidable id=dynamic',
		);
		foreach ( $does_not_contain as $n ) {
			$this->assertNotContains( $n, $content, 'The View with all types of shortcodes (with filter=1) is missing some expected content.' );
		}

		self::check_easy_table_html( $content );
		self::check_counts_for_shortcodes( $content );

		// Check Formshortcode:[formidable id=x] occurrences (should be filtered)
		$form_id = FrmForm::getIdByKey( 'dynamic-field-num-form' );
		$form_count = substr_count( $content, 'id="frm_form_' . $form_id . '_container"');
		$this->assertEquals( 4, $form_count, 'The number of forms is not the expected value in a View.' );
	}

	function make_sure_easy_tables_is_active(){
		$plugin = 'easy-table/easy-table.php';
		$is_active = is_plugin_active( $plugin ) && class_exists('EasyTable');
		if ( ! $is_active ) {
			$this->markTestSkipped( 'Easy table is not active' );
		}
		$this->assertTrue( $is_active, 'Easy table is not active.' );
	}

	function get_standard_expected_values(){
		$expected_values = array(
			'Site Name: Test Blog',// Make sure [sitename] is filtered in Before Content
			'FrmFieldValueBeforeContent: Steve',// Make sure frm-field-value is filtered in Before Content
			'<table',// Check if Easy Table Shortcode in Before Content is filtered
			'<tbody',
			'steph_entry_key',// Tests [key] shortcode
			'steve_entry_key',// Make sure [key] shortcode is filtered on all rows
			'jamie_entry_key',// Make sure [key] shortcode is filtered on all rows
			'<td class="someclass"',// Check easy table shortcodes inside of Content
			'StandardFieldID:Steph',// Check that field ID shortcode is filtered
			'</tbody',
			'/table>',
		);

		return $expected_values;
	}

	function check_easy_table_html( $content ) {
		// Check th occurrences (from Easy Table)
		$th_count = substr_count( $content, '<th ' );
		$this->assertEquals( 5, $th_count, 'The number of table headers is not the expected value in View with Easy Table shortcodes.' );

		// Check tr occurrences (from Easy Table)
		$tr_count = substr_count( $content, '<tr' );
		$this->assertEquals( 4, $tr_count, 'The number of table rows is not the expected value in View with Easy Table shortcodes.' );

		// Check td occurrences (from Easy Table)
		$td_count = substr_count( $content, '<td' );
		$this->assertEquals( 15, $td_count, 'The number of table cells is not the expected value in View with Easy Table shortcodes.' );
	}

	function check_counts_for_shortcodes( $content ) {
		// Check SiteName:[siteurl] occurrences (filtered)
		$sitename_count = substr_count( $content, 'SiteName:Test Blog' );
		$this->assertEquals( 3, $sitename_count, 'The number of SiteName is not the expected value in View.' );

		// Check FrmFieldValue:[frm-field-value] occurrences (filtered)
		$frmfieldvalue_count = substr_count( $content, 'FrmFieldValue:Steve' );
		$this->assertEquals( 3, $frmfieldvalue_count, 'The number of FrmFieldValue:Steve occurrences is not the expected value in View.' );
	}

	function clear_get_values(){
		$_GET = array();
	}

	function get_view_by_key( $view_key ) {
		$view_id = FrmProDisplay::get_id_by_key( $view_key );
		return FrmProDisplay::getOne( $view_id, false, true );
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

	function maybe_create_draft_entry_in_all_fields_form( $form_id ) {
		// Check if draft entry exists first
		$where = array(
			'form_id' => $form_id,
			'is_draft' => '1',
		);
		$new_id = FrmDb::get_col( 'frm_items', $where, 'id' );

		if ( ! $new_id ) {
			// Duplicate an entry
			$jamie_entry_id = FrmEntry::get_id_by_key( 'jamie_entry_key' );
			$new_id = FrmEntry::duplicate( $jamie_entry_id );

			// Switch it to a draft
			global $wpdb;
			$wpdb->update( $wpdb->prefix . 'frm_items', array( 'is_draft' => 1 ), array( 'id' => $new_id ) );

			// Change text field value
			$field_id = FrmField::get_id_by_key( '493ito' );
			$wpdb->update( $wpdb->prefix . 'frm_item_metas', array( 'meta_value' => 'Celeste' ), array( 'item_id' => $new_id, 'field_id' => $field_id ) );
		}

		return $new_id;
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

	/**
	 * Test the query size for a View with no filters
	 *
	 * @covers FrmProDisplaysController::get_where_query_for_view_listing_page
	 */
	function test_query_size_for_no_filter_view() {
		$view = self::get_view_by_key( 'dynamic-view' );
		$atts = self::get_default_atts_for_view( $view );

		// Test with no filters at all
		$where = self::_do_private_return_method( 'get_where_query_for_view_listing_page', array( $view, $atts ) );

		// There should be no it.id value set in $where clause
		$this->assertFalse( isset( $where['it.id'] ), 'Entry IDs are being added to the where clause (adding unnecessary bulk).' );

	}

	/**
	 * Test the query size for a View with no only the draft filter
	 *
	 * @covers FrmProDisplaysController::get_where_query_for_view_listing_page
	 */
	function test_query_size_for_draft_filter_view() {
		$view = self::get_view_by_key( 'dynamic-view' );
		$atts = self::get_default_atts_for_view( $view );

		// Add single draft filter (is_draft = 0)
		$filter_args = array( array( 'type' => 'col', 'col' => 'is_draft', 'op' => '=', 'val' => '0' ), );
		self::add_filter_to_view( $view, $filter_args );

		// Get where query
		$where = self::_do_private_return_method( 'get_where_query_for_view_listing_page', array( $view, $atts ) );

		// There should be no it.id value set in $where clause
		$this->assertFalse( isset( $where['it.id'] ), 'Entry IDs are being added to the where clause with draft filter only (adding unnecessary bulk).' );
	}

	function get_default_atts_for_view( $view ) {
		$args = array( array( 'entry_id' => '' ), $view );
		return self::_do_private_return_method( 'get_atts_for_view', $args );
	}

	function _do_private_return_method( $method_name, $args ){
		$class = new ReflectionClass( 'FrmProDisplaysController' );
		$method = $class->getMethod( $method_name );
		$method->setAccessible( true );
		return $method->invokeArgs( null, $args );
	}
}