<?php

/**
 * @group stripe
 */
class test_FrmTransLiteListHelper extends FrmUnitTest {

	/**
	 * Builds a payments list helper instance.
	 *
	 * @return FrmTransLiteListHelper
	 */
	private function get_list_helper() {
		$_REQUEST['trans_type'] = 'payments';
		return new FrmTransLiteListHelper( array( 'params' => array() ) );
	}

	/**
	 * @covers FrmTransLiteListHelper::get_table_query
	 */
	public function test_get_table_query() {
		global $wpdb;

		$list_helper = $this->get_list_helper();
		$query       = $this->run_private_method( array( $list_helper, 'get_table_query' ), array() );
		$this->assertStringContainsString( 'FROM `' . $wpdb->prefix . 'frm_payments` p', $query );

		$form_id      = $this->factory->form->create();
		$_GET['form'] = $form_id;
		$query        = $this->run_private_method( array( $list_helper, 'get_table_query' ), array() );

		unset( $_GET['form'], $_REQUEST['trans_type'] );

		$this->assertStringContainsString( 'FROM `' . $wpdb->prefix . 'frm_payments` p', $query );
		$this->assertStringContainsString( 'JOIN `' . $wpdb->prefix . 'frm_items` i ON p.item_id = i.id', $query );
		$this->assertStringContainsString( 'i.form_id = ' . $form_id, $query );
	}

	/**
	 * @covers FrmTransLiteListHelper::get_form_ids
	 */
	public function test_get_form_ids() {
		$form               = $this->factory->form->create_and_get();
		$entry              = $this->factory->entry->create_and_get( $this->factory->field->generate_entry_array( $form ) );
		$list_helper        = $this->get_list_helper();
		$list_helper->items = array(
			(object) array( 'item_id' => $entry->id ),
		);

		$form_ids = $this->run_private_method( array( $list_helper, 'get_form_ids' ), array() );

		unset( $_REQUEST['trans_type'] );

		$this->assertArrayHasKey( $entry->id, $form_ids );
		$this->assertEquals( $form->id, $form_ids[ $entry->id ]->form_id );
	}
}
