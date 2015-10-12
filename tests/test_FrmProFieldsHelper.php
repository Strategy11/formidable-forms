<?php

/**
 * @group fields
 */
class WP_Test_FrmProFieldsHelper extends FrmUnitTest {

	/**
	* @covers FrmProFieldsHelper::convert_to_static_year
	* Check if dynamic year (in date field) is converted to static correctly
	*/
	function test_convert_to_static_year(){
		$values_to_test = array( '', '0', '-10', '-100', '10', '+10', '+100', '1900', '2015' );
		foreach ( $values_to_test as $dynamic_val ) {
			$year = FrmProFieldsHelper::convert_to_static_year( $dynamic_val );
			$this->assertTrue( ( strlen( $year ) == 4 && is_numeric ( $year ) && strpos( $year, '-' ) === false && strpos( $year, '+' ) === false ), 'The dynamic value ' . $dynamic_val . ' was not correctly converted to a year. It was converted to: ' . $year  );
		}
	}

	/**
	* @covers FrmProFieldsHelper::update_dynamic_category_field_if_empty
	*/
	function test_update_dynamic_category_field_if_empty(){
		$tests = array(
			'child_is_empty_dropdown_parent',
			'child_not_empty_dropdown_parent',
			'child_is_empty_checkbox_parent',
			'child_not_empty_checkbox_parent',
			//'different_post_type',
			//'specific child is excluded',
		);

		$key = 0;
		$expected_cats = array();

		foreach ( $tests as $test ) {
			$field = self::_set_up_field_object( $test, $expected_cats, $key );

			if ( empty( $expected_cats ) ) {
				$expected_hide_opt = array( $key => '' );
			} else {
				$expected_hide_opt = $field->field_options['hide_opt'];
			}

			self::_do_update_dynamic_category_field_if_empty( $field, $key );

			$this->assertEquals( $expected_hide_opt, $field->field_options['hide_opt'], 'The hide_opt setting is not getting updated correctly with the ' . $test . ' test. This will make conditionally hidden dynamic category fields remain required.');

		}
	}

	function _set_up_field_object( $test, &$expected_cats, $key ) {
		// Parent categories
		$args = array(
			//'exclude' => implode( ',', $parent_field->field_options['exclude_cat'] ),
			'hide_empty' => false,
			'taxonomy'   => 'category',
			'parent'     => 0
		);
		$parent_cats = get_categories( $args );

		// Get the correct parent ID(s) for the current test
		$parent_cat_id = self::_get_parent_cat_id( $test, $parent_cats, $expected_cats );
		$this->assertNotFalse( $parent_cat_id, 'Check if there are at least two parent categories with children and two with no children. Needed for the ' . $test . ' test.');

		// Child field
		$field_id = $this->factory->field->get_id_by_key( 'child-dynamic-taxonomy' );
		$field = FrmField::getOne( $field_id );
		$field->field_options['hide_opt'][ $key ] = $parent_cat_id;

		return $field;
	}

	function _get_parent_cat_id( $test, $parent_cats, &$expected_cats ) {
		$parent_cat_array = $expected_cat_array = array();

		foreach ( $parent_cats as $parent_cat ) {
			$expected_cats = get_categories( array( 'parent' => $parent_cat->term_id, 'hide_empty' => false ) );
			$parent_cat_id = $parent_cat->term_id;

			if ( $test == 'child_is_empty_dropdown_parent' && ! $expected_cats ) {
				break;
			} else if ( $test == 'child_not_empty_dropdown_parent' && $expected_cats ) {
				break;
			} else if ( $test == 'child_is_empty_checkbox_parent' && ! $expected_cats ) {
				$parent_cat_array[] = $parent_cat_id;
				if ( count( $parent_cat_array ) == 2 ) {
					$parent_cat_id = $parent_cat_array;
					break;
				}
			} else if ( $test == 'child_not_empty_checkbox_parent' && $expected_cats ) {
				$parent_cat_array[] = $parent_cat_id;
				$expected_cat_array = array_merge( $expected_cats, $expected_cat_array );
				if ( count( $parent_cat_array ) == 2 ) {
					$parent_cat_id = $parent_cat_array;
					$expected_cats = $expected_cat_array;
					break;
				}
			}
			$parent_cat_id = false;
		}

		return $parent_cat_id;
	}

	function _do_update_dynamic_category_field_if_empty( &$field, $key ){
		$class = new ReflectionClass( 'FrmProFieldsHelper' );
		$method = $class->getMethod( 'update_dynamic_category_field_if_empty' );
		$method->setAccessible( true );
		$method->invokeArgs( null, array( &$field, $key ) );
	}
}