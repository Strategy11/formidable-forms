<?php

/**
 * @group graphs
 */
class WP_Test_FrmProGraphsController extends FrmUnitTest {

	// TODO: Add tests for field with separate values
	// TODO: id=x,y test

	/**
	 * Check [frm-graph id=x] where x is the ID of a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_line_text_with_id() {
		self::clear_frm_vars();

		$field_id = FrmField::get_id_by_key( '493ito' );
		$graph_atts = array( 'id' => $field_id );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x] where x is the key of a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_line_text_with_key() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => '493ito' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x type=pie] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_line_text_with_type_pie() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => '493ito', 'type' => 'pie' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_expected_data_for_text_field( $graph_atts );
		self::modify_expected_data_for_pie_graph( $expected_data );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x type=bar] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_line_text_with_type_bar() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => '493ito', 'type' => 'bar' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x type=column] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_line_text_with_type_column() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => '493ito', 'type' => 'column' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}


	/**
	 * Check [frm-graph id=x type=bar] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_line_text_with_type_line() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => '493ito', 'type' => 'line' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x type=bar] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_line_text_with_type_area() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => '493ito', 'type' => 'area' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x type=geo] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_line_text_with_type_geo() {
		self::clear_frm_vars();
		$graph_atts = array(
			'id' => '2atiqt',
			'type' => 'geo',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_country_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x type=SteppedArea] where x is a Number field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_line_text_with_type_stepped_area() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => 'msyehy',
			'type' => 'SteppedArea',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_number_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x user_id=1] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_user_id() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => '493ito', 'user_id' => '1' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Single Line Text', $graph_atts );
		$expected_data['rows'] = array(
			array( 'Jamie', 1.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x user_id=1] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_user_id_current() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => '493ito', 'user_id' => 'current' );
		$this->set_current_user_to_1();

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Single Line Text', $graph_atts );
		$expected_data['rows'] = array(
			array( 'Jamie', 1.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x entry_id=y] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_entry_id() {
		self::clear_frm_vars();

		$entry_id = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$graph_atts = array( 'id' => '493ito', 'entry_id' => $entry_id );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Single Line Text', $graph_atts );
		$expected_data['rows'] = array(
			array( 'Jamie', 1.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x entry_id=y,z] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_entry_ids() {
		self::clear_frm_vars();

		$entry_id_one = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$entry_id_two = FrmEntry::get_id_by_key( 'steph_entry_key' );
		$graph_atts = array(
			'id' => '493ito',
			'entry_id' =>  $entry_id_one . ',' . $entry_id_two,
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Single Line Text', $graph_atts );
		$expected_data['rows'] = array(
			array( 'Jamie', 1.0 ),
			array( 'Steph', 1.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x entry_id=entry-key] where x is a single line text field
	 * Fails in 2.02 or lower
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_entry_key() {
		$this->markTestSkipped( 'Fails in 2.2' );

		self::clear_frm_vars();
		$field_key = '493ito';
		$graph_atts = array( 'id' => $field_key, 'entry_id' =>  'jamie_entry_key' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Single Line Text', $graph_atts );
		$expected_data['rows'] = array(
			array( 'Jamie', 1.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y="value"] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_dropdown_id() {
		self::clear_frm_vars();

		$dropdown_id = FrmField::get_id_by_key( '54tffk' );
		$graph_atts = array( 'id' => '493ito', $dropdown_id => 'Ace Ventura' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Single Line Text', $graph_atts );
		$expected_data['rows'] = array(
			array( 'Jamie', 1.0 ),
			array( 'Steve', 1.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y="value"] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_dropdown_key() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => '493ito', '54tffk' => 'Ace Ventura' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Single Line Text', $graph_atts );
		$expected_data['rows'] = array(
			array( 'Jamie', 1.0 ),
			array( 'Steve', 1.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y="value"] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_dynamic_field() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => '493ito', 'dynamic-state' => 'California' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Single Line Text', $graph_atts );
		$expected_data['rows'] = array(
			//array( 'Steph', '1' ), This part fails due to incorrect import. Fix the import!
			array( 'Steve', 1.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y="value"] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_dynamic_field_two() {
		self::clear_frm_vars();

		$filter_value = FrmEntry::get_id_by_key( 'cali_entry' );
		$graph_atts = array( 'id' => '493ito', 'dynamic-state' => $filter_value );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Single Line Text', $graph_atts );
		$expected_data['rows'] = array(
			//array( 'Steph', '1' ), This part fails due to incorrect import. Fix the import!
			array( 'Steve', 1.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y="value"] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_checkbox_field() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => '493ito', 'uc580i' => 'Green' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Single Line Text', $graph_atts );
		$expected_data['rows'] = array(
			array( 'Jamie', 1.0 ),
			array( 'Steve', 1.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y="value"] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_user_id_field() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => '493ito', 't1eqkj' => '2' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Single Line Text', $graph_atts );
		$expected_data['rows'] = array(
			array( 'Steph', 1.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x start_date="-100 years"] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_with_start_date() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => '493ito', 'start_date' => '-100 years' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x start_date="-100 years"] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_with_specific_start_date() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => '493ito', 'start_date' => '2015-05-13' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x start_date="-100 years"] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_with_specific_start_date_no_entries() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => '493ito', 'start_date' => '2015-05-14' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		self::run_no_data_graph_tests( $graph_html );
	}

	/**
	 * Check [frm-graph id=x end_date="+100 years"] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_with_end_date() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => '493ito', 'end_date' => '+100 years' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x end_date="Y-m-d"] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_with_specific_end_date() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => '493ito', 'end_date' => '2015-05-14' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x end_date="-100 years"] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_with_end_date_no_entries() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => '493ito', 'end_date' => '-100 years' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		self::run_no_data_graph_tests( $graph_html );
	}

	/**
	 * Check [frm-graph id=x response_count="2"] where x is a single line text field
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_with_response_count() {
		$this->markTestSkipped( 'Fails in 2.2' );

		self::clear_frm_vars();
		$graph_atts = array( 'id' => '493ito', 'response_count' => '2' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Single Line Text', $graph_atts );
		$expected_data['rows'] = array(
			array( 'Jamie', '1' ),
			array( 'Steph', '1' ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check title, title_font, title_size, x_title, and y_title
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_with_title_params() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '493ito',
			'title' => 'Jamie\'s Graph',
			'title_font' => 'Arial',
			'title_size' => '50px',
			'x_title' => 'My x-axis',
			'y_title' => 'My y-axis',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check tooltip_label, colors, bg_color, grid_color, height, and width
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_with_multiple_display_params() {
		self::clear_frm_vars();

		$graph_atts = array
		(
			'id' => '493ito',
			'tooltip_label' => 'Leads',
			'colors' => '#EF8C08,#21759B,#1C9E05',
			'bg_color' => '#000000',
			'grid_color' => '#FFFFFF',
			'height' => '100',
			'width' => '100%',
			'truncate' => '3',
			'truncate_label' => '3',// Fails
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check min and max
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_with_min_and_max() {
		self::clear_frm_vars();

		$graph_atts = array
		(
			'id' => '493ito',
			'min' => '1',
			'max' => '2',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check is3d and show_key
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_with_is3d_and_show_key() {
		self::clear_frm_vars();

		$graph_atts = array
		(
			'id' => '493ito',
			'is3d' => '1',
			'show_key' => '1',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_expected_data_for_text_field( $graph_atts );
		$expected_data['options']['legend'] = array(
			'position' => 'right',
			'textStyle' => array( 'fontSize' => 10 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check is3d and show_key
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '493ito',
			'ids' => 'uc580i,4t3qo4',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	* Check [frm-graph id=x ids=y,z user_id=1]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_user_id_filter() {
		self::clear_frm_vars();

		$field_key_two = 'uc580i';
		$field_key_three = '4t3qo4';
		$graph_atts = array
		(
			'id' => '493ito',
			'ids' => $field_key_two . ',' . $field_key_three,
			'user_id' => '1',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_expected_data_for_text_field( $graph_atts );
		foreach ( $expected_data['rows'] as $key => $value ) {
			$expected_data['rows'][ $key ][1] = 1.0;
			$expected_data['rows'][ $key ]['tooltip'] = str_replace( '3', '1', $value['tooltip'] );
		}

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y,z entry_id=1]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_entry_id_filter() {
		self::clear_frm_vars();

		$field_key_two = 'uc580i';
		$field_key_three = '4t3qo4';
		$entry_id = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$graph_atts = array(
			'id' => '493ito',
			'ids' => $field_key_two . ',' . $field_key_three,
			'entry_id' => $entry_id,
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_expected_data_for_text_field( $graph_atts );
		foreach ( $expected_data['rows'] as $key => $value ) {
			$expected_data['rows'][ $key ][1] = 1.0;
			$expected_data['rows'][ $key ]['tooltip'] = str_replace( '3', '1', $value['tooltip'] );
		}

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y,z entry_ids=1,2]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_entry_ids_filter() {
		$this->markTestSkipped( 'Fails in 2.2' );

		self::clear_frm_vars();

		$field_key_two = 'uc580i';
		$field_key_three = '4t3qo4';
		$entry_id_one = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$entry_id_two = FrmEntry::get_id_by_key( 'steph_entry_key' );
		$graph_atts = array(
			'id' => '493ito',
			'ids' => $field_key_two . ',' . $field_key_three,
			'entry_id' => $entry_id_one . ',' . $entry_id_two,
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_expected_data_for_text_field( $graph_atts );
		foreach ( $expected_data['rows'] as $key => $value ) {
			$expected_data['rows'][ $key ][1] = 2.0;
			$expected_data['rows'][ $key ]['tooltip'] = str_replace( '3', '2', $value['tooltip'] );
		}

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y,z dropdown=value]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_filter_by_dropdown_id() {
		$this->markTestSkipped( 'Fails in 2.2' );

		self::clear_frm_vars();

		$field_key_two = 'uc580i';
		$field_key_three = '4t3qo4';
		$dropdown_id = FrmField::get_id_by_key( '54tffk' );
		$graph_atts = array(
			'id' => '493ito',
			'ids' => $field_key_two . ',' . $field_key_three,
			$dropdown_id => 'Ace Ventura',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_expected_data_for_text_field( $graph_atts );
		foreach ( $expected_data['rows'] as $key => $value ) {
			$expected_data['rows'][ $key ][1] = 2.0;
			$expected_data['rows'][ $key ]['tooltip'] = str_replace( '3', '2', $value['tooltip'] );
		}

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y,z start_date='-100 years']
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_with_start_date() {
		self::clear_frm_vars();

		$field_key_two = 'uc580i';
		$field_key_three = '4t3qo4';
		$graph_atts = array
		(
			'id' => '493ito',
			'ids' => $field_key_two . ',' . $field_key_three,
			'start_date' => '-100 years',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y,z start_date='+100 years']
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_with_start_date_no_entries() {
		$this->markTestSkipped( 'Fails in 2.2' );

		self::clear_frm_vars();

		$field_key_two = 'uc580i';
		$field_key_three = '4t3qo4';
		$graph_atts = array
		(
			'id' => '493ito',
			'ids' => $field_key_two . ',' . $field_key_three,
			'start_date' => '+100 years',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		self::run_no_data_graph_tests( $graph_html );
	}

	/**
	 * Check [frm-graph id=x ids=y,z end_date='+100 years']
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_with_end_date() {
		self::clear_frm_vars();

		$field_key_two = 'uc580i';
		$field_key_three = '4t3qo4';
		$graph_atts = array
		(
			'id' => '493ito',
			'ids' => $field_key_two . ',' . $field_key_three,
			'end_date' => '+100 years',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y,z end_date='-100 years']
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_with_end_date_no_entries() {
		$this->markTestSkipped( 'Fails in 2.2' );

		self::clear_frm_vars();

		$field_key_two = 'uc580i';
		$field_key_three = '4t3qo4';
		$graph_atts = array
		(
			'id' => '493ito',
			'ids' => $field_key_two . ',' . $field_key_three,
			'end_date' => '-100 years',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		self::run_no_data_graph_tests( $graph_html );
	}

	/**
	 * Check [frm-graph id=x ids=y,z] with title, title_fonr, title_size, x_title, and y_title
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_with_title_params() {
		self::clear_frm_vars();

		$field_key_two = 'uc580i';
		$field_key_three = '4t3qo4';
		$graph_atts = array
		(
			'id' => '493ito',
			'ids' => $field_key_two . ',' . $field_key_three,
			'title' => 'Jamie\'s Graph',
			'title_font' => 'Arial',
			'title_size' => '50px',
			'x_title' => 'My x-axis',
			'y_title' => 'My y-axis',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y,z] with tooltip_label, color, bg_color, grid_color, height, and width
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_with_multiple_display_params() {
		self::clear_frm_vars();

		$field_key_two = 'uc580i';
		$field_key_three = '4t3qo4';
		$graph_atts = array
		(
			'id' => '493ito',
			'ids' => $field_key_two . ',' . $field_key_three,
			'tooltip_label' => 'Leads',
			'colors' => '#EF8C08,#21759B,#1C9E05',
			'bg_color' => '#000000',
			'grid_color' => '#FFFFFF',
			'height' => '100',
			'width' => '100%',
			'truncate' => '3',
			'truncate_label' => '3',// Fails
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_expected_data_for_text_field( $graph_atts );
		$expected_data['rows'][0][0] = 'Leads';
		$expected_data['rows'][0]['tooltip'] = 'Leads: 3';

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y,z] with min, max, is3d, and show_key
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_with_max_and_min() {
		self::clear_frm_vars();

		$field_key_two = 'uc580i';
		$field_key_three = '4t3qo4';
		$graph_atts = array(
			'id' => '493ito',
			'ids' => $field_key_two . ',' . $field_key_three,
			'min' => '1',
			'max' => '2',
			'is3d' => '1',
			'show_key' => '1',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_expected_data_for_text_field( $graph_atts );
		$expected_data['options']['legend'] = array(
			'position' => 'right',
			'textStyle' => array( 'fontSize' => 10 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="created_at"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_with_x_axis_created_at() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'created_at',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Dropdown', $graph_atts );
		$expected_data['rows'][0] = array( '05/13/2015', 3.0 );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="date-field"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_with_x_axis_date_field() {
		self::clear_frm_vars();

		$x_axis_id = FrmField::get_id_by_key( 'f67hbu' );
		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => $x_axis_id,
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_dropdown( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_order=0]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_with_x_order() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_order' => '0',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Dropdown', $graph_atts );
		$expected_data['rows'] = array(
			array( 'William Wells', 1.0 ),
			array( 'Ace Ventura', 2.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="date-field-key"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_with_x_axis_date_field_key() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_dropdown( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="date-field" user_id="1"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_with_x_axis_date_field_filter_by_user_id() {
		self::clear_frm_vars();
		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
			'user_id' => '1',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Dropdown', $graph_atts );
		$expected_data['rows'] = array(
			array( '08/16/2015', 1.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="date-field" user_id="current"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_with_x_axis_date_field_filter_by_user_id_current() {
		self::clear_frm_vars();
		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
			'user_id' => 'current',
		);

		$this->set_current_user_to_1();

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Dropdown', $graph_atts );
		$expected_data['rows'] = array( array( '08/16/2015', 1.0 ) );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="date-field" entry_id="y"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_with_x_axis_date_field_filter_by_entry_id() {
		self::clear_frm_vars();
		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
			'entry_id' => FrmEntry::get_id_by_key( 'jamie_entry_key'),
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Dropdown', $graph_atts );
		$expected_data['rows'] = array( array( '08/16/2015', 1.0 ) );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="date-field" entry_id="y,z"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_with_x_axis_date_field_filter_by_entry_ids() {
		self::clear_frm_vars();
		$entry_one = FrmEntry::get_id_by_key( 'jamie_entry_key');
		$entry_two = FrmEntry::get_id_by_key( 'steph_entry_key');
		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
			'entry_id' => $entry_one . ',' . $entry_two,
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Dropdown', $graph_atts );
		$expected_data['rows'] = array(
			array( '07/08/2015', 1.0 ),
			array( '08/16/2015', 1.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="date-field" entry_id="y,z"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_with_x_axis_date_field_filter_by_checkbox() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
			'uc580i' => 'Green',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Dropdown', $graph_atts );
		$expected_data['rows'] = array(
			array( '01/24/2015', 1.0 ),
			array( '08/16/2015', 1.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="date-field" start_date="-100 years"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_x_axis_date_field_with_start_date() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
			'start_date' => '-100 years',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_dropdown( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="date-field" start_date="+100 years"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_x_axis_date_field_with_start_date_no_entries() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
			'start_date' => '+100 years',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		self::run_no_data_graph_tests( $graph_html );
	}

	/**
	 * Check [frm-graph id=x x_axis="date-field" start_date="Y-m-d"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_x_axis_date_field_with_start_date_specific() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
			'start_date' => '2015-06-01',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Dropdown', $graph_atts );
		$expected_data['rows'] = array(
			array( '07/08/2015', 1.0 ),
			array( '08/16/2015', 1.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="date-field" end_date="+100 years"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_x_axis_date_field_with_end_date() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
			'end_date' => '+100 years',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_dropdown( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="date-field" end_date="-100 years"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_x_axis_date_field_with_end_date_no_entries() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
			'end_date' => '-100 years',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		self::run_no_data_graph_tests( $graph_html );
	}

	/**
	 * Check [frm-graph id=x x_axis="date-field" start_date="2015-01-01" end_date="2016-01-01"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_x_axis_date_field_with_start_and_end_date() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
			'start_date' => '2015-01-01',
			'end_date' => '2016-01-01',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_dropdown( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis=y] with title, title_font, title_size, x_title, and y_title
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_x_axis_with_title_params() {
		self::clear_frm_vars();

		$graph_atts = array
		(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
			'title' => 'Jamie\'s Graph',
			'title_font' => 'Arial',
			'title_size' => '50px',
			'x_title' => 'My x-axis',
			'y_title' => 'My y-axis',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_dropdown( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis=y] with tooltip_label, color, bg_color, grid_color, height, and width
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_x_axis_with_multiple_display_params() {
		self::clear_frm_vars();

		$graph_atts = array
		(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
			'tooltip_label' => 'Leads',
			'colors' => '#EF8C08,#21759B,#1C9E05',
			'bg_color' => '#000000',
			'grid_color' => '#FFFFFF',
			'height' => '100',
			'width' => '100%',
			'truncate' => '3',
			'truncate_label' => '3',// Fails
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_dropdown( $graph_atts );

		//$expected_data['rows'][0][0] = 'Leads';
		//$expected_data['rows'][0]['tooltip'] = 'Leads: 3';

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis=y] with min, max, is3d, and show_key
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_x_axis_with_max_and_min() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
			'min' => '1',
			'max' => '2',
			'is3d' => '1',
			'show_key' => '1',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_dropdown( $graph_atts );
		$expected_data['options']['legend'] = array(
			'position' => 'right',
			'textStyle' => array( 'fontSize' => 10 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x data_type=total]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_data_type_total() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => 'msyehy',
			'data_type' => 'total',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_number_field( $graph_atts );
		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x data_type=total x_axis="date_field"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_data_type_total_and_x_axis() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => 'msyehy',
			'data_type' => 'total',
			'x_axis' => 'f67hbu',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Number', $graph_atts );
		$expected_data['rows'] = array(
			array( '01/24/2015', 5.0 ),
			array( '07/08/2015', 1.0 ),
			array( '08/16/2015', 10.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y data_type=total x_axis="date_field"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_data_type_total_multiple_ids_and_x_axis() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => 'msyehy',
			'ids' => 'qbrd2o',
			'data_type' => 'total',
			'x_axis' => 'f67hbu',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Number', $graph_atts );
		$expected_data['rows'] = array(
			array( '01/24/2015', 5.0, 8.0 ),
			array( '07/08/2015', 1.0, 8.0 ),
			array( '08/16/2015', 10.0, 5.0 ),
		);
		$expected_data['cols'][2] = array(
			'type' => 'number',
			'name' => 'Scale',
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y data_type=total]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_data_type_total_multiple_ids() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => 'msyehy',
			'ids' => 'qbrd2o',
			'data_type' => 'total',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Number', $graph_atts );
		$expected_data['rows'] = array(
			array(
				0 => 'Number',
				1 => 16.0,
				'tooltip' => 'Number: 16',
			),
			array(
				0 => 'Scale',
				1 => 21.0,
				'tooltip' => 'Scale: 21',
			),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y x_axis="date_field"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_ids_and_x_axis() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'ids' => 'qbrd2o',
			'x_axis' => 'f67hbu',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Dropdown', $graph_atts );
		$expected_data['rows'] = array(
			array( '01/24/2015', 1.0, 1.0 ),
			array( '07/08/2015', 1.0, 1.0 ),
			array( '08/16/2015', 1.0, 1.0 ),
		);
		$expected_data['cols'][ 2 ] = array( 'type' => 'number', 'name' => 'Scale' );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x include_zero="1"]
	 * Graphs a checkbox field and should show blank option (Purple)
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_include_zero() {
		$this->markTestSkipped( 'Fails in 2.2' );
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => 'uc580i',
			'include_zero' => '1',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_checkbox( $graph_atts );
		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x include_zero="1" x_axis="date-field"]
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_include_zero_with_x_axis() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
			'include_zero' => '1',
			'start_date' => '2015-01-20',
			'end_date' => '2015-01-30',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Dropdown', $graph_atts );
		for ( $i = 20; $i <= 30; $i++ ) {
			if ( $i == 24 ) {
				$count = 1.0;
			} else {
				$count = 0.0;
			}
			$expected_data['rows'][] = array( '01/' . $i . '/2015', $count );
		}
		$expected_data['options']['hAxis']['showTextEvery'] = 2.0;

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x include_zero="1" x_axis="date-field"]
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_ids_include_zero_with_x_axis() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'ids' => '493ito',
			'x_axis' => 'f67hbu',
			'include_zero' => '1',
			'start_date' => '2015-01-20',
			'end_date' => '2015-01-30',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Dropdown', $graph_atts );
		for ( $i = 20; $i <= 30; $i++ ) {
			if ( $i == 24 ) {
				$count = 1.0;
			} else {
				$count = 0.0;
			}
			$expected_data['rows'][] = array( '01/' . $i . '/2015', $count, $count );
		}
		$expected_data['cols'][ 2 ] = array( 'type' => 'number', 'name' => 'Single Line Text' );
		$expected_data['options']['hAxis']['showTextEvery'] = 2.0;

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="date-field" group_by="month"]
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_x_axis_with_group_by_month() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
			'group_by' => 'month',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Dropdown', $graph_atts );
		$expected_data['rows'] = array(
			array( 'January 2015', 1.0 ),
			array( 'July 2015', 1.0 ),
			array( 'August 2015', 1.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="date-field" group_by="month" include_zero="1"]
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_x_axis_with_group_by_month_include_zero() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
			'group_by' => 'month',
			'include_zero' => '1',
			'start_date' => '2015-06-01',
			'end_date' => '2015-08-31',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Dropdown', $graph_atts );
		$expected_data['rows'] = array(
			array( 'June 2015', 0.0 ),
			array( 'July 2015', 1.0 ),
			array( 'August 2015', 1.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="date-field" group_by="quarter"]
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_x_axis_with_group_by_quarter() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
			'group_by' => 'quarter',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Dropdown', $graph_atts );
		$expected_data['rows'] = array(
			array( 'Q1 2015', 1.0 ),
			array( 'Q3 2015', 2.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="checkbox"]
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_x_axis_checkbox() {
		$this->markTestSkipped( 'Fails in 2.2 - but test needs to be updated as well' );
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'uc580i',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Dropdown', $graph_atts );
		$expected_data['rows'] = array(
			array( 'Red', 3.0 ),
			array( 'Green', 2.0 ),
			array( 'Blue', 2.0 ),
		);
		$expected_data['cols'][ 2 ] = array( 'type' => 'number', 'name' => 'Checkboxes - colors' );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="dropdown"]
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_x_axis_dropdown() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '493ito',
			'x_axis' => '54tffk',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Single Line Text', $graph_atts );
		$expected_data['rows'] = array(
			array( 'William Wells', 1.0 ),
			array( 'Ace Ventura', 2.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x limit="1"]
	 * x is a checkbox field and only the option with the most entries should show
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_with_limit() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => 'uc580i',
			'limit' => '1',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Checkboxes - colors', $graph_atts );
		$expected_data['rows'][ 0 ] = array( 'Red', 3.0 );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x field="object" is3d="true" min="0" colors="x,y,z" width="650" bg_color="transparent"]
	 * Imitates the show_reports call to the graph shortcode
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_with_field_object() {
		self::clear_frm_vars();

		$field_key = '493ito';
		$text_field = FrmField::getOne( $field_key );

		$graph_atts = array(
			'id' => $field_key,
			'field' => $text_field,
			'is3d' => true,
			'min' => 0,
			'colors' => '#21759B,#EF8C08,#C6C6C6',
			'width' => 650,
			'bg_color' => 'transparent',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="date-field" x_start="date" x_end="date"]
	 * Imitates the show_reports call to the graph shortcode
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_with_x_start_and_x_end() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'f67hbu',
			'x_start' => '2015-02-02',
			'x_end' => '2015-08-01',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( 'Dropdown', $graph_atts );
		$expected_data['rows'] = array(
			array( '07/08/2015', 1.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	function get_expected_data_for_text_field( $graph_atts ) {
		$expected_data = self::get_graph_defaults( 'Single Line Text', $graph_atts );

		if ( isset( $graph_atts['ids'] ) && $graph_atts['ids'] == 'uc580i,4t3qo4' ) {
			//ids
			$expected_data['rows'] = array(
				array(
					0 => 'Single Line Text',
					1 => 3.0,
					'tooltip' => 'Single Line Text: 3',
				),
				array(
					0 => 'Checkboxes - colors',
					1 => 3.0,
					'tooltip' => 'Checkboxes - colors: 3',
				),
				array(
					0 => 'Radio Buttons - dessert',
					1 => 3.0,
					'tooltip' => 'Radio Buttons - dessert: 3',
				),
			);
		} else {
			$expected_data['rows'] = array(
				array( 'Jamie', 1.0 ),
				array( 'Steph', 1.0 ),
				array(  'Steve', 1.0 ),
			);
		}

		return $expected_data;
	}

	function get_expected_data_for_country_field( $graph_atts ) {
		$expected_data = self::get_graph_defaults( 'Country', $graph_atts );

		// Basic data
		$expected_data['rows'] = array(
			array( 'Brazil', 1.0 ),
			array( 'United States', 1.0 ),
		);

		return $expected_data;
	}

	function get_expected_data_for_dropdown( $graph_atts ) {
		$expected_data = self::get_graph_defaults( 'Dropdown', $graph_atts );

		$date_field_id = FrmField::get_id_by_key( 'f67hbu' );
		$x_axis = isset( $graph_atts['x_axis'] ) ? $graph_atts['x_axis'] : '';

		if ( $x_axis == 'f67hbu' || $x_axis == $date_field_id  ) {
			// x_axis=date-field
			$expected_data['rows'][0] = array( '01/24/2015', 1.0 );
			$expected_data['rows'][1] = array( '07/08/2015', 1.0 );
			$expected_data['rows'][2] = array( '08/16/2015', 1.0 );
		}

		return $expected_data;

	}

	function get_expected_data_for_number_field( $graph_atts ) {
		$expected_data = self::get_graph_defaults( 'Number', $graph_atts );

		$expected_data['rows'] = array(
			array( '1', 1.0 ),
			array( '5', 1.0 ),
			array( '10', 1.0 ),
		);

		return $expected_data;
	}

	function get_expected_data_for_checkbox( $graph_atts ) {
		$expected_data = self::get_graph_defaults( 'Checkboxes - colors', $graph_atts );

		$expected_data['rows'] = array(
			array( 'Blue', 2.0 ),
			array( 'Green', 2.0 ),
			array( 'Red', 3.0 ),
		);

		return $expected_data;
	}

	function get_graph_defaults( $field_name, $graph_atts ) {
		$atts = self::convert_atts_to_expected_data_atts( $field_name, $graph_atts );

		$expected_data = array(
			'data' => array(),
			'options' => array(
				'width' => $atts['width'],
				'height' => $atts['height'],
				'legend' => 'none',
				'title' => $atts['title'],
				'hAxis' => array(
					'slantedText' => true,
					'slantedTextAngle' => 20,
				),
				'vAxis' => array(
					'gridlines' => array( 'color' => $atts['v_axis_color'] ),
				),
				'backgroundColor' => $atts['bg_color'],
				'is3D' => $atts['is3d'],
			),
			'type' => $atts['type'],
			'graph_id' => '_frm_' . strtolower( $atts['type'] ) . '1',
			'rows' => array(),
			'cols' => array(
				0 => array(
					'type' => 'string',
					'name' => 'xaxis',
				),
				1 => array(
					'type' => 'number',
					'name' => $atts['col_name'],
				),
			),
		);

		if ( isset( $graph_atts['colors'] ) ) {
			$expected_data['options']['colors'] = explode( ',', $graph_atts['colors'] );
		}

		if ( isset( $graph_atts['min'] ) && isset( $graph_atts['max'] ) ) {
			$expected_data['options']['vAxis']['viewWindow'] = array(
				'max' => $graph_atts['max'],
				'min' => $graph_atts['min'],
			);
		}

		if ( isset( $graph_atts['x_axis'] ) ) {
			$date_field_id = FrmField::get_id_by_key( 'f67hbu' );

			if ( $graph_atts['x_axis'] == '54tffk' ) {
				$expected_data[ 'options' ][ 'hAxis' ][ 'title' ] = 'Dropdown';
			}

			if ( $graph_atts['x_axis'] == 'f67hbu' || $graph_atts['x_axis'] == $date_field_id ) {
				$expected_data[ 'options' ][ 'hAxis' ][ 'title' ] = 'Date';
			}
		}

		if ( isset( $graph_atts['title_font'] ) && $graph_atts['title_size'] ) {
			$expected_data['options']['titleTextStyle'] = array(
				'fontSize' => $graph_atts['title_size'],
				'fontName' => $graph_atts['title_font'],
			);
		}

		if ( isset( $graph_atts['x_title'] ) ) {
			$expected_data['options']['hAxis']['title'] = $graph_atts['x_title'];
		}

		if ( isset( $graph_atts['y_title'] ) ) {
			$expected_data['options']['vAxis']['title'] = $graph_atts['y_title'];
		}

		return $expected_data;
	}

	function convert_atts_to_expected_data_atts( $field_name, $graph_atts ) {
		$atts = array();

		if ( isset( $graph_atts['type'] ) && $graph_atts['type'] != 'bar' ) {
			$atts['type'] = $graph_atts['type'];
		} else {
			$atts['type'] = 'column';
		}

		$default_width = $atts['type'] == 'geo' ? 600 : 400;
		$atts['width'] = isset( $graph_atts['width'] ) ? $graph_atts['width'] : $default_width;
		$atts['height'] = isset( $graph_atts['height'] ) ? $graph_atts['height'] : 400;
		$atts['bg_color'] = isset( $graph_atts['bg_color'] ) ? $graph_atts['bg_color'] : '#FFFFFF';
		$atts['is3d'] = isset( $graph_atts['is3d'] ) && $graph_atts['is3d'];
		$atts['title'] = isset( $graph_atts['title'] ) ? $graph_atts['title'] : $field_name;
		$atts['col_name'] = isset( $graph_atts['tooltip_label'] ) ? $graph_atts['tooltip_label'] : $field_name;
		$atts['v_axis_color'] = isset( $graph_atts['grid_color'] ) ? $graph_atts['grid_color'] : '#CCC';

		if ( isset( $graph_atts['truncate'] ) ) {
			$atts['title'] = substr( $atts['title'], 0, $graph_atts['truncate'] ) . '...';
		}

		return $atts;
	}


	function modify_expected_data_for_pie_graph( &$expected_data ) {
		$expected_data['cols'] = array(
			0 => array(
				'type' => 'string',
				'name' => 'Field',
			),
			1 => array(
				'type' => 'number',
				'name' => 'Entries',
			),
		);

		unset( $expected_data['options']['hAxis'] );
		unset( $expected_data['options']['vAxis'] );
	}

	function run_graph_tests( $graph_html, $expected_data ) {
		global $frm_vars;

		$expected_html = '<div id="chart_' . $expected_data['graph_id'] . '" style="height:' .
			$expected_data['options']['height'] . ';width:' . $expected_data['options']['width'] . '"></div>';
		$this->assertEquals( $expected_html, $graph_html );

		$this->assertNotEmpty(
			$frm_vars['google_graphs'],
			'The global $frm_vars google_graphs variable is not getting set as expected'
		);

		if ( $expected_data['type'] == 'geo' ) {
			$corechart = 'geochart';
		} else {
			$corechart = 'corechart';
		}

		$actual_data = $frm_vars['google_graphs'][ $corechart ][0];
		print_r( $frm_vars['google_graphs'][ $corechart ][0] );

		self::compare_actual_to_expected_graph_values( $expected_data, $actual_data );

	}

	function compare_actual_to_expected_graph_values( $expected_data, $actual_data, $prev_key = '' ) {
		foreach ( $expected_data as $data_key => $e_data ) {
			if ( is_array( $e_data ) ) {
				$this->assertEquals( count( $e_data ), count( $actual_data[ $data_key ] ), 'The number of items in the ' . $prev_key . $data_key . ' variable is not correct.' );

				if ( empty( $e_data ) ) {
					$this->assertEquals( $e_data, $actual_data[ $data_key ], 'The ' . $prev_key . $data_key . ' variable is not set as expected.' );
				} else {
					self::compare_actual_to_expected_graph_values( $e_data, $actual_data[ $data_key ], $data_key . ' - ' );
				}
			} else {
				$msg = 'The ' . $prev_key . $data_key . ' variable is not set as expected ';
				$msg .= '(' . $e_data . ' - ' . gettype( $e_data ) . ' is not equal to ';
				$msg .= $actual_data[ $data_key ] . '-' . gettype( $actual_data[ $data_key ] ) . ')';
				$this->assertTrue( $e_data === $actual_data[ $data_key ], $msg );
			}
		}
	}

	function run_no_data_graph_tests( $graph_html ) {
		$no_data_html = '<div class="frm_no_data_graph">No Data</div>';
		$this->assertEquals( $no_data_html, $graph_html, 'Data is showing when there should be no entries found.' );
	}

	function clear_frm_vars() {
		global $frm_vars;
		unset( $frm_vars['google_graphs'] );
	}
}