<?php

/**
 * @group ajax
 */
class WP_Test_FrmFieldsAjax extends FrmAjaxUnitTest {
	
	public function setUp() {
		parent::setUp();

		// Set a user so the $post has 'post_author'
		$this->user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );

		$form = $this->factory->form->create_and_get();
		$this->assertNotEmpty( $form );
		$this->form_id = $form->id;
	}

	public function _test_load_field() {
		
	}

	/**
	 * @covers FrmFieldsController::create
	 */
    public function test_create() {
        wp_set_current_user( $this->user_id );
        $this->assertTrue(is_numeric($this->form_id));

		$_POST = array(
			'action'    => 'frm_insert_field',
            'nonce'     => wp_create_nonce('frm_ajax'),
			'form_id'   => $this->form_id,
            'field_type'     => 'text', //create text field
		);

		try {
			$this->_handleAjax( 'frm_insert_field' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

        global $wpdb;
        $this->field_id = $wpdb->insert_id;

        $this->assertTrue( is_numeric( $this->field_id ) );
        $this->assertNotEmpty( $this->field_id );

        // make sure the field exists
		$field = FrmField::getOne( $this->field_id );
        $this->assertTrue( is_object( $field ) );
    }

	/**
	 * Test duplicating a text field
	 *
	 * @covers FrmFieldsController::duplicate
	 */
	function test_duplicating_text_field() {
		wp_set_current_user( $this->user_id );
		$this->assertTrue(is_numeric($this->form_id));

		$text_field = self::get_field_by_key( 'text-field' );

		$_POST = array(
			'action' => 'frm_duplicate_field',
			'nonce' => wp_create_nonce('frm_ajax'),
			'field_id' => $text_field->id,
			'form_id' => $this->form_id,
		);

		try {
			$this->_handleAjax( 'frm_duplicate_field' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		global $wpdb;
		$newest_field_id = $wpdb->insert_id;

		self::check_if_field_id_is_created_correctly( $newest_field_id );

		// make sure the field exists
		$field = FrmField::getOne( $newest_field_id );
		$this->assertTrue( is_object( $field ) );

		self::check_in_section_variable( $field, 0 );
	}

	/**
	 * Test duplicating a divider field (not repeating)
	 *
	 * @covers FrmFieldsController::duplicate
	 * @covers FrmProFieldsController::duplicate_section
	 */
	function test_duplicating_divider_field() {
		wp_set_current_user( $this->user_id );
		$this->assertTrue(is_numeric($this->form_id));

		$divider_field = self::get_field_by_key( 'pro-fields-divider' );
		$children = self::get_divider_children( $divider_field );

		$_POST = array(
			'action' => 'frm_duplicate_field',
			'nonce' => wp_create_nonce('frm_ajax'),
			'field_id' => $divider_field->id,
			'form_id' => $this->form_id,
			'children' => $children,
		);

		try {
			$this->_handleAjax( 'frm_duplicate_field' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		self::check_duplicated_divider_and_children( $children );
	}

	// Get children from a divider field object
	function get_divider_children( $divider_field ) {
		$field_array = get_object_vars( $divider_field );

		return FrmProField::get_children( $field_array );
	}

	// Check a duplicated divider and its children
	function check_duplicated_divider_and_children( $original_children ) {
		global $wpdb;
		$newest_field_id = $wpdb->insert_id;

		self::check_for_end_divider( $newest_field_id );

		$divider_id = $newest_field_id - count( $original_children ) - 1;
		self::check_duplicated_divider( $divider_id );

		$num_children = count( $original_children );
		for ( $i = 0; $i<$num_children; $i++ ) {

			$get_field_id = $divider_id + $i + 1;

			self::check_duplicated_field_values( $get_field_id, $original_children[ $i ], $divider_id );
		}

	}

	// Check for an end divider (when a divider is duplicated)
	function check_for_end_divider( $newest_field_id ) {
		$last_field_added = FrmField::getOne( $newest_field_id );
		$this->assertEquals( 'end_divider', $last_field_added->type, 'When a section is duplicated, the last field added should be an end divider' );
	}

	// Check for a start divider (when a divider is duplicated)
	function check_duplicated_divider( $field_id ) {
		$divider = FrmField::getOne( $field_id );

		$this->assertEquals( 'divider', $divider->type, 'Duplicating divider not working as expected.' );

		self::check_in_section_variable( $divider, 0 );
	}

	// Check fields inside of duplicated section
	function check_duplicated_field_values( $get_field_id, $original_field_id, $new_divider_id ) {
		$new_field = FrmField::getOne( $get_field_id );
		$original_field = FrmField::getOne( $original_field_id );

		// Check field type
		$this->assertEquals( $original_field->type, $new_field->type, 'Field in section is not duplicated correctly.' );

		// Check in_section variable
		self::check_in_section_variable( $new_field, $new_divider_id );
		$this->assertTrue( $new_field->field_options['in_section'] != 0, 'in section variable set to 0 when a section is duplicated.');
	}

	// Get a field object by key
	function get_field_by_key( $field_key ){
		$divider_field_id = FrmField::get_id_by_key( $field_key );
		$field = FrmField::getOne( $divider_field_id );
		self::check_field_prior_to_duplication( $field );

		return $field;

	}

	// Check in_section variable prior to duplication
	function check_field_prior_to_duplication( $field ) {
		$this->assertTrue( isset( $field->field_options[ 'in_section' ] ), 'The in_section variable is not set correctly on import.' );
	}

	// Check if a field is created correctly
	function check_if_field_id_is_created_correctly( $newest_field_id ) {
		$this->assertTrue( is_numeric( $newest_field_id ) );
		$this->assertNotEmpty( $newest_field_id );
	}

	// Check for a specific in section value
	function check_in_section_variable( $field, $expected ) {
		$message = 'The in_section variable is not set correctly when a ' . $field->type . ' field is duplicated.';
		$this->assertTrue( isset( $field->field_options['in_section'] ), $message );

		$message = 'The in_section variable is not set to the correct value when a ' . $field->type . ' field is duplicated.';
		$this->assertEquals( $expected, $field->field_options['in_section'], $message );
	}

	/**
	 * @covers FrmFieldsController::edit_name
	 */
	function test_edit_name() {
		wp_set_current_user( $this->user_id );
		$form = $this->factory->form->get_object_by_id( 'contact-with-email' );
		$field = $this->factory->field->create_and_get( array('form_id' => $form->id ) );
        $this->assertNotEmpty( $field );

		$new_name = 'New Field Name';
		$_POST = array(
			'action'        => 'frm_field_name_in_place_edit',
            'element_id'    => $field->id,
            'update_value'  => $new_name,
			'nonce'         => wp_create_nonce( 'frm_ajax' ),
		);

		try {
			$this->_handleAjax( 'frm_field_name_in_place_edit' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		$response = $this->_last_response;
		$this->assertEquals( $response, $new_name );

		// Check that the edit happened
		$field = $this->factory->field->get_object_by_id( $field->id );

        $this->assertTrue( is_object( $field ), 'Failed to get field ' . $field->id );
		$this->assertEquals( $field->name, $new_name );
	}
}