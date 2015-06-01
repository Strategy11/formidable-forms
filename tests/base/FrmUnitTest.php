<?php

class FrmUnitTest extends WP_UnitTestCase {

	protected $form;
	protected $form_id = 0;
	protected $field_ids = array();
	protected $user_id = 0;
	protected $contact_form_key = 'contact-with-email';
	protected $all_fields_form_key = 'all_field_types';
	protected $create_post_form_key = 'create-a-post';
	protected $is_pro_active = false;

	/**
	 * Ensure that the plugin has been installed and activated.
	 */
	function setUp() {
		parent::setUp();
		$this->frm_install();

		$this->factory->form = new Form_Factory( $this );
		$this->factory->field = new Field_Factory( $this );
		$this->factory->entry = new Entry_Factory( $this );

		$this->is_pro_active = FrmAppHelper::pro_is_installed();
		$current_class_name = get_class( $this );
		if ( strpos( $current_class_name, 'FrmPro' ) && ! $this->is_pro_active ) {
			$this->markTestSkipped( 'Pro is not active' );
		}
	}

	/**
	 * @covers FrmAppController::install()
	 */
	function frm_install() {
		if ( ! defined( 'WP_IMPORTING' ) ) {
			// set this to false so all our tests won't be done with this active
			define( 'WP_IMPORTING', false );
		}

		FrmHooksController::trigger_load_hook( 'load_admin_hooks' );
		FrmAppController::install();

		$this->do_tables_exist();
		$this->import_xml();
	}

	function get_table_names() {
		global $wpdb;

		$tables = array(
			$wpdb->prefix . 'frm_fields', $wpdb->prefix . 'frm_forms',
			$wpdb->prefix . 'frm_items',  $wpdb->prefix . 'frm_item_metas',
		);
		if ( is_multisite() && is_callable( 'FrmProCopy::table_name' ) ) {
			$tables[] = FrmProCopy::table_name();
		}

		return $tables;
	}

	function do_tables_exist( $should_exist = true ) {
		global $wpdb;
		$method = $should_exist ? 'assertNotEmpty' : 'assertEmpty';
		foreach ( $this->get_table_names() as $table_name ) {
			$this->$method( $wpdb->query( 'DESCRIBE ' . $table_name ), $table_name . ' table failed to (un)install' );
		}
	}

    function import_xml() {
        // install test data in older format
		add_filter( 'frm_default_templates_files', 'FrmUnitTest::install_data' );
        FrmXMLController::add_default_templates();

        $form = FrmForm::getOne( 'contact-db12' );
        $this->assertEquals( $form->form_key, 'contact-db12' );
    }

	/**
	* Set the global current user to 1
	*/
	function set_current_user_to_1( ) {
		$user_id = 1;
		$user = $this->factory->user->get_object_by_id( $user_id );
		if ( $user == false ) {
			$user_id = $this->set_as_user_role( 'admin' );
		} else {
			wp_set_current_user( $user_id );
		}
	}

    function set_as_user_role( $role ) {
        // create user
		$user = $this->factory->user->create_and_get( array( 'role' => $role ) );
		$this->assertTrue( $user->exists(), 'Problem getting user' );

		// log in as user
		wp_set_current_user( $user->ID );
		$this->assertTrue( current_user_can( $role ), 'Failed setting the current user role' );

		FrmAppHelper::maybe_add_permissions();

		return $user->ID;
    }

	function go_to_new_post() {
		$new_post = $this->factory->post->create_and_get();
		$page = get_permalink( $new_post->ID );

		$this->set_front_end( $page );
		return $new_post->ID;
	}

	function set_front_end( $page = '' ) {
		if ( $page == '' ) {
			$page = home_url( '/' );
		}

		$this->clean_up_global_scope();
		$this->go_to( $page );
		$this->assertFalse( is_admin(), 'Failed to switch to the front-end' );
	}

	function set_admin_screen( $page = 'index.php' ) {
		global $current_screen;

		$screens = array(
			'index.php' => array( 'base' => 'dashboard', 'id' => 'dashboard' ),
			'admin.php?page=formidable' => array( 'base' => 'admin', 'id' => 'toplevel_page_formidable' ),
		);

		if ( $page == 'formidable-edit' ) {
			$form = $this->factory->form->get_object_by_id( $this->contact_form_key );
			$page = 'admin.php?page=formidable&frm_action=edit&id=' . $form->id;
			$screens[ $page ] = $screens['admin.php?page=formidable'];
		}

		$screen = $screens[ $page ];

		$_GET = $_POST = $_REQUEST = array();
		$GLOBALS['taxnow'] = $GLOBALS['typenow'] = '';
		$screen = (object) $screen;
		$hook = parse_url( $page );

		$GLOBALS['hook_suffix'] = $hook['path'];
		set_current_screen();

		$this->assertTrue( $current_screen->in_admin(), 'Failed to switch to the back-end' );
		$this->assertEquals( $screen->base, $current_screen->base, $page );

		FrmHooksController::trigger_load_hook();
	}

	function clean_up_global_scope() {
		parent::clean_up_global_scope();
		if ( isset( $GLOBALS['current_screen'] ) ) {
			unset( $GLOBALS['current_screen'] );
		}

		global $frm_vars;
		$frm_vars = array(
			'load_css'          => false,
			'forms_loaded'      => array(),
			'created_entries'   => array(),
			'pro_is_authorized' => false,
			'next_page'         => array(),
			'prev_page'         => array(),
		);

		if ( class_exists( 'FrmUpdatesController' ) ) {
			global $frm_update;
			$frm_update  = new FrmUpdatesController();
			$frm_vars['pro_is_authorized'] = $frm_update->pro_is_authorized();
		}
	}

	function get_footer_output() {
        ob_start();
        do_action( 'wp_footer' );
        $output = ob_get_contents();
        ob_end_clean();

		return $output;
	}

    static function install_data() {
        return array( dirname( __FILE__ ) . '/testdata.xml' );
    }
}
