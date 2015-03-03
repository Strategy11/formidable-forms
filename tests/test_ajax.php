<?php
/**
 * @group ajax
 */
class Tests_Frm_Ajax extends WP_Ajax_UnitTestCase {
	/**
	 * form_id
	 * @var int
	 */
	protected $form_id = 0;

	/**
	 * field_id
	 * @var int
	 */
	protected $field_id = 0;

	/**
	 * user_id
	 * @var int
	 */
	protected $user_id = 0;

	/**
	 * Set up the test fixture
	 */

	public function setUp() {
		parent::setUp();
		// Set a user so the $post has 'post_author'
		$this->user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->user_id );

        $values = FrmFormsHelper::setup_new_vars(false);
        $form_id = FrmForm::create( $values );
        $this->form_id = (int) $form_id;
	}

    public function test_create_field() {
        wp_set_current_user( $this->user_id );
        $this->assertTrue(is_numeric($this->form_id));

		// Set up the $_POST request
		$_POST = array(
			'action'    => 'frm_insert_field',
            'nonce'     => wp_create_nonce('frm_ajax'),
			'form_id'   => $this->form_id,
            'field'     => 'text', //create text field
		);

		// Make the request
		try {
			$this->_handleAjax( 'frm_insert_field' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

        global $wpdb;
        $this->field_id = $wpdb->insert_id;

        $this->assertTrue(is_numeric($this->field_id));
        $this->assertTrue($this->field_id > 0);

        // make sure the field exists
		$field = FrmField::getOne( $this->field_id );
        $this->assertTrue(is_object($field));

        $this->edit_field_name();
    }

	// Test editing a field name
	public function edit_field_name() {
		wp_set_current_user( $this->user_id );
        $new_name = 'New Field Name';

        $this->assertTrue(is_numeric($this->field_id));

		// Set up the $_POST request
		$_POST = array(
			'action'        => 'frm_field_name_in_place_edit',
            'element_id'    => $this->field_id,
            'update_value'  => $new_name,
		);

		// Make the request
		try {
			$this->_handleAjax( 'frm_field_name_in_place_edit' );
		} catch ( WPAjaxDieContinueException $e ) {
			unset( $e );
		}

		// Check that the edit happened
		$field = FrmField::getOne( $this->field_id );

        $this->assertTrue(is_object($field));
		$this->assertEquals( $field->name, $new_name );
	}

    // Prevent unauthorized user from unistalling
	function test_block_uninstall(){
        $this->set_as_user_role('editor');

        try {
            $frmdb = new FrmDb();
            $uninstalled = $frmdb->uninstall();
            $this->assertNotEquals($uninstalled, true);
        } catch ( WPAjaxDieStopException $e ) {
            $this->assertTrue( $e->getMessage() ? true : false );
        }

        $exists = $wpdb->query( 'DESCRIBE '. $wpdb->prefix . 'frm_fields' );
        $this->assertTrue($exists ? true : false);
	}

    /* Helper Functions */
    function set_as_user_role( $role ) {
        // create user
        $user_id = $this->factory->user->create( array( 'role' => $role ) );
		$user = new WP_User($user_id);
		$this->assertTrue($user->exists(), "Problem getting user $user_id");

        // log in as user
        wp_set_current_user($user_id);
        $this->$user_id = $user_id;
    }
}