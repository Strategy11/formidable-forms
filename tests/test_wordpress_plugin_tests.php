<?php

class WP_Test_WordPress_Plugin_Tests extends FrmUnitTest {

	function test_plugin_activated() {
		$this->assertTrue( is_plugin_active( 'formidable/formidable.php' ) );
	}

	function test_wpml_install(){
        if ( is_callable('FrmProCopy::install') ) {
	        $copy = new FrmProCopy();
	        $copy->install();
        }
	}

	function test_create_form() {
        $values = FrmFormsHelper::setup_new_vars(false);
        $form_id = FrmForm::create( $values );
        $this->form_id = (int) $form_id;

	    $this->assertTrue(is_numeric($form_id));
		$this->assertTrue($form_id > 0);

	    // create each field type
	    $this->field_ids = array();
	    $field_types = array_merge(FrmFieldsHelper::field_selection(), FrmFieldsHelper::pro_field_selection());
	    foreach ( $field_types as $field_type => $field_info ) {
    	    $field_values = apply_filters('frm_before_field_created', FrmFieldsHelper::setup_new_vars($field_type, $form_id));

            $field_id = FrmField::create( $field_values );
            $this->assertTrue(is_numeric($field_id));
            $this->assertTrue($field_id > 0);

            if ( $field_id ) {
                $this->field_ids[ $field_id ] = rand_str();

                $field = FrmField::getOne($field_id);
                $this->assertNotEmpty($field);

                $field = FrmFieldsHelper::setup_edit_vars($field);
                $this->assertArrayHasKey('id', $field);
            }
        }

        $this->create_entry();
		$this->search_all_entries();
	}

    /**
     * Search for a value in an entry
     */
    function search_all_entries() {
	    $this->assertTrue(is_numeric($this->form_id));

        $items = FrmEntry::getAll( array( 'it.form_id' => $this->form_id ), '', '', true, false);
        $this->assertFalse(empty($items));

        $this->search_by_field();
    }

    function search_by_field() {
	    $this->assertTrue(is_numeric($this->form_id));

        $s = reset($this->field_ids);
        $fid = key($this->field_ids);
        $this->assertTrue(is_numeric($fid));

	    $s_query = array( 'it.form_id' => $this->form_id );

        if ( is_callable('FrmProEntriesHelper::get_search_str') ) {
	        $s_query = FrmProEntriesHelper::get_search_str($s_query, $s, $this->form_id, $fid);
        }

        $items = FrmEntry::getAll($s_query, '', '', true, false);
        $this->assertFalse(empty($items));
    }

    function test_duplicate_form(){
        $form = $this->get_one_form( 'contact' );

        $id = FrmForm::duplicate( $form->id );
        $this->assertTrue( is_numeric($id) );
        $this->assertTrue( $id > 0 );
    }

    function test_delete_form(){
        $forms = FrmForm::getAll();
        $this->assertTrue( count( $forms ) >= 1 );

        foreach ( $forms as $form ) {
            if ( $form->is_template ) {
                continue;
            }

            $id = FrmForm::destroy( $form->id );
            $this->assertNotEmpty( $id, 'Failed to delete form ' . $form->form_key );
        }
    }
}

/**
*
* Namespacing allows more flexibility
* 	- Mock objects without real data
* Check out PHPUnit test doubles, PHPunit data providers, Test-driven development (TDD)
* qunit/phantomjs for js unit testing
* See: "Browser Eyeballing != Javascript testing" by Jordna Kaspar
*
*/