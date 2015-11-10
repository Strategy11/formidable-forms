<?php
/**
 * @group xml
 */
class WP_Test_FrmXMLHelper extends FrmUnitTest {

	function test_imported_fields(){
		$imported_fields = $this->get_all_fields_for_form_key( $this->all_fields_form_key );

		$total_fields_to_test = 2;
		$fields_tested = 0;
		foreach ( $imported_fields as $f ) {
			self::_check_imported_repeating_fields( $f, $fields_tested );
			self::_check_imported_embed_form_fields( $f, $fields_tested );
			// Check fields inside repeating section
		}

		$this->assertEquals( $fields_tested, $total_fields_to_test, 'Only ' . $fields_tested . ' fields were tested, but ' . $total_fields_to_test . ' were expected.');
	}

	function _check_imported_repeating_fields( $f, &$fields_tested ){
		if ( ! FrmField::is_repeating_field( $f ) ) {
			return;
		}

		$fields_tested++;

		self::_check_form_select( $f, 'rep_sec_form' );
	}

	function _check_imported_embed_form_fields( $f, &$fields_tested ){
		if ( $f->type != 'form' ) {
			return;
		}

		$fields_tested++;

		self::_check_form_select( $f, $this->contact_form_key );
	}

	/**
	* @covers FrmXMLHelper::track_repeating_fields
	* @covers FrmXMLHelper::update_repeat_field_options
	*/
	function _check_form_select( $f, $expected_form_key ) {
		$this->assertNotEmpty( $f->field_options['form_select'], 'Imported repeating section has a blank form_select.' );

		// Check if the form_select setting matches the correct form
		$nested_form = FrmForm::getOne( $f->field_options['form_select'] );
		$this->assertNotEmpty( $nested_form, 'The form_select in an imported repeating section is not updating correctly.');
		$this->assertEquals( $expected_form_key, $nested_form->form_key, 'The form_select is not updating properly when a repeating section is imported.');
	}

	function test_imported_forms() {
		$imported_forms = FrmForm::getAll();

		self::_check_parent_form_id( $imported_forms );
	}

	function _check_parent_form_id( $imported_forms ) {
		$child_form_key = 'rep_sec_form';
		$expected_parent_id = FrmForm::getIdByKey( $this->all_fields_form_key );

		foreach ( $imported_forms as $form ) {
			if ( $form->form_key == $child_form_key ) {
				$this->assertTrue( $form->parent_form_id != 0, 'Parent form ID was removed when ' . $child_form_key . ' form was imported.' );
				$this->assertEquals( $expected_parent_id, $form->parent_form_id, 'The parent form was not updated correctly when the ' . $child_form_key . ' form was imported.' );
			} else {
				$this->assertEquals( 0, $form->parent_form_id, 'Parent form ID was added to ' . $form->form_key . ' on import.' );
			}
		}

	}

	function test_xml_import_to_update_fields_and_forms() {
		$args = self::_get_xml_update_args();

		// TODO: export XML file instead of using repeating_section_data.xml
		//self::_generate_xml_for_all_fields_form();
		$path = FrmAppHelper::plugin_path() . '/tests/base/repeating_section_data.xml';
		$message = FrmXMLHelper::import_xml( $path );

		self::_check_xml_updated_forms_parent_id( $args );
		self::_check_xml_updated_fields( $args );
		self::_check_xml_updated_repeating_fields( $args );
		self::_check_xml_updated_repeating_section( $args );

		// Note: 3 parent entries should be updated and 9 repeating entries should be updated
		self::_check_the_imported_and_updated_numbers( $message );
	}

	function _get_xml_update_args() {
		$parent_form_id = FrmForm::getIdByKey( $this->all_fields_form_key );
		$repeating_section_id = FrmField::get_id_by_key( 'repeating-section' );
		$all_fields = FrmField::get_all_for_form( $parent_form_id, '', 'include', 'include' );
		$repeating_section = FrmField::getOne( $repeating_section_id );
		$rep_sec_form = FrmForm::getOne( $repeating_section->field_options['form_select'] );
		$repeating_fields = FrmField::get_all_for_form( $repeating_section->field_options['form_select'] );

		$args = array(
			'parent_form_id' => $parent_form_id,
			'repeating_section' => $repeating_section,
			'all_fields' => $all_fields,
			'rep_sec_form' => $rep_sec_form,
			'repeating_fields' => $repeating_fields
		);

		return $args;
	}

	function _check_xml_updated_forms_parent_id( $args ) {
		$original_parent_id = $args['rep_sec_form']->parent_form_id;
		$new_form = FrmForm::getOne( $args['rep_sec_form']->id );
		$new_parent_id = $new_form->parent_form_id;

		$this->assertEquals( $original_parent_id, $new_parent_id, 'The repeating section form\'s parent ID was modified on XML import when it should not have been updated.' );
	}

	function _check_xml_updated_fields( $args ) {
		$fields = FrmField::get_all_for_form( $args['parent_form_id'], '', 'include', 'include' );

		$this->assertEquals( count( $args['all_fields'] ), count( $fields ), 'Fields were either added or removed on XML import, but they should have been updated.' );
	}

