<?php

class FrmUnitTest extends WP_UnitTestCase {
	/**
	 * form_id
	 * @var int
	 */
	protected $form_id = 0;

	/**
	 * field_ids
	 * @var array
	 */
	protected $field_ids = array();

	/**
	 * user_id
	 * @var int
	 */
	protected $user_id = 0;

	protected $contact_form_key = 'contact-with-email';

	/**
	 * Ensure that the plugin has been installed and activated.
	 */
	function setUp() {
		parent::setUp();

		$this->factory->form = new Form_Factory( $this );
		$this->factory->field = new Field_Factory( $this );
		$this->factory->entry = new Entry_Factory( $this );

		$this->frm_install();
	}

    /* Helper Functions */
	function frm_install() {
		if ( ! defined( 'WP_IMPORTING' ) ) {
			// set this to false so all our tests won't be done with this active
			define( 'WP_IMPORTING', false );
		}

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

        $form = $this->factory->form->get_object_by_id( 'contact-db12' );
        $this->assertEquals( $form->form_key, 'contact-db12' );
    }

	/**
	* Set the global current user to 1
	*/
	function set_current_user_to_1( ) {
		$user_id = 1;
		$user = get_user_by( 'id', $user_id );
		if ( $user == false ) {
			$user_id = $this->set_as_user_role( 'admin' );
		} else {
			wp_set_current_user( $user_id );
		}
	}

    function set_as_user_role( $role ) {
        // create user
        $user_id = $this->factory->user->create( array( 'role' => $role ) );
		$user = new WP_User( $user_id );

		$this->assertTrue( $user->exists(), 'Problem getting user ' . $user_id );

        // log in as user
        wp_set_current_user( $user_id );
		$this->assertTrue( current_user_can( $role ), 'Failed setting the current user role' );

		FrmAppHelper::maybe_add_permissions( 'frm_view_entries' );

		return $user_id;
    }

	function set_front_end() {
		set_current_screen( 'front' );
		$this->clean_up_global_scope();
		$this->go_to( home_url( '/' ) );
		$this->assertFalse( is_admin(), 'Failed to switch to the front-end' );
	}

	function set_admin_screen( $page = 'index.php' ) {
		global $current_screen;

		$screens = array(
			'index.php' => array( 'base' => 'dashboard', 'id' => 'dashboard' ),
		);

		$screen = $screens[ $page ];

		$_GET = $_POST = $_REQUEST = array();
		$GLOBALS['taxnow'] = $GLOBALS['typenow'] = '';
		$screen = (object) $screen;
		$hook = parse_url( $page );

		if ( ! empty( $hook['query'] ) ) {
			$args = wp_parse_args( $hook['query'] );
			if ( isset( $args['taxonomy'] ) )
				$GLOBALS['taxnow'] = $_GET['taxonomy'] = $_POST['taxonomy'] = $_REQUEST['taxonomy'] = $args['taxonomy'];
			if ( isset( $args['post_type'] ) )
				$GLOBALS['typenow'] = $_GET['post_type'] = $_POST['post_type'] = $_REQUEST['post_type'] = $args['post_type'];
			else if ( isset( $screen->post_type ) )
				$GLOBALS['typenow'] = $_GET['post_type'] = $_POST['post_type'] = $_REQUEST['post_type'] = $screen->post_type;
		}

		$GLOBALS['hook_suffix'] = $hook['path'];
		set_current_screen();

		$this->assertEquals( $screen->id, $current_screen->id, $page );
	}

    static function install_data() {
        return array( dirname( __FILE__ ) . '/testdata.xml' );
    }
}
