<?php

/**
 * @group entries
 */
class test_FrmEntriesListHelper extends FrmUnitTest {

	public $factory;
	/**
	 * @covers FrmEntriesListHelper::column_value
	 */
	public function test_column_value() {
		FrmAppHelper::set_current_screen_and_hook_suffix();

		$item = new stdClass();

		// This doesn't need to be accurate for this test. This ID doesn't match the database.
		$item->id             = 1;
		$item->name           = 'My entry name';
		$item->description    = array(
			'browser'  => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:107.0) Gecko/20100101 Firefox/107.0',
			'referrer' => 'http://example.com',
		);
		$item->post_id        = 0;
		$item->form_id        = $this->factory->form->create();
		$description_field_id = $this->factory->field->create(
			array(
				'form_id'   => $item->form_id,
				'field_key' => 'description',
			)
		);
		$item->metas          = array(
			$description_field_id => 'Description field value',
		);

		$column_value = $this->column_value( $item, 'description' );
		$this->assertIsString( $column_value );
		$this->assertEquals( 'Description field value', $column_value );

		$column_value = $this->column_value( $item, 'id' );
		$this->assertEquals( 1, $column_value );

		$column_value = $this->column_value( $item, 'name' );
		$this->assertEquals( 'My entry name', $column_value );
	}

	/**
	 * @param stdClass $item
	 * @param string   $column_name
	 */
	private function column_value( $item, $column_name ) {
		$list_helper = new FrmEntriesListHelper( array() );
		$this->set_private_property( $list_helper, 'column_name', $column_name );
		return $this->run_private_method( array( $list_helper, 'column_value' ), array( $item ) );
	}
}
