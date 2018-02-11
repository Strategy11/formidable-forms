<?php

/**
 * @group ajax
 * @group free
 */
class test_FrmFieldsAjax extends FrmAjaxUnitTest {

	protected $form_id = 0;
	
	public function setUp() {
		parent::setUp();

		// Set a user so the $post has 'post_author'
		$this->user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );

		$form = $this->factory->form->create_and_get();
		$this->assertNotEmpty( $form );
		$this->form_id = $form->id;
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
	public function test_duplicating_text_field() {
		wp_set_current_user( $this->user_id );
		$this->assertTrue(is_numeric($this->form_id));

		$format = '^([a-zA-Z]\d{4})$';
		$original_field = $this->factory->field->create_and_get( array(
			'form_id'       => $this->form_id,
			'type'          => 'text',
			'field_options' => array(
				'format'    => $format,
				'in_section' => 0,
			),
		) );
		$this->assertEquals( $format, $original_field->field_options['format'] );

		$_POST = array(
			'action'   => 'frm_duplicate_field',
			'nonce'    => wp_create_nonce('frm_ajax'),
			'field_id' => $original_field->id,
			'form_id'  => $original_field->form_id,
		);

		$response = $this->trigger_action( 'frm_duplicate_field' );

		global $wpdb;
		$newest_field_id = $wpdb->insert_id;

		self::check_if_field_id_is_created_correctly( $newest_field_id );

		// make sure the field exists
		$field = FrmField::getOne( $newest_field_id );
		$this->assertTrue( is_object( $field ) );
		$this->assertEquals( $format, $field->field_options['format'] );

		self::check_in_section_variable( $field, 0 );
	}

	// Get a field object by key
	protected function get_field_by_key( $field_key ){
		$divider_field_id = FrmField::get_id_by_key( $field_key );
		$field = FrmField::getOne( $divider_field_id );
		self::check_field_prior_to_duplication( $field );

		return $field;

	}

	// Check in_section variable prior to duplication
	protected function check_field_prior_to_duplication( $field ) {
		$this->assertTrue( isset( $field->field_options[ 'in_section' ] ), 'The in_section variable is not set correctly on import.' );
	}

	// Check if a field is created correctly
	protected function check_if_field_id_is_created_correctly( $newest_field_id ) {
		$this->assertTrue( is_numeric( $newest_field_id ) );
		$this->assertNotEmpty( $newest_field_id );
	}

	// Check for a specific in section value
	protected function check_in_section_variable( $field, $expected ) {
		$message = 'The in_section variable is not set correctly when a ' . $field->type . ' field is duplicated.';
		$this->assertTrue( isset( $field->field_options['in_section'] ), $message );

		$message = 'The in_section variable is not set to the correct value when a ' . $field->type . ' field is duplicated.';
		$this->assertEquals( $expected, $field->field_options['in_section'], $message );
	}
}
