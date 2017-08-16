<?php

/**
 * @group fields
 * @group pro
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
	 * Checks single file upload shortcode with different combinations of attributes
	 * Field is retrieving an image file
	 *
	 * @covers FrmProFieldsHelper::get_file_display_value()
	 */
	function test_displayed_image_file_in_view() {
		$att_combinations = self::get_file_att_combinations_for_testing();
		$media_ids = self::get_media_ids_from_database( 'single-file-upload-field', 'jamie_entry_key' );

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
		$media_ids = self::get_media_ids_from_database( 'multi-file-upload-field', 'jamie_entry_key' );

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
		$media_ids = self::get_media_ids_from_database( 'single-file-upload-field', 'steph_entry_key' );

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
		$expected_value = implode( ', ', $media_ids );
		$this->assertEquals( $expected_value, $displayed_value, 'Displayed image value that was modified with custom code is being overridden.');
	}

	/**
	 * Checks [x] where x is a single file upload field ID displaying an image file
	 * Tests what happens when value is empty
	 * @covers FrmProFieldsHelper::get_file_display_value()
	 */
	function test_displayed_files_with_no_value() {
		$media_ids = array( '', '<img src="hello.png" />' );
		$atts = array();

		$displayed_value = FrmProFieldsHelper::get_file_display_value( $media_ids, $atts );
		$expected_value = '<img src="hello.png" />';
		$this->assertEquals( $expected_value, $displayed_value, 'An empty image value is not displayed correctly.');
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
		$sep = self::get_separator_for_displayed_files( $atts );
		$expected_value = '';

		foreach ( $media_ids as $media_id ) {
			$expected_value .= self::get_expected_value_for_file( $media_id, $atts );
			$expected_value .= $sep;
		}

		$expected_value = rtrim( $expected_value, $sep );

		if ( count( $media_ids ) > 1 && ( isset( $atts['show_image'] ) || isset( $atts['html'] ) ) ) {
			$expected_value = '<div class="frm_file_container">' . $expected_value . '</div>';
		}

		$atts_list = '';
		foreach ( $atts as $parameter => $value ) {
			$atts_list .= ' ' . $parameter . '=' . $value;
		}

		$msg = 'The displayed value is not correct for a file with the following atts: ' . $atts_list;
		$this->assertEquals( $expected_value, $displayed_value, $msg );
	}

	function get_separator_for_displayed_files( $atts ) {
		if ( isset( $atts['sep'] ) ) {
			$sep = $atts['sep'];
		} else {
			if ( isset( $atts['show_image'] ) || isset( $atts['html'] ) ) {
				$sep = ' ';
			} else {
				$sep = ', ';
			}
		}

		return $sep;
	}

	function get_expected_value_for_file( $media_id, $atts ) {
		$expected_value = '';

		$size = isset( $atts['size'] ) ? $atts['size'] : 'thumbnail';
		$image = wp_get_attachment_image_src( $media_id, $size );
		self::simplify_atts( $image, $atts );
		$is_non_image = empty( $image );

		// Get default URL
		if ( $is_non_image ) {
			$default_url = wp_get_attachment_url( $media_id );
		} else {
			$default_url = $image[0];
		}

		// show=id
		if ( isset( $atts['show'] ) && $atts['show'] == 'id' ) {
			$expected_value = $media_id;
		}

		// show_filename=1
		if ( isset( $atts['show_filename'] ) ) {
			$attachment = get_post( $media_id );
			$label = basename( $attachment->guid );
			$expected_value .= $label;
		}

		// show_image=1
		if ( isset( $atts['show_image'] ) ) {
			//$expected_value = '<img src="' . esc_attr( $default_url ) . '" />' . $expected_value; old functionality
			$expected_value = wp_get_attachment_image( $media_id, $size, $is_non_image );

			// If show_filename=1 is included
			if ( isset( $label ) ) {
				$expected_value .= ' <span id="frm_media_' . absint( $media_id ) . '" class="frm_upload_label">' . $label . '</span>';
			}
		}

		// add_link=1
		if ( isset( $atts['add_link'] ) ) {
			$full_url = wp_get_attachment_url( $media_id );

			if ( ! $expected_value ) {
				$expected_value = $default_url;
			}

			$expected_value = '<a href="' . $full_url . '" class="frm_file_link">' . $expected_value . '</a>';
		}

		// No atts
		if ( ! $expected_value ) {
			$expected_value = $default_url;
		}

		return $expected_value;
	}

	function simplify_atts( $image, &$atts ) {
		$is_image = ! empty( $image );

		if ( isset( $atts['show'] ) && $atts['show'] == 'label' ) {
			$atts['show_filename'] = '1';
			unset( $atts['show'] );
		}

		if ( isset( $atts['links'] ) && $atts['links'] ) {
			$atts['add_link'] = '1';
			unset( $atts['links'] );
		}

		if ( isset( $atts['html'] ) ) {
			if ( isset( $atts['show_filename'] ) ) {
				$atts['add_link'] = true;
			} else {
				$atts['show_image'] = true;
				if ( ! $is_image ) {
					$atts['add_link'] = true;
				}
			}
		}
	}
}
