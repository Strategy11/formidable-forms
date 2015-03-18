<?php

class FrmUnitTest extends WP_UnitTestCase {

    /* Helper Functions */
	function frm_install() {
		FrmAppController::install();

		global $wpdb;
		$exists = $wpdb->query( 'DESCRIBE '. $wpdb->prefix . 'frm_fields' );
		$this->assertTrue($exists ? true : false);

		$exists = $wpdb->query( 'DESCRIBE '. $wpdb->prefix . 'frm_forms' );
		$this->assertTrue($exists ? true : false);

		$exists = $wpdb->query( 'DESCRIBE '. $wpdb->prefix . 'frm_items' );
		$this->assertTrue($exists ? true : false);

		$exists = $wpdb->query( 'DESCRIBE '. $wpdb->prefix . 'frm_item_metas' );
		$this->assertTrue($exists ? true : false);
	}

    function get_one_form( $form_key ) {
        $form = FrmForm::getOne( $form_key );
        $this->assertTrue($form ? true : false);
        return $form;
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

    static function install_data() {
        return array( dirname( __FILE__ ) . '/testdata.xml' );
    }
}
