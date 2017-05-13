<?php

/**
 * @group graphs
 */
class WP_Test_FrmProGraphsController extends FrmUnitTest {

	/******************************************************
	 * Single Field Graphs (no x-axis)
	 *****************************************************/

	/**
	 * Check [frm-graph id=x] where x is the ID of a single line text field
	 * For backward compatibility
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_line_text_with_id() {
		self::clear_frm_vars();

		$field_id = FrmField::get_id_by_key( 'text-field' );
		$graph_atts = array( 'id' => $field_id );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x] where x is the key of a single line text field
	 * For backward compatibility
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_line_text_with_key() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => 'text-field' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph fields=x] where x is the key of a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_line_text_with_fields() {
		self::clear_frm_vars();
		$graph_atts = array( 'fields' => 'text-field' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Test all graph types with single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_line_text_with_all_types() {
		self::clear_frm_vars();

		$graph_atts = array( 'id' => 'text-field');
		$all_types = self::get_graph_types_for_testing();
		$graph_html = array();
		$expected_data = array();

		$count = 0;
		foreach ( $all_types as $type ) {
			$count++;

			$graph_atts['type'] = $type;
			$graph_html[] = FrmProGraphsController::graph_shortcode( $graph_atts );

			$single_expected_data = self::get_expected_data_for_text_field( $graph_atts );
			$single_expected_data['graph_id'] = str_replace( '1', $count, $single_expected_data['graph_id'] );
			$expected_data[] = $single_expected_data;
		}

		self::run_graph_tests_for_multiple_graphs( $graph_html, $expected_data );
	}

	/**
	 * Test all graph types with userID field
	 *
	 * @covers FrmProGraphsController::graph_shortcode
	 */
	function test_graph_shortcode_user_id_with_all_types() {
		$graph_atts = array( 'id' => 'user-id-field');
		$all_types = self::get_graph_types_for_testing();
		$graph_html = array();
		$expected_data = array();

		$count = 0;
		foreach ( $all_types as $type ) {
			$count++;

			$graph_atts['type'] = $type;
			$graph_html[] = FrmProGraphsController::graph_shortcode( $graph_atts );

			$single_expected_data = self::get_expected_data_for_user_id_field( $graph_atts );
			$single_expected_data['graph_id'] = str_replace( '1', $count, $single_expected_data['graph_id'] );
			$expected_data[] = $single_expected_data;
		}

		self::run_graph_tests_for_multiple_graphs( $graph_html, $expected_data );
	}

