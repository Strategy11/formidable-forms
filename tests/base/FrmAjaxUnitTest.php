<?php

FrmHooksController::trigger_load_hook( 'load_admin_hooks' );
FrmHooksController::trigger_load_hook( 'load_ajax_hooks' );
FrmHooksController::trigger_load_hook( 'load_form_hooks' );

/**
 * @group ajax
 */
class FrmAjaxUnitTest extends WP_Ajax_UnitTestCase {
	protected $form_id = 0;
	protected $field_id = 0;
	protected $user_id = 0;
	protected $is_pro_active = false;

	function setUp() {
		parent::setUp();
		FrmAppController::install();
		$this->import_xml();

		$this->factory->form = new Form_Factory( $this );
		$this->factory->field = new Field_Factory( $this );
		$this->factory->entry = new Entry_Factory( $this );

		$this->is_pro_active = FrmAppHelper::pro_is_installed();
		$current_class_name = get_class( $this );
		if ( strpos( $current_class_name, 'FrmPro' ) && ! $this->is_pro_active ) {
			$this->markTestSkipped( 'Pro is not active' );
		}
	}

    function import_xml() {
        // install test data in older format
		add_filter( 'frm_default_templates_files', 'FrmUnitTest::install_data' );
        FrmXMLController::add_default_templates();

        $form = FrmForm::getOne( 'contact-db12' );
        $this->assertEquals( $form->form_key, 'contact-db12' );

		$entry = FrmEntry::getOne( 'utah' );
		$this->assertNotEmpty( $entry );
		$this->assertEquals( $entry->item_key, 'utah' );
    }

    function set_as_user_role( $role ) {
        // create user
        $user_id = $this->factory->user->create( array( 'role' => $role ) );
		$user = new WP_User($user_id);
		$this->assertTrue( $user->exists(), 'Problem getting user ' . $user_id );

        // log in as user
        wp_set_current_user($user_id);
        $this->$user_id = $user_id;
		$this->assertTrue( current_user_can( $role ) );
    }
}