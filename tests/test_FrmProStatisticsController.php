<?php

/**
 * @group stats
 */
class WP_Test_FrmProStatisticsController extends FrmUnitTest {
	function test_stats_shortcode_count() {
		$forms_to_test = array(
			$this->all_fields_form_key  => array( '493ito', 'p3eiuk', 'uc580i', '4t3qo4', '54tffk', 'endbcl', 'repeating-text' ),
			//$this->create_post_form_key => array( 'yi6yvm' ),
		);

		foreach ( $forms_to_test as $form_key => $fields ) {
			foreach ( $fields as $field_key ) {
				$field = FrmField::getOne( $field_key );
				$value = do_shortcode( '[frm-stats id=' . $field->id . ' type=count]' );
				$this->assertNotEmpty( $value, 'Field ' . $field_key . ' has no saved values' );

				if ( ! empty( $field->options ) ) {
					$first_option = array_filter( $field->options );
					$first_option = reset( $first_option );
					$filter_by_value = do_shortcode( '[frm-stats id=' . $field->id . ' type=count value="' . $first_option . '"]' );
					$this->assertNotEmpty( $filter_by_value, 'Field ' . $field_key . ' has no saved values for "' . $first_option . '"' );
				}
			}
		}
	}

	/**
	 * Tests default stats type with field ID
	 * [frm-stats id=x]
	 */
	function test_stats_shortcode_default_type() {
		$field_id = FrmField::get_id_by_key( 'msyehy' );
		$shortcode = '[frm-stats id=' . $field_id . ']';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 16;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="Number-field-key" type="total"]
	 */
	function test_stats_shortcode_total() {
		$shortcode = '[frm-stats id="msyehy" type="total"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 16;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="Number-field-key" type="average"]
	 */
	function test_stats_shortcode_average() {
		$shortcode = '[frm-stats id="msyehy" type="average"]';
		$actual_value = do_shortcode( $shortcode );
		//$expected_value = '5.33333333333'; default round used to be 100
		$expected_value = 5.33;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );

		$shortcode = '[frm-stats id="msyehy" type="mean"]';
		$actual_value = do_shortcode( $shortcode );
		//$expected_value = '5.33333333333'; default round used to be 100
		$expected_value = 5.33;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="Number-field-key" type="median"]
	 */
	function test_stats_shortcode_median() {
		$shortcode = '[frm-stats id="msyehy" type="median"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 5;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="Scale-field-key" type="star"]
	 */
	function test_stats_shortcode_star() {
		$shortcode = '[frm-stats id="qbrd2o" type="star"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_parts = array(
			'<div class="frm_form_fields">',
			'<input type="radio"',
			'value="1"  class="star" disabled="disabled"',
			'value="7"  checked=\'checked\' class="star"',
			'value="10"  class="star"',
			'</div>',
		);

		foreach ( $expected_parts as $needle ) {
			$this->assertContains( $needle, $actual_value, $shortcode . ' is not getting the correct value' );
		}
	}

