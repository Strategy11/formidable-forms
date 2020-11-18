<?php

/**
 * @group entries
 */
class test_FrmEntriesListHelper extends FrmUnitTest {

	private $form_id;
	private $helper;

	/**
	 * @covers FrmEntriesListHelper::column_value form_id
	 */
	public function test_column_value_form_id() {
		$some_form_id  = 63334;
		$this->form_id = $some_form_id;
		$this->helper  = $this->get_new_helper_instance();

		$this->set_private_property( $this->helper, 'column_name', 'form_id' );

		$link  = FrmFormsHelper::edit_form_link( $this->form_id );
		$label = FrmFormsHelper::edit_form_link_label( $this->form_id );

		wp_set_current_user( null );
		$this->assert_value_equals( $link );

		wp_set_current_user( 1 );
		$this->assert_value_equals( $link );

		$this->switch_to_entries_listing_page();
		$this->assert_value_equals( $link );

		wp_set_current_user( null );
		$this->assert_value_equals( $label );
	}

	private function switch_to_entries_listing_page() {
		global $pagenow;
		$pagenow = 'admin.php'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		if ( ! defined( 'WP_ADMIN' ) ) {
			define( 'WP_ADMIN', true );
		}
		$_GET['page'] = 'formidable-entries';
	}

	private function assert_value_equals( $expected ) {
		$value = $this->column_value( $this->get_an_item_object() );
		$this->assertEquals( $expected, $value );
	}

	private function get_new_helper_instance() {
		$GLOBALS['hook_suffix'] = 'post'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$args                   = array(
			'screen' => '',
		);
		return new FrmEntriesListHelper( $args );
	}

	private function get_an_item_object() {
		$item          = new stdClass();
		$item->form_id = $this->form_id;
		return $item;
	}

	private function column_value( $item ) {
		return $this->run_private_method(
			array( $this->helper, 'column_value' ),
			array( $item )
		);
	}
}
