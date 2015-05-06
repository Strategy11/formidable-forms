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

    /* Helper Functions */
	function frm_install() {
		if ( ! defined( 'WP_IMPORTING' ) ) {
			// set this to false so all our tests won't be done with this active
			define( 'WP_IMPORTING', false );
		}

		FrmAppController::install();

		global $wpdb;
		$exists = $wpdb->query( 'DESCRIBE ' . $wpdb->prefix . 'frm_fields' );
		$this->assertTrue( $exists ? true : false );

		$exists = $wpdb->query( 'DESCRIBE ' . $wpdb->prefix . 'frm_forms' );
		$this->assertTrue( $exists ? true : false );

		$exists = $wpdb->query( 'DESCRIBE ' . $wpdb->prefix . 'frm_items' );
		$this->assertTrue( $exists ? true : false );

		$exists = $wpdb->query( 'DESCRIBE ' . $wpdb->prefix . 'frm_item_metas' );
		$this->assertTrue( $exists ? true : false );

		$this->import_xml();
	}

    function import_xml() {
        // install test data in older format
		add_filter( 'frm_default_templates_files', 'FrmUnitTest::install_data' );
        FrmXMLController::add_default_templates();

        $form = $this->get_one_form( 'contact-db12' );
        $this->assertEquals( $form->form_key, 'contact-db12' );
    }

    function get_one_form( $form_key ) {
        $form = FrmForm::getOne( $form_key );
        $this->assertNotEmpty( $form, 'Problem getting form ' . $form_key );
        return $form;
    }

	/**
	 * Get all fields in a form
	 */
	function get_fields( $form_id ) {
		$fields = FrmField::get_all_for_form( $form_id );
		$this->assertNotEmpty( $fields );
		return $fields;
	}

    function set_as_user_role( $role ) {
        // create user
        $user_id = $this->factory->user->create( array( 'role' => $role ) );
		$user = new WP_User( $user_id );

		$this->assertTrue( $user->exists(), 'Problem getting user ' . $user_id );

        // log in as user
        wp_set_current_user( $user_id );
		FrmAppHelper::maybe_add_permissions();
    }

	/**
	 * When creating an entry, set the correct data formats
	 */
	function set_field_value( $field ) {
		$value = rand_str();
		$field_values = array(
			'email'  => 'test@test.com',
			'url'    => 'http://test.com',
			'number' => 120,
			'date'   => '2015-01-01',
		);

		if ( isset( $field_values[ $field->type ] ) ) {
			$value = $field_values[ $field->type ];
		}

		return $value;
	}

    /**
	 * create an entry
	 */
    function create_entry( $values = array() ) {
        $default_values = array(
            'form_id'   => $this->form_id,
            'item_key'  => rand_str(),
            'item_meta' => $this->field_ids,
        );
		$values = array_merge( $default_values, $values );
        $entry_id = FrmEntry::create( $values );

	    $this->assertTrue( is_numeric( $entry_id ) );
        $this->assertTrue( $entry_id > 0 );

		return $entry_id;
    }

    static function install_data() {
        return array( dirname( __FILE__ ) . '/testdata.xml' );
    }
}