	/**
	 * [frm-stats id="Number-field-key" type="maximum"]
	 */
	function test_stats_shortcode_maximum() {
		$shortcode = '[frm-stats id="msyehy" type="maximum"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 10;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="Number-field-key" type="minimum"]
	 */
	function test_stats_shortcode_minimum() {
		$shortcode = '[frm-stats id="msyehy" type="minimum"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 1;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="Number-field-key" type="unique"]
	 */
	function test_stats_shortcode_unique() {
		$shortcode = '[frm-stats id="msyehy" type="unique"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 3;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="Number-field-key" type="total" user_id="1"]
	 */
	function test_stats_shortcode_total_with_user_id() {
		$shortcode = '[frm-stats id="msyehy" type="total" user_id="1"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 10;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="Number-field-key" type="total" round="2"]
	 */
	function test_stats_shortcode_total_with_round() {
		$shortcode = '[frm-stats id="msyehy" type="total" round="2"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 16.00;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="Number-field-key" type="total" decimal="2"]
	 */
	function test_stats_shortcode_total_with_decimals() {
		//$this->markTestSkipped( 'Fails before 2.02.06' );
		$shortcode = '[frm-stats id="msyehy" type="total" decimal="2"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 16.00;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="Number-field-key" type="average" round="2"]
	 */
	function test_stats_shortcode_average_with_round() {
		$shortcode = '[frm-stats id="msyehy" type="average" round="2"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 5.33;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="Number-field-key" type="average" decimal="3"]
	 */
	function test_stats_shortcode_average_with_decimal() {
		//$this->markTestSkipped( 'Fails before 2.02.06' );
		$shortcode = '[frm-stats id="msyehy" type="average" decimal="3"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 5.333;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="Number-field-key" type="total" limit="2"]
	 */
	function test_stats_shortcode_total_with_limit() {
		$shortcode = '[frm-stats id="msyehy" type="total" limit="2"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 6;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="Number-field-key" type="total" value="5"]
	 */
	function test_stats_shortcode_total_with_value() {
		$shortcode = '[frm-stats id="msyehy" type="total" value="5"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 5;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="dropdown-field-key" type="count" value="Ace Ventura"]
	 */
	function test_stats_shortcode_count_with_value() {
		$shortcode = '[frm-stats id="54tffk" type="count" value="Ace Ventura"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 2;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="number-field-key" type="total" entry_id="123"]
	 */
	function test_stats_shortcode_total_with_entry_id() {
		//$this->markTestSkipped( 'Fails before 2.02.06' );
		$entry_id = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$shortcode = '[frm-stats id="msyehy" type="total" entry_id="' . $entry_id . '"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 10;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="number-field-key" type="total" entry_id="key"]
	 */
	function test_stats_shortcode_total_with_entry_key() {
		//$this->markTestSkipped( 'Fails before 2.02.06' );
		$shortcode = '[frm-stats id="msyehy" type="total" entry_id="jamie_entry_key"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 10;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="number-field-key" type="total" dropdown="Ace Ventura"]
	 */
	function test_stats_shortcode_total_with_dropdown_filter() {
		$shortcode = '[frm-stats id="msyehy" type="total" 54tffk="Ace Ventura"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 15;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="number-field-key" type="total" dropdown="Ace Ventura" fake="test"]
	 */
	function test_stats_shortcode_total_with_dropdown_filter_and_fake_filter() {
		$shortcode = '[frm-stats id="msyehy" type="total" 54tffk="Ace Ventura" fake="test"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 15;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="number-field-key" type="total" dropdown="Ace Ventura" entry_id="jamie_entry_key"]
	 */
	function test_stats_shortcode_total_with_dropdown_filter_and_entry_id() {
		$entry_id = FrmEntry::get_id_by_key( 'jamie_entry_key' );
		$shortcode = '[frm-stats id="msyehy" type="total" 54tffk="Ace Ventura" entry_id="' . $entry_id . '"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 10;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="number-field-key" type="total" dropdown="Ace Ventura" entry_id="jamie_entry_key"]
	 */
	function test_stats_shortcode_total_with_dropdown_filter_and_entry_key() {
		//$this->markTestSkipped( 'Fails before 2.02.06' );
		$shortcode = '[frm-stats id="msyehy" type="total" 54tffk="Ace Ventura" entry_id="jamie_entry_key"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 10;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="number-field-key" type="total" "2015-01-01"<f67hbu<"2015-08-01"]
	 */
	function test_stats_shortcode_total_with_deprecated_date_range_filter() {
		$shortcode = '[frm-stats id="msyehy" type="total" "2015-01-01"&lt;f67hbu&lt;"2015-08-01"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 6;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="number-field-key" type="total" f67hbu_greater_than="2015-01-01" f67hbu_less_than="2015-08-01"]
	 */
	function test_stats_shortcode_total_with_new_date_range_filter() {
		$shortcode = '[frm-stats id="msyehy" type="total" f67hbu_greater_than="2015-01-01" f67hbu_less_than="2015-08-01"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 6;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="number-field-key" type="total" "2015-05-12"<created_at<"2015-06-01"]
	 */
	function test_stats_shortcode_total_with_deprecated_creation_date_filter() {
		$shortcode = '[frm-stats id="msyehy" type="total" "2015-05-13"&lt;created_at&lt;"2015-06-01"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 6;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * [frm-stats id="number-field-key" type="total" created_at_greater_than="2015-05-12" created_at_less_than="2015-06-01"]
	 */
	function test_stats_shortcode_total_with_creation_date_filter() {
		$shortcode = '[frm-stats id="msyehy" type="total" created_at_greater_than="2015-05-13" created_at_less_than="2015-06-01"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 6;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * Tests count with post field
	 * [frm-stats id="post-title" type="count"]
	 */
	function test_stats_shortcode_count_for_post_field() {
		$shortcode = '[frm-stats id=yi6yvm type="count"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 2;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * Tests count with taxonomy field
	 * [frm-stats id="post-category" type="count"]
	 */
	function test_stats_shortcode_count_for_taxonomy_field() {
		$shortcode = '[frm-stats id="parent-dynamic-taxonomy" type="count"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 2;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * Tests count with a checkbox field
	 * [frm-stats id="checkbox-field" type="count"]
	 */
	function test_stats_shortcode_count_for_checkbox_field() {
		$shortcode = '[frm-stats id="uc580i" type="count"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 3;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}

	/**
	 * Tests count with a checkbox field and a filter for that field
	 * [frm-stats id="checkbox-field" type="count" checkbox-field="Blue"]
	 */
	function test_stats_shortcode_count_for_checkbox_field_with_filter() {
		$shortcode = '[frm-stats id="uc580i" type="count" uc580i="Blue"]';
		$actual_value = do_shortcode( $shortcode );
		$expected_value = 2;

		$this->assertEquals( $expected_value, $actual_value, $shortcode . ' is not getting the correct value' );
	}
}