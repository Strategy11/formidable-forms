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

	/**
	 * Checks single file upload shortcode with different combinations of attributes
	 * Field is retrieving an image file
	 *
	 * @covers FrmProFieldsHelper::get_file_display_value()
	 */
	function test_displayed_image_file_in_view() {
		$att_combinations = self::get_file_att_combinations_for_testing();
		$media_ids = self::get_media_ids_from_database( 'mprllc', 'jamie_entry_key' );

		foreach ( $att_combinations as $atts ) {
			$displayed_value = FrmProFieldsHelper::get_file_display_value( $media_ids, $atts );
			self::run_tests_on_displayed_html( $media_ids, $displayed_value, $atts );
		}
	}

	/**
	 * Checks multiple file upload shortcode with different combinations of attributes
	 * Field is retrieving combination of image and non-image files
	 *
	 * @covers FrmProFieldsHelper::get_file_display_value()
	 */
	function test_displayed_files_in_view() {
		$att_combinations = self::get_file_att_combinations_for_testing();
		$media_ids = self::get_media_ids_from_database( '72hika', 'jamie_entry_key' );

		foreach ( $att_combinations as $atts ) {
			$displayed_value = FrmProFieldsHelper::get_file_display_value( $media_ids, $atts );
			self::run_tests_on_displayed_html( $media_ids, $displayed_value, $atts );
		}
	}

	/**
	 * Checks single file upload shortcode with different combinations of attributes
	 * @covers FrmProFieldsHelper::get_file_display_value()
	 */
	function test_displayed_non_image_file_in_view() {
		$att_combinations = self::get_file_att_combinations_for_testing();
		$media_ids = self::get_media_ids_from_database( 'mprllc', 'steph_entry_key' );

		foreach ( $att_combinations as $atts ) {
			$displayed_value = FrmProFieldsHelper::get_file_display_value( $media_ids, $atts );
			self::run_tests_on_displayed_html( $media_ids, $displayed_value, $atts );
		}
	}

	/**
	 * Checks [x] where x is a single file upload field ID displaying an image file
	 * Tests what happens when value is modified with frmpro_fields_replace_shortcodes
	 * @covers FrmProFieldsHelper::get_file_display_value()
	 */
	function test_displayed_image_file_modified_with_custom_code() {
		$media_ids = 'custom image';
		$atts = array();

		$displayed_value = FrmProFieldsHelper::get_file_display_value( $media_ids, $atts );
		$this->assertEquals( $media_ids, $displayed_value, 'Displayed image value that was modified with custom code is being overridden.');
	}

	/**
	 * Checks [x] where x is a single file upload field ID displaying an image file
	 * Tests what happens when value is modified with frmpro_fields_replace_shortcodes
	 * @covers FrmProFieldsHelper::get_file_display_value()
	 */
	function test_displayed_files_modified_with_custom_code() {
		$media_ids = array( 'custom image 1', '<img src="hello.png" />' );
		$atts = array();

		$displayed_value = FrmProFieldsHelper::get_file_display_value( $media_ids, $atts );
		$expected_value = implode( ' ', $media_ids );
		$this->assertEquals( $expected_value, $displayed_value, 'Displayed image value that was modified with custom code is being overridden.');
	}

	function get_file_att_combinations_for_testing() {
		$att_combinations = array(
			array( ),
			array( 'show' => 'id' ),
			array( 'size' => 'full' ),
			array( 'html' => '1' ),// no longer documented
			array( 'show_filename' => '1' ),
			array( 'show' => 'label' ),// no longer documented
			array( 'links' => '1' ),// no longer documented
			array( 'show_image' => '1' ),
			array( 'add_link' => '1' ),
			array( 'html' => '1', 'size' => 'full' ),
			array( 'show_filename' => '1', 'size' => 'full' ),
			array( 'show_image' => '1', 'size' => 'full' ),
			array( 'html' => '1', 'show_filename' => '1' ),
			array( 'show_filename' => '1', 'links' => '1' ),
			array( 'html' => '1', 'show_filename' => '1', 'links' => '1' ),
			array( 'html' => '1', 'links' => '1' ),
			array( 'add_link' => '1', 'show_image' => '1' ),
			array( 'show_filename' => '1', 'show_image' => '1' ),
			array( 'add_link' => '1', 'show_image' => '1', 'show_filename' => '1' ),
			array( 'add_link' => '1', 'show_image' => '1', 'show_filename' => '1', 'size' => 'full' ),
			array( 'add_link' => '1', 'show_filename' => '1' ),
		);

		return $att_combinations;
	}


	function get_media_ids_from_database( $field_key, $entry_key ) {
		global $wpdb;
		$single_upload_id = FrmField::get_id_by_key( $field_key );
		$entry_id = FrmEntry::get_id_by_key( $entry_key );
		$query = "
			SELECT
				meta_value
			FROM
				" . $wpdb->prefix . "frm_item_metas
			WHERE
				field_id=" . $single_upload_id . " AND
				item_id=" . $entry_id;
		$meta_value = $wpdb->get_var( $query );

		$this->assertNotEquals( '', $meta_value, 'Media ids are not set or retrieved correctly for entry ' . $entry_key . ' and field ' . $field_key );
		$media_ids = maybe_unserialize( $meta_value );

		if ( strpos( $media_ids, ',' ) !== false ) {
			$media_ids = explode( ',', $media_ids);
		}

		return $media_ids;
	}

	function run_tests_on_displayed_html( $media_ids, $displayed_value, $atts ) {
		$media_ids = (array) $media_ids;

		if ( isset( $atts['sep'] ) ) {
			$sep = $atts['sep'];
		} else {
			if ( isset( $atts['show_image'] ) || isset( $atts['html'] ) ) {
				$sep = ' ';
			} else {
				$sep = ',';
			}
		}

		$size = isset( $atts['size'] ) ? $atts['size'] : 'thumbnail';

		if ( isset( $atts['show'] ) && $atts['show'] == 'label' ) {
			$atts['show_filename'] = '1';
			unset( $atts['show'] );
		}

		if ( isset( $atts['links'] ) && $atts['links'] ) {
			$atts['add_link'] = '1';
			unset( $atts['links'] );
		}

		$expected_value = '';
		$image_types = array();

		foreach ( $media_ids as $media_id ) {
			$image = wp_get_attachment_image_src( $media_id, $size );

			if ( $image ) {
				$image_types[] = 'image';
				$expected_value .= self::get_expected_value_for_image_file( $media_id, $image, $atts );
			} else {
				$image_types[] = 'non-image';
				$expected_value .= self::get_expected_value_for_non_image_file( $media_id, $image, $atts );
			}

			$expected_value .= $sep;
		}

		$expected_value = rtrim( $expected_value, $sep );

		$atts_list = '';
		foreach ( $atts as $parameter => $value ) {
			$atts_list .= ' ' . $parameter . '=' . $value;
		}

		$msg = 'The displayed value is not correct for a file with the following atts: ' . $atts_list;
		$this->assertEquals( $expected_value, $displayed_value, $msg );
	}

	function get_expected_value_for_image_file( $media_id, $image, $atts ) {
		$expected_value = '';
		$default_url = $image[0];
		$size = isset( $atts['size'] ) ? $atts['size'] : 'thumbnail';

		if ( isset( $atts['show'] ) && $atts['show'] == 'id' ) {
			$expected_value = $media_id;
		}

		if ( isset( $atts['show_filename'] ) ) {
			$attachment = get_post( $media_id );
			$expected_value .= basename( $attachment->guid );
		}

		if ( isset( $atts['html'] ) ) {
			if ( isset( $atts['show_filename'] ) ) {
				// If show_filename=1 and html=1 is used, the image is not shown and a link is added to the filename
				$atts['add_link'] = 1;
			} else {
				$expected_value = wp_get_attachment_image( $media_id, $size, false );
			}
		}

		if ( isset( $atts['show_image'] ) ) {
			$expected_value = wp_get_attachment_image( $media_id, $size, false );
		}

		if ( isset( $atts['add_link'] ) ) {
			$full_url = wp_get_attachment_url( $media_id );

			if ( ! $expected_value ) {
				$expected_value = $default_url;
			}

			$expected_value = '<a href="' . $full_url . '" class="frm_file_link">' . $expected_value . '</a>';
		}

		if ( ! $expected_value ) {
			$expected_value = $default_url;
		}

		return $expected_value;
	}

	function get_expected_value_for_non_image_file( $media_id, $image, $atts ) {
		$expected_value = '';
		$size = isset( $atts['size'] ) ? $atts['size'] : 'thumbnail';

		$file_url = wp_get_attachment_url( $media_id );

		if ( isset( $atts['show'] ) && $atts['show'] == 'id' ) {
			$expected_value = $media_id;
		}

		if ( isset( $atts['show_filename'] ) ) {
			$attachment = get_post( $media_id );
			$expected_value = basename( $attachment->guid );
		}

		if ( isset( $atts['html'] ) ) {
			if ( isset( $atts['show_filename'] ) ) {
				// If show_filename=1 and html=1 is used, the image is not shown and a link is added to the filename
				$atts['add_link'] = 1;
			} else {
				$expected_value = wp_get_attachment_link( $media_id, $size, false, true, false );
				return $expected_value;
			}
		}

		if ( isset( $atts['show_image'] ) ) {
			$expected_value = wp_get_attachment_image( $media_id, $size, true );
		}


		if ( isset( $atts['add_link'] ) ) {
			if ( ! $expected_value ) {
				$expected_value = $file_url;
			}

			$expected_value = '<a href="' . $file_url . '">' . $expected_value . '</a>';
		}

		if ( ! $expected_value ) {
			$expected_value = $file_url;
		}

		return $expected_value;
	}
}