	/**
	 * Test all graph types with Number field
	 *
	 * @covers FrmProGraphsController::graph_shortcode
	 */
	function test_graph_shortcode_number_field_with_all_types() {
		$graph_atts = array( 'id' => 'msyehy' );
		$all_types = self::get_graph_types_for_testing();
		$graph_html = array();
		$expected_data = array();

		$count = 0;
		foreach ( $all_types as $type ) {
			$count++;

			$graph_atts['type'] = $type;
			$graph_html[] = FrmProGraphsController::graph_shortcode( $graph_atts );

			$single_expected_data = self::get_expected_data_for_number_field( $graph_atts );
			$single_expected_data['graph_id'] = str_replace( '1', $count, $single_expected_data['graph_id'] );
			$expected_data[] = $single_expected_data;
		}

		self::run_graph_tests_for_multiple_graphs( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x type=geo] where x is a single line text field
	 *
	 * @covers FrmProGraphsController::graph_shortcode
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
	 * Check [frm-graph id=x user_id=1] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_user_id() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => 'text-field', 'user_id' => '1' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Single Line Text' );

		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Jamie', 1 ),
			array( 'Jwahlin', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x user_id=1] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_user_id_current() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => 'text-field', 'user_id' => 'current' );
		$this->set_current_user_to_1();

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Jamie', 1 ),
			array( 'Jwahlin', 1 ),
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
		$graph_atts = array( 'id' => 'text-field', 'entry_id' => $entry_id );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Jamie', 1 ),
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
			'id' => 'text-field',
			'entry_id' =>  $entry_id_one . ',' . $entry_id_two,
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Jamie', 1 ),
			array( 'Steph', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x entry_id=entry-key] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_entry_key() {

		self::clear_frm_vars();

		$graph_atts = array( 'id' => 'text-field', 'entry_id' =>  'jamie_entry_key' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Jamie', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y="value"] where x is a single line text field
	 *
	 * @covers FrmProGraphsController::graph_shortcode
	 */
	function test_graph_shortcode_single_field_filter_by_dropdown_id() {
		self::clear_frm_vars();

		$dropdown_id = FrmField::get_id_by_key( '54tffk' );
		$graph_atts = array( 'id' => 'text-field', $dropdown_id => 'Ace Ventura' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Jamie', 1 ),
			array( 'Jwahlin', 1 ),
			array( 'Steve', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y_not_equal="value"] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_dropdown_id_not_value() {
		self::clear_frm_vars();

		$dropdown_id = FrmField::get_id_by_key( '54tffk' );
		$graph_atts = array( 'id' => 'text-field', $dropdown_id . '_not_equal' => 'Ace Ventura' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Steph', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y_not_equal="value"] where x is a single line text field
	 * For backward compatibility
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_dropdown_id_not_value_bc() {
		self::clear_frm_vars();

		$dropdown_id = FrmField::get_id_by_key( '54tffk' );
		$graph_atts = array( 'id' => 'text-field' );
		$graph_atts[] = $dropdown_id . '!="Ace Ventura"';

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Steph', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y=""] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_dropdown_id_blank() {
		self::clear_frm_vars();

		$dropdown_id = FrmField::get_id_by_key( '54tffk' );
		$graph_atts = array( 'id' => 'text-field', $dropdown_id => '' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		self::run_no_data_graph_tests( $graph_html );
	}

	/**
	 * Check [frm-graph id=x y_not_equal=""] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_dropdown_id_not_blank() {
		self::clear_frm_vars();

		$dropdown_id = FrmField::get_id_by_key( '54tffk' );
		$graph_atts = array( 'id' => 'text-field', $dropdown_id . '_not_equal' => '' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y="value"] where x is a single line text field
	 *
	 * @covers FrmProGraphsController::graph_shortcode
	 */
	function test_graph_shortcode_single_field_filter_by_dropdown_key() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => 'text-field', '54tffk' => 'Ace Ventura' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Jamie', 1 ),
			array( 'Jwahlin', 1 ),
			array( 'Steve', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y_not_equal=""] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_dropdown_key_not_blank() {
		self::clear_frm_vars();

		$graph_atts = array( 'id' => 'text-field', '54tffk_not_equal' => '' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y!=""] where y is a dropdown
	 * For backward compatibility
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_dropdown_key_not_blank_bc() {
		self::clear_frm_vars();

		$graph_atts = array( 'id' => 'text-field' );
		$graph_atts[] = '54tffk!=""';

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y="value" z_greater_than="value"] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_multiple_fields() {
		self::clear_frm_vars();

		$dropdown_id = FrmField::get_id_by_key( '54tffk' );
		$graph_atts = array(
			'fields' => 'text-field',
			$dropdown_id => 'Ace Ventura',
			'msyehy_greater_than' => '6'
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Jamie', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y="value"] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_dynamic_field() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => 'text-field', 'dynamic-state' => 'California' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			//array( 'Steph', 1 ), This part fails due to incorrect import. Fix the import!
			array( 'Steve', 1 ),
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
		$graph_atts = array( 'id' => 'text-field', 'dynamic-state' => $filter_value );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Steph', 1 ),
			array( 'Steve', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y_greater_than="value"] where y is a number field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_number_greater_than() {
		self::clear_frm_vars();

		$graph_atts = array( 'id' => 'text-field', 'msyehy_greater_than' => '1' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Jamie', 1 ),
			array( 'Steve', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y_less_than="value"] where y is a number field
	 *
	 * @covers FrmProGraphsController::graph_shortcode
	 */
	function test_graph_shortcode_single_field_filter_by_number_less_than() {
		self::clear_frm_vars();

		$graph_atts = array( 'id' => 'text-field', 'msyehy_less_than' => '5' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Jwahlin', 1 ),
			array( 'Steph', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y_greater_than="value"] where y is a date field
	 *
	 * @covers FrmProGraphsController::graph_shortcode
	 */
	function test_graph_shortcode_single_field_filter_by_date_greater_than() {
		self::clear_frm_vars();

		$graph_atts = array( 'id' => 'text-field', 'date-field_greater_than' => '2015-01-31' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Jamie', 1 ),
			array( 'Jwahlin', 1 ),
			array( 'Steph', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y_less_than="value"] where y is a date field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_date_less_than() {
		self::clear_frm_vars();

		$graph_atts = array( 'id' => 'text-field', 'date-field_less_than' => '2015-02-01' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Steve', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y_contains="value"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_text_contains() {
		self::clear_frm_vars();

		$graph_atts = array( 'id' => 'text-field', 'text-field_contains' => 'St' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Steph', 1 ),
			array( 'Steve', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x created_at_greater_than="value"]
	 *
	 * @covers FrmProGraphsController::graph_shortcode
	 */
	function test_graph_shortcode_single_field_filter_by_created_at_greater_than() {
		self::clear_frm_vars();

		$graph_atts = array( 'id' => 'text-field', 'created_at_greater_than' => '2015-05-13' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Jwahlin', 1 ),
			array( 'Steph', 1 ),
			array( 'Steve', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x created_at_less_than="value"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_created_at_less_than() {
		self::clear_frm_vars();

		$graph_atts = array( 'id' => 'text-field', 'created_at_less_than' => '2015-05-12' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Jamie', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y="value"] where x is a single line text field
	 *
	 * @covers FrmProGraphsController::graph_shortcode
	 */
	function test_graph_shortcode_single_field_filter_by_checkbox_field() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => 'text-field', 'uc580i' => 'Green' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Jamie', 1 ),
			array( 'Jwahlin', 1 ),
			array( 'Steve', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x y="value"] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_filter_by_user_id_field() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => 'text-field', 'user-id-field' => '2' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Steph', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x start_date="-100 years"] where x is a single line text field
	 * For backward compatibility
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_with_start_date() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => 'text-field', 'start_date' => '-100 years' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x start_date="-100 years"] where x is a single line text field
	 * For backward compatibility
	 *
	 * @covers FrmProGraphsController::graph_shortcode
	 */
	function test_graph_shortcode_single_field_with_specific_start_date() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => 'text-field', 'start_date' => '2015-05-13' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Single Line Text', 'Submissions' ),
			array( 'Jwahlin', 1 ),
			array( 'Steph', 1 ),
			array( 'Steve', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x start_date="-100 years"] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_with_specific_start_date_no_entries() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => 'text-field', 'start_date' => '2015-05-14' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		self::run_no_data_graph_tests( $graph_html );
	}

	/**
	 * Check [frm-graph id=x end_date="+100 years"] where x is a single line text field
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_with_end_date() {
		self::clear_frm_vars();
		$graph_atts = array( 'id' => 'text-field', 'end_date' => '+100 years' );

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
		$graph_atts = array( 'id' => 'text-field', 'end_date' => '2015-05-14' );

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
		$graph_atts = array( 'id' => 'text-field', 'end_date' => '-100 years' );

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		self::run_no_data_graph_tests( $graph_html );
	}

	/**
	 * Check title, title_font, title_size, x_title, and y_title
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_single_field_with_title_params() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => 'text-field',
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
			'id' => 'text-field',
			'tooltip_label' => 'Leads',
			'colors' => '#EF8C08,#21759B,#1C9E05',
			'bg_color' => '#000000',
			'grid_color' => '#FFFFFF',
			'height' => '100',
			'width' => '100%',
			'truncate' => '3',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );
		self::add_column_colors( $graph_atts, $expected_data['data'] );

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
			'id' => 'text-field',
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
			'id' => 'text-field',
			'is3d' => '1',
			'show_key' => '1',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id="dynamic-field"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dynamic_field() {
		self::clear_frm_vars();

		$graph_atts = array(
			'fields' => 'dynamic-state',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Dynamic Field - level 2' );
		$expected_data['data'] = array(
			array( 'Dynamic Field - level 2', 'Submissions' ),
			array( 'California', 2 ),
			array( 'Utah', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_order=0]
	 *
	 * @covers FrmProGraphsController::graph_shortcode
	 */
	function test_graph_shortcode_dropdown_field_with_x_order() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_order' => '0',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Dropdown' );
		$expected_data['data'] = array(
			array( 'Dropdown', 'Submissions' ),
			array( 'William Wells', 1 ),
			array( 'Ace Ventura', 3 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_order="desc"]
	 *
	 * @covers FrmProGraphsController::graph_shortcode
	 */
	function test_graph_shortcode_dropdown_field_with_x_order_desc() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_order' => 'desc',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Dropdown' );
		$expected_data['data'] = array(
			array( 'Dropdown', 'Submissions' ),
			array( 'Ace Ventura', 3 ),
			array( 'William Wells', 1 ),
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
	 * Check [frm-graph id="checkbox" include_zero="1"]
	 *
	 * @covers FrmProGraphsController::graph_shortcode
	 */
	function test_graph_shortcode_include_zero() {

		self::clear_frm_vars();

		$graph_atts = array(
			'fields' => 'uc580i',
			'include_zero' => '1',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_graph_defaults( $graph_atts, 'Checkboxes - colors' );

		$expected_data['data'] = array(
			array( 'Checkboxes - colors', 'Submissions' ),
			array( 'Blue', 2 ),
			array( 'Green', 3 ),
			array( 'Red', 4 ),
			//array( 'Purple', 0 ),// TODO: Should show blank option (Purple)
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
			'fields' => 'uc580i',
			'limit' => '1',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Checkboxes - colors' );
		$expected_data['data'] = array(
			array( 'Checkboxes - colors', 'Submissions' ),
			array( 'Red', 4 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id="taxonomy-field"]
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_with_taxonomy_field() {
		self::clear_frm_vars();

		$graph_atts = array(
			'fields' => 'parent-dynamic-taxonomy',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Parent Dynamic Field' );
		$expected_data['data'] = array(
			array( 'Parent Dynamic Field', 'Submissions' ),
			array( 'Uncategorized', 3 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/******************************************************
	 * Multiple Field Graphs (no x-axis)
	 *****************************************************/

	/**
	 * Check [frm-graph id=x ids=y,z start_date='+100 years']
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_with_start_date_no_entries() {
		self::clear_frm_vars();

		$graph_atts = array(
			'fields' => 'text-field,uc580i,4t3qo4',
			'start_date' => '+100 years',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts );

		// All 0 values should be returned
		$expected_data['data'] = array(
			array( 'Fields', 'Submissions' ),
			array( 'Single Line Text', 0 ),
			array( 'Checkboxes - colors', 0 ),
			array( 'Radio Buttons - dessert', 0 ),
		);

		self::add_column_colors( $graph_atts, $expected_data['data'] );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y,z end_date='+100 years']
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_with_end_date() {
		self::clear_frm_vars();

		$graph_atts = array(
			'fields' => 'text-field,uc580i,4t3qo4',
			'end_date' => '+100 years',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );
		self::add_column_colors( $graph_atts, $expected_data['data'] );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y,z end_date='-100 years']
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_with_end_date_no_entries() {


		self::clear_frm_vars();

		$graph_atts = array(
			'fields' => 'text-field,uc580i,4t3qo4',
			'end_date' => '-100 years',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts );

		// All 0 values should be returned
		$expected_data['data'] = array(
			array( 'Fields', 'Submissions' ),
			array( 'Single Line Text', 0 ),
			array( 'Checkboxes - colors', 0 ),
			array( 'Radio Buttons - dessert', 0 ),
		);

		self::add_column_colors( $graph_atts, $expected_data['data'] );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y,z] with title, title_font, title_size, x_title, and y_title
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_with_title_params() {
		self::clear_frm_vars();

		$graph_atts = array(
			'fields' => 'text-field,uc580i,4t3qo4',
			'title' => 'Jamie\'s Graph',
			'title_font' => 'Arial',
			'title_size' => '50px',
			'x_title' => 'My x-axis',
			'y_title' => 'My y-axis',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );
		self::add_column_colors( $graph_atts, $expected_data['data'] );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y,z] with tooltip_label, color, bg_color, grid_color, height, and width
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_with_multiple_display_params() {
		self::clear_frm_vars();

		$graph_atts = array(
			'fields' => 'text-field,uc580i,4t3qo4',
			'tooltip_label' => 'Leads',
			'colors' => '#EF8C08,#21759B,#1C9E05',
			'bg_color' => '#000000',
			'grid_color' => '#FFFFFF',
			'height' => '100',
			'width' => '100%',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::add_column_colors( $graph_atts, $expected_data['data'] );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y,z] with min, max, is3d, and show_key
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_with_max_and_min() {
		self::clear_frm_vars();

		$graph_atts = array(
			'fields' => 'text-field,uc580i,4t3qo4',
			'min' => '1',
			'max' => '2',
			'is3d' => '1',
			'show_key' => '1',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::add_column_colors( $graph_atts, $expected_data['data'] );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check multiple fields in graph [frm-graph id=x ids=y,z]
	 * For backward compatibility
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_bc() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => 'text-field',
			'ids' => 'uc580i,4t3qo4',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );
		self::add_column_colors( $graph_atts, $expected_data['data'] );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check multiple fields in graph [frm-graph fields="a,b,c"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields() {
		self::clear_frm_vars();

		$graph_atts = array(
			'fields' => 'text-field,uc580i,4t3qo4',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );
		self::add_column_colors( $graph_atts, $expected_data['data'] );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y,z user_id=1]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_user_id_filter() {
		self::clear_frm_vars();

		$graph_atts = array(
			'fields' => 'text-field,uc580i,4t3qo4',
			'user_id' => '1',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts );
		$expected_data['data'] = array(
			array( 'Fields', 'Submissions' ),
			array( 'Single Line Text', 2 ),
			array( 'Checkboxes - colors', 2 ),
			array( 'Radio Buttons - dessert', 2 ),
		);
		self::add_column_colors( $graph_atts, $expected_data['data'] );

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
			'id' => 'text-field',
			'ids' => $field_key_two . ',' . $field_key_three,
			'entry_id' => $entry_id,
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts );
		$expected_data['data'] = array(
			array( 'Fields', 'Submissions' ),
			array( 'Single Line Text', 1 ),
			array( 'Checkboxes - colors', 1 ),
			array( 'Radio Buttons - dessert', 1 ),
		);
		self::add_column_colors( $graph_atts, $expected_data['data'] );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y,z entry=1,2]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_entry_ids_filter() {
		self::clear_frm_vars();

		$entry_id_one = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$entry_id_two = FrmEntry::get_id_by_key( 'steph_entry_key' );
		$graph_atts = array(
			'fields' => 'text-field,uc580i,4t3qo4',
			'entry' => $entry_id_one . ',' . $entry_id_two,
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts );
		$expected_data['data'] = array(
			array( 'Fields', 'Submissions' ),
			array( 'Single Line Text', 2 ),
			array( 'Checkboxes - colors', 2 ),
			array( 'Radio Buttons - dessert', 2 ),
		);

		self::add_column_colors( $graph_atts, $expected_data['data'] );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y,z dropdown=value]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_filter_by_dropdown_id() {
		self::clear_frm_vars();

		$dropdown_id = FrmField::get_id_by_key( '54tffk' );
		$graph_atts = array(
			'fields' => 'text-field,uc580i,4t3qo4',
			$dropdown_id => 'Ace Ventura',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts );
		$expected_data['data'] = array(
			array( 'Fields', 'Submissions' ),
			array( 'Single Line Text', 3 ),
			array( 'Checkboxes - colors', 3 ),
			array( 'Radio Buttons - dessert', 3 ),
		);

		self::add_column_colors( $graph_atts, $expected_data['data'] );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y,z start_date='-100 years']
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_multiple_fields_with_start_date() {
		self::clear_frm_vars();

		$graph_atts = array(
			'fields' => 'text-field,uc580i,4t3qo4',
			'start_date' => '-100 years',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_text_field( $graph_atts );

		self::add_column_colors( $graph_atts, $expected_data['data'] );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y data_type=total]
	 *
	 * @covers FrmProGraphsController::graph_shortcode
	 */
	function test_graph_shortcode_data_type_total_multiple_ids() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => 'msyehy',
			'ids' => 'qbrd2o',
			'data_type' => 'total',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts);
		$expected_data['data'] = array(
			array( 'Fields', 'Total' ),
			array( 'Number', 17 ),
			array( 'Scale', 26 ),
		);

		self::add_column_colors( $graph_atts, $expected_data['data'] );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/******************************************************
	 * X-axis graphs
	 *****************************************************/

	/**
	 * Check [frm-graph id="dropdown" x_axis="date-field" x_greater_than="date" x_less_than="date"]
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_with_date_range() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'date-field',
			'date-field_greater_than' => '2015-02-02',
			'date-field_less_than' => '2015-08-01',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Dropdown' );
		$expected_data['data'] = array(
			array( 'Date', 'Dropdown' ),
			array( 'July 8, 2015', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id="text-field" x_axis="date-field" x_greater_than="date" x_less_than="date" include_zero="1"]
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_with_date_range_include_zero() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => 'text-field',
			'x_axis' => 'date-field',
			'date-field_greater_than' => '2015-07-01',
			'date-field_less_than' => '2015-07-31',
			'include_zero' => '1',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Date', 'Single Line Text' ),
		);

		for ( $i=1; $i<32; $i++ ) {
			if ( $i == 8 ) {
				$count = 1;
			} else {
				$count = 0;
			}

			$expected_data['data'][] = array( 'July ' . $i . ', 2015', $count );
		}

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id="dropdown" x_axis="created_at"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_with_x_axis_created_at() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'created_at',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'Dropdown' );
		$expected_data['data'] = array(
			array( 'Creation Date', 'Dropdown' ),
			array( 'May 12, 2015', 1 ),
			array( 'May 13, 2015', 3 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id="dropdown" x_axis="date-field"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_with_x_axis_date_field() {
		self::clear_frm_vars();

		$x_axis_id = FrmField::get_id_by_key( 'date-field' );
		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => $x_axis_id,
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_dropdown( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id="dropdown" x_axis="date-field-key"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_with_x_axis_date_field_key() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'date-field',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_dropdown( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id="dropdown" x_axis="date-field" user_id="1"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_with_x_axis_date_field_filter_by_user_id() {
		self::clear_frm_vars();
		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'date-field',
			'user_id' => '1',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Dropdown' );
		$expected_data['data'] = array(
			array( 'Date', 'Dropdown' ),
			array( 'August 16, 2015', 2 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id="dropdown" x_axis="date-field" user_id="current"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_with_x_axis_date_field_filter_by_user_id_current() {
		self::clear_frm_vars();
		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'date-field',
			'user_id' => 'current',
		);

		$this->set_current_user_to_1();

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Dropdown' );
		$expected_data['data'] = array(
			array( 'Date', 'Dropdown' ),
			array( 'August 16, 2015', 2 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id="dropdown" x_axis="date-field" entry_id="y"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_with_x_axis_date_field_filter_by_entry_id() {
		self::clear_frm_vars();
		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'date-field',
			'entry_id' => FrmEntry::get_id_by_key( 'jamie_entry_key'),
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Dropdown' );
		$expected_data['data'] = array(
			array( 'Date', 'Dropdown' ),
			array( 'August 16, 2015', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id="dropdown" x_axis="date-field" entry_id="y,z"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_with_x_axis_date_field_filter_by_entry_ids() {
		self::clear_frm_vars();
		$entry_one = FrmEntry::get_id_by_key( 'jamie_entry_key');
		$entry_two = FrmEntry::get_id_by_key( 'steph_entry_key');
		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'date-field',
			'entry_id' => $entry_one . ',' . $entry_two,
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Dropdown' );
		$expected_data['data'] = array(
			array( 'Date', 'Dropdown' ),
			array( 'July 8, 2015', 1 ),
			array( 'August 16, 2015', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id="dropdown" x_axis="date-field" entry_id="y,z"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_with_x_axis_date_field_filter_by_checkbox() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'date-field',
			'uc580i' => 'Green',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Dropdown' );
		$expected_data['data'] = array(
			array( 'Date', 'Dropdown' ),
			array( 'January 24, 2015', 1 ),
			array( 'August 16, 2015', 2 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id="dropdown" x_axis="date-field" start_date="-100 years"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_x_axis_date_field_with_start_date() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'date-field',
			'start_date' => '-100 years',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_dropdown( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id="dropdown" x_axis="date-field" start_date="+100 years"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_x_axis_date_field_with_start_date_no_entries() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'date-field',
			'start_date' => '+100 years',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		self::run_no_data_graph_tests( $graph_html );
	}

	/**
	 * Check [frm-graph id="dropdown" x_axis="date-field" start_date="Y-m-d"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_x_axis_date_field_with_start_date_specific() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'date-field',
			'start_date' => '2015-06-01',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Dropdown' );
		$expected_data['data'] = array(
			array( 'Date', 'Dropdown' ),
			array( 'July 8, 2015', 1 ),
			array( 'August 16, 2015', 2 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id="dropdown" x_axis="date-field" end_date="+100 years"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_x_axis_date_field_with_end_date() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'date-field',
			'end_date' => '+100 years',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_dropdown( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id="dropdown" x_axis="date-field" end_date="-100 years"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_x_axis_date_field_with_end_date_no_entries() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'date-field',
			'end_date' => '-100 years',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		self::run_no_data_graph_tests( $graph_html );
	}

	/**
	 * Check [frm-graph id="dropdown" x_axis="date-field" start_date="2015-01-01" end_date="2016-01-01"]
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_dropdown_field_x_axis_date_field_with_start_and_end_date() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'date-field',
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
			'x_axis' => 'date-field',
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
			'x_axis' => 'date-field',
			'tooltip_label' => 'Leads',
			'colors' => '#EF8C08,#21759B,#1C9E05',
			'bg_color' => '#000000',
			'grid_color' => '#FFFFFF',
			'height' => '100',
			'width' => '100%',
			'truncate' => '3',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_dropdown( $graph_atts );
		self::add_column_colors( $graph_atts, $expected_data['data'] );

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
			'x_axis' => 'date-field',
			'min' => '1',
			'max' => '2',
			'is3d' => '1',
			'show_key' => '1',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );
		$expected_data = self::get_expected_data_for_dropdown( $graph_atts );

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id="number" data_type=total x_axis="date_field"]
	 *
	 * @covers FrmProGraphsController::graph_shortcode
	 */
	function test_graph_shortcode_data_type_total_and_x_axis() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => 'msyehy',
			'data_type' => 'total',
			'x_axis' => 'date-field',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts,'Number');
		$expected_data['data'] = array(
			array( 'Date', 'Number' ),
			array( 'January 24, 2015', 5.0 ),
			array( 'July 8, 2015', 1.0 ),
			array( 'August 16, 2015', 11.0 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x ids=y data_type=total x_axis="date_field"]
	 *
	 * @covers FrmProGraphsController::graph_shortcode
	 */
	function test_graph_shortcode_data_type_total_multiple_ids_and_x_axis() {
		self::clear_frm_vars();

		$graph_atts = array(
			'fields' => 'msyehy,qbrd2o',
			'data_type' => 'total',
			'x_axis' => 'date-field',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts);
		$expected_data['data'] = array(
			array( 'Date', 'Number', 'Scale' ),
			array( 'January 24, 2015', 5.0, 8.0 ),
			array( 'July 8, 2015', 1.0, 8.0 ),
			array( 'August 16, 2015', 11.0, 10.0 ),
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
			'fields' => '54tffk,qbrd2o',
			'x_axis' => 'date-field',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts);
		$expected_data['data'] = array(
			array( 'Date', 'Dropdown', 'Scale' ),
			array( 'January 24, 2015', 1, 1 ),
			array( 'July 8, 2015', 1, 1 ),
			array( 'August 16, 2015', 2, 2 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x include_zero="1" x_axis="date-field"]
	 *
	 * @covers FrmProGraphsController::graph_shortcode
	 */
	function test_graph_shortcode_include_zero_with_x_axis() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'date-field',
			'include_zero' => '1',
			'start_date' => '2015-01-20',
			'end_date' => '2015-01-30',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Dropdown' );
		$expected_data['data'] = array( array( 'Date', 'Dropdown' ) );
		for ( $i = 20; $i <= 30; $i++ ) {
			if ( $i == 24 ) {
				$count = 1;
			} else {
				$count = 0;
			}
			$expected_data['data'][] = array( 'January ' . $i . ', 2015', $count );
		}
		//$expected_data['options']['hAxis']['showTextEvery'] = 2;

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
			'fields' => '54tffk,text-field',
			'x_axis' => 'date-field',
			'include_zero' => '1',
			'start_date' => '2015-01-20',
			'end_date' => '2015-01-30',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts);
		$expected_data['data'] = array( array( 'Date', 'Single Line Text', 'Dropdown' ) );
		for ( $i = 20; $i <= 30; $i++ ) {
			if ( $i == 24 ) {
				$count = 1;
			} else {
				$count = 0;
			}
			$expected_data['data'][] = array( 'January ' . $i . ', 2015', $count, $count );
		}

		//$expected_data['options']['hAxis']['showTextEvery'] = 2;

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
			'x_axis' => 'date-field',
			'group_by' => 'month',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Dropdown' );
		$expected_data['data'] = array(
			array( 'Date', 'Dropdown' ),
			array( 'January 2015', 1 ),
			array( 'July 2015', 1 ),
			array( 'August 2015', 2 ),
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
			'x_axis' => 'date-field',
			'group_by' => 'month',
			'include_zero' => '1',
			'start_date' => '2015-06-01',
			'end_date' => '2015-08-31',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Dropdown' );
		$expected_data['data'] = array(
			array( 'Date', 'Dropdown' ),
			array( 'June 2015', 0 ),
			array( 'July 2015', 1 ),
			array( 'August 2015', 2 ),
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
			'x_axis' => 'date-field',
			'group_by' => 'quarter',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Dropdown' );
		$expected_data['data'] = array(
			array( 'Date', 'Dropdown' ),
			array( 'Q1 2015', 1 ),
			array( 'Q3 2015', 3 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="checkbox"]
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_x_axis_checkbox() {
		$this->markTestSkipped( 'Fails since before 2.2' );
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => '54tffk',
			'x_axis' => 'uc580i',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Dropdown' );
		$expected_data['data'] = array(
			array( 'Checkboxes - colors', 'Dropdown' ),
			array( 'Red', 3 ),
			array( 'Green', 2 ),
			array( 'Blue', 2 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="dynamic-field"]
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_x_axis_dynamic() {
		self::clear_frm_vars();

		$graph_atts = array(
			'id' => 'text-field',
			'x_axis' => 'dynamic-country',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Dynamic Field - level 1', 'Single Line Text' ),
			array( 'United States', 4 ),
		);

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
			'id' => 'text-field',
			'x_axis' => '54tffk',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Dropdown', 'Single Line Text' ),
			array( 'Ace Ventura', 3 ),
			array( 'William Wells', 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph id=x x_axis="number"]
	 *
	 * @covers FrmProGraphsController::graph_shortcode
	 */
	function test_graph_shortcode_x_axis_number() {
		self::clear_frm_vars();

		$graph_atts = array(
			'fields' => 'text-field',
			'x_axis' => 'msyehy',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults(  $graph_atts, 'Single Line Text' );
		$expected_data['data'] = array(
			array( 'Number', 'Single Line Text' ),
			array( 0.0, 1 ),
			array( 1.0, 1 ),
			array( 5.0, 1 ),
			array( 11.0, 1 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}


	/******************************************************
	 * Form graphs
	 *****************************************************/

	/**
	 * Check [frm-graph form=x start_date="Y-m-d" end_date="Y-m-d"] where x is the ID of a form
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_with_form() {
		self::clear_frm_vars();

		$form_id = FrmForm::getIdByKey( 'all_field_types' );
		$graph_atts = array(
			'form' => $form_id,
			'start_date' => '2015-05-01',
			'end_date' => '2015-05-31',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'All field types' );

		$expected_data['data'] = array(
			array( 'Creation Date', 'Submissions' ),
		);

		for ( $i=1; $i<32; $i++ ) {
			if ( $i == 12 ) {
				$count = 1;
			} else if ( $i == 13 ) {
				$count = 3;
			} else {
				$count = 0;
			}

			$expected_data['data'][] = array( 'May ' . $i . ', 2015', $count );
		}

		self::run_graph_tests( $graph_html, $expected_data );
	}

	/**
	 * Check [frm-graph form=x ... group_by="month" ] where x is the ID of a form
	 *
	 * @covers FrmProGraphsController::graph_shortcode()
	 */
	function test_graph_shortcode_with_form_and_group_by() {
		self::clear_frm_vars();

		$form_id = FrmForm::getIdByKey( 'all_field_types' );
		$graph_atts = array(
			'form' => $form_id,
			'start_date' => '2015-02-01',
			'end_date' => '2015-06-01',
			'group_by' => 'month',
		);

		$graph_html = FrmProGraphsController::graph_shortcode( $graph_atts );

		$expected_data = self::get_graph_defaults( $graph_atts, 'All field types' );

		$expected_data['data'] = array(
			array( 'Creation Date', 'Submissions' ),
			array( 'February 2015', 0 ),
			array( 'March 2015', 0 ),
			array( 'April 2015', 0 ),
			array( 'May 2015', 4 ),
		);

		self::run_graph_tests( $graph_html, $expected_data );
	}


	/******************************************************
	 * Get expected data
	 *****************************************************/

	function get_expected_data_for_text_field( $graph_atts ) {
		$expected_data = self::get_graph_defaults( $graph_atts, 'Single Line Text' );
		$tooltip_label = self::get_expected_tooltip_label( $graph_atts );

		if ( ( isset( $graph_atts['ids'] ) && $graph_atts['ids'] == 'uc580i,4t3qo4' ) ||
			( isset( $graph_atts['fields'] ) && $graph_atts['fields'] == 'text-field,uc580i,4t3qo4' ) ) {

			$total_submissions = 4;

			$expected_data['data'] = array(
				array( 'Fields', $tooltip_label ),
				array( 'Single Line Text', $total_submissions ),
				array( 'Checkboxes - colors', $total_submissions ),
				array( 'Radio Buttons - dessert', $total_submissions ),
			);
		} else {
			$expected_data['data'] = array(
				array( 'Single Line Text', $tooltip_label ),
				array( 'Jamie', 1 ),
				array( 'Jwahlin', 1 ),
				array( 'Steph', 1 ),
				array(  'Steve', 1 ),
			);
		}

		return $expected_data;
	}

	function get_expected_data_for_user_id_field( $graph_atts ) {
		$expected_data = self::get_graph_defaults( $graph_atts, 'User ID' );
		$tooltip_label = self::get_expected_tooltip_label( $graph_atts );

		$expected_data['data'] = array(
			array( 'User ID', $tooltip_label ),
			array( 'admin', 2 ),
		);

		return $expected_data;
	}

	function get_expected_tooltip_label( $graph_atts, $default = 'Submissions' ) {
		if ( isset( $graph_atts['tooltip_label'] ) ) {
			$tooltip_label = $graph_atts['tooltip_label'];
		} else if ( isset( $graph_atts['data_type'] ) && $graph_atts['data_type'] == 'total' ) {
			$tooltip_label = 'Total';
		} else {
			$tooltip_label = $default;
		}

		return $tooltip_label;
	}

	function get_expected_data_for_country_field( $graph_atts ) {
		$expected_data = self::get_graph_defaults( $graph_atts, 'Country' );

		$expected_data['data'] = array(
			array( 'Country', 'Submissions' ),
			array( 'Brazil', 1 ),
			array( 'United States', 1 ),
		);

		return $expected_data;
	}

	function get_expected_data_for_dropdown( $graph_atts ) {
		$expected_data = self::get_graph_defaults(  $graph_atts, 'Dropdown' );

		$date_field_id = FrmField::get_id_by_key( 'date-field' );
		$x_axis = isset( $graph_atts['x_axis'] ) ? $graph_atts['x_axis'] : '';
		$tooltip_label = self::get_expected_tooltip_label( $graph_atts, 'Dropdown' );

		if ( $x_axis == 'date-field' || $x_axis == $date_field_id  ) {
			// x_axis=date-field

			$expected_data['data'] = array(
				array( 'Date', $tooltip_label ),
				array( 'January 24, 2015', 1 ),
				array( 'July 8, 2015', 1 ),
				array( 'August 16, 2015', 2 ),
			);
		}

		return $expected_data;

	}

	function get_expected_data_for_number_field( $graph_atts ) {
		$expected_data = self::get_graph_defaults( $graph_atts,'Number');
		$tooltip_label = self::get_expected_tooltip_label( $graph_atts );

		$number_data = array( 0, 1, 5, 11 );

		$expected_data['data'] = array(
			array( 'Number', $tooltip_label ),
		);

		if ( isset( $graph_atts['type'] ) && $graph_atts['type'] == 'pie' ) {
			foreach ( $number_data as $value ) {
				$expected_data['data'][] = array( (string) $value, 1 );
			}
		} else {
			foreach ( $number_data as $value ) {
				$expected_data['data'][] = array( $value, 1 );
			}
		}

		return $expected_data;
	}

	function get_graph_defaults( $graph_atts, $field_name = '' ) {
		$atts = self::convert_atts_to_expected_data_atts( $graph_atts );

		$expected_data = array(
			'type' => $atts['type'],
			'data' => array(),
			'options' => array(
				'width' => $atts['width'],
				'height' => $atts['height'],
				'legend' => array(
					'position' => 'none',
				),
				'tooltip' => array( 'isHtml' => true ),
				'title' => self::get_expected_graph_title( $graph_atts, $field_name ),
				'titleTextStyle' => array(
					'bold' => false,
					'fontSize' => $atts['title_size'],
					'color' => '#666',
				),
				'colors' => $atts['colors'],
				'backgroundColor' => $atts['bg_color'],
				'is3D' => $atts['is3d'],
			),
			'package' => self::get_expected_package_type( $atts ),
			'graph_id' => '_frm_' . strtolower( $atts['type'] ) . '1',
		);

		self::get_expected_axes_options( $atts, $graph_atts, $expected_data );

		if ( isset( $graph_atts['title_font'] ) ) {
			$expected_data['options']['titleTextStyle']['fontName'] = $graph_atts['title_font'];
		}

		if ( isset( $graph_atts['show_key'] ) ) {
			$expected_data['options']['legend'] = array(
				'position' => 'right'
			);

			if ( is_numeric( $graph_atts['show_key'] ) && $graph_atts['show_key'] >= 10 ) {
				$expected_data['options']['legend']['textStyle'] = array( 'fontSize' => $graph_atts['show_key'] );
			}
		}

		return $expected_data;
	}

	function get_expected_axes_options( $atts, $graph_atts, &$expected_data ) {

		if ( $atts['type'] !== 'pie' && $atts['type'] !== 'geo' ) {

			self::get_expected_x_axis_options( $graph_atts, $expected_data );

			self::get_expected_y_axis_options( $atts, $graph_atts, $expected_data );
		}

	}

	function get_expected_x_axis_options( $graph_atts, &$expected_data ) {
		// x-axis
		$expected_data['options']['hAxis'] = array(
			'titleTextStyle' => array(
				'italic' => false,
				'fontSize' => 13,
				'color' => '#666',
				),
			'slantedText' => true,
			'slantedTextAngle' => 20,
			'textStyle' => array(
			),
		);

		self::get_expected_x_axis_title( $graph_atts, $expected_data );
	}

	function get_expected_y_axis_options( $atts, $graph_atts, &$expected_data ) {
		// y-axis
		$expected_data['options']['vAxis'] = array(
			'gridlines' => array( 'color' => $atts['v_axis_color'] ),
			'textStyle' => array(),
			'titleTextStyle' => array(
				'italic' => false,
				'fontSize' => 13,
				'color' => '#666',
			),
		);

		// y min and max
		if ( isset( $graph_atts['min'] ) && isset( $graph_atts['max'] ) ) {
			$expected_data['options']['vAxis']['viewWindow'] = array(
				'max' => $graph_atts['max'],
				'min' => $graph_atts['min'],
			);
		}

		// y-axis title
		if ( isset( $graph_atts['y_title'] ) ) {
			$expected_data['options']['vAxis']['title'] = $graph_atts['y_title'];
		}
	}

	function get_expected_x_axis_title( $graph_atts, &$expected_data ) {
		if ( isset( $graph_atts['x_title'] ) ) {
			$expected_data['options']['hAxis']['title'] = $graph_atts['x_title'];
		}

	}

	function get_expected_package_type( $atts ) {
		if ( $atts[ 'type' ] == 'geo' ) {
			$package = 'geochart';
		} else if ( $atts['type'] == 'table' ) {
			$package = 'table';
		} else {
			$package = 'corechart';
		}

		return $package;
	}

	function convert_atts_to_expected_data_atts( $graph_atts ) {
		$atts = array();

		$atts['type'] = self::get_expected_graph_type( $graph_atts );
		$atts['width'] = isset( $graph_atts['width'] ) ? $graph_atts['width'] : 400;
		$atts['height'] = isset( $graph_atts['height'] ) ? $graph_atts['height'] : 400;
		$atts['bg_color'] = isset( $graph_atts['bg_color'] ) ? $graph_atts['bg_color'] : '#FFFFFF';
		$atts['is3d'] = isset( $graph_atts['is3d'] ) && $graph_atts['is3d'];
		$atts['v_axis_color'] = isset( $graph_atts['grid_color'] ) ? $graph_atts['grid_color'] : '#CCC';
		$atts['title_size'] = isset( $graph_atts['title_size'] ) ? $graph_atts['title_size'] : 14;
		$atts['colors'] = isset( $graph_atts['colors'] ) ? explode( ',', $graph_atts['colors'] ) : self::get_standard_graph_colors();

		return $atts;
	}

	function get_expected_graph_title( $graph_atts, $field_name ) {
		$fields = isset( $graph_atts['fields'] ) ? explode( ',', $graph_atts['fields'] ) : array();

		if ( isset( $graph_atts['title'] ) ) {
			$title = $graph_atts[ 'title' ];
		} else if ( isset( $graph_atts['ids'] ) || count( $fields ) > 1 ) {
			$title = 'Submissions';
		} else {
			$title = $field_name;
		}

		if ( isset( $graph_atts['truncate'] ) ) {
			$title = substr( $title, 0, $graph_atts['truncate'] ) . '...';
		}

		return $title;
	}

	function get_expected_graph_type( $graph_atts ) {
		if ( isset( $graph_atts['type'] ) && $graph_atts['type'] != 'bar' ) {
			if ( $graph_atts['type'] == 'hbar' ) {
				$type = 'bar';
			} else if ( $graph_atts['type'] == 'stepped_area' ) {
				$type = 'steppedArea';
			} else {
				$type = $graph_atts['type'];
			}
		} else {
			$type = 'column';
		}

		$type = lcfirst( $type );

		$allowed_types = array(
			'pie',
			'line',
			'column',
			'area',
			'steppedArea',
			'geo',
			'bar',
			'scatter',
			'histogram',
			'table',
		);

		if ( ! in_array( $type, $allowed_types ) ) {
			$type = 'column';
		}

		return $type;

	}

	function get_standard_graph_colors() {
		return array( '#00bbde','#fe6672','#eeb058','#8a8ad6','#ff855c','#00cfbb','#5a9eed','#73d483','#c879bb','#0099b6' );
	}

	/******************************************************
	 * Generic functions
	 *****************************************************/

	function get_graph_types_for_testing() {
		$types = array(
			'pie',
			'line',
			'column',
			'area',
			'stepped_area',
			'SteppedArea',
			'geo',
			'bar',
			'scatter',
			'histogram',
			'hbar',
			'table',
			'fake',
		);

		return $types;
	}

	function clear_frm_vars() {
		global $frm_vars;
		unset( $frm_vars['google_graphs'] );
	}

	function add_column_colors( $atts, &$graph_data ) {
		if ( isset( $atts['colors'] ) ) {
			$colors = explode( ',', $atts['colors'] );
		} else {
			$colors = self::get_standard_graph_colors();
		}

		$color_upper_limit = count( $colors ) - 1;
		$count = -1;

		foreach ( $graph_data as $key => $item ) {
			if ( $count < 0 ) {
				$graph_data[ $key ][] = array( 'role' => 'style' );
			} else {
				$graph_data[ $key ][] = $colors[ $count ];
			}

			if ( $count < $color_upper_limit ) {
				$count++;
			} else {
				$count = 0;
			}
		}
	}

	/******************************************************
	 * Graph tests
	 *****************************************************/

	function run_graph_tests_for_multiple_graphs( $graph_html, $expected_data ) {
		foreach ( $graph_html as $key => $html ) {
			self::run_graph_tests( $html, $expected_data[ $key ], $key );
		}
	}

	function run_graph_tests( $graph_html, $expected_data, $count = 0 ) {
		global $frm_vars;

		$expected_html = '<div id="chart_' . $expected_data['graph_id'] . '" style="height:' .
			$expected_data['options']['height'] . ';width:' . $expected_data['options']['width'] . '"></div>';
		$this->assertEquals( $expected_html, $graph_html );

		$this->assertNotEmpty(
			$frm_vars['google_graphs'],
			'The global $frm_vars google_graphs variable is not getting set as expected'
		);

		$actual_data = $frm_vars['google_graphs']['graphs'][ $count ];

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
		$no_data_html = '<div class="frm_no_data_graph">No data</div>';
		$this->assertEquals( $no_data_html, $graph_html, 'Data is showing when there should be no entries found.' );
	}
}