	function _check_xml_updated_repeating_fields( $args ) {
		$fields = FrmField::get_all_for_form( $args['repeating_section']->field_options['form_select'] );

		// Check if the number of fields in repeating form is correct
		$this->assertEquals( count( $args['repeating_fields'] ), count( $fields ), 'Fields in repeating section were either added or deleted when they should have been updated.' );

		// Make sure the same fields are still in the section
		$repeating_field_keys = array( 'repeating-text', 'repeating-checkbox', 'repeating-date' );
		foreach ( $fields as $field ) {
			$this->assertTrue( in_array( $field->field_key, $repeating_field_keys ), 'A field with the key ' . $field->field_key . ' was created when it should have been upated.' );
		}
	}

	function _check_xml_updated_repeating_section( $args ) {
		$expected_form_select = $args['repeating_section']->field_options['form_select'];
		$new_repeating_section = FrmField::getOne( $args['repeating_section']->id );
		$new_form_select = $new_repeating_section->field_options['form_select'];

		$this->assertEquals( $expected_form_select, $new_form_select, 'A repeating section\'s form_select was changed on XML import, but it should have remained the same.' );
	}

	function _check_the_imported_and_updated_numbers( $message ) {
		foreach ( $message['imported'] as $type => $number ) {
			$this->assertEquals( 0, $number, $number . ' ' . $type . ' were imported but they should have been updated.' );
		}

		$expected_numbers = array(
			'forms' => 2,
			'fields' => 36,
			'items' => 12
		);

		foreach ( $expected_numbers as $type => $e_number ) {
			$this->assertEquals( $e_number, $message['updated'][ $type ], 'There is a discrepancy between the number of ' . $type . ' expected to be updated vs. the actual number of updated ' . $type . '. Before digging into this, check the $expected_numbers to make sure it is correct.' );
		}
	}

	/*function _generate_xml_for_all_fields_form() {
		global $wpdb;

		$type = array(
			'forms',
			'items',
			'actions'
		};

		$tables = array(
			'items'     => $wpdb->prefix .'frm_items',
			'forms'     => $wpdb->prefix .'frm_forms',
			'posts'     => $wpdb->posts,
			'styles'    => $wpdb->posts,
			'actions'   => $wpdb->posts,
		);

		$defaults = array( 'ids' => false );
		$args = wp_parse_args( $args, $defaults );

		$sitename = sanitize_key( get_bloginfo( 'name' ) );

		if ( ! empty( $sitename ) ) {
			$sitename .= '.';
		}
		$filename = $sitename . 'formidable.' . date( 'Y-m-d' ) . '.xml';

		$xml = new SimpleXMLElement('<xml/>');

		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );

		//make sure ids are numeric
		if ( is_array( $args['ids'] ) && ! empty( $args['ids'] ) ) {
			$args['ids'] = array_filter( $args['ids'], 'is_numeric' );
		}

		$records = array();

		foreach ( $type as $tb_type ) {
			$where = array();
			$join = '';
			$table = $tables[ $tb_type ];

			$select = $table .'.id';
			$query_vars = array();

			switch ( $tb_type ) {
				case 'forms':
					//add forms
					if ( $args['ids'] ) {
						$where[] = array( 'or' => 1, $table . '.id' => $args['ids'], $table .'.parent_form_id' => $args['ids'] );
					} else {
						$where[ $table . '.status !' ] = 'draft';
					}
					break;
					case 'actions':
					$select = $table .'.ID';
					$where['post_type'] = FrmFormActionsController::$action_post_type;
					if ( ! empty($args['ids']) ) {
						$where['menu_order'] = $args['ids'];
					}
					break;
					case 'items':
					//$join = "INNER JOIN {$wpdb->prefix}frm_item_metas im ON ($table.id = im.item_id)";
					if ( $args['ids'] ) {
						$where[ $table . '.form_id' ] = $args['ids'];
					}
					break;
					case 'styles':
					// Loop through all exported forms and get their selected style IDs
					$form_ids = $args['ids'];
					$style_ids = array();
					foreach ( $form_ids as $form_id ) {
						$form_data = FrmForm::getOne( $form_id );
						// For forms that have not been updated while running 2.0, check if custom_style is set
						if ( isset( $form_data->options['custom_style'] ) ) {
							$style_ids[] = $form_data->options['custom_style'];
						}
						unset( $form_id, $form_data );
					}
					$select = $table .'.ID';
					$where['post_type'] = 'frm_styles';

					// Only export selected styles
					if ( ! empty( $style_ids ) ) {
						$where['ID'] = $style_ids;
					}
					break;
					default:
					$select = $table .'.ID';
					$join = ' INNER JOIN ' . $wpdb->postmeta . ' pm ON (pm.post_id=' . $table . '.ID)';
					$where['pm.meta_key'] = 'frm_form_id';

					if ( empty($args['ids']) ) {
						$where['pm.meta_value >'] = 1;
					} else {
						$where['pm.meta_value'] = $args['ids'];
					}
					break;
				}

				$records[ $tb_type ] = FrmDb::get_col( $table . $join, $where, $select );
				unset($tb_type);
			}

			echo '<?xml version="1.0" encoding="' . esc_attr( get_bloginfo('charset') ) . "\" ?>\n";
			include(FrmAppHelper::plugin_path() .'/classes/views/xml/xml.php');
	}*/
}