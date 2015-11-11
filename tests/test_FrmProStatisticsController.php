<?php

/**
 * @group stats
 */
class WP_Test_FrmProStatisticsController extends FrmUnitTest {
	function test_stats_shortcode() {
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